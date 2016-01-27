<?php
/**
 * 接口调用频率限制
 *
 * php version of https://www.npmjs.com/package/ratelimiter
 *
 * @Author: 黄景祥(Joel Huang) <joelhy@gmail.com>
 * @Date  :   2016-01-04 18:50:36
 * @Last  Modified by:   Joel Huang
 * @Last  Modified time: 2016-01-05 14:21:34
 */
namespace App\Middleware;

class RateLimiter {
    const HEADER_LIMIT = 'X-RateLimit-Limit';
    const HEADER_RESET = 'X-RateLimit-Reset';
    const HEADER_REMAINING = 'X-RateLimit-Remaining';
    const HEADER_RETRY_AFTER = 'Retry-After';

    /**
     * Redis
     *
     * @var \Redis
     */
    private $db;

    protected $id = null;
    protected $max = null;
    protected $count = null;
    protected $limit = null;
    protected $reset = null;
    protected $opts = [];

    /**
     * 构造函数
     *
     * @param array $opts
     */
    public function __construct($opts = []) {
        $this->id       = $opts['id'] ?? null;
        $this->db       = $opts['db'];
        $this->max      = $opts['max'] ?? 2500;
        $this->duration = $opts['duration'] ?? 3600000;
        $this->opts     = $opts;
    }

    public function inspect() {
        return [
            'id'       => $this->id,
            'duration' => $this->duration,
            'max'      => $this->max,
        ];
    }

    public function get() {
        $this->prefix = 'limit:' . $this->id . ':';
        $this->count  = $this->prefix . 'count';
        $this->limit  = $this->prefix . 'limit';
        $this->reset  = $this->prefix . 'reset';

        return $this->mget();
    }

    protected function create() {
        $ex = time() + round($this->duration / 1000);

        $replies = $this->db->multi()
            ->set($this->count, $this->max, ['nx', 'px' => $this->duration])
            ->set($this->limit, $this->max, ['nx', 'px' => $this->duration])
            ->set($this->reset, $ex, ['nx', 'px' => $this->duration])
            ->exec();

        $error = $this->db->getLastError();
        if ($error) {
            return $error;
        }

        // If the request has failed, it means the values already
        // exist in which case we need to get the latest values.
        if (self::isFirstReplyNull($replies)) {
            return $this->mget();
        }

        return [
            'total'     => $this->max,
            'remaining' => $this->max,
            'reset'     => $ex,
        ];
    }

    protected function decr($res) {
        $n   = ~~$res[0];
        $max = ~~$res[1];
        $ex  = ~~$res[2];

        $done = function () use (&$max, &$n, &$ex) {
            return [
                'total'     => $max,
                'remaining' => $n < 0 ? 0 : $n,
                'reset'     => $ex,
            ];
        };

        if ($n <= 0) {
            return $done();
        }

        // $date_now = round(microtime(true) * 1000);
        // pexpire 有问题，不能用
        $date_now = time();
        $replies  = $this->db->multi()
            // ->set($this->count, $n - 1, ['xx', 'px' => $ex * 1000 - $date_now])
            // ->pexpire($this->limit, $ex * 1000 - $date_now)
            // ->pexpire($this->reset, $ex * 1000 - $date_now)
            ->set($this->count, $n - 1, ['xx', 'ex' => $ex - $date_now])
            ->expire($this->limit, $ex - $date_now)
            ->expire($this->reset, $ex - $date_now)
            ->exec();

        $error = $this->db->getLastError();
        if ($error) {
            return $error;
        }

        if (self::isFirstReplyNull($replies)) {
            return $this->mget();
        }

        return $done();
    }

    protected function mget() {
        $this->db->watch([$this->count]);
        $error = $this->db->getLastError();
        if ($error) {
            return $error;
        }

        $replies = $this->db->mGet([$this->count, $this->limit, $this->reset]);
        $error   = $this->db->getLastError();
        if ($error) {
            return $error;
        }

        if (!$replies[0] && $replies[0] !== '0') {
            return $this->create();
        }

        return $this->decr($replies);
    }

    protected function call(Req $req, Res $res, callable $next) {
        $limit = $this->get();
        if (is_string($limit)) {
            return $next($req, $res);
        }

        $id = $this->id;

        if (!$id) {
            return $next($req, $res);
        }

        /** @var Res $res */
        $res = $res->withHeader(self::HEADER_LIMIT, $limit['total'])
            ->withHeader(self::HEADER_REMAINING, $limit['remaining'] - 1)
            ->withHeader(self::HEADER_RESET, $limit['reset']);

        if ($limit['remaining']) {
            return $next($req, $res);
        }

        $reset_in = $limit['reset'] - time();
        $data = ['code' => 429, 'msg' => 'Rate limit exceeded, retry in ' . $reset_in];

        return $res->withHeader(self::HEADER_RETRY_AFTER, $reset_in)
            ->withJson($data, 429);
    }

    public function __invoke(Req $req, Res $res, callable $next) {
        return $this->call($req, $res, $next);
    }

    public static function isFirstReplyNull($replies) {
        if (!$replies) {
            return true;
        }

        return !$replies[0];
    }
}
