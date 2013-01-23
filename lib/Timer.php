<?php
/**
@author apollo@whitebbs.net
@date 2004/08/24
@note Execute Time Check Class
*/
class Timer
{
	var $names ;
	var $start_times ;
	var $end_times ;
	var $timer_count ;
	var $debug ;

	function Timer()
	{
		$this->debug = 0 ;
		$this->timer_count = 0 ;
	}

	function microtime()
	{ 
		list($usec, $sec) = explode(" ",microtime()); 

		return  round(((float)$usec + (float)$sec), 4) ; 
	} 


	function timeDiff($name)
	{

		$exec_time = round((float)$this->end_times[$name] - (float)$this->start_times[$name], 4);
		
		return $exec_time ;
	}

	function start($name)
	{
		//if(empty($name)) $name = "Timer ".$this->timer_count ; 

		$this->names[$name] = $name ;
		$this->start_times[$name] = $this->microtime() ;
		$this->timer_count++ ;
	}

	function end($name) //반드시 name이 있어야함. 
	{
		$this->end_times[$name] = $this->microtime() ;
		if($this->debug) echo("Timer::end() name[$name] end_times[$name]:".$this->end_times[$name]."<br>") ;
	}

	function report($mode = 1) 
	{
		$tab = "&nbsp; &nbsp; &nbsp; &nbsp;" ;
		echo("<b>Execute Time Reports</b><br>") ;
		reset($this->names) ;
		while( ($name = current($this->names)) )
		{
			if($mode == 1)
				$line = $tab."[$name]::" ;
			else 
				$line = $tab."[$name]:: Start[{$this->start_times[$name]}] End[{$this->end_times[$name]}]" ;

			if(empty($this->end_times[$name]))
				echo("$line end time empty, It may be no check end()<br>\n") ;
			else
				echo("$line Spent Time(".($this->timeDiff($name) ).")<br>\n") ;

			next($this->names) ;
		}
		echo("<br>") ;
	}

}

/**
$timer = new Timer() ;

$timer->start("Timer Test") ;
for($i = 0 ; $i < 10000; $i++ ) ;
$timer->start("New Timer") ;


$timer->end("Timer Test") ;
for($i = 0 ; $i < 10000; $i++ ) ;
$timer->end("New Timer") ;
$timer->report() ;
*/
?>