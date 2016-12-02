<?php

class Base_DB {

    static private $links = array();//数据库连接
    static private $linkConfig = array();

    /**
     * 数据库连接资源描述符
     * @var resource
     */
    protected $link = null;

    /**
     * 最后一次执行的查询语句
     * @var string
     */
    protected $sql;

    /**
     * 数据库返回数据集行数
     * @var int
     */
    protected $countNum;

    /**
     * 是否开启调试模式
     * @var bool
     */
    protected $debug = null;

    /**
     * 可忽略的语句执行错误
     * @var array
     */
    protected $ignoreErrorArr = array();

    /**
     * 数据表表名
     * @var string
     */
    protected $tableName;

    /**
     * 翻页实例
     * @var object
     */
    public $pageModel;

    /**
     * 翻页参数名
     * @var string
     */
    public $pname = 'page';


    /**
     * 语句执行时间
     * @var int
     */
    protected $runTime = 0;

    /**
     * 是否在事务中
     * @var bool
     */
    static protected $transaction = false;

    /**
     * 需要重连接的错误代码
     2006 MySQL server has gone away              mysql服务器主动断开
     2013 Lost connection to MySQL server during query  查询时连接中断
     1317 ER_QUERY_INTERRUPTED     查询被打断
     1046 ER_NO_DB_ERROR     无此数据库
     * @var array
     */
    static protected $reConnectErrorArr = array(2006, 1317, 2013, 1046);

    /**
     * @param string $DBName 数据名名称,在config/DBConfig.php中配置
     * @param array $DBConfig 数据库配置，可单独修改主库或从库host、port、user、pass、database、charset
     */
    public function __construct() {
    }

    /**
     * 设置默认缓存的过期时间
     * @param string $pName 分页参数名，默认为page
     * @return void
     */
    public function setPage($pname = 'page') {
        $this->pname = $pname;
    }

    /**
     * 设置表名
     * @param string $tableName 表名
     * @return void
     */
    public function setTableName($tableName) {
        $this->tableName = $tableName;
    }

    /**
     * 获取表名
     * @return string 表名
     */
    public function getTableName() {
        return $this->tableName;
    }

    /**
     * 设置分页样式
     * @return void
     */
    public function setPageStyle($pageStyle) {
        is_object($this->pageModel) ? $this->pageModel->setStyle($pageStyle) : '';
    }


    /**
     * 设置或略报错错误号
     * @return void
     */
    public function setIgnoreErrorArr(array $ignoreErrorArr) {
        $this->ignoreErrorArr = $ignoreErrorArr;
    }

    /**
     * 获取返回结果总数
     * @return void
     */
    public function getCountNum() {
        return $this->countNum;
    }

    /**
     * 获取分页器html片段
     * @return void
     */
    public function getPageStr() {
        return is_object($this->pageModel) ? $this->pageModel->getPageStr() : '';
    }

    /**
     * 过滤数据
     * @return void
     */
    public function escapeString($string, $masterOrSlave = 'slave') {
        $this->checkLink($masterOrSlave);
        if (!$this->link) {
            return $this->_error(90311, "数据库连接失败");
        }
        return $this->link->real_escape_string($string);
    }

    /**
     *
     */
    public function getPageJump() {
        // page类中函数删除，暂时返回空字符串处理
        return '';
        // return is_object($this->pageModel) ? $this->pageModel->getPageJump() : '';
    }

    /**
     * 获取查询出错信息
     */
    public function getErrorInfo() {
        if (!$this->link) {
            return $this->link->error;
        } else {
            return '';
        }
    }

    /**
     * 获取查询出错代号
     */
    public function getErrorCode() {
        if (!$this->link) {
            return $this->link->errno;
        } else {
            return -1;
        }
    }

