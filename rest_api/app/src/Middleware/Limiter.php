<?php
/**
 * 调用频率限制
 * 
 * @Author: 黄景祥(Joel Huang) <joelhy@gmail.com>
 * @Date:   2016-01-19 20:10:19
 * @Last Modified by:   Joel Huang
 * @Last Modified time: 2016-01-19 21:47:05
 */
namespace App\Middleware;

class Limiter extends RateLimiter {
    /**
     * 根据 IP、当前小时、对应的路由、arg 参数、key 限制调用频率
     *
     * @param Req $req HTTP 请求对象
     */
    protected function set_id(Req $req) {
        $ip = $req->getServerParam('REMOTE_ADDR');
        $id = $ip . ':' . date('H');
        $route = $req->getAttribute('route');
        if ($route) {
            $id .= ':' . $route->getIdentifier();
            if (!empty($this->opts['arg'])) {
                $id .= ':' . $route->getArgument($this->opts['arg']);
            }
        }
        if (!empty($this->opts['key'])) {
            $id .= ':' . $this->opts['key'];
        }

        $this->id = $id;
    }

    public function __invoke(Req $req, Res $res, callable $next) {
        $this->set_id($req);
        return $this->call($req, $res, $next);
    }
}
