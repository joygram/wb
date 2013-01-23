<?php
/// Copyright 2005, whiteBBS.net
/// @author apollo@WhiteBBS.net
/// @date 2005/10/09
/// 지정한 배열과 인덱스명을 맞추어주는 기능 수행
/// 텍스트나 SQL에 데이터 입력시에 사용함. 


if(!defined("__wb_xplode__")) define("__wb_xplode__","1") ;
else return ;

class Xplode
{

	//순서를 지정한 배열과 구분자를 넣어주면 $src_array를 implode()함수와 같이 문자열로 변환.
	// src_array는 "field"이름이 지정된 배열이어야 함. ex) src_array["name"] 
	function ordered_implode($delim, $order_array, $src_array) 
	{
		//ordered_implode(구분자,순서배열, 내용배열) ;
		reset($src_array) ;
		while( ($field = current($order_array)) )
		{
			$ordered_string .= $src_array[$field] ;
			if(next($order_array)) $ordered_string .= $delim ;
		}

		return $ordered_string ;
	}


	/// 지정된 필드 이름의 배열로 생성하도록 한다. 
	function ordered_explode( $delim, $order_array, $src_line ) 
	{

		$line_a = explode( $delim, $src_line ) ;

		$idx = 0 ;
		
		$ordered_a ;

		reset($order_array) ;
		while( ($field = current($order_array)) )
		{
			$ordered_a[$field] = $line_a[$idx] ;
			
			if(next($order_array)) $idx++ ;
		}

		return $ordered_a ;
	}

}




?>