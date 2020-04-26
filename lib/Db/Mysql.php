<?php
/*
2019-10-28
Mysql链式调用类
*/

namespace Lib\Db;
use Lib;
use Lib\IBase;

class Mysql {
        
        private $modelName = "Lib\Model";   
        public $lastError = "-_-";
        public $foundRows = 0;

		private $conn = null;
        private $tbname = "";
		private $where = "";
		private $order = null;
		private $limit = "";
		private $pairs = array();//insert/update statement require this var

		function __construct()
		{
			if($this->conn == null){
				$cfg = Lib\Config::get("db");

				$connect = mysqli_connect($cfg["host"],$cfg["user"],$cfg["pass"],$cfg["dbname"]); 
				if(!$connect){
					$msg = mysqli_connect_error();
					Lib\Helper::info($msg);
					die("mysqli_connect failed ：".$msg);
				}
				mysqli_set_charset($connect,"utf8");
				$this->conn = $connect;
			}
		}

        public function kv($arr)
		{
			$this->pairs = $arr;
			return $this;
		}

        public function error($msg)
		{
			Lib\Helper::info($msg);
			$this->result();
			$debug = Lib\Config::get("debug");
			if($debug === "true"){
				die($msg);
			}
		}
        public function result($var)
		{
			$this->where = "";
			$this->order = null;
			$this->limit = "";
			$this->pairs = array();
			return $var;
		}

        public function table($name)
		{
			$this->tbname = $name;
			return $this;
        }

        public function where($where)
		{
			if($this->where != ""){
				die("multi where");
			}

			$this->where = " where $where ";
			
			return $this;
		}

        public function wh($field,$act,$value)
		{
			if($this->where != ""){
				die("multi where");
			}

			if($act == "like"){
				$this->where = " where `$field` like '%".addslashes($value)."%' ";
			}else{
				$this->where = " where `$field` $act '".addslashes($value)."' ";
			}
			
			return $this;
		}

        public function desc()
		{
			if($this->order == null){
				$this->order = array();
			}
		    $num = func_num_args();//参数个数
			for($i = 0; $i < $num; $i++){
				$field = func_get_arg($i);
				if(is_string($field)){
					array_push($this->order, " `$field` desc ");
				}else if(is_array($field)){
					for($j = 0;$j < count($field);$j++){
						$sub = $field[$j];
						array_push($this->order, " `$sub` desc ");
					}
				} 
			}
			return $this;
        }

        public function asc()
		{
			if($this->order == null){
				$this->order = array();
			}
		    $num = func_num_args();//参数个数
			for($i = 0; $i < $num; $i++){
				$field = func_get_arg($i);
				if(is_string($field)){
					array_push($this->order, " `$field` asc ");
				}else if(is_array($field)){
					for($j = 0;$j < count($field);$j++){
						$sub = $field[$j];
						array_push($this->order, " `$sub` asc ");
					}
				} 
			}
			return $this;
        }

        public function limit($start, $end)
		{
			if($this->limit != ""){
				die("multi limit");
			}
			$this->limit = " limit $start,$end ";
			return $this;
        }

        public function remove()
		{
			if($this->where == ""){
				die("required where statement");
			}
			$sql = "delete from ".$this->tbname.$this->where;

			mysqli_query($this->conn,$sql);
			$this->lastError = mysqli_error($this->conn);
			if($this->lastError != ""){
				$this->error($this->lastError);
				return $this->result(false);
			}
			return $this->result(true);
		}

		//select count
        public function rows()
		{
			$sql = "select count(1) as `total` from ".$this->tbname.$this->where;

			//Lib\Helper::info($sql);
			$result = mysqli_query($this->conn,$sql);
			$this->lastError = mysqli_error($this->conn);
			if($this->lastError != ""){
				$this->error($this->lastError);
			}else{
				$count = mysqli_fetch_array($result);
				return $this->result($count[0]);
			}
		}

        public function cout($where)
		{
			if($this->where != ""){
				die("multi where");
			}
			$this->where = " where $where ";
			return $this->rows();
		}

        public function update($bool)
		{
			if(count($this->pairs) == 0){
				die("key-value pairs is required");
			}
			if($this->where == ""){
				die("where statement is required");
			}

			$kv = array();

			foreach($this->pairs as $key=>$val){
				$value = "";
				if(is_string($val)){
					$value = "'".addslashes($val)."'";
				}else if($val === null){//注意：在window下，null和0等价
					$value = "null";
				}else{
					$value = $val;
				}

				$kv[] = "`$key`=$value";
			}
			
			$sql = sprintf("update `%s` set %s %s", $this->tbname, implode(",",$kv), $this->where);
			if($bool === true){
				return $this->result($sql);//方便使用该方法生成sql
			}
			//echo $sql;exit;
			//执行sql语句
			mysqli_query($this->conn,$sql);
			$this->lastError = mysqli_error($this->conn);
			if($this->lastError != ""){
				$this->error($this->lastError."=>".$sql);
				return $this->result(false);
			}

			return $this->result(true);
		}

        public function add($bool)
		{
			if(count($this->pairs)==0){
				die("key-value pairs is required");
			}

            $tag = 0;
            
            if($this->conn != null){  
                
                $format = "insert into `%s`(%s) values(%s)";
				$fields = array();
				$values = array();
                foreach($this->pairs as $key=>$val){
                    $fields[] = "`".$key."`";
					if(is_string($val)){
						$values[] = "'".addslashes($val)."'";
					}else if($val === null){
						$values[] = "null";
					}else{
						$values[] = $val;
					}
                }
                
				$sql = sprintf($format, $this->tbname, implode(",",$fields), implode(",",$values));
				if($bool === true){
					return $this->result($sql);//方便使用该方法生成sql
				}
				//echo $sql;exit;
				mysqli_query($this->conn,$sql);
				$this->lastError = mysqli_error($this->conn);
				if($this->lastError != ""){
					$this->error($this->lastError."=>".$sql);
					return $this->result($this->lastError."=>".$sql);
				}
				//LAST_INSERT_ID是不用查表的，而且只针对当前连接$this->conn，也就是说别的连接的更新不会影响到当前连接的取值
				$tag = mysqli_insert_id($this->conn);
            }
            return $this->result($tag);
		
		}