    /**
     * 执行查询语句
     * @param string @sql 需要执行的查询语句
     * @param array $data 查询语句中以'?'替代的变量值
     * @param int $pageSize 每页结果数
     * @param string $master_or_slave 指定从主库还是从库查询
     * @return array
     */
    public function getData($sql, $data = '', $pageSize = '', $masterOrSlave = 'slave') {
        if (!is_array($data) && !is_numeric($pageSize)) {
            $pageSize = $data;
            $data = '';
        }
        if (is_numeric($pageSize) && $pageSize > 0) {
            //获取读出记录数（用于翻页计算）
            $countSql = "SELECT count(*) AS num " . substr($sql, stripos($sql, "from"));
            $countSql = preg_replace("/\s*ORDER\s*BY.*/i", "", $countSql);
            $query = $this->_sendQuery($countSql, $data, $masterOrSlave);

            if ($query->num_rows == 1) {
                $row = $query->fetch_row();
                $this->countNum = $row[0];
            } else {
                $this->countNum = $query->num_rows;
            }
            $this->debugResult($this->countNum);

            $page = isset($_GET['page']) ? $_GET['page'] : 1;
            $sql .= " LIMIT " . ($page -1) * $pageSize . ', ' . $pageSize;//用于 MySQL 分页生成语句;
        }

        $query = $this->_sendQuery($sql, $data, $masterOrSlave);
        $arr = array();
        if (!is_object($query)) {
            return $this->_error(90301, '数据库返回非资源');
        }
        while ($row = $query->fetch_assoc()) {
            empty($row) || $arr[] = $row;
        }
        $this->debugResult($arr);
        return $arr;
    }

    /**
     * 执行查询语句
     * @param string @sql 需要执行的查询语句,获得单列的一维数组
     * @param array $data 查询语句中以'?'替代的变量值
     * @param string $master_or_slave 指定从主库还是从库查询
     * @return array
     */
    public function getColumn($sql, $data = '', $masterOrSlave = 'slave') {
        $query = $this->_sendQuery($sql, $data, $masterOrSlave);
        if (!is_object($query)) {
            return $this->_error(90301, '数据库返回非资源');
        }
        $arr = array();
        while ($row = $query->fetch_row()) {
            empty($row) || $arr[] = $row[0];
        }
        $this->debugResult($arr);
        return $arr;
    }

    /**
     * 执行SQL 返回一行记录
     * @param string @sql 需要执行的查询语句
     * @param array $data 查询语句中以'?'替代的变量值，默认为空
     * @param string $master_or_slave 指定从主库还是从库查询，默认为从库
     * @return array
     */
    public function getRow($sql, $data = '', $masterOrSlave = 'slave') {
        $query = $this->_sendQuery($sql, $data, $masterOrSlave);
        
        if (!is_object($query)) {
            return $this->_error(90301, '数据库返回非资源');
        }
        $row = $query->fetch_assoc();
        $row = is_null($row) ? array() : $row;
        $this->debugResult($row);
        return $row;
    }

    /**
     * 执行SQL 返回二维数组
     */
    public function getFirst($sql, $data = '', $masterOrSlave = "slave") {
        $query = $this->_sendQuery($sql, $data, $masterOrSlave);
        if (!is_object($query)) {
            return $this->_error(90301, '数据库返回非资源');
        }
        $row = $query->fetch_row();
        $row[0] = is_null($row[0]) ? '' : $row[0];
        $this->debugResult($row[0]);
        return $row[0];
    }

    /**
     * 插入数据
     * @param array $insertArr array('key1' => $value1, 'key2' => $value2)
     * @param string $affix default is '' LOW_PRIORITY|DELAYED|HIGH_PRIORITY|IGNORE
     * @param array &$result default is array()
     * @param string $sqlType default is INSERT INSERT|REPLACE
     * @return bool
     */
    public function insert($insertValue, $affix = '', &$result = array(), $sqlType = 'INSERT') {
        $sqlType = strtoupper($sqlType) !== 'REPLACE' ? 'INSERT' : 'REPLACE';
        if (!is_array($insertValue) || empty($insertValue)) {
            return $this->_error(90302, $sqlType !== 'REPLACE' ? 'insert中insert_value传参错误' : 'replace中replace_value传参错误');
        }
        if (!in_array($affix, array('LOW_PRIORITY', 'DELAYED', 'HIGH_PRIORITY', 'IGNORE'), true)) {
            $affix = '';
        }
        $inKeyArr = $inValArr = array();
        foreach ($insertValue as $key => $value) {
            $inKeyArr[] = ' `' . $key . '` ';
            $inValArr[] = ' ? ';
        }
        if (empty($inKeyArr)) {
            return $this->_error(90302, $sqlType !== 'REPLACE' ? 'insert中insert_value传参错误' : 'replace中replace_value传参错误');
        }
        $sql = "{$sqlType} {$affix} INTO `" . $this->getTableName() . "` (" . implode(',', $inKeyArr) . ") VALUE (" . implode(',', $inValArr) . ")";

        $this->_sendQuery($sql, array_values($insertValue), 'master', $result);

        $this->debugResult($result, 'db_affected_num');
        if (is_int($result['affected_num']) && $result['affected_num'] >= 0) {
            return true;
        }
        return false;
    }


