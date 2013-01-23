<?
if(!defined("__wb_db_board_mysql__")) define("__wb_db_board_mysql__","1") ;
else return ;
/**
// Database Interface & data handling
@date 2004/08/17
@author whitebbs.net
**/
define("DB_SORT", "1") ;
define("DB_FIND", "2") ;
define("DB_OK",   "1000") ;

define("E_DB_CONN", "1") ;
define("E_DB_QUERY", "2") ;
define("E_USER_EXIST", "3") ;
define("E_IDNUM_EXIST", "4") ;

class db_board
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
	var $find_field ;	// 여러개의 필드일 경우 고려 필요. 

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

	function db_board($data, $table, $mode = "", $find_type = "", $find_key = "", $find_field = "", $db_type = "file", $ver = "2", $base_dir = ".", $sort_field="", $sort_order="ASC" ) 
	{
		$this->open($data, $table, $mode, $find_type, $find_key, $find_field, $db_type, $ver, $base_dir, $sort_field, $sort_order ) ;

	}

	//여기서 필요한 WHERE구절을 만들어 놓도록 하자.
	function open($data, $table, $mode = "", $find_type = "", $find_key = "", $find_field = "", $db_type = "file", $ver = "2", $base_dir = ".", $sort_field="", $sort_order="ASC" ) 
	{
		global $C_base ;//temp 

		$this->debug = 0 ;

		$this->version    = "db_board_mysql 0.1 200408" ;
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
		$this->conf_name = $this->base_dir."/board/conf/".$this->data.".conf.php" ;
		$this->sort_field = $sort_field ;
		$this->sort_order = $sort_order ;

		//register_shutdown_function("db_board_destroy") ;
		// open data base  -> 각 데이터베이스의 형태에 맞도록...
		$this->db_conn = mysql_connect($this->host, $this->user, $this->passwd) 
			or die("DB_BOARD Connect Error:". mysql_error()) ;
		mysql_select_db($this->data) 
			or die("DB_BOARD Choice DB Error:". mysql_error()) ;
	}

	function destroy()
	{
		return ;
	}

	/**
	///////////////////////////////////////////
	// 전체 데이터 개수 세기
	///////////////////////////////////////////
	*/
	function count_data() 
	{
		$_data = $this->data ;
		require_once($this->base_dir."/lib/config.php") ;
		$conf = read_board_config($_data) ;
		// MYSQL count 
		$query =  "SELECT count(*) FROM $this->table " ; //WHERE $where" ;
		$result = mysql_db_query($query, $this->db_conn) 
			or die("DB_BOARD count_data:". mysql_error()) ;
		$row = mysql_fetch_row($result) ;
		$this->total = $row[0] ;
	}

	/**
		// SELECT 
		//각 데이터를 읽어오는 함수
		//파일의 경우 각각 해당하는 데이터에 불러오는 함수를 따로 만들어주어야 한다.
		@todo 
	*/
	function select_data($offset=0, $limit=1)
	{
		$_data = $this->data ;
		require_once($this->base_dir."/lib/config.php") ;
		$conf = read_board_config($_data) ;

		$query = "SELECT * FROM $this->table WHERE $this->search_condition OFFSET $offset ORDER BY $this->order" ;

		$this->num_rows = 0;
		//한페이지에 출력할 양만큼만 보이도록 조정. 
		$this->row_begin = $offset ;
		$this->row_end  = $offset + $limit ;
		// total이 아니라 글의 전체...
		$this->row_end  = ($this->row_end > $this->total )?($this->total):$this->row_end ;
		$this->num_rows = $nCnt ;
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
		$conf = read_board_config($_data) ;

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
			"comment"         => $comment 
		) ;

		return $body ;
	}

	/**
		인덱스에 추가/갱신
		갱신한 인덱스를 배열로 리턴한다.
		@todo 함수로 분리시킬 필요가 있음.
	*/
	function update_index($_data, $index_name, $idx_data, $mode)
	{
		if($this->debug) echo("update_index_to_file<br>") ;
		$log_date = date("Y/m/d H:i:s") ;
		$return_idx = array("") ;
		umask(0000) ;
		//2002/03/16 2.1.2 이하 버젼에서는 기본적으로 data.idx 라는 파일 이름을 사용하기 때문에
		if( empty($index_name) )
		{
			$index_name = "data" ;
		} 

		$write_cnt = 0 ;
		switch($mode)
		{
			case "insert" :
				//2002/03/25 인덱스 갱신하는 부분에 base64_encode이동 
				//;$idx_data[name]    = base64_encode($idx_data[name]) ;
				//$idx_data[subject] = base64_encode($idx_data[subject]) ;

				$idx_row = implode("|", $idx_data) ;
				//UPDATE SET
				$return_idx = $idx_data  ;
				break ;

			case "update" :
			case "count" :
				// UPDATE SET $table (a=b) WHERE $where 
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
				}
				break ;

			case "delete" 
					// DELETE FROM $table WHERE $where ;;
					// index에서 삭제 처리
					// 데이터가 많아지만 문제가 될 수 있다.
					// 답글 본문하나만 제거 하느냐 아니면 글 자체를 지우느냐...
					// 본문이 제거되어도 답글은 존재하므로 제거되지 않도록...
			default :
				err_abort("unknown index update mode") ;
				break ;
		}
		return $return_idx ;
	}
}

?>
