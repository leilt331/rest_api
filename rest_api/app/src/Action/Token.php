<?php
/**
 * ��ȡȨ����֤ token
 * 
 * @Author: �ƾ���(Joel Huang) <joelhy@gmail.com>
 * @Date:   2015-12-18 10:32:13
 * @Last Modified by:   Joel Huang
 * @Last Modified time: 2015-12-21 17:44:17
 */
namespace App\Action;

use App\Model\Oauth;

class Token extends BaseAction {
    /**
     * {@inheritdoc}
     */
    public function __construct($container) {
        parent::__construct($container);

        $this->get('db');
    }

    // ��ȡȨ����֤�� token
    public function authorize(Req $req, Res $res, $args) {
        $grant_type = $req->getInput('grant_type');
        $client_id = $req->getServerParam('PHP_AUTH_USER');
        $client_secret = $req->getServerParam('PHP_AUTH_PW');

        $oauth = new Oauth();
        $result = $oauth->get_token($client_id, $client_secret, $grant_type);

        return $res->authorize_output($result);
    }
}
