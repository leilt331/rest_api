<?php
/**
 * 入口程序
 * 
 * @Author: 黄景祥(Joel Huang) <joelhy@gmail.com>
 * @Date:   2015-12-16 18:20:22
 * @Last Modified by:   Joel Huang
 * @Last Modified time: 2016-01-20 14:55:30
 */
require '../vendor/autoload.php';

// 初始化应用
require __DIR__ . '/../app/init.php';

// Prepare app
$settings = require __DIR__ . '/../app/settings.php';
$app = new Slim\App($settings);

// Register dependencies with the DIC
require __DIR__ . '/../app/dependencies.php';

// Register routes
require __DIR__ . '/../app/routes.php';

// Register middleware
require __DIR__ . '/../app/middlewares.php';

// Run app
$app->run();
