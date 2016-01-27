<?php

/**
 * 数据库操作类
 */
class cncn_db_class extends db_class {
    public function __destruct() {
        //当有管理员cookie，并且非ajax请求时
        if (isset($GLOBALS['debug']) && $GLOBALS['debug'] == 2) {
            if (empty($_GET['inajax'])) {
                echo '<style>.debug_table{width:100%;color:#666}.debug_table td{word-break:break-all;color:#666}</style>';
                if (isset($this->queryinfo)) {
                    echo '<div style="clear:both;border-top:1px dotted #666;color:#666" />以下为技术人员调试用，这句话应该显示在页面底部居中。如果不是，说明可能页面的div有点问题。</div>';
                    echo $this->queryinfo;
                }

                if (isset($this->query_db_num) || isset($this->query_cache_time)) {
                    $str = '<p class="debug_table">';
                    if ($this->query_db_num) {
                        $str .= 'SQL查询' . $this->query_db_num . '次';
                        $str .= ',花了:' . $this->query_db_time . 'ms';
                    }

                    if ($this->query_cache_time) {
                        $str .= ' SQL缓存' . $this->query_cache_num . '次';
                        $str .= ',花了:' . $this->query_cache_time . 'ms';
                    }

                    unset($this->query_db_num, $this->query_cache_time);
                } else {
                    $str = '';
                }

                if (isset($this->queryinfo)) {
                    $str .= ' 内存使用：' . number_format(memory_get_usage() / 1024, 2) . 'K';
                    $runtime = number_format((microtime(1) - $GLOBALS['starttime']) * 1000, 2) . 'ms';
                    $str .= ' 运行时间：' . $runtime;

                    if ($files = get_included_files()) {
                        $str .= '<br>---------------------------------------------------------------------<br>';
                        $str .= $files[0];
                    }
                    $str .= '</p>';
                    echo $str;
                }

                unset($this->queryinfo);
            }
        }

        //if (is_resource($this->link)) mysqli_close($this->link);
    }

    /**
     * 执行 insert 操作
     *
     * @param  string $table 表名
     * @param  array  $data  要操作的数据
     * @return bool 
     */
    public function insert($table, $data) {
        return $this->persist('INSERT', $table, $data);
    }

    /**
     * 执行 insert ignore 操作
     *
     * @param  string $table 表名
     * @param  array  $data  要操作的数据
     * @return bool 
     */
    public function insert_ignore($table, $data) {
        return $this->persist('INSERT IGNORE', $table, $data);   
    }

    /**
     * 执行 replace 操作
     *
     * @param  string $table 表名
     * @param  array  $data  要操作的数据
     * @return bool 
     */
    public function replace($table, $data) {
        return $this->persist('REPLACE', $table, $data);
    }

    /**
     * 执行 insert/insert ignore/replace 操作
     *
     * @param  string $type  操作类型： INSERT, INSERT IGNORE, REPLACE
     * @param  string $table 表名
     * @param  array  $data   要操作的数据
     * @return bool         
     */
    protected function persist($type, $table, $data) {
        if (!$data) {
            return false;
        }

        $fields = array();
        $values = array();
        foreach ($data as $field => $val) {
            $fields[] = $field;
            $values[] = $val;
        }

        $sql = "$type INTO $table (" . implode(', ', $fields) . ") VALUES ('" . implode("', '", $values) . "')";
        return $this->query($sql);
    }


    /**
     * 执行 insert into ... on duplicate update 操作
     *
     * @param  string     $table       表名
     * @param  array      $data        要操作的数据
     * @param  array|null $data_update 重复时要更新的数据，取值 null 时则同 $data
     * @return bool         
     */
    public function insert_duplicate_update($table, $data, $data_update = null) {
        if (!$data) {
            return false;
        }

        $set = $this->implode($data);
        if ($data_update === null) {
            $update_str = $set;
        } else {
            $update_str = $this->implode($data_update);
        }

        $sql = "INSERT INTO $table SET $set ON DUPLICATE KEY UPDATE $update_str";

        return $this->query($sql);
    }

    /**
     * Excute an update query.
     *
     * @param string       $table     the table upon which the query will be performed
     * @param array        $data      an associative array data of key/values
     * @param array|string $condition the "condition" statement
     * @param bool|int     $limit     the limit clause
     * @param bool         $low_priority
     * @return bool
     */
    public function update($table, $data, $condition, $limit = false, $low_priority = false) {
        if (!$data) {
            return false;
        }

        if (!$condition) {
            return false;
        }

        $sql = $this->implode($data);

        if (!$sql) {
            return false;
        }

        $cmd = "UPDATE " . ($low_priority ? 'LOW_PRIORITY' : '');

        // where 查询条件
        if (empty($condition)) {
            $where = '1';
        } elseif (is_array($condition)) {
            $where = $this->build_condition($condition);
        } else {
            $where = $condition;
        }

        $limit = (!$limit) ? '' : ' LIMIT ' . $limit;

        $sql = "$cmd $table SET $sql WHERE $where" . $limit;
        return $this->query($sql);
    }

