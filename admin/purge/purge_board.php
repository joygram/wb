<?php
	///////////////////////////
	// 권한검사와 기본환경 읽기 
	// 순서를 지켜주어야 함. 
	// 2002/03/15
	///////////////////////////
	include("../lib.php") ;
	$C_base = get_base(1) ; //기준이 되는 주소 받아오기, lib.php에 있음.
	include($C_base[dir]."/auth/auth.php") ; //권한,인증모듈 선언및 초기화 실행

	include("../file_list.php") ;
	include("../conf/config.php") ;

	$C_auth_perm = "7000" ;
	$auth->perm($C_auth_user, $C_auth_group, $C_auth_perm, $check_data) ;
	///////////////////////////


	if( empty($data) )
	{
		echo("<script>window.alert('게시판이 지정되지 않았습니다. data=게시판'); history.go(-1);</script>") ;
		exit ;
	}	
	else
	{
		$C_data = $data ;
	}
?>
<link rel=StyleSheet href=../skin/default/style.css type=text/css >
<body topmargin='0'  leftmargin='0' marginwidth='0' marginheight='0' bgcolor=#336699>

<?
	//$uniq_id = uniqid("deleted.") ;

	//rename("../data/$data",      "../data/${data}.${uniq_id}") ;
	//rename("../conf/$data.conf", "../conf/${data}.conf.${uniq_id}") ;

	$code="../data/".$data;
	$dir=opendir($code);
	while ($file = readdir($dir)) 
	{
		if(($file != ".") && ($file != ".."))
		{
			unlink ($code."/".$file);
			//$list[]=$file;
		}//if
	}//while
	closedir($dir);
	rmdir ($code);
	unlink ("../conf/".$data.".conf");
     
	echo("<center>게시판 ${data}을 삭제하였습니다.<br>  </center>") ;
	echo("<META HTTP-EQUIV=REFRESH CONTENT=\"2; URL='index.php'\">") ;
	exit ;

?>
