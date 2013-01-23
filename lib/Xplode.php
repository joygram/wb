<?php
/// Copyright 2005, whiteBBS.net
/// @author apollo@WhiteBBS.net
/// @date 2005/10/09
/// ������ �迭�� �ε������� ���߾��ִ� ��� ����
/// �ؽ�Ʈ�� SQL�� ������ �Է½ÿ� �����. 


if(!defined("__wb_xplode__")) define("__wb_xplode__","1") ;
else return ;

class Xplode
{

	//������ ������ �迭�� �����ڸ� �־��ָ� $src_array�� implode()�Լ��� ���� ���ڿ��� ��ȯ.
	// src_array�� "field"�̸��� ������ �迭�̾�� ��. ex) src_array["name"] 
	function ordered_implode($delim, $order_array, $src_array) 
	{
		//ordered_implode(������,�����迭, ����迭) ;
		reset($src_array) ;
		while( ($field = current($order_array)) )
		{
			$ordered_string .= $src_array[$field] ;
			if(next($order_array)) $ordered_string .= $delim ;
		}

		return $ordered_string ;
	}


	/// ������ �ʵ� �̸��� �迭�� �����ϵ��� �Ѵ�. 
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