<?php
if(!defined("__wb_make_url__")) define("__wb_make_url__","1") ;
else return ;
	///////////////////////
	// WB의 링크 URL만들기 
	///////////////////////
	function make_url($_data, $Row, $wb_prg="board", $to ="list.php")
	{
		global $C_base ;
		global $W_SES ; //세션 변수
		global $auth ;
		global $auth_param ;
		//global $cur_page, $tot_page ;
		//global $PHP_SELF ;

		global $WRITE_URL, $EDIT_URL, $REPLY_URL ;
		global $LIST_URL, $DELETE_URL, $CAT_URL ;
		global $ATTACH_URL, $ATTACH2_URL, $ATTACH_FILE, $ATTACH2_FILE ;
		global $DOWNLOAD_URL, $DOWNLOAD2_URL, $HOMEPAGE_URL ;

		require_once("$C_base[dir]/lib/config.php") ;
		$read_config = "read_{$wb_prg}_config" ;
		$conf = $read_config($_data) ;

        //backword compatibility
		$C_skin = $conf[skin] ;
		$C_data = $_data ;
		$LIST_PHP = $conf[list_php] ;
		$WRITE_PHP = $conf[write_php] ; 
		$DELETE_PHP = $conf[delete_php] ;

		$DOWNLOAD_PHP = (empty($DOWNLOAD_PHP))?"download.php":$DOWNLOAD_PHP ;
		$CAT_PHP = (empty($CAT_PHP))?"cat.php":$CAT_PHP ;

		$LIST_URL  = "$C_base[url]/$wb_prg/$LIST_PHP?data=$_data" ;
		$LIST_URL .= !empty($Row[cur_page])?"&cur_page=$Row[cur_page]":"" ;
		$LIST_URL .= !empty($Row[filter_type])?"&filter_type=$Row[filter_type]":"" ;

		$CAT_URL = "$C_base[url]/$wb_prg/$CAT_PHP?data=$_data&board_group=$Row[board_group]" ;
		$CAT_URL .= !empty($Row[cur_page])?"&cur_page=$Row[cur_page]":"" ;
		$CAT_URL .= !empty($Row[filter_type])?"&filter_type=$Row[filter_type]":"" ;

		$WRITE_URL = "$C_base[url]/$wb_prg/$WRITE_PHP?data=$_data" ;

		$EDIT_URL = "$C_base[url]/$wb_prg/$WRITE_PHP?data=$_data&mode=edit&board_group=$Row[board_group]&board_id=$Row[board_id]" ;
		$EDIT_URL .= "&to=$to" ;
		$EDIT_URL .= !empty($Row[cur_page])?"&cur_page=$Row[cur_page]":"" ;
		$EDIT_URL .= !empty($Row[is_main_writing])?"&main_writing=$Row[is_main_writing]":"" ;
		$EDIT_URL .= !empty($Row[filter_type])?"&filter_type=$Row[filter_type]":"" ;
		$REPLY_URL = "$C_base[url]/$wb_prg/$WRITE_PHP?data=$_data&mode=reply_form&board_group=$Row[board_group]&board_id=$Row[board_id]" ;
		$REPLY_URL .= !empty($Row[cur_page])?"&cur_page=$Row[cur_page]":"" ;
		$REPLY_URL .= !empty($Row[filter_type])?"&filter_type=$Row[filter_type]":"" ;

		$DELETE_URL = "$C_base[url]/$wb_prg/$DELETE_PHP?data=$_data&board_group=$Row[board_group]&board_id=$Row[board_id]" ;
		$DELETE_URL .= !empty($Row[cur_page])?"&cur_page=$Row[cur_page]":"" ;
		$DELETE_URL .= !empty($Row[is_main_writing])?"&main_writing=$Row[is_main_writing]":"" ;
		$DELETE_URL .= !empty($Row[filter_type])?"&filter_type=$Row[filter_type]":"" ;
		
		//가급적이면 쓰지 않도록 유도
		$ATTACH_FILE  = "$C_base[dir]/data/$_data/$Row[board_group].$Row[InputFile_name]_attach" ;
		$ATTACH2_FILE = "$C_base[dir]/data/$_data/$Row[board_group].$Row[InputFile2_name]_attach2" ;
		
		//count_pos : 이전버젼 스킨과 호환성을 유지시키기 위해서..
		// 1번 첨부파일 3 
		// 2번 첨부파일 2 : 
		$no_image_path = "skin/$conf[skin]/images/noimage.gif" ;
		$no_image = 0 ;
		
		//첫번째 첨부파일 링크
		$attach_filename  = "$Row[board_group].$Row[InputFile_name]_attach" ;
		$ATTACH_URL = $attach_filename ;

		$pos = strstr($body[InputFile_type], "image") ;
		//이미지파일이 없다면  
		if( $pos == false && empty($Row[InputFile_name]) && !@file_exists("data/$_data/$attach_filename") )
		{
			if( @file_exists($no_image_path) )
			{
				$attach_filename = $no_image_path ;
			}
			else
			{
				$attach_filename = "images/noimage.gif" ;
			}
			$no_image = 1 ;
			$no_img = 1 ;
		}
			//첫번째 이미지 URL만들기
		if($C_base[os] == "Unix")
		{
			$IMG_URL = "$C_base[url]/$wb_prg/$DOWNLOAD_PHP?file=$attach_filename&board_group=$Row[board_group]&file_type=$Row[InputFile_type]&file_name=$Row[InputFile_name]&data=$_data&count_pos=0&no_image=$no_image" ;
		}
		else
		{
			$IMG_URL = "$C_base[url]/$wb_prg/data/$_data/$attach_filename" ;
		}

		$DOWNLOAD_URL = "$C_base[url]/$wb_prg/$DOWNLOAD_PHP?file=$attach_filename&board_group=$Row[board_group]&data=$_data&count_pos=2&file_name=$Row[InputFile_name]" ;

			//두번째 첨부파일 링크
		$no_image = 0 ;
		$attach2_filename = "$Row[board_group].$Row[InputFile2_name]_attach2" ;
		$ATTACH2_URL = $attach2_filename ;
		
		$pos = strstr($body[InputFile2_type], "image") ;
		if( $pos == false && empty($Row[InputFile2_name]) && !@file_exists("data/$_data/$body[board_group].$body[InputFile2_name]_attach2") )
		{
			if( @file_exists($no_image_path) )
			{
				$attach2_filename = $no_image_path ;
			}
			else
			{
				$attach2_filename = "images/noimage.gif" ;
			}

			$no_image = 1 ;
			$no_img2 = 1 ;
		}
			//프로그램으로 변경
		
			//두번째 이미지 URL만들기
		if($C_base[os] == "Unix")
		{
			$IMG2_URL = "$C_base[url]/$wb_prg/$DOWNLOAD_PHP?file=$attach2_filename&board_group=$Row[board_group]&file_type=$Row[InputFile2_type]&file_name=$Row[InputFile2_name]&data=$_data&count_pos=0&no_image=$no_image" ;
		}
		else
		{
			$IMG2_URL = "$C_base[url]/$wb_prg/data/$_data/$attach2_filename" ;
		}


		$DOWNLOAD2_URL = "$C_base[url]/$wb_prg/$DOWNLOAD_PHP?file=$attach2_filename&board_group=$Row[board_group]&data=$_data&count_pos=3&file_name=$Row[InputFile2_name]" ;

		$HOMEPAGE_URL = "$C_base[url]/$wb_prg/goto.php?data=$_data&board_group=$Row[board_group]&url=$Row[homepage]&count_pos=1" ;

		$LINK_URL = "$C_base[url]/$wb_prg/goto.php?data=$_data&board_group=$Row[board_group]&url=$Row[link]" ;

		$url = array( 
			"write"     => $WRITE_URL,
			"edit"      => $EDIT_URL,
			"reply"     => $REPLY_URL,
			"list"      => $LIST_URL,
			"delete"    => $DELETE_URL,
			"cat"       => $CAT_URL,
			"attach"    => $ATTACH_URL,
			"attach2"   => $ATTACH2_URL,
			"download"  => $DOWNLOAD_URL,
			"download2" => $DOWNLOAD2_URL,
			"img"		=> $IMG_URL,
			"img2"		=> $IMG2_URL,
			"no_img"	=> $no_img,
			"no_img2"	=> $no_img2,
			"attach_filename" => $attach_filename,
			"attach2_filename" => $attach2_filename,
			"homepage"  => $HOMEPAGE_URL,
			"link"      => $LINK_URL,
			"skin"      => "$C_base[url]/board/skin/$conf[skin]"
			) ;

		if($auth->is_anonymous()) // not login
		{
			$url['login'] = $PHP_SELF."?".param2url(base64_decode($auth_param))."log=on&" ;
		}
		else
		{
			$url['logout'] = $PHP_SELF."?".param2url(base64_decode($auth_param))."log=off&" ;
			$url['config']  = "../admin/board/config_open.php?conf_name={$_data}.conf.php";

		}
	
		
		$url['avatar'] = "$C_base[url]/member/$Row[avatar]" ;

			// 2002/04/21 email output encoding
		$to_mail = base64_encode($Row[email]) ;
		$url['sendmail'] = "$C_base[url]/sendmail.php?to=$to_mail" ;
		$url['admin'] = "$C_base[url]/admin/index.php" ;
		return $url ;
	}
?>