		//生成执行的select语句，但是并不执行返回
		public function selectl()
		{
			$fields = "*";
		    $arr = func_get_args();//参数个数
			if(count($arr) > 0){
				foreach($arr as &$field)
				{
					$field = "`$field`";
				}
				$fields = implode(",", $arr);
			}
			$order = "";
			if(count($this->order) > 0){
				$order = " order by ".implode(",",$this->order);
			}

			$sql = "select $fields from `".$this->tbname."`".$this->where.$order.$this->limit.";";

			return $this->result($sql);//方便使用该方法生成sql

        }

		//执行查询
        public function select()
		{
			$fields = "*";
		    $arr = func_get_args();//参数个数
			if(count($arr) > 0){
				foreach($arr as &$field)
				{
					$field = "`$field`";
				}
				$fields = implode(",", $arr);
			}
			$order = "";
			if(count($this->order) > 0){
				$order = " order by ".implode(",",$this->order);
			}

			$sql = "select $fields from `".$this->tbname."`".$this->where.$order.$this->limit.";";

			$result = mysqli_query($this->conn,$sql);

			if($this->conn == null){
				$this->error("conn null");
			}
			$this->lastError = mysqli_error($this->conn);
			if($this->lastError != ""){
				$this->error($this->lastError."=>".$sql);
			}

			$rows = array();
			while($result!==false&&($row=mysqli_fetch_object($result))!=null){
				$obj = new $this->modelName();
				foreach($row as $key=>$value){
					$obj->set(strtolower($key),stripcslashes($value));  
					//$obj->{"set".ucfirst($key)}($value);  
				}
				$rows[] = $obj; 
			}
			mysqli_free_result($result);
			
			return $this->result($rows);
        }

		//demo: page("iid","time")
        public function page()
		{
			$fields = "*";
		    $arr = func_get_args();//参数个数
			if(count($arr) > 0){
				foreach($arr as &$field)
				{
					$field = "`$field`";
				}
				$fields = implode(",", $arr);
			}
			$order = "";
			if(count($this->order) > 0){
				$order = "order by ".implode(",",$this->order);
			}

			$sql = "select SQL_CALC_FOUND_ROWS $fields from `".$this->tbname."`".$this->where.$order.$this->limit.";";
			$sql .= "select FOUND_ROWS();";

			//echo $sql;exit;
			$result = array();

			if (mysqli_multi_query($this->conn, $sql)) {
				
				do {
					$result[] = mysqli_store_result($this->conn);//store result set
					if(!mysqli_more_results($this->conn)){
						break;
					}
				} while (mysqli_next_result($this->conn));
			}
			
			$this->lastError = mysqli_error($this->conn);

			if($this->lastError != ""){
				$this->error($this->lastError."=>".$sql);
			}

			$count = mysqli_fetch_array($result[1]);
			$this->foundRows = $count[0];
			mysqli_free_result($result[1]);

			$rows = array();
			while($result[0]!==false&&($row=mysqli_fetch_object($result[0]))!=null){
				$obj = new $this->modelName();
				foreach($row as $key=>$value){
					$obj->set(strtolower($key),stripcslashes($value));  
					//$obj->{"set".ucfirst($key)}($value);  
				}
				$rows[] = $obj; 
			}
			mysqli_free_result($result[0]);
			
			return $this->result(array("foundrows"=>$this->foundRows,"data"=>$rows));
        }
        
		//复杂的sql查询用该方法
        public function query($sql){
			$arr = array();  
            if($this->conn != null){  
				$result = mysqli_query($this->conn,$sql);
				$this->lastError = mysqli_error($this->conn);
				if($this->lastError != ""){
					$this->error($this->lastError);
				}else{
					while($result[0]!==false&&($row=mysqli_fetch_object($result))!=null){
						$obj = new $this->modelName();
						foreach($row as $key=>$value){
							$obj->set(strtolower($key),$value);  
							//$obj->{"set".ucfirst($key)}($value);  
						}
						$arr[] = $obj; 
					}
				}
			}
			return $this->result($arr);
		}
		
        public function execute($sql){
			$tag = false;  
            if($this->conn != null){  
				mysqli_query($this->conn,$sql);
				$this->lastError = mysqli_error($this->conn);
				if($this->lastError != ""){
					$this->error($this->lastError."=>".$sql);
				}else{
                    $tag = true;  
				}
			}
			return $this->result($tag);
		}
        
        public function tran($arr){
			$tag = false;
            if($this->conn != null){
				$errors = 0;
				mysqli_autocommit($this->conn,false);//关闭自动提交功能,表示事务开始
				foreach($arr as $sql){
					mysqli_query($this->conn,$sql);
					$this->lastError = mysqli_error($this->conn);
					if($this->lastError != ""){
						$errors++;
						$this->error($this->lastError."=>".$sql);
					}
				}
				if($errors == 0)
				{
					$tag = mysqli_commit($this->conn);
				}else{
					mysqli_rollback($this->conn);//失败进行回滚到事务开始点
				}
				mysqli_autocommit($this->conn,true);//开启自动提交
			}
			return $this->result($tag);
		}

    }  

?>