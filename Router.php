<?php
namespace Lib;

class Router{
	
	//是否ajax请求
	function isAjax()
	{
		$result = false;
		if(isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"])=="xmlhttprequest"){ 
			// ajax 请求的处理方式 
			$result = true;
		}
		return $result;
	}
	
	static function getRequestUri() {
		if (isset($_SERVER['PATH_INFO'])) {
			$requestUri = $_SERVER['PATH_INFO']; 
		}else if(isset($_SERVER['HTTP_X_REWRITE_URL'])) {
			$requestUri = $_SERVER['HTTP_X_REWRITE_URL']; // check this first so IIS will catch 
		} elseif (isset($_SERVER['REDIRECT_URL'])) { 
			$requestUri = $_SERVER['REDIRECT_URL']; // Check if using mod_rewrite 
		} elseif (isset($_SERVER['REQUEST_URI'])) { 
			$requestUri = $_SERVER['REQUEST_URI']; 
		} elseif (isset($_SERVER['ORIG_PATH_INFO'])) {
			$requestUri = $_SERVER['ORIG_PATH_INFO']; // IIS 5.0, PHP as CGI 
			 if (!empty($_SERVER['QUERY_STRING'])) { 
				$requestUri .= '?' . $_SERVER['QUERY_STRING']; 
			 } 
		}
		return $requestUri; 
	}
	
	static function dispatch()
	{
		$path_info = self::getRequestUri();
		$path_info = str_replace(strtolower(Config::get("root")),"",strtolower($path_info));
		
		$key = $path_info.$query_string;

		$_GET["p"] = $path_info;

		$path_info_array = array();
		
		if($path_info != "" && $path_info != null)
		{
			$path_info = trim($path_info,"/");
			//去掉最后的后缀名
			$arr = explode(".",$path_info);
			if(count($arr)>0){
				$path_info_array = explode("/", $arr[0]);
			}else{
				new Error("path_info解析出错(count(arr)=0)");
			}
		}else{
			new Error("path_info解析出错(path_info为空)");
		}
		
		if($path_info_array == null || implode("",$path_info_array) == ""){
			$path_info_array = array("Home","Index");
		}

		$areas = Config::get("area");

		$seg0 = ucfirst($path_info_array[0]);//取出第一个元素area
		$seg1 = ucfirst($path_info_array[1]);//取出第二个元素controller
		$seg2 = ucfirst($path_info_array[2]);//取出第三个元素action
		$seg3 = $path_info_array[3];//取出第四个元素id

		$area =  $seg0;
		if(substr($seg0,0,1) === '@'){
			$area =  ucfirst(substr($seg0,1));
			$controller = $seg1;
			$action =  $seg2;
			$id =  $seg3;
		}else{
			$area =  'H3';//默认
			$controller = $seg0;
			$action =  $seg1;
			$id =  $seg2;
		}
		
		if($controller == ""){
			$controller = "Home";//默认控制器名
		}
		if($action == ""){
			$action = "Index";//默认Action名
		}

		Config::set("uri","$area/$controller/$action");

		$ctrlfile = ROOTDIR.SPACE."/ctrl/".strtolower($area)."/".$controller.".php";//这句话的strtolower是根据linux的调适而来

		if(!file_exists($ctrlfile)){
			new Http(404, "dispatch alert : Controller '$area/$controller' not found");
		}else{
			$classname = "Ctrl\\$area\\".$controller;

			//权限认证机制
			$authcfg = Config::get("auth");
			
			$auths = array();
			foreach($authcfg as $k=>$v)
			{
				if(Config::get("debug") === 'true'){
					$arr = explode('/',strtolower($k));
					if(count($arr) != 3){
						die("路由配置项 $k 不符合要求");
					}
				}
				$level1 = "$area/*/*";
				$level2 = "$area/$controller/*";
				$level3 = "$area/$controller/$action";
				if($level3 == $k){
					array_push($auths, $v);//压栈
				}
				if($level2 == $k){
					array_push($auths, $v);
				}
				if($level1 == $k){
					array_push($auths, $v);
				}
			}
			
			while(count($auths) > 0){
				$auth = array_pop($auths);//出栈
				$auth = "Auth\\".$auth;

				$check = $auth::valid(Config::get("uri"));
				if($check){
					$auth::allow();
				}else{
					$auth::deny();
				}
			}

			//实例化controller
			$ctrl = new $classname;

			$ctrl->setArea($area);
			$ctrl->setController($controller);
			$ctrl->setTpl($action);

			echo $ctrl->$action($id);
		}
	}

}

?>