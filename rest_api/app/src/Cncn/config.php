<?php
    header('Content-Type: text/html; charset=GBK');

	$tablepre = '';
	$dbcharset = 'gbk';

$app_root = realpath(dirname(__DIR__, 3)) . '/';

//if(substr($_SERVER["SERVER_ADDR"],0,9) == '192.168.1') {
    error_reporting(E_ALL);

	$dbhost = '192.168.1.158';			// ���ݿ������
	$dbuser = 'ihaicang';     				// ���ݿ��û���
	$dbpw = 'SDJL2Vy8zjbWFJdc';           		// ���ݿ�����
	$dbname = 'ihaicang';				// ���ݿ���
	$pconnect = 0;

    $redis_cfgs = array(
        'host'      => '127.0.0.1',
        'port'      => 6379,
        'timeout'   => null,
        'prefix'    => null,
    );

    define('ENV', 'dev');
    define('SITEDATA', $app_root . '/../sitedata');
//} else {
    //������
	//error_reporting(E_ALL ^ E_NOTICE);

    //$dbhost = '192.168.1.22';
    //$dbuser = 'cncn';
    //$dbpw = '38u&dI';
    //$dbname = 'new_cncn';

    //$dbhost2 = '192.168.1.22';

    //$memcachehost = '192.168.1.32';
    //$memcacheport = 12121;

    //$mem_servers = array(
        //array($memcachehost, $memcacheport),
        //array('192.168.1.26', '12121')
    //);

    //define('ENV', 'prod');
    //define('SITEDATA', $app_root . '/../sitedata');
//}
