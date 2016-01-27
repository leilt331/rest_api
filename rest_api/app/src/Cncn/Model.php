<?php
namespace Cncn;

/**
 * Model - ģ�������
 *
 * @author �ƾ���(Joel Huang) <joelhy@gmail.com>
 * @since  2015-08-11 14:25:28
 */
class Model {
    /**
     * ����
     *
     * @var string
     */
    protected static $table;

    /**
     * ���ݿ��������
     *
     * @var \cncn_db_class
     */
    protected $db = null;

    /**
     * ���ݿ������
     *
     * @var string
     */
    protected static $pkey = 'id';

    public function __construct() {
        $this->get_db();
    }

    /**
     * ��ȡ���ݿ��������
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
     * ����ֻ�ṩ���������
     *
     * @param  string|array|int $condition ����
     * @return string|array
     */
    protected function pkey_condition($condition) {
        if (is_numeric($condition)) {  // ����
            $condition = array(static::$pkey => $condition);
        }

        return $condition;
    }

    /**
     * ��ȡһ������
     *
     * @param string|array|int $condition ����
     * @param string           $fields    Ҫȡ���ֶ�
     * @param array            $criteria  ������ѯ���� (order by, group by, having)
     * @param null|int|array   $limit     LIMIT ����
     * @return array
     */
    public function find($condition, $fields = '*', $criteria = array(), $limit = null) {
        $sql = $this->db->select_string($fields, static::$table, 
            $this->pkey_condition($condition), $limit, $criteria);
        return $this->db->get_row($sql);
    }

    /**
     * ��ȡ��������
     *
     * @param string|array|int $condition ����
     * @param string           $fields    Ҫȡ���ֶ�
     * @param array            $criteria  ������ѯ���� (order by, group by, having)
     * @param null|int|array   $limit     LIMIT ����
     * @return array
     */
    public function find_all($condition, $fields = '*', $criteria = array(), $limit = null) {
        $sql = $this->db->select_string($fields, static::$table, 
            $this->pkey_condition($condition), $limit, $criteria);
        return $this->db->get_rows($sql);
    }

    /**
     * ��ȡһ������
     *
     * @param string|array|int $condition ����
     * @param string|int       $column    Ҫ���ص��м�ֵ
     * @param array            $criteria  ������ѯ���� (order by, group by, having)
     * @param null|int|array   $limit     LIMIT ����
     * @return array
     */
    public function find_column($condition, $column = 0, $criteria = array(), $limit = null) {
        $fields = is_int($column) ? '*' : $column;
        $sql = $this->db->select_string($fields, static::$table, 
            $this->pkey_condition($condition), $limit, $criteria);
        return $this->db->get_column($sql, $column);
    }

    /**
     * ��ȡ��һ�����ݵĵ�һ��
     *
     * @param string           $field     Ҫȡ���ֶ�
     * @param string|array|int $condition ����
     * @param null|int|array   $limit     LIMIT ����
     * @param array            $criteria  ������ѯ���� (order by, group by, having)
     * @return mixed
     */
    public function value($field, $condition, $limit = null, $criteria = array()) {
        $sql = $this->db->select_string($field, static::$table, 
            $this->pkey_condition($condition), $limit, $criteria);
        return $this->db->get_value($sql);
    }

    /**
     * �������
     *
     * @param array $data
     * @return int|false �ɹ����� ID ������ʧ�ܷ��� false
     */
    public function add($data) {
        $result = $this->db->insert(static::$table, $data);
        if (!$result) {
            return false;
        }

        return $this->db->insert_id();
    }

    /**
     * �޸�����
     *
     * @param  string|array|int $condition ����
     * @param  array            $data
     * @param  bool|int         $limit LIMIT ����
     * @return bool �ɹ����� true��ʧ�ܷ��� false
     */
    public function edit($condition, $data, $limit = false) {
        return $this->db->update(static::$table, $data, 
            $this->pkey_condition($condition), $limit);
    }

    /**
     * ɾ������
     *
     * @param string|array|int $condition ��ѯ����
     * @param bool|false       $limit     LIMIT ����
     * @return mixed
     */
    public function remove($condition, $limit = true) {
        return $this->db->delete(static::$table, 
            $this->pkey_condition($condition), $limit);
    }
}
