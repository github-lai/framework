<?php
namespace Lib;

class Loader
{
	static function init()
	{
		Config::init(ROOTDIR."usr/config.ini");//初始化用户配置文件
		if(Config::has('auth')){
			die('Loader::init alert : config.ini items are not allow named like "auth", change it to another');
		}
		Config::init(ROOTDIR."usr/auth.ini");//初始化认证器配置文件
	}

}

?>