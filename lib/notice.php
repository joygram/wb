<?php
if(!defined("__wb_notice__")) define("__wb_notice__","1") ;
else return ;
/**
	공지글에 필요한 사항 검사 
*/
	function notice_check($_data, $status)
	{
		if(!empty($status))
		{
			$check = "<input type=checkbox name=notice_check>" ;
		}
		else
		{
			$check = "" ;
		}
		return $check ;
	}
?>