    /**
     * 插入数据唯一键更新
     * @param array $insertArr array('key1' => $value1,'key2' => $value2);
     * @param array $updateKeys('key1','key2');
     * @param string $affix default is '' LOW_PRIORITY|DELAYED|HIGH_PRIORITY|IGNORE
     * @param array &$result default is array()
     * @return bool
     */
    public function insertOnDuplicate($insertValue, $updateKeys, $affix = '', &$result = array()) {
        if (!is_array($insertValue) || empty($insertValue) || !is_array($updateKeys) || empty($updateKeys)) {
            return $this->_error(90302, 'insertOnDuplicate中insert_value传参错误');
        }
        if (!in_array($affix, array('LOW_PRIORITY', 'DELAYED', 'HIGH_PRIORITY', 'IGNORE'), true)) {
            $affix = '';
        }
        $inKeyArr = $inValArr = array();
        foreach ($insertValue as $key => $value) {
            $inKeyArr[] = ' `' . $key . '` ';
            $inValArr[] = ' ? ';
        }
        if (empty($inKeyArr)) {
            return $this->_error(90302, 'insert_on_duplicate中insert_value传参错误');
        }
        $upKeyArr = $upValArr = array();
        foreach ($updateKeys as $key) {
            if (array_key_exists($key, $insertValue)) {
                $upKeyArr[] = ' `' . $key . '` = ?';
                $upValArr[] = $insert_value[$key];
            } else {
                return $this->_error(90303, 'insertOnDuplicate中update_keys参数在insert_value中不存在');
            }
        }
        if (empty($upKeyArr)) {
            return $this->_error(90304, 'insertOnDuplicate传参update无有效字段');
        }
        $sql = "INSERT {$affix} INTO `" . $this->getTableName() . "` (" . implode(',', $inKeyArr) . ") VALUE (" . implode(',', $inValArr) . ") ON DUPLICATE KEY UPDATE " . implode(',', $upKeyArr);
        $this->_sendQuery($sql, array_merge(array_values($insertValue), $upValArr), 'master', $result);
        $this->debugResult($result, 'db_affected_num');
        if (is_int($result['affected_num']) && $result['affected_num'] >= 0) {
            return true;
        }
        return false;
    }

    /**
     * 更新数据
     * @param array * $updateValue('key1' => $value1,'key2' => $value2);
     * @param array||string $where
     * @param array &$result default is array()
     * @return bool
     */
    public function update($updateValue, $where, &$result = array()) {
        if (!is_array($updateValue)) {
            return $this->_error(90305, 'update中update_value传参错误');
        }
        $whereStr = '';
        $whereArr = array();
        if (is_string($where)) {
            $tmpWhere = strtolower($where);
            if (!strpos($tmpWhere, "=") && !strpos($tmpWhere, 'in') && !strpos($tmpWhere, 'like')) {
                return $this->_error(90306, 'update中where条件错误');
            }
            $whereStr = $where;
        } elseif (is_array($where)) {
            $tmp = $whereArr = array();//条件，对应key=value
            foreach ($where as $key => $value) {
                if (is_array($value)) {
                    $tmp[] = "`" . $key . "` in ? ";
                } else {
                    $tmp[] = "`" . $key . "` = ? ";
                }
                $whereArr[] = $value;
            }
            $whereStr = implode(' AND ', $tmp);
        } else {
            return $this->_error(90306, 'update中where条件错误');
        }
        $upArr = array();
        foreach ($updateValue as $key => $value) {
            if ($key{0} === "#") {// 用于特殊操作。有注入漏洞
                $up_arr[] = " `" . substr($key, 1) . "` = {$value} ";
                unset($updateValue[$key]);
            } else {
                $upArr[] = ' `' . $key . '` = ? ';
            }
            
        }
        $sql = "UPDATE `" . $this->getTableName() . "` SET " . implode(',', $upArr) . " WHERE {$whereStr}";
        $this->_sendQuery($sql, array_merge(array_values($updateValue), $whereArr), 'master', $result);
        $this->debugResult($result, 'db_affected_num');
        if (is_int($result['affected_num']) && $result['affected_num'] >= 0) {
            return true;
        }
        return false;
    }

