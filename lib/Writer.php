<?php

require_once("../lib/WhiteBBS_Suite.php") ;

class Writer extends WhiteBBS_Suite
{
	var $_write_form ;

	var $_mode ;


	function Writer( $table, $auth )
	{
		$this->_globals = "global ".prepare_server_vars()." ;" ;

		$this->_auth = $auth ;

		$this->_table = $table ; 


		$this->_basedir = $this->_auth->auth_data[base_dir] ;

		$this->_debug = 0 ;

	}

	/// ������ ������ ������ WEB���κ��� ���� ������ ���� 
	function set_data()
	{
	}


	/// ������ ���� 
	function correct_data()
	{
	}

	
	/// ���ε� ���� ó�� 
	function move_upload_file() 
	{
	}


	/// DB�� ���� 
	function insert_to_db() 
	{
	}

	///
	function include_write_form() 
	{
	}




	///�۾��� �� 
	function write_form()
	{
		$this->_mode = "insert" ;

		$this->check_perm() ;

		$this->outer_header() ;

		$this->header() ;

		$this->write_header() ;

		$this->include_write_form() ;
		
		$this->footer() ;

		$this->outer_footer() ;
	}


	///
	function insert()
	{
		$this->check_perm() ;

		$this->print_licence() ;
	
		$this->set_data() ;

		$this->correct_data() ;

		$this->move_upload_file() ;

		$this->insert_to_db() ;

	
		err_msg("���Խ�û�� �Ͽ����ϴ�.") ;
		
//		redirect( $C_base[url], 1 ) ;
	} 


	/// ���̼��� ���
	function print_licence()
	{
		// ��Ű���� header sent������ �߻��Ͽ� ������ �ٲ�
		echo("<!--$my_version-->\n") ;
		$lang = "kr" ;
		include("{$this->_basedir}/admin/bsd_license.$lang") ;
	}






}


?>