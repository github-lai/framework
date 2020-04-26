<?php
namespace Lib\Cache;
use Lib;
use Lib\IBase;

class MemCache implements IBase\ICache{
	//研究memcache的过程中经历了被selinux阻止访问的痛苦，
	//原来一直在怀疑memcache，libmemcached，php-memcached的版本不对应导致的，经过一系列的监控和日志的查看等分析调试失败后
	//最终在2019.09.03找到selinux这个罪魁祸首
	private static $instance = null;
	private static $version = null;

	function __construct(){
		if(self::$instance == null){

			$memcfg = Lib\Config::get("memcache");
			if(class_exists('Memcached',false)){
				//这是09年出的php的拓展，一直有人维护，推荐
				self::$instance = new \Memcached;//反斜杠代表根命名空间

				//这里可以做一些基础配置
				//self::$instance->setOption(\Memcached::OPT_REMOVE_FAILED_SERVERS, true);
				//self::$instance->setOption(\Memcached::OPT_CONNECT_TIMEOUT, 10);
				//self::$instance->setOption(\Memcached::OPT_DISTRIBUTION, \Memcached::DISTRIBUTION_CONSISTENT);
				//self::$instance->setOption(\Memcached::OPT_SERVER_FAILURE_LIMIT, 4);//失败的次数超过了这个，该服务器就会被连接池移除
				//self::$instance->setOption(\Memcached::OPT_RETRY_TIMEOUT, 1);
				self::$instance->addServer($memcfg["host"], $memcfg["port"]);
			}else if(class_exists('Memcache',false)){
				//这是04年php出的扩展，好久没有人维护了
				self::$instance = new \Memcache;//反斜杠代表根命名空间
				self::$instance->addServer($memcfg["host"], $memcfg["port"]);
				//之所以注释下面的语句是因为connect只能连接一台服务器和端口，
				//使用addserver可以允许分布式的多台服务器和端口
				//self::$instance->connect($memcfg["host"], $memcfg["port"]) or die("connect失败");
				//$status = self::$instance->getStats();
			}else{
				die("Memcache not found");
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

	/*
	* 存在则设置，不存在则添加
	* @param mixed $key
	* @param mixed $val
	* @param mixed $flag   默认为0不压缩  压缩状态填写：MEMCACHE_COMPRESSED.单项数据比较大的时候启用压缩能提升内存使用效率和性能，数据小则不需要
	* @param mixed $seconds  默认缓存时间(单位秒)
	*/
	public function set($key, $val, $flag=0, $seconds=600)
	{
		$key = $this->prefix($key);
		return self::$instance->set($key, $val, $flag, $seconds);
	}
	
	//删除
	public function del($key)
	{
		return self::$instance->delete($this->prefix($key)); 
	}

	//判断
	public function haskey($key)
	{
		$key = $this->prefix($key);
		$val = $this->get($key); 
		return $val !== false;
	}

	//读取
	public function get($key)
	{
		return self::$instance->get($this->prefix($key));
	}

	public function clear()
	{
		self::$instance->flush();
	}
	
	//增量计数器
	//操作说明：必须确保$key存在才会执行增量操作
	public function increase($key, $step=1)
	{
		$key = $this->prefix($key);
		$val = self::$instance->get($key);
		if($val === false){
			self::$instance->set($key, 0, 0, 0);
		}
		return self::$instance->increment($key, $step);
	}
	
	//增量计数器
	//操作说明：必须确保$key存在才会执行增量操作
	//注意：递减的结果不能小于0，最小值是0，区别于redis可以为负数的情况
	public function decrease($key, $step=1)
	{
		$key = $this->prefix($key);
		$val = self::$instance->get($key);
		if($val === false){
			self::$instance->set($key, 0, 0, 0);
		}
		return self::$instance->decrement($key, $step);
	}
	
}


