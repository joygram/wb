<?php
if(!defined("__wb_reply_list__")) define("__wb_reply_list__","1") ;
else return ;
	//아주 오래전 버젼의 스킨 호환성을 유지 시켜주기 위해서...
	function reply_list($_data, $board_group)
	{
		global $C_skin ;
		err_abort(_L_REPLACE_REPLYLIST) ;
	}
	////////////////////////////////////////////
	//WB의 답글 리스트를 출력함. 
	////////////////////////////////////////////
	function wb_reply_list($data, $board_group = "", $board_id = "", $mode = "output", $base_dir = "." )
	{ 
		$_debug = 0 ;
		//global $C_base ;
		global $PHP_SELF ;
		global $cur_page, $tot_page ;

		//previous URL 
		global $WRITE_URL,    $EDIT_URL,    $REPLY_URL ;
		global $LIST_URL,     $DELETE_URL,  $CAT_URL ;
		global $ATTACH_URL,   $ATTACH2_URL ;
		global $ATTACH_FILE,  $ATTACH2_FILE ;
		global $DOWNLOAD_URL, $DOWNLOAD2_URL ;
		global $HOMEPAGE_URL ;

		// previous hide
		global $bg_exist_start,      $bg_exist_end ;
		global $home_exist_start,    $home_exist_end ;
		global $email_exist_start,   $email_exist_end ;
		global $attach_exist_start,  $attach_exist_end ; 
		global $attach2_exist_start, $attach2_exist_end ;
		global $link_exist_start,    $link_exist_end ;
		global $reply_hide_start,    $reply_hide_end ;

		if( empty($data) )
		{
			err_abort("wb_reply_list: %s", _L_NOVAR_DATA) ; 
		}	
		else
		{
			$_data = $data ;
		}

		if($_debug) echo("wb_reply_list:[$_data] board_group[$board_group] board_id[$board_id]<br>\n") ;
		if( empty($board_group) )
		{
			err_abort("wb_reply_list: %s", _L_NOVAR_GROUP) ; 
		}	

		$C_base[dir] = $base_dir ;
		require_once("$C_base[dir]/lib/config.php") ;
		$conf = read_board_config($_data) ;
		$C_skin = $conf[skin] ;

		$_skindir = "$C_base[dir]/board/skin/$conf[skin]" ;
		//echo("[$C_base[dir]][$base_dir]") ;
		// 답글의 개수 만큼 반복하기
		$flist = new file_list("$C_base[dir]/board/data/$_data/", 1) ;
		$dbi = new db_board($_data, "index", $mode, $type, $key, $field, "file", "2", $base_dir ) ;
		$flist->read("$board_group") ;

		ob_start() ;
		if( @file_exists("$_skindir/reply_list.html") )
		{
			echo("<table border=0 width=100%>\n") ;
		}
		$skip = 1 ;
		$get_cnt = 0 ;
		while( ($file_name = $flist->next()) )
		{
			if($get_cnt > $flist->cnt )
			{
				if($_debug) error_log("[$log_date]cnt over\n", 3, "logs/error_log") ;
				break ;
			}
			$get_cnt++ ;

			$tmp = explode(".", $file_name) ;
			$cur_board_id =  ".".$tmp[1] ;

			if( $_debug ) echo("loop: [$board_group][$cur_board_id][$file_name]<br>\n") ;

			if( strstr($file_name, "attach") ) { continue ; }

			if( $skip == 1 && $board_id == $cur_board_id) 
			{ 
				if($_debug) echo("first writing is skipped<br>\n") ;
				$skip = 0 ; 
				continue ;	
			}	//처음글 통과

			$Row = $dbi->row_fetch_array(0, $board_group, $cur_board_id) ;
			if($Row == -1)
			{
				break ;
			}


			if( @file_exists("$_skindir/reply_list.html") )
			{
				if($conf[url2link_use] == "1") 
				{
					$Row['comment'] = url2link( $Row['comment'] ) ;
				}
				$Row['is_main_writing'] = 0 ;
				$Row['cur_page'] = $cur_page ;
				$Row['tot_page'] = $tot_page ;
				$URL = make_url($_data, $Row) ;
				if( $URL[no_img] == "1" )
				{
					$size = @GetImageSize($URL[attach_filename]) ;
					$Row['img_width'] = $size[0] ;
					$Row['img_height'] = $size[1] ;
				}
				if( $URL[no_img2] == "1" )
				{
					$size = @GetImageSize($URL[attach2_filename]) ;
					$Row[img2_width] = $size[0] ;
					$Row[img2_height] = $size[1] ;
				}
				
			//reply 에서 <br /> 태그에 대한 문제로 수정
			//	$Row['comment'] = nl2br($Row[comment]) ;
				if($Row['br_use'] == "no")
				{				
				}
				else
				{
					if( $Row['html_use'] == HTML_NOTUSE || $Row['br_use'] != "no" ) 
					{				
						$Row['comment'] = nl2br($Row[comment]) ;
						$Row['comment'] = str_replace("  ", "&nbsp;&nbsp;", $Row[comment]) ;
						$Row['comment'] = str_replace("\t", "&nbsp;&nbsp;&nbsp;&nbsp;", $Row[comment]) ;
					}
				}
			//reply 에서 <br /> 태그에 대한 문제로 삽입

				$hide = make_comment($_data, $Row) ;

				echo("<tr><td>\n") ;
				include ("$_skindir/reply_list.html") ;
				echo("</td></tr>\n") ;
			}
			else
			{
				if( $_debug ) echo("2-1<br>\n") ;
		
				$reply_list .= "<br>".$Row[name]." … ".$Row['comment'] ;
			}
		} // end of while 

		if($_debug) echo("end of while") ;
		if( @file_exists("$_skindir/reply_list.html") )
		{
			echo("</table>\n") ;

			$reply_list = ob_get_contents() ;	
			ob_end_clean() ;
			flush() ;
		}

		if( $mode == "output" )
		{
			echo ("$reply_list") ;
		}

		//if($_debug) echo("$reply_list") ;

		return $reply_list ;
	}
?>
