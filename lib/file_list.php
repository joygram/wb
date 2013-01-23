<?php
if(!defined("__wb_file_list__")) define("__wb_file_list__","1") ;
else return ;
/*
WhiteBoard 2.0.0 pre 2001/10
Copyright (c) 2001, WhiteBBs.net, All rights reserved.
Refer copyright.txt
WhiteBoard 1.4.2 : 2001/08/15
WhiteBoard 1.4.0 pre : 2001/08/11
WhiteBoard 1.3.0 : 2001/06/17
WhiteBoard 1.2.3 : 2001/05/10
WhiteBoard 1.1.1 2001/4/11
*/
class file_list 
{
	var $path ;
	var $my_list ;
	var $cnt ;
	var $pos ;
	var $ascending ;
	var $debug ;


	function file_list( $p_path = "./" , $ascending = 0)
	{
		$this->path = $p_path ;	
		$this->cnt = 0 ;
		$this->pos = 0 ;
		$this->ascending = $ascending ;
		$this->debug = 0 ;
	}

	function read( $filter, $head = 1)	
	{
		if($this->debug) echo("this->path[".$this->path."]<br>") ;
		$dir = dir($this->path) ;
		if( $head == 1 )
		{
			$reg_str = "$filter\..*" ;
		}
		else
		{
			$reg_str = ".*\.$filter" ;
		}

		$this->cnt = 0 ;
		unset($this->my_list) ;

		while( $file_name = $dir->read() ) 
		{
			if(ereg($reg_str, $file_name))
			{
				//echo $file_name."<br>\n";
				$this->my_list[$this->cnt] = $file_name ;
				$this->cnt++ ;
			}
		}
		$dir->close();

		if($this->cnt <= 0 )
		{
			return ;
		}

		if($this->ascending)
		{
			sort($this->my_list) ;
		}
		else
		{
			rsort($this->my_list) ;
		}
	}

	function current()
	{
		if($this->pos > sizeof($this->my_list))
		{
			return 0 ;
		}
		$result = $this->my_list[$this->pos] ;
		return $result ;
	}

	function next() 
	{
		if($this->pos > sizeof($this->my_list))
		{
			return 0 ;
		}

		$result = $this->my_list[$this->pos] ;
		$this->pos++ ;	
		return $result ;
	}

	function reset()
	{
		$this->pos = 0 ;
	}
}
?>
