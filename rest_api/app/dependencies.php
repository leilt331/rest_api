<?php
/**
 * DIC容器配置
 * 
 * @Author: 黄景祥(Joel Huang) <joelhy@gmail.com>
 * @Date:   2015-12-16 19:05:20
 * @Last Modified by:   Joel Huang
 * @Last Modified time: 2016-01-20 15:03:51
 */
use Monolog\{
    Logger,
    Processor\WebProcessor,
    Handler\StreamHandler
};

// DIC configuration
$container = $app->getContainer();

// -----------------------------------------------------------------------------
// Service providers
// -----------------------------------------------------------------------------
$container['db'] = function ($c) {
    require_once PATH_CNCN . 'database-i.class.php';
    require_once PATH_CNCN . 'cncn_db-i.php';
    require_once PATH_CNCN . 'Model.php';
    global $dbhost, $dbuser, $dbpw, $dbname, $dbcharset;
    $db = new cncn_db_class($dbhost, $dbuser, $dbpw, $dbname, $dbcharset);
    $GLOBALS['db'] = $db;

    return $db;
};

$container['redis'] = function ($c) {
    $opts = $GLOBALS['redis_cfgs'];
    $opts['prefix'] = 'api:';

    $host       = $opts['host'] ?? '127.0.0.1';
    $port       = $opts['port'] ?? 6379;
    $timeout    = $opts['timeout'] ?? null;
    $prefix     = $opts['prefix'] ?? null;

    $redis = new Redis();
    $redis->connect($host, $port, $timeout);

     // 验证密码
    if (isset($opts['password'])) {
        $redis->auth($opts['password']);
    }

    if ($prefix) {
        $redis->setOption(Redis::OPT_PREFIX, $prefix);
    }

    // 使用指定的数据库
    if (isset($opts['database'])) {
        $redis->select($opts['database']);
    }

    return $redis;
};

$container['cache'] = function () {
    return new \Slim\HttpCache\CacheProvider();
};

$container['response'] = function ($c) {
    $response = new App\Http\Response();
    return $response->withProtocolVersion($c->get('settings')['httpVersion']);
};
$container['request'] = function ($c) {
    return App\Http\Request::createFromEnvironment($c->get('environment'));
};

$container['errorHandler'] = function ($c) {
    return new App\Model\ErrorHandler($c->get('settings')['displayErrorDetails']);
};

$container['notFoundHandler'] = function ($c) {
    return function ($req, $res) {
        return $res->error(404, 'Not Found', 404);
    };
};

$container['notAllowedHandler'] = function ($c) {
    return function ($req, $res) {
        return $res->error(405, 'Method Not Allowed', 405);
    };
};

//签名验证秘钥
$container['key'] = function ($c) {
    return 'ihc&*()_';
};

// -----------------------------------------------------------------------------
// Service factories
// -----------------------------------------------------------------------------
// // monolog
$container['logger'] = function ($c) {
    $log_path = SITEDATA . '/log/api/' . date('Ym');
    if (!is_dir($log_path)) {
        mkdir($log_path, 0755, true);
    }

    $level = ENV === 'prod' ? Logger::ERROR : Logger::DEBUG;

    $logger = new Logger('cncn-api');
    $logger->pushProcessor(new WebProcessor($c['request']->getServerParams()));
    $logger->pushHandler(new StreamHandler($log_path . '/api.log', $level));
    return $logger;
};

// -----------------------------------------------------------------------------
// Action factories
// -----------------------------------------------------------------------------
// $container['App\Action\HomeAction'] = function ($c) {
//     return new App\Action\HomeAction();
// };
