<?php
/*
2003/11/04 
*/

if(!defined("__wb_spam__")) define("__wb_spam__","1") ;
else return ;

	/*
	문자열을 하나 하나 문자로 분리한 다음 배열에 저장하여 리턴한다.
	*/
	function str2array($str)
	{
		$_debug = 0 ;
		
		for($i = 0 ; $i <= strlen(str); $i++)
		{
			$str_lists[$i] = substr($str, $i, 1) ;
		}
		if($_debug) print_r($str_lists) ;
		return $str_lists ;
	}

	/*
	타임스탬프의 시간 간격을 비교하여 배열로 리턴한다.
	total,
	day,
	hour,
	min,
	sec
	*/
	function diff_time($timestamp)
	{
		$_debug = 0 ;
		$diff = time() - $timestamp ;

		if($_debug)	echo("diff[$diff]<br>") ;

		$diff_lists["total"] = $diff ;

		$diff_lists["day"] = floor($diff/60/60/24) ;
		$diff -= $diff_lists["day"]*60*60*24 ;
		if($_debug)	echo("after day diff[$diff]<br>") ;	

		$diff_lists["hour"] = floor($diff/60/60) ;
		$diff -= $diff_lists["hour"]*60*60 ;
		if($_debug)	echo("after hour diff[$diff]<br>") ;	

		$diff_lists["min"] = floor($diff/60) ;
		$diff -= $diff_lists["min"]*60 ;
		if($_debug)	echo("after min diff[$diff]<br>") ;	

		$diff_lists["sec"] = $diff ;

		if($_debug) {echo("diff_time:") ; print_r($diff_lists) ; echo("<br>") ;}
		return $diff_lists ;
	}


	/* 입력한 시간이내로 파기 되었는지 검사하도록 한다.*/
	function time_expire($timestamp, $day_error_range = 0, $hour_error_range = 2, $min_error_range = 0, $sec_error_range = 0) 
	{
		$_debug = 0 ;
	    $min_sec = 2 ;
		$diff_lists = diff_time($timestamp) ;
		if($diff_lists["total"] < 0)
		{
			if($_debug) echo($diff_lists["total"]." time can't be roll back<br>") ;
			return 1 ;
		}
		$total_error_range = $day_error_range*60*60*24 + $hour_error_range*60*60 + $min_error_range*60 + $sec_error_range ; 

		if( $diff_lists["total"] > $total_error_range ||  $diff_lists["total"] < $min_sec )
		{
			if($_debug) echo("time has been expired $diff_lists[total]{$diff_lists[total]},total_error_range[$total_error_range]{$diff_lists[total]}min_sec[$min_sec]<br>") ;
			return 1 ;
		}
		return 0 ; // 시간이 지나지 않았음을 리턴해줌.
	}

	/**
	0~9까지 숫자를 중복되지 않도록 섞어서 배열로 리턴해줌.
	*/
	function get_uniq_num_list()
	{
		//숫자 섞어서 보여주기
		$_debug = 0 ;
		
		//rand()의 예전버젼과의 호환성을 위해서...
		srand(time()) ;
		
			//처음 순서대로 나왔는지 비교하기 위한 배열
		$org_lists = array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9) ;
		$num_lists = array() ;
		$selected = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0) ;
		
		$pos = 0 ;
		while(true)
		{
			$rand_num = rand(0, 9) ;
			
			if($_debug) echo("rand_num : [$rand_num]<br>") ;
			
			//이미 선택하였으면 다시 선택한다. 
			if($selected[$rand_num] == 1)
			{
				continue ;
			}
			else
			{
				$selected[$rand_num] = 1 ;
			}
			
			$num_lists[$pos] = $rand_num ;
			$pos ++ ;
			
			if($pos >= 10)
			{ 
				break ;
			}
		}
		
		return $num_lists ;
	}

	function get_rand_num($length)
	{
		$_debug = 0 ;
		//rand()의 예전버젼과의 호환성을 위해서...
		srand(time()) ;
		
		for($i = 0; $i < $length; $i++)
		{
			$num = rand(0, 9) ;
			$rand_num .= $num ;
		}
		
		if($_debug) echo("get_rand_num: rand_num [$rand_num]<br>") ;
		return $rand_num ;
	}

?>
