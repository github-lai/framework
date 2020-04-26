<?php
namespace Lib\IBase;

interface ICache {

	//更新，如果不存在则添加
	public function set($key,$val,$seconds);  
	
	//删除
	public function del($key);  

	//读取
	public function get($key);  
	
	//判断
	public function haskey($key);

	//清空所有
	public function clear();

}  

?>