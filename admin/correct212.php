<?php
	///////////////////////////////
	// 설치 여부 검사  
	///////////////////////////////
	if(file_exists("../setup{$setup_release_no}.done"))
	{
		echo("<script>
			alert('화이트보드의 설치가 이미 완료되었습니다.\\n\\n새로 설치를 원하신다면 패키지 파일을 업로드 하신후 setup하십시오.\\n\\n기능 설정은 관리자 도구를 이용하세요.') ;
			document.location.href = '../setup.php?cmd=exit' ;
			</script>") ;
		exit ;
	}

	$_debug = 0 ;
	require_once("../lib/system_ini.php") ;
	require_once("../lib/get_base.php") ;

	$C_base = get_base(1, "off") ; //기준이 되는 주소 받아오기, lib.php에 있음.
	require_once("$C_base[dir]/lib/wb.inc.php") ;
	require_once("$C_base[dir]/board/conf/config.php") ;

	echo("<meta http-equiv=\"Content-Type\" content=\"text/html; charset=euc-kr\">") ;

	///////////////////////////////
	// 수정여부 검사
	// 설치가 되었는지 확인 후 되었으면 처음설정으로 
	// 교정옵션이 있으면 수정 처리 
	// correct212.html에서 correct212.php를 correct옵션을 주어 로드한다.
	// 설치가 끝난 경우 페이지를 리로드 시킴으로 프레임을 벗어나 원하는 페이지로 이동할 수 있도록 한다.
	///////////////////////////////
	if( @file_exists("$C_base[dir]/board/data/wb_correct212.done") )
	{
		echo("<script>parent.location.href='./setup/language.php?upgrade=1';</script>\n") ;
	}
	else if($correct)
	{
		correct212($C_base) ;
		if( @file_exists("$C_base[dir]/board/data/wb_correct212.done") )
		{
			echo("<script>parent.location.href='./setup/language.php?upgrade=1';</script>\n") ;
		}
	}
	else
	{
		include("./html/correct212.html") ;
	}

	exit ;

//////////////////////////////////////////////////////////////////
	/**
	2002/03/26 base64중복 encoding된 것을 복구 해주는 함수
	2.1.2 이하 버젼의 버그 	
	*/
	function restore_name212( $C_base, $_data )
	{
		include_once("$C_base[dir]/lib/io.php") ;

		$_debug = 1 ;
		$conf_file = "$C_base[dir]/board/conf/{$_data}.conf.php" ;
		if( @file_exists($conf_file) )
		{
			include($conf_file) ;
		} 
		else
		{
			err_abort("resotre_name212: $conf_file 파일이 존재하지 않습니다.") ;
		}

		/**
			인덱스에서 base64 중복 인코딩 된 이름의 교정복구
		*/	
			//2002/03/24 index info 가져오기
		$_datadir = "$C_base[dir]/board/data/{$_data}" ;

		$old_ver = @file_exists("$_datadir/data.idx")?1:0 ;
		if($old_ver) // 2.1이하버젼이면...
		{
			$info[1] = "1" ; //correct_no|
			$idx_file = "$_datadir/data.idx" ;
		}
		else 
		{
			$fp = wb_fopen("$_datadir/data.idx.php", "r", 1, 0) ;
			if(!$fp) return false ;

			$line = fgets($fp, 1024) ;
			fclose($fp) ;
			$line = chop($line) ;
			list($tag, $idx_info) = split("/\*", $line) ;  
			$info = explode("|", $idx_info) ;

			$info[1]++ ; //correct_no increase
			$idx_file = "$_datadir/data.idx.php" ;
		}
		$line = "" ;


		$dbi = new db_board($_data, "index", $mode, $type, $key, $field, "file", "2", $C_base[dir] ) ;
			// select하기전에는 total값을 구할 수 없기 때문에...
			// dbi class에서 limit값을 구할 수 있는 방법이 없다.
		$dbi->count_data() ;
		$line_begin = 0 ;
		$dbi->select_data($line_begin, $dbi->total) ;

		$nPos = $start ; //검색할 경우  라인 br을 위해서 선언 
		$nCnt = $line_begin ; // 넘버링을 위한 숫자

		$idx_backup   = "$_datadir/data.backup.php" ;
		$tmp_idx_file = "$_datadir/".md5(uniqid("")); 
		$idx_fd = wb_fopen( $tmp_idx_file, "w" ) ;

		$idx_info = "2.4|".$info[1] ;
		fwrite($idx_fd, "<?php /*$idx_info\n") ;

		for($i = $dbi->row_begin ; $i < $dbi->row_end ; $i ++)
		{
			///////////////////////////////////////
			$Row = $dbi->row_fetch_array($i) ;
			if( $Row == -1)
			{
				echo("Row is -1<br>") ;
				break ;
			}

			if( $Row["encode_type"] == "1" )
			{
				$Row["name"] = base64_encode($Row["name"]) ;
				$Row["subject"] = base64_encode($Row["subject"]) ;
			}

			$index_name = "data" ;
			$row_idx[0]  = $Row["board_group"] ; 
			$row_idx[1]  = $Row["board_id"] ;
			$row_idx[2]  = $Row["name"] ; 
			$row_idx[3]  = $Row["cnt"] ; 
			$row_idx[4]  = $Row["cnt2"] ;
			$row_idx[5]  = $Row["subject"] ;
			$row_idx[6]  = $Row["type"] ;
			$row_idx[7]  = $Row["cnt3"] ;
			$row_idx[8]  = $Row["save_dir"] ;
			$row_idx[9]  = $Row["encode_type"] ;
			$row_idx[10] = $Row["update_timestamp"] ;
			$row_idx[11] = $Row["cnt4"] ;
			$row_idx[12] = $Row["nReply"] ;              
			$row_idx[13] = $Row["nWriting"] ;         

			$idx_content = implode("|", $row_idx) ;

			fwrite($idx_fd, "$idx_content\n" ) ;
			$write_cnt++ ;
		}

		fwrite($idx_fd, "*/ ?>\n") ;
		fclose($idx_fd) ;

			//생성된 자료가 있다면
		if( filesize($tmp_idx_file) > 0 )
		{
			wb_lock($idx_file) ;
			if($write_cnt > 0 )
			{
				// index를 백업하고
				if($this->debug) echo("BACKUP INDEX<br>") ;
				if( filesize($idx_file) > 0 )
				{
					@unlink($idx_backup) ;
					rename($idx_file, $idx_backup) ;
					@chmod($idx_backup, 0666) ;
				}
			}

			/*
			if($_debug) echo("rename index<br>") ;
			if( @file_exists( $idx_file ) )
			{
				echo("idx_file[$idx_file]") ;
				$name = file($idx_file) ;	
				print_r($name) ;
				echo("------") ;
			}
			*/

				//인덱스를 제거한다음 이름변경
			@unlink($idx_file) ;
			$new_idx_file = "$_datadir/data.idx.php" ;
			rename($tmp_idx_file, $new_idx_file) ;
			@chmod($new_idx_file, 0666) ;
			wb_unlock($idx_file) ;
		}

		echo("[$_data]보드 [$write_cnt]개의 데이터 교정이 완료되었습니다.<br>") ;
	}


	function correct212($C_base)
	{
		$flist = new file_list("$C_base[dir]/board/conf", 1) ;
		$flist->read("conf.php", 0) ;
		$i = 0 ;
		$nTotal = 0 ;

		$flist->reset() ;
		while( ($file_name = $flist->next()) )
		{
			if( strstr($file_name, "deleted") || strstr($file_name,"__global") )
			{
				continue ;
			}

			$i++ ;
			$board = explode(".", $file_name) ;
			$Row[no] = $nTotal-$i+1 ;
			$Row[board] = $board[0] ;
			$_datadir = "$C_base[dir]/board/data/$board[0]" ;

				//2002/03/24 데이터 교정 링크 추가
			//$idx_filename = file_exists("$_datadir/data.idx")?"data.idx":"data.idx.php" ;
			$old_ver = file_exists("$_datadir/data.idx")?1:0 ;
			if($old_ver) // 2.1이하버젼이면...
			{
				//무조건 호출
				echo ("보드 $board[0], 인덱스[data.idx] 교정<br>") ;
				restore_name212( $C_base, $board[0] ) ;
				flush() ;
			}
			else 
			{
				$fp = wb_fopen("$_datadir/data.idx.php", "r", 1, 0) ;
				if(!$fp) continue ;

				$line = fgets($fp, 1024) ;
				fclose($fp) ;
				list($tag, $idx_info) = split("/\*", $line) ;  
				$info = explode("|", $idx_info) ;
				if($info[1] < 1  ) // 1회 이상의 여유를 둔다.
				{
					// correct call
					echo ("보드 $board[0], 인덱스[$data.idx.php] $info[1]회 교정<br>") ;
					restore_name212( $C_base, $board[0] ) ;
					flush() ;
				}
				else
				{
					//skip
					echo("$board[0]는 이미 교정되었습니다.<br>") ;
				}
			} // end elseif
			$line = "" ;
		}
		echo("화이트보드 2.1.2 이하 이름 중복 인코딩 데이터 교정완료했습니다.") ;
		touch("$C_base[dir]/board/data/wb_correct212.done") ;
		chmod("$C_base[dir]/board/data/wb_correct212.done", 0666) ;
	}

?>