    /**
     * Compiles a delete string and runs the query.
     *
     * @param string       $table     the table(s) to delete from.
     * @param string|array $condition the condition clause
     * @param bool|int     $limit     the limit clause
     * @return bool
     */
    public function delete($table, $condition = '', $limit = false) {
        if (empty($condition)) {
            $where = '1';
        } elseif (is_array($condition)) {
            $where = $this->build_condition($condition);
        } else {
            $where = $condition;
        }
        $limit = (!$limit) ? '' : ' LIMIT ' . $limit;

        $sql = "DELETE FROM $table WHERE $where" . $limit;
        return $this->query($sql);
    }

    protected function implode($array, $glue = ',') {
        $sql = $comma = '';
        $glue = ' ' . trim($glue) . ' ';
        foreach ($array as $k => $v) {
            $sql .= $comma . $k . "='" . $v . "'";
            $comma = $glue;
        }
        return $sql;
    }

    /**
     * Tests whether the string has an SQL operator
     *
     * @access  private
     * @param   string
     * @return  bool
     */
    protected function _has_operator($str) {
        return preg_match('/(<|>|!|=|\sIS NULL|\sIS NOT NULL|\sEXISTS|\sBETWEEN|\sLIKE|\sIN\s*\(|\s)/i',
            trim($str)) === 1;
    }

    /**
     * 构建查询条件
     *
     * @param array $condition
     * @return string
     */
    protected function build_condition(array $condition) {
        $clause = '';
        $prefix = '';
        foreach ($condition as $k => $v) {
            if (!is_null($v)) {
                if (!$this->_has_operator($k)) {
                    $k .= ' =';
                }
            }
            $clause .= $prefix . $k . " '" . $v . "' ";
            $prefix = 'AND ';
        }

        return $clause;
    }

    /**
     * 返回SQL语句
     *
     * 使用示例:
     * ```php
     * $db->select_string(
     *     '*', 
     *     'member_lostpasswd', 
     *     array('usetime !=' => 0, 'ip' => '3232235777'), 
     *     1, 
     *     array('ORDER' => 'id DESC')
     * );
     * 
     * $db->select_string(
     *     '*, count(*) as cnt', 
     *     'member_lostpasswd', 
     *     array('usetime' => 0), 
     *     1, // 值为 null，则不限制；取整数值，例如 3，则为 LIMIT 3；值为数组，例如 array(10, 5)，则为 LIMIT 10, 5
     *     array(
     *         'GROUP'  => array('ip', 'id_type'),  // 或者 'GROUP' => 'ip, id_type',
     *         'HAVING' => array('cnt >' => 2),  // HAVING 的值格式跟 $condition的条件一样
     *         'ORDER'  => array('usetime' => 'DESC', 'id' => 'DESC'),
     *         // 或者 'ORDER' => 'usetime DESC, id DESC',
     *     )
     * );
     * ```
     *
     * @param string|array   $fields    要取的字段
     * @param string         $table     表名
     * @param string|array   $condition 条件
     * @param null|int|array $limit     LIMIT 条件
     * @param array          $criteria  其他查询条件 (order by, group by, having)
     * @return string
     */
    public function select_string($fields, $table, $condition, $limit = null, $criteria = array()) {
        // 查询的字段
        if (is_string($fields) && $fields ) {
            $arr = explode(',', $fields);
            $fields = $comma = '';
            foreach ($arr as $val) {
                $fields .= $comma . trim($val);
                $comma = ', ';
            }
        } elseif (is_array($fields)) {
            $fields = implode(', ', $fields);
        } else {
            $fields = '*';
        }

        // where 查询条件
        if (empty($condition)) {
            $where = '1';
        } elseif (is_array($condition)) {
            $where = $this->build_condition($condition);
        } else {
            $where = $condition;
        }

        $sql = "SELECT $fields FROM $table WHERE $where";

        // group by 查询条件
        if (isset($criteria['GROUP'])) {
            if (is_array($criteria['GROUP'])) {
                $sql .= ' GROUP BY ' . implode(', ', $criteria['GROUP']);
            } elseif (is_string($criteria['GROUP'])) {
                $sql .= ' GROUP BY ' . $criteria['GROUP'];
            }
        }

        // having 查询条件
        if (isset($criteria['HAVING'])) {
            if (is_array($criteria['HAVING'])) {
                $sql .= ' HAVING ' . $this->build_condition($criteria['HAVING']);
            } elseif (is_string($criteria['HAVING'])) {
                $sql .= ' HAVING ' . $criteria['HAVING'];
            }
        }

        // order by 查询条件
        if (isset($criteria['ORDER'])) {
            if (is_array($criteria['ORDER']) && $criteria['ORDER']) {
                $order_by = array();
                foreach ($criteria['ORDER'] as $field => $direction) {
                    $order_by[] = "$field $direction";
                }
                $sql .= ' ORDER BY ' . implode(', ', $order_by);
            } elseif (is_string($criteria['ORDER'])) {
                $sql .= ' ORDER BY ' . $criteria['ORDER'];
            }
        }

        // limit 查询条件
        if ($limit !== null) {
            if (is_int($limit)) {
                $sql .= ' LIMIT ' . $limit;
            } elseif (is_array($limit) && count($limit) === 2) {
                list($offset, $count) = $limit;
                $sql .= " LIMIT $offset, $count";
            }
        }

        return $sql;
    }

