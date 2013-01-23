<?php
if(!defined("__wb_db_member_file__")) define("__wb_db_member_file__","1") ;
else return ;

global $C_base ;
global $__SERVER, $__GET, $__POST, $__COOKIE, $__FILES, $__ENV, $__SESSION ;

require_once("$C_base[dir]/member/Member.php") ;

/**
///////////////////////////////////////////
// Database Interface & file data handling
// 2002/02
// 2002/03/15 
//	index파일명 자동 변환
**/
//$version = "WhiteBoard 2.3.0 2002/02/04" ;

class db_member
{
	var $debug ;
	var $version ; 		// class version
	var $db_type ;		// 데이터베이스 타입
	var $db_ver ; 		// 보드 데이터의 버젼: 2, 3 
	var $data ;			// 보드 데이터 이름
	var $table ;		// 사용하고자하는 테이블이름
	// sort and filtering
	var $mode ;			// 검색모드 
	var $find_type ;	//
	var $find_key ;
	var $find_field ;

	var $total ;		// 전체 데이터의 수 
	var $num_rows ;		// 선택한 자료의 수 
	var $rows ;			// 자료 배열
	// 경우에 따라서 시작과 끝이 동일 하지 않기 때문에 사용.
	var $row_begin ;    // 배열의 시작번호
	var $row_end ;		// 배열의 끝번호

	var $base_dir ;		//기준 디렉토리
	var $base_url ; 	//기준 URL
	var $conf_name ;	// configuration name

	var $db_conn ;      // 데이터베이스 연결변수 

	function db_member($data, $table, $mode = "", $find_type = "", $find_key = "", $find_field = "", $db_type = "file", $ver = "2", $base_dir = ".", $sort_field="", $sort_order="ASC" ) 
	{
		$this->init($data, $table, $mode, $find_type, $find_key, $find_field, $db_type, $ver, $base_dir, $sort_field, $sort_order ) ;

		include("$this->base_dir/system.ini.php") ;
	}

	function init($data, $table, $mode = "", $find_type = "", $find_key = "", $find_field = "", $db_type = "file", $ver = "2", $base_dir = ".", $sort_field="", $sort_order="ASC" ) 
	{
		global $C_base ;//temp 

		$this->debug = 1 ;

		$this->version    = "db_member 0.3 2005/10" ;
		$this->db_ver     = $ver ;
		$this->db_type    = $db_type ;
		$this->data       = $data ;
		$this->table 	  = $table ;
		$this->mode       = $mode ;

		$this->find_type  = $find_type ;
		$this->find_key   = $find_key ;
		$this->find_field = $find_field ;

		$this->base_dir = $base_dir ;
		$this->base_url = $C_base[url] ;
		$this->conf_name = $this->base_dir."/member/conf/".$this->data.".conf.php" ;
		$this->sort_field = $sort_field ;
		$this->sort_order = $sort_order ;

		//register_shutdown_function("db_interface_destroy") ;
		// open data base  -> 각 데이터베이스의 형태에 맞도록...
	}

	function destroy()
	{
		return ;
	}


///////////////////////////////////////////
// 데이터 개수 세기
///////////////////////////////////////////
/**
*/
	function count_data() 
	{
		//@todo 검색옵션지정

		//for version 2.3.x below
		//다음에 나오는 버젼에서는 사용하지 않도록 한다.
		$_data = $this->data ;

		/*
			//읽기에 락을 안하면 읽다가 한번 오류만 나지 않을까?
		if(wb_lock($idx_file) < 0 )
		{
			err_abort("$data_idx를 잠그기(lock) 실패했습니다.") ;
		}	
		*/

		$fd = wb_fopen( $idx_file, "r" ) ;

		// 인덱스 파일에서 사용자 ID를 검색하여 총 건수를 센다.
		$data_total = 0 ;
		$nCnt = 0 ;

		if($this->debug) echo("count_index: mode[$this->mode]<br>") ;

		$cnt_file =	file("$this->base_dir/member/data/$_data/total.cnt") ;
		$this->total = $cnt_file[0] ;

		//wb_unlock($idx_file) ;
		if ($this->debug) echo("count_index:TOTAL_DATA[".$this->total."]<br>") ;
		fclose($fd) ;	
	}