    /**
     * 删除指定的数据
     * @param string||array $where
     * @param array &$result default is array()
     * @return bool
     */
    public function delete($where, &$result = array()) {
        if (is_array($where)) {
            $tmp = $whereArr = array();//条件，对应key=value
            foreach ($where as $key => $value) {
                if (is_array($value)) {
                    $tmp[] = "`" . $key . "` in ? ";
                } else {
                    $tmp[] = "`" . $key . "` = ? ";
                }
                $whereArr[] = $value;
            }
            $whereStr = implode(' AND ', $tmp);
        } else {
            $tmpWhere = strtolower($where);
            if (!strpos($tmpWhere, "=") && !strpos($tmpWhere, 'in') && !strpos($tmpWhere, 'like')) {
                return $this->_error(90307, 'delete中where条件错误');
            }
            $whereStr = $where;
            $whereArr = '';
        }
        $sql = "DELETE FROM `" . $this->getTableName() . "` WHERE {$whereStr}";
        $this->_sendQuery($sql, $whereArr, 'master', $result);
        $this->debugResult($result, 'db_affected_num');
        if (is_int($result['affected_num']) && $result['affected_num'] > 0) {
            return true;
        }
        return false;
    }

    /**
     * 执行给出的SQL语句
     * @param string $sql               sql statement
     * @param array &$result            result data
     * @param string $master_or_slave   master db / slave db
     * @return the number of this->_affected rows
     */
    public function exec($sql, $data = '', &$result = array(), $masterOrSlave = 'master') {
        switch (strtoupper(trim($sql))) {
            case 'START TRANSACTION':
            case 'BEGIN':
                self::$transaction = true;
                break;
            case 'COMMIT':
            case 'ROLLBACK':
                self::$transaction = false;
                break;
        }
        Base_Log::debug('transaction', 0, array(self::$transaction));
        $this->_sendQuery($sql, $data, $masterOrSlave, $result);
        $this->debugResult($result, 'db_affected_num');
        if (is_int($result['affected_num']) && $result['affected_num'] >= 0) {
            return true;
        }
        return false;
    }

    /**
     * 获取插入数据id
     */
    public function insertId() {
        $sql = 'SELECT last_insert_id()';
        return $this->getFirst($sql, '', 'master');
    }

    /**
     * 确保数据库连接
     * @param string $master_or_slave   检查主库还是从库
     * @return void
     */
    protected function checkLink($masterOrSlave = 'slave', $reConnect = false) {
        $timeout = defined('DBCONNECT_TIMEOUT') ? DBCONNECT_TIMEOUT : 1;
        $startTime = microtime(true);
        $this->link = $this->connect($masterOrSlave, $reConnect);
        $runTime = microtime(true) - $startTime;
        if($runTime > $timeout) {
            $this->_error(90314, "m/s[{$masterOrSlave}],runtime[{$runTime}s/{$timeout}s]");
        }
    }

