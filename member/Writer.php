<?php



class Writer
{
	var $_db ; 

	var $_globals ; 

	var $_skindir ;

	var $_basedir ;

	var $_write_form ;

	var $_member ;

	var $_auth ;

	var $_conf ;




	function Writer( $db, $auth )
	{

		$this->_globals = prepare_server_vars() ;

		$this->_auth = $auth ;

		$this->_db = $db ; 


		$this->_basedir = $this->_auth->auth_data[base_dir] ;

		$this->_conf = read_member_config( $this->_db, $this->_basedir ) ;

		$this->_skindir = "{$this->_basedir}/member/skin/{$this->_conf[skin]}" ;	

		$this->_write_form = "write" ;

	}


	/// GET�̳� POST ������ �ϳ��� �����. ȥ������ ������. 
	function get_server_var()
	{
    	eval( $this->_globals ) ;  //���� ���� ���� ��� ����.

		if( empty( $__POST["uid"] ) )
		{
			return $__GET ;
		}

		return $_POST ;
	}


	function set_data()
	{
    	eval( $this->_globals ) ;  //���� ���� ���� ��� ����.

		$VAR = get_server_var() ;

		$this->_member->set( "uid",					$VAR[uid] ) ;
		$this->_member->set( "gid",					$VAR[gid] ) ;

		$this->_member->set( "uname",				$VAR[uname] ) ;
		$this->_member->set( "alias",				$VAR[alias] ) ;

		$this->_member->set( "password",			$VAR[password] ) ;

		$this->_member->set( "access_count",		$VAR[access_count] ) ;
		$this->_member->set( "point",				$VAR[point] ) ;
		$this->_member->set( "auth_level",			$VAR[auth_level] ) ;

		$this->_member->set( "name",				$VAR[name] ) ;
		$this->_member->set( "lastname",			$VAR[lastname] ) ;
		$this->_member->set( "firstname",			$VAR[firstname] ) ;

		$this->_member->set( "sex",					$VAR[sex] ) ;
		$this->_member->set( "idnum",				$VAR[idnum] ) ;

		$this->_member->set( "birthday",			$VAR[birthday] ) ;
		$this->_member->set( "lunar_birth",			$VAR[lunar_birth] ) ;

		$this->_member->set( "email",				$VAR[email] ) ;
		$this->_member->set( "homepage",			$VAR[homepage] ) ;
		$this->_member->set( "mobilephone",			$VAR[mobilephone] ) ;

		$this->_member->set( "note",				$VAR[note] ) ;

		$this->_member->set( "final_scholarship",	$VAR[final_scholarship] ) ;
		$this->_member->set( "job_kind",			$VAR[job_kind] ) ;
		$this->_member->set( "foreigner",			$VAR[foreigner] ) ;

		$this->_member->set( "home_country",		$VAR[home_country] ) ;
		$this->_member->set( "home_city",			$VAR[home_city] ) ;
		$this->_member->set( "home_district",		$VAR[home_district] ) ;
		$this->_member->set( "home_address",		$VAR[home_address] ) ;
		$this->_member->set( "home_zipcode",		$VAR[home_zipcode] ) ;
		$this->_member->set( "home_phone",			$VAR[home_phone] ) ;
		$this->_member->set( "home_fax",			$VAR[home_fax] ) ;

		$this->_member->set( "company_country",		$VAR[company_country] ) ;
		$this->_member->set( "company_city",		$VAR[company_city] ) ;
		$this->_member->set( "company_district",	$VAR[company_district] ) ;
		$this->_member->set( "company_address",		$VAR[company_address] ) ;
		$this->_member->set( "company_zipcode",		$VAR[company_zipcode] ) ;
		$this->_member->set( "company_name",		$VAR[company_name] ) ;
		$this->_member->set( "company_department",	$VAR[company_department] ) ;
		$this->_member->set( "company_title",		$VAR[company_title] ) ;
		$this->_member->set( "company_phone",		$VAR[company_phone] ) ;
		$this->_member->set( "company_fax",			$VAR[company_fax] ) ;
		$this->_member->set( "company_homepage",	$VAR[company_homepage] ) ;

		$timestamp = time() ; //�پ��� �ð������� �����ϱ� ���ؼ�

		$this->_member->set( "create_time",			$timestamp ) ;
		$this->_member->set( "modify_time",			$timestamp ) ;
		//$this->_member->set( "login_time", $login_time ) ;
		$this->_member->set( "save_dir",			$save_dir ) ;

		$this->_member->set( "password_clue",		$VAR[password_clue] ) ;
		$this->_member->set( "password_answer",		$VAR[password_answer] ) ;
		$this->_member->set( "email_receive",		$VAR[email_receive] ) ;

	}