	///////////////////////////////////////////
	// index data부분 읽어오기
	///////////////////////////////////////////
	function select_data($offset=0, $limit=1)
	{
		$_data = $this->data ;
		
		require_once($this->base_dir."/lib/config.php") ;
		$conf = read_board_config($_data) ;
		//backword compatibility
		$C_skin = $conf[skin] ;
		// 1. 조건에 해당하는 인덱스의 데이터를 배열로 select 한다.
		// 검색모드인 경우와 아닌경우를 분리
		$idx_file = $this->base_dir."/member/data/$_data/data.idx" ;
		if(!@file_exists( $idx_file ))
		{
			$idx_file = "${idx_file}.php" ;
		}	
		
		
		//읽기에 락을 안하면 읽다가 한번 오류만 나지 않을까?
		//if(wb_lock($idx_file) < 0 )
		//{
		//	err_abort("$idx_file를 잠그기(lock) 실패했습니다.") ;
		//}	

		$fd = wb_fopen( $idx_file, "r" ) ;

		// 인덱스 파일에서 사용자 ID를 검색하여 총 건수를 센다.
		// 검색 field도 같이 check 필요.
		// 검색의 경우 모든 데이터를 읽어서 정렬해야 하기 때문에 성능에 대단한 문제가 된다. 어서 개선해야지 2002/02/05.
		$data_total = 0 ;
		$nCnt = 0 ;

		$this->num_rows = 0;

		//한페이지에 출력할 양만큼만 보이도록 조정. 
		$this->row_begin = $offset ;
		$this->row_end  = $offset + $limit ;

		// total이 아니라 글의 전체...
		$this->row_end  = ($this->row_end > $this->total )?($this->total):$this->row_end ;
		

		if($this->debug) echo("select_index_from_file:no find, just select<br>") ;
		$nCnt = 0 ;
		while( !feof($fd) )
		{
			$line = fgets( $fd, 1024 ) ;		
			$line = chop($line) ;
			if( strlen($line) == 0 )
			{
				continue ;
			}
				// 2002/03/15 데이터내부의 스크립트 시작,끝태그 걸러내기
				// 2002/03/23 eregi로 대치
			if( eregi("<\?php", $line) || eregi("\?>", $line) )
			{
				continue ;
			}

			//메모리 절약: php에서는 인덱스가 배열의 크기를 좌우하지 않으므로...
			//if($this->debug) echo("nCnt[$nCnt]offset[$offset]::") ;
			if( $nCnt >= $offset ) 
			{

				// $one_row = Member::explode( "|", $line ) ;
				// 

				$one_row = explode("|", $line ) ;
				//echo(":: $one_row[0] :: $one_row[1] ::<br>") ;
				//echo substr($one_row[0],1,1)."<br>" ; 
				if( substr($one_row[0],0,1) != "D" )
				{
					if($this->debug) echo("invalid data, just skip.") ;
					continue ;
				} 

				$this->rows["board_group"][$nCnt]		= $one_row[0] ;
				$this->rows["board_id"][$nCnt]    		= $one_row[1] ;
				$this->rows["name"][$nCnt]				= $one_row[2] ;
				$this->rows["cnt"][$nCnt]				= $one_row[3] ;
				$this->rows["cnt2"][$nCnt]				= $one_row[4] ;
				$this->rows["subject"][$nCnt]			= $one_row[5] ;
				$this->rows["type"][$nCnt]				= $one_row[6] ;
				$this->rows["cnt3"][$nCnt]				= $one_row[7] ;
				$this->rows["save_dir"][$nCnt]			= $one_row[8] ;
				$this->rows["encode_type"][$nCnt]		= $one_row[9] ;
				$this->rows["update_timestamp"][$nCnt]	= $one_row[10] ;
				$this->rows["cnt4"][$nCnt]				= $one_row[11] ;
				$this->rows["nReply"][$nCnt]			= $one_row[12] ;
				$this->rows["nWriting"][$nCnt]			= $one_row[13] ;
				$this->rows["subject_color"][$nCnt]	= $one_row[14] ;
				$this->rows["mail_reply"][$nCnt]		= $one_row[15] ;
				$this->rows["extra"][$nCnt]			= $one_row[16] ;

			}
			else
			{
				//if($this->debug) echo("skip") ;
			}

			//echo("nCnt[$nCnt][".$this->rows["board_group"][$nCnt]."]<br>") ;
			$nCnt++ ;
			if( $nCnt >= $this->row_end ) break ;
		} // end of while 

		$this->num_rows = $nCnt ;
		if($this->debug) echo("row_begin[".$this->row_begin."] row_end[".$this->row_end."]<br>") ;
		//wb_unlock($idx_file) ;
		fclose($fd) ;		

	}

