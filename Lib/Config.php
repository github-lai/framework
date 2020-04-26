<?php
namespace Lib;

class Config{
	
	private static $config = null;
	public static $env = 'dev';

	static function init($file)
	{
		if(self::$config == null){
			self::$config = parse_ini_file($file,true);//linux正常
		}else{
			$array = parse_ini_file($file,true);
			self::$config['global'] = $array;
		}
		self::$env = self::$config['current']['env'];
	}

	static function get($key)
	{
		if(isset(self::$config[self::$env][$key])){
			return self::$config[self::$env][$key];
		}else{
			return self::$config['global'][$key];
		}
	}
	
	static function set($key,$val)
	{
		self::$config[self::$env][$key] = $val;
	}
	
	static function has($key)
	{
		if(isset(self::$config[self::$env][$key])){
			return true;
		}elseif(isset(self::$config['global'][$key])){
			return true;
		}else{
			return false;
		}
	}

}

?>