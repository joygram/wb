<?php
if(!defined("__wb_filter__")) define("__wb_filter__","1") ;
else return ;
/**
	이름 필터
	filter해야하는 경우 true 리턴
	통과해도 좋은 경우 false 리턴
*/
function filter_name($_data, $name, $wb_prg="board")
{
	global $C_base ;
	$_debug = 0 ;
	require_once("$C_base[dir]/lib/config.php") ;
	$read_config = "read_{$wb_prg}_config" ;
	$conf = $read_config($_data) ;
	$C_skin = $conf[skin] ;

	$result = 0 ;
	$conf[filter_name]  = eregi_replace("[[:space:]]+", "", $conf[filter_name]);
   	$filter_array = explode(",", $conf[filter_name]);

	for( $i = 0 ; $i < sizeof($filter_array) ; $i++)
	{
		if(empty($filter_array[$i]))
		{
			continue ;
		}
		if(eregi($filter_array[$i], $name))
		{
			$result = 1; 
			break ;
		}
	}

	return $result ;	

}

/**
	제목 필터
	2003/11/09
	filter해야하는 경우 true 리턴
	통과해도 좋은 경우 false 리턴
*/
function filter_subject($_data, $subject, $wb_prg="board")
{
	global $C_base ;
	$_debug = 0 ;
	require_once("$C_base[dir]/lib/config.php") ;
	$read_config = "read_{$wb_prg}_config" ;
	$conf = $read_config($_data) ;
	$C_skin = $conf[skin] ;

	$result = 0 ;
	$conf[filter_subject]  = eregi_replace("[[:space:]]+", "", $conf[filter_subject]);
   	$filter_array = explode(",", $conf[filter_subject]);

	for( $i = 0 ; $i < sizeof($filter_array) ; $i++)
	{

		if(empty($filter_array[$i]))
		{
			continue ;
		}
		if($_debug) echo("{$filter_array[$i]}, $subject<br>") ;
		if(eregi($filter_array[$i], $subject))
		{
			$result = 1; 
			break ;
		}
	}
	return $result ;	
}

/**
기능설정에 추가된 내용을 검사하여 필터링의 여부를 알려줌
filter해야하는 경우 true 리턴
통과해도 좋은 경우 false 리턴
*/
function filter_txt($_data, $comment, $wb_prg="board") 
{
	$_debug = 0 ;
	global $C_base ;

	require_once("$C_base[dir]/lib/config.php") ;
	$read_config = "read_{$wb_prg}_config" ;
	$conf = $read_config($_data) ;
	$C_skin = $conf[skin] ;
	//기본값으로 세팅해준다. 2002/04/02
	if( !isset($conf[filter_txt_use]))
	{
		$conf[filter_txt_use] = 1 ;
		$conf[filter_txt] = "복사,저주,행운\n돈벌기,홈페이지,메일\n추천,돈벌기\n추천,적립,돈\n광고,장사,돈\n합법,돈,원\n가입,추천,만원\n가입,적립\n대출,신용,증명\n신용,카드,신청,발급\n뉴머니,원,돈\ndonjunda.net\ngoindols.com\nnewmoney.co.kr\nadhappy.co.kr\nadhappy.com\nnetpoints.co.kr\nassaweb.com\ndonnamu.co.kr\ngamebusiness.net\ncash,surfer\nalladvantage\ngetpaid4\nadity,cash\ngoldemail\nmintmail.com\n씨발,새끼\n씨팔,새끼\n미친,지랄" ;
	}
		//사용하지 않는 경우 통과
	if( $conf[filter_txt_use] == 0)
	{
		if($_debug) echo("NO FILTER USE<br>") ;
		return false ;
	}

	$comment = str_replace(" ","", $comment) ;
	$comment = str_replace("\n","", $comment) ;

		//문자열을 \n로 끊어서 배열로저장.
	$filter = false ;
	$conf[filter_txt] = str_replace(" ","", $conf[filter_txt]) ;
	$filter_list = explode("\n", $conf[filter_txt]) ;
	for($i = 0 ; $i < sizeof($filter_list) ; $i++ )
	{
		if(empty($filter_list[$i])) continue ;	
		if($_debug) echo("filter_txt: [$i]<br>") ;
		$filter_list[$i] = chop($filter_list[$i]) ;
			//정규식 표현 막기 2002/05/05
		$filter_list[$i] = str_replace("[","\[", $filter_list[$i]) ;
		$filter_list[$i] = str_replace("]","\]", $filter_list[$i]) ;
		$filter_list[$i] = str_replace("|","\|", $filter_list[$i]) ;
		$filter_list[$i] = str_replace("*","\*", $filter_list[$i]) ;

		if($_debug) echo("filter_txt: [$filter_list[$i]]<br>") ;

		if(empty($filter_list[$i]))
		{
			continue ;
		}

		$word_array = explode(",", $filter_list[$i]) ;

		$filter = false ;
		$total_match = sizeof($word_array) ;
		$match = 0 ;

/*		for($j = 0 ; $j < sizeof($word_array); $j++)
		{
			if(empty($word_array[$j]))
			{
				if($_debug) echo("empty $j th word_array, total match minus<br>") ;
				$total_match--;
				continue ;
			}

				// strpos()는 못찾았을 경우에만 boolean값 false를 리턴
			//$ok = strpos($comment, $word_array[$j])  ;
			if($_debug) echo("word_array[$j]:[$word_array[$j]]<br>") ;
			if( eregi($word_array[$j], $comment ) )
			{
				if($_debug) echo("match $word_array[$j]<br>") ;
				$match++ ;
			}
			else // 못 찾으면
			{
				break ;
			}
		}

		if($_debug) echo("[$filter_list[$i]][$match][$total_match]<br>") ;
		if($match >= $total_match && $match > 0)
		{
			$filter = true ;
			break ;
		}
*/
		//금지 단어 리스트의 처음 단어만 검색해서 comment 에 들어 있는 경우에만 남어지 단어들이 들어 있는지를 확인해서, 단어가 모두 들어 있으면 $filter 값으로 true 를 넘기고 반복문을 시킴
		// 경고가 떨어짐 체크필요...
		
		// preg_match() 를 strpos() 로 바꿈 2004.1.18
		//수정이유 -  Tip: Do not use preg_match() if you only want to check if one string is contained in another string. Use strpos() or strstr() instead as they will be faster.  from http://php.net		
		
		$pos = strpos($comment,"$word_array[0]");
		if (!($pos === false))		
		{
			$match=1;	
			$filtered_word = $word_array[0];
			for($j =1 ; $j < sizeof($word_array); $j++)
				{
					if(!(strpos($comment,"$word_array[$j]")===false))
					{
						$match++;
						if($_debug) echo("$word_array[$j]<br>");						
						if($_debug) echo("$match<br>");
					}
					else
					{
						break;
					}
				}
			if($match == sizeof($word_array))
			{				
				$filter = implode("," ,$word_array);				
				break;
			}
		}		
		// 여기까지 수정
	}
	return $filter;  // 필터링된 단어 셋을 리턴시킴 --> write.php 에서도 수정 
}

