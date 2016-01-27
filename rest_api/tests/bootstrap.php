<?php
/**
 * 测试初始化程序
 * 
 * @Author: 黄景祥(Joel Huang) <joelhy@gmail.com>
 * @Date:   2015-12-21 09:12:03
 * @Last Modified by:   Joel Huang
 * @Last Modified time: 2016-01-19 17:01:30
 */
header('Content-Type: text/html; charset=utf-8');

require __DIR__ . '/../sdk/src/init.php';

$host = $_SERVER['HTTP_HOST'] ?? '10.0.2.15:1883';
$host = 'http://' . $host;

$client = new RestApi([
    'env'           => 'dev',  // 可选参数，默认值为 prod，正式接口使用 prod，测试接口使用 dev
    'debug_level'   => 1,  // 可选参数，调试级别(开启调试后，会在当前页面输出 debug 信息)
    'version'       => 1,  // 可选参数，API接口版本，默认值为 1
    'client_id'     => 'mobile',  // 用户名
    'client_secret' => 'c20ad4d76fe97759aa27a0c99bff6710',  // 密码的 md5 值
    'urls'          => [
        'dev'   => $host,  // 测试环境地址
        'prod'  => $host,  // 正式环境地址
    ],
]);
