<?php
///////////////////////////////////////////////
// board 2.4.5�� ���� ���׷��̵� �� ���� ��ġ
///////////////////////////////////////////////

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
	umask(0000) ;
	$move_directory = 0 ;
	///////////////////////////////
	// skin directory �̵�
	///////////////////////////////
	//���� �˻�.
	if (!is_writeable("$C_base[dir]/board/skin"))
	{
		echo("board/skin�� ���� ������ �����ϴ�. ������ 777�� �ٲ��ֽð� �ٽ� �õ� ���ֽñ� �ٶ��ϴ�.") ;
		echo("<br><input type=button class='wButton' value='�ٽ� �õ�' onClick='document.location.reload();'>") ;
		exit ;

	}

	if (!is_writeable("$C_base[dir]/board/conf"))
	{
		echo("board/conf�� ���� ������ �����ϴ�. ������ 777�� �ٲ��ֽð� �ٽ� �õ� ���ֽñ� �ٶ��ϴ�.") ;
		echo("<br><input type=button class='wButton' value='�ٽ� �õ�' onClick='document.location.reload();'>") ;
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

	echo("ȭ��ƮBBS 245���� ���׷��̵� �۾��� �Ϸ��߽��ϴ�.<br>") ;
	redirect("./setup/language.php?upgrade=1") ;
	exit ;
?>
