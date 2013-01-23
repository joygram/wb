<?php
/** 
MySQL Interface 
@note Board Class
@author apollo@WhiteBBS.net
Copyright 2004, WhiteBBS.net
*/

class DB_Interface
{
	var $debug ;

	var $db_host ;
	var $db_user ;
	var $db_passwd ;

	var $db_conn ;
	var $db_name ;		// ����ϰ����ϴ� �����ͺ��̽�

	var $is_open ;		// ������ �����ͺ��̽��� �ߺ����Ⱑ ���� �ʵ��� �����ϱ� ���� ���� 
	var $result ;		// �����

	var $version ; 		// class version
	var $db_type ;		// �����ͺ��̽� Ÿ��
	var $db_ver ; 		// ���� �������� ����: 2, 3 

	function DB_Interface($db_name="",$host = "localhost", $user = "", $passwd = "")
	{
		$this->debug = 1 ;
		$this->db_type = "DB_Mysql" ;

		$this->db_name   = $db_name ;
		$this->db_host   = $host ;
		$this->db_user   = $user ;
		$this->db_passwd = $passwd ;

		$this->result ;

		$this->is_open = 0 ;

		if($this->debug) echo("$this->db_type,DB_Interface() <br>") ;
		if($this->debug)
		{
			echo("$this->db_type,DB_Interface() db_name[$this->db_name], db_host[$this->db_host], db_user[$this->db_user], db_passwd[$this->db_passwd]<br>") ;
		}

		$this->open() ;
	}

	function open($db_name="", $host ="localhost", $user = "", $passwd = "")
	{
		if($this->debug) echo("$this->db_type,DB_Interface::open()<br>") ;

		if($this->is_open) 
		{
			//�ƴϸ� ������ ������ ���� ���ο� ������ �ϵ��� ����
			if($this->debug) echo("$this->db_type,DB_Interface::open() already open. close current connection.<br>") ;
			$this->close() ;
			return ;
		}
		
		//open���� ���ڰ����� �ʱ�ȭ�� ������ �� ��������� �� �ʱ�ȭ�Ͽ� ��� ����ϵ����Ѵ�.
		if($host != "localhost" ) $this->db_host = $host ;
		if(!empty($user)) $this->db_user = $user ;
		if(!empty($passwd)) $this->passwd = $passwd ;

		// open data base  -> �� �����ͺ��̽��� ���¿� �µ���...
		$this->db_conn = mysql_connect($this->db_host, $this->db_user, $this->db_passwd) 
			or die("$this->db_type,DB_Interface::open() Connect Error:". mysql_error()) ;

		mysql_select_db($this->db_name) 
			or die("$this->db_type,DB_Interface::open() Select DB Error:".mysql_error()) ;

		$this->is_open = 1 ;
	}

	function close()
	{
		if($this->debug) echo("$this->db_type,DB_Interface::close()<br>") ;
		if($this->is_open)
		{
			if($this->debug) echo("$this->db_type,DB_Interface::close() close openning connection<br>") ;
			//$result���� ���� ��쵵 �־ �������� �� �� �ִ�. @�� if�񱳾��� �ڵ�
			@mysql_free_result($this->result);
			//�ߺ� close�� ������ ���� �ʵ��� ����. @�θ� ����.
			@mysql_close($this->db_conn);
			$this->is_open = 0 ;
		}
		else
		{
			if($this->debug) echo("$this->db_type,DB_Interface::close() connection is not opened. drop request.<br>") ;
		}
	
	}

	/**
	SQL�� limit mySQL�� ���� ������ֱ�
	postgreSQL������ LIMIT $n OFFSET $n ���� �ۼ����ָ� �ȴ�.
	*/
	function sql_limit($offset, $limit) 
	{
		$sql = "" ;

		// OFFSETó���� MySQL�� ��� LIMIT start,end �� �����Ǿ� �����Ƿ� offset, limit �� �ְ� ���� ��쿡 ���� SQL����
		// offset�� ����ִ� ���� 0�������� �����ϵ��� �ϰ� limit�� ���� ��쿡�� , limit�� �ȸ���.
		if(empty($offset))
		{
			if(!empty($limit)) 
				$sql .= " LIMIT 0, $limit" ;
		}
		else
		{
			if(!empty($limit)) 
				$sql .= " LIMIT $offset, $limit" ;
			else
				$sql .= " LIMIT $offset" ;
		}

		return $sql ;
	}

	/**
	SQL�� ORDER BY mySQL�� ���� ������ֱ�
	*/
	function sql_orderby($sort_field, $sort_type = "ASC") 
	{
		$sql = "" ;
		if(empty($sort_field)) return ;

		$sql = " ORDER BY $sort_field $sort_type" ;

		return $sql ;
	}


	/*UPDATE ���ڿ� ������ֱ� */
	function sql_update($field_array, $data_array) 
	{
		reset($field_array) ;
		while( ($field = current($field_array)) )
		{
			$sql .= "$field = $data_array[$field]"  ;
			if(next($field_array)) $sql .=  ", " ;
		}

		return $sql ;
	}

	function sql_where($condition)
	{
		if(!empty($condition))
			$sql = " WHERE $condition " ;
		return $sql ;
	}

	//������ ������ �迭�� �����ڸ� �־��ָ� $src_array�� implode()�Լ��� ���� ���ڿ��� ��ȯ.
	function ordered_implode($delim, $order_array, $src_array) 
	{
		//ordered_implode(������,�����迭, ����迭) ;
		reset($src_array) ;
		while( ($field = current($order_array)) )
		{
			$ordered_string .= $src_array[$field] ;
			if(next($order_array)) $ordered_string .= $delim ;
		}

		return $ordered_string ;
	}


	function select($sql)
	{
		$this->result = mysql_query($sql) or die("$this->db_type,DB_Interface::select() fail,[$sql]".mysql_error());
	}

	function delete($sql)
	{
		if($this->debug) echo("$this->db_type,DB_Interface::delete()<br>") ;
		$this->result = mysql_query($sql) or die("$this->db_type,DB_Interface::delete() fail,[$sql]".mysql_error());
	}

	function insert($sql)
	{
		if($this->debug) echo("$this->db_type,DB_Interface::insert()<br>") ;
		$this->result = mysql_query($sql) or die("$this->db_type,DB_Interface::insert() fail,[$sql]".mysql_error());
	}

	function update($sql)
	{
		if($this->debug) echo("$this->db_type,DB_Interface::update()<br>") ;
		$this->result = mysql_query($sql) or die("$this->db_type,DB_Interface::update() fail,[$sql]".mysql_error());
	}

	function fetch_row($result_type = MYSQL_ASSOC)
	{
		$row = mysql_fetch_array ($this->result, $result_type) ; 
		return $row ;
	}
}

?>
