<?php
/*
$Id: database.class.php 2011-5-3 10:24:36 hx $
本地windows使用，改写为文本存储
存的文件太多了，改写为一级目录存储
*/

$GLOBALS['query_mem'] = array('get'=>0,'set'=>0);
$GLOBALS['cache_path'] = SITEDATA.DIRECTORY_SEPARATOR.'local_memcache'.DIRECTORY_SEPARATOR;

/**
* cache file url
*/

function cache_file_url($id,$ismake=0) {
    global $cache_path;
    if(strlen($id)<1) exit('error');
    $t = substr($id,0,1);
    if($ismake) {
        if(!is_dir($cache_path.$t)) {
            mkdir($cache_path.$t, 0755, true);
            chmod($cache_path.$t, 0777);
        }
    }
    return $cache_path.$t.DIRECTORY_SEPARATOR.$id;
}
/**
* Save into cache
*
* @param   string      unique key
* @param   mixed       data to store
* @param   int         length of time (in seconds) the cache is valid
*                      - Default is 0 seconds
* @return  boolean     true on success/false on failure
*/
function mem_set($id,$data,$ttl=0) {

    $contents = array(
    'time'      => time(),
    'ttl'       => $ttl,
    'data'      => $data
    );

    if (file_put_contents(cache_file_url($id,1), serialize($contents))) {
        @chmod(cache_file_url($id), 0777);
        $GLOBALS['query_mem']['set']++;
        return TRUE;
    }

    return FALSE;
}
/**
* Fetch from cache
*
* @param   mixed       unique key id
* @return  mixed       data on success/false on failure
*/
function mem_get($id) {
    if ( ! file_exists(cache_file_url($id)))    {
        return FALSE;
    }
    $data = file_get_contents(cache_file_url($id));
    $data = @unserialize($data);
    $GLOBALS['query_mem']['get']++;

    if ($data['ttl'] > 0 && time() >  $data['time'] + $data['ttl']) {
        unlink(cache_file_url($id));
        return FALSE;
    }

    return $data['data'];
}

/**
* Delete from Cache
*
* @param   mixed       unique identifier of item in cache
* @return  boolean     true on success/false on failure
*/
function mem_delete($id) {
    if(file_exists(cache_file_url($id))) {
        return unlink(cache_file_url($id));
    } else {
        return TRUE;
    }
}

function mem_getMulti($var) {
    if(is_array($var)) {
        $arrs = array();
        foreach($var as $key) {
            if(mem_get($key)!==FALSE) {
                $arrs[$key] = mem_get($key);
            }
        }
        return $arrs;
    } else {
        return mem_get($var);
    }
}

class db_class {
    public $query_db_num = 0;
    public $query_cache_num = 0;
    public $query_db_time = '';
    public $query_cache_time = '';
    public $queryinfo = '';
    private $link = false;
    private $dbhost = '';
    private $dbuser = '';
    private $dbpw   = '';
    private $dbcharset = '';
    public $dbname = '';

    public function __construct($dbhost='',$dbuser='',$dbpw='',$dbname='',$dbcharset=''){
        if(!$dbpw) {
            $this->dbhost = $GLOBALS['dbhost'];
            $this->dbpw   = $GLOBALS['dbpw'];
            $this->dbuser = $GLOBALS['dbuser'];
            $this->dbname = $GLOBALS['dbname'];
            $this->dbcharset= $GLOBALS['dbcharset'];
        } else {
            if (is_array($dbhost)) {
                // 兼容 database-ms 构造函数的数组传参方式，但只取第一个 dbhost
                $dbhost = array_shift($dbhost);
            }
            
            $this->dbhost = $dbhost;
            $this->dbuser = $dbuser;
            $this->dbpw = $dbpw;
            $this->dbname = $dbname;
            $this->dbcharset = $dbcharset;
        }
    }
    public function connect($type = '') {
        if(!$this->link = @mysqli_connect($this->dbhost, $this->dbuser,  $this->dbpw)) {
            $this_error = $this->errno();
            $this->log_msg($type.$this_error);
            if( $this_error == 2003 && !$type) {
                $this->log_msg('重试连接');
                $this->connect('RETRY');
            } else {
                $this->halt('Can not connect to MySQL server');
            }
        } else {
            //mysqli_query("SET NAMES 'gbk'");
            //必须这样设置，不能使用mysqli_set_charset
            mysqli_query($this->link, "SET character_set_connection={$this->dbcharset}, character_set_results={$this->dbcharset}, character_set_client=binary");
            //@mysqli_set_charset($GLOBALS['dbcharset'], $this->link);
            if($this->dbname) {
                mysqli_select_db($this->link, $this->dbname);
            }
        }
    }

    function select_db($dbname) {
        return mysqli_select_db($this->link, $dbname);
    }

    function fetch_array($query, $result_type = MYSQLI_ASSOC) {
        return mysqli_fetch_array($query, $result_type);
    }
    function log_msg($msg) {
        echo $msg;
    }

