<?php
if(!defined("__wb_make_news__")) define("__wb_make_news__","1") ;
else return ;
	//////////////////////////////////////
	//WB의 최근 게시물 저장하기
	/////////////////////////////////////
	function make_news( $_data, $Row ) 
	{
		global $C_base ;
		global $PHP_SELF ;

		global $WRITE_URL,    $EDIT_URL,   $REPLY_URL ;
		global $LIST_URL,     $DELETE_URL, $CAT_URL ;
		global $ATTACH_URL,   $ATTACH2_URL ;
		global $ATTACH_FILE,  $ATTACH2_FILE ;
		global $DOWNLOAD_URL, $DOWNLOAD2_URL ;
		global $HOMEPAGE_URL ;

		global $bg_exist_start,      $bg_exist_end ;
		global $home_exist_start,    $home_exist_end ;
		global $email_exist_start,   $email_exist_end ;
		global $attach_exist_start,  $attach_exist_end ; 
		global $attach2_exist_start, $attach2_exist_end ;
		global $link_exist_start,    $link_exist_end ;
		global $reply_hide_start,    $reply_hide_end ;

		$_debug = 0 ;
		$data = $_data ;
		require_once("$C_base[dir]/lib/io.php") ;
		require_once("$C_base[dir]/lib/config.php") ;
		$conf = read_board_config($_data) ;
		$C_skin = $conf[skin] ;

		if ($conf[news_skin])
		{
			$_skindir = "$C_base[dir]/board/skin/__global/news/$conf[news_skin]" ;	
		}
		else
		{
			$_skindir = "$C_base[dir]/board/skin/$conf[skin]" ;	
			if (file_exists("$_skindir/news") && is_dir("$_skindir/news"))
			{
				$_skindir = "$C_base[dir]/board/skin/$conf[skin]/news" ;	
			}
		}
		$_datadir = "$C_base[dir]/board/data/$_data" ;
		//최근게시물 스킨이 없으면 생성하지 않는다.
		//240버젼에서는 스킨에 news.html의 유무로 생성판단했음.
		if (isset($conf[news_use]))
		{
			if (!$conf[news_use]) 
				return ; 	
		}
		else
		{
			if (!@file_exists("$_skindir/news.html")) 
			{
				return ;
			}
		}
		$cnt_file = file("$_datadir/total.cnt") ;
		$nTotal = $cnt_file[0] ;

		//이전버젼 호환성 유지
		if(empty($conf[news_nCol]))
		{
			$conf[news_nCol] = $conf[nNews] ;
			$conf[news_nRow] = 1 ;
		}
		$conf[nNews] = $conf[news_nCol] * $conf[news_nRow] ; 
		if (empty($conf[nNews]))
		{
			$conf[nNews] = 4 ;
		}
		$line_end = $conf[nNews] ; 
		if( $line_end > $nTotal )
		{
			$line_end = $nTotal ;	
		}
		//검색모드 설정 db_board안으로 들어가야 하지 않을까?
		//검색란에 아무것도 입력하지 않으면 전체목록을 나오게 해야 하므로
		$mode = (empty($key))?"":$mode ;
		$mode = ($filter_type > "0")?"find":$mode ;
		$mode = ($conf[sort_order]=="asc" && $conf[sort_order] == 0)?"find":$mode ;
		$mode = ($conf[sort_index] != 0)?"find":$mode ;
		if($_debug) echo("LIST:mode[$mode] filter_type:$filter_type<br>") ;
			//기본 검색 필드 name
		$field = empty($field)?"name":$field ;

		$dbi = new db_board($_data, "index", $mode, $filter_type, $key, $field, "file", "2", $C_base[dir] ) ;
		$dbi->count_data() ;
		$dbi->select_data($line_begin, $conf[nNews]) ;

		$nPos = 0 ; //검색할 경우  라인 br을 위해서 선언 
		$news_content = "" ;

		$URL['list'] = "$C_base[url]/board/$conf[list_php]?data=$_data" ;
		ob_start() ; //출력을 문자열로 하기
		if (file_exists("$_skindir/news_header"))
			include("$_skindir/news_header") ;
		else
			echo("$conf[BOX_START]\n") ; 	
		$news_content = ob_get_contents() ;	
		ob_end_clean() ;
		for($i = 0 ; $i < $line_end ; $i ++)
		{
			$Row = $dbi->row_fetch_array($i) ;
			if($Row == -1)
			{
				echo("make_news:row is -1<br>") ;
				break ; //파일을 끝까지 읽었을 경우
			}
			//if( $mode == "find" && $Row['name'] != $key )
			//{
			//	$line_end++ ; // 끝나는 것 무시
			//	continue ; //찾기일 경우 이름이 같지 않으면 통과
			//}
			if( !empty($conf[news_subject_max]) )
			{
				$Row['subject'] = cutting($Row[subject], $conf[news_subject_max]) ;
				//이전 제목의 길이가 다르면 ...을 붙여주자.
				if( strlen($Row[subject]) >= $conf[news_subject_max] )
				{
					$Row['subject'] .= "..." ;
				}
			}
			if( !empty($conf[news_char_max]) )
			{
				$Row['comment'] = cutting($Row[comment], $conf[news_char_max]) ;
			}
			// correct this.
			$Row['comment'] = nl2br($Row[comment]) ;
			$Row['status'] = $status ;
			$Row['is_main_writing'] = 1 ;
			//$Row['cur_page'] = $cur_page ;
			//$Row['tot_page'] = $tot_page ;
			$URL = make_url($_data, $Row) ;
			if( $URL[no_img] == "1" )
			{
				$size = GetImageSize($URL[attach_filename]) ;
				$Row['img_width'] = $size[0] ;
				$Row['img_height'] = $size[1] ;
			}
			if( $URL[no_img2] == "1" )
			{
				$size = GetImageSize($URL[attach2_filename]) ;
				$Row[img2_width] = $size[0] ;
				$Row[img2_height] = $size[1] ;
			}
			$Row['data'] = $_data ;
			$hide = make_comment($_data, $Row) ;
			// ob_start가 제대로 적용이 안되어서 루프내로 이동 2002/05/15
			ob_start() ; //출력을 문자열로 하기
			if (!file_exists("$_skindir/news_header"))
				echo("$conf[BOX_DATA_START]\n\n") ;
			include("$_skindir/news.html") ;
			if (!file_exists("$_skindir/news_header"))
				echo("$conf[BOX_DATA_END]\n\n") ;
			if( ($nPos % $conf[news_nCol]) == ($conf[news_nCol]-1) )
			{
				echo("$conf[BOX_BR]\n\n") ;
			}
			$news_content .= ob_get_contents() ;	
			ob_end_clean() ;
			$nPos++ ;
		} // end of for
		ob_start() ;
		if (file_exists("$_skindir/news_footer"))
			include("$_skindir/news_footer") ;
		else
			echo("$conf[BOX_END]\n") ; 	
		$news_content .= ob_get_contents() ;	
		ob_end_clean() ;
		flush() ;
		//flush() ; //버퍼링 문제가 되는 시스템에서 출력 보도록
		//일부 시스템에서는 ob_start명령어사용시 
		//ob_end_clean()를 쓴후 파일 닫기를 하면 수행중단이 된다. [30초에러]
        //순서에 유의
		// flush()함수를 호출하거나 echo를 하면 수행중단이 없어짐.
		$news_file = "$_datadir/news.txt" ;
		$fp = wb_fopen($news_file, "w") ;
		fwrite($fp, $news_content) ;
		fclose($fp) ;
	}
?>