	/// ������ ���� 
	function correct_data()
	{
		// boolean�� ���� mysql���� boolean���� ����.

		//$update_timestamp = $timestamp ;

		//$birthday_select = ($birthday_select == "on")? 't':'f' ;

		$this->_member->set( "lunar_birth", (($this->_member->get("lunar_birth") =='t')? 1:0) )  ;

		$this->_member->set( "email_receive", (( $this->_member->get("email_receive") =='t')? 1:0) ) ;

		$this->_member->set( "foreigner", (($this->_member->get("foreigner") =='t')? 1:0) ) ;

		$idnum = eregi_replace("(\.\.|\/|`|'|;|#|~|-|@|\?|=|&|!)", "", $this->_member->get("idnum") ) ;
		$this->_member->set( "idnum", $idnum ) ;

		$this->_member->set( "sex", (($this->_member->get("sex") =='t')? 1:0) ) ;
		

		//if( ! 
		$this->_member->get("idnum") ;

			
		//&& 
		//	! $this->_member->get("foreigner") )
		//{
		if(substr($idnum, 6, 1) == "1") $sex = '1' ;
		if(substr($idnum, 6, 1) == "2") $sex = '0' ;
		//}
	}

	
	/// ���ε� ���� ó�� 
	function move_upload_file() 
	{
		//������ �޾Ҵ��� check
		//������ ���ϴ� ���丮�� ������ �� �����Ѵ�.
		if( $InputFile != "none" && !empty($InputFile) )
		{
			if( !check_string_pattern( $C_attach1_ext, $InputFile_name ) )
			{
				err_abort("Ȯ���ڰ�[$C_attach1_ext]�� ���ϸ� �ø��� �� �ֽ��ϴ�."); 
			}
			$attach_file = "${board_group}.${InputFile_name}_attach" ;
				// 2002/03/26 open_basedir ���Ѷ����� ����.
			move_uploaded_file($InputFile, "$C_base[dir]/member/data/$data/$attach_file") ;
		}
		if( $InputFile_size == 0 )
		{
			$InputFile_name = "" ;
		}

	}


	/// DB�� ���� 
	function insert_to_db() 
	{

		$dbi = new db_member($data, "member", $mode, $filter_type, $key, $field, $C_base[member_db_type], "2", $C_base[dir] ) ;
		
		$index_name = "data" ;
		$ret_data = $dbi->update_index($data, $index_name, $idx_data, "insert") ;	
		switch($ret_data)
		{
			case E_QUERY :
				err_abort("SQL��û �����Դϴ�.<br> �����ڿ��� �������ּ���.") ; 
				break ;

			case E_USER_EXIST :
				err_abort("[$idx_data[uname]]����� �̸��� ������Դϴ�. �ٸ� ���̵� �������ּ���.") ; 
				break ;
		}
		
	}


	/// ���̼��� ���
	function print_licence()
	{
		// ��Ű���� header sent������ �߻��Ͽ� ������ �ٲ�
		echo("<!--$my_version-->\n") ;
		$lang = "kr" ;
		include("$C_base[dir]/admin/bsd_license.$lang") ;
	}


