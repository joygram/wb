<?php
	include_once("lib/system_ini.php") ;
	include_once("lib/get_base.php") ;
	$C_base = get_base(0) ; //������ �Ǵ� �ּ� �޾ƿ���, lib.php�� ����.

	include("lib/wb.inc.php") ;
	include("${C_base[dir]}/auth/auth.php") ; //����,������� ����� �ʱ�ȭ ����

	$url = "board/write.php?auth_param=$auth_param" ;
	redirect($url) ;
?>
