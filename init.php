<?php
date_default_timezone_set('Asia/Shanghai');
ini_set("short_open_tag","On");

define("ROOTDIR",getcwd().DIRECTORY_SEPARATOR);

Lib\Config::init(ROOTDIR."usr".DIRECTORY_SEPARATOR."config.ini");
if(Lib\Config::has('auth')){
	die('init alert : config.ini items are not allow named like "auth", change it to another');
}
Lib\Config::init(ROOTDIR."usr".DIRECTORY_SEPARATOR."auth.ini");

if(Lib\Config::get('debug') == "true"){
	error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
}else{
	error_reporting(0);//display nothing online
}

function trigger()
{
	$_error = error_get_last();
	if ($_error && in_array($_error['type'], array(1, 4, 16, 64, 256, 4096, E_ALL))) 
	{
		if(Lib\Config::get('debug') == "true"){
			//output directly
			echo '<font color=red>Error 500</font></br>';
			echo 'message : ' . $_error['message'] . '</br>';
			echo 'file : ' . $_error['file'] . '</br>';
			echo 'line : ' . $_error['line'] . '</br>';
		}else{
			//log
			$msg = var_export($_error,true);
			Lib\Helper::error($msg);
		}
	}
}

register_shutdown_function('trigger');

?>