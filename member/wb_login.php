<?
	$login_skin = "jia_login3";			// 나중에 기본 스킨으로 수정

	$log_on_page = "$C_base_dir/member/skin/$login_skin/log_on.html";
	$log_off_page = "$C_base_dir/member/skin/$login_skin/log_off.html";

	if( session_is_registered("W_SES") && !empty($W_SES[uid]) )
	{	
		include("$log_on_page");  // 세션 있으면 log_on_page 출력
	}
	else
	{		
		include("$log_off_page");	
	}
?>