    /**
     * 执行SQL语句
     * @param string $sql 需要执行的语句
     * @param array $data 执行的语句中以'?'替代的变量值
     * @param string $masterOrSlave   主从选择master或者slave
     * @param array &$result            result data
     * @return mixed
     */
    protected function _sendQuery($sql, $data = '', $masterOrSlave = 'slave', &$result = array()) {
        $this->checkLink($masterOrSlave);

        if (!$this->link) {
            return $this->_error(90311, "数据库连接失败");
        }

        $this->setSql($sql, $data, $masterOrSlave);

        if (empty($this->sql)) {
            return $this->_error(90312, "sql不能为空");
        }
        $this->runTime = microtime(true);
        $retry = 0;
        do {
            if ($retry) {
                $this->checkLink($masterOrSlave, true);
                if (!$this->link) {
                    return $this->_error(90311, "数据库连接失败");
                }
            }
            $query = $this->link->query($this->sql);
            if (strtoupper(substr(ltrim($this->sql), 0, 6)) !== "SELECT") {
                $result['affected_num'] = $this->link->affected_rows;
            }
            if (in_array($this->link->errno, self::$reConnectErrorArr, true)) {
                $retry++;
            } elseif ($this->link->errno !== 0) {
                return $this->_error();
            } elseif ($query === false && $this->link->errno === 0) {
                //TODO处理不可能的错误
                $retry++;
            } elseif ($retry) {
                $retry++;
            }
        } while ($retry === 1 && !self::$transaction);
        return $query;
    }

    /**
     * 错误处理
     * @param int $errno 错误号
     * @param string $error 错误信息
     * @param data $data 相关提示数据
     * @return void
     * @author wangxin3
     * error code
     * 90301 数据库返回非资源
     * 90302 insert或replace入库数据错误
     * 90303 insertOnDuplicate中update_keys参数在insert_value中不存在
     * 90304 insertOnDuplicate中update_keys参数无有效字段
     * 90305 update中update_value传参错误
     * 90306 update中where条件错误
     * 90307 delete中where条件错误
     * 90308 字段在配置文件中未定义
     * 90309 字段在配置文件中禁止修改
     * 90310 字段值不符合在配置文件定义的类型
     * 90311 据库连接失败
     * 90312 sql不能为空
     * 90313 传参不符合拼接规范，无法正确翻译sql语句
     * 90314 数据库连接超过指定时间
     * 90320 数据库基类方法不存在
     **/
    protected function _error($errno = 0, $error = '', $data = array()) {
        // mysql错误忽略
        if (!$this->link && in_array($this->link->errno, $this->ignoreErrorArr, true)) {
            Base_Log::debug('db_ignoreErrno_info', $this->link->errno, array($error, $data));
            return false;
        }
        $errno = empty($errno) ? $this->link->errno : $errno;
        $error = empty($error) ? $this->link->error : $error;
        if(in_array($errno, array(90314), true) || defined('QUEUE') || defined('EXTERN')) {
            Base_Log::fatal('db_error', $errno, array($error, $data));
            return false;
        }
        Base_Log::debug('db_error', $errno, array($error, $data));
        return false;
    }

    /**
     * 构造sql语句
     * @param string $sql
     * @param array $data
     * @return void
     */
    protected function setSql($sql, $data = '', $masterOrSlave = 'slave') {
        $this->sql = $sqlShow = '';
        if (strpos($sql, '?') && is_array($data) && count($data) > 0) {
            if (substr_count($sql, '?') != count($data)) {
                return $this->_error(90313, '传参不符合拼接规范，无法正确翻译sql语句! [sql] ' . $sql . ' [data] ' . var_export($data, true));
            }
            $sqlArr = explode('?', $sql);
            $last = array_pop($sqlArr);
            foreach ($sqlArr as $k => $v) {
                if (!empty($v) && isset($data[$k])) {
                    if (!is_array($data[$k])) {
                        $value = "'" . $this->escapeString($data[$k], $masterOrSlave) . "'";
                    } else {
                        $valueArr = array();
                        foreach ($data[$k] as $val) {
                            $valueArr[] = "'" . $this->escapeString($val, $masterOrSlave) . "'";
                        }
                        $value = '(' . implode(', ', $valueArr) . ')';
                    }
                    $sqlShow .= $v . $value;
                } else {
                    return $this->_error(90313, '传参不符合拼接规范，无法正确翻译sql语句! [sql] ' . $sql . ' [data] ' . var_export($data, true));
                }
            }
            $sqlShow .= $last;
        } else {
            $sqlShow = $sql;
        }
        $this->sql = $sqlShow;
        Base_Log::notice('sql statement', 0, $this->sql);
    }

