<?php
	///////////////////////////
	// ���Ѱ˻�� �⺻ȯ�� �б� 
	// ������ �����־�� ��. 
	// 2002/03/15
	///////////////////////////
	$_debug = 0 ;
	require_once("../../lib/system_ini.php") ;
	require_once("../../lib/get_base.php") ;
	$C_base = get_base(2, "on") ; //������ �Ǵ� �ּ� �޾ƿ���, lib.php�� ����.
	require_once("$C_base[dir]/lib/wb.inc.php") ;
	require_once("$C_base[dir]/auth/auth.php") ; //����,������� ����� �ʱ�ȭ ����
	$conf[auth_perm] = "7000" ;
	$auth->perm($conf[auth_user], $conf[auth_group], $conf[auth_perm], $check_data) ;
	umask(0000) ;
	if ($_debug) echo("part[$part]<br>") ;
	switch($part)
	{
		case "news":
			$target_dir = "$C_base[dir]/counter/skin/__global/news" ;
			$title = "�ֱٰԽù�" ;
			break ;
		case "category":
			$target_dir = "$C_base[dir]/counter/skin/__global/category" ;
			$title = "ī�װ�" ;
			break ;
		case "pagebar":
			$target_dir = "$C_base[dir]/counter/skin/__global/pagebar" ;
			$title = "��������" ;
			break ;
		default:
			$target_dir = "$C_base[dir]/counter/skin" ;
			$title = "����" ;
			break ;
	}
	$Row[title] = $title." ��Ų ����" ;
	include("./html/skin_header.html") ;
	if ($_debug) echo("target_dir[$target_dir]<br>") ;	

		// ��Ų ���丮 �̸��� �о ������ֱ�
	$flist = new file_list($target_dir, 1) ;
	$flist->read("*", 0) ;
		//��ġ�� ��Ų ī��Ʈ
	$nTotal = 0 ;
	while( ($file_name = $flist->next()) )
	{
		if( $file_name == "." || $file_name == ".." || $file_name == "CVS" || $file_name == "__global" || eregi("deleted", $file_name))
		{
			continue ;
		}
		$nTotal++ ;
	}

	$flist->reset() ;
	while( ($file_name = $flist->next()) )
	{
		if( $file_name == "." || $file_name == ".." || $file_name == "CVS" || $file_name == "__global" || eregi("deleted", $file_name) || eregi(".zip", $file_name))
		{
			continue ;
		}

		/**
		if($C_skin == $file_name)
		{
			$selected = "selected" ;
		}
		$Row[func] .= "<option value='$file_name' $selected>$file_name</option>\n" ;
		*/
		if ($_debug) echo("file_name[$file_name]") ;
		if (empty($part)) 
		{
			$_skin_dir = "$C_base[dir]/counter/skin/$file_name" ;
			$_skin_url = "$C_base[url]/counter/skin/$file_name" ;
		}
		else
		{
			$_skin_dir = "$C_base[dir]/counter/skin/__global/$part/$file_name" ;
			$_skin_url = "$C_base[url]/counter/skin/__global/$part/$file_name" ;
		}
		$i++ ;
		$board = explode(".", $file_name) ;
		$Row[no] = $nTotal-$i+1 ;
		$Row[board] = $board[0] ;

		$Row[cnt] = $cnt ;
		$Row[setup] = "read_config.php?data=$file_name" ;

		$hide['preview'] = "" ;
		$hide['/preview'] = "" ;
		if (file_exists("$_skin_dir/preview.gif"))
		{
			$PREVIEW_URL = "$_skin_url/preview.gif" ; 
		}
		else
		{
			$hide['preview'] = "<!--\n" ;
			$hide['/preview'] = "-->\n" ;
		}		

		$CONFIG_URL = "./skin.php?conf=$file_name&dock=on" ; 

		//author|author_email|author_url|type|version|auth_range
		$cont = @file("$_skin_dir/author.txt") ;
		$cont[0] = chop($cont[0]) ;
		$cont[0] = str_replace("<", "&lt;", $cont[0]) ;
		$cont[0] = str_replace(">", "&gt;", $cont[0]) ;
		$tmp_arr = explode("|", $cont[0]) ;

		$Row[author]       = $tmp_arr[0] ;
		$Row[author_email] = $tmp_arr[1] ;
		$AUTHOR_URL        = $tmp_arr[2] ;
		$hide['author_url'] = "" ;
		$hide['/author_url'] = "" ;
		if(empty($AUTHOR_URL))
		{
			$hide['author_url'] = "<!--\n" ;
			$hide['/author_url'] = "-->\n" ;
		}
		else if(!eregi("http://", $AUTHOR_URL))
		{
			$AUTHOR_URL = "http://".$AUTHOR_URL ;
		}
		$hide['author_email'] = "" ;
		$hide['/author_email'] = "" ;
		if(empty($Row[author_email]))
		{
			$hide['author_email'] = "<!--\n" ;
			$hide['/author_email'] = "-->\n" ;
		}
		$hide['readme'] =  "" ;
		$hide['/readme'] = "" ;
		if(!file_exists("$_skin_dir/readme.txt"))
		{
			$hide['readme'] =  "<!--\n" ;
			$hide['/readme'] = "-->\n" ;
		}
		else
		{
			$README_URL = "$_skin_url/readme.txt" ;
		}
		$DEL_URL    = "javascript:onClick=Confirm(\"skin_del.php?data=$board[0]&part=$part\",\"$board[0]\",\"del\"); " ;
		$hide['del'] = "" ;
		$hide['/del'] = "" ;
		if(!is_writeable($_skin_dir))
		{
			$DEL_URL = "" ;
			$hide['del'] = "<!--\n" ;
			$hide['/del'] = "-->\n" ;
		}
		include("./html/skin_list.html") ;
	}

	if ($_debug) echo("part[$part]<br>") ;
	$Row[title] = $title ;
	$Row[part] = $part ;
	include("./html/skin_footer.html") ;
?>
