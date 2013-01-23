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

	if($_debug) echo ("$C_base[dir]/board/conf/$conf_name") ;
	$fp = wb_fopen("$C_base[dir]/board/conf/$conf_name", "w") ;

	if($_debug) echo("[$C_skin]board_title[$C_board_title]<br>") ;
	fwrite($fp, "<?php\n\n") ;
	fwrite($fp, "\$LIST_PHP = \"$LIST_PHP\" ;\n") ;
	fwrite($fp, "\$WRITE_PHP   = \"$WRITE_PHP\" ;\n") ;
	fwrite($fp, "\$DELETE_PHP  = \"$DELETE_PHP\" ;\n") ;

	$C_global_use = ($C_global_use == "on")?"1":"0" ;  
	fwrite($fp, "\$C_global_use = \"$C_global_use\" ; \n") ;

	$C_global_general_use = ($C_global_general_use == "on")?"1":"0" ;  
	fwrite($fp, "\$C_global_general_use = \"$C_global_general_use\" ; \n") ;
	fwrite($fp, "\$C_skin = \"$C_skin\" ;\n") ;
	fwrite($fp, "\$C_board_title = \"$C_board_title\" ;\n") ;	
	fwrite($fp, "\$C_table_size = \"$C_table_size\" ;\n") ;
	fwrite($fp, "\$C_table_align = \"$C_table_align\" ;\n") ;
	fwrite($fp, "\$C_license_align = \"$C_license_align\" ;\n\n") ;
	$C_spam_check_use = ( $C_spam_check_use == "on" )?"1":"0" ;
	fwrite($fp, "\$C_spam_check_use = \"$C_spam_check_use\" ;\n\n") ;
	
	$C_global_perm_use = ($C_global_perm_use == "on")?"1":"0" ;  
	fwrite($fp, "\$C_global_perm_use = \"$C_global_perm_use\" ; \n") ;
	//권한 설정부분
	$C_write_admin_only = ( $C_write_admin_only == "on" )?"1":"0" ;
	//2002/03/17 권한 설정 처리 부분 
	//체크박스에 선택한 값을 비트값으로 변경시켜준다.
	// normal_perm
	$admin_perm = 0 ;
	$admin_perm |= ($C_perm[admin_read] == "on")? 4 : $admin_perm ;
	$admin_perm |= ($C_perm[admin_write] == "on")? 2 : $admin_perm ;
	$admin_perm |= ($C_perm[admin_exec] == "on")? 1 : $admin_perm ;
	$group_perm = 0 ;
	$group_perm |= ($C_perm[group_read] == "on")? 4 : $group_perm ;
	$group_perm |= ($C_perm[group_write] == "on")? 2 : $group_perm ;
	$group_perm |= ($C_perm[group_exec] == "on")? 1 : $group_perm ;
	$member_perm = 0 ;
	$member_perm |= ($C_perm[member_read] == "on")? 4 : $member_perm ;
	$member_perm |= ($C_perm[member_write] == "on")? 2 : $member_perm ;
	$member_perm |= ($C_perm[member_exec] == "on")? 1 : $member_perm ;
	$anonymous_perm = 0 ;
	$anonymous_perm |= ($C_perm[anonymous_read] == "on")? 4 : $anonymous_perm ;
	$anonymous_perm |= ($C_perm[anonymous_write] == "on")? 2 : $anonymous_perm ;
	$anonymous_perm |= ($C_perm[anonymous_exec] == "on")? 1 : $anonymous_perm ;
	$C_auth_perm = $admin_perm.$group_perm.$member_perm.$anonymous_perm ;
	// reply_perm
	$admin_reply_perm = 0 ;
	$admin_reply_perm |= ($C_perm[admin_read] == "on")? 4 :  $admin_reply_perm ;
	$admin_reply_perm |= ($C_perm[admin_reply] == "on")? 2 : $admin_reply_perm ;
	$admin_reply_perm |= ($C_perm[admin_exec] == "on")? 1 :  $admin_reply_perm ;
	$group_reply_perm = 0 ;
	$group_reply_perm |= ($C_perm[group_read] == "on")? 4 :  $group_reply_perm ;
	$group_reply_perm |= ($C_perm[group_reply] == "on")? 2 : $group_reply_perm ;
	$group_reply_perm |= ($C_perm[group_exec] == "on")? 1 :  $group_reply_perm ;
	$member_reply_perm = 0 ;
	$member_reply_perm |= ($C_perm[member_read] == "on")? 4 :  $member_reply_perm ;
	$member_reply_perm |= ($C_perm[member_reply] == "on")? 2 : $member_reply_perm ;
	$member_reply_perm |= ($C_perm[member_exec] == "on")? 1 :  $member_reply_perm ;
	$anonymous_reply_perm = 0 ;
	$anonymous_reply_perm |= ($C_perm[anonymous_read] == "on")? 4 :  $anonymous_reply_perm ;
	$anonymous_reply_perm |= ($C_perm[anonymous_reply] == "on")? 2 : $anonymous_reply_perm ;
	$anonymous_reply_perm |= ($C_perm[anonymous_exec] == "on")? 1 :  $anonymous_reply_perm ;
	$C_auth_reply_perm = $admin_reply_perm.$group_reply_perm.$member_reply_perm.$anonymous_reply_perm ;
	// cat_perm
	$admin_cat_perm = 0 ;
	$admin_cat_perm |= ($C_perm[admin_cat] == "on")? 4 :  $admin_cat_perm ;
	$admin_cat_perm |= ($C_perm[admin_write] == "on")? 2 : $admin_cat_perm ;
	$admin_cat_perm |= ($C_perm[admin_exec] == "on")? 1 :  $admin_cat_perm ;
	$group_cat_perm = 0 ;
	$group_cat_perm |= ($C_perm[group_cat] == "on")? 4 :  $group_cat_perm ;
	$group_cat_perm |= ($C_perm[group_write] == "on")? 2 : $group_cat_perm ;
	$group_cat_perm |= ($C_perm[group_exec] == "on")? 1 :  $group_cat_perm ;
	$member_cat_perm = 0 ;
	$member_cat_perm |= ($C_perm[member_cat] == "on")? 4 :  $member_cat_perm ;
	$member_cat_perm |= ($C_perm[member_write] == "on")? 2 : $member_cat_perm ;
	$member_cat_perm |= ($C_perm[member_exec] == "on")? 1 :  $member_cat_perm ;
	$anonymous_cat_perm = 0 ;
	$anonymous_cat_perm |= ($C_perm[anonymous_cat] == "on")? 4 :  $anonymous_cat_perm ;
	$anonymous_cat_perm |= ($C_perm[anonymous_write] == "on")? 2 : $anonymous_cat_perm ;
	$anonymous_cat_perm |= ($C_perm[anonymous_exec] == "on")? 1 :  $anonymous_cat_perm ;
	$C_auth_cat_perm = $admin_cat_perm.$group_cat_perm.$member_cat_perm.$anonymous_cat_perm ;
	fwrite($fp, "\$C_admin_perm = \"$admin_perm\" ;\n") ;
	fwrite($fp, "\$C_group_perm = \"$group_perm\" ;\n") ;
	fwrite($fp, "\$C_member_perm = \"$member_perm\" ;\n") ;
	fwrite($fp, "\$C_anonymous_perm = \"$anonymous_perm\" ;\n") ;
	fwrite($fp, "\$C_auth_perm = \"$C_auth_perm\" ;\n\n") ;
	fwrite($fp, "\$C_admin_reply_perm = \"$admin_reply_perm\" ;\n") ;
	fwrite($fp, "\$C_group_reply_perm = \"$group_reply_perm\" ;\n") ;
	fwrite($fp, "\$C_member_reply_perm = \"$member_reply_perm\" ;\n") ;
	fwrite($fp, "\$C_anonymous_reply_perm = \"$anonymous_reply_perm\" ;\n") ;
	fwrite($fp, "\$C_auth_reply_perm = \"$C_auth_reply_perm\" ;\n\n") ;
	fwrite($fp, "\$C_admin_cat_perm = \"$admin_cat_perm\" ;\n") ;
	fwrite($fp, "\$C_group_cat_perm = \"$group_cat_perm\" ;\n") ;
	fwrite($fp, "\$C_member_cat_perm = \"$member_cat_perm\" ;\n") ;
	fwrite($fp, "\$C_anonymous_cat_perm = \"$anonymous_cat_perm\" ;\n") ;
	fwrite($fp, "\$C_auth_cat_perm = \"$C_auth_cat_perm\" ;\n\n") ;

	$C_global_write_use = ($C_global_write_use == "on")?"1":"0" ;  
	fwrite($fp, "\$C_global_write_use = \"$C_global_write_use\" ; \n") ;
	$C_small_reply_use = ( $C_small_reply_use == "on" )?"1":"0" ;
	fwrite($fp, "\$C_small_reply_use = \"$C_small_reply_use\" ;\n") ;
	$C_cookie_use = ( $C_cookie_use == "on" )?"1":"0" ;
	fwrite($fp, "\$C_cookie_use = \"$C_cookie_use\" ;\n\n") ;
	$C_subject_html_use = ( $C_subject_html_use == "on" )?"1":"0" ;
	fwrite($fp, "\$C_subject_html_use = \"$C_subject_html_use\" ;\n") ;
	$C_name_html_use = ( $C_name_html_use == "on" )?"1":"0" ;
	fwrite($fp, "\$C_name_html_use = \"$C_name_html_use\" ;\n") ;
	$C_html_use = ( $C_html_use == "on" )?"1":"0" ;
	fwrite($fp, "\$C_html_use = \"$C_html_use\" ;\n") ;

	$C_global_list_use = ($C_global_list_use == "on")?"1":"0" ;  
	fwrite($fp, "\$C_global_list_use = \"$C_global_list_use\" ; \n") ;
	fwrite($fp, "\$C_nCol = \"$C_nCol\" ;\n") ;
	fwrite($fp, "\$C_nRow  = \"$C_nRow\" ;\n\n") ;
	fwrite($fp, "\$C_subject_max = \"$C_subject_max\"; \n\n") ;
	fwrite($fp, "\$C_comment_max = \"$C_comment_max\" ;\n") ;
	fwrite($fp, "\$C_img_size_limit = \"$C_img_size_limit\" ;\n") ;	
	$C_url2link_use  = ( $C_url2link_use == "on" )?"1":"0" ;
	fwrite($fp, "\$C_url2link_use = \"$C_url2link_use\" ;\n") ;
	fwrite($fp, "\$C_sort_index = \"$C_sort_index\" ;\n") ; 
	fwrite($fp, "\$C_sort_order = \"$C_sort_order\" ;\n\n") ; 
	$C_bg_img_use = ( $C_bg_img_use == "on" )?"1":"0" ;
	fwrite($fp, "\$C_bg_img_use = \"$C_bg_img_use\" ;\n") ;

	$C_global_news_use = ($C_global_news_use == "on")?"1":"0" ;  
	fwrite($fp, "\$C_global_news_use = \"$C_global_news_use\" ; \n") ;
	$C_news_use = ( $C_news_use == "on" )?"1":"0" ;
	fwrite($fp, "\$C_news_use = \"$C_news_use\" ;\n") ;
	if($_debug) echo("C_news_nCol[$C_news_nCol]<br>") ;
	if(empty($C_news_nCol))
	{
		if($_debug) echo("C_news_nCol is empty<br>") ;
		$C_news_nCol = "1" ;
		$C_news_nRow = "3" ;
	}
	fwrite($fp, "\$C_news_nCol = \"$C_news_nCol\" ;\n") ;
	fwrite($fp, "\$C_news_nRow  = \"$C_news_nRow\" ;\n\n") ;
	fwrite($fp, "\$C_news_subject_max = \"$C_news_subject_max\"; \n\n") ;
	fwrite($fp, "\$C_news_char_max = \"$C_news_char_max\"; \n\n") ;
	fwrite($fp, "\$C_news_skin     = \"$C_news_skin\" ;\n") ;

	$C_global_pagebar_use = ($C_global_pagebar_use == "on")?"1":"0" ;  
	fwrite($fp, "\$C_global_pagebar_use = \"$C_global_pagebar_use\" ; \n") ;
	fwrite($fp, "\$MAX_PAGE_SHOW = \"$MAX_PAGE_SHOW\" ;\n") ;
	fwrite($fp, "\$C_page_bar_align = \"$C_page_bar_align\" ;\n\n") ;
	fwrite($fp, "\$C_pagebar_skin  = \"$C_pagebar_skin\" ;\n") ;

	$C_global_grad_use = ($C_global_grad_use == "on")?"1":"0" ;  
	fwrite($fp, "\$C_global_grad_use = \"$C_global_grad_use\" ; \n") ;
	fwrite($fp, "\$C_grad_start_color = \"$C_grad_start_color\" ;\n") ;
	fwrite($fp, "\$C_grad_end_color = \"$C_grad_end_color\" ;\n\n") ;

	$C_global_filter_use = ($C_global_filter_use == "on")?"1":"0" ;  
	fwrite($fp, "\$C_global_filter_use = \"$C_global_filter_use\" ; \n") ;
	fwrite($fp, "\$C_filter_name = \"$C_filter_name\" ;\n") ;
	fwrite($fp, "\$C_filter_subject = \"$C_filter_subject\";\n") ;
	fwrite($fp, "\$C_filter_type = \"$C_filter_type\" ;\n") ;
	fwrite($fp, "\$C_block_tag = \"$C_block_tag\" ;\n") ;
	$C_filter_ip_use = ($C_filter_ip_use == "on")?"1":$C_filter_ip_use ;	
	fwrite($fp, "\$C_filter_ip_use = \"$C_filter_ip_use\" ;\n") ;
	fwrite($fp, "\$C_filter_ip = \"$C_filter_ip\" ;\n") ;
	$C_filter_txt_use = ($C_filter_txt_use == "on")?"1":$C_filter_txt_use ;	
	fwrite($fp, "\$C_filter_txt_use = \"$C_filter_txt_use\" ;\n") ;
	fwrite($fp, "\$C_filter_txt = \"$C_filter_txt\" ;\n") ;

	$C_global_upload_use = ($C_global_upload_use == "on")?"1":"0" ;  
	fwrite($fp, "\$C_global_upload_use = \"$C_global_upload_use\" ; \n") ;
	fwrite($fp, "\$C_upload_limit = \"$C_upload_limit\" ;\n") ;
	fwrite($fp, "\$C_attach1_ext = \"$C_attach1_ext\" ;\n") ;
	fwrite($fp, "\$C_attach2_ext = \"$C_attach2_ext\" ;\n\n") ;

	$C_global_frame_use = ($C_global_frame_use == "on")?"1":"0" ;  
	fwrite($fp, "\$C_global_frame_use = \"$C_global_frame_use\" ; \n") ;
	fwrite($fp, "\$C_box = \"$C_box\" ;\n") ;
	fwrite($fp, "\$C_BOX_START = \"$C_BOX_START\" ;\n") ;
	fwrite($fp, "\$C_BOX_END   = \"$C_BOX_END\" ;\n") ;
	fwrite($fp, "\$C_BOX_BR    = \"$C_BOX_BR\" ;\n") ;
	fwrite($fp, "\$C_BOX_DATA_START  = \"$C_BOX_DATA_START\" ;\n") ;
	fwrite($fp, "\$C_BOX_DATA_END    = \"$C_BOX_DATA_END\" ;\n\n") ;

	$C_global_category_use = ($C_global_category_use == "on")?"1":"0" ;  
	fwrite($fp, "\$C_global_category_use = \"$C_global_category_use\" ; \n") ;
	$MAX_CATEGORY = 10 ;
	if($C_category_all_use == "on") $C_category_all_use = "1" ;
	fwrite($fp, "\$C_category_all_use = \"$C_category_all_use\" ;\n") ;
	fwrite($fp, "\$C_category_name[0] = \"$C_category_name[0]\" ;\n") ;
	for($i = 1 ; $i <= $MAX_CATEGORY; $i++) 
	{
		if($C_category_use[$i] == "on") $C_category_use[$i] = "1" ;
		fwrite($fp, "\$C_category_use[$i] = \"$C_category_use[$i]\" ;\n") ;
		fwrite($fp, "\$C_category_name[$i] = \"$C_category_name[$i]\" ;\n") ;
	}
	fwrite($fp, "\n") ;
	fwrite($fp, "\$C_category_skin = \"$C_category_skin\" ;\n\n") ;


	$C_global_outer_use = ($C_global_outer_use == "on")?"1":"0" ;  
	fwrite($fp, "\$C_global_outer_use = \"$C_global_outer_use\" ; \n\n") ;
	$C_cat_outer_header_use = ( $C_cat_outer_header_use == "on" )?"1":"0" ; 
	fwrite($fp, "\$C_cat_outer_header_use = \"$C_cat_outer_header_use\" ;\n") ;
	$C_list_outer_header_use = ( $C_list_outer_header_use == "on" )?"1":"0" ; 
	fwrite($fp, "\$C_list_outer_header_use = \"$C_list_outer_header_use\" ;\n") ;
	$C_write_outer_header_use = ( $C_write_outer_header_use == "on" )?"1":"0" ; 
	fwrite($fp, "\$C_write_outer_header_use = \"$C_write_outer_header_use\" ;\n") ;
	$C_edit_outer_header_use = ( $C_edit_outer_header_use == "on" )?"1":"0" ; 
	fwrite($fp, "\$C_edit_outer_header_use = \"$C_edit_outer_header_use\" ;\n") ;
	$C_reply_outer_header_use = ( $C_reply_outer_header_use == "on" )?"1":"0" ; 
	fwrite($fp, "\$C_reply_outer_header_use = \"$C_reply_outer_header_use\" ;\n") ;
	fwrite($fp, "\$C_OUTER_HEADER[0] = \"$C_OUTER_HEADER[0]\" ;\n") ;
	fwrite($fp, "\$C_OUTER_HEADER[1] = \"$C_OUTER_HEADER[1]\" ;\n") ;
	fwrite($fp, "\$C_OUTER_FOOTER[0] = \"$C_OUTER_FOOTER[0]\" ;\n") ;
	fwrite($fp, "\$C_OUTER_FOOTER[1] = \"$C_OUTER_FOOTER[1]\" ;\n\n") ;

	$C_plugin_use = ( $C_plugin_use == "on" )?"1":"0" ;

	//SAVE PLUGIN_INSTALL LIST
	if($_debug) print_r($C_plugin_install) ;
	$_plugin_install = "" ;
	// convert array to string
	for( $i = 0; $i < sizeof($C_plugin_install); $i++ )
	{
		$_plugin_install .= $C_plugin_install[$i]."," ;
	}
	$C_plugin_install = $_plugin_install ;
	fwrite($fp, "\$C_plugin_install = \"$C_plugin_install\" ;\n\n") ;
	fwrite($fp, "?>") ;
	fclose($fp) ; 

	err_msg(_L_SAVE_COMPLETE) ;
	echo("<META HTTP-EQUIV=REFRESH CONTENT=\"0; URL='config_read.php?conf_name=$conf_name'\">") ;
	//echo("<script>self.close();</script>") ;
	exit ;
?>
