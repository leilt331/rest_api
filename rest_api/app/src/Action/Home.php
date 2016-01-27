<?php
/**
 * @Author: »Æ¾°Ïé(Joel Huang) <joelhy@gmail.com>
 * @Date:   2015-12-16 19:16:51
 * @Last Modified by:   Joel Huang
 * @Last Modified time: 2015-12-19 17:17:51
 */
namespace App\Action;

class Home extends BaseAction {
    public function index(Req $req, Res $res, $args) {
        $body = $res->getBody();
        $body->write('Hello world');

        return $res;
    }
}
