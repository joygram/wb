<?php
///////////////////////////////////////////////
// board 2.4.5대 버젼 업그레이드 및 버그 패치
///////////////////////////////////////////////

	///////////////////////////////
	// 설치 여부 검사  
	///////////////////////////////
	if(file_exists("../setup{$setup_release_no}.done"))
	{
		echo("<script>
			alert('화이트보드의 설치가 이미 완료되었습니다.\\n\\n새로 설치를 원하신다면 패키지 파일을 업로드 하신후 setup하십시오.\\n\\n기능 설정은 관리자 도구를 이용하세요.') ;
			document.location.href = '../setup.php?cmd=exit' ;
			</script>") ;
		exit ;
	}
	require_once("../lib/system_ini.php") ;
	require_once("../lib/get_base.php") ;
	$C_base = get_base(1, "on") ; //기준이 되는 주소 받아오기, lib.php에 있음.
	require_once("$C_base[dir]/lib/wb.inc.php") ;
	umask(0000) ;
	$move_directory = 0 ;
	///////////////////////////////
	// skin directory 이동
	///////////////////////////////
	//권한 검사.
	if (!is_writeable("$C_base[dir]/board/skin"))
	{
		echo("board/skin에 쓰기 권한이 없습니다. 권한을 777로 바꿔주시고 다시 시도 해주시기 바랍니다.") ;
		echo("<br><input type=button class='wButton' value='다시 시도' onClick='document.location.reload();'>") ;
		exit ;

	}

	if (!is_writeable("$C_base[dir]/board/conf"))
	{
		echo("board/conf에 쓰기 권한이 없습니다. 권한을 777로 바꿔주시고 다시 시도 해주시기 바랍니다.") ;
		echo("<br><input type=button class='wButton' value='다시 시도' onClick='document.location.reload();'>") ;
		exit ;

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

	echo("화이트BBS 245버젼 업그레이드 작업을 완료했습니다.<br>") ;
	redirect("./setup/language.php?upgrade=1") ;
	exit ;
?>
