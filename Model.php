<?php
//1.命名空间 2.为了在不同的命名空间下使用相同的类名方法名
namespace houdunwang\model;
//1.定义一个类model 2.为了一次定义多次调用，使用方便
class Model{
    //1.定义一个静态属性$config 2.因为页面多次调用，定义成属性使用方便
	private static $config;
    //1.定义一个方法__call 2.为了在访问方法不存在的情况下自动执行
	public function __call( $name, $arguments ) {
        //1.静态调用parseAction方法 2.为了连接数据库获得数据
		return self::parseAction($name,$arguments);
	}
    //1.定义一个方法__callStatic 2.为了在访问静态方法不存在的情况下自动执行
	public static function __callStatic( $name, $arguments ) {
        //var_dump($name);这个时候传过来的方法是where
        //1.静态调用parseAction方法 2.为了连接数据库获得数据
		return self::parseAction($name,$arguments);
	}
    //1.定义一个方法parseAction 2.为了实例化base类使用其中的$name方法
	private static function parseAction($name, $arguments ){
		//system\model\Article
		//获得哪个类调用本类
        //1.获得当前使用的类名 2.因为下一步要对当前使用类进行切分
		$table = get_called_class();
        //var_dump($table);
        //1.对$table进行切分 2.是因为我们需要$table要在下面实例化base类中进行传参
		$table = strtolower(ltrim(strrchr($table,'\\'),'\\'));
        //1.执行call_user_func_array方法 实例化类并且将表名，配置项传进去 2.为了实例化base类的时候直接就能用表名，配置项
		return call_user_func_array([new Base(self::$config,$table),$name],$arguments);
	}

    //1.定义一个方法setConfig 2.为了将配置项调进来
	public static function setConfig($config){
        //1.给属性$config赋值 2.为了将它传入到base中，这样一实例化base参数就进入到__construct中了，直接就用了配置项
		self::$config = $config;
	}
}







