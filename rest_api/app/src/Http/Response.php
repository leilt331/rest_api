<?php
/**
 * HTTP 响应
 *
 * @Author: 黄景祥(Joel Huang) <joelhy@gmail.com>
 * @Date  :   2015-12-17 14:46:35
 * @Last  Modified by:   Joel Huang
 * @Last  Modified time: 2015-12-30 11:09:04
 */
namespace App\Http;

use Cncn\Encoding as Enc;
use InvalidArgumentException;

class Response extends \Slim\Http\Response {
    /**
     * Add `ETag` header to PSR7 response object
     *
     * @param  string $value The ETag value
     * @param  string $type  ETag type: "strong" or "weak"
     *
     * @return \Psr\Http\Message\ResponseInterface           A new PSR7 response object with `ETag` header
     * @throws \InvalidArgumentException if the etag type is invalid
     */
    public function withEtag($value, $type = 'strong') {
        if (!in_array($type, ['strong', 'weak'])) {
            throw new InvalidArgumentException('Invalid etag type. Must be "strong" or "weak".');
        }
        $value = '"' . $value . '"';
        if ($type === 'weak') {
            $value = 'W/' . $value;
        }

        return $this->withHeader('ETag', $value);
    }

    /**
     * 获取用于 json_encode 的设置项
     *
     * @return int
     */
    protected function json_options() {
        //return ENV === 'prod' ? 0 : JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT;
    }

    /**
     * 获取成功信息的HTTP响应
     *
     * @param  mixed $data        成功响应
     * @param  int   $http_status HTTP status code
     * @return Response
     */
    public function success($data = null, int $http_status = 200) : Response {
        $result =['code'=>1,'msg'=>'ok'];

        if ($data !== null) {
            $result['data'] = Enc::convert($data, 'GBK', 'UTF-8');
           // $res            = $this->withEtag(md5(serialize($result['data'])));
            $res = $this;
        } else {
            $res = $this;
        }

        return $res->withJson($result, $http_status, $this->json_options());
    }

    /**
     * 获取失败信息的HTTP响应
     *
     * @param  int    $status      返回状态
     * @param  string $message     错误信息
     * @param  int    $http_status HTTP status code
     * @return Response
     */
    public function error(int $status, string $message, int $http_status = 200) : Response {
        $result = [
            'code' => $status,
            'msg'  => Enc::convert($message, 'GBK', 'UTF-8'),
        ];

        return $this->withJson($result, $http_status, $this->json_options());
    }

    /**
     * 获取要输出的成功或失败信息的HTTP响应
     *
     * @param  array $result 成功响应
     * @return Response
     */
    public function output(array $result) : Response {
        if ($result[0] === 0) {
            return $this->success($result[1]);
        } else {
            return $this->error($result[0], $result[1]);
        }
    }

    /**
     * 获取 OAuth 的　HTTP　响应
     *
     * @param  array $result 成功响应
     * @return Response
     */
    public function authorize_output(array $result) : Response {
        $result = Enc::convert($result, 'GBK', 'UTF-8');
        if (isset($result['error'])) {
            $http_status = 400;
            $res         = $this;
        } else {
            $http_status = 200;
            $res         = $this->withHeader('Cache-Control', 'no-store')
                ->withHeader('Pragma', 'no-cache');
        }

        return $res->withJson($result, $http_status, $this->json_options());
    }
}
