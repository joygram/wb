<?php
/*
2003/11/04 
*/

if(!defined("__wb_spam__")) define("__wb_spam__","1") ;
else return ;

	/*
	���ڿ��� �ϳ� �ϳ� ���ڷ� �и��� ���� �迭�� �����Ͽ� �����Ѵ�.
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
	Ÿ�ӽ������� �ð� ������ ���Ͽ� �迭�� �����Ѵ�.
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


	/* �Է��� �ð��̳��� �ı� �Ǿ����� �˻��ϵ��� �Ѵ�.*/
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
		return 0 ; // �ð��� ������ �ʾ����� ��������.
	}

	/**
	0~9���� ���ڸ� �ߺ����� �ʵ��� ��� �迭�� ��������.
	*/
	function get_uniq_num_list()
	{
		//���� ��� �����ֱ�
		$_debug = 0 ;
		
		//rand()�� ������������ ȣȯ���� ���ؼ�...
		srand(time()) ;
		
			//ó�� ������� ���Դ��� ���ϱ� ���� �迭
		$org_lists = array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9) ;
		$num_lists = array() ;
		$selected = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0) ;
		
		$pos = 0 ;
		while(true)
		{
			$rand_num = rand(0, 9) ;
			
			if($_debug) echo("rand_num : [$rand_num]<br>") ;
			
			//�̹� �����Ͽ����� �ٽ� �����Ѵ�. 
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
		//rand()�� ������������ ȣȯ���� ���ؼ�...
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
