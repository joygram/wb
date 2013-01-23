<?php
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
	require_once("$C_base[dir]/lib/io.php") ;

	$conf[auth_perm] = "7000" ;
	$auth->perm($conf[auth_user], $conf[auth_group], $conf[auth_perm], $check_data) ;
	///////////////////////////
	$_debug = 0 ;
	//include("html/header") ;
	//보안을 위해 변수 필터링, 
	//2002/03/15 보완
	if ($_debug) echo("conf_name[$conf_name]<br>") ;
	$conf_name = eregi_replace("(\.\.|\/|`|'|;|#|~|@|\?|=|&|!|admin\.php|[[:space:]])", "", $conf_name) ;

	if($_debug) echo ("$C_base[dir]/counter/conf/$conf_name") ;
	$fp = wb_fopen("$C_base[dir]/counter/conf/$conf_name", "w") ;

	fwrite($fp, "<?php\n\n") ;

	$C_global_use = ($C_global_use == "on")?"1":"0" ;  
	fwrite($fp, "\$C_global_use = \"$C_global_use\" ; \n") ;

	$C_global_general_use = ($C_global_general_use == "on")?"1":"0" ;  
	fwrite($fp, "\$C_global_general_use = \"$C_global_general_use\" ; \n") ;
	fwrite($fp, "\$C_skin = \"$C_skin\" ;\n") ;
	fwrite($fp, "\$C_cookie_time = \"$C_cookie_time\" ;\n") ;	
	fwrite($fp, "\$C_popup_func = \"$C_popup_func\" ;\n\n") ;
	
	$C_global_data_use = ($C_global_data_use == "on")?"1":"0" ;  
	fwrite($fp, "\$C_total_base = \"$C_total_base\" ;\n\n") ;

	$C_global_view_use = ($C_global_view_use == "on")?"1":"0" ;
	fwrite($fp, "\$C_global_view_use = \"$C_global_view_use\" ;\n\n") ;
	fwrite($fp, "\$C_view_yesterday  = \"$C_view_yesterday\" ;\n") ;
	fwrite($fp, "\$C_view_today      = \"$C_view_today\" ;\n") ;
	fwrite($fp, "\$C_view_month      = \"$C_view_month\" ;\n") ;
	fwrite($fp, "\$C_view_year       = \"$C_view_year\" ;\n") ;
	fwrite($fp, "\$C_view_total      = \"$C_view_total\" ;\n") ;
	fwrite($fp, "\$C_view_max        = \"$C_view_max\" ;\n") ;

	$C_global_event_use = ($C_global_event_use == "on")?"1":"0" ;  
	fwrite($fp, "\$C_global_event_use = \"$C_global_event_use\" ; \n") ;
	fwrite($fp, "\$C_event_point = \"$C_event_point\" ;\n") ;
	fwrite($fp, "\$C_event_url = \"$C_event_url\" ;\n") ;
	fwrite($fp, "?>") ;
	fclose($fp) ; 

	err_msg("저장하였습니다.") ;
	echo("<META HTTP-EQUIV=REFRESH CONTENT=\"0; URL='config_read.php?conf_name=$conf_name'\">") ;
	//echo("<script>self.close();</script>") ;
	exit ;
?>
