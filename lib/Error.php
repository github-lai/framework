<?php
namespace Lib;

class Error
{
	function __construct($msg){
		$messge = "<span style='color:red; padding:0px; text-align:left;  '>Error：$msg</span>";
		trigger_error($messge,E_USER_ERROR);
	}
}

?>