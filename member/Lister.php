<?php
require_once("../lib/WhiteBBS_Suite.php") ;

class Lister extends WhiteBBS_Suite
{
	
	function Lister( $table, $auth )
	{
		// 전체 데이터 수 계산 : 페이지 바를 위해서.. 
		// DB를 사용하지 않기 때문에 이부분에 시간이 많이 허비됨.
		$this->_dbi = new db_member($C_data, "member", $mode, $filter_type, $key, $field, $C_base[member_db_type], "2", $C_base[dir] ) ;

			// select하기전에는 total값을 구할 수 없기 때문에...
			// dbi class에서 limit값을 구할 수 있는 방법이 없다.
		$this->_dbi->count_data() ;

		$tot_page = get_total_page( $this->_dbi->total, $C_nCol*$C_nRow ) ;
		if($C_debug) echo("total[$this->_dbi->total], TOT_PAGE:[$tot_page]<br>") ;

			// 가지고온 전체 데이터의 개수와 현재 페이지가 일치하지 않으면 현재 페이지를 최초로 reset시킨다.	
			// page control variable set
		$cur_page = ($cur_page < 0 )?0:$cur_page ;
		$cur_page = ($cur_page >= $tot_page )?0:$cur_page ;

			// offset calc
		$line_begin = $cur_page * ($C_nCol * $C_nRow) ;
		if($C_debug) echo("line_begin[$line_begin] cur_page[$cur_page]<br>") ;

		$this->_dbi->select_data($line_begin, $C_nCol * $C_nRow) ;

			// category list 2001/12/09
		$URL['list'] = "$C_base[url]/member/list.php?data=$C_data" ;
		//$Row['category_list'] = category_list($C_data, $URL['list']) ;
			//머리말에 들어갈 변수들
		$Row['nTotal']   = $this->_dbi->total ; 
		$Row['cur_page'] = empty($cur_page)?1:$cur_page ;
		$Row['tot_page'] = $tot_page ;
		$Row['play_list'] = $play_list ; //음악 선택곡 목록
		

		$hide = make_comment($C_data, $Row, NOT_USE, "member") ;
		
	}
7	
	
	function list()
	{
		$nPos = $start ; //검색할 경우  라인 br을 위해서 선언 
		$nCnt = $line_begin ; // 넘버링을 위한 숫자
		if($C_debug) echo("[$this->_dbi->row_begin][$this->_dbi->row_end]<br>") ;
		echo("$C_BOX_START") ;
		for($i = $this->_dbi->row_begin ; $i < $this->_dbi->row_end ; $i ++)
		{
			///////////////////////////////////////
				//1.
			$Row = $this->_dbi->row_fetch_array($i) ;
			if( $Row == -1)
			{
				echo("Row [$i]th is -1<br>") ;
				break ;
			}

			$Row['no'] = $this->_dbi->total - $nCnt ;

			$Row['cur_page'] = $cur_page ;
			$Row['tot_page'] = $tot_page ;
			$Row['filter_type'] = $filter_type ;


				//plug_in 처리 필요 2003/06/13
			$Row['name'] = $Row[firstname].$Row['lastname'] ;
			$Row['mobilephone'] = mobile_phone($Row[mobilephone]) ;
			$Row['sex'] = $Row[sex]?"남":"여" ;
			$result = get_department($Row[interest_department]) ;
			$Row['interest_department'] = $result["name"] ;
			$Row['job'] = get_job($Row[job_kind]) ;

			if(!$auth->is_anonymous())
			{
				//$Row['alias'] = $auth->alias() ;
			}
				//2.
			$URL = make_url($C_data, $Row, "member") ;
			if( $URL[no_img] == "1" )
			{
				if(@file_exists($URL[attach_filename]))
					$size = GetImageSize($URL[attach_filename]) ;
				$Row['img_width'] = $size[0] ;
				$Row['img_height'] = $size[1] ;
			}
			if( $URL[no_img2] == "1" )
			{
				if(@file_exists($URL[attach2_filename]))
					$size = GetImageSize($URL[attach2_filename]) ;
				$Row[img2_width] = $size[0] ;
				$Row[img2_height] = $size[1] ;
			}
				//3.
			$hide = make_comment($C_data, $Row, $i, "member") ;

			echo("$C_BOX_DATA_START") ;
			include "$C_skindir/{$list}.html" ;
			echo("$C_BOX_DATA_END") ;
			if( ($nPos % $C_nCol) == ($C_nCol-1) )
			{
				echo("$C_BOX_BR") ;
			}
			$nPos++ ;
			$nCnt++ ;
		}
		echo("$C_BOX_END") ;

		$this->_dbi->destroy() ;
	}
	
} ;
?>