	// 본문 글을 하나 가져오는 함수
	////////////////////////////////////////
	// 글의 내용이 보관되어 있는 파일에서 자료 가져오기
	//idx_fetch_array and file_fetch_array is converted && converged.
	function row_fetch_array($no = 0, $board_group = "", $board_id = "", $table="board") 
	{
		$log_date = date("Y/m/d H:i:s") ;
		$_debug = 0 ;
		$_data = $this->data ;

		require_once($this->base_dir."/lib/config.php") ;
		$conf = read_board_config($_data) ;
		//backword compatibility
		$C_skin = $conf[skin] ;
		//인덱스를 검색하여 데이터를 가져오도록 해야 한다.
		//인덱스에서 필요한 것을 불러야 하기 때문

		// if $no is empty then open index and get index data
		// 파일이름이 있다면 인덱스를 select하지 않고 사용하는 경우이므로  
		// 파일에서 불러온후 index에서 데이터를 가져온다.
		// writing_head부분과 writing_data부분 두개를 각각 가져올수 있도록 파일 구조 개선 하면 이곳에서 writing_head를 직접가져오도록 한다. // D1231231 : writing_data, H1231231 : writing_data

		//if($_debug) error_log("[$log_date]start[$board_group][$board_id]\n", 3, $this->base_dir."/logs/error_log") ;
		if( !empty($board_group) && !empty($board_id) )
		{
			// 하나의 데이터만 select가 필요하다.
			$this->mode = "find" ;
			$this->find_field = "board_group" ;
			$this->find_key = $board_group ;
			$this->select_data(0,1) ;

			$this->rows["board_group"][$no] = $board_group ;
			$this->rows["board_id"][$no]    = $board_id ;
		}
		$data_file = $this->rows["board_group"][$no].$this->rows["board_id"][$no] ;
		//echo("data_file:$data_file<br>") ;
		if(empty($data_file))
		{
			//if($_debug) error_log("[$log_date]data_file[$data_file]is empty\n", 3, $this->base_dir."/logs/error_log") ;
			//echo("row_fetch_array: data_file[$data_file] is empty " ) ;
			return -1 ;
		}

		if($board_id != "dummy") // index header만 가져오기
		{
			$fp_body = wb_fopen($this->base_dir."/member/data/$_data/$data_file", "r") ; 
			//if($_debug) error_log("[$log_date]read data\n", 3, $this->base_dir."/logs/error_log") ;
			$i = 0 ;
			// wrap기능 필요...
			//$comment = "<pre>" ;
			$comment = "" ;
			while( !feof($fp_body) )
			{
				$line = fgets($fp_body, 8192) ;
				if( $i == 0 )
				{
					$line = chop($line) ;
					$head = explode("|", $line) ;	
				}
				else
				{	
					$line = stripslashes( $line ) ;
					//\n -> BR로 바꾸었을때 테이블 밀리는 것을 방지하기 위해.
					//if( eregi("</?[[:space:]]*(table|tr|td|title|body|script|style)[^>]*>", $line) )
					/*
					if( eregi("</?[[:space:]]*(.[^>])*>", $line) )
					{
						$line = chop($line) ;
					}
					*/
					$comment = $comment.$line ; 
				}
				$i++ ;
			}
			//$comment = $comment."</pre>" ;
			fclose($fp_body) ;
		}
			
		$body = array (
			"board_group"  		=> $this->rows["board_group"][$no],
			"board_id"     		=> $this->rows["board_id"][$no],
			"cnt"          		=> $this->rows["cnt"][$no],
			"cnt2"         		=> $this->rows["cnt2"][$no],
			"subject"      		=> $this->rows["subject"][$no],
			"type"         		=> $this->rows["type"][$no],
			"cnt3"         		=> $this->rows["cnt3"][$no],
			"save_dir"     		=> $this->rows["save_dir"][$no],
			"update_timestamp"  => $this->rows["update_timestamp"][$no],
			"cnt4"         		=> $this->rows["cnt4"][$no],
			"nReply"       		=> $this->rows["nReply"][$no], 
			"nWriting"			=> $this->rows["nWriting"][$no],
			"subject_color"		=> $this->rows["subject_color"][$no],
			"mail_reply"		=> $this->rows["mail_reply"][$no],
			"extra"				=> $this->rows["extra"][$no],

			"password"        => $head[0],
			"name"            => $head[1],
			"w_date"          => $head[2],
			"email"           => $head[3],
			"homepage"        => $head[4],
			"bgimg"           => $head[5],
			"InputFile_name"  => $head[6],
			"InputFile_size"  => $head[7],
			"InputFile_type"  => $head[8],
			"InputFile2_name" => $head[9],
			"InputFile2_size" => $head[10],
			"InputFile2_type" => $head[11],
			"link"            => $head[12],
			"remote_ip"       => $head[13],
			"encode_type"     => $head[14],
			"timestamp"       => $head[15],
			"uid"			  => $head[16],
			"is_reply"		  => $head[17],
			"html_use"		  => $head[18],
			"br_use"		  => $head[19],
			"secret"		  => $head[20],
			"secret_passwd"   => $head[21],

			"comment"         => $comment 
		) ;

		//2002/09/24 현재는 관리자뿐이 없으므로 임시로 아바타 경로를 지정
		//URL[avatar]로 경로지정
		//Row[avatar]는 이름만...
		if( $body[uid] == __ROOT ) 
		{
			$body[avatar] = "avatar_".$body[uid] ;
		}
		else
		{
			$body[avatar] = "" ;
		}
		
		$body[InputFile_kb_size] =  (int)$body[InputFile_size]/1000.0 ;
		$body[InputFile_mb_size] =  $body[InputFile_size]/1000000 ;

		$body[InputFile2_kb_size] = (int)($body[InputFile2_size] / 1000) ;
		$body[InputFile2_mb_size] = $body[InputFile2_size]/1000000 ;

		//암호화 되어있으면 
		if( strlen($body[password]) > 15 || $body[encode_type] == "1" ) 
		{
			$body[name] = base64_decode($body[name]) ;
			$body[subject] = base64_decode($body[subject]) ;
		}

		$body[style_id]  = substr($body[board_id],1) ;

		// 내용의 일부분을 넣어줌. 2002/09/15
		if( empty($body[subject]) )
		{
			$subject_max = empty($conf[subject_max])?30:$conf[subject_max] ;
			$body[subject] = cutting($body[comment], $subject_max) ;
		}
		else if(!empty($conf[subject_max]))
		{
			$body[subject] = cutting($body[subject], $conf[subject_max]) ;
		}
		$body[subject] = str_replace("\r\n", "", $body[subject]) ;

		$body[subject] = stripslashes($body[subject]) ;
	
		$pos = eregi("^http://|^mms://|^ftp://|^https://", $body[homepage]) ;
		if( $pos != true && !empty($body[homepage]))
		{
			$body[homepage] = "http://".$body[homepage] ;	
		}

		$pos = eregi("^http://|^mms://|^ftp://|^https://", $body[link]) ;
		if( $pos != true && !empty($body[link]))
		{
			$body[link] = "http://".$body[link] ;	
		}

		$pos = strstr($body[InputFile_type], "image") ;
		if( $pos != false && !empty($body[InputFile_name]) && @file_exists($this->base_dir."/member/data/$_data/$body[board_group].$body[InputFile_name]_attach") )
		{
			$size = GetImageSize($this->base_dir."/member/data/$_data/{$body[board_group]}.{$body[InputFile_name]}_attach") ;
			$body[img_width] = $size[0] ;
			$body[img_height] = $size[1] ;

			$body[org_img_height] = $body[img_height] ;
			$body[org_img_width] = $body[img_width] ;
			if(!empty($conf[img_size_limit]) && ($body[img_width] > $conf[img_size_limit]) )
			{
				$body[img_height] = ($conf[img_size_limit] * $body[img_height]) / $body[img_width] ;
				$body[img_width] = $conf[img_size_limit] ;
			}
		}

		$pos = strstr($body[InputFile2_type], "image") ;
		if( $pos != false && !empty($body[InputFile2_name]) && @file_exists($this->base_dir."/member/data/$_data/$body[board_group].$body[InputFile2_name]_attach2") )
		{
			$size = GetImageSize($this->base_dir."/member/data/$_data/{$body[board_group]}.{$body[InputFile2_name]}_attach2") ;
			$body[img2_width] = $size[0] ;
			$body[img2_height] = $size[1] ;

			$body[org_img2_height] = $body[img2_height] ;
			$body[org_img2_width] = $body[img2_width] ;
			if(!empty($conf[img_size_limit]) && $body[img2_width] > $conf[img_size_limit])
			{
				$body[img2_height] = ($conf[img_size_limit] * $body[img2_height])/$body[img2_width] ;
				$body[img2_width] = $conf[img_size_limit] ;
			}
		}
		$body[name] = stripslashes($body[name]) ;

		//board_group이 없는 경우는 index파일을 접근하여 데이터를 가져오는 경우이므로
		//답글처리가 필요한 경우이다.
		//하지만 board_group이 있는경우에는 직접 파일의 내용을 가져오는 것이므로 
		//이경우에는 reply_list나 cat에서 하나의 글을 가져오는 목적이기 때문에 
		//답글 처리를 하면 안된다. 2002/03/17
		//다른 인터페이스가 있었으면...
		if( empty($board_group) )
		{
			////////////////////////////////
			//답글 처리
			////////////////////////////////
			//cat.html이 있다면 게시판 형태이기 때문에 리스트에서 답글들을 보여주지 않는다.
			if( $conf[small_reply_use] == "1" && !@file_exists($this->base_dir."/member/skin/$conf[skin]/cat.html") )
			{
				$body[reply_list] = wb_reply_list($_data, $this->rows["board_group"][$no], $this->rows["board_id"][$no], "variable", $this->base_dir) ;
				if( ! @file_exists($this->base_dir."/member/skin/$conf[skin]/reply_list.html") )
				{
						//기본 답글 모양 출력
					$body[comment] .= $body[reply_list] ;
				}
			}
		}
		//각 카운트에 이름 붙이기
		$body[cnt_homepage]  = $body[cnt] ;
		$body[cnt_download]  = $body[cnt3] ;
		$body[cnt_download2] = $body[cnt2] ;
		$body[cnt_view]      = $body[cnt4] ;
		//날짜처리
		//이전버젼인 경우 날짜 스트링 변환을 거쳐 timestamp에 넣어준다.
		$old_date = "0" ;
        if( empty($body[timestamp] ) ) 
        {
            $old_date = "1" ;
            $body[timestamp] = strtotime($body[w_date]) ;
        }

        if( $old_date == "1" )
        {
            $body[w_year]  = "2001" ;
            $body[w_year2] = "01" ;
        }
        else
        {
            $body[w_year]  = date("Y", $body[timestamp]) ; // 4자리
            $body[w_year2] = date("y", $body[timestamp]) ; // 2자리
        }

        $body[w_mon]   = date("m", $body[timestamp]) ; // 숫자
        $body[w_mon2]  = date("M", $body[timestamp]) ; // 영어문자
        $body[w_day]   = date("d", $body[timestamp]) ;
        $body[w_hour]  = date("H", $body[timestamp]) ;
        $body[w_hour2] = date("h", $body[timestamp]) ;
        $body[w_min]   = date("i", $body[timestamp]) ; 
        $body[w_sec]   = date("s", $body[timestamp]) ;
		$body[w_ampm]  = date("A", $body[timestamp]) ; // A.M. P.M.
		
		$body[category_name] = category_name($_data, $body[type], $this->base_dir) ;
		//echo("body[category_name]:$body[category_name]<br>") ;
			// anonymous글인 경우로 기본값 setting
		if( empty($body[uid]) || !isset($body[uid]) )
		{
			$body[uid] = 0 ;
		}
		return $body ;
	}


