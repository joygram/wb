<?php
if(!defined("__wb_db_member_pgsql__")) define("__wb_db_member_pgsql__","1") ;
else return ;
/**
///////////////////////////////////////////
// Database Interface & file data handling
// 2002/02
// 2002/03/15 
//	index파일명 자동 변환
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
	var $db_type ;		// 데이터베이스 타입
	var $db_ver ; 		// 보드 데이터의 버젼: 2, 3 
	var $data ;			// 보드 데이터 이름
	var $table ;		// 사용하고자하는 테이블이름
	// sort and filtering
	var $mode ;			// 검색모드 
	var $find_type ;	//
	var $find_key ;
	var $find_field ;

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

	function db_member($data, $table, $mode = "", $find_type = "", $find_key = "", $find_field = "", $db_type = "file", $ver = "2", $base_dir = ".", $sort_field="", $sort_order="ASC" ) 
	{
		$this->init($data, $table, $mode, $find_type, $find_key, $find_field, $db_type, $ver, $base_dir, $sort_field, $sort_order ) ;

		include("$this->base_dir/system.ini.php") ;
		if($this->debug) echo("dbms:postgresql db:$C_db_name") ;
		$this->db_conn = pg_connect("dbname=$C_db_name") 
			or err_abort("db_interface:no db connection,".pg_errormessage($this->db_conn)) ;
	}

	function init($data, $table, $mode = "", $find_type = "", $find_key = "", $find_field = "", $db_type = "file", $ver = "2", $base_dir = ".", $sort_field="", $sort_order="ASC" ) 
	{
		global $C_base ;//temp 

		$this->debug = 0 ;

		$this->version    = "db_interface 0.1 2002/02" ;
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
		//register_shutdown_function("db_interface_destroy") ;
		// open data base  -> 각 데이터베이스의 형태에 맞도록...
	}

	function destroy()
	{
		//DB name system.ini.php로 이동
		include("$this->base_dir/system.ini.php") ;
		if($this->debug) echo("dbms:postgresql db:$C_db_name") ;
		pg_close($this->db_conn) ;
	}


///////////////////////////////////////////
// 데이터 개수 세기
///////////////////////////////////////////
/**
*/
	function count_data() 
	{
		//$count_func = "count_".$this->table."_from_".$this->db_type ;
		//$this->$count_func() ;
		//blow php 4.0.6
		//require check php version

		$query = "SELECT count(*) FROM $this->table" ;
		if($this->find_field && $this->find_key)
			$query .= " WHERE $this->find_field like '$this->find_key'" ;

		$result = pg_exec($this->db_conn, $query) ;
		$query_data = pg_fetch_row($result, 0) ;
		if($this->debug) print_r($query_data) ; 
		$this->total = $query_data[0] ;
	}

	///////////////////////////////////////////
	// index data부분 읽어오기
	///////////////////////////////////////////
	function select_data($offset=0, $limit=1)
	{
		$query = "SELECT * FROM $this->table" ; 
		if($this->find_field && $this->find_key)
			$query .= " WHERE $this->find_field like '$this->find_key'" ;
		if($this->sort_field)
			$query .= " ORDER BY $this->sort_field $this->sort_order" ;
		if($limit)
			$query .= " LIMIT $limit" ;
		if($offset)
			$query .= " OFFSET $offset" ;

		if($this->debug) echo("select_from_postgresql[$query]sort_field[$sort_field]<br>") ;
		$result = pg_exec($this->db_conn, $query) ;
			//loop
		$i = 0 ;
		while ($one_row = pg_fetch_array($result))
		{
			//2002/10/13 file에서 배열정렬 때문에 순서가 바뀌어서 수정되어야 하는 부분 ?
			// Y,X -> X,Y로 변경
			$this->rows[$i] = $one_row ; 
			$i++ ;
		}
			
		$this->num_rows = pg_numrows($result);  //pg_num_rows() ;

		if($this->debug) echo("select_from_postgresql[$this->num_rows]") ;
		$this->row_begin = 0 ;
		$this->row_end  = $this->num_rows ;
		return ;
	}

	// 본문 글을 하나 가져오는 함수
	////////////////////////////////////////
	// 글의 내용이 보관되어 있는 파일에서 자료 가져오기
	//idx_fetch_array and file_fetch_array is converted && converged.
	function row_fetch_array($no = 0, $board_group = "", $board_id = "", $table="board") 
	{
		//$board_group, $board_id가 있으면 select
		if(!empty($board_group))
		{
			$this->select_data() ;
		}

		//print_r
		$this->rows[$no][board_group] = $this->rows[$no][uid] ;
		if($this->debug) echo("row_fetch_array_postgresql:$no,") ;
		return $this->rows[$no] ; 
	}

	/**
		인덱스에 추가/갱신
		갱신한 인덱스를 배열로 리턴한다.
	*/
	function update_index($_data, $index_name, $idx_data, $mode)
	{
		switch($mode)
		{
			case "insert" ;
				if($this->debug) echo("update_member_to_postgresql INSERT<br>") ;
				$result = pg_exec($this->db_conn, "BEGIN") ;

				$query = "SELECT count(*) FROM $this->table WHERE uname='$idx_data[uname]'" ;
				$result = pg_exec($this->db_conn, $query) ;
				if(!$result)
				{
					if($this->debug) echo("update_member_to_postgresql:$query<br>") ;
					$result = pg_exec($this->db_conn, "ROLLBACK") ;
					err_abort("update_member_to_postgresql:query error:$query") ;
				}
				$query_data = pg_fetch_row($result, 0) ;
				if($this->debug) echo("update_member_to_postgresql:".print_r($query_data)) ;
				if($query_data[0] > 0)
				{
					if($this->debug) echo("update_member_to_postgresql:uname already exists $query_data[0]<br>") ;
					$result = pg_exec($this->db_conn, "ROLLBACK") ;
					return E_USER_EXIST ;
				}

					//길이 검사
				$query = "SELECT count(*) FROM $this->table WHERE idnum='$idx_data[idnum]' AND idnum != null" ;
				$result = pg_exec($this->db_conn, $query) ;
				if(!$result)
				{
					if($this->debug) echo("update_member_to_postgresql:$query") ;
					$result = pg_exec($this->db_conn, "ROLLBACK") ;
					err_abort("update_member_to_postgresql:query error:$query") ;
				}
				$query_data = pg_fetch_row($result, 0) ;
				if($query_data[0] > 0)
				{
					if($this->debug) echo("update_member_to_postgresql:idnum already exists<br>") ;
					$result = pg_exec($this->db_conn, "ROLLBACK") ;
					return E_IDNUM_EXIST ;
				}

				$query = "INSERT INTO 
					$this->table (
						uid, 
						uname, 
						gid, 
						password, 
						alias, 
						access_count, 
						point, 
						auth_level, 

						e_grad_year, 
						m_grad_year, 
						h_grad_year, 
						u_grad_year, 
						g_grad_year, 

						lastname, 
						firstname, 
						petname, 
						sex, 
						idnum, 
						birthday, 
						birthday_select, 
						email, 
						homepage, 
						mobilephone, 

						home_country, 
						home_city, 
						home_district, 
						home_address, 
						home_zipcode, 
						home_phone, 
						home_fax, 

						company_country,
						company_city,
						company_district,
						company_address,
						company_zipcode,
						company_name,
						company_department,
						company_title,
						company_phone,
						company_fax,
						company_homepage,

						save_dir,
						update_timestamp
					) 
					VALUES
					(
						nextval('member_seq'),
						'$idx_data[uname]',
						'$idx_data[gid]',
						'$idx_data[password]',
						'$idx_data[alias]',
						'$idx_data[access_count]',
						'$idx_data[point]',
						'$idx_data[auth_level]',

						'$idx_data[e_grad_year]', 
						'$idx_data[m_grad_year]', 
						'$idx_data[h_grad_year]', 
						'$idx_data[u_grad_year]', 
						'$idx_data[g_grad_year]', 

						'$idx_data[lastname]', 
						'$idx_data[firstname]', 
						'$idx_data[petname]', 
						'$idx_data[sex]', 
						'$idx_data[idnum]', 
						'$idx_data[birthday]', 
						'$idx_data[birthday_select]', 
						'$idx_data[email]', 
						'$idx_data[homepage]', 
						'$idx_data[mobilephone]', 

						'$idx_data[home_country]', 
						'$idx_data[home_city]', 
						'$idx_data[home_district]', 
						'$idx_data[home_address]', 
						'$idx_data[home_zipcode]', 
						'$idx_data[home_phone]', 
						'$idx_data[home_fax]', 

						'$idx_data[company_country]',
						'$idx_data[company_city]',
						'$idx_data[company_district]',
						'$idx_data[company_address]',
						'$idx_data[company_zipcode]',
						'$idx_data[company_name]',
						'$idx_data[company_department]',
						'$idx_data[company_title]',
						'$idx_data[company_phone]',
						'$idx_data[company_fax]',
						'$idx_data[company_homepage]',

						'$idx_data[save_dir]',
						'now'::datetime	

					)" ; 
				$result = pg_exec($this->db_conn, $query) ;
				if(!$result)
				{
					if($this->debug) ("update_member_to_postgresql:$query") ;
					err_abort("update_member_to_postgresql:query error:$query") ;
				}
				$result = pg_exec($this->db_conn, "COMMIT") ;
				break ;

			case "update" ;
				$query = "UPDATE $this->table SET
						uname                  =   '$idx_data[uname]',
						gid                    =   $idx_data[gid],
						password               =   '$idx_data[password]',
						alias                  =   '$idx_data[alias]',
						access_count           =   '$idx_data[access_count]',
						point                  =   '$idx_data[point]',
						auth_level             =   '$idx_data[auth_level]',
                                                                             
						e_grad_year            =   '$idx_data[e_grad_year]', 
						m_grad_year            =   '$idx_data[m_grad_year]', 
						h_grad_year            =   '$idx_data[h_grad_year]', 
						u_grad_year            =   '$idx_data[u_grad_year]', 
						g_grad_year            =   '$idx_data[g_grad_year]', 
                                                                             
						lastname               =   '$idx_data[lastname]', 
						firstname              =   '$idx_data[firstname]', 
						petname                =   '$idx_data[petname]', 
						sex                    =   $idx_data[sex], 
						idnum                  =   '$idx_data[idnum]', 
						birthday               =   $idx_data[birthday], 
						birthday_select        =   $idx_data[birthday_select], 
						email                  =   '$idx_data[email]', 
						homepage               =   '$idx_data[homepage]', 
						mobilephone            =   '$idx_data[mobilephone]', 
                                                                             
						home_country           =   '$idx_data[home_country]', 
						home_city              =   '$idx_data[home_city]', 
						home_district          =   '$idx_data[home_district]', 
						home_address           =   '$idx_data[home_address]', 
						home_zipcode           =   '$idx_data[home_zipcode]', 
						home_phone             =   '$idx_data[home_phone]', 
						home_fax               =   '$idx_data[home_fax]', 
                                                                             
						company_country        =   '$idx_data[company_country]',
						company_city           =   '$idx_data[company_city]',
						company_district       =   '$idx_data[company_district]',
						company_address        =   '$idx_data[company_address]',
						company_zipcode        =   '$idx_data[company_zipcode]',
						company_name           =   '$idx_data[company_name]',
						company_department     =   '$idx_data[company_department]',
						company_title          =   '$idx_data[company_title]',
						company_phone          =   '$idx_data[company_phone]',
						company_fax            =   '$idx_data[company_fax]',
						company_homepage       =   '$idx_data[company_homepage]',
                                                                             
						save_dir               =   '$idx_data[save_dir]',
						update_timestamp       =   $idx_data[update_timestamp]
					WHERE uid = $idx_data[uid]" ; 

				$result = pg_exec($this->db_conn, $query) ;
				if(!$result)
				{
					if($this->debug) ("update_member_to_postgresql:$query") ;
					err_abort("update_member_to_postgresql:query error:$query") ;
				}
				break ;

			case "delete" ;
				$query = "DELETE FROM $this->table WHERE uid = $idx_data[uid]" ;
				$result = pg_exec($this->db_conn, $query) ;
				if(!$result)
				{
					if($this->debug) ("update_member_to_postgresql:$query") ;
					err_abort("update_member_to_postgresql:query error:$query") ;
				}
				break ;

			default:
				if($this->debug) echo ("update_index_to_postgresql:unknown mode") ;
				break ;
		}

		//제대로 실행되었는지 검사...
		return DB_OK ;
	}
}

?>