/**
기능설정에 추가된 내용을 검사하여 필터링의 여부를 알려줌
*/
function filter_ip($_data, $remote_ip, $wb_prg="board") 
{
	$_debug = 0 ;
	global $C_base ;
	require_once("$C_base[dir]/lib/config.php") ;
	$read_config = "read_{$wb_prg}_config" ;
	$conf = $read_config($_data) ;
	$C_skin = $conf[skin] ;
	//기본값으로 세팅해준다. 2002/04/02
	if( !isset($conf[filter_ip_use]))
	{
		$conf[filter_ip_use] = 1 ;
	}
		//사용하지 않는 경우 통과
	if( $conf[filter_ip_use] == 0)
	{
		if($_debug) echo("NO FILTER USE<br>") ;
		return false ;
	}

	$remote_ip   = str_replace(" ","", $remote_ip) ;
	$conf[filter_ip] = str_replace(" ","", $conf[filter_ip]) ;

		//문자열을 \n로 끊어서 배열로저장.
	$filter = false ;
	$filter_list = explode("\n", $conf[filter_ip]) ;

	$filter = false ;
	for($i = 0 ; $i < sizeof($filter_list) ; $i++ )
	{
		if($_debug) echo("filter_ip: [$i]<br>") ;
		$filter_list[$i] = chop($filter_list[$i]) ;
			//정규식 표현 막기 2002/05/05
		$filter_list[$i] = str_replace("*", "", $filter_list[$i]) ;
		$filter_list[$i] = str_replace("[","\[", $filter_list[$i]) ;
		$filter_list[$i] = str_replace("]","\]", $filter_list[$i]) ;
		$filter_list[$i] = str_replace("|","\|", $filter_list[$i]) ;
		$filter_list[$i] = str_replace("*","\*", $filter_list[$i]) ;

		if($_debug) echo("filter_ip: [$filter_list[$i]]<br>") ;

		if(empty($filter_list[$i]))
		{
			continue ;
		}

		if($_debug) echo("filter_ip:remote_ip:$remote_ip<br>") ;
		if(eregi($filter_list[$i],$remote_ip))
		{
			if($_debug) echo("filter_ip:it's filtered<br>") ;
			$filter = true ;
		}
	}
	return $filter ;
}

?>
