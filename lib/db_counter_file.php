<?php
if(!defined("__wb_db_counter_file__")) define("__wb_db_counter_file__","1") ;
else return ;
/**
///////////////////////////////////////////
// Database Interface & file data handling
// 2002/02
// 2002/03/15 
//	index���ϸ� �ڵ� ��ȯ
**/
//$version = "WhiteBoard 2.3.0 2002/02/04" ;

class db_counter
{
	var $debug ;
	var $version ; 		// class version
	var $db_type ;		// �����ͺ��̽� Ÿ��
	var $db_ver ; 		// ���� �������� ����: 2, 3 
	var $data ;			// ���� ������ �̸�
	var $table ;		// ����ϰ����ϴ� ���̺��̸�
	// sort and filtering
	var $mode ;			// �˻���� 
	var $find_type ;	//
	var $find_key ;
	var $find_field ;

	var $total ;		// ��ü �������� �� 
	var $num_rows ;		// ������ �ڷ��� �� 
	var $rows ;			// �ڷ� �迭
	// ��쿡 ���� ���۰� ���� ���� ���� �ʱ� ������ ���.
	var $row_begin ;    // �迭�� ���۹�ȣ
	var $row_end ;		// �迭�� ����ȣ

	var $base_dir ;		//���� ���丮
	var $base_url ; 	//���� URL
	var $conf_name ;	// configuration name

	var $db_conn ;      // �����ͺ��̽� ���ắ�� 

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
		// open data base  -> �� �����ͺ��̽��� ���¿� �µ���...
	}

	function destroy()
	{
		return ;
	}


