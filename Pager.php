<?php
namespace Lib;

class Pager
{
	public $pagesize = 15;
	public $prev = 1;
	public $next = 1;
	public $total = 0;//记录总数
	public $cur = 0;//当前页数
	public $totalpages = 0;//总页数

	function __construct($cur,$total,$pagesize=15)
	{
		if($pagesize > 0){
			$this->pagesize = $pagesize;
		}
		$this->total = $total;
		if($total <= $this->pagesize){
			$this->totalpages = 1;
		}else{
			$this->totalpages = floor($total / $this->pagesize) + (($total % $this->pagesize)==0 ? 0 : 1);
		}
		
		$this->cur = $cur;
		$this->prev = (($cur-1) > 0 ? ($cur-1) : 1);
		$this->next = (($cur+1) < $this->totalpages ? ($cur+1) : $this->totalpages);
		$this->pagesize = $pagesize;
	}
}

?>