<?php
if(!defined("__wb_system_ini__")) define("__wb_system_ini__","1") ;
else return ;
/**
	save system.ini
	2002/03/18

	2002/11/09 emptyüũ�ؼ� �⺻���� �־� �ִ� ���� �������� �� �� �ֱ� ������  �����ϴ� ��ƾ�� �⺻ �������� �־��ִ� ������ �õ� �ؾ� �Ѵ�. �� ���þ������� �ؼ� empty�� �Ǵ� ���� ���� �ܺο��� �Ķ���ͷ� �־���� �Ѵٴ� ���̴�.
*/
function save_system_ini($system_conf, $ini)
{
	$_debug = 0 ;

	if($_debug) echo("save_system_ini<br>") ;
	if( @file_exists($system_conf) )
	{
		include($system_conf) ;
	}
	// �̰��� �뵵��?
	$C_lang = empty($C_lang)?"kr":$C_lang ;

	// empty�� �� ����.
	$C_base_url = empty($ini['base_url'])?$C_base_url:$ini['base_url'] ;	
	$C_base_dir = empty($ini['base_dir'])?$C_base_dir:$ini['base_dir'] ;
	$C_lang     = empty($ini['lang'])?$C_lang:$ini['lang'];
	$C_theme    = empty($C_theme)?"white":$C_theme;

	// empty�� �� ����.
	$C_db_type       = empty($C_db_type)?"old_type":$C_db_type ;
	//Ȥ�� ������ ����ϰ� �Ǵ� ��츦 ���.
	$C_board_db_type = empty($C_board_db_type)?"old_type":$C_board_db_type ;
	$C_member_db_type = empty($C_member_db_type)?"old_type":$C_member_db_type ;	

	// empty�� �� ����.
	$C_db_name   = empty($ini['db_name'])?$C_db_name:$ini['db_name'] ;      
	$C_db_uid    = empty($ini['db_uid'])?$C_db_name:$ini['db_uid'] ;
	$C_db_passwd = empty($ini['db_passwd'])?$C_db_passwd:$ini['db_passwd'] ;

	// empty�� �� ����. �Ʒ��� ��� �ܺο��� ����� �޾Ƽ� ���� �ؾ� �Ѵ�.
	$C_use_board   = $ini['use_board'] ;	
	$C_use_counter = $ini['use_counter'] ;
	$C_use_member  = $ini['use_member'] ;	
	$C_use_mail    = $ini['use_mail'] ;	

	// emtpy�� �� ����. 
	$C_language    = empty($ini['language'])?$C_language:$ini['language'] ;	
	$C_timezone    = empty($ini['timezone'])?$C_timezone:$ini['timezone'] ;	

	// version 2.6���� spam��ɿ� ���� �߰��� ���̹Ƿ� ���������� ������ ��� empty�� �� ����.
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
