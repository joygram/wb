<?
	///////////////////////////
	// 권한검사와 기본환경 읽기 
	// 순서를 지켜주어야 함. 
	// 2002/03/15
	///////////////////////////
	require_once("../../lib/system_ini.php") ;
	require_once("../../lib/get_base.php") ;
	$C_base = get_base(2) ; //기준이 되는 주소 받아오기, lib.php에 있음.
	require_once("$C_base[dir]/lib/wb.inc.php") ;
	require_once("$C_base[dir]/auth/auth.php") ; //권한,인증모듈 선언및 초기화 실행
	$conf[auth_perm] = "7000" ;
	$auth->perm($conf[auth_user], $conf[auth_group], $conf[auth_perm], $check_data) ;
	///////////////////////////

		// filtering, 2002/03/15 
	$data = eregi_replace("(\.\.|\/|`|'|;|#|~|@|\?|=|&|!|php|conf|[[:space:]])", "", $data) ;
	if( empty($data) )
	{
		echo("<script>window.alert('"._L_SKIN_NAME_NEED."'); history.go(-1);</script>") ;
		exit ;
	}	
	else
	{
		$C_data = $data ;
	}
	include("./html/header.html") ;
	$uniq_id = uniqid("deleted.") ;
	$_part = $part ;
	if (empty($_part))
	{
		$_skin_dir = "$C_base[dir]/board/skin/$C_data" ; 
	}
	else
	{
		$_skin_dir = "$C_base[dir]/board/skin/__global/$_part/$C_data" ;
	}
	wb_rename($_skin_dir, "$_skin_dir.$uniq_id",1,1) ;
	err_msg("[$data] "._L_SKIN_DELETE_COMPLETE) ;
	echo("<META HTTP-EQUIV=REFRESH CONTENT=\"1; URL='skin.php?part=$_part'\">") ;
	exit ;
?>
