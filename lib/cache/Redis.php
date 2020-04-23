<?php
namespace Lib\Cache;
use Lib;
use Lib\IBase;

//说明(centos)：
//1，关闭selinux
//2，注意防火墙，别档了6379端口
//3，redis.conf注释掉 bind 127.0.0.1（如果需要远程连接。注意：远程连接不安全）
//4，redis.conf设置protected-mode no

class Redis implements IBase\ICache{

	private static $instance = null;
	private static $version = null;

	function __construct(){
		if(self::$instance == null){

			$rediscfg = Lib\Config::get("redis");
			if(class_exists('Redis',false)){
				self::$instance = new \Redis();
				self::$instance->connect($rediscfg["host"], $rediscfg["port"]);
			}else{
				die("Redis not found");
			}
		}

		if(self::$version == null){
			$cc = Lib\Config::get("cache");
			$ver = $cc["version"];
			self::$version = $ver;
		}
	}

	public function getMethods()
	{
		return \get_class_methods(self::$instance);
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
		$result = self::$instance->set($key,$val);
		self::$instance->expire($key ,$seconds);
		return $result;
	}	
	
	//删除
	public function del($key)
	{
		return self::$instance->delete($this->prefix($key)); 
	}

	//判断
	public function haskey($key)
	{
		return self::$instance->exists($this->prefix($key)) == '1' ? true : false;
	}

	//读取
	public function get($key)
	{
		return self::$instance->get($this->prefix($key));
	}

	public function clear()
	{
		self::$instance->flushAll();
	}
	
	//增量计数器
	//说明：如果$key不存在则会自动添加，并设置默认值为0，注意跟memcache区别
	public function increase($key)
	{
		$key = $this->prefix($key);
		return self::$instance->incr($key);
	}

	//增量计数器
	//说明：如果$key不存在则会自动添加，并设置默认值为0，注意跟memcache区别
	public function decrease($key)
	{
		$key = $this->prefix($key);
		return self::$instance->decr($key);
	}
}

