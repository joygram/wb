<?php
if(!defined("__wb_system_ini__")) define("__wb_system_ini__","1") ;
else return ;
/**
	save system.ini
	2002/03/18

	2002/11/09 empty체크해서 기본값을 넣어 주는 것은 사용안함을 할 수 있기 때문에  저장하는 루틴에 기본 설정값을 넣어주는 것으로 시도 해야 한다. 즉 선택안함으로 해서 empty가 되는 것은 직접 외부에서 파라메터로 넣어줘야 한다는 것이다.
*/
function save_system_ini($system_conf, $ini)
{
	$_debug = 0 ;

	if($_debug) echo("save_system_ini<br>") ;
	if( @file_exists($system_conf) )
	{
		include($system_conf) ;
	}
	// 이것의 용도는?
	$C_lang = empty($C_lang)?"kr":$C_lang ;

	// empty일 수 없음.
	$C_base_url = empty($ini['base_url'])?$C_base_url:$ini['base_url'] ;	
	$C_base_dir = empty($ini['base_dir'])?$C_base_dir:$ini['base_dir'] ;
	$C_lang     = empty($ini['lang'])?$C_lang:$ini['lang'];
	$C_theme    = empty($C_theme)?"white":$C_theme;

	// empty일 수 없음.
	$C_db_type       = empty($C_db_type)?"old_type":$C_db_type ;
	//혹시 별도로 사용하게 되는 경우를 고려.
	$C_board_db_type = empty($C_board_db_type)?"old_type":$C_board_db_type ;
	$C_member_db_type = empty($C_member_db_type)?"old_type":$C_member_db_type ;	

	// empty일 수 없음.
	$C_db_name   = empty($ini['db_name'])?$C_db_name:$ini['db_name'] ;      
	$C_db_uid    = empty($ini['db_uid'])?$C_db_name:$ini['db_uid'] ;
	$C_db_passwd = empty($ini['db_passwd'])?$C_db_passwd:$ini['db_passwd'] ;

	// empty일 수 있음. 아래의 경우 외부에서 결과를 받아서 갱신 해야 한다.
	$C_use_board   = $ini['use_board'] ;	
	$C_use_counter = $ini['use_counter'] ;
	$C_use_member  = $ini['use_member'] ;	
	$C_use_mail    = $ini['use_mail'] ;	

	// emtpy일 수 없음. 
	$C_language    = empty($ini['language'])?$C_language:$ini['language'] ;	
	$C_timezone    = empty($ini['timezone'])?$C_timezone:$ini['timezone'] ;	

	// version 2.6에서 spam기능에 새로 추가된 것이므로 이전버젼의 설정의 경우 empty일 수 있음.
	$C_uniq_num = empty($ini['uniq_num'])?$C_uniq_num:$ini['uniq_num'] ;

	include_once("$C_base_dir/lib/io.php") ;
	$fd = wb_fopen($system_conf, "w") ;
	fwrite($fd, "<?php\n") ;
	fwrite($fd, "\$C_base_url = \"$C_base_url\" ; \n" ) ;
	fwrite($fd, "\$C_base_dir = \"$C_base_dir\" ; \n" ) ;
	fwrite($fd, "\$C_theme    = \"$C_theme\" ; \n" ) ;
	fwrite($fd, "\$C_lang     = \"$C_lang\" ; \n" ) ;

	fwrite($fd, "\$C_db_type  = \"$C_db_type\";\n") ;
	fwrite($fd, "\$C_db_uid    = \"$C_db_uid\";\n") ;
	fwrite($fd, "\$C_db_passwd = \"$C_db_passwd\";\n") ;
	fwrite($fd, "\$C_db_name   = \"$C_db_name\";\n") ;

	fwrite($fd, "\$C_use_board   = \"$C_use_board\";\n") ;
	fwrite($fd, "\$C_use_counter = \"$C_use_counter\";\n") ;
	fwrite($fd, "\$C_use_member  = \"$C_use_member\";\n") ;
	fwrite($fd, "\$C_use_mail    = \"$C_use_mail\";\n") ;

	fwrite($fd, "\$C_language    = \"$C_language\";\n") ;
	fwrite($fd, "\$C_timezone    = \"$C_timezone\";\n") ;
	fwrite($fd, "\$C_uniq_num    = \"$C_uniq_num\";\n") ;
	fwrite($fd, "?>\n") ;
	fclose($fd) ;

	if($_debug) echo("<br>\n") ;

	@chmod($system_conf, 0666) ;
}
?>
