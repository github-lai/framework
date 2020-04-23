<?php
namespace Lib\Cache;
use Lib\IBase;

class NoCache implements IBase\ICache{

	//设置
	public function set($key,$val,$secs)
	{
		return true;
	}
	
	//删除
	public function del($key)
	{
		return true;
	}
	//判断
	public function haskey($key)
	{

	}
	//读取
	public function get($key)
	{
		return false;
	}

	public function clear()
	{
		return true;
	}
}