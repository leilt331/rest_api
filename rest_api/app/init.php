<?php
/**
 * ��ʼ������
 *
 * @Author: �ƾ���(Joel Huang) <joelhy@gmail.com>
 * @Date  :   2015-12-17 09:30:32
 * @Last  Modified by:   Joel Huang
 * @Last  Modified time: 2016-01-05 17:42:55
 */
$starttime = microtime(true);  // cncn_db_class ���õ�
define('PATH_CNCN', __DIR__ . '/src/Cncn/');

// ���빫���ļ�
require_once PATH_CNCN . 'config.php';
require_once PATH_CNCN . 'Encoding.php';

// ����ӿڡ���ı���������ʹ��
class_alias('Psr\Http\Message\ServerRequestInterface', 'App\Action\Req');
class_alias('Psr\Http\Message\ServerRequestInterface', 'App\Middleware\Req');
class_alias('Psr\Http\Message\ResponseInterface', 'App\Action\Res');
class_alias('Psr\Http\Message\ResponseInterface', 'App\Middleware\Res');
