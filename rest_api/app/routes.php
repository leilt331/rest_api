<?php
/**
 * 路由配置
 * 
 * @Author: 黄景祥(Joel Huang) <joelhy@gmail.com>
 * @Date:   2015-12-16 19:06:07
 * @Last Modified by:   Joel Huang
 * @Last Modified time: 2016-01-20 15:09:49
 */
$app->get('/', 'App\Action\Home:index')->setName('home_page');

// 获取token接口
//$app->post('/token', 'App\Action\Token:authorize');

// // 账号注册相关
// $app->group('/users', function () {
//     $action = 'App\Action\Users';
//     $this->get('/mobile/exists/{mobile:\d+}', "$action:mobile_exists");
//     $this->get('/email/exists/{email}', "$action:email_exists");

//     $this->post('/register', "$action:register");
//     $this->post('/login', "$action:login");
//     $this->get('/logged_in', "$action:logged_in");
//     $this->get('/check_uc_session', "$action:check_uc_session");

//     $this->put('/{uid:\d+}', "$action:edit_info");
//     $this->get('/{uid:\d+}', "$action:get_info");
//     $this->put('/{uid:\d+}/password/reset', "$action:reset_password")->setName('reset_password');
//     $this->put('/{username}/password', "$action:edit_password")->setName('edit_password');
// });

// 景点接口
$app->group('/jingdians', function () {
    $action = 'App\Action\Jingdians';
    $this->get('', "$action:index");
    $this->get('/list', "$action:list");
    $this->get('/{id:\d+}', "$action:detail");
    $this->get('/{id:\d+}/photo_urls', "$action:photo_urls");
});

//爱海沧app
$app->group('/api',function(){
    $action = 'App\Action\Api';
    $this->get('/get_time',"$action:get_time");
    $this->get('/get_homepage',"$action:get_homepage");
    $this->get('/get_pic_list',"$action:get_pic_list");
    $this->get('/get_thumb',"$action:get_thumb");
    $this->get('/app_upgrade',"$action:app_upgrade");

});
