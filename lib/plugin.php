<?php
if(!defined("__wb_plugin__")) define("__wb_plugin__","1") ;
else return ;
/*
2002/10/31 작성
2002/11/10
주의: plugin에서 사용하는 전역변수의 이름은 통일하여 사용하도록 하여야 한다. 
여기서는 $conf에 값이 들어가도록 정의 하자.
원래 conf변수는 현재보드의 환경을 받아 저장하여 사용하기는 하나 이전에 $C_ 변수역시 환경이라는 정의를 사용하였으니 $conf로 대치하는 것도 무난하다고 본다.
아니면 따로 플러그인용 변수로 받아 쓰게 할까?
*/
function include_plugin($parts, $_plugindir, $conf)
{
	prepare_server_vars() ;
	global $__SERVER, $__GET, $__POST, $__COOKIE, $__FILES, $__ENV, $__SESSION ;

	global $Row, $URL, $hide, $plug, $plug_hide, $plug_url, $param, $_data  ;
	global $board_group, $board_id,  $cur_page, $field, $key, $filter_type, $to, $mode ;
	$_debug = 0 ;
	$data = $_data ;

	//변수 저장후 복구 할것인가 확인
	//2002/10/26 plugin parts삽입
	//echo를 막을것인가 고려
	$_plug_output = "" ;
	for($j = 0 ; $j < sizeof($conf[plugin_install]) ; $j++)
	{
		if(empty($conf[plugin_install][$j])) 
			continue ;
		if(file_exists("$_plugindir/{$conf[plugin_install][$j]}/{$parts}.php"))
		{
			$_plug_var_save = "" ;
			$_plugin = $conf[plugin_install][$j] ;
			//기존 global변수 SAVE
			//플러그인에서 출력하는 것을 막기 위해서...
			if($_debug) echo("include plugin [$_plugin][$parts]<br>") ;

			//변수 복구를 위해 저장
			$old = "" ;
			$old[Row]          = $Row ;
			$old[URL]          = $URL ;
			$old[hide]         = $hide ;
			$old[_data]        = $_data ;
			$old[board_group]  = $board_group ;
			$old[board_id]     = $board_id ;
			$old[cur_page]     = $cur_page ;
			$old[field]        = $field ;
			$old[key]          = $key ;
			$old[filter_type]  = $filter_type ;
			$old[to]           = $to ;
			$old[mode]         = $mode ;

			ob_start() ;
			include("$_plugindir/{$_plugin}/{$parts}.php") ;
			$_plug_output .= ob_get_contents() ;
			ob_end_clean() ;

			//예전변수 복구
			if($_plugin_var_save)
			{
				//restore
				$Row         =  $old[Row]         ;
				$URL         =  $old[URL]         ;
				$hide        =  $old[hide]        ;
				$_data       =  $old[_data]       ;
				$board_group =  $old[board_group] ;
				$board_id    =  $old[board_id]    ;
				$cur_page    =  $old[cur_page]    ;
				$field       =  $old[field]       ;
				$key         =  $old[key]         ;
				$filter_type =  $old[filter_type] ;
				$to          =  $old[to]          ;
				$mode        =  $old[mode]        ;
			}
		}
	}		
	if($_debug) echo $_plug_output ;
	return $_plug_output ;
}
?>
