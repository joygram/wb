<?
	$login_skin = "jia_login3";			// ���߿� �⺻ ��Ų���� ����

	$log_on_page = "$C_base_dir/member/skin/$login_skin/log_on.html";
	$log_off_page = "$C_base_dir/member/skin/$login_skin/log_off.html";

	if( session_is_registered("W_SES") && !empty($W_SES[uid]) )
	{	
		include("$log_on_page");  // ���� ������ log_on_page ���
	}
	else
	{		
		include("$log_off_page");	
	}
?>