	function insert()
	{
		$this->_auth->run_mode(WRITE_MODE) ;

		$this->_auth->perm($C_auth_user, $C_auth_group, $C_auth_perm, $check_data) ;

		//$sess = $this->_auth->member_info() ;

		$this->print_licence() ;
	
		$this->set_data() ;

		$this->correct_data() ;

		$this->move_upload_file() ;

		$this->insert_to_db() ;

	
		err_msg("���Խ�û�� �Ͽ����ϴ�.") ;
		
		redirect( $C_base[url], 1 ) ;
	} 


	function outer_header()
	{
		if( $outer_header_use == "1" ) 
		{
			// �ܺ� �Ӹ��� ����
			for($i = 0 ; $i < sizeof($C_OUTER_HEADER) ; $i++ )
			{
				if( !empty($C_OUTER_HEADER[$i]) )
				{
					@include($C_OUTER_HEADER[$i]) ;
				}
			}
		}
		echo("<!--$my_version-->\n") ;

	}
	

	function header() 
	{
		$M_conf = $this->_conf ; 
	
		//print_r( $M_conf ) ;


		//$M_conf[table_size] = empty($C_table_size)?500:$C_table_size ;  
		//$C_table_size = empty($C_table_size)?500:$C_table_size ; 
		//$M_$Row['table_size'] = $C_table_size ;

		
		//$M_row[]
		//$M_hide[]
		//$B_row[]

			//HEADER ��ҹ��� ���о��� �б� v 1.3.0 
			// ����忡 �´� header�� ������ �� ����� �̿�
		if( @file_exists("{$this->_skindir}/header.html") )
		{
			include("{$this->_skindir}/header.html") ;
		}
		else
		{
			err_abort( "$this->_skindir/header.html ������ �������� �ʽ��ϴ�." ); 
		}

	}


	function outer_footer()
	{
		if( $C_write_outer_header_use == "1" || 
			!isset($C_write_outer_header_use) )
		{
			$outer_header_use = 1 ;
		}


		if( $outer_header_use == "1" ) 
		{
			//�ܺ� ������ ����
			for($i = 0 ; $i < sizeof($C_OUTER_FOOTER) ; $i++ )
			{
				if( !empty($C_OUTER_FOOTER[$i]) )
				{
					@include($C_OUTER_FOOTER[$i]) ;
				}
			}
		}
	}



	function footer()
	{
		//$hide = make_comment($this->_db, $Row, NOT_USE, "member") ;
	
		$this->outer_footer() ;
	}


	function write_header() 
	{

		if( @file_exists("{$this->_skindir}/{$this->_write_form}_header.html") )
		{
			include("{$this->_skindir}/{$this->_write_form}_header.html") ;
		}
	}



	function write_form()
	{

		//2002/03/18 �⺻ ���Ѱ�����
		if( !isset($C_auth_perm) )
		{
			if($C_write_admin_only == 1)
			{
				$C_auth_perm = "7555" ; //�⺻ ���� ����
				$C_auth_cat_perm = "7555" ;
				$C_auth_reply_perm = "7555" ;
			}
			else
			{
				$C_auth_perm = "7667" ; //�⺻ ���� ����
				$C_auth_cat_perm = "7667" ;
				$C_auth_reply_perm = "7667" ;
			}
			$C_auth_user = "root" ; //�⺻ ������ ���̵� 
			$C_auth_group = "wheel" ; //�⺻ ������ �׷�
		}


		//��尡 ������ �ȵǾ� �ִ� ��� : ó�� �۾���� ����...
		$mode = "insert" ;

		$this->_auth->run_mode(WRITE_MODE) ;

		$this->_auth->perm($C_auth_user, $C_auth_group, $C_auth_perm, $check_data) ;

		//$sess = $auth->member_info() ;

		if( ! $this->_auth->is_anonymous() )
		{
			err_abort("�α��� �߿��� �����Ͻ� �� �����ϴ�.") ;
		}

		$this->outer_header() ;

		$this->header() ;

		$this->write_header() ;

		include "{$this->_skindir}/{$this->_write_form}.html" ;

		$this->footer() ;

		$this->outer_footer() ;

	}

}


?>