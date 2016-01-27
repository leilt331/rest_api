<?php
namespace Cncn;

/**
 * Model - 模型类基类
 *
 * @author 黄景祥(Joel Huang) <joelhy@gmail.com>
 * @since  2015-08-11 14:25:28
 */
class Model {
    /**
     * 表名
     *
     * @var string
     */
    protected static $table;

    /**
     * 数据库操作对象
     *
     * @var \cncn_db_class
     */
    protected $db = null;

    /**
     * 数据库表主键
     *
     * @var string
     */
    protected static $pkey = 'id';

    public function __construct() {
        $this->get_db();
    }

    /**
     * 获取数据库操作对象
     *
     * @return \cncn_db_class
     */
    protected function get_db() {
        if ($this->db !== null) {
            return $this->db;
        }

        if (isset($GLOBALS['__cncn_db'])) {
            $this->db = $GLOBALS['__cncn_db'];
        } elseif (!isset($GLOBALS['db']) || get_class($GLOBALS['db']) !== 'cncn_db_class') {
            require_once HY_ROOT . 'include/cncn_db.php';
            $this->db = $GLOBALS['__cncn_db'] = new \cncn_db_class;
        } else {
            $this->db = $GLOBALS['db'];
        }

        return $this->db;
    }

    /**
     * 处理只提供主键的情况
     *
     * @param  string|array|int $condition 条件
     * @return string|array
     */
    protected function pkey_condition($condition) {
        if (is_numeric($condition)) {  // 主键
            $condition = array(static::$pkey => $condition);
        }

        return $condition;
    }

    /**
     * 获取一行数据
     *
     * @param string|array|int $condition 条件
     * @param string           $fields    要取的字段
     * @param array            $criteria  其他查询条件 (order by, group by, having)
     * @param null|int|array   $limit     LIMIT 条件
     * @return array
     */
    public function find($condition, $fields = '*', $criteria = array(), $limit = null) {
        $sql = $this->db->select_string($fields, static::$table, 
            $this->pkey_condition($condition), $limit, $criteria);
        return $this->db->get_row($sql);
    }

    /**
     * 获取所有数据
     *
     * @param string|array|int $condition 条件
     * @param string           $fields    要取的字段
     * @param array            $criteria  其他查询条件 (order by, group by, having)
     * @param null|int|array   $limit     LIMIT 条件
     * @return array
     */
    public function find_all($condition, $fields = '*', $criteria = array(), $limit = null) {
        $sql = $this->db->select_string($fields, static::$table, 
            $this->pkey_condition($condition), $limit, $criteria);
        return $this->db->get_rows($sql);
    }

    /**
     * 获取一列数据
     *
     * @param string|array|int $condition 条件
     * @param string|int       $column    要返回的列键值
     * @param array            $criteria  其他查询条件 (order by, group by, having)
     * @param null|int|array   $limit     LIMIT 条件
     * @return array
     */
    public function find_column($condition, $column = 0, $criteria = array(), $limit = null) {
        $fields = is_int($column) ? '*' : $column;
        $sql = $this->db->select_string($fields, static::$table, 
            $this->pkey_condition($condition), $limit, $criteria);
        return $this->db->get_column($sql, $column);
    }

    /**
     * 获取第一行数据的第一列
     *
     * @param string           $field     要取的字段
     * @param string|array|int $condition 条件
     * @param null|int|array   $limit     LIMIT 条件
     * @param array            $criteria  其他查询条件 (order by, group by, having)
     * @return mixed
     */
    public function value($field, $condition, $limit = null, $criteria = array()) {
        $sql = $this->db->select_string($field, static::$table, 
            $this->pkey_condition($condition), $limit, $criteria);
        return $this->db->get_value($sql);
    }

    /**
     * 添加数据
     *
     * @param array $data
     * @return int|false 成功返回 ID 主键，失败返回 false
     */
    public function add($data) {
        $result = $this->db->insert(static::$table, $data);
        if (!$result) {
            return false;
        }

        return $this->db->insert_id();
    }

    /**
     * 修改数据
     *
     * @param  string|array|int $condition 条件
     * @param  array            $data
     * @param  bool|int         $limit LIMIT 条件
     * @return bool 成功返回 true，失败返回 false
     */
    public function edit($condition, $data, $limit = false) {
        return $this->db->update(static::$table, $data, 
            $this->pkey_condition($condition), $limit);
    }

    /**
     * 删除数据
     *
     * @param string|array|int $condition 查询条件
     * @param bool|false       $limit     LIMIT 条件
     * @return mixed
     */
    public function remove($condition, $limit = true) {
        return $this->db->delete(static::$table, 
            $this->pkey_condition($condition), $limit);
    }
}
