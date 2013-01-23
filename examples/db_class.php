<?php
/**
@file
@brief Board Class
@author apollo@WhiteBBS.net
Copyright 2004, whiteBBS.net
@date 2004/08/24

����� ���� ���� ���� �� ���� 
ȣȯ�� ���δ� ���� ��ɿϷ��� ����غ����� �Ұ�.
*/

/**
�����ͺ��̽� Ÿ�Կ� ���߾� include�ϱ�
include("$db_class.inc.php") ;
Skin Class�� Timer Class���� �ʿ�.
*/
$db_type = "DB_Mysql" ;
require_once("$db_type.php") ;
require_once("Skin_Base.php") ;
require_once("../lib/Timer.php") ;

class Board extends DB_Interface
{
	var $debug ; 
	var $timer ;

	var $header_field ;
	var $data_field ; //�̰� data_field��� �ұ�?

	var $board_name ;		//�����
	var $db_header_table ; //����� ������ �ִ� ���̺� ��
	var $db_data_table ; //�� ������ ������ �ִ� ���̺� ��

	var $list_rows ;	//��� �κ��� �� �������� �����͵�
	var $data_rows ;	//�� �ϳ��� ���� �����͵�
	var $total_count ; //��ü �������� ����

	//var $C_base ;	// �ý��� �⺻ ȯ��
	var $conf ;

	function Board($board_name="")
	{
		$this->debug = 1 ;
		$this->timer = new Timer() ; //����ð� üũ�� ���ؼ�
		$this->list_rows = array() ;
		$this->data_rows = array() ;

		// DB��Ű���� ������ �ش��ϴ� �κ��� �ش� ���ø����̼ǿ� ����صΰ� insert�� update� Ȱ���ϵ��� �Ѵ�.
		$this->header_fields = array(
			"board_group","board_id","uid","uname","subject","subject_color",
			"type","date_update","date_write",
			"cnt_view","cnt_reply","cnt_article","cnt_down1","cnt_down2","mail_reply") ;

		$this->data_fields = array(
			"board_group","board_id","encode_type","uid","uname","password","email","homepage",
			"date_write","date_update","attach_name","attach_size","attach_type",
			"attach2_name","attach2_size","attach2_type","bgimg","link","remote_ip",
			"use_html","use_br","note") ;

		$this->set_table_name($board_name) ;

		DB_Interface::DB_Interface("whitebbs", "localhost", "whitebbs", "whitebbs") ;

		if($this->debug) $this->timer->start("Board") ;
		if($this->debug) echo("Board::Board() board_name[$this->board_name], ".time()."<br>") ;

		$this->init_env() ; //�ý��� �⺻ȯ�� ����
	}

	function init_env()
	{
		//�ý��� �⺻ ȯ�� �о����
		//�⺻���丮�� ���� �غ�.
		require_once("../lib/system_ini.php") ;
		require_once("../lib/get_base.php") ;
		$this->C_base = get_base(1) ; //������ �Ǵ� �ּ� �޾ƿ���, lib.php�� ����.

		//�⺻���̺귯�� include.
		$C_base = $this->C_base ; // wb.inc.php���� $C_base�� �����.
		require_once("{$this->C_base[dir]}/lib/wb.inc.php") ;

		if($this->debug) echo("{$this->board_name}<br>") ;
		//ȯ�漳�� �б�.
		$this->conf = read_board_config($this->board_name, $this->C_base) ;
		return ;
	}

	function auth()
	{
		//$C_base = $this->C_base ;

		//����, ����ó��.
		require_once("$this->C_base[dir]/auth/auth.php") ;
		if( $log == "on" ) $auth->login() ; 
		else if( $log == "off" ) $auth->logout() ; 
		return ;
	}

	function close() 
	{	
		DB_Interface::close() ;
		if($this->debug) $this->timer->report() ;
	}

