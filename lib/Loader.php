<?php
namespace Lib;

class Loader
{
	static function init()
	{
		spl_autoload_register('self::Load');//类自动加载机制
		Config::init(ROOTDIR.SPACE."/config.ini");//初始化用户配置文件
		if(Config::has('auth')){
			die('Loader::init alert : config.ini items are not allow named like "auth", change it to another');
		}
		Config::init(ROOTDIR.SPACE."/auth.ini");//初始化认证器配置文件
	}

	static function Load($classname){
		//用命名空间的方法查找
		$classpath = ROOTDIR;
		$arr = explode("\\",$classname);
		if($arr[0] != "Lib"){
			$classpath .= SPACE."/";
		}
		$count = count($arr);
		for($i = 0;$i < $count;$i++){
			if($i == ($count-1)){
				$name = $arr[$i];
				$classpath .= $name.".php";
			}else{
				$name = strtolower($arr[$i]);
				$classpath .= $name."/";
			}
		}

		if(file_exists($classpath)){
			require_once($classpath);
		}else{
			//警告：class_exists的第二个参数默认是true，如果不设置为false，程序会自动调用__autoload查找类，形成死循环（20191103发现该问题）
			if(!class_exists($classpath, false)){
				die("Loader alert : class $classname not found");
			}
		}
	}
}

?>