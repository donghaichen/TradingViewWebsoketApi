<?php
/*
掌握满足单例模式的必要条件
(1)私有的构造方法-为了防止在类外使用new关键字实例化对象
(2)私有的成员属性-为了防止在类外引入这个存放对象的属性
(3)私有的克隆方法-为了防止在类外通过clone成生另一个对象
(4)公有的静态方法-为了让用户进行实例化对象的操作

实例化方式：$db = db::getIntance();

*/
class db
{
    //私有的属性
    private static $dbcon = false;
    private $host;
    private $port;
    private $user;
    private $pass;
    private $db;
    private $charset;
    private $link;
    //私有的构造方法
    /**
     * db::__construct()
     * 
     * @return
     */
    private function __construct()
    {
        $this->host = '127.0.0.1';
        $this->port = '3306';
        $this->user = 'root';
        $this->pass = 'root';
        $this->db = 'exwe';
        $this->charset = 'utf8';
        //连接数据库
        $this->db_connect();
        //选择数据库
        $this->db_usedb();
        //设置字符集
        $this->db_charset();
    }
    //连接数据库
    /**
     * db::db_connect()
     * 
     * @return
     */
    private function db_connect()
    {
        $this->link = mysqli_connect($this->host . ':' . $this->port, $this->user, $this->pass);
        if (!$this->link) {
            echo "数据库连接失败<br>";
            echo "错误编码" . mysqli_errno($this->link) . "<br>";
            echo "错误信息" . mysqli_error($this->link) . "<br>";
            exit;
        }
    }
    //设置字符集
    /**
     * db::db_charset()
     * 
     * @return
     */
    private function db_charset()
    {
        mysqli_query($this->link, "set names {$this->charset}");
    }
    //选择数据库
    /**
     * db::db_usedb()
     * 
     * @return
     */
    private function db_usedb()
    {
        mysqli_query($this->link, "use {$this->db}");
    }
    //私有的克隆
    /**
     * db::__clone()
     * 
     * @return
     */
    private function __clone()
    {
        die('clone is not allowed');
    }
    //公用的静态方法
    /**
     * db::getIntance()
     * 
     * @return
     */
    public static function getIntance()
    {
        if (self::$dbcon == false) {
            self::$dbcon = new self;
        }
        return self::$dbcon;
    }
    public function closedb(){
        mysqli_close(self::$dbcon); 
    }
    //执行sql语句的方法
    /**
     * db::query()
     * 
     * @return
     */
    public function query($sql)
    {
        $res = mysqli_query($this->link, $sql);
        if (!$res) {
            return false;
            /*
            echo "sql语句执行失败<br>";
            echo "错误编码是" . mysqli_errno($this->link) . "<br>";
            echo "错误信息是" . mysqli_error($this->link) . "<br>";
            */
        }
        return $res;
    }
    //获得最后一条记录id
    /**
     * db::getInsertid()
     * 
     * @return
     */
    public function getInsertid()
    {
        return mysqli_insert_id($this->link);
    }
    //获取一行记录,return array 一维数组
    /**
     * db::getOne()
     * 
     * @return
     */
    public function getOne($sql, $type = "assoc")
    {
        $query = $this->query($sql);
        if (!in_array($type, array(
            "assoc",
            'array',
            "row"))) {
            die("mysqli_query error");
        }
        $funcname = "mysqli_fetch_" . $type;
         
        return $funcname($query);
    }
    //获取一条记录,前置条件通过资源获取一条记录
    /**
     * db::getFormSource()
     * 
     * @return
     */
    public function getFormSource($query, $type = "assoc")
    {
        if (!in_array($type, array(
            "assoc",
            "array",
            "row"))) {
            die("mysqli_query error");
        }
        $funcname = "mysqli_fetch_" . $type;
        return $funcname($query);
    }
    //获取多条数据，二维数组
    /**
     * db::getAll()
     * 
     * @return
     */
    public function getAll($sql)
    {
        $query = $this->query($sql);
        $list = array();
        while ($r = $this->getFormSource($query)) {
            $list[] = $r;
        }
        return $list;
    }
    /**
     * 定义添加数据的方法
     * @param string $table 表名
     * @param string orarray $data [数据]
     * @return int 最新添加的id
     */
    /**
     * db::insert()
     * 
     * @return
     */
    public function insert($table, $data)
    {
        //遍历数组，得到每一个字段和字段的值
        $key_str = '';
        $v_str = '';
        foreach ($data as $key => $v) {
            if (empty($v) && $v!=0) {
                die("error");
            }
            //$key的值是每一个字段s一个字段所对应的值
            $key = trim($key, '`');
            $key_str .= '`' . $key . '`,';
            $v_str .= "'$v',";
        }
        $key_str = trim($key_str, ',');
        $v_str = trim($v_str, ',');
        //判断数据是否为空
        $sql = "insert into $table ($key_str) values ($v_str)";
        //return $sql;
        $this->query($sql);
        //返回上一次增加操做产生ID值
        return $this->getInsertid();
    }
    /*
    * 删除数据
    * @param1 $table, $where 表名 条件
    * @return 受影响的行数
    */
    /**
     * db::delete()
     * 
     * @return
     */
    public function delete($table, $where)
    {
        if(empty($where)){
            return false;
        }
        $sql = "delete from $table where $where";
        $this->query($sql);
        //返回受影响的行数
        return mysqli_affected_rows($this->link);
    }
    /**
     * [修改操作description]
     * @param [type] $table [表名]
     * @param [type] $data [数据]
     * @param [type] $where [条件]
     * @return [type]
     */
    /**
     * db::update()
     * 
     * @return
     */
    public function update($table, $data, $where)
    {
        if(empty($where)){
            return false;
        }
        //遍历数组，得到每一个字段和字段的值
        $str = '';
        foreach ($data as $key => $v) {
            $str .= "$key='$v',";
        }
        $str = rtrim($str, ',');
        //修改SQL语句
        $sql = "update $table set $str where $where";
        $rs = $this->query($sql);
        //mysqli_free_result($rs); 
        //返回受影响的行数
        return mysqli_affected_rows($this->link);
    }
}
?>