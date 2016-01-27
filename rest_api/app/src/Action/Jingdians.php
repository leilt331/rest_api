<?php
/**
 * @Author: 黄景祥(Joel Huang) <joelhy@gmail.com>
 * @Date:   2015-12-21 10:19:21
 * @Last Modified by:   Joel Huang
 * @Last Modified time: 2016-01-05 10:35:44
 */
namespace App\Action;

class Jingdians extends BaseAction {
    public function __construct($container) {
        parent::__construct($container);

        $this->get('db');
    }

    // 景点列表
    public function index(Req $req, Res $res, $args) {
        $jingdian = new \App\Model\Jingdian();
        $params = $req->getQueryParams();
        $result = $jingdian->get_list($params);

        $jingdian->add([
            'jd_id' => 123,
            'content' => 'xxx',
        ]);

        return $res->output($result);
    }

    // 景点列表(返回景点图片地址)
    public function list(Req $req, Res $res, $args) {
        $jingdian = new \App\Model\Jingdian();
        $params = $req->getQueryParams();

        // 获取权限控制的额外参数
        $extra_args = $req->getExtraArgs();
        if (isset($extra_args[0])) {
            $params['zone_id'] = $extra_args[0];
        }

        $result = $jingdian->get_list($params, true);

        return $res->output($result);
    }

    // 景点详情
    public function detail(Req $req, Res $res, $args) {
        $id = $args['id'] ?? '0';
        
        $m_j = new \App\Model\Jingdian();
        $result = $m_j->detail($id);

        return $res->output($result);
    }

    // 景点图片
    public function photo_urls(Req $req, Res $res, $args) {
        $params = $req->getQueryParams();
        $jd_id = $args['jd_id'] ?? '0';
        $params['jd_id'] = $jd_id;

        $m_p = new \App\Model\Photo();
        $result = $m_p->get_urls($params);

        return $res->output($result);
    }
}