	function set_table_name($board_name)
	{
		if(empty($board_name)) return ;

		$this->board_name = $board_name ;
		$this->db_header_table = "wb_".$this->board_name."_header" ;
		$this->db_data_table = "wb_".$this->board_name."_data" ;			

		if($this->debug) echo("Board::set_table_name() db_header_table[$this->db_header_table], db_data_table[$this->db_data_table]<br>") ;

	}

	//DB�� �ϳ��� ���� ����
	//���⿡�� ó���۰� ����� �����Ͽ� �����ϴ� ����� �־�� ��.
	//Ʈ����� ó���� �ʿ���.
	function insert_data($header, $data, $board_name="")
	{
		if($this->debug) echo("Board::insert_data()<br>") ;
		$this->timer->start("Board::insert") ;

		$this->set_table_name($board_name) ;

		$header_field_str = implode(",", $this->header_fields) ;
		//xSQL���� field���� ������ ������ ������ ������ �ݵ�� ��ġ�ؾ� �ϹǷ� insert�� �����͹迭������ �� �����ֵ��� �Ѵ�.
		//-> �Լ��� ���� ��ġ�Ͽ� �ڵ����� ������ �߻��� �� �ִ� ���� ���ɼ��� ���ҽ�Ŵ.
		$header_str = $this->ordered_implode(",", $this->header_fields, $header) ;
		$sql = "INSERT INTO $this->db_header_table($header_field_str) VALUES ($header_str)" ;
		if($this->debug) echo("Board::insert [$sql]<br>") ;
		//Ʈ����� ó���� �ʿ���.
		$this->insert($sql) ;

		$data_field_str = implode(",", $this->data_fields) ;
		$data_str = $this->ordered_implode(",", $this->data_fields, $data) ;
		$sql = "INSERT INTO $this->db_data_table($this->data_str) VALUES ($data_str)" ;
		$this->insert($sql) ; 

		//MySQL�� ��� ���⼭ ������ ���� ó���� �ȵǴ°�� header�� �����͸� �������ִ� ����� �ʿ�.

		$this->timer->end("Board::insert") ;
	}

	//�ϳ��� �����͸� ����? 
	//Ʈ����� ó���� �ʿ���.
	function update_data($header, $data, $condition, $board_name="")
	{
		if($this->debug) echo("Board::update_data()<br>") ;
		$this->timer->start("Board::update_data") ;

		$this->set_table_name($board_name) ;

		$sql = "UPDATE $this->db_header_table SET " ;
		$sql .= $this->sql_update($this->header_fields, $header) ;
		$sql .= $this->sql_where($condition) ;

		if($this->debug) echo("Board::update [$sql]<br>") ;
		$this->update($sql) ;

		//���� �κп���... ���� Ű�� �����´ٴ� �����Ͽ����� ������ condition�� ����� �� �ִ�.
		$sql = "UPDATE $this->db_data_table SET " ;
		$sql .= $this->sql_update($this->data_fields, $data) ;
		$sql .= $this->sql_where($condition) ;
		if($this->debug) echo("Board::update [$sql]<br>") ;
		$this->update($sql) ; 

		//MySQL�� ��� ���⼭ ������ ���� ó���� �ȵǴ°�� header�� �����͸� �������ִ� ����� �ʿ�.

		$this->timer->end("Board::update_data") ;
	}

