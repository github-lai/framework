<?php
namespace Lib;

class Loader
{
	static function init()
	{
		date_default_timezone_set('Asia/Shanghai');
		ini_set("short_open_tag","On");

		if(ENV != "live"){
			error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
		}else{
			error_reporting(0);//display nothing online
		}

		Config::init(ROOTDIR."usr/config.ini");//初始化用户配置文件
		if(Config::has('auth')){
			die('Loader::init alert : config.ini items are not allow named like "auth", change it to another');
		}
		Config::init(ROOTDIR."usr/auth.ini");//初始化认证器配置文件
	}

}

?>