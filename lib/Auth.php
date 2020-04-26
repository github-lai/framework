<?php
namespace Lib;

class Auth
{  

	static function reg($name,$array)
	{
		setcookie($name, serialize($array),time()+3600*24*30,'/');
	}

	static function check($name)
	{
		return isset($_COOKIE[$name]);
	}

	static function get($name)
	{
		return unserialize($_COOKIE[$name]);
	}

	static function remove($name)
	{
		setcookie($name,"",time()-1,'/');
	}
	
}  

?>