	//�ϳ��� �����͸� ����
	//Ʈ����� ó���� �ʿ���.
	//�⺻�����δ� data���̺��� ���� �����ϰ� ��� ���� ���ŵǾ��� ��� header���̺��� �����Ѵ�.
	//÷�������� ���⼭ �����ϴ°�?
	function delete_data($condition, $board_name="")
	{
		if($this->debug) echo("Board::delete_data()<br>") ;
		$this->timer->start("Board::delete_data") ;
		$this->set_table_name($board_name) ;

		//�ش��ϴ� ��������, ������ ����
		//������ ó���ۿ� �ش��ϴ� ��� ÷�����ϵ� �Բ� �����ϵ��� �Ѵ�.
		//÷�����ϵ� �⺻ ������ Ȯ���Ϸ��� ������ select�� �ʿ���.
		//$this->select($sql) 

		$sql = "DELETE FROM $this->db_data_table " ;
		$sql .= $this->sql_where($condition) ;
		if($this->debug) echo("Board::delete [$sql]<br>") ;
		$this->delete($sql) ;
		
		//������ �����ִ��� Ȯ���� �غ����� ���� �Ǽ��� ���ٸ� �����ϵ����Ѵ�.
		$cnt = $this->count_data("data", $condition) ;
		//������ �����ִٸ� �����Ѵ�.
		//
		if($cnt)
		{
			$sql = "UPDATE $this->db_header_table" ; 
			//�����Ǵ� ����� ó�����̳� ����̳Ŀ� ���� �� ������ ���ҽ�Ű���� �Ѵ�.
		}
		else
		{
			$sql = "DELETE FROM $this->db_header_table " ;
			$sql .= $this->sql_where($condition) ;
			if($this->debug) echo("Board::delete [$sql]<br>") ;
			$this->delete($sql) ;
		}
		//���� �κп���... ���� Ű�� �����´ٴ� �����Ͽ����� ������ condition�� ����� �� �ִ�.

		//MySQL�� ��� ���⼭ ������ ���� ó���� �ȵǴ°�� header�� �����͸� �������ִ� ����� �ʿ�.

		$this->timer->end("Board::delete_data") ;
	}


	//�ٸ� ���� ���̺�� ����
	function copy_data($dest_board_name="", $condition, $board_name="")
	{
		//����
		// INSERT FROM �ٸ����̺�
	}

	//�ٸ� ���� ���̺� ���� �����ͷ� ���� �� ���� ���̺��� ����
	function move_data($dest_board_name="", $condition, $board_name="")
	{
		//����
		// INSERT FROM �ٸ����̺�
		//����
		//DELETE
	}

	//��� �޾ƿ���
	//���� ������� ���� �迭�� �����Ͽ� �ٰ��ΰ�?
	//�׷� ����� ���� ��Ų ó���� ��� �� ���ΰ�?
	function select_list($contidion, $offset = 0, $limit = 0, $sort_field = "", $sort_type = "ASC", $board_name="")
	{
		if($this->debug) echo("Board::select_list()<br>") ;
		$this->timer->start("Board::select_list") ;

		$this->set_table_name($board_name) ;

		//�⺻ ���� �����
		$sql = "SELECT * FROM $this->db_header_table " ;
	
		//WHEREó�� �ʿ�
		$sql .= $this->sql_where($condition) ;
		$sql .= $this->sql_limit($offset, $limit) ;
		$sql .= $this->sql_orderby($sort_field, $sort_type) ;

		if($this->debug) echo("Board::select_list() SQL[$sql]<br>") ;
		$this->select($sql) ;

		//�����͸� ����� ����
		while( ($this->list_rows[] = $this->fetch_row()) )
		{
		}

		//if($this->debug) print_r($this->list_rows) ;

		$this->timer->end("Board::select_list") ;
		//if($this->debug) $this->timer->report() ;

		$this->total_count = $this->count_total("header") ;
		if($this->debug) echo(__FUNCTION__."> [total_count:{$this->total_count}]<br>") ;
		return $this->list_rows ;
	}


	//���� �޾ƿ���
	function select_data($condition, $offset = 0, $limit = 0, $sort_field = "", $sort_type = "ASC", $board_name="")
	{
		if($this->debug) echo("Board::select_data()<br>") ;
		$this->timer->start("Board::select_data") ;

		$this->set_table_name($board_name) ;

		//�⺻ ���� �����
		$sql = "SELECT * FROM $this->db_data_table " ;
	
		//WHEREó�� �ʿ�
		$sql .= $this->sql_where($condition) ;
		$sql .= $this->sql_limit($offset, $limit) ;
		$sql .= $this->sql_orderby($sort_field, $sort_type) ;

		if($this->debug) echo("Board::select_data() SQL[$sql]<br>") ;
		$this->select($sql) ;

		while( ($this->data_rows[] = $this->fetch_row()) )
		{
		}

		//if($this->debug) print_r($this->data_rows) ;
		$this->timer->end("Board::select_data") ;
		//if($this->debug) $this->timer->report() ;

		return $this->data_rows ;
	}

	/**
	�ش��ϴ� ���̺� ���ǿ� �����ϴ� ������ �����ش�.
	*/
	function count_data($table_prefix, $condition, $field = "*",  $board_name="")
	{
		if($this->debug) echo("Board::count_data()<br>") ;
		$this->timer->start("Board::count_data") ;

		$this->set_table_name($board_name) ;

		$table = "wb_{$this->board_name}_{$table_prefix}" ;
		//�⺻ ���� �����
		$sql = "SELECT count(*) FROM $table " ;
		$sql .= $this->sql_where($condition) ;

		if($this->debug) echo("Board::count_data() SQL[$sql]<br>") ;
		$this->select($sql) ;

		$row = $this->fetch_row() ;
		$count = current($row) ;
		if($this->debug) 
		{
			echo("<pre>") ;
			//print_r($row) ;
			echo("</pre>") ;
		}
		$this->timer->end("Board::count_data") ;

		return $count ;
	}


	/**
	��ü �������� ���� ��������.
	��������, ������ ����� ���� �ʿ���.
	���ǿ����� ���Ե��� ����.
	*/
	function count_total($table_prefix, $field = "*",  $board_name="")
	{
		if($this->debug) echo("Board::count_total()<br>") ;
		$this->timer->start("Board::count_total") ;

		$this->set_table_name($board_name) ;

		$table = "wb_{$this->board_name}_{$table_prefix}" ;
		//�⺻ ���� �����
		$sql = "SELECT count(*) FROM $table " ;
		$sql .= $this->sql_where($condition) ;

		if($this->debug) echo("Board::count_data() SQL[$sql]<br>") ;
		$this->select($sql) ;

		$row = $this->fetch_row() ;
		$count = current($row) ;
		if($this->debug) 
		{
			echo("<pre>") ;
			//print_r($row) ;
			echo("</pre>") ;
		}
		$this->timer->end("Board::count_total") ;

		return $count ;
	}


	/**
	������� ����ϴ� ������ �迭�� ����
	2.x�뿡�� �Լ� ��ü�� �����͸� �־ �ڵ� �������� ����Ʈ�ȴ� �κ��� �غ�����.
	@author apollo@whitebbs.net
	@date 2005/01/21(Fri)
	@todo URL, catetory, page_count ó�� �ʿ�.
	*/
	function make_header_row()
	{
		$tot_page = get_total_page( $this->total_count, $conf[nCol] * $conf[nRow] ) ;
		if($this->debug) echo(__FUNCTION__."> [total:{$this->total_count}], TOT_PAGE:[$tot_page]<br>") ;
		// ������� ��ü �������� ������ ���� �������� ��ġ���� ������ ���� �������� ���ʷ� reset��Ų��.	
		$cur_page = ($cur_page < 0 )?0:$cur_page ;
		$cur_page = ($cur_page >= $tot_page )?0:$cur_page ;

		// offset calc
		$line_begin = $cur_page * ($conf[nCol] * $conf[nRow]) ;
		if($this->debug) echo(__FUNCTION__.">line_begin[$line_begin] cur_page[$cur_page]<br>") ;

		// category list 2001/12/09
		$URL['list'] = "$C_base[url]/board/$conf[list_php]?data=".$this->board_name ;
		$Row[category_list] = category_list($this->board_name, $URL['list']) ;

		//�Ӹ����� �� ������
		$Row[nTotal]   = $dbi->total ; 
		$Row[cur_page] = empty($cur_page)?1:$cur_page+1 ;
		$Row[tot_page] = $tot_page ;
		$Row[play_list] = $play_list ; //���� ���ð� ���


		$header_row[total_count] = $this->total_count ; //Skin2.x::$Row[nTotal] 
		//$header_row[cur_page] = $__GET


		return $header_row ;
	}

	/**
	select_list()���� ������ �� ����� ��Ų�� ���ؼ� ������ִ� ������ �����.
	����� ó���� ������ ������ ���鼭 ����Ѵ�. ��Ų�� ó���� Skin_Base�� ���� ó���Ѵ�.
	@todo 3.0�� �´� ���������� ���� 
	@author apollo@whitebbs.net
	@date 2005/01/21(Fri)
	@todo $C_base, $hide, $Row�� �ֿ� ó�� �������� ȣȯ�� ����.
	@todo page_baró��, �ڷᰳ�� ��� 
	*/
	function show_list()
	{
		$pos = 0 ;
		$cnt = 0 ;
		
		//3��° �Ķ���� globals�� �ݵ�� Ȭ����ǥ�� ����Ѵ�.
		$skin = new Skin_Base("wb_board", "list", '$conf, $C_base') ; 
		eval($skin->globals) ; //��Ų�� �������� ������ ���� ���. ���������� ���ϸ� �ݵ�� ���� ȣ���Ѵ�.
		//@warning global �����Ŀ� ������ �־��־�� ����� ����. 

		$conf = $this->conf ;
		$C_base = $this->C_base ;


		if($this->debug) echo("make_header_row<br>") ;
		$header_row = $this->make_header_row() ;

		//��ũ�����
		//$URL = make_url($_data, $Row, "board", $conf[list_php]) ;
		//$URL['list'] = "$C_base[url]/board/$conf[list_php]?data=$_data" ;
	
		if($this->debug) echo("header<br>") ;
		$skin->header($header_row) ;

		//count($this->list_rows) ;
		echo("$conf[BOX_START]") ;
		foreach($this->list_rows as $row)
		{

			//������ �ɷ�����
			if(empty($row[board_group]) && empty($row[board_id])) 
				continue ;

			echo("$conf[BOX_DATA_START]") ;

			//array_merge($row, $this->make_additional_row($row)) ;
			$row[no] = $this->total_count - $cnt ; //�ۿ� ���� ��ȣ ���



			//$hide = make_comment($row) ;
			$skin->show($row, $hide) ;

			if( ($pos % $conf[nCol]) == ($conf[nCol]-1) )
			{
				echo("$conf[BOX_BR]") ;
			}
			$pos++ ;
			$cnt++ ; //�ѹ����� �ϱ����� ����
		}
		echo("$conf[BOX_END]") ;

		$skin->footer() ;
	}
} ;



/**
2.x�� ���� ������ ���� ��������
*/
function set_compat($old_var_list = "")
{
	//eval($wb_default_globals) ;
	return "global $C_skin, ".$old_var_list. "; " ;
}

////////////////////////////////////////////////////////////////////////////////
//Main
//���߿� ���� ���Ϸ� �Ʒ� ������ �и��� �Ǿ����. �����ڵ�� �Ʒ� ����.
////////////////////////////////////////////////////////////////////////////////
	ob_start() ; // cookie, session ������ ���� ���ֱ� ���� ���.
	//�����ϴ� �� �κ��� ���������� ��붧���� �Լ��� ���� ����ϱⰡ �����.
	//�ٸ� ���� ����� �ִٸ�? �ֿ��ϰ� ���� ������ �Ķ���ͷ� �Ѱܼ� ����ϴ¾���?
	////////////////////////////////////////////////////////////////////////////////

	//�⺻���丮�� ���� �غ�.
	/**
	require_once("../lib/system_ini.php") ;
	require_once("../lib/get_base.php") ;
	$C_base = get_base(1) ; //������ �Ǵ� �ּ� �޾ƿ���, lib.php�� ����.

	$wb_charset = wb_charset($C_base[language]) ;
	
	//����, ����ó��.
	require_once("$C_base[dir]/auth/auth.php") ;
	if( $log == "on" ) $auth->login() ; 
	else if( $log == "off" ) $auth->logout() ; 

	//�⺻���̺귯�� include.
	require_once("$C_base[dir]/lib/wb.inc.php") ;

	//ȯ�漳�� �б�.
	//$conf = read_board_config("wb_".$board_name) ;
	*/
	////////////////////////////////////////////////////////////////////////////////


	echo("Board Class Test<br>") ;
	$board_name = "board" ;
	$board = new Board($board_name) ;

	//�⺻���̺귯�� include.
	$C_base = $board->C_base ;
	require_once("{$board->C_base[dir]}/lib/wb.inc.php") ;


	$now = time() ;
	$board_group = time() ;
	$board_id = time() ;
	$subject = "�ȳ��ϼ���".time() ; 
	$subject_color = "green" ;
	$type = 1 ;
	$date_update = $now ;
	$date_write = $now ;
	$name = "������".time() ;
	$uid = 100 ;

	$note = "�����Դϴ�. ���������ϰ� �� ������ ���� �ֽ��ϴ�".time() ;


	//header_data���� �̷��� ������شٰ� ��������.
	//�迭�� ������ ���ø����̼��� DB��Ű�������� �� ��ġ��Ű���� ����. ���α׷� �ڵ����� ���Ǹ� ���� ����.
	//�������� �ص� �־��� �� �ֵ��� ���� ordered_implode�߰�
	//@todo �Էµ� �����Ϳ� ���� ��ȯ ������ �ʿ��� ��� ������ ���ֵ��� �ؾ���.
	$header = array( 
		"board_group" => "'".$board_group."'", 
		"board_id" => "'".$board_id."'", 
		"uname" => "'".$name."'", 
		"uid" => "'".$uid."'",
		"subject" => "'".$subject."'", 
		"subject_color" => "'".$subject_color."'",
		"type" => "'".$type."'", 
		"date_update" => "'".$update_timestamp."'", 
		"date_write" => "'".$w_date."'", 
		"cnt_view" => "'".$cnt."'", 
		"cnt_reply" => "'".$cnt2."'", 
		"cnt_article" => "'".$cnt3."'",
		"cnt_down1" => "'".$cnt3."'", 
		"cnt_down2" => "'".$cnt4."'", 
		"mail_reply"    => "'".$mail_reply."'",
		) ;
	//make_save_header($header) ;
	$data = array(
		"board_group" => "'".$board_group."'",
		"board_id" =>"'".$board_id."'",
		"encode_type" =>"'".$encode_type."'",
		"uid" =>"'".$uid."'",
		"uname" =>"'".$name."'",
		"password" =>"'".$password."'",
		"email" =>"'".$email."'",
		"homepage" =>"'".$homepage."'",
		"date_write" =>"'".$date_write."'",
		"date_update" =>"'".$date_update."'",
		"attach_name" =>"'".$attach_name."'",
		"attach_size" =>"'".$attach_size."'",
		"attach_type" =>"'".$attach_type."'",
		"attach2_name" =>"'".$attach2_name."'",
		"attach2_size" =>"'".$attach2_size."'",
		"attach2_type" =>"'".$attach2_type."'",
		"bgimg" =>"'".$bgimg."'",
		"link" =>"'".$link."'",
		"remote_ip" =>"'".$remote_ip."'",
		"use_html" =>"'".$use_html."'",
		"use_br" =>"'".$use_br."'",
		"note" =>"'".$note."'"
		) ;

	//make_save_data($data) ;


	//�߰�
	$board->insert_data($header, $data) ;

	//���忡�� conf�� �����´�. var $conf �� ���� ����.
	//����� select_�ϰ�...
	//��Ų�� ���� ȣ���ϴ� ���� �ƴ϶�. $board->lists()�� ȣ��
	//$board->lists()���� ��Ų�� ���� ����ϵ�������.


	//���
	$board->select_list("", 0,0,"subject","DESC") ;
	//�⺻ ������ó�� && blocking && filterling
	//�÷����� ó��
	//$board->make_contents() ;


	//��Ų ó��
	$board->show_list() ;



	//cat �ùķ��̼�
	//
	//$board->select_data("", 0,0,"date_write","DESC") ;

/*
Edit �ùķ��̼� 
//����...
$now = time() ;
$board_group = time() ;
$board_id = time() ;
$subject = "����ٲ۴�".time() ; 
$subject_color = "green" ;
$type = 1 ;
$date_update = $now ;
$date_write = $now ;
$name = "ü���丶��".time() ;
$uid = 100 ;

$note = "�����Դϴ�. ���������ϰ� �� ������ ���� �ֽ��ϴ�".time() ;

//header_data���� �̷��� ������شٰ� ��������.
//�迭�� ������ ���ø����̼��� DB��Ű�������� �� ��ġ��Ű���� ����. ���α׷� �ڵ����� ���Ǹ� ���� ����.
//�������� �ص� �־��� �� �ֵ��� ���� ordered_implode�߰�
//@todo �Էµ� �����Ϳ� ���� ��ȯ ������ �ʿ��� ��� ������ ���ֵ��� �ؾ���.
$header = array( 
	"board_group" => "'".$board_group."'", 
	"board_id" => "'".$board_id."'", 
	"uname" => "'".$name."'", 
	"uid" => "'".$uid."'",
	"subject" => "'".$subject."'", 
	"subject_color" => "'".$subject_color."'",
	"type" => "'".$type."'", 
	"date_update" => "'".$update_timestamp."'", 
	"date_write" => "'".$w_date."'", 
	"cnt_view" => "'".$cnt."'", 
	"cnt_reply" => "'".$cnt2."'", 
	"cnt_article" => "'".$cnt3."'",
	"cnt_down1" => "'".$cnt3."'", 
	"cnt_down2" => "'".$cnt4."'", 
	"mail_reply"    => "'".$mail_reply."'",
	) ;
//make_save_header($header) ;
$data = array(
	"board_group" => "'".$board_group."'",
	"board_id" =>"'".$board_id."'",
	"encode_type" =>"'".$encode_type."'",
	"uid" =>"'".$uid."'",
	"uname" =>"'".$name."'",
	"password" =>"'".$password."'",
	"email" =>"'".$email."'",
	"homepage" =>"'".$homepage."'",
	"date_write" =>"'".$date_write."'",
	"date_update" =>"'".$date_update."'",
	"attach_name" =>"'".$attach_name."'",
	"attach_size" =>"'".$attach_size."'",
	"attach_type" =>"'".$attach_type."'",
	"attach2_name" =>"'".$attach2_name."'",
	"attach2_size" =>"'".$attach2_size."'",
	"attach2_type" =>"'".$attach2_type."'",
	"bgimg" =>"'".$bgimg."'",
	"link" =>"'".$link."'",
	"remote_ip" =>"'".$remote_ip."'",
	"use_html" =>"'".$use_html."'",
	"use_br" =>"'".$use_br."'",
	"note" =>"'".$note."'"
	) ;

//make_save_data($data) ;



//$condition = "uid = '$uid' " ;
//$board->update_data($header, $data, $condition) ;

//���
//$board->select_list("", 0,0,"subject","DESC") ;
//$board->select_data("", 0,0,"date_write","DESC") ;
//����..
//$board->delete_data($header) ;
*/

//���� ���� 
$condition = "board_id = 32767" ;
$cnt = $board->count_data("data", $condition) ;
echo ("Header num: $cnt<br>") ;


$board->close() ;

?>

