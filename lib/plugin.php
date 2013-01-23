<?php
if(!defined("__wb_plugin__")) define("__wb_plugin__","1") ;
else return ;
/*
2002/10/31 �ۼ�
2002/11/10
����: plugin���� ����ϴ� ���������� �̸��� �����Ͽ� ����ϵ��� �Ͽ��� �Ѵ�. 
���⼭�� $conf�� ���� ������ ���� ����.
���� conf������ ���纸���� ȯ���� �޾� �����Ͽ� ����ϱ�� �ϳ� ������ $C_ �������� ȯ���̶�� ���Ǹ� ����Ͽ����� $conf�� ��ġ�ϴ� �͵� �����ϴٰ� ����.
�ƴϸ� ���� �÷����ο� ������ �޾� ���� �ұ�?
*/
function include_plugin($parts, $_plugindir, $conf)
{
	prepare_server_vars() ;
	global $__SERVER, $__GET, $__POST, $__COOKIE, $__FILES, $__ENV, $__SESSION ;

	global $Row, $URL, $hide, $plug, $plug_hide, $plug_url, $param, $_data  ;
	global $board_group, $board_id,  $cur_page, $field, $key, $filter_type, $to, $mode ;
	$_debug = 0 ;
	$data = $_data ;

	//���� ������ ���� �Ұ��ΰ� Ȯ��
	//2002/10/26 plugin parts����
	//echo�� �������ΰ� ���
	$_plug_output = "" ;
	for($j = 0 ; $j < sizeof($conf[plugin_install]) ; $j++)
	{
		if(empty($conf[plugin_install][$j])) 
			continue ;
		if(file_exists("$_plugindir/{$conf[plugin_install][$j]}/{$parts}.php"))
		{
			$_plug_var_save = "" ;
			$_plugin = $conf[plugin_install][$j] ;
			//���� global���� SAVE
			//�÷����ο��� ����ϴ� ���� ���� ���ؼ�...
			if($_debug) echo("include plugin [$_plugin][$parts]<br>") ;

			//���� ������ ���� ����
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

			//�������� ����
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
