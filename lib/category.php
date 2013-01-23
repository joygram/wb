<?php
if(!defined("__wb_category__")) define("__wb_category__","1") ;
else return ;
/**
	카테고리 코드에 해당하는 이름 불러오기
*/
	function category_name($_data, $type, $base_dir = "." )
	{
		$_debug = 0 ;
		if( empty($type) ) 
		{
			return $type ;
		}
		require_once("$base_dir/lib/wb.inc.php") ;
		$conf = read_board_config($_data) ;
		$C_skin = $conf[skin] ;
		$C_data = $_data ;

		if($_debug) echo("type[$type] base_dir[$base_dir] _data[$_data]<br>") ;
			//이내용 이해가 가지 않음.
		$cate_name = is_numeric($type)?$conf[category_name][$type]:"" ;
		if($_debug) echo("[".$conf[category_name][$type]."]<br>") ;

		//$cate_name = $conf[category_name][$type] ;
		if($_debug) echo("conf[category_name][$type][".$conf[category_name][$type]."]<br>") ;
		return $cate_name ; 
	}

/**
	echo category select bar 
*/
	function category_select($_data, $type) 
	{
		$_debug = 1 ;
		global $C_base ;

		$category_cnt = 0 ;
		$category_header = "<select name='type' class='wCate'>" ;
		$category_body_start = "" ;
		$category_body_end = "" ;
		$category_footer = "</select>" ;

		require_once("$C_base[dir]/lib/wb.inc.php") ;
		$conf = read_board_config($_data) ;
		$C_skin = $conf[skin] ;
		$C_data = $_data ;

		if (empty($conf[category_skin]))
			$_skindir = "$C_base[dir]/board/skin/$conf[skin]/category" ;
		else
			$_skindir = "$C_base[dir]/board/skin/__global/category/$conf[category_skin]" ;
		if( !@file_exists("$_skindir/category_select.html") ) 
		{
			$no_category_file = "1" ;
		}
		ob_start() ;
		if($no_category_file == "1")
		{
			echo("$category_header\n") ;
		}
		else
		{
			include("$_skindir/category_select_header") ;
		}
		for($i = 0 ; $i < $conf[MAX_CATEGORY] ; $i++)
		{
			$selected = "" ;
			if( $conf[category_use][$i] == "1" )
			{
				if( $type == $i ) $selected = "selected" ;
				$category_cnt++ ;
				if($no_category_file == "1")
				{
					echo("<option value='$i' $selected>".$conf[category_name][$i]."</option>\n") ;
				}
				else
				{
					$Row[category_name] = $conf[category_name][$i] ;
					include("$_skindir/category_select.html") ;
				}
			}
		}
		if($no_category_file == "1")
		{
			echo("$category_footer") ;
		}
		else
		{
			include("$_skindir/category_select_footer") ;
		}
		if( $category_cnt > 0 )
		{
			$category = ob_get_contents() ;	
		}
		else
		{
			$category = "" ;
		}
		ob_end_clean() ;
		flush() ;

		return $category ;
	}

/**
*/
	function category_list($_data, $LIST_URL) 
	{
		$_debug = 0 ;
		global $C_base ;
		global $br ;
		global $filter_type ;

		$category_cnt = 0 ;
		$category_header = "<table cellpadding='0' cellspacing='0' border='0' class='wDefault' ><tr>" ;
		$category_body_start = "<td nowrap>" ;
		$category_body_end = "</td>" ;
		$category_footer = "</tr></table>" ;

		require_once("$C_base[dir]/lib/wb.inc.php") ;
		$conf = read_board_config($_data) ;
		$C_skin = $conf[skin] ;
		$C_data = $_data ;

		if ($_debug) echo("conf[MAX_CATEGORY]:$conf[MAX_CATEGORY]:<br>") ;
		if (empty($conf[category_skin]))
			$_skindir = "$C_base[dir]/board/skin/$conf[skin]/category" ;
		else
			$_skindir = "$C_base[dir]/board/skin/__global/category/$conf[category_skin]" ;
		if( !@file_exists("$_skindir/category_header") || !@file_exists("$_skindir/category_list.html") || !@file_exists("$_skindir/category_footer") )
		{
			$no_category_file = "1" ;
		}
		ob_start() ;
		if ($no_category_file == "1")
		{
			echo("$category_header\n") ;
		}
		else
		{
			include("$_skindir/category_header") ;
		}
		if ($_debug) echo("conf[MAX_CATEGORY][$conf[MAX_CATEGORY]]<br>") ;
		for($i = 0 ; $i <= $conf[MAX_CATEGORY] ; $i++)
		{
			if ($_debug) echo("i[$i]") ;
			if($i == 0)
			{
				if(!isset($conf[category_all_use])) 
				{
					$conf[category_all_use] = 1 ;
					$conf[category_name][$i] = "ALL" ;
				}
				$conf[category_use][$i] = $conf[category_all_use] ;
			}
			if( $conf[category_use][$i] == "1" )
			{
				$category_cnt++ ;
				if($no_category_file == "1")
				{
					echo("$category_body_start <a href='$LIST_URL&filter_type=$i'>[".$conf[category_name][$i]."]</a>&nbsp; $category_body_end") ;
				}
				else
				{
					$Row[category_selected] = "" ;
					$URL['list'] = "$LIST_URL&filter_type=$i" ;
					$URL['list'] .= !empty($br_use)?"&br_use=$br_use":"" ;
					$Row[category_name] = $conf[category_name][$i] ;
					if( $filter_type == $i )
						$Row[category_selected] = "selected" ;
					include("$_skindir/category_list.html") ;
				}
			}
		}
		if($no_category_file == "1")
		{
			echo("$category_footer") ;
		}
		else
		{
			include("$_skindir/category_footer") ;
		}
		if ($_debug) echo("category_cnt[$category_cnt]<br>") ;
		$category = ob_get_contents() ;	
		if( $category_cnt > 1 )
		{
			$category = ob_get_contents() ;	
		}
		else
		{
			$category = "" ;
		}
		ob_end_clean() ;
		flush() ;

		return $category ;
	}
?>
