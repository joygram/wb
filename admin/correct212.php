<?php
	///////////////////////////////
	// ��ġ ���� �˻�  
	///////////////////////////////
	if(file_exists("../setup{$setup_release_no}.done"))
	{
		echo("<script>
			alert('ȭ��Ʈ������ ��ġ�� �̹� �Ϸ�Ǿ����ϴ�.\\n\\n���� ��ġ�� ���ϽŴٸ� ��Ű�� ������ ���ε� �Ͻ��� setup�Ͻʽÿ�.\\n\\n��� ������ ������ ������ �̿��ϼ���.') ;
			document.location.href = '../setup.php?cmd=exit' ;
			</script>") ;
		exit ;
	}

	$_debug = 0 ;
	require_once("../lib/system_ini.php") ;
	require_once("../lib/get_base.php") ;

	$C_base = get_base(1, "off") ; //������ �Ǵ� �ּ� �޾ƿ���, lib.php�� ����.
	require_once("$C_base[dir]/lib/wb.inc.php") ;
	require_once("$C_base[dir]/board/conf/config.php") ;

	echo("<meta http-equiv=\"Content-Type\" content=\"text/html; charset=euc-kr\">") ;

	///////////////////////////////
	// �������� �˻�
	// ��ġ�� �Ǿ����� Ȯ�� �� �Ǿ����� ó���������� 
	// �����ɼ��� ������ ���� ó�� 
	// correct212.html���� correct212.php�� correct�ɼ��� �־� �ε��Ѵ�.
	// ��ġ�� ���� ��� �������� ���ε� ��Ŵ���� �������� ��� ���ϴ� �������� �̵��� �� �ֵ��� �Ѵ�.
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
	2002/03/26 base64�ߺ� encoding�� ���� ���� ���ִ� �Լ�
	2.1.2 ���� ������ ���� 	
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
			err_abort("resotre_name212: $conf_file ������ �������� �ʽ��ϴ�.") ;
		}

		/**
			�ε������� base64 �ߺ� ���ڵ� �� �̸��� ��������
		*/	
			//2002/03/24 index info ��������
		$_datadir = "$C_base[dir]/board/data/{$_data}" ;

		$old_ver = @file_exists("$_datadir/data.idx")?1:0 ;
		if($old_ver) // 2.1���Ϲ����̸�...
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
			// select�ϱ������� total���� ���� �� ���� ������...
			// dbi class���� limit���� ���� �� �ִ� ����� ����.
		$dbi->count_data() ;
		$line_begin = 0 ;
		$dbi->select_data($line_begin, $dbi->total) ;

		$nPos = $start ; //�˻��� ���  ���� br�� ���ؼ� ���� 
		$nCnt = $line_begin ; // �ѹ����� ���� ����

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

			//������ �ڷᰡ �ִٸ�
		if( filesize($tmp_idx_file) > 0 )
		{
			wb_lock($idx_file) ;
			if($write_cnt > 0 )
			{
				// index�� ����ϰ�
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

				//�ε����� �����Ѵ��� �̸�����
			@unlink($idx_file) ;
			$new_idx_file = "$_datadir/data.idx.php" ;
			rename($tmp_idx_file, $new_idx_file) ;
			@chmod($new_idx_file, 0666) ;
			wb_unlock($idx_file) ;
		}

		echo("[$_data]���� [$write_cnt]���� ������ ������ �Ϸ�Ǿ����ϴ�.<br>") ;
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

				//2002/03/24 ������ ���� ��ũ �߰�
			//$idx_filename = file_exists("$_datadir/data.idx")?"data.idx":"data.idx.php" ;
			$old_ver = file_exists("$_datadir/data.idx")?1:0 ;
			if($old_ver) // 2.1���Ϲ����̸�...
			{
				//������ ȣ��
				echo ("���� $board[0], �ε���[data.idx] ����<br>") ;
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
				if($info[1] < 1  ) // 1ȸ �̻��� ������ �д�.
				{
					// correct call
					echo ("���� $board[0], �ε���[$data.idx.php] $info[1]ȸ ����<br>") ;
					restore_name212( $C_base, $board[0] ) ;
					flush() ;
				}
				else
				{
					//skip
					echo("$board[0]�� �̹� �����Ǿ����ϴ�.<br>") ;
				}
			} // end elseif
			$line = "" ;
		}
		echo("ȭ��Ʈ���� 2.1.2 ���� �̸� �ߺ� ���ڵ� ������ �����Ϸ��߽��ϴ�.") ;
		touch("$C_base[dir]/board/data/wb_correct212.done") ;
		chmod("$C_base[dir]/board/data/wb_correct212.done", 0666) ;
	}

?>
