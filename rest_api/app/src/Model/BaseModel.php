<?php
/**
 * ģ�������
 * 
 * @Author: �ƾ���(Joel Huang) <joelhy@gmail.com>
 * @Date:   2015-12-17 18:47:04
 * @Last Modified by:   Joel Huang
 * @Last Modified time: 2015-12-18 09:13:42
 */

namespace App\Model;

class BaseModel extends \Cncn\Model {
    /**
     * ���ݿ����Ӷ���
     *
     * @var \cncn_db_class
     */
    protected $db;

    public function __construct() {
        // ��ʹ�� parent::__construct();
        $this->db = $GLOBALS['db'];
    }
}