    public function result_array($query) {
        $arrs = array();
        while ($arr = $this->fetch_array($query)) {
            $arrs[] = $arr;
        }
        return $arrs;
    }

    /**
     * 获取第一行数据的第一列
     *
     * 使用示例：
     * <code>
     * $num = $db->get_value("SELECT count(*) FROM users");  // 获取用户数
     * </code>
     *
     * @param string $sql
     * @return mixed
     */
    public function get_value($sql) {
        $row = $this->get_row($sql);
        if ($row) {
            return current($row);
        } else {
            return null;
        }
    }

    /**
     * 获取第一行数据
     *
     * @param  string $sql         SQL语句
     * @param  int    $result_type 结果类型： MYSQLI_ASSOC, MYSQLI_NUM, MYSQLI_BOTH
     * @return array|null              成功返回数组，失败返回 null
     */
    public function get_row($sql, $result_type = MYSQLI_ASSOC) {
        $result = $this->query($sql);
        if (!$result) {
            return null;
        }

        return mysqli_fetch_array($result, $result_type);
    }

    /**
     * 获取所有数据
     *
     * @param  string $sql         SQL语句
     * @param  int    $result_type 结果类型： MYSQLI_ASSOC, MYSQLI_NUM, MYSQLI_BOTH
     * @return array
     */
    public function get_rows($sql, $result_type = MYSQLI_ASSOC) {
        $result = $this->query($sql);
        if (!$result) {
            return array();
        }

        $rows = array();
        while ($row = mysqli_fetch_array($result, $result_type)) {
            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * 获取所有数据
     *
     * @param  string $sql         SQL语句
     * @param  string $key         要作为索引的键值
     * @param  int    $result_type 结果类型： MYSQLI_ASSOC, MYSQLI_NUM, MYSQLI_BOTH
     * @return array
     */
    public function get_key_rows($sql, $key, $result_type = MYSQLI_ASSOC) {
        $result = $this->query($sql);
        if (!$result) {
            return array();
        }

        $rows = array();
        while ($row = mysqli_fetch_array($result, $result_type)) {
            $rows[$row[$key]] = $row;
        }

        return $rows;
    }

    /**
     * 获取一列数据
     *
     * 使用示例：
     * <code>
     * $db->get_column("SELECT id FROM users");
     * $db->get_column("SELECT id FROM users", 0);
     * $db->get_column("SELECT id FROM users", 'id');
     * </code>
     *
     * @param  string     $sql    SQL语句
     * @param  int|string $column 要返回的列键值
     * @return array
     */
    public function get_column($sql, $column = 0) {
        $result_type = is_int($column) ? MYSQLI_NUM : MYSQLI_ASSOC;
        $result = $this->query($sql);
        $columns = array();
        while ($row = mysqli_fetch_array($result, $result_type)) {
            $columns[] = $row[$column];
        }

        return $columns;
    }

    /**
     * 获取键值对数组
     *
     * 使用示例：
     * <code>
     * $db->get_pairs("SELECT id, title FROM article");
     * // 返回 id 值作为数组的键值， title 作为值的数组，例如
     * // array(
     * //     21 => '享受法兰西浓情 法国自助游',
     * //     97 => '大溪地——世界最美的岛屿',
     * //     ...
     * // )
     * </code>
     *
     * @param  string $sql SQL语句
     * @return array
     */
    public function get_pairs($sql) {
        $result = $this->query($sql);
        if (!$result) {
            return array();
        }

        $rows = array();
        while ($row = mysqli_fetch_row($result)) {
            $rows[$row[0]] = $row[1];
        }

        return $rows;
    }
}
