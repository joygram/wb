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
	var $db_name ;		// 사용하고자하는 데이터베이스

	var $is_open ;		// 동일한 데이터베이스에 중복열기가 되지 않도록 방지하기 위한 변수 
	var $result ;		// 결과셋

	var $version ; 		// class version
	var $db_type ;		// 데이터베이스 타입
	var $db_ver ; 		// 보드 데이터의 버젼: 2, 3 

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
			//아니면 기존의 연결을 끊고 새로운 연결을 하도록 지정
			if($this->debug) echo("$this->db_type,DB_Interface::open() already open. close current connection.<br>") ;
			$this->close() ;
			return ;
		}
		
		//open에서 인자값으로 초기화가 들어왔을 때 멤버변수를 재 초기화하여 계속 사용하도록한다.
		if($host != "localhost" ) $this->db_host = $host ;
		if(!empty($user)) $this->db_user = $user ;
		if(!empty($passwd)) $this->passwd = $passwd ;

		// open data base  -> 각 데이터베이스의 형태에 맞도록...
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
			//$result값이 없을 경우도 있어서 경고오류가 날 수 있다. @로 if비교없이 코딩
			@mysql_free_result($this->result);
			//중복 close로 오류가 나지 않도록 조정. @로만 조정.
			@mysql_close($this->db_conn);
			$this->is_open = 0 ;
		}
		else
		{
			if($this->debug) echo("$this->db_type,DB_Interface::close() connection is not opened. drop request.<br>") ;
		}
	
	}

	/**
	SQL의 limit mySQL용 문장 만들어주기
	postgreSQL에서는 LIMIT $n OFFSET $n 으로 작성해주면 된다.
	*/
	function sql_limit($offset, $limit) 
	{
		$sql = "" ;

		// OFFSET처리는 MySQL의 경우 LIMIT start,end 로 구성되어 있으므로 offset, limit 가 있고 없는 경우에 따라서 SQL생성
		// offset이 비어있는 경우는 0에서부터 시작하도록 하고 limit가 없는 경우에는 , limit를 안만듦.
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
	SQL의 ORDER BY mySQL용 문장 만들어주기
	*/
	function sql_orderby($sort_field, $sort_type = "ASC") 
	{
		$sql = "" ;
		if(empty($sort_field)) return ;

		$sql = " ORDER BY $sort_field $sort_type" ;

		return $sql ;
	}


	/*UPDATE 문자열 만들어주기 */
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

	//순서를 지정한 배열과 구분자를 넣어주면 $src_array를 implode()함수와 같이 문자열로 변환.
	function ordered_implode($delim, $order_array, $src_array) 
	{
		//ordered_implode(구분자,순서배열, 내용배열) ;
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
