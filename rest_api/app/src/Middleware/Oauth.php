<?php
/**
 * Oauth2 验证中间件
 * 
 * @Author: 黄景祥(Joel Huang) <joelhy@gmail.com>
 * @Date:   2015-12-19 15:05:34
 * @Last Modified by:   Joel Huang
 * @Last Modified time: 2016-01-05 18:28:22
 */
namespace App\Middleware;

class Oauth {
    /**
     * DIC 容器
     *
     * @var \Interop\Container\ContainerInterface;
     */
    protected $container = null;

    /**
     * 构造函数
     *
     * @param \Interop\Container\ContainerInterface $container DIC 容器
     */
    public function __construct($container) {
        $this->container = $container;
    }

    public function __invoke(Req $req, Res $res, callable $next) {
        $request_uri = $req->getServerParam('REQUEST_URI');
        if (strpos($request_uri, '/token') !== 0) {  // 获取　token 链接无需验证权限
            $route = $req->getAttribute('route');
            if (!$route) {
                return $next($req, $res);
            }
            $action = ltrim($route->getCallable(), 'App\\Action\\');

            $this->container->get('db');
            $m_o = new \App\Model\Oauth();
            $token = $req->getAccessToken();
            $result = $m_o->valid_token($token, $action, $req);
            if ($result[0] !== 0) {
                return $res->output($result);
            }
        }

        return $next($req, $res);
    }
}
