<?php
if(!defined("__wb_config__")) define("__wb_config__","1") ;
else return ;

/**
새로운 환경 변수를 추가하면 
처음 전역 스킨 변수부분에 하나 
일반 스킨 변수에 치환하는 부분에 하나
리턴할 변수에 하나 
이 세가지 부분에 추가해주어야 한다.
*/
function read_board_config($_data, $C_base_ = "") 
{
	$_debug = 0 ;
	if(empty($C_base_))
	{
		global $C_base ;
	}
	else
	{
		$C_base = $C_base_ ;
	}

	$conf = array("") ;

	$conf_file = "$C_base[dir]/board/conf/$_data.conf.php" ;
	$g_conf_file = "$C_base[dir]/board/conf/__global.conf.php" ;
	if (!file_exists($conf_file))
	{
		err_abort("read_board_config:$conf_file %s", _L_NOFILE) ;
	} 
	if (!file_exists($g_conf_file))
	{
		err_abort("read_board_config:$g_conf_file %s", _L_NOFILE) ;
	}

	//각 나라별로 필터되는 내용이 들어가 있도록 한다.
	$pre_filter = "복사,저주,행운\n
돈벌기,홈페이지,메일\n
추천,돈벌기\n
추천,적립,돈\n
광고,장사,돈\n
합법,돈,원\n
가입,추천,만원\n
가입,적립\n
대출,신용,증명\n
신용,카드,신청,발급\n
뉴머니,원,돈\n
donjunda.net\n
goindols.com\n
newmoney.co.kr\n
adhappy.co.kr\n
adhappy.com\n
netpoints.co.kr\n
assaweb.com\n
donnamu.co.kr\n
gamebusiness.net\n
cash,surfer\n
alladvantage\n
getpaid4\n
adity,cash\n
goldemail\n
mintmail.com\n
씨발,새끼\n
씨팔,새끼\n
미친,지랄\n
" ;


	//전역설정으로 치환하기 위한 준비
	include($g_conf_file) ;
	$g_skin                       =  empty($C_skin)?"wb_board":$C_skin  ;
	$g_board_title                =  $C_board_title  ;
	$g_table_size                 =  $C_table_size ;
	$g_table_align				=   $C_table_align ;
	$g_license_align           =  $C_license_align ; 
	$g_spam_check_use           =  $C_spam_check_use ; 
                                                                       
	$g_admin_perm                 =  !isset($C_admin_perm)?"7":$C_admin_perm ; 
	$g_group_perm                 =  !isset($C_group_perm)?"0":$C_group_perm ;
	$g_member_perm                =  !isset($C_member_perm)?"7":$C_member_perm ;
	$g_anonymous_perm             =  !isset($C_anonymous_perm)?"7":$C_anonymous_perm ;
	$g_auth_perm                  =  !isset($C_auth_perm)?"7077":$C_auth_perm ;
	$g_admin_reply_perm           =  !isset($C_admin_reply_perm)?"7":$C_admin_reply_perm ;
	$g_group_reply_perm           =  !isset($C_group_reply_perm)?"0":$C_group_reply_perm ;
	$g_member_reply_perm          =  !isset($C_member_reply_perm)?"7":$C_member_reply_perm ;
	$g_anonymous_reply_perm       =  !isset($C_anonymous_reply_perm)?"7":$C_anonymous_reply_perm ;
	$g_auth_reply_perm            =  !isset($C_auth_reply_perm)?"7077":$C_auth_reply_perm ;
	$g_admin_cat_perm             =  !isset($C_admin_cat_perm)?"7":$C_admin_cat_perm ;
	$g_group_cat_perm             =  !isset($C_group_cat_perm)?"0":$C_group_cat_perm ;
	$g_member_cat_perm            =  !isset($C_member_cat_perm)?"7":$C_member_cat_perm ;
	$g_anonymous_cat_perm         =  !isset($C_anonymous_cat_perm)?"7":$C_anonymous_cat_perm ;
	$g_auth_cat_perm              =  !isset($C_auth_cat_perm)?"7077":$C_auth_cat_perm ;
                                                                       
	$g_small_reply_use            =  !isset($C_small_reply_use)?1:$C_small_reply_use ;
	$g_cookie_use                 =  !isset($C_cookie_use)?1:$C_cookie_use ;
	$g_subject_html_use           =  $C_subject_html_use ;
	$g_name_html_use              =  $C_name_html_use ;
	$g_html_use                   =  $C_html_use ;
                                                                       
	$g_nCol                       =  !isset($C_nCol)?1:$C_nCol ; 
	$g_nRow                       =  !isset($C_nRow)?5:$C_nRow ; 
	$g_subject_max                =  $C_subject_max ;
	$g_comment_max                =  $C_comment_max ;
	$g_img_size_limit             =  $C_img_size_limit ;
	$g_url2link_use               =  !isset($C_url2link_use)?1:$C_url2link_use ;
	$g_sort_index                 =  !isset($C_sort_index)?10:$C_sort_index ;  
	$g_sort_order                 =  !isset($C_sort_order)?"desc":$C_sort_order ;
	$g_bg_img_use                 =  !isset($C_bg_img_use)?1:$C_bg_img_use ;
                                                                       
	$g_nNews                      =  $C_nNews ;
	$g_news_use                   =  !isset($C_news_use)?1:$C_news_use ;
	$g_news_nCol                  =  !isset($C_news_nCol)?1:$C_news_nCol ;
	$g_news_nRow                  =  !isset($C_news_nRow)?5:$C_news_nRow ;
	$g_news_subject_max           =  $C_news_subject_max ;
	$g_news_char_max              =  $C_news_char_max ;
	$g_news_skin                  =  $C_news_skin ;    
                                                                       
	$g_MAX_PAGE_SHOW              =  !isset($MAX_PAGE_SHOW)?5:$MAX_PAGE_SHOW ;
	$g_page_bar_align             =  !isset($C_page_bar_align)?"center":$C_page_bar_align ;
	$g_pagebar_skin               =  $C_pagebar_skin ; 
                                                                       
	$g_grad_start_color           =  $C_grad_start_color ;
	$g_grad_end_color             =  $C_grad_end_color ;
                                                                       
	$g_filter_name                =  !isset($C_filter_name)?"운영자,관리자":$C_filter_name ;
	$g_filter_subject            =  !isset($C_filter_subject)?"포르노,광고,카드연체,신용불량":$C_filter_subject ;
	$g_filter_type                =  $C_filter_type ;
	$g_block_tag                  =  !isset($C_block_tag)?"meta,xmp,":$C_block_tag ;
	$g_filter_ip_use              =  $C_filter_ip_use ;
	$g_filter_ip                  =  $C_filter_ip ;
	$g_filter_txt_use             =  !isset($C_filter_txt_use)?1:$C_filter_txt_use ;
	$g_filter_txt                 =  !isset($C_filter_txt)?$pre_filter:$C_filter_txt ; 
	$g_upload_limit               =  $C_upload_limit ;
	$g_attach1_ext                =  !isset($C_attach1_ext)?"gif,jpg,jpeg,png,bmp":$C_attach1_ext ;
	$g_attach2_ext                =  !isset($C_attach2_ext)?"zip,rar,tgz,gz,bz,bz2,tar":$C_attach2_ext ;
                                                                       
	if(!isset($C_box))
	{
		$C_box = "board" ;
		$C_BOX_START = "<table width=100% border=0 cellspacing=0 cellpadding=0><tr>" ;
		$C_BOX_END   = "</tr></table>" ;
		$C_BOX_BR    = "</tr><tr>" ;
		$C_BOX_DATA_START  = "<td>" ;
		$C_BOX_DATA_END    = "</td>" ;
	}
	$g_box                        =  $C_box ;
	$g_BOX_START                  =  $C_BOX_START ;
	$g_BOX_END                    =  $C_BOX_END ;
	$g_BOX_BR                     =  $C_BOX_BR ;
	$g_BOX_DATA_START             =  $C_BOX_DATA_START ;
	$g_BOX_DATA_END               =  $C_BOX_DATA_END ;
                                                                       
	$g_MAX_CATEGORY               =  ($MAX_CATEGORY==0)?10:$MAX_CATEGORY ;
	$g_category_all_use           =  $C_category_all_use ;
	$g_category_name              =  $C_category_name ;
	$g_category_use               =  $C_category_use ;
	$g_category_skin              =  $C_category_skin ;
                                                                      
	$g_cat_outer_header_use       =  $C_cat_outer_header_use ;
	$g_list_outer_header_use      =  $C_list_outer_header_use ;
	$g_write_outer_header_use     =  $C_write_outer_header_use ;
	$g_edit_outer_header_use      =  $C_edit_outer_header_use ;
	$g_reply_outer_header_use     =  $C_reply_outer_header_use ;
	$g_OUTER_HEADER               =  $C_OUTER_HEADER ;
	$g_OUTER_FOOTER               =  $C_OUTER_FOOTER ;
	
	$g_plugin_install             =  $C_plugin_install ;


	include($conf_file) ;
	$MAX_CATEGORY = ($MAX_CATEGORY==0)?10:$MAX_CATEGORY ;
	if ($C_global_use)
	{
		if ($_debug) echo("C_global_use:$C_global_use") ;
		$C_global_general_use = 1 ;
		$C_global_perm_use = 1 ;
		$C_global_write_use = 1 ;
		$C_global_list_use = 1 ;
		$C_global_news_use = 1 ;
		$C_global_pagebar_use = 1 ;
		$C_global_grad_use = 1 ;
		$C_global_filter_use = 1 ;
		$C_global_frame_use = 1 ;
		$C_global_category_use = 1 ;
		$C_global_outer_use = 1 ;
		$C_global_plugin_use = 1 ;
	} 
	if ($C_global_general_use)
	{ 
		if ($_debug) echo("C_global_general_use:$C_global_general_use<br>") ;
		$C_skin         = $g_skin ;
		$C_board_title  = $g_board_title ;
		$C_table_size   = $g_table_size ;
		$C_table_align	=   $g_table_align ;
		$C_license_align = $g_license_align ;
		$C_spam_check_use = $g_spam_check_use ;
	}
	if ($C_global_perm_use)
	{
		$C_admin_perm             = $g_admin_perm  ;
		$C_group_perm             = $g_group_perm ;
		$C_member_perm            = $g_member_perm ;
		$C_anonymous_perm         = $g_anonymous_perm ;
		$C_auth_perm              = $g_auth_perm ;
                                                             
		$C_admin_reply_perm       = $g_admin_reply_perm ;
		$C_group_reply_perm       = $g_group_reply_perm ;
		$C_member_reply_perm      = $g_member_reply_perm ;
		$C_anonymous_reply_perm   = $g_anonymous_reply_perm ;
		$C_auth_reply_perm        = $g_auth_reply_perm ;
                                                             
		$C_admin_cat_perm         = $g_admin_cat_perm ;
		$C_group_cat_perm         = $g_group_cat_perm ;
		$C_member_cat_perm        = $g_member_cat_perm ;
		$C_anonymous_cat_perm     = $g_anonymous_cat_perm ;
		$C_auth_cat_perm          = $g_auth_cat_perm ;            
	}
	if ($C_global_write_use)
	{
		$C_small_reply_use        = $g_small_reply_use ;
		$C_cookie_use             = $g_cookie_use ;
		$C_subject_html_use       = $g_subject_html_use ;
		$C_name_html_use          = $g_name_html_use ;
		$C_html_use               = $g_html_use ;    
	}
	if ($C_global_list_use)
	{ 
		$C_nCol                   = $g_nCol ;
		$C_nRow                   = $g_nRow ;
		$C_subject_max            = $g_subject_max ;
		$C_comment_max            = $g_comment_max ;
		$C_img_size_limit         = $g_img_size_limit ;
		$C_url2link_use           = $g_url2link_use ;
		$C_sort_index             = $g_sort_index ; 
		$C_sort_order             = $g_sort_order ;
		$C_bg_img_use             = $g_bg_img_use ;       
	}
	if ($C_global_news_use) 
	{
		$C_nNews                  = $g_nNews ;
		$C_news_use               = $g_news_use ;
		$C_news_nCol              = $g_news_nCol ;
		$C_news_nRow              = $g_news_nRow ;
		$C_news_subject_max       = $g_news_subject_max ;
		$C_news_char_max          = $g_news_char_max ;
		$C_news_skin              = $g_news_skin ;
	}
	if ($C_global_pagebar_use)
	{
		$MAX_PAGE_SHOW            =  $g_MAX_PAGE_SHOW ;
		$C_page_bar_align         =  $g_page_bar_align ;
		$C_pagebar_skin           =  $g_pagebar_skin ;        
	}
	if ($C_global_grad_use)
	{
		$C_grad_start_color       =  $g_grad_start_color ;
		$C_grad_end_color         =  $g_grad_end_color ;       
	}
	if ($C_global_filter_use)
	{
		$C_filter_name            =  $g_filter_name ;
		$C_filter_subject         =  $g_filter_subject ;
		$C_filter_type            =  $g_filter_type ;
		$C_block_tag              =  $g_block_tag ;
		$C_filter_ip_use          =  $g_filter_ip_use ;
		$C_filter_ip              =  $g_filter_ip ;
		$C_filter_txt_use         =  $g_filter_txt_use ;
		$C_filter_txt             =  $g_filter_txt ;
	}
	if ($C_global_upload_use)
	{
		$C_attach1_ext            =  $g_attach1_ext ;       
		$C_attach2_ext            =  $g_attach2_ext ;
		$C_upload_limit           =  $g_upload_limit ;     
	}
	if ($C_global_frame_use)
	{
		$C_box                    =  $g_box ;              
		$C_BOX_START              =  $g_BOX_START ;
		$C_BOX_END                =  $g_BOX_END ;
		$C_BOX_BR                 =  $g_BOX_BR ;
		$C_BOX_DATA_START         =  $g_BOX_DATA_START ;
		$C_BOX_DATA_END           =  $g_BOX_DATA_END ;         
	}
	if ($C_global_category_use)
	{
		$C_category_all_use       =  $g_category_all_use ;
		$C_category_use           =  $g_category_use ;
		$C_category_name          =  $g_category_name ;
		$C_category_skin          =  $g_category_skin ;      
		$MAX_CATEGORY             =  $g_MAX_CATEGORY ;
	}
	if ($C_global_outer_use)
	{
		$C_cat_outer_header_use    =  $g_cat_outer_header_use ;   
		$C_list_outer_header_use   =  $g_list_outer_header_use ;
		$C_write_outer_header_use  =  $g_write_outer_header_use ;
		$C_edit_outer_header_use   =  $g_edit_outer_header_use ;
		$C_reply_outer_header_use  =  $g_reply_outer_header_use ;
		$C_OUTER_HEADER            =  $g_OUTER_HEADER ;
		$C_OUTER_FOOTER            =  $g_OUTER_FOOTER ;             
	}
	if($C_global_plugin_use)
	{
		$C_plugin_install			= $g_plugin_install ;
	}

	//없는 경우 기본값 입력
	//리턴할 변수로 이동
	$conf[skin]                      =  empty($C_skin)?"wb_board":$C_skin  ;
	$conf[board_title]               =  $C_board_title  ;
	$conf[table_size]                =  $C_table_size ;
	$conf[table_align]					= $C_table_align ;
	$conf[license_align]             =  $C_license_align ;
	$conf[spam_check_use]             =  $C_spam_check_use ;
                                                                       
	$conf[admin_perm]                =  $C_admin_perm ; 
	$conf[group_perm]                =  $C_group_perm ;
	$conf[member_perm]               =  $C_member_perm ;
	$conf[anonymous_perm]            =  $C_anonymous_perm ;
	$conf[auth_perm]                 =  $C_auth_perm ;
	$conf[admin_reply_perm]          =  $C_admin_reply_perm ;
	$conf[group_reply_perm]          =  $C_group_reply_perm ;
	$conf[member_reply_perm]         =  $C_member_reply_perm ;
	$conf[anonymous_reply_perm]      =  $C_anonymous_reply_perm ;
	$conf[auth_reply_perm]           =  $C_auth_reply_perm ;
	$conf[admin_cat_perm]            =  $C_admin_cat_perm ;
	$conf[group_cat_perm]            =  $C_group_cat_perm ;
	$conf[member_cat_perm]           =  $C_member_cat_perm ;
	$conf[anonymous_cat_perm]        =  $C_anonymous_cat_perm ;
	$conf[auth_cat_perm]             =  $C_auth_cat_perm ;
                                                                       
	$conf[small_reply_use]           =  $C_small_reply_use ;
	$conf[cookie_use]                =  $C_cookie_use ;
	$conf[subject_html_use]          =  $C_subject_html_use ;
	$conf[name_html_use]             =  $C_name_html_use ;
	$conf[html_use]                  =  $C_html_use ;
                                                                       
	$conf[nCol]                      =  empty($C_nCol)?1:$C_nCol ; 
	$conf[nRow]                      =  empty($C_nRow)?5:$C_nRow ; 
	$conf[subject_max]               =  $C_subject_max ;
	$conf[comment_max]               =  $C_comment_max ;
	$conf[img_size_limit]            =  $C_img_size_limit ;
	$conf[url2link_use]              =  $C_url2link_use ;
	$conf[sort_index]                =  $C_sort_index ;  
	$conf[sort_order]                =  empty($C_sort_order)?"desc":$C_sort_order ;
	$conf[bg_img_use]                =  $C_bg_img_use ;
                                                                       
	$conf[nNews]                     =  $C_nNews ;
	$conf[news_use]                  =  $C_news_use  ;
	$conf[news_nCol]                 =  empty($C_news_nCol)?1:$C_news_nCol ;
	$conf[news_nRow]                 =  empty($C_news_nRow)?5:$C_news_nRow ;
	$conf[news_subject_max]          =  $C_news_subject_max ;
	$conf[news_char_max]             =  $C_news_char_max ;
	$conf[news_skin]                 =  $C_news_skin ;    
                                                                       
	$conf[MAX_PAGE_SHOW]             =  empty($MAX_PAGE_SHOW)?5:$MAX_PAGE_SHOW ;
	$conf[page_bar_align]            =  $C_page_bar_align ;
	$conf[pagebar_skin]              =  $C_pagebar_skin ; 
                                                                       
	$conf[grad_start_color]          =  $C_grad_start_color ;
	$conf[grad_end_color]            =  $C_grad_end_color ;
                                                                       
	$conf[filter_name]               =  $C_filter_name ;
	$conf[filter_subject]            =  $C_filter_subject ;
	$conf[filter_type]               =  $C_filter_type ;
	$conf[block_tag]                 =  $C_block_tag ;
	$conf[filter_ip_use]             =  $C_filter_ip_use ;
	$conf[filter_ip]                 =  $C_filter_ip ;
	$conf[filter_txt_use]            =  $C_filter_txt_use ;
	$conf[filter_txt]                =  $C_filter_txt ;
                                                                       
	$conf[upload_limit]              =  $C_upload_limit ;
	$conf[attach1_ext]               =  $C_attach1_ext ;
	$conf[attach2_ext]               =  $C_attach2_ext ;
                                                                       
	$conf[box]                       =  $C_box ;
	$conf[BOX_START]                 =  $C_BOX_START ;
	$conf[BOX_END]                   =  $C_BOX_END ;
	$conf[BOX_BR]                    =  $C_BOX_BR ;
	$conf[BOX_DATA_START]            =  $C_BOX_DATA_START ;
	$conf[BOX_DATA_END]              =  $C_BOX_DATA_END ;
                                                                       
	$conf[category_all_use]          =  $C_category_all_use ;
	$conf[category_use]              =  $C_category_use ;
	$conf[category_name]             =  $C_category_name ;
	$conf[MAX_CATEGORY]              =  $MAX_CATEGORY ;
	$conf[category_skin]             =  $C_category_skin ;
                                                                      
	$conf[cat_outer_header_use]      =  $C_cat_outer_header_use ;
	$conf[list_outer_header_use]     =  $C_list_outer_header_use ;
	$conf[write_outer_header_use]    =  $C_write_outer_header_use ;
	$conf[edit_outer_header_use]     =  $C_edit_outer_header_use ;
	$conf[reply_outer_header_use]    =  $C_reply_outer_header_use ;
	$conf[OUTER_HEADER]              =  $C_OUTER_HEADER ;
	$conf[OUTER_FOOTER]              =  $C_OUTER_FOOTER ;

	$conf[plugin_install]			= explode(",",$C_plugin_install) ;

	$conf[list_php]					= empty($LIST_PHP)?"list.php":$LIST_PHP ;
	$conf[write_php] 				= empty($WRITE_PHP)?"write.php":$WRITE_PHP ;
	$conf[delete_php]				= empty($DELETE_PHP)?"delete.php":$DELETE_PHP ;
	$conf[cat_php]                  = empty($CAT_PHP)?"cat.php":$CAT_PHP ;

	//print_r($conf) ;
	return $conf ;
}


