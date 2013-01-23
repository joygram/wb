<?php
	///////////////////////////
	// ���Ѱ˻�� �⺻ȯ�� �б� 
	// ������ �����־�� ��. 
	// 2002/03/15
	///////////////////////////
	require_once("../../lib/system_ini.php") ;
	require_once("../../lib/get_base.php") ;
	$C_base = get_base(2) ; //������ �Ǵ� �ּ� �޾ƿ���, lib.php�� ����.

	require_once("$C_base[dir]/lib/wb.inc.php") ;
	require_once("$C_base[dir]/counter/conf/config.php") ;
	require_once("$C_base[dir]/auth/auth.php") ; //����,������� ����� �ʱ�ȭ ����
	$conf[auth_perm] = "7000" ;
	$auth->perm($conf[auth_user], $conf[auth_group], $conf[auth_perm], $check_data) ;
	///////////////////////////

	// filtering, 2002/03/15 
	$data = eregi_replace("(\.\.|\/|`|'|;|#|~|@|\?|=|&|!|php|conf|[[:space:]])", "", $data) ;
	if( empty($data) )
	{
		err_abort("[$data]�� �ùٸ� ī���� ���� �ƴմϴ�.") ;
	}	
	else
	{
		$C_data = $data ;
	}

	$_datadir = "$C_base[dir]/counter/data/$data" ;

	include("./html/header.html") ;
	//�ߺ� �˻�
	$flist = new file_list("$C_base[dir]/counter/conf", 1) ;
	$flist->read("conf.php", 0) ;
	while( ($file_name = $flist->next()) )
	{
		if( "$data.conf.php" == $file_name ) 
		{
			err_msg("ī���� $data �� �̹� �����մϴ�.") ;
			echo("<META HTTP-EQUIV=REFRESH CONTENT=\"1; URL='counter.php'\">") ;
			exit ;
		}
	}
	//����ó�� �ʿ�
	umask(0000) ;

	
	if(!file_exists("$C_base[dir]/counter/data"))
	{
		mkdir ("$C_base[dir]/counter/data", 0777);
	}

	mkdir ("$C_base[dir]/counter/data/$data", 0777);

	if (!copy("$C_base[dir]/counter/conf/config.php", "$C_base[dir]/counter/conf/${data}.conf.php")) 
	{
		rmdir("$C_base[dir]/data/$data") ;
		err_msg(" $data.conf.php ������ �����ϴ� �� �����߽��ϴ�.") ;
		echo("<META HTTP-EQUIV=REFRESH CONTENT=\"1; URL='counter.php'\">") ;
		exit ;
	}
      
	mkdir("$_datadir/ip",      0777) ;
	mkdir("$_datadir/browser", 0777) ;
	mkdir("$_datadir/lang",    0777) ;
	mkdir("$_datadir/referer", 0777) ;
	mkdir("$_datadir/os",      0777) ;

	touch("$_datadir/data.idx.php") ;
	chmod("$_datadir/data.idx.php", 0666) ;

	touch("$_datadir/total.dat.php") ;
	chmod("$_datadir/total.dat.php", 0666) ;
    
	err_msg("ī���� [${data}] �� ��������ϴ�.<br>[${data}] ī������ ��ɼ����� ���ּ���.") ;
	echo("<META HTTP-EQUIV=REFRESH CONTENT=\"1; URL='counter.php'\">") ;
	include("./html/counter_footer.html") ;
	exit ;
?>