	/// 예전파일 호환성 고려 데이터 파일명 지정
	function idx_filename( $idx_name, $data )
	{
		if( empty($idx_name) )
		{
			$name = "data" ;
		} 
		//갱신하는 동안 파일 전체에 락을 걸면 delay요인이 될수 있으므로 
		//락을 걸지 않는다. 다만 갱신의 결과가 소실 될 수도 있다. ( 새글을 쓰는 경우 )
		//추후 데이터 구조를 변경하면 개선 여지가 있는 부분.
		$idx_filename = $this->base_dir."/member/data/$data/${idx_name}.idx" ;

		//2002/03/16 2.1.2 이하 버젼에서는 기본적으로 data.idx 라는 파일 이름을 사용하기 때문에
		//2002/03/15, 인덱스 파일 이름 자동 변경 
		if( @file_exists($idx_filename) )
		{
			if( ! @file_exists("${idx_filename}.php") )
			{
				if($this->debug) echo("to rename [${idx_filename}.php]") ;

				//@todo 남은 하드디스크 공간 알아내기
				rename($idx_filename, "${idx_filename}.php") ;
				$idx_filename = "${idx_file}.php" ;
			}
			else
			{
				//두개의 파일이 다 존재하면 크기 검사를 한다음 크기가 있는 것만 남겨둔다.
			}
		}
		else
		{
			$idx_filename = "${idx_filename}.php" ;
		}

		return $idx_filename ;
	}



