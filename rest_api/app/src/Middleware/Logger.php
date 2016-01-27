<?php
/**
 * 日志中间件
 * 
 * @Author: 黄景祥(Joel Huang) <joelhy@gmail.com>
 * @Date:   2016-01-20 13:36:09
 * @Last Modified by:   Joel Huang
 * @Last Modified time: 2016-01-20 14:13:36
 */
namespace App\Middleware;

class Logger {
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
        $res = $next($req, $res);

        $content = $res->__toString();  // 获取 HTTP 响应
        $content = str_replace(["\r", "\n"], ' ', $content);  // 去掉换行符
        if (ENV !== 'prod') {
            $content = str_replace('  ', '', $content);  // 去掉多余的空格
        }

        $logger = $this->container->get('logger');
        $logger->info($content);

        return $res;
    }
}