function read_counter_config($_data) 
{
	$_debug = 0 ;
	global $C_base ;
	$conf = array("") ;

	$conf_file = "$C_base[dir]/counter/conf/$_data.conf.php" ;
	$g_conf_file = "$C_base[dir]/counter/conf/__global.conf.php" ;
	if (!file_exists($conf_file))
	{
		err_abort("read_counter_config:$conf_file %s", _L_NOFILE) ;
	} 
	if (!file_exists($g_conf_file))
	{
		err_abort("read_counter_config:$g_conf_file %s", _L_NOFILE) ;
	}

	//전역설정으로 치환하기 위한 준비
	include($g_conf_file) ;
	$g_skin                       =  empty($C_skin)?"wc_white":$C_skin  ;
	$g_cookie_time                =  $C_cookie_time ;
	$g_popup_func                 =  $C_popup_func ;

	$g_view_yesterday             =  $C_view_yesterday ;
	$g_view_today                 =  $C_view_today ;
	$g_view_month                 =  $C_view_month ;
	$g_view_year                  =  $C_view_year ;
	$g_view_total                 =  $C_view_total ;
	$g_view_max                   =  $C_view_max ;

	$g_total_base                 =  $C_total_base ;

	$g_event_point                =  $C_event_point ;
	$g_event_url                  =  $C_event_url ;
                                                                       
	include($conf_file) ;
	if ($C_global_use)
	{
		if ($_debug) echo("C_global_use:$C_global_use") ;
		$C_global_general_use = 1 ;
		$C_global_event_use = 1 ;
		$C_global_view_use = 1 ;
		$C_global_data_use = 1 ;
	} 
	if ($C_global_general_use)
	{ 
		if ($_debug) echo("C_global_general_use:$C_global_general_use<br>") ;
		$C_skin         = $g_skin ;
		$C_cookie_time  =  $g_cookie_time ;
		$C_popup_func   =  $g_popup_func ;
	}
	if ($C_global_view_use)
	{
		if ($_debug) echo("C_global_view_use:$C_global_view_use<br>") ;
		$C_view_yesterday  =  $g_view_yesterday ;
		$C_view_today      =  $g_view_today ;
		$C_view_month      =  $g_view_month ;
		$C_view_year       =  $g_view_year ;
		$C_view_total      =  $g_view_total ;
		$C_view_max        =  $g_view_max ;
	}
	if ($C_global_data_use)
	{
		if ($_debug) echo("C_global_data_use:$C_global_data_use<br>") ;
		$C_total_base      = $g_total_base ;
	}
	if ($C_global_event_use)
	{
		if ($_debug) echo("C_global_event_use:$C_global_event_use<br>") ;
		$C_event_point  =  $g_event_point ;
		$C_event_url    =  $g_event_url ;
	}

	//없는 경우 기본값 입력
	//리턴할 변수로 이동
	$conf[skin]                      =  empty($C_skin)?"wc_white":$C_skin  ;

	$conf[cookie_time]               =  $C_cookie_time ;
	$conf[popup_func]                =  $C_popup_func ;


	$conf[event_point]               =  $C_event_point ;
	$conf[event_url]                 =  $C_event_url ;
 
	$conf[view_yesterday]            =  $C_view_yesterday ;
	$conf[view_today]                =  $C_view_today ;
	$conf[view_month]                =  $C_view_month ;
	$conf[view_year]                 =  $C_view_year ;
	$conf[view_total]                =  $C_view_total ;
	$conf[view_max]                  =  $C_view_max ;

	$conf[total_base]                =  $C_total_base ;
                                                                      
	//print_r($conf) ;
	return $conf ;
}

