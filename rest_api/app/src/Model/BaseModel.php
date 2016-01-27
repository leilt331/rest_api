<?php
/**
 * 模型类基类
 * 
 * @Author: 黄景祥(Joel Huang) <joelhy@gmail.com>
 * @Date:   2015-12-17 18:47:04
 * @Last Modified by:   Joel Huang
 * @Last Modified time: 2015-12-18 09:13:42
 */

namespace App\Model;

class BaseModel extends \Cncn\Model {
    /**
     * 数据库连接对象
     *
     * @var \cncn_db_class
     */
    protected $db;

    public function __construct() {
        // 不使用 parent::__construct();
        $this->db = $GLOBALS['db'];
    }
}
