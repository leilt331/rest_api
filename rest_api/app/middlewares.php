<?php
/**
 * �м��
 * 
 * @Author: �ƾ���(Joel Huang) <joelhy@gmail.com>
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

//$app->add(new \App\Middleware\Oauth($container));  // Oauth Ȩ����֤

// ����Ƶ������ begin -------------------------------
if (0) {
$redis  = $container['redis'];
$router = $container['router'];

$app->add(new Limiter([
    'db'    => $redis,
    'max'   => 2500,
    'key'   => 'all',
]));  // ���нӿڵ����ƣ�һСʱ��һ�� IP ����������һ���ӿ� 2500 ��

$route = $router->getNamedRoute('token');
$route->add(new Limiter([
    'db'    => $redis,
    'max'   => 250,
    'key'   => $route->getName(),
]));  // ��ȡ token ����Ƶ�����ƣ�һСʱ��һ�� IP ����ȡ 250 �� token

// $route = $router->getNamedRoute('reset_password');
// $route->add(new Limiter([
//     'db'    => $redis,
//     'max'   => 250,
//     'key'   => $route->getName(),
// ]));  // �����������Ƶ�����ƣ�һСʱ��һ�� IP ����������� 250 ���˺ŵ�����

// $route = $router->getNamedRoute('edit_password');
// $route->add(new Limiter([
//     'db'    => $redis,
//     'max'   => 25,
//     'arg'   => 'username',
//     'key'   => $route->getName(),
// ]));  // �޸��������Ƶ�����ƣ�һ�� IP һСʱ�����ֻ�����޸�һ����Ա 5 ��

// $route = $router->getNamedRoute('send_sms');
// $route->add(new Limiter([
//     'db'    => $redis,
//     'max'   => 250,
//     'key'   => $route->getName(),
// ]));  // �޸��������Ƶ�����ƣ�һ�� IP һСʱ�����ֻ�����Ͷ��� 250 ��

unset($redis, $router, $route);
// ����Ƶ������ end ---------------------------------
}

$app->add(new Logger($container));  // HTTP������Ӧ��־

$app->add(new \Slim\HttpCache\Cache('public', 86400));  // �������
