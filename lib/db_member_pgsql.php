<?php
if(!defined("__wb_db_member_pgsql__")) define("__wb_db_member_pgsql__","1") ;
else return ;
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
		// open data base  -> �� �����ͺ��̽��� ���¿� �µ���...
	}

	function destroy()
	{
		//DB name system.ini.php�� �̵�
		include("$this->base_dir/system.ini.php") ;
		if($this->debug) echo("dbms:postgresql db:$C_db_name") ;
		pg_close($this->db_conn) ;
	}


///////////////////////////////////////////
// ������ ���� ����
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
	// index data�κ� �о����
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
			//2002/10/13 file���� �迭���� ������ ������ �ٲ� �����Ǿ�� �ϴ� �κ� ?
			// Y,X -> X,Y�� ����
			$this->rows[$i] = $one_row ; 
			$i++ ;
		}
			
		$this->num_rows = pg_numrows($result);  //pg_num_rows() ;

		if($this->debug) echo("select_from_postgresql[$this->num_rows]") ;
		$this->row_begin = 0 ;
		$this->row_end  = $this->num_rows ;
		return ;
	}

	// ���� ���� �ϳ� �������� �Լ�
	////////////////////////////////////////
	// ���� ������ �����Ǿ� �ִ� ���Ͽ��� �ڷ� ��������
	//idx_fetch_array and file_fetch_array is converted && converged.
	function row_fetch_array($no = 0, $board_group = "", $board_id = "", $table="board") 
	{
		//$board_group, $board_id�� ������ select
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
		�ε����� �߰�/����
		������ �ε����� �迭�� �����Ѵ�.
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

					//���� �˻�
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

		//����� ����Ǿ����� �˻�...
		return DB_OK ;
	}
}

?>
