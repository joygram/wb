<?php
if(!defined("__wb_total_cnt__")) define("__wb_total_cnt__","1") ;
else return ;
/**
	������ ���� ��ü ���� ����  
*/
	function get_total_cnt($_data, $total_name) 
	{
		global $C_base ;
		include_once("$C_base[dir]/lib/io.php") ;

		$fp = wb_fopen("$C_base[dir]/board/data/$_data/total.cnt", "r") ;
		$total = fgets($fp, 512) ;
		fclose($fp) ;

		return $total ;
	}


/**
	��ü�� ���� ����
*/
	function update_total_cnt($_data, $total_name, $increment)
	{
		global $C_base ;
		require_once("$C_base[dir]/lib/io.php") ;

		$total = get_total_cnt($_data, $total_name) ;

		$fp = wb_fopen("$C_base[dir]/board/data/$_data/total.cnt", "w") ;

		$total = $total+$increment ;
		fwrite($fp, "$total") ;
		fclose($fp) ;
	}
?>
