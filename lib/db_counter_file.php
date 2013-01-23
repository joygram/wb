<?php
if(!defined("__wb_db_counter_file__")) define("__wb_db_counter_file__","1") ;
else return ;
/**
///////////////////////////////////////////
// Database Interface & file data handling
// 2002/02
// 2002/03/15 
//	index파일명 자동 변환
**/
//$version = "WhiteBoard 2.3.0 2002/02/04" ;

class db_counter
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

	function db_counter($data, $table, $mode = "", $find_type = "", $find_key = "", $find_field = "", $db_type = "file", $ver = "2", $base_dir = ".", $sort_field="", $sort_order="ASC" ) 
	{
		$this->init($data, $table, $mode, $find_type, $find_key, $find_field, $db_type, $ver, $base_dir, $sort_field, $sort_order ) ;

	}

	function init($data, $table, $mode = "", $find_type = "", $find_key = "", $find_field = "", $db_type = "file", $ver = "2", $base_dir = ".", $sort_field="", $sort_order="ASC" ) 
	{
		global $C_base ;//temp 

		$this->debug = 0 ;

		$this->version    = "db_counter 0.1 2002/11" ;
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
		$this->conf_name = $this->base_dir."/counter/conf/".$this->data.".conf.php" ;
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
		$_data = $this->data ;

		require_once($this->base_dir."/lib/config.php") ;
		$conf = read_counter_config($_data) ;
		// backword compatibility
		$C_skin = $conf[skin] ;

		$idx_file = $this->base_dir."/counter/data/$_data/data.idx" ;
		if(!@file_exists($idx_file))
		{
			$idx_file = "{$idx_file}.php" ;
		}
		/*
			//읽기에 락을 안하면 읽다가 한번 오류만 나지 않을까?
		if(wb_lock($idx_file) < 0 )
		{
			err_abort("$data_idx를 잠그기(lock) 실패했습니다.") ;
		}	
		*/

		$fd = wb_fopen( $idx_file, "r" ) ;
		$nTotal = 0 ;
		$nCnt = 0 ;
		if($this->debug) echo("count_index: mode[$this->mode]<br>") ;
		if( $this->mode == "find" )
		{
			if($this->debug) echo("this->mode find find_field[$this->find_field]<br>") ;
			// type을 필터링 하지 않는 경우에 카운팅 할 수 있도록
			if($this->debug) echo("find_field[$this->find_field]type[$one_row[6]]<br>") ;
			while( !feof($fd) )
			{
				$line = chop(fgets( $fd, 2048 )) ;		
				if( strlen($line) == 0 )
				{
					if($this->debug) echo("count_index:line length is zero<br>") ;
					continue ;
				}
				// 2002/03/15 데이터내의 스크립트 시작,끝 걸러내기
				if( eregi("<\?php", $line) || eregi("\?>", $line) )
				{
					if($this->debug) echo("count_index:skip php script head,foot [$line]<br>") ; 
					continue ;
				}

				$one_row = explode("|", $line ) ;
					// $mode == "find" 일경우 사용
				//if($this->rows["encode_type"][$nCnt] == "1") 
				if($one_row[9] == "1") 
				{
					$name    = base64_decode($one_row[2]) ;
					$subject = base64_decode($one_row[5]) ;
					//if($this->debug) echo("decode[$name][$subject]<br>") ;
				}
				else
				{
					$name    = $one_row[2] ;
					$subject = $one_row[5] ;
				}
				//2002/10/26 위치 교정 검색 되도록
				switch($this->find_field)
				{
					case "name" :
						$this->find_item = $name ;
						break ;
					case "subject" :
						$this->find_item = $subject ;
						break ;
					case "content" ;
						break ;
				}

				if(empty($this->find_type))
				{
					$filter = true ;
				}
				else	
				{
					$filter = ($this->find_type == $one_row[6])?true:false ; 
				}

				if(empty($this->find_key))
				{
					if($this->debug) echo("[$this->find_key] is empty<br>") ;
					$found = true ;
				}
				else
				{
					if($this->debug) echo("find key not empty[$this->find_key][$this->find_item]<br>") ;
					$found = eregi($this->find_key, $this->find_item) ;
				}

				//if($this->debug) echo("found[$found]filter[$filter]<br>") ;
				if( $found != false && $filter == true ) 
				{
					$nCnt++ ;
				}

			} // end of while
			$this->total = $nCnt ;
			if($this->debug) echo("nCNT[$this->total]<br>") ;
		}
		else
		{
			$cnt_file =	file("$this->base_dir/counter/data/$_data/total.cnt") ;
			$this->total = $cnt_file[0] ;
		} // end of if 

		//wb_unlock($idx_file) ;
		if ($this->debug) echo("count_index:TOTAL_DATA[".$this->total."]<br>") ;
		fclose($fd) ;	
	}


	///////////////////////////////////////////
	// index data부분 읽어오기
	///////////////////////////////////////////
	/**
		//각 데이터를 읽어오는 함수
		//파일의 경우 각각 해당하는 데이터에 불러오는 함수를 따로 만들어주어야 한다.
		@todo 
	*/
	// 2.3.x 이전 버젼에서 인덱스 읽어오기
	// for 2.3.x version below.
	function select_data($offset=0, $limit=1)
	{
		$_data = $this->data ;
		
		require_once($this->base_dir."/lib/config.php") ;
		$conf = read_counter_config($_data) ;
		//backword compatibility
		$C_skin = $conf[skin] ;
		// 1. 조건에 해당하는 인덱스의 데이터를 배열로 select 한다.
		// 검색모드인 경우와 아닌경우를 분리
		$idx_file = $this->base_dir."/counter/data/$_data/data.idx" ;
		if(!@file_exists( $idx_file ))
		{
			$idx_file = "${idx_file}.php" ;
		}	
		
		/*
			//읽기에 락을 안하면 읽다가 한번 오류만 나지 않을까?
		if(wb_lock($idx_file) < 0 )
		{
			err_abort("$idx_file를 잠그기(lock) 실패했습니다.") ;
		}	
		*/

		$fd = wb_fopen( $idx_file, "r" ) ;
		// 검색 field도 같이 check 필요.
		// 검색의 경우 모든 데이터를 읽어서 정렬해야 하기 때문에 성능에 대단한 문제가 된다. 어서 개선해야지 2002/02/05.
		$nTotal = 0 ;
		$nCnt = 0 ;

		$this->num_rows = 0;
		//한페이지에 출력할 양만큼만 보이도록 조정. 
		$this->row_begin = $offset ;
		$this->row_end  = $offset + $limit ;
		// total이 아니라 글의 전체...
		$this->row_end  = ($this->row_end > $this->total )?($this->total):$this->row_end ;

		if($this->debug) echo("select_index_from_file:total[$this->total]row_begin[$this->row_begin]row_end[$this->row_end] offset[$offset] limit[$limit]<br>") ;

		if($this->debug) echo("select_from_file: read to all<br>") ;
		//todo: 정렬을 해야하기 때문에 전부다 읽어야만 한다. 변경요망
		while( !feof($fd) )
		{
			$line = chop(fgets( $fd, 1024 )) ;		
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

			$one_row = explode("|", $line ) ;
			// $mode == "find" 일경우 사용해야 한다. 비교해야하니까.
			// 변수 받는 방식을 바꾸었음, 정확한 메모리 할당을 위해서 	
			// 번호로 구분해야하는 단점을 가지고감. 2002/03/22
			if(empty($this->find_key))
			{
				$found = true ;
			}
			else
			{
				if($this->debug) echo("[$this->find_key][$this->find_item]<br>") ;
				$found = eregi($this->find_key, $this->find_item) ;
			}
			if( $found != false && $filter == true ) 
			{
				// 선택된 데이터 저장, 2002/03/22
				// 2002/10/13 Y,X -> X,Y로 변경저장
				$this->rows["date"][$nCnt]		= $one_row[0] ;
				$this->rows["hours"][$nCnt]    	= $one_row[1] ;
				$this->rows["total"][$nCnt]		= $one_row[16] ;

				$nCnt++ ;
			}
			else
			{
				//아닌경우 데이터를 다시 비워줘야한다.
				//if($this->debug) echo("select_from_file pass<br>") ;
			}
		} // end of while


		$_time_spend = number_format(getmicrotime() - $_time_start, 3) ;	
		if($this->debug) echo("sort exec time[$_time_spend]<br>") ;

		$this->num_rows = $nCnt ;
		if($this->debug) echo("row_begin[".$this->row_begin."] row_end[".$this->row_end."]<br>") ;
		//wb_unlock($idx_file) ;
		fclose($fd) ;		
	}


	////////////////////////////////////////
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
		$conf = read_counter_config($_data) ;
		//backword compatibility
		$C_skin = $conf[skin] ;
		//인덱스를 검색하여 데이터를 가져오도록 해야 한다.
		//인덱스에서 필요한 것을 불러야 하기 때문

		// if $no is empty then open index and get index data
		// 파일이름이 있다면 인덱스를 select하지 않고 사용하는 경우이므로  
		// 파일에서 불러온후 index에서 데이터를 가져온다.
		// writing_head부분과 writing_data부분 두개를 각각 가져올수 있도록 파일 구조 개선 하면 이곳에서 writing_head를 직접가져오도록 한다. // D1231231 : writing_data, H1231231 : writing_data

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
			$fp_body = wb_fopen($this->base_dir."/counter/data/$_data/$data_file", "r") ; 
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
		) ;

		return $body ;
	}

	/**
		인덱스에 추가/갱신
		갱신한 인덱스를 배열로 리턴한다.
	*/
	function update_index($_data, $index_name, $idx_data, $mode = "")
	{
		if($this->debug) echo("update_index_to_file<br>") ;
		$log_date = date("Y/m/d H:i:s") ;
		$return_idx = array("") ;
		umask(0000) ;
		if( empty($index_name) )
		{
			$index_name = "data" ;
		} 
		//갱신하는 동안 파일 전체에 락을 걸면 delay요인이 될수 있으므로 
		//락을 걸지 않는다. 다만 갱신의 결과가 소실 될 수도 있다. ( 새글을 쓰는 경우 )
		//추후 데이터 구조를 변경하면 개선 여지가 있는 부분.
		$idx_file = $this->base_dir."/counter/data/$_data/${index_name}.idx.php" ;
		$idx_backup = $this->base_dir."/counter/data/$_data/${index_name}.backup.php" ;
		$idx_content = file($idx_file) ;
		//실제 인덱스 크기와 전체 글의 개수와 비교하여 이상이 없는지 검사
		$idx_filesize = count($idx_content) ;
		if($this->debug) echo("update_index:idx_filesize[$idx_filesize]<br>") ;
		/*
		$cnt_file = file($this->base_dir."/counter/data/$_data/total.cnt") ;
        $nTotal = $cnt_file[0] ;
		//인덱스 손상을 최대한 방지하기 위해.
		if( ($idx_filesize != $nTotal) && ($idx_filesize <= 1) )
		{
			//손상 되었다고 판단 복구
			if($this->debug) echo("copy last index data") ;
			rename($idx_backup, $idx_file) ;
			return ;
		}
		//이것의 경우 손상되었다고 봐야 하는가?
		if( $mode == "count" && empty($idx_data[count_pos]) )
		{
			return ;
		}
		*/

		//임시 인덱스 파일에 갱신내용을 쓴다.
		$tmp_idxfile = $this->base_dir."/counter/data/$_data/".md5(uniqid("")); 
		$fd = wb_fopen( $tmp_idxfile, "w" ) ;

		//2002/03/24 정보를 정할곳이 마땅치 않아 임시로 이곳에 지정.
		$idx_info = "2.5|".$idx_data[idx_info] ;
		fwrite($fd, "<?php /*$idx_info\n") ;

		$row = explode("|", $idx_content[1]) ;
		if($this->debug) echo("IDX_CONTENT[1]:[$idx_content[1]][$row[0]]<br>") ;
		if($idx_data[date] == $row[0])
		{
			$mode = "update" ;
		}
		else
		{
			$mode = "insert" ;
		}
		if($this->debug) echo("update_index:mode[$mode]<br>") ;

		$return_idx = array("") ;
		$return_idx['yesterday'] = 0 ;
		$return_idx['today'] = 0 ;
		$return_idx['month'] = 0 ;
		$return_idx['year'] = 0 ;
		$return_idx['total'] = 0 ;
		$write_cnt = 0 ;
		switch($mode)
		{
			case "insert" :
				$total = 0 ;
				for($j = 0; $j < 24; $j++)
				{
					if($j == $idx_data["hour"]) 
					{
						$hours[$j]++ ;
					}
					$hours_str .= "$hours[$j]," ;

					$total += $hours[$j] ; 
				}
				$idx[0] = $idx_data['date'] ;
				$idx[1] = $hours_str ;
				$idx[2] = $total ;
				$idx[3] = "" ;
				$idx_row = implode("|", $idx) ;
				fwrite($fd, "$idx_row\n") ;
				$write_cnt++ ;

				if($this->debug) echo("[$idx_row]") ;

				$return_idx['today']   = $total ;
				$return_idx['week']  ; //일단 보류
				$return_idx['month'] =  $return_idx['today'];
				$return_idx['year']  =  $return_idx['today'];
				$return_idx['total'] =  $return_idx['today'];
				$return_idx['max']   =	$return_idx['today'];

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
					
					//전체 카운트 요약 자료 생성을 위해
					$tmp_row = explode("|", $idx_content[$i]) ;
					//첫줄은 오늘이기 때문에 아직 1이면 어제...
					if($write_cnt == 1)
					{
						$return_idx['yesterday'] = $tmp_row[2] ;
					}
					if(ereg(substr($idx_data['date'],0,7), $tmp_row[0]))
					{ 
						$return_idx['month'] += $tmp_row[2];
						$return_idx['max'] = max($return_idx['max'], $tmp_row[2]) ;
					}
					if(ereg(substr($idx_data["date"],0,4), $tmp_row[0]))
					{ 
						$return_idx['year'] += $tmp_row[2];
					}
					$return_idx['total'] += $tmp_row[2];

					fwrite($fd, "$idx_content[$i]\n" ) ;
					$write_cnt++ ;
				}
				break ;

			case "update" :
				if($this->debug) echo("sizeof idx_content[".sizeof($idx_content)."]<br>") ;
				for($i = 0 ; $i < sizeof($idx_content) ; $i++ )
				{
					$idx_content[$i] = chop($idx_content[$i]) ;
					if( strlen($idx_content[$i]) == 0 )
					{
						continue ;
					}
					// 2002/03/23 eregi로 대치
					if( eregi("<\?php", $idx_content[$i]) || eregi("\?>", $idx_content[$i]) )
					{
						//2002/03/15 내부데이터 보호를 위한 스크립트 시작,끝태그 통과
						continue ;
					}
					$idx = explode("|", $idx_content[$i]) ;
					if($idx[0] == $idx_data['date']) 
					{
						$c_date = explode("/", $idx_data['date']) ;
						$hours = explode(",", $idx[1]) ;
						$total = 0 ;
						for($j = 0; $j < 24; $j++)
						{
							if($j == $idx_data['hour']) 
							{
								$hours[$j]++ ;
							}
							$hours_str .= "$hours[$j]," ;
							$total += $hours[$j] ; 
						}
						$idx[0] = $idx_data["date"] ;
						$idx[1] = $hours_str ;
						$idx[2] = $total ;
						$idx[3] = "" ;
						$idx_row = implode("|", $idx) ;
						fwrite($fd, "$idx_row\n") ;
						$write_cnt++ ;
					}
					else
					{
						fwrite($fd, "$idx_content[$i]\n" ) ;
						$write_cnt++ ;
					}
					//전체 카운트 요약 자료 생성을 위해
					if($write_cnt == 1) //첫번째데이터:오늘 
					{
						$return_idx['today'] = $idx[2] ;
					}
					else if($write_cnt == 2) //두번째 데이터:어제
					{
						$return_idx['yesterday'] = $idx[2] ;
					}
					if($this->debug) echo substr($idx_data['date'],0,7) ;
					if($this->debug) echo(" update_index:$idx[0]:$idx[2]<br>") ;
					if(ereg(substr($idx_data['date'],0,7), $idx[0]))
					{ 
						$return_idx['month'] += $idx[2];
						$return_idx['max'] = max($return_idx['max'], $idx[2]) ;
						if($this->debug) echo("update month:$return_idx[month]<br>") ;
					}
					if(ereg(substr($idx_data["date"],0,4), $idx[0]))
					{ 
						$return_idx['year'] += $idx[2];
						if($this->debug) echo("update year:$return_idx[year]<br>") ;
					}
					$return_idx['total'] += $idx[2];
					if($this->debug) echo("update total:$return_idx[total]<br>") ;
				}
				break ;
			default :
				err_abort("unknown index update mode") ;
				break ;
		}

		fwrite($fd, "*/ ?>\n") ;
		fclose($fd) ;

			//지우기보다는 백업한다. 단한개만...
			//깨졌다면 이전것으로 복구하도록...
		if($this->debug) echo("write_cnt[$write_cnt]<br>") ;

		//인덱스 처리한 개수는 전체 개수가 되므로. 2002/03/16
		$this->total = $write_cnt ;

		//2002/03/26 성공적으로 인덱스를 저장했을 때만 백업을 하고 인덱스를 교체한다.
		if( filesize($tmp_idxfile) > 0 )
		{
			wb_lock($idx_file) ;
			if($write_cnt > 0 )
			{
				if($this->debug) echo("BACKUP INDEX<br>") ;
				if( filesize($idx_file) > 0 )
				{
					@unlink($idx_backup) ;
					rename($idx_file, $idx_backup) ;
					chmod($idx_backup, 0666) ;
				}
			}

			if($this->debug) echo("rename index<br>") ;
			if(@file_exists($idx_file))
			{
				//2002/03/26 이부분에서 파일이 최초없을 경우 오류가 난다.
				if($this->debug) echo("unlink $idx_file<br>") ;
				@unlink($idx_file) ;
			}
			rename("$tmp_idxfile", $idx_file) ;
			chmod($idx_file, 0666) ;
			wb_unlock($idx_file) ;
		}
		return $return_idx ;
	}
}
?>
