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
//	index���ϸ� �ڵ� ��ȯ
**/
//$version = "WhiteBoard 2.3.0 2002/02/04" ;
define("DB_SORT", "1") ;
define("DB_FIND", "2") ;
define("DB_OK",   "1000") ;

define("E_DB_CONN", "1") ;
define("E_DB_QUERY", "2") ;
define("E_USER_EXIST", "3") ;
define("E_IDNUM_EXIST", "4") ;

class db_member
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
		//@todo �˻��ɼ�����

		//for version 2.3.x below
		//������ ������ ���������� ������� �ʵ��� �Ѵ�.
		$_data = $this->data ;

		/*
			//�б⿡ ���� ���ϸ� �дٰ� �ѹ� ������ ���� ������?
		if(wb_lock($idx_file) < 0 )
		{
			err_abort("$data_idx�� ��ױ�(lock) �����߽��ϴ�.") ;
		}	
		*/

		$fd = wb_fopen( $idx_file, "r" ) ;

		// �ε��� ���Ͽ��� ����� ID�� �˻��Ͽ� �� �Ǽ��� ����.
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
	// index data�κ� �о����
	///////////////////////////////////////////
	function select_data($offset=0, $limit=1)
	{
		$_data = $this->data ;
		
		require_once($this->base_dir."/lib/config.php") ;
		$conf = read_board_config($_data) ;
		//backword compatibility
		$C_skin = $conf[skin] ;
		// 1. ���ǿ� �ش��ϴ� �ε����� �����͸� �迭�� select �Ѵ�.
		// �˻������ ���� �ƴѰ�츦 �и�
		$idx_file = $this->base_dir."/member/data/$_data/data.idx" ;
		if(!@file_exists( $idx_file ))
		{
			$idx_file = "${idx_file}.php" ;
		}	
		
		
		//�б⿡ ���� ���ϸ� �дٰ� �ѹ� ������ ���� ������?
		//if(wb_lock($idx_file) < 0 )
		//{
		//	err_abort("$idx_file�� ��ױ�(lock) �����߽��ϴ�.") ;
		//}	

		$fd = wb_fopen( $idx_file, "r" ) ;

		// �ε��� ���Ͽ��� ����� ID�� �˻��Ͽ� �� �Ǽ��� ����.
		// �˻� field�� ���� check �ʿ�.
		// �˻��� ��� ��� �����͸� �о �����ؾ� �ϱ� ������ ���ɿ� ����� ������ �ȴ�. � �����ؾ��� 2002/02/05.
		$data_total = 0 ;
		$nCnt = 0 ;

		$this->num_rows = 0;

		//���������� ����� �縸ŭ�� ���̵��� ����. 
		$this->row_begin = $offset ;
		$this->row_end  = $offset + $limit ;

		// total�� �ƴ϶� ���� ��ü...
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
				// 2002/03/15 �����ͳ����� ��ũ��Ʈ ����,���±� �ɷ�����
				// 2002/03/23 eregi�� ��ġ
			if( eregi("<\?php", $line) || eregi("\?>", $line) )
			{
				continue ;
			}

			//�޸� ����: php������ �ε����� �迭�� ũ�⸦ �¿����� �����Ƿ�...
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
		$conf = read_board_config($_data) ;
		//backword compatibility
		$C_skin = $conf[skin] ;
		//�ε����� �˻��Ͽ� �����͸� ���������� �ؾ� �Ѵ�.
		//�ε������� �ʿ��� ���� �ҷ��� �ϱ� ����

		// if $no is empty then open index and get index data
		// �����̸��� �ִٸ� �ε����� select���� �ʰ� ����ϴ� ����̹Ƿ�  
		// ���Ͽ��� �ҷ����� index���� �����͸� �����´�.
		// writing_head�κа� writing_data�κ� �ΰ��� ���� �����ü� �ֵ��� ���� ���� ���� �ϸ� �̰����� writing_head�� �������������� �Ѵ�. // D1231231 : writing_data, H1231231 : writing_data

		//if($_debug) error_log("[$log_date]start[$board_group][$board_id]\n", 3, $this->base_dir."/logs/error_log") ;
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
			$fp_body = wb_fopen($this->base_dir."/member/data/$_data/$data_file", "r") ; 
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

		//2002/09/24 ����� �����ڻ��� �����Ƿ� �ӽ÷� �ƹ�Ÿ ��θ� ����
		//URL[avatar]�� �������
		//Row[avatar]�� �̸���...
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

		//��ȣȭ �Ǿ������� 
		if( strlen($body[password]) > 15 || $body[encode_type] == "1" ) 
		{
			$body[name] = base64_decode($body[name]) ;
			$body[subject] = base64_decode($body[subject]) ;
		}

		$body[style_id]  = substr($body[board_id],1) ;

		// ������ �Ϻκ��� �־���. 2002/09/15
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

		//board_group�� ���� ���� index������ �����Ͽ� �����͸� �������� ����̹Ƿ�
		//���ó���� �ʿ��� ����̴�.
		//������ board_group�� �ִ°�쿡�� ���� ������ ������ �������� ���̹Ƿ� 
		//�̰�쿡�� reply_list�� cat���� �ϳ��� ���� �������� �����̱� ������ 
		//��� ó���� �ϸ� �ȵȴ�. 2002/03/17
		//�ٸ� �������̽��� �־�����...
		if( empty($board_group) )
		{
			////////////////////////////////
			//��� ó��
			////////////////////////////////
			//cat.html�� �ִٸ� �Խ��� �����̱� ������ ����Ʈ���� ��۵��� �������� �ʴ´�.
			if( $conf[small_reply_use] == "1" && !@file_exists($this->base_dir."/member/skin/$conf[skin]/cat.html") )
			{
				$body[reply_list] = wb_reply_list($_data, $this->rows["board_group"][$no], $this->rows["board_id"][$no], "variable", $this->base_dir) ;
				if( ! @file_exists($this->base_dir."/member/skin/$conf[skin]/reply_list.html") )
				{
						//�⺻ ��� ��� ���
					$body[comment] .= $body[reply_list] ;
				}
			}
		}
		//�� ī��Ʈ�� �̸� ���̱�
		$body[cnt_homepage]  = $body[cnt] ;
		$body[cnt_download]  = $body[cnt3] ;
		$body[cnt_download2] = $body[cnt2] ;
		$body[cnt_view]      = $body[cnt4] ;
		//��¥ó��
		//���������� ��� ��¥ ��Ʈ�� ��ȯ�� ���� timestamp�� �־��ش�.
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
            $body[w_year]  = date("Y", $body[timestamp]) ; // 4�ڸ�
            $body[w_year2] = date("y", $body[timestamp]) ; // 2�ڸ�
        }

        $body[w_mon]   = date("m", $body[timestamp]) ; // ����
        $body[w_mon2]  = date("M", $body[timestamp]) ; // �����
        $body[w_day]   = date("d", $body[timestamp]) ;
        $body[w_hour]  = date("H", $body[timestamp]) ;
        $body[w_hour2] = date("h", $body[timestamp]) ;
        $body[w_min]   = date("i", $body[timestamp]) ; 
        $body[w_sec]   = date("s", $body[timestamp]) ;
		$body[w_ampm]  = date("A", $body[timestamp]) ; // A.M. P.M.
		
		$body[category_name] = category_name($_data, $body[type], $this->base_dir) ;
		//echo("body[category_name]:$body[category_name]<br>") ;
			// anonymous���� ���� �⺻�� setting
		if( empty($body[uid]) || !isset($body[uid]) )
		{
			$body[uid] = 0 ;
		}
		return $body ;
	}


	/// �������� ȣȯ�� ���� ������ ���ϸ� ����
	function idx_filename( $idx_name, $data )
	{
		if( empty($idx_name) )
		{
			$name = "data" ;
		} 
		//�����ϴ� ���� ���� ��ü�� ���� �ɸ� delay������ �ɼ� �����Ƿ� 
		//���� ���� �ʴ´�. �ٸ� ������ ����� �ҽ� �� ���� �ִ�. ( ������ ���� ��� )
		//���� ������ ������ �����ϸ� ���� ������ �ִ� �κ�.
		$idx_filename = $this->base_dir."/member/data/$data/${idx_name}.idx" ;

		//2002/03/16 2.1.2 ���� ���������� �⺻������ data.idx ��� ���� �̸��� ����ϱ� ������
		//2002/03/15, �ε��� ���� �̸� �ڵ� ���� 
		if( @file_exists($idx_filename) )
		{
			if( ! @file_exists("${idx_filename}.php") )
			{
				if($this->debug) echo("to rename [${idx_filename}.php]") ;

				//@todo ���� �ϵ��ũ ���� �˾Ƴ���
				rename($idx_filename, "${idx_filename}.php") ;
				$idx_filename = "${idx_file}.php" ;
			}
			else
			{
				//�ΰ��� ������ �� �����ϸ� ũ�� �˻縦 �Ѵ��� ũ�Ⱑ �ִ� �͸� ���ܵд�.
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
		//���� �ε��� ũ��� ��ü ���� ������ ���Ͽ� �̻��� ������ �˻�
		$idx_file_size = count( $idx_content ) ;

		$cnt_file = file( $this->base_dir."/member/data/{$this->data}/total.cnt" ) ;

        $data_total = $cnt_file[0] ;


		if( ($idx_file_size != $data_total) && ($idx_file_size <= 1) )
		{
			//�ջ� �Ǿ��ٰ� �Ǵ� ����
			if($this->debug) echo("copy last index data") ;
			rename($idx_backup, $idx_filename) ;
			return true ;
		}

		//�̰��� ��� �ջ�Ǿ��ٰ� ���� �ϴ°�?
		if( $mode == "count" && empty($idx_data[count_pos]) )
		{
			return true ;
		}
	}


	/// �ε��� ������ ���� �߰� 
	function insert_idx( $idx_fd, $member, $idx_content )
	{
		$idx_row = $member->implode( "|" ) ;
	
		// @todo �ߺ��Ǵ� �����Ͱ� �����ϴ°� �˻� 
		// @todo gid, uid ���� 

		if($this->debug) echo("[$idx_row]") ;

		fwrite($idx_fd, "$idx_row\n") ;

		$write_cnt++ ;

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
			
			fwrite($idx_fd, "$idx_content[$i]\n" ) ;
			$write_cnt++ ;
		}
		
	}

	///	�ε����� �߰�/����
	///	������ �ε����� �迭�� �����Ѵ�.
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

		//�ӽ� �ε��� ���Ͽ� ���ų����� ����.
		$tmp_idx_file = $this->base_dir."/member/data/$_data/".md5(uniqid("")); 
		$idx_fd = wb_fopen( $tmp_idx_file, "w" ) ;

		//2002/03/24 ������ ���Ұ��� ����ġ �ʾ� �ӽ÷� �̰��� ����.
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
				//�̰����� �̸��� ������ ���ڵ� �Ѵ�.
				//if($_debug) error_log("[$log_date][$mode]: data[$_data],idx_data[count_pos][".$idx_data[count_pos]."]\n", 3, $this->base_dir."/logs/error_log") ;
				if ($mode =="count" ) 
				{
					$count_pos = "cnt".$idx_data[count_pos] ;
					$idx_data[$count_pos] = "1" ;
				}

				// $idx_data[board_group], $idx_data[board_id]
				for($i = 0 ; $i < $idx_file_size ; $i++ )
				{
					//��� ���κп� �߰� �ڷᰡ ����� 
					// \n�� ������ ���� ������ ����� ����������.
					$idx_content[$i] = chop($idx_content[$i]) ;
					if( strlen($idx_content[$i]) == 0 )
					{
						// �߰��� \n���� ����� ���� ��� ������ ���� ���
						continue ;
					}

					// 2002/03/23 eregi�� ��ġ
					if( eregi("<\?php", $idx_content[$i]) || eregi("\?>", $idx_content[$i]) )
					{
						//2002/03/15 ���ε����� ��ȣ�� ���� ��ũ��Ʈ ����,���±� ���
						//��,�Ʒ����� �־��ִϱ�...
						continue ;
					}

					$idx = explode("|", $idx_content[$i]) ;

					// nReply�� �ִ� ���� ����� ������ �ð��� �����ϴ� ���̹Ƿ� 
					// ���� board_group�� ���� ��Ų��. 
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
							//�ּ�ó���� ������ ���� ���� ó��
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

						// nReply�� �ִ� ���� ������Ű�� ����̹Ƿ�...
						//if($this->debug) echo("nReply[$idx[12]]<br>") ;
						$idx[12] = empty($idx_data[nReply]) ? $idx[12] : intval($idx[12])+1 ;
						if($this->debug) echo("nReply[$idx[12]]<br>") ;
						//��ü ����� ����, 2002.03.06
						//������ ���� �ʵ��̹Ƿ� �ڵ����� ������ֵ��� �Ѵ�.
						if(empty($idx[13]))
						{
							// 2.1.x�뿡���� ���� �����ϸ� ù�۰� ������ 
							$idx[13] = intval($idx[12])+1 ; // ����� ������ ù���� ���� ���ϱ�
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
					// index���� ���� ó��
					// �����Ͱ� �������� ������ �� �� �ִ�.
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
						// �߰��� \n���� ����� ���� ��� ������ ���� ���
						continue ;
					}

						// 2002/03/23 eregi�� ��ġ
					if( eregi("<\?php", $idx_content[$i]) || eregi("\?>", $idx_content[$i]) )
					{
						//2002/03/15 ���ε����� ��ȣ�� ���� ��ũ��Ʈ ����,���±� ���
						//��,�Ʒ����� �־��ִϱ�...
						continue ;
					}

					$idx = explode("|", $idx_content[$i]) ;

					if( $idx[0] == $idx_data[board_group] && $idx[1] == $idx_data[board_id] ) 
					{
							//write����� �������� ���� ó�� 
						if($this->debug) echo("is main writing...") ;
						//�̺κп��� ��� �����Ͱ� �����Ǿ����� ���� �����ϴ� ������ �����Ͽ��� ��.
						$return_idx[main_writing_delete] = "1" ;

						/*
						$idx[13]-- ; //��ü�� ���� ����
						if($this->debug) echo("��ü�� ����$idx[13]<br>") ;
							//��ü���� ���� 0���ϸ� ����Ͽ� �������� ����ó��
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
							//��۸� ����� ��� ��ۼ� ����
						$idx[12]-- ; // ��ۼ� ����
						$idx[13]-- ; //��ü ������ �۰����� ���� 

							//��ü���� ���� 0���ϸ� ����Ͽ� �������� ����ó��
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

			//����⺸�ٴ� ����Ѵ�. ���Ѱ���...
			//�����ٸ� ���������� �����ϵ���...
		if($this->debug) echo("write_cnt[$write_cnt]<br>") ;

		//�ε��� ó���� ������ ��ü ������ �ǹǷ�. 2002/03/16
		$this->total = $write_cnt ;

		//2002/03/26 ���������� �ε����� �������� ���� ����� �ϰ� �ε����� ��ü�Ѵ�.
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
				//2002/03/26 �̺κп��� ������ ���ʾ��� ��� ������ ����.
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