<?php
namespace Lib;

class Model{  
	  
	private $map = null;
	
	function __construct(){
		$this->map = array(); 

	}

	function set($key,$value){
		$this->map[$key] = $value;  
	}         
	
	function get($key){
		return $this->map[$key];  
	}
	
	function __set($key,$value){
		$this->map[$key] = $value;  
	}

	function __get($key){
		return $this->map[$key];  
	}

	//当调用了该类的不存在的方法时，会自动调用本函数处理而不是爆出error
	function __call($name,$arguments) {
		if(count($arguments) == 1){
			$this->__set(strtolower($name),$arguments[0]);  
		}else{
			return $this->__get(strtolower($name));  
		}  
	}
}  
?>