    /**
     * 调试结果
     * @param string $sql
     * @param array $data
     * @return void
     */
    protected function debugResult($result, $type = '') {
        $this->runTime = Base_Common::addStatInfo('db', $this->runTime);
        $arr = empty($type) ? array(array('运行时间', '查询结果'), array($this->runTime, $result)) : array(array('运行时间', '影响条目'), array($this->runTime, $result['affected_num']));
        Base_Log::debug('db_sql_result', 0, $arr);
    }

    public function connect($masterOrSlave, $reConnect){
        
        $config = Base_Config::getConf('db/mysql');
        
        $masterOrSlave === 'master' || $masterOrSlave = 'slave';
        
        if ($masterOrSlave === 'master' && !empty($_SERVER['HTTP_HOST'])
            //防止CSRF
            && isset($_SERVER['REQUEST_METHOD']) && strtoupper($_SERVER['REQUEST_METHOD']) !== 'POST') {
            //Base_Log::warning('Should not operate master db via HTTP GET METHOD, only POST is permited !', 500, array($masterOrSlave, $reConnect));
        }
        
        if ($masterOrSlave) {
            $username = isset($_SERVER['DB_DEFAULT_USER_W']) ? $_SERVER['DB_DEFAULT_USER_W'] : $config[$masterOrSlave]['user'];
            $password = isset($_SERVER['DB_DEFAULT_PASS_W']) ? $_SERVER['DB_DEFAULT_PASS_W'] : $config[$masterOrSlave]['pass'];
            $hostspec = isset($_SERVER['DB_DEFAULT_HOST_W']) ? $_SERVER['DB_DEFAULT_HOST_W'] : $config[$masterOrSlave]['host'];
            $port     = isset($_SERVER['DB_DEFAULT_PORT_W']) ? $_SERVER['DB_DEFAULT_PORT_W'] : $config[$masterOrSlave]['port'];
            $database = isset($_SERVER['DB_DEFAULT_NAME_W']) ? $_SERVER['DB_DEFAULT_NAME_W'] : $config[$masterOrSlave]['dbname'];
        } else {
            $username = isset($_SERVER['DB_DEFAULT_USER_R']) ? $_SERVER['DB_DEFAULT_USER_R'] : $config[$masterOrSlave]['user'];
            $password = isset($_SERVER['DB_DEFAULT_PASS_R']) ? $_SERVER['DB_DEFAULT_PASS_R'] : $config[$masterOrSlave]['pass'];
            $hostspec = isset($_SERVER['DB_DEFAULT_HOST_R']) ? $_SERVER['DB_DEFAULT_HOST_R'] : $config[$masterOrSlave]['host'];
            $port     = isset($_SERVER['DB_DEFAULT_PORT_R']) ? $_SERVER['DB_DEFAULT_PORT_R'] : $config[$masterOrSlave]['port'];
            $database = isset($_SERVER['DB_DEFAULT_NAME_R']) ? $_SERVER['DB_DEFAULT_NAME_R'] : $config[$masterOrSlave]['dbname'];
        }
       
        $charset  = 'utf8';
        $dbKey = md5(implode('-', array($hostspec, $port, $username, $database, $charset)));
        self::$linkConfig = array('host' => $hostspec, 'port' => $port, 'db' => $database, 'charset' => $charset);

        if (isset(self::$links[$dbKey]) && !$reConnect) {
            return self::$links[$dbKey];
        }

        $dsn = "mysql:dbname=$database;port=$port;host=$hostspec";
        $connectType = $reConnect ? 'db_reconnect' : 'db_connect';
        $mysqli = mysqli_init();
        $mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, 4);
        
        if ($mysqli->real_connect($hostspec, $username, $password, $database, $port)) {
            $mysqli->set_charset($charset);
            self::$links[$dbKey] = $mysqli;
            return self::$links[$dbKey];
        } else {
            Base_Log::warning('connect failed !', 500, array($hostspec, $username, $password, $database, $port));
            return false;
        }
    
    }
    
    /**
     * 析构释放内存
     */
    public function __destruct() {
        unset($this->tableAame);
        unset($this->masterOrSlave);
        unset($this->sql);
    }

}
