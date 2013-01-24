<?php
if(!defined("__wb_make_news__")) define("__wb_make_news__","1") ;
else return ;
	//////////////////////////////////////
	//WB�� �ֱ� �Խù� �����ϱ�
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
		//�ֱٰԽù� ��Ų�� ������ �������� �ʴ´�.
		//240���������� ��Ų�� news.html�� ������ �����Ǵ�����.
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

		//�������� ȣȯ�� ����
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
		//�˻���� ���� db_board������ ���� ���� ������?
		//�˻����� �ƹ��͵� �Է����� ������ ��ü����� ������ �ؾ� �ϹǷ�
		$mode = (empty($key))?"":$mode ;
		$mode = ($filter_type > "0")?"find":$mode ;
		$mode = ($conf[sort_order]=="asc" && $conf[sort_order] == 0)?"find":$mode ;
		$mode = ($conf[sort_index] != 0)?"find":$mode ;
		if($_debug) echo("LIST:mode[$mode] filter_type:$filter_type<br>") ;
			//�⺻ �˻� �ʵ� name
		$field = empty($field)?"name":$field ;

		$dbi = new db_board($_data, "index", $mode, $filter_type, $key, $field, "file", "2", $C_base[dir] ) ;
		$dbi->count_data() ;
		$dbi->select_data($line_begin, $conf[nNews]) ;

		$nPos = 0 ; //�˻��� ���  ���� br�� ���ؼ� ���� 
		$news_content = "" ;

		$URL['list'] = "$C_base[url]/board/$conf[list_php]?data=$_data" ;
		ob_start() ; //����� ���ڿ��� �ϱ�
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
				break ; //������ ������ �о��� ���
			}
			//if( $mode == "find" && $Row['name'] != $key )
			//{
			//	$line_end++ ; // ������ �� ����
			//	continue ; //ã���� ��� �̸��� ���� ������ ���
			//}
			if( !empty($conf[news_subject_max]) )
			{
				$Row['subject'] = cutting($Row[subject], $conf[news_subject_max]) ;
				//���� ������ ���̰� �ٸ��� ...�� �ٿ�����.
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
			// ob_start�� ����� ������ �ȵǾ �������� �̵� 2002/05/15
			ob_start() ; //����� ���ڿ��� �ϱ�
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
		//flush() ; //���۸� ������ �Ǵ� �ý��ۿ��� ��� ������
		//�Ϻ� �ý��ۿ����� ob_start��ɾ���� 
		//ob_end_clean()�� ���� ���� �ݱ⸦ �ϸ� �����ߴ��� �ȴ�. [30�ʿ���]
        //������ ����
		// flush()�Լ��� ȣ���ϰų� echo�� �ϸ� �����ߴ��� ������.
		$news_file = "$_datadir/news.txt" ;
		$fp = wb_fopen($news_file, "w") ;
		fwrite($fp, $news_content) ;
		fclose($fp) ;
	}
?>
