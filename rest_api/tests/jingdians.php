<?php
/**
 * 景点接口
 * 
 * @Author: 黄景祥(Joel Huang) <joelhy@gmail.com>
 * @Date:   2015-12-21 11:00:44
 * @Last Modified by:   Joel Huang
 * @Last Modified time: 2016-01-05 18:29:13
 */
require_once 'bootstrap.php';
// Jingdians::index
$params = [
    'zone_id'       => 3504,
    'page'          => 2,  // 可选参数
    'per_page'      => 10,  // 可选参数
    'search_key'    => '山',  // 可选参数
];
$client->get('/jingdians', $params);