function read_member_config($_data) 
{
	$_debug = 0 ;
	global $C_base ;
	$conf = array("") ;

	$conf_file = "$C_base[dir]/member/conf/$_data.conf.php" ;

	$g_conf_file = "$C_base[dir]/member/conf/__global.conf.php" ;
	if (!file_exists($conf_file))
	{
		err_abort("read_board_config:$conf_file %s", _L_NOFILE) ;
	} 
	
	if (!file_exists($g_conf_file))
	{
		//err_abort("read_board_config:$g_conf_file %s", _L_NOFILE) ;
	}
	$pre_filter = "" ;


	//전역설정으로 치환하기 위한 준비
	//include($g_conf_file) ;
	$g_skin                       =  empty($C_skin)?"wb_board":$C_skin  ;
	$g_board_title                =  $C_board_title  ;
	$g_table_size                 =  $C_table_size ;
                                                                       
	$g_admin_perm                 =  !isset($C_admin_perm)?"7":$C_admin_perm ; 
	$g_group_perm                 =  !isset($C_group_perm)?"0":$C_group_perm ;
	$g_member_perm                =  !isset($C_member_perm)?"7":$C_member_perm ;
	$g_anonymous_perm             =  !isset($C_anonymous_perm)?"7":$C_anonymous_perm ;
	$g_auth_perm                  =  !isset($C_auth_perm)?"7077":$C_auth_perm ;
	$g_admin_reply_perm           =  !isset($C_admin_reply_perm)?"7":$C_admin_reply_perm ;
	$g_group_reply_perm           =  !isset($C_group_reply_perm)?"0":$C_group_reply_perm ;
	$g_member_reply_perm          =  !isset($C_member_reply_perm)?"7":$C_member_reply_perm ;
	$g_anonymous_reply_perm       =  !isset($C_anonymous_reply_perm)?"7":$C_anonymous_reply_perm ;
	$g_auth_reply_perm            =  !isset($C_auth_reply_perm)?"7077":$C_auth_reply_perm ;
	$g_admin_cat_perm             =  !isset($C_admin_cat_perm)?"7":$C_admin_cat_perm ;
	$g_group_cat_perm             =  !isset($C_group_cat_perm)?"0":$C_group_cat_perm ;
	$g_member_cat_perm            =  !isset($C_member_cat_perm)?"7":$C_member_cat_perm ;
	$g_anonymous_cat_perm         =  !isset($C_anonymous_cat_perm)?"7":$C_anonymous_cat_perm ;
	$g_auth_cat_perm              =  !isset($C_auth_cat_perm)?"7077":$C_auth_cat_perm ;
                                                                       
	$g_small_reply_use            =  !isset($C_small_reply_use)?1:$C_small_reply_use ;
	$g_cookie_use                 =  !isset($C_cookie_use)?1:$C_cookie_use ;
	$g_subject_html_use           =  $C_subject_html_use ;
	$g_name_html_use              =  $C_name_html_use ;
	$g_html_use                   =  $C_html_use ;
                                                                       
	$g_nCol                       =  !isset($C_nCol)?1:$C_nCol ; 
	$g_nRow                       =  !isset($C_nRow)?5:$C_nRow ; 
	$g_subject_max                =  $C_subject_max ;
	$g_comment_max                =  $C_comment_max ;
	$g_img_size_limit             =  $C_img_size_limit ;
	$g_url2link_use               =  !isset($C_url2link_use)?1:$C_url2link_use ;
	$g_sort_index                 =  !isset($C_sort_index)?10:$C_sort_index ;  
	$g_sort_order                 =  !isset($C_sort_order)?"desc":$C_sort_order ;
	$g_bg_img_use                 =  !isset($C_bg_img_use)?1:$C_bg_img_use ;
                                                                       
	$g_nNews                      =  $C_nNews ;
	$g_news_use                   =  !isset($C_news_use)?1:$C_news_use ;
	$g_news_nCol                  =  !isset($C_news_nCol)?1:$C_news_nCol ;
	$g_news_nRow                  =  !isset($C_news_nRow)?5:$C_news_nRow ;
	$g_news_subject_max           =  $C_news_subject_max ;
	$g_news_char_max              =  $C_news_char_max ;
	$g_news_skin                  =  $C_news_skin ;    
                                                                       
	$g_MAX_PAGE_SHOW              =  !isset($MAX_PAGE_SHOW)?5:$MAX_PAGE_SHOW ;
	$g_page_bar_align             =  !isset($C_page_bar_align)?"center":$C_page_bar_align ;
	$g_pagebar_skin               =  $C_pagebar_skin ; 
                                                                       
	$g_grad_start_color           =  $C_grad_start_color ;
	$g_grad_end_color             =  $C_grad_end_color ;
                                                                       
	$g_filter_name                =  !isset($C_filter_name)?"운영자,관리자":$C_filter_name ;
	$g_filter_type                =  $C_filter_type ;
	$g_block_tag                  =  !isset($C_block_tag)?"meta,xmp,":$C_block_tag ;
	$g_filter_ip_use              =  $C_filter_ip_use ;
	$g_filter_ip                  =  $C_filter_ip ;
	$g_filter_txt_use             =  !isset($C_filter_txt_use)?1:$C_filter_txt_use ;
	$g_filter_txt                 =  !isset($C_filter_txt)?$pre_filter:$C_filter_txt ; 
	$g_upload_limit               =  $C_upload_limit ;
	$g_attach1_ext                =  !isset($C_attach1_ext)?"gif,jpg,jpeg,png,bmp":$C_attach1_ext ;
	$g_attach2_ext                =  !isset($C_attach2_ext)?"zip,rar,tgz,gz,bz,bz2,tar":$C_attach2_ext ;
                                                                       
	if(!isset($C_box))
	{
		$C_box = "board" ;
		$C_BOX_START = "<table width=100% border=0 cellspacing=0 cellpadding=0><tr>" ;
		$C_BOX_END   = "</tr></table>" ;
		$C_BOX_BR    = "</tr><tr>" ;
		$C_BOX_DATA_START  = "<td>" ;
		$C_BOX_DATA_END    = "</td>" ;
	}
	$g_box                        =  $C_box ;
	$g_BOX_START                  =  $C_BOX_START ;
	$g_BOX_END                    =  $C_BOX_END ;
	$g_BOX_BR                     =  $C_BOX_BR ;
	$g_BOX_DATA_START             =  $C_BOX_DATA_START ;
	$g_BOX_DATA_END               =  $C_BOX_DATA_END ;
                                                                       
	$g_MAX_CATEGORY               =  ($MAX_CATEGORY==0)?10:$MAX_CATEGORY ;
	$g_category_all_use           =  $C_category_all_use ;
	$g_category_name              =  $C_category_name ;
	$g_category_use               =  $C_category_use ;
	$g_category_skin              =  $C_category_skin ;
                                                                      
	$g_cat_outer_header_use       =  $C_cat_outer_header_use ;
	$g_list_outer_header_use      =  $C_list_outer_header_use ;
	$g_write_outer_header_use     =  $C_write_outer_header_use ;
	$g_edit_outer_header_use      =  $C_edit_outer_header_use ;
	$g_reply_outer_header_use     =  $C_reply_outer_header_use ;
	$g_OUTER_HEADER               =  $C_OUTER_HEADER ;
	$g_OUTER_FOOTER               =  $C_OUTER_FOOTER ;
	
	$g_plugin_install             =  $C_plugin_install ;


	include($conf_file) ;
	$MAX_CATEGORY = ($MAX_CATEGORY==0)?10:$MAX_CATEGORY ;
	if ($C_global_use)
	{
		if ($_debug) echo("C_global_use:$C_global_use") ;
		$C_global_general_use = 1 ;
		$C_global_perm_use = 1 ;
		$C_global_write_use = 1 ;
		$C_global_list_use = 1 ;
		$C_global_news_use = 1 ;
		$C_global_pagebar_use = 1 ;
		$C_global_grad_use = 1 ;
		$C_global_filter_use = 1 ;
		$C_global_frame_use = 1 ;
		$C_global_category_use = 1 ;
		$C_global_outer_use = 1 ;
		$C_global_plugin_use = 1 ;
	} 
	if ($C_global_general_use)
	{ 
		if ($_debug) echo("C_global_general_use:$C_global_general_use<br>") ;
		$C_skin         = $g_skin ;
		$C_board_title  = $g_board_title ;
		$C_table_size   = $g_table_size ;
	}
	if ($C_global_perm_use)
	{
		$C_admin_perm             = $g_admin_perm  ;
		$C_group_perm             = $g_group_perm ;
		$C_member_perm            = $g_member_perm ;
		$C_anonymous_perm         = $g_anonymous_perm ;
		$C_auth_perm              = $g_auth_perm ;
                                                             
		$C_admin_reply_perm       = $g_admin_reply_perm ;
		$C_group_reply_perm       = $g_group_reply_perm ;
		$C_member_reply_perm      = $g_member_reply_perm ;
		$C_anonymous_reply_perm   = $g_anonymous_reply_perm ;
		$C_auth_reply_perm        = $g_auth_reply_perm ;
                                                             
		$C_admin_cat_perm         = $g_admin_cat_perm ;
		$C_group_cat_perm         = $g_group_cat_perm ;
		$C_member_cat_perm        = $g_member_cat_perm ;
		$C_anonymous_cat_perm     = $g_anonymous_cat_perm ;
		$C_auth_cat_perm          = $g_auth_cat_perm ;            
	}
	if ($C_global_write_use)
	{
		$C_small_reply_use        = $g_small_reply_use ;
		$C_cookie_use             = $g_cookie_use ;
		$C_subject_html_use       = $g_subject_html_use ;
		$C_name_html_use          = $g_name_html_use ;
		$C_html_use               = $g_html_use ;    
	}
	if ($C_global_list_use)
	{ 
		$C_nCol                   = $g_nCol ;
		$C_nRow                   = $g_nRow ;
		$C_subject_max            = $g_subject_max ;
		$C_comment_max            = $g_comment_max ;
		$C_img_size_limit         = $g_img_size_limit ;
		$C_url2link_use           = $g_url2link_use ;
		$C_sort_index             = $g_sort_index ; 
		$C_sort_order             = $g_sort_order ;
		$C_bg_img_use             = $g_bg_img_use ;       
	}
	if ($C_global_news_use) 
	{
		$C_nNews                  = $g_nNews ;
		$C_news_use               = $g_news_use ;
		$C_news_nCol              = $g_news_nCol ;
		$C_news_nRow              = $g_news_nRow ;
		$C_news_subject_max       = $g_news_subject_max ;
		$C_news_char_max          = $g_news_char_max ;
		$C_news_skin              = $g_news_skin ;
	}
	if ($C_global_pagebar_use)
	{
		$MAX_PAGE_SHOW            =  $g_MAX_PAGE_SHOW ;
		$C_page_bar_align         =  $g_page_bar_align ;
		$C_pagebar_skin           =  $g_pagebar_skin ;        
	}
	if ($C_global_grad_use)
	{
		$C_grad_start_color       =  $g_grad_start_color ;
		$C_grad_end_color         =  $g_grad_end_color ;       
	}
	if ($C_global_filter_use)
	{
		$C_filter_name            =  $g_filter_name ;
		$C_filter_type            =  $g_filter_type ;
		$C_block_tag              =  $g_block_tag ;
		$C_filter_ip_use          =  $g_filter_ip_use ;
		$C_filter_ip              =  $g_filter_ip ;
		$C_filter_txt_use         =  $g_filter_txt_use ;
		$C_filter_txt             =  $g_filter_txt ;
	}
	if ($C_global_upload_use)
	{
		$C_attach1_ext            =  $g_attach1_ext ;       
		$C_attach2_ext            =  $g_attach2_ext ;
		$C_upload_limit           =  $g_upload_limit ;     
	}
	if ($C_global_frame_use)
	{
		$C_box                    =  $g_box ;              
		$C_BOX_START              =  $g_BOX_START ;
		$C_BOX_END                =  $g_BOX_END ;
		$C_BOX_BR                 =  $g_BOX_BR ;
		$C_BOX_DATA_START         =  $g_BOX_DATA_START ;
		$C_BOX_DATA_END           =  $g_BOX_DATA_END ;         
	}
	if ($C_global_category_use)
	{
		$C_category_all_use       =  $g_category_all_use ;
		$C_category_use           =  $g_category_use ;
		$C_category_name          =  $g_category_name ;
		$C_category_skin          =  $g_category_skin ;      
		$MAX_CATEGORY             =  $g_MAX_CATEGORY ;
	}
	if ($C_global_outer_use)
	{
		$C_cat_outer_header_use    =  $g_cat_outer_header_use ;   
		$C_list_outer_header_use   =  $g_list_outer_header_use ;
		$C_write_outer_header_use  =  $g_write_outer_header_use ;
		$C_edit_outer_header_use   =  $g_edit_outer_header_use ;
		$C_reply_outer_header_use  =  $g_reply_outer_header_use ;
		$C_OUTER_HEADER            =  $g_OUTER_HEADER ;
		$C_OUTER_FOOTER            =  $g_OUTER_FOOTER ;             
	}
	if($C_global_plugin_use)
	{
		$C_plugin_install			= $g_plugin_install ;
	}

	//없는 경우 기본값 입력
	//리턴할 변수로 이동
	$conf[skin]                      =  empty($C_skin)?"wb_board":$C_skin  ;
	$conf[board_title]               =  $C_board_title  ;
	$conf[table_size]                =  $C_table_size ;
                                                                       
	$conf[admin_perm]                =  $C_admin_perm ; 
	$conf[group_perm]                =  $C_group_perm ;
	$conf[member_perm]               =  $C_member_perm ;
	$conf[anonymous_perm]            =  $C_anonymous_perm ;
	$conf[auth_perm]                 =  $C_auth_perm ;
	$conf[admin_reply_perm]          =  $C_admin_reply_perm ;
	$conf[group_reply_perm]          =  $C_group_reply_perm ;
	$conf[member_reply_perm]         =  $C_member_reply_perm ;
	$conf[anonymous_reply_perm]      =  $C_anonymous_reply_perm ;
	$conf[auth_reply_perm]           =  $C_auth_reply_perm ;
	$conf[admin_cat_perm]            =  $C_admin_cat_perm ;
	$conf[group_cat_perm]            =  $C_group_cat_perm ;
	$conf[member_cat_perm]           =  $C_member_cat_perm ;
	$conf[anonymous_cat_perm]        =  $C_anonymous_cat_perm ;
	$conf[auth_cat_perm]             =  $C_auth_cat_perm ;
                                                                       
	$conf[small_reply_use]           =  $C_small_reply_use ;
	$conf[cookie_use]                =  $C_cookie_use ;
	$conf[subject_html_use]          =  $C_subject_html_use ;
	$conf[name_html_use]             =  $C_name_html_use ;
	$conf[html_use]                  =  $C_html_use ;
                                                                       
	$conf[nCol]                      =  empty($C_nCol)?1:$C_nCol ; 
	$conf[nRow]                      =  empty($C_nRow)?5:$C_nRow ; 
	$conf[subject_max]               =  $C_subject_max ;
	$conf[comment_max]               =  $C_comment_max ;
	$conf[img_size_limit]            =  $C_img_size_limit ;
	$conf[url2link_use]              =  $C_url2link_use ;
	$conf[sort_index]                =  $C_sort_index ;  
	$conf[sort_order]                =  empty($C_sort_order)?"desc":$C_sort_order ;
	$conf[bg_img_use]                =  $C_bg_img_use ;
                                                                       
	$conf[nNews]                     =  $C_nNews ;
	$conf[news_use]                  =  $C_news_use  ;
	$conf[news_nCol]                 =  empty($C_news_nCol)?1:$C_news_nCol ;
	$conf[news_nRow]                 =  empty($C_news_nRow)?5:$C_news_nRow ;
	$conf[news_subject_max]          =  $C_news_subject_max ;
	$conf[news_char_max]             =  $C_news_char_max ;
	$conf[news_skin]                 =  $C_news_skin ;    
                                                                       
	$conf[MAX_PAGE_SHOW]             =  empty($MAX_PAGE_SHOW)?5:$MAX_PAGE_SHOW ;
	$conf[page_bar_align]            =  $C_page_bar_align ;
	$conf[pagebar_skin]              =  $C_pagebar_skin ; 
                                                                       
	$conf[grad_start_color]          =  $C_grad_start_color ;
	$conf[grad_end_color]            =  $C_grad_end_color ;
                                                                       
	$conf[filter_name]               =  $C_filter_name ;
	$conf[filter_type]               =  $C_filter_type ;
	$conf[block_tag]                 =  $C_block_tag ;
	$conf[filter_ip_use]             =  $C_filter_ip_use ;
	$conf[filter_ip]                 =  $C_filter_ip ;
	$conf[filter_txt_use]            =  $C_filter_txt_use ;
	$conf[filter_txt]                =  $C_filter_txt ;
                                                                       
	$conf[upload_limit]              =  $C_upload_limit ;
	$conf[attach1_ext]               =  $C_attach1_ext ;
	$conf[attach2_ext]               =  $C_attach2_ext ;
                                                                       
	$conf[box]                       =  $C_box ;
	$conf[BOX_START]                 =  $C_BOX_START ;
	$conf[BOX_END]                   =  $C_BOX_END ;
	$conf[BOX_BR]                    =  $C_BOX_BR ;
	$conf[BOX_DATA_START]            =  $C_BOX_DATA_START ;
	$conf[BOX_DATA_END]              =  $C_BOX_DATA_END ;
                                                                       
	$conf[category_all_use]          =  $C_category_all_use ;
	$conf[category_use]              =  $C_category_use ;
	$conf[category_name]             =  $C_category_name ;
	$conf[MAX_CATEGORY]              =  $MAX_CATEGORY ;
	$conf[category_skin]             =  $C_category_skin ;
                                                                      
	$conf[cat_outer_header_use]      =  $C_cat_outer_header_use ;
	$conf[list_outer_header_use]     =  $C_list_outer_header_use ;
	$conf[write_outer_header_use]    =  $C_write_outer_header_use ;
	$conf[edit_outer_header_use]     =  $C_edit_outer_header_use ;
	$conf[reply_outer_header_use]    =  $C_reply_outer_header_use ;
	$conf[OUTER_HEADER]              =  $C_OUTER_HEADER ;
	$conf[OUTER_FOOTER]              =  $C_OUTER_FOOTER ;

	$conf[plugin_install]			= explode(",",$C_plugin_install) ;

	$conf[list_php]					= empty($LIST_PHP)?"list.php":$LIST_PHP ;
	$conf[write_php] 				= empty($WRITE_PHP)?"write.php":$WRITE_PHP ;
	$conf[delete_php]				= empty($DELETE_PHP)?"delete.php":$DELETE_PHP ;
	$conf[cat_php]                  = empty($CAT_PHP)?"cat.php":$CAT_PHP ;

	//print_r($conf) ;
	return $conf ;
}
?>
