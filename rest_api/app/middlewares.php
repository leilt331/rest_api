<?php
/**
 * 中间件
 * 
 * @Author: 黄景祥(Joel Huang) <joelhy@gmail.com>
 * @Date:   2015-12-16 19:05:31
 * @Last Modified by:   Joel Huang
 * @Last Modified time: 2016-01-20 15:09:11
 */
use App\Middleware\{
    Oauth,
    Limiter,
    Logger
};
// e.g: $app->add(new Slim\Csrf\Guard);

//$app->add(new \App\Middleware\Oauth($container));  // Oauth 权限验证

// 调用频率限制 begin -------------------------------
if (0) {
$redis  = $container['redis'];
$router = $container['router'];

$app->add(new Limiter([
    'db'    => $redis,
    'max'   => 2500,
    'key'   => 'all',
]));  // 所有接口的限制：一小时内一个 IP 最多允许调用一个接口 2500 次

$route = $router->getNamedRoute('token');
$route->add(new Limiter([
    'db'    => $redis,
    'max'   => 250,
    'key'   => $route->getName(),
]));  // 获取 token 调用频率限制：一小时内一个 IP 最多获取 250 次 token

// $route = $router->getNamedRoute('reset_password');
// $route->add(new Limiter([
//     'db'    => $redis,
//     'max'   => 250,
//     'key'   => $route->getName(),
// ]));  // 重置密码调用频率限制：一小时内一个 IP 最多允许重置 250 次账号的密码

// $route = $router->getNamedRoute('edit_password');
// $route->add(new Limiter([
//     'db'    => $redis,
//     'max'   => 25,
//     'arg'   => 'username',
//     'key'   => $route->getName(),
// ]));  // 修改密码调用频率限制：一个 IP 一小时内最多只允许修改一个会员 5 次

// $route = $router->getNamedRoute('send_sms');
// $route->add(new Limiter([
//     'db'    => $redis,
//     'max'   => 250,
//     'key'   => $route->getName(),
// ]));  // 修改密码调用频率限制：一个 IP 一小时内最多只允许发送短信 250 次

unset($redis, $router, $route);
// 调用频率限制 end ---------------------------------
}

$app->add(new Logger($container));  // HTTP请求响应日志

$app->add(new \Slim\HttpCache\Cache('public', 86400));  // 缓存控制
