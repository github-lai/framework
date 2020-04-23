<?php
namespace Lib;
/*
控制器基类
规则：大写字母开头的都是action否则是普通方法
原则：基类不直接处理http请求，仅辅助action处理http请求
*/
class CtrlBase
{
    public $vars = array();
    public $area = "";
    public $controller = "";
    public $tpl = "";

	function __construct(){

	}

	function __call($name,$args) {
		new Http(404,"Action '$name' not found");
	}

	function setArea($area)
	{
		$this->area = $area;
	}

	function setController($controller)
	{
		$this->controller = $controller;
	}

	function setTpl($tpl)
	{
		$this->tpl = $tpl.Config::get("tplext");
	}
	
	function set($key, $val)
	{
		//检查key是否存在
		if(in_array($key, array_keys($this->vars))){
			new Error($key."重复赋值(".$this->vars[$key].")(".$val.")");
		}else{
			$this->vars[$key] = $val;
		}
	}

	function redirect($path) 
	{
		header("location: $path");
		return;
	}

	function action($path) 
	{
		$path = Config::get("root")."/".$path;
		header("location: $path");
		return;
	}

	function tpltmp($area,$tpl)
	{
		$name = strtolower("tpl.tmp.$area.$tpl.php");//缓存文件名
		$file = ROOTDIR."tmp/$name";
		return $file;
	}

	function view($tpl,$layout) 
	{
		if($tpl == "")
		{
			$tpl = "Index";//默认摸板
		}

		$area = $this->area;
		if($area == "")
		{
			$area = "h3";//摸板默认分组
		}

		$file = $this->tpltmp($area, $tpl);
		$setting = Config::get("cache");
		if($setting["tpl"] == "true"){
			if(!file_exists($file)){
				$this->build($area, $tpl, $layout);
			}else{
				if(filemtime($file) <= time()){
					$this->build($area, $tpl, $layout);
				}
			}
		}else{
			$this->build($area, $tpl, $layout);
		}

		include $file;
	}

	function build($area,$tpl,$layout)
	{
		$ext = Config::get("tplext");
		$tplfull = $tpl.$ext;

		//判断view是否存在
		$viewfile = ROOTDIR.SPACE."/tpl/".strtolower($area).'/'.strtolower($this->controller)."/".$tplfull;
		if(!file_exists($viewfile)){
			die("找不到模板文件".$tplfull);
		}

		$content = file_get_contents($viewfile, "r");
				
		if($layout !== false){
			$layoutname = ($layout == null ? "_Layout" : $layout);
			$masterfile = ROOTDIR.SPACE."/tpl/".strtolower($area)."/".$layoutname.$ext;//在linux下大小写敏感，注意了
			$tmp = "";
			if(file_exists($masterfile)){
				$tmp = file_get_contents($masterfile,"r");
				preg_match_all('|<container([^>]*?)/>|U',$tmp, $result, PREG_PATTERN_ORDER);
				$error = array();
				foreach($result[1] as $item)
				{
					$id = str_replace(array('id=','"',' '), array('','',''), $item);
					preg_match("|<container(\s+id=\"\s*?$id\s*?\")>([\w\W]*)</container>|U",$content, $arr);
					if(isset($arr[0])){
						$tmp = str_replace("<container$item/>",$arr[2],$tmp);
						$content = str_replace($arr[0],"",$content);
					}else{
						array_push($error, "模板 $tplfull 缺少容器".htmlentities("<container$item/>"));
					}
				}
				if(count($error) > 0)
				{
					die(implode('<br/>',$error));
				}
			}else{
				//没有母版
				$tmp = $content;
			}

			$final = $tmp;

		}else{
			$final = $content;
		}
		$eg = new Engine;

		$head = "<?php\r\n";
		foreach($this->vars as $key=>$val)
		{
			$head .= '$'."$key = ".'$this->vars'."[\"$key\"];\r\n";
		}
		$head .= "\r\n?>\r\n";

		$final = $eg->compile($final);
		
		$final = $head.$final;

		$file = $this->tpltmp($area, $tpl);
		$bytes = file_put_contents($file, $final, LOCK_EX);
		if($bytes === false){
			die("CtrlBase alert: permission deny!");
		}
		$setting = Config::get("cache");
		$seconds = intval($setting["tplexpire"]);
		@touch($file, time() + $seconds);
	}

}

?>