    function query($sql, $type = '') {
        if (!$this->link) $this->connect();
        $t1 = microtime(1);
        $func = /*$type == 'UNBUFFERED' ? 'mysqli_unbuffered_query' : */'mysqli_query';
        if(!($query = $func($this->link, $sql))) {
            if(in_array($this->errno(), array(2006, 2013)) && substr($type, 0, 5) != 'RETRY') {
                $this->log_msg($sql);
                $this->close();
                $this->connect('RETRY_QUERY');
                return $this->query($sql, 'RETRY'.$type);
            } elseif($type != 'SILENT' && substr($type, 5) != 'SILENT') {
                $this->halt('MySQL Query Error', $sql);
            }

        }

        $t2 = microtime(1);
        $spendtime = number_format(($t2 - $t1)*1000,1);

        //有管理员Cookie，才记录查询次数，查询情况
        if(isset($_COOKIE['admin_auth']) || ENV != 'prod') {
            $this->query_db_time += $spendtime;
            $this->query_db_num++;
            $this->queryinfo .= $spendtime."ms --- {$sql}<br>";
        }
        return $query;
    }


    /*
    DB缓存
    $isupdate 0 默认读取 1 重新读取 2 丢弃
    $cache_key 带_的为key，不带的为兼容以前的写法，$cache_key_md5($sql)
    带_的为key，只获取一条记录。如lxs_10001，则返回$arr。可用$mem->get('lxs_10001')直接获取，也可直接设置$mem->set('lxs_10001',$value);
    不设置为不带_的key，返回数据记录，如company，则返回$arrs。
    */
    function query_cache($sql,$cache_key='',$cache_time='3600',$isupdate='0') {
        $is_cache = 0;
        $t1 = microtime(1);

        if($cache_key){
            if(strpos($cache_key,'_')){
                $mem_sql_key = $cache_key;
            }else{
                $mem_sql_key = $cache_key."_".md5($sql);
            }
        }else{
            $mem_sql_key = md5($sql);
        }
        $arrs = mem_get($mem_sql_key);

        if(!is_array($arrs) || $isupdate){
            if($isupdate){
                unset($arrs);
                if($isupdate == 2){
                    mem_delete($mem_sql_key);
                    return;
                }
            }
            if (!$this->link) $this->connect();
            $arrs = array();
            if($query = mysqli_query($this->link, $sql)) {
                if($mem_sql_key == $cache_key){
                    $arrs = $this->fetch_array($query);
                }else{
                    while($arr = $this->fetch_array($query)){
                        $arrs[] = $arr;
                    }
                }
                /****add by jeffy.woo*****/
            }else{
                $this->halt('MySQL Query Error', $sql);
                /****add by jeffy.woo*****/
            }
            mem_set($mem_sql_key,$arrs,$cache_time);
        }else{
            $is_cache = 1;
        }

        if(isset($_COOKIE['admin_auth']) || ENV != 'prod') {
            $t2 = microtime(1);
            $spendtime = number_format(($t2 - $t1)*1000,1);
            if($is_cache){
                $this->query_cache_num++;
                $this->query_cache_time += $spendtime;
                $this->queryinfo .= $spendtime."ms --- Cache {$sql}<br>";
            }else{
                $this->query_db_num++;
                $this->query_db_time += $spendtime;
                $this->queryinfo .= $spendtime."ms --- {$sql}<br>";
            }
        }

        return $arrs;
    }

    function affected_rows() {
        return mysqli_affected_rows($this->link);
    }

    function error() {
        return (($this->link) ? mysqli_error($this->link) : 0);
    }

    function errno() {
        return intval(($this->link) ? mysqli_errno($this->link) : 0);
    }

    function get_one($sql){
        if (!$this->link) $this->connect();
        $query= $this->query($sql);
        if($query){
            $rs = $this->fetch_row($query);
            return $rs;
        }else{
            return false;
        }
    }

    function result($result, $offset) {
        if (!$this->link) {
            $this->connect();
        }
        $result->data_seek($offset);
        $row = $result->fetch_row();
        return $row[0];
    }

    function num_rows($query) {
        $query = mysqli_num_rows($query);
        return $query;
    }

    function num_fields($query) {
        return mysqli_num_fields($query);
    }

    function free_result($query) {
        return mysqli_free_result($query);
    }

    function insert_id() {
        return ($id = mysqli_insert_id($this->link)) >= 0 ? $id : 
            current(mysqli_fetch_row($this->query("SELECT last_insert_id()")));
    }

    function fetch_row($query) {
        $query = mysqli_fetch_row($query);
        return $query;
    }

    function fetch_fields($query) {
        return mysqli_fetch_field($query);
    }

    function version() {
        return mysqli_get_server_info($this->link);
    }

    function close() {
        if (is_resource($this->link)) {
            return mysqli_close($this->link);
        }
    }

    function halt($message = '', $sql = '') {
        echo "sql error info: " . $message . "<br>\n";
        echo "sql: " . $sql . "<br>\n";
        exit;
    }

    //析构函数
    function __destruct(){
        unset($this->queryinfo);
        //        if ($this->link) mysqli_close($this->link);
    }
}
