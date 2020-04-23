<?php
namespace Lib;

class Config{
	
	private static $config = null;
	
	static function init($file)
	{
		if(self::$config == null){
			self::$config = parse_ini_file($file,true);//linux正常
		}else{
			$array = parse_ini_file($file,true);
			self::$config['global'] = $array;
			//print_r(self::$config);exit;
		}
	}

	static function get($key)
	{
		if(isset(self::$config[ENV][$key])){
			return self::$config[ENV][$key];
		}else{
			return self::$config['global'][$key];
		}
	}
	
	static function set($key,$val)
	{
		self::$config[ENV][$key] = $val;
	}
	
	static function has($key)
	{
		if(isset(self::$config[ENV][$key])){
			return true;
		}elseif(isset(self::$config['global'][$key])){
			return true;
		}else{
			return false;
		}
	}

}

?>