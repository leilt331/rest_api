<?php
/**
 * 初始化程序
 *
 * @Author: 黄景祥(Joel Huang) <joelhy@gmail.com>
 * @Date  :   2015-12-17 09:30:32
 * @Last  Modified by:   Joel Huang
 * @Last  Modified time: 2016-01-05 17:42:55
 */
$starttime = microtime(true);  // cncn_db_class 中用到
define('PATH_CNCN', __DIR__ . '/src/Cncn/');

// 引入公用文件
require_once PATH_CNCN . 'config.php';
require_once PATH_CNCN . 'Encoding.php';

// 定义接口、类的别名，方便使用
class_alias('Psr\Http\Message\ServerRequestInterface', 'App\Action\Req');
class_alias('Psr\Http\Message\ServerRequestInterface', 'App\Middleware\Req');
class_alias('Psr\Http\Message\ResponseInterface', 'App\Action\Res');
class_alias('Psr\Http\Message\ResponseInterface', 'App\Middleware\Res');