///////////////////////////////////////////
// ������ ���� ����
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
			//�б⿡ ���� ���ϸ� �дٰ� �ѹ� ������ ���� ������?
		if(wb_lock($idx_file) < 0 )
		{
			err_abort("$data_idx�� ��ױ�(lock) �����߽��ϴ�.") ;
		}	
		*/

		$fd = wb_fopen( $idx_file, "r" ) ;
		$nTotal = 0 ;
		$nCnt = 0 ;
		if($this->debug) echo("count_index: mode[$this->mode]<br>") ;
		if( $this->mode == "find" )
		{
			if($this->debug) echo("this->mode find find_field[$this->find_field]<br>") ;
			// type�� ���͸� ���� �ʴ� ��쿡 ī���� �� �� �ֵ���
			if($this->debug) echo("find_field[$this->find_field]type[$one_row[6]]<br>") ;
			while( !feof($fd) )
			{
				$line = chop(fgets( $fd, 2048 )) ;		
				if( strlen($line) == 0 )
				{
					if($this->debug) echo("count_index:line length is zero<br>") ;
					continue ;
				}
				// 2002/03/15 �����ͳ��� ��ũ��Ʈ ����,�� �ɷ�����
				if( eregi("<\?php", $line) || eregi("\?>", $line) )
				{
					if($this->debug) echo("count_index:skip php script head,foot [$line]<br>") ; 
					continue ;
				}

				$one_row = explode("|", $line ) ;
					// $mode == "find" �ϰ�� ���
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
				//2002/10/26 ��ġ ���� �˻� �ǵ���
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
	// index data�κ� �о����
	///////////////////////////////////////////
	/**
		//�� �����͸� �о���� �Լ�
		//������ ��� ���� �ش��ϴ� �����Ϳ� �ҷ����� �Լ��� ���� ������־�� �Ѵ�.
		@todo 
	*/
	// 2.3.x ���� �������� �ε��� �о����
	// for 2.3.x version below.
	function select_data($offset=0, $limit=1)
	{
		$_data = $this->data ;
		
		require_once($this->base_dir."/lib/config.php") ;
		$conf = read_counter_config($_data) ;
		//backword compatibility
		$C_skin = $conf[skin] ;
		// 1. ���ǿ� �ش��ϴ� �ε����� �����͸� �迭�� select �Ѵ�.
		// �˻������ ���� �ƴѰ�츦 �и�
		$idx_file = $this->base_dir."/counter/data/$_data/data.idx" ;
		if(!@file_exists( $idx_file ))
		{
			$idx_file = "${idx_file}.php" ;
		}	
		
		/*
			//�б⿡ ���� ���ϸ� �дٰ� �ѹ� ������ ���� ������?
		if(wb_lock($idx_file) < 0 )
		{
			err_abort("$idx_file�� ��ױ�(lock) �����߽��ϴ�.") ;
		}	
		*/

		$fd = wb_fopen( $idx_file, "r" ) ;
		// �˻� field�� ���� check �ʿ�.
		// �˻��� ��� ��� �����͸� �о �����ؾ� �ϱ� ������ ���ɿ� ����� ������ �ȴ�. � �����ؾ��� 2002/02/05.
		$nTotal = 0 ;
		$nCnt = 0 ;

		$this->num_rows = 0;
		//���������� ����� �縸ŭ�� ���̵��� ����. 
		$this->row_begin = $offset ;
		$this->row_end  = $offset + $limit ;
		// total�� �ƴ϶� ���� ��ü...
		$this->row_end  = ($this->row_end > $this->total )?($this->total):$this->row_end ;

		if($this->debug) echo("select_index_from_file:total[$this->total]row_begin[$this->row_begin]row_end[$this->row_end] offset[$offset] limit[$limit]<br>") ;

		if($this->debug) echo("select_from_file: read to all<br>") ;
		//todo: ������ �ؾ��ϱ� ������ ���δ� �о�߸� �Ѵ�. ������
		while( !feof($fd) )
		{
			$line = chop(fgets( $fd, 1024 )) ;		
			if( strlen($line) == 0 )
			{
				continue ;
			}
			// 2002/03/15 �����ͳ����� ��ũ��Ʈ ����,���±� �ɷ�����
			// 2002/03/23 eregi�� ��ġ
			if( eregi("<\?php", $line) || eregi("\?>", $line) )
			{
				continue ;
			}

			$one_row = explode("|", $line ) ;
			// $mode == "find" �ϰ�� ����ؾ� �Ѵ�. ���ؾ��ϴϱ�.
			// ���� �޴� ����� �ٲپ���, ��Ȯ�� �޸� �Ҵ��� ���ؼ� 	
			// ��ȣ�� �����ؾ��ϴ� ������ ������. 2002/03/22
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
				// ���õ� ������ ����, 2002/03/22
				// 2002/10/13 Y,X -> X,Y�� ��������
				$this->rows["date"][$nCnt]		= $one_row[0] ;
				$this->rows["hours"][$nCnt]    	= $one_row[1] ;
				$this->rows["total"][$nCnt]		= $one_row[16] ;

				$nCnt++ ;
			}
			else
			{
				//�ƴѰ�� �����͸� �ٽ� �������Ѵ�.
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
	// ���� ���� �ϳ� �������� �Լ�
	////////////////////////////////////////
	// ���� ������ �����Ǿ� �ִ� ���Ͽ��� �ڷ� ��������
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
		//�ε����� �˻��Ͽ� �����͸� ���������� �ؾ� �Ѵ�.
		//�ε������� �ʿ��� ���� �ҷ��� �ϱ� ����

		// if $no is empty then open index and get index data
		// �����̸��� �ִٸ� �ε����� select���� �ʰ� ����ϴ� ����̹Ƿ�  
		// ���Ͽ��� �ҷ����� index���� �����͸� �����´�.
		// writing_head�κа� writing_data�κ� �ΰ��� ���� �����ü� �ֵ��� ���� ���� ���� �ϸ� �̰����� writing_head�� �������������� �Ѵ�. // D1231231 : writing_data, H1231231 : writing_data

		if( !empty($board_group) && !empty($board_id) )
		{
			// �ϳ��� �����͸� select�� �ʿ��ϴ�.
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

		if($board_id != "dummy") // index header�� ��������
		{
			$fp_body = wb_fopen($this->base_dir."/counter/data/$_data/$data_file", "r") ; 
			//if($_debug) error_log("[$log_date]read data\n", 3, $this->base_dir."/logs/error_log") ;
			$i = 0 ;
			// wrap��� �ʿ�...
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
					//\n -> BR�� �ٲپ����� ���̺� �и��� ���� �����ϱ� ����.
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
		�ε����� �߰�/����
		������ �ε����� �迭�� �����Ѵ�.
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
		//�����ϴ� ���� ���� ��ü�� ���� �ɸ� delay������ �ɼ� �����Ƿ� 
		//���� ���� �ʴ´�. �ٸ� ������ ����� �ҽ� �� ���� �ִ�. ( ������ ���� ��� )
		//���� ������ ������ �����ϸ� ���� ������ �ִ� �κ�.
		$idx_file = $this->base_dir."/counter/data/$_data/${index_name}.idx.php" ;
		$idx_backup = $this->base_dir."/counter/data/$_data/${index_name}.backup.php" ;
		$idx_content = file($idx_file) ;
		//���� �ε��� ũ��� ��ü ���� ������ ���Ͽ� �̻��� ������ �˻�
		$idx_filesize = count($idx_content) ;
		if($this->debug) echo("update_index:idx_filesize[$idx_filesize]<br>") ;
		/*
		$cnt_file = file($this->base_dir."/counter/data/$_data/total.cnt") ;
        $nTotal = $cnt_file[0] ;
		//�ε��� �ջ��� �ִ��� �����ϱ� ����.
		if( ($idx_filesize != $nTotal) && ($idx_filesize <= 1) )
		{
			//�ջ� �Ǿ��ٰ� �Ǵ� ����
			if($this->debug) echo("copy last index data") ;
			rename($idx_backup, $idx_file) ;
			return ;
		}
		//�̰��� ��� �ջ�Ǿ��ٰ� ���� �ϴ°�?
		if( $mode == "count" && empty($idx_data[count_pos]) )
		{
			return ;
		}
		*/

		//�ӽ� �ε��� ���Ͽ� ���ų����� ����.
		$tmp_idxfile = $this->base_dir."/counter/data/$_data/".md5(uniqid("")); 
		$fd = wb_fopen( $tmp_idxfile, "w" ) ;

		//2002/03/24 ������ ���Ұ��� ����ġ �ʾ� �ӽ÷� �̰��� ����.
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
				$return_idx['week']  ; //�ϴ� ����
				$return_idx['month'] =  $return_idx['today'];
				$return_idx['year']  =  $return_idx['today'];
				$return_idx['total'] =  $return_idx['today'];
				$return_idx['max']   =	$return_idx['today'];

				for($i = 0 ; $i < sizeof($idx_content) ; $i++ )
				{
					$idx_content[$i] = chop($idx_content[$i]) ;
					if( strlen($idx_content[$i]) == 0 )
					{
						//�߰��� ������ ������ ���� ��찡 ���� �̸� �����ϱ� ����
						continue ;
					}

					// 2002/03/23 eregi�� ��ġ
					if( eregi("<\?php", $idx_content[$i]) || eregi("\?>", $idx_content[$i]) )
					{
						//2002/03/15 ���ε����� ��ȣ�� ���� ��ũ��Ʈ ����,���±� ���
						//��,�Ʒ����� �־��ִϱ�...
						continue ;
					}
					
					//��ü ī��Ʈ ��� �ڷ� ������ ����
					$tmp_row = explode("|", $idx_content[$i]) ;
					//ù���� �����̱� ������ ���� 1�̸� ����...
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
					// 2002/03/23 eregi�� ��ġ
					if( eregi("<\?php", $idx_content[$i]) || eregi("\?>", $idx_content[$i]) )
					{
						//2002/03/15 ���ε����� ��ȣ�� ���� ��ũ��Ʈ ����,���±� ���
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
					//��ü ī��Ʈ ��� �ڷ� ������ ����
					if($write_cnt == 1) //ù��°������:���� 
					{
						$return_idx['today'] = $idx[2] ;
					}
					else if($write_cnt == 2) //�ι�° ������:����
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

			//����⺸�ٴ� ����Ѵ�. ���Ѱ���...
			//�����ٸ� ���������� �����ϵ���...
		if($this->debug) echo("write_cnt[$write_cnt]<br>") ;

		//�ε��� ó���� ������ ��ü ������ �ǹǷ�. 2002/03/16
		$this->total = $write_cnt ;

		//2002/03/26 ���������� �ε����� �������� ���� ����� �ϰ� �ε����� ��ü�Ѵ�.
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
				//2002/03/26 �̺κп��� ������ ���ʾ��� ��� ������ ����.
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
