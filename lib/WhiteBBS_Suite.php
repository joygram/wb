<?php

class WhiteBBS_Suite
{
	
	var $_table ; 

	var $_globals ; 

	var $_skindir ;

	var $_basedir ;

	var $_auth ;

	var $_dbi ;

	/// ��Ų ���� 
	var $_conf ;
	
	var $_row ;
	
	var $_url ;
	
	var $_hide ;
	
	/// ó�� ���� 
	var $_chk_vars ;	

	var $_debug ;


	function WhiteBBS_Suite()
	{
		$this->_debug = 1 ;		
	}
	
	/// GET�̳� POST ������ �ϳ��� �����. whiteBBS�� �����͸� ����
	function get_server_var( $chk_vars )
	{
		/// �ƴϸ� �ش��ϴ� ������ ã�Ƽ� �Ѱ��ִ� ����� �������... 
		
    	eval( $this->_globals ) ;  //���� ���� ���� ��� ����.

		// @todo $chk_vars array�� üũ �迭 �������鼭 üũ 

		if( array_key_exists( $chk_vars, $__POST ) )
		{
			return $__POST ;
		}

		return $_GET ;
	}

	
	///
	function check_perm()
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


		$this->_auth->run_mode(WRITE_MODE) ;

		$this->_auth->perm($C_auth_user, $C_auth_group, $C_auth_perm, $check_data) ;

		if( ! $this->_auth->is_anonymous() )
		{
			err_abort("�α��� �߿��� �����Ͻ� �� �����ϴ�.") ;
		}
	}
	
	/// �ܺθӸ��� 
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
		$Row_m = $this->_row ;

		$Conf_m = $this->_conf ; 

		if( $this->_debug) print_r( $Conf_m ) ;
		//$URL_m = make_url($data, $Row, "member") ;

	
		//print_r( $M_conf ) ;


		//$M_conf[table_size] = empty($C_table_size)?500:$C_table_size ;  
		//$C_table_size = empty($C_table_size)?500:$C_table_size ; 
		//$Row_m[table_size] = $C_table_size ;

		
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


	///�ܺ� ������
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


	/// ������
	function footer()
	{
		$Row_m = $this->skin_var_row() ;

		$Conf_m = $this->_conf ; 


		//$hide = make_comment($this->_table, $Row, NOT_USE, "member") ;


		$this->outer_footer() ;
	}


	function write_header() 
	{

		if( @file_exists("{$this->_skindir}/{$this->_write_form}_header.html") )
		{
			include("{$this->_skindir}/{$this->_write_form}_header.html") ;
		}
	}


	
	
} ;

?>