	function idx_recover( $idx_filename, $idx_backup, $idx_content )
	{
		//실제 인덱스 크기와 전체 글의 개수와 비교하여 이상이 없는지 검사
		$idx_file_size = count( $idx_content ) ;

		$cnt_file = file( $this->base_dir."/member/data/{$this->data}/total.cnt" ) ;

        $data_total = $cnt_file[0] ;


		if( ($idx_file_size != $data_total) && ($idx_file_size <= 1) )
		{
			//손상 되었다고 판단 복구
			if($this->debug) echo("copy last index data") ;
			rename($idx_backup, $idx_filename) ;
			return true ;
		}

		//이것의 경우 손상되었다고 봐야 하는가?
		if( $mode == "count" && empty($idx_data[count_pos]) )
		{
			return true ;
		}
	}


	/// 인덱스 데이터 새로 추가 
	function insert_idx( $idx_fd, $member, $idx_content )
	{
		$idx_row = $member->implode( "|" ) ;
	
		// @todo 중복되는 데이터가 존재하는가 검사 
		// @todo gid, uid 생성 

		if($this->debug) echo("[$idx_row]") ;

		fwrite($idx_fd, "$idx_row\n") ;

		$write_cnt++ ;

		for($i = 0 ; $i < sizeof($idx_content) ; $i++ )
		{
			$idx_content[$i] = chop($idx_content[$i]) ;

			if( strlen($idx_content[$i]) == 0 )
			{
				//중간에 깨지면 빈줄이 들어가는 경우가 있음 이를 제거하기 위해
				continue ;
			}

			// 2002/03/23 eregi로 대치
			if( eregi("<\?php", $idx_content[$i]) || eregi("\?>", $idx_content[$i]) )
			{
				//2002/03/15 내부데이터 보호를 위한 스크립트 시작,끝태그 통과
				//위,아래에서 넣어주니까...
				continue ;
			}
			
			fwrite($idx_fd, "$idx_content[$i]\n" ) ;
			$write_cnt++ ;
		}
		
	}

