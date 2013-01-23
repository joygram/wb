<?php
///////////////////////////////////////////////
// board 2.1.2 ���Ϲ��� ���׷��̵� �� ���� ��ġ
///////////////////////////////////////////////
	/*
	///////////////////////////////////////////////	
	include("$C_base[dir]/board/conf/config.php") ;
	$C_auth_perm = "7000" ;
	$auth->perm($C_auth_user, $C_auth_group, $C_auth_perm, $check_data) ;

	if(@file_exists("../board/VERSION"))
	{
		$ver = file("$C_base[dir]/board/VERSION") ;
	}
	else
	{
		err_abort("$C_base[dir]/board/VERSION ������ ã�� �� �����ϴ�.") ;
	}

	$Row['base_dir'] = $C_base[dir] ;
	$Row['base_url'] = $C_base[url] ;
	$Row['version'] = $ver[0] ;
	$Row['alias'] = $auth->alias() ;
	*/

	include("./html/header") ;
	$_debug = 0 ;
	///////////////////////////////
	// ��ġ ���� �˻�  
	///////////////////////////////
	if(file_exists("../setup{$setup_release_no}.done"))
	{
		echo("<script>
			alert('ȭ��Ʈ������ ��ġ�� �̹� �Ϸ�Ǿ����ϴ�.\\n\\n���� ��ġ�� ���ϽŴٸ� ��Ű�� ������ ���ε� �Ͻ��� setup�Ͻʽÿ�.\\n\\n��� ������ ������ ������ �̿��ϼ���.') ;
			document.location.href = '../setup.php?cmd=exit' ;
			</script>") ;
		exit ;
	}
	require_once("../lib/system_ini.php") ;
	require_once("../lib/get_base.php") ;
	$C_base = get_base(1, "on") ; //������ �Ǵ� �ּ� �޾ƿ���, lib.php�� ����.
	require_once("$C_base[dir]/lib/wb.inc.php") ;

	
	///////////////////////////////////////////////
	//1. default position correction below 2.1.2 
	///////////////////////////////////////////////
	// 2002/04/10 ���� ���丮 �̵�
	// skin -> board/skin
	// data -> board/data
	// conf -> board/conf
	// conf/admin.php -> admin/admin.php

	umask(0000) ;
	$move_directory = 0 ;
	///////////////////////////////
	// skin directory �̵�
	///////////////////////////////
	if( @file_exists("$C_base[dir]/skin"))
	{
		$move_directory = 1 ;
		wb_rename("$C_base[dir]/skin", "$C_base[dir]/board/skin",1,1) ;

	}
	///////////////////////////////
	// conf directory �̵�
	///////////////////////////////
	if( @file_exists("$C_base[dir]/conf"))
	{
		$move_directory = 1 ;
		wb_rename("$C_base[dir]/conf", "$C_base[dir]/board/conf",1,1) ;
	}

	// *) make conf directory
	// *) move config.php file move 
	if( ! @file_exists("$C_base[dir]/board/conf") )
	{
		echo("<h3>STEP 3</h3>") ;
		if(! mkdir("$C_base[dir]/board/conf", 0777) )
		{
			echo("$C_base[dir]/board/conf ����µ� ���� �߽��ϴ�.\n$C_base[dir]/board ���丮�� ������ 777���� Ȯ�����ֽʽÿ�.") ;
			exit ;
		} 
	}

	if (!file_exists("$C_base[dir]/board/conf/__global.conf.php")) 
		touch("$C_base[dir]/board/conf/__global.conf.php") ;	

	if (!file_exists("$C_base[dir]/board/skin/__global")) 
		mkdir("$C_base[dir]/board/skin/__global", 0757) ;

	if (!file_exists("$C_base[dir]/board/skin/__global/news")) 
		mkdir("$C_base[dir]/board/skin/__global/news", 0757) ;

	if (!file_exists("$C_base[dir]/board/skin/__global/category")) 
		mkdir("$C_base[dir]/board/skin/__global/category", 0757) ;

	if (!file_exists("$C_base[dir]/board/skin/__global/pagebar")) 
		mkdir("$C_base[dir]/board/skin/__global/pagebar", 0757) ;

	///////////////////////////////////////////////	
	// data dir �̵�
	///////////////////////////////////////////////	
	if( @file_exists("$C_base[dir]/data"))
	{
		$move_directory = 1 ;
		wb_rename("$C_base[dir]/data", "$C_base[dir]/board/data",1,1) ;
	}
	/////////////////////////////////////////////
	// �ʿ��� ���丮 ���� 
	/////////////////////////////////////////////
	/*
	// auth���丮 ����
	if (!file_exists("../auth"))
	{
		umask(0000) ;	
		mkdir("../auth", "0777") ;
	}
	*/
	

	/////////////////////////////////////////////
	// conf���� �̸� ����
	// .conf������ �̸��� .conf.php�� �ٲپ��ش�.
	/////////////////////////////////////////////
	$flist = new file_list("$C_base[dir]/board/conf", 1) ;
	$flist->read("conf", 0) ;
	$i = 0 ;
	$nTotal = 0 ;
	while( ($file_name = $flist->next()) )
	{
		if($_debug) echo("conf file name [$file_name]<br>") ;
		if(strstr($file_name, "deleted") || strstr($file_name, "conf.php"))
		{
			if($_debug) echo("skip file_name => [$file_name] skip<br>") ;
			continue ;
		}

		wb_rename("$C_base[dir]/board/conf/$file_name", "$C_base[dir]/board/conf/{$file_name}.php",1,1) ;
		//$nTotal++ ;
	}
	$flist->reset() ;


	$cont = file("$C_base[dir]/release_no") ;
	$installed_release_no = chop($cont[0]) ;
	$installed_ver = chop($cont[1]) ;
	////////////////////////////////////////
	// 2.1.2 name nested encoding bug fix
	// rename data.idx name
	////////////////////////////////////////
	redirect("./correct212.php?setup_release_no={$installed_ver}_{$installed_relase_no}", 1) ;
	exit ;
?>
