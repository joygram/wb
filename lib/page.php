<?php
if(!defined("__wb_page__")) define("__wb_page__","1") ;
else return ;
	////////////////////////////////////////////
	// 총 페이지 수를 계산
	////////////////////////////////////////////
	function get_total_page($nTotal_line, $one_page)
	{
		$total_page = (int)($nTotal_line / $one_page) ;

		$more = $nTotal_line % $one_page ;
		if( $more > 0  )
		{
			$total_page ++ ;
		}
		if($total_page == 0) $total_page = 1 ;
		return $total_page ; 
	}


	////////////////////////////////////////////
	// 페이지 이동 막대 표시
	////////////////////////////////////////////
	function wb_page_bar($data, $cur_page, $tot_page, $key, $field, $mode, $wb_prg="board")
	{
		$_debug = 0 ;
		global $C_base ;
		global $br_use ;
		global $filter_type ;
		$Row = array("") ;
		if($tot_page <= 1 )
		{
			return ;
		}
		$_data = $data ;

		require_once("$C_base[dir]/lib/config.php") ;
		$read_config = "read_{$wb_prg}_config" ;
		$conf = $read_config($_data) ;
		$C_skin = $conf[skin] ;

		if (empty($conf[pagebar_skin]))
			$_skindir = "$C_base[dir]/$wb_prg/skin/$conf[skin]/pagebar" ;
		else
			$_skindir = "$C_base[dir]/$wb_prg/skin/__global/pagebar/$conf[pagebar_skin]" ;

		if ($_debug) echo("_skindir[$_skindir]<br>") ;
		$URL = array("") ; 
		$BASE_LIST = "$C_base[url]/$wb_prg/$conf[list_php]?data=$data" ;
		$BASE_LIST .= !empty($mode)?"&mode=$mode":"" ;
		$BASE_LIST .= !empty($key)?"&key=$key":"" ;
		$BASE_LIST .= !empty($field)?"&field=$field":"" ;
		$BASE_LIST .= !empty($filter_type)?"&filter_type=$filter_type":"" ;
		$BASE_LIST .= !empty($br_use)?"&br_use=$br_use":"" ;
		$page_bar_str = "" ;
		$page_begin = 0 ;
		$page_end = 0 ;

		$no_pagebar_file = 0 ;
		$pagebar_header = "<table border=0 cellpadding=0 cellspacing=1 align='$conf[page_bar_align]'><tr>" ;
		$pagebar_body_start = "<td class='page_bar' nowrap>" ;
		$pagebar_selected_body_start = "<td class='page_bar' nowrap bgcolor='#555555'>" ;
		$pagebar_body_end = "</td>" ;
		$pagebar_footer = "</tr></table>" ;

		$page_begin = ((int)($cur_page / $conf[MAX_PAGE_SHOW])) * $conf[MAX_PAGE_SHOW] ;
		$page_end   = $page_begin + $conf[MAX_PAGE_SHOW] ;

		$URL['page_prev'] = $BASE_LIST."&cur_page=".($cur_page-1) ;
		$URL['page_next'] = $BASE_LIST."&cur_page=".($cur_page+1) ;

		if(!@file_exists("$_skindir/pagebar_header") )
		{
			$no_pagebar_file = 1 ;
		}
		ob_start() ;
		if( $no_pagebar_file == "1")
		{
			echo ("$pagebar_header") ;
		}
		else
		{
			$Row["pagebar_align"] = $conf[page_bar_align] ;
			include("$_skindir/pagebar_header") ;
		}

 		if ( $page_begin > 0 )
		{
			$URL['list'] = $BASE_LIST."&cur_page=".($page_begin-1) ; 
			if( $no_pagebar_file == "1")
			{
				echo ("$pagebar_body_start") ;
				echo ( "<a href='".$URL['list']."'>&lt;&lt;</a>\n" );
				echo ("$pagebar_body_end") ;
			}
			else
			{
				$Row[page] = "&lt;&lt;" ;
				include("$_skindir/pagebar_list.html") ;
			}
 		}
		else 
		{
			if($no_pagebar_file == "1")
			{
				echo ("$pagebar_body_start") ;
				echo ("&nbsp;&nbsp;\n") ;
				echo ("$pagebar_body_end") ;
			}
		}
		for ( $i = $page_begin ; $i < $page_end ; $i++)
		{
			if ( $i >= $tot_page )
			{
				echo ("$pagebar_body_start") ;
				echo ("$pagebar_body_end") ;
				break ;
			}
			if ( $i == $cur_page )
			{
				if( $no_pagebar_file == "1")
				{
					echo ("$pagebar_body_start") ;
					echo ("&nbsp; <font id='page_bar'> [".($i+1)."] </font>&nbsp;" );
					echo ("$pagebar_body_end") ;
				}
				else
				{
					$Row[page] = ($i + 1) ;
					include("$_skindir/pagebar_selected.html") ;
				}
			}
			else
			{
				$URL['list'] = $BASE_LIST."&cur_page=".($i) ; 
				if( $no_pagebar_file == "1")
				{
					echo ("$pagebar_body_start") ;
					echo ("<a href='".$URL['list']."'>&nbsp; ".($i+1)." &nbsp;</a>\n");
					echo ("$pagebar_body_end") ;
				}
				else
				{
					$Row[page] = ($i + 1) ;
					include("$_skindir/pagebar_list.html") ;
				}
			}
		} // end of for

		if( $tot_page > $page_end )
 		{

			$URL['list'] = $BASE_LIST."&cur_page=".($page_end) ; 
			if( $no_pagebar_file == "1")
			{
				echo ("$pagebar_body_start") ;
				echo ("<a href='".$URL['list']."'>&gt&gt</a>\n")  ;
				echo ("$pagebar_body_end") ;
			}
			else
			{
				$Row[page] = "&gt;&gt;" ;
				include("$_skindir/pagebar_list.html") ;
			}
   		}

		if( $no_pagebar_file == "1")
		{
			echo("$pagebar_footer") ;
		}
		else
		{
			$Row[page] = "&lt;&lt;" ;
			include("$_skindir/pagebar_footer") ;
		}

		$pagebar_list = ob_get_contents() ;	
		ob_end_clean() ;
		flush() ;
		return $pagebar_list ;
	}
?>
