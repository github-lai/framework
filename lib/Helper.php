<?php
namespace Lib;

class Helper{
	//计时函数
	static function runtime($mode=0){
		static $t;   
		if(!$mode){   
			$t = microtime();
			return;
		}   
		$t1 = microtime();   
		list($m0,$s0) = explode("   ",$t);   
		list($m1,$s1) = explode("   ",$t1);   
		return sprintf("%.3f ms",($s1+$m1-$s0-$m0)*1000);
	}

	static function microtime_float()
	{
		list($usec, $sec) = explode(" ", microtime());
		return ((float)$usec + (float)$sec);
	}
	
	static function error($msg)
	{
		$msg = date("Y-m-d H:i:s",time())."\r\n".$msg."\r\n";
		error_log($msg, 3,  ROOTDIR."/log/error_".date("Ymd",time()).".log");
	}

	static function info($msg)
	{
		$msg = date("Y-m-d H:i:s",time())."\r\n".$msg."\r\n";
		error_log($msg, 3,  ROOTDIR."/log/info_".date("Ymd",time()).".log");
	}

	static function filter($str)
	{
		return htmlentities(addslashes(trim($str)),ENT_NOQUOTES,"utf-8");
	}
	
	static function cubstr($string, $beginIndex, $length){
		if(strlen($string) < $length){
			return substr($string, $beginIndex);
		}
	 
		$char = ord($string[$beginIndex + $length - 1]);
		if($char >= 224 && $char <= 239){
			$str = substr($string, $beginIndex, $length - 1);
			return $str."...";
		}
	 
		$char = ord($string[$beginIndex + $length - 2]);
		if($char >= 224 && $char <= 239){
			$str = substr($string, $beginIndex, $length - 2);
			return $str."...";
		}
	 
		return substr($string, $beginIndex, $length)."...";
	}
}

?>