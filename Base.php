<?php
//1.命名空间 2.为了在不同的命名空间下使用相同的类名方法名
namespace jiaoluo\model;
//1.使用PDO对象 
use PDO;
//1.使用PDOException对象 2.因为不载入找不到会报错
use PDOException;
//1.定义一个类model 2.为了一次定义多次调用，使用方便
class Base{
    //1.定义一个静态属性$pdo 2.因为页面多次调用，定义成属性使用方便
	private static $pdo = NULL;
    //1.定义一个静态属性$table 2.因为页面多次调用，定义成属性使用方便
	private $table;
    //1.定义一个静态属性$where 2.因为页面多次调用，定义成属性使用方便
	private $where = '';

    //1.定义构造方法并传参 2.构造方法中的参数是在调用parseAction方法，实例化base类的时候传参过来的，$config是配置信息，$table是article
	public function __construct($config,$table) {
        //p($config);
        //1.调用connect方法 2.进行连接数据库
		$this->connect($config);
        //1.调用table属性赋值 2.为了方便全局使用，下面还有方法会使用表名
		$this->table = $table;
	}

	/**
	 * 链接数据库
	 *
	 */
    //1.定义一个方法connect 2.为了执行连接数据库操作
	private function connect($config){
		//如果属性$pdo已经链接过数据库了，不需要重复链接了
        //1.三元表达式 2.避免重复连接数据库
		if(!is_null(self::$pdo)) return;
        //1.捕获异常处理 2.是因为要执行的代码放入try中，如果出现异常处理触发catch，
		try{
            //1.组合sql语句，2.这是语法规定
			$dsn = "mysql:host=" . $config['db_host'] . ";dbname=" . $config['db_name'];
            //p($dsn);
            //1.组合sql语句，2.这是语法规定
			$user = $config['db_user'];
            //1.组合sql语句，2.这是语法规定
			$password = $config['db_password'];
            //1.组合sql语句，2.这是语法规定
			$pdo = new PDO($dsn,$user,$password);
			//1.设置错误类型 2.因为默认情况下错误是不弹出的
			$pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
			//1.设置字符集2.为了防止乱码
			$pdo->query("SET NAMES " . $config['db_charset']);
			//1.存到静态属性中2.因为下面q方法还忒发送pdo所以定义成属性
			self::$pdo = $pdo;
        //1.当有异常错误的时候，catch自动捕捉 2.语法规定就是这样
		}catch (PDOException $e){
            //1.输出错误下面代码不执行 2.为了方便开发者改正错误
			exit($e->getMessage());
		}
	}
    //把where条件封装成函数
	public function where($where){
        //1.组合where条件sql语句 2.将传过来的where条件存入到私有属性当中
		$this->where = " WHERE {$where}";

		return $this;
	}

	/**
	 * 获取全部数据封装成函数
	 */
	public function get(){
        //1.组合查询语句的sql语句 2.是为了方便q函数发送
		$sql = "SELECT * FROM {$this->table} {$this->where}";
		return $this->q($sql);
	}
    //将获得主键字段封装成函数 将获得单条数据封装成函数
	public function find($pri){
		//获得主键字段，比如cid还是aid
		//如果是Article::find(4)，那么现在$priField它是aid
		$priField = $this->getPri();
		//经过$this->where方法之后，那么$this->where的值是 WHERE aid=4
        //定义主键的条件
        $this->where("{$priField}={$pri}");
        //1.组合sql查询语句 2.是为了查询单独的指定的某一条数据
        $sql = "SELECT * FROM {$this->table} {$this->where}";
        //echo $sql;
        //获得单独的指定的某一条数据信息
        $data = $this->q($sql);
        //p($data);
		//把原来的二维数组变为一维数组
		$data = current($data);
        //p($data);
        //把单独的一条赋值给属性
		$this->data = $data;
		return $this;
	}

	public function findArray($pri){
        //获得所有的查询的数据信息
		$obj = $this->find($pri);
        //var_dump($obj);
		return $obj->data;
	}


	public function toArray(){
		return $this->data;
	}


	/**
	 * 获得表的主键名称封装成函数，主键是唯一的
	 */
	public function getPri(){
        //1.发送查询表结构的sql语句
		$desc = $this->q("DESC {$this->table}");
		//打印desc看结果调试
		//p($desc);
        //定义一个容器变量$priField并且赋值为空字符串
		$priField = '';
        //对表结构进行遍历
		foreach ($desc as $v){
            //当键值是PRI的时候就是主键
			if($v['Key'] == 'PRI'){
                //把主键赋值给容器变量，输出并且跳出循环，因为主键只有一个
				$priField = $v['Field'];
				break;
			}
		}
		return $priField;
	}
    //将统计字段数量封装成函数
	public function count($field='*'){
        //组合统计字段数量的sql语句
		$sql = "SELECT count({$field}) as c FROM {$this->table} {$this->where}";
        //把sql语句通过q函数发送出去，统计数量
		$data = $this->q($sql);
        //p($data);
		return $data[0]['c'];
	}


	/**
	 * 执行有结果集操作
	 */
    //    将query发送方式封装成q函数
	public function q($sql){
        //1.捕获异常处理 2.是因为要执行的代码放入try中，如果出现异常处理触发catch，
		try{
            //调用pdo对象通过query语句发送sql
			$result = self::$pdo->query($sql);
            //获得关联数组
			$data = $result->fetchAll(PDO::FETCH_ASSOC);
			return $data;
            //当有异常错误的时候，被catch捕捉到
		}catch (PDOException $e){
            //当有异常错误的时候输出错误，
			exit($e->getMessage());
		}

	}

	/**
	 * 执行无结果集操作例如：增删改
	 * 将exec发送方式封装成e函数
	 */
	public function e($sql){
        //1.捕获异常处理 2.是因为要执行的代码放入try中，如果出现异常处理触发catch，
		try{
            //调用pdo对象通过exec语句发送sql
			return self::$pdo->exec($sql);
        //当有异常错误的时候，被catch捕捉到
		}catch (PDOException $e){
            //当有异常错误的时候输出错误，
			exit($e->getMessage());
		}
	}














}