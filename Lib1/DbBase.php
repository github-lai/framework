<?php
namespace Lib;

class DbBase {

	public $tableName = "";

	private static $instance = null;

	private function one(){
		if($this->instance == null){
			$this->instance = new Db\Mysql();
		}
		return $this->instance;
	}

	public function setName($name){
		$this->tableName = $name;
	}

	public function where($where)
	{
		$this->one()->table($this->tableName);
		return $this->one()->where($where);
	}

	public function wh($field,$act,$value)
	{
		$this->one()->table($this->tableName);
		return $this->one()->wh($field,$act,$value);
	}

	public function kv($arr)
	{
		$this->one()->table($this->tableName);
		return $this->one()->kv($arr);
	}

	public function cout($where)
	{
		$this->one()->table($this->tableName);
		return $this->one()->cout($where);
	}

	public function execute($sql)
	{
		$this->one()->table($this->tableName);
		return $this->one()->execute($sql);
	}

	public function tran($sql)
	{
		$this->one()->table($this->tableName);
		return $this->one()->tran($sql);
	}

	public function desc()
	{
		$this->one()->table($this->tableName);
		$args = func_get_args();
		return $this->one()->desc($args);
	}
	public function asc()
	{
		$this->one()->table($this->tableName);
		$args = func_get_args();
		return $this->one()->asc($args);
	}

}  

?>