	///	인덱스에 추가/갱신
	///	갱신한 인덱스를 배열로 리턴한다.
	function update_index($_data, $idx_name, $member, $mode)
	{
		if($this->debug) echo("update_index_to_file<br>") ;
		$log_date = date("Y/m/d H:i:s") ;
		$return_idx = array("") ;

		umask(0000) ;

		$idx_backup = $this->base_dir."/member/data/$_data/${idx_name}.backup.php" ;

		$idx_filename = $this->idx_filename( $idx_name, $_data ) ;

		$idx_content = file( $idx_filename ) ;

		if( $this->idx_recover( $idx_filename, $idx_backup, $idx_content ) ) 
			return ;

		//임시 인덱스 파일에 갱신내용을 쓴다.
		$tmp_idx_file = $this->base_dir."/member/data/$_data/".md5(uniqid("")); 
		$idx_fd = wb_fopen( $tmp_idx_file, "w" ) ;

		//2002/03/24 정보를 정할곳이 마땅치 않아 임시로 이곳에 지정.
		$idx_info = "2.4|".$idx_data[idx_info] ;
		fwrite($idx_fd, "<?php /*$idx_info\n") ;

		$write_cnt = 0 ;
		switch($mode)
		{
			case "insert" :
				$this->insert_idx( $idx_fd, $member, $idx_content ) ;
				
				$return_idx = $idx_data  ;
				break ;

			case "update" :
			case "count" :
				//이곳에서 이름과 제목을 인코딩 한다.
				//if($_debug) error_log("[$log_date][$mode]: data[$_data],idx_data[count_pos][".$idx_data[count_pos]."]\n", 3, $this->base_dir."/logs/error_log") ;
				if ($mode =="count" ) 
				{
					$count_pos = "cnt".$idx_data[count_pos] ;
					$idx_data[$count_pos] = "1" ;
				}

				// $idx_data[board_group], $idx_data[board_id]
				for($i = 0 ; $i < $idx_file_size ; $i++ )
				{
					//헤더 끝부분에 추가 자료가 들어갈경우 
					// \n을 제거해 주지 않으면 헤더가 깨져버린다.
					$idx_content[$i] = chop($idx_content[$i]) ;
					if( strlen($idx_content[$i]) == 0 )
					{
						// 중간에 \n으로 헤더가 깨진 경우 복구를 위해 통과
						continue ;
					}

					// 2002/03/23 eregi로 대치
					if( eregi("<\?php", $idx_content[$i]) || eregi("\?>", $idx_content[$i]) )
					{
						//2002/03/15 내부데이터 보호를 위한 스크립트 시작,끝태그 통과
						//위,아래에서 넣어주니까...
						continue ;
					}

					$idx = explode("|", $idx_content[$i]) ;

					// nReply가 있는 경우는 답글의 개수와 시간만 갱신하는 것이므로 
					// 같은 board_group를 갱신 시킨다. 
					if( empty($idx_data["board_id"]) )
					{
						$condition = ($idx[0] == $idx_data[board_group] ) ;
					}
					else
					{
						$condition = ($idx[0] == $idx_data[board_group] && $idx[1] == $idx_data[board_id]  ) ;
					}

					if( $condition ) 
					{
							//주석처리는 폼에서 받은 내용 처리
						$idx[0] = empty($idx_data["board_group"]) ? $idx[0] : $idx_data["board_group"] ;
						$idx[1] = empty($idx_data["board_id"]) ? $idx[1] : $idx_data["board_id"] ;
						$idx[2] = empty($idx_data["name"]) ? $idx[2] : base64_encode($idx_data["name"]) ; // encode
						//$idx[3] = empty($idx_data["cnt"]) ? $idx[3] : $idx_data["cnt"] ;
						$idx[3] = empty($idx_data["cnt1"]) ? $idx[3] : $idx[3]+1 ; //$idx_data["cnt"] ;
						$idx[4] = empty($idx_data["cnt2"]) ? $idx[4] : $idx[4]+1 ; 

						if( !empty($idx_data["subject"]) ) // $idx[5] : subject
						{
							if( $conf[subject_html_use] == "0" )
							{
								//$idx[5] = block_tags($idx[5]) ;
								$idx_data["subject"] = $idx_data["subject"] ;
							}
							$idx[5] = base64_encode($idx_data["subject"]) ; // 2002/03/25 encode	
						}

						$idx[6] = empty($idx_data["type"]) ? $idx[6] : $idx_data["type"] ;
						$idx[7] = empty($idx_data["cnt3"]) ? $idx[7] : $idx[7]+1 ; 
						$idx[8] = empty($idx_data["save_dir"]) ? $idx[8] : $idx_data["save_dir"] ;
						$idx[9] = empty($idx_data["encode_type"]) ? $idx[9] : $idx_data["encode_type"] ;
						$idx[10] = empty($idx_data["update_timestamp"]) ? $idx[10] : $idx_data["update_timestamp"] ;
						$idx[11] = empty($idx_data["cnt4"]) ? $idx[11] : $idx[11]+1 ; 
						if($this->debug) echo("cnt4[$idx[11]<br>") ;

						// nReply가 있는 경우는 증가시키는 경우이므로...
						//if($this->debug) echo("nReply[$idx[12]]<br>") ;
						$idx[12] = empty($idx_data[nReply]) ? $idx[12] : intval($idx[12])+1 ;
						if($this->debug) echo("nReply[$idx[12]]<br>") ;
						//전체 내용글 개수, 2002.03.06
						//이전에 없던 필드이므로 자동으로 계산해주도록 한다.
						if(empty($idx[13]))
						{
							// 2.1.x대에서는 글을 삭제하면 첫글과 모든글이 
							$idx[13] = intval($idx[12])+1 ; // 답글의 개수에 첫글의 개수 더하기
						}
						$idx[13] = empty($idx_data[nWriting])? $idx[13] : intval($idx[13])+1 ; 
						if($this->debug) echo("nWriting[$idx[13]]<br>") ;

						$idx[14] = empty($idx_data[subject_color])? $idx[14] : $idx_data[subject_color] ; 
						if($this->debug) echo("subject_color[$idx[14]]<br>") ;

						$idx[15] = empty($idx_data[mail_reply])? $idx[15] : $idx_data[mail_reply] ; 
						if($this->debug) echo("mail_reply[$idx[15]]<br>") ;

						$idx[16] = empty($idx_data[extra])? $idx[16] : $idx_data[extra] ; 
						if($this->debug) echo("extra[$idx[16]]<br>") ;

						$idx[17] = "" ;

						$idx_row = implode("|", $idx) ;
						fwrite($idx_fd, "$idx_row\n") ;

						$return_idx = $idx ;
						$return_idx["subject"] = $return_idx[5] ;
						$return_idx["nReply"] = $return_idx[12] ;
					}
					else
					{
						fwrite($idx_fd, "$idx_content[$i]\n" ) ;
					}
					$write_cnt++ ;
				}
				break ;

			case "delete" ;
					// index에서 삭제 처리
					// 데이터가 많아지만 문제가 될 수 있다.
				$idx_file = $this->base_dir."/member/data/$_data/data.idx" ;
				if( !@file_exists($idx_file) )
				{
					$idx_file = "${idx_file}.php" ;
				}


				$idx_content = file($idx_file) ;
				for($i = 0 ; $i < sizeof($idx_content) ; $i++ )
				{
					$idx_content[$i] = chop($idx_content[$i]) ;
					if( strlen($idx_content[$i]) == 0 )
					{
						// 중간에 \n으로 헤더가 깨진 경우 복구를 위해 통과
						continue ;
					}

						// 2002/03/23 eregi로 대치
					if( eregi("<\?php", $idx_content[$i]) || eregi("\?>", $idx_content[$i]) )
					{
						//2002/03/15 내부데이터 보호를 위한 스크립트 시작,끝태그 통과
						//위,아래에서 넣어주니까...
						continue ;
					}

					$idx = explode("|", $idx_content[$i]) ;

					if( $idx[0] == $idx_data[board_group] && $idx[1] == $idx_data[board_id] ) 
					{
							//write통과로 실제적인 삭제 처리 
						if($this->debug) echo("is main writing...") ;
						//이부분에서 모든 데이터가 삭제되었을때 글을 삭제하는 것으로 변경하여야 함.
						$return_idx[main_writing_delete] = "1" ;

						/*
						$idx[13]-- ; //전체글 개수 감소
						if($this->debug) echo("전체글 개수$idx[13]<br>") ;
							//전체글의 개수 0이하면 통과하여 실제적인 삭제처리
						if($idx[13] > 0)
						{
							$idx_str = implode("|", $idx ) ;
							fwrite($idx_fd, "$idx_str\n") ;	
							$write_cnt++ ;
						}
						else
						{
						}
						*/
					}
					else if( $idx[0] == $idx_data[board_group] )
					{
							//답글만 지우는 경우 답글수 갱신
						$idx[12]-- ; // 답글수 감소
						$idx[13]-- ; //전체 내용의 글개수도 감소 

							//전체글의 개수 0이하면 통과하여 실제적인 삭제처리
						if($idx[13] > 0)
						{
							$idx_str = implode("|", $idx ) ;
							fwrite($idx_fd, "$idx_str\n") ;	
							$write_cnt++ ;
						}
					}
					else
					{
						fwrite($idx_fd, "$idx_content[$i]\n" ) ;
						$write_cnt++ ;
					}
				}
				break ;

			default :
				err_abort("unknown index update mode") ;
				break ;
		}

		fwrite($idx_fd, "*/ ?>\n") ;
		fclose($idx_fd) ;

			//지우기보다는 백업한다. 단한개만...
			//깨졌다면 이전것으로 복구하도록...
		if($this->debug) echo("write_cnt[$write_cnt]<br>") ;

		//인덱스 처리한 개수는 전체 개수가 되므로. 2002/03/16
		$this->total = $write_cnt ;

		//2002/03/26 성공적으로 인덱스를 저장했을 때만 백업을 하고 인덱스를 교체한다.
		if( filesize($tmp_idx_file) > 0 )
		{
			wb_lock($idx_filename) ;
			if($write_cnt > 0 )
			{
				if($this->debug) echo("BACKUP INDEX<br>") ;
				if( filesize($idx_filename) > 0 )
				{
					@unlink($idx_backup) ;
					rename($idx_filename, $idx_backup) ;
					chmod($idx_backup, 0666) ;
				}
			}

			if($this->debug) echo("rename index<br>") ;
			if(@file_exists($idx_filename))
			{
				//2002/03/26 이부분에서 파일이 최초없을 경우 오류가 난다.
				if($this->debug) echo("unlink $idx_file<br>") ;
				@unlink($idx_filename) ;
			}
			rename("$tmp_idx_file", $idx_filename) ;
			chmod($idx_file, 0666) ;
			wb_unlock($idx_file) ;
		}
		return $return_idx ;
	}
}


?>
