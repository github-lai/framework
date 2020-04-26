<?php
namespace Lib\Cache;
use Lib;
use Lib\IBase;

class FileCache implements IBase\ICache{

	private static $version = null;

	function __construct(){
		if(self::$version == null){
			$cc = Lib\Config::get("cache");
			$ver = $cc["version"];
			self::$version = $ver;
		}
	}

	public function prefix($key)
	{
		$key = "h3_".self::$version."_".md5($key);
		return $key;
	}

	//设置
	public function set($key,$val,$seconds)
	{
		$key = $this->prefix($key);
		$file = ROOTDIR."tmp/".$key;

		if(file_put_contents($file, $val, LOCK_EX) !== false){
			@touch($file, $seconds + time());
            return true;
		}else{
			return false;
		}

	}
	
	//删除
	public function del($key)
	{
		$key = $this->prefix($key);
		$file = ROOTDIR."tmp/".$key;
		if(unlink($file)){
			return true;
		}else{
			return false;
		}
	}
	//判断
	public function haskey($key)
	{
		$key = $this->prefix($key);
		$file = ROOTDIR."tmp/".$key;
		return file_exists($file);
	}
	//读取
	public function get($key)
	{
		$key = $this->prefix($key);
		$file = ROOTDIR."tmp/".$key;
		if(file_exists($file)){
			if(filemtime($file) > time()){
				return file_get_contents($file);
			}else{
				@unlink($file);
				return false;
			}
		}else{
			return false;
		}
	}

	public function clear()
	{
		Lib\Sys::emptydir(ROOTDIR."tmp/");
	}

}