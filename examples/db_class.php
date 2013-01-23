<?php
/**
@file
@brief Board Class
@author apollo@WhiteBBS.net
Copyright 2004, whiteBBS.net
@date 2004/08/24

사용할 보드 변수 정리 및 선언 
호환성 여부는 추후 기능완료후 고려해보도록 할것.
*/

/**
데이터베이스 타입에 맞추어 include하기
include("$db_class.inc.php") ;
Skin Class와 Timer Class적용 필요.
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
	var $data_field ; //이걸 data_field라고 할까?

	var $board_name ;		//보드명
	var $db_header_table ; //목록을 가지고 있는 테이블 명
	var $db_data_table ; //글 내용을 가지고 있는 테이블 명

	var $list_rows ;	//목록 부분의 한 페이지의 데이터들
	var $data_rows ;	//글 하나에 대한 데이터들
	var $total_count ; //전체 데이터의 개수

	//var $C_base ;	// 시스템 기본 환경
	var $conf ;

	function Board($board_name="")
	{
		$this->debug = 1 ;
		$this->timer = new Timer() ; //실행시간 체크를 위해서
		$this->list_rows = array() ;
		$this->data_rows = array() ;

		// DB스키마의 순서에 해당하는 부분을 해당 어플리케이션에 기록해두고 insert나 update등에 활용하도록 한다.
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

		$this->init_env() ; //시스템 기본환경 세팅
	}

	function init_env()
	{
		//시스템 기본 환경 읽어오기
		//기본디렉토리와 변수 준비.
		require_once("../lib/system_ini.php") ;
		require_once("../lib/get_base.php") ;
		$this->C_base = get_base(1) ; //기준이 되는 주소 받아오기, lib.php에 있음.

		//기본라이브러리 include.
		$C_base = $this->C_base ; // wb.inc.php에서 $C_base를 사용함.
		require_once("{$this->C_base[dir]}/lib/wb.inc.php") ;

		if($this->debug) echo("{$this->board_name}<br>") ;
		//환경설정 읽기.
		$this->conf = read_board_config($this->board_name, $this->C_base) ;
		return ;
	}

	function auth()
	{
		//$C_base = $this->C_base ;

		//인증, 권한처리.
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

	//DB에 하나의 글을 저장
	//여기에는 처음글과 답글을 구분하여 저장하는 기능이 있어야 함.
	//트랜잭션 처리가 필요함.
	function insert_data($header, $data, $board_name="")
	{
		if($this->debug) echo("Board::insert_data()<br>") ;
		$this->timer->start("Board::insert") ;

		$this->set_table_name($board_name) ;

		$header_field_str = implode(",", $this->header_fields) ;
		//xSQL에서 field값의 순서와 데이터 내용의 순서가 반드시 일치해야 하므로 insert할 데이터배열순서를 꼭 맞춰주도록 한다.
		//-> 함수로 구현 대치하여 코딩상의 오류로 발생할 수 있는 오류 가능성을 감소시킴.
		$header_str = $this->ordered_implode(",", $this->header_fields, $header) ;
		$sql = "INSERT INTO $this->db_header_table($header_field_str) VALUES ($header_str)" ;
		if($this->debug) echo("Board::insert [$sql]<br>") ;
		//트랜잭션 처리가 필요함.
		$this->insert($sql) ;

		$data_field_str = implode(",", $this->data_fields) ;
		$data_str = $this->ordered_implode(",", $this->data_fields, $data) ;
		$sql = "INSERT INTO $this->db_data_table($this->data_str) VALUES ($data_str)" ;
		$this->insert($sql) ; 

		//MySQL의 경우 여기서 오류가 나서 처리가 안되는경우 header의 데이터를 삭제해주는 기능이 필요.

		$this->timer->end("Board::insert") ;
	}

	//하나의 데이터를 갱신? 
	//트랜잭션 처리가 필요함.
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

		//조건 부분에서... 같은 키로 가져온다는 조건하에서만 동일한 condition을 사용할 수 있다.
		$sql = "UPDATE $this->db_data_table SET " ;
		$sql .= $this->sql_update($this->data_fields, $data) ;
		$sql .= $this->sql_where($condition) ;
		if($this->debug) echo("Board::update [$sql]<br>") ;
		$this->update($sql) ; 

		//MySQL의 경우 여기서 오류가 나서 처리가 안되는경우 header의 데이터를 삭제해주는 기능이 필요.

		$this->timer->end("Board::update_data") ;
	}

	//하나의 데이터를 삭제
	//트랜잭션 처리가 필요함.
	//기본적으로는 data테이블에서 글을 삭제하고 모든 글이 제거되었을 경우 header테이블에서 제거한다.
	//첨부파일은 여기서 제거하는가?
	function delete_data($condition, $board_name="")
	{
		if($this->debug) echo("Board::delete_data()<br>") ;
		$this->timer->start("Board::delete_data") ;
		$this->set_table_name($board_name) ;

		//해당하는 본문내용, 데이터 삭제
		//본문이 처음글에 해당하는 경우 첨부파일도 함께 삭제하도록 한다.
		//첨부파일등 기본 정보를 확인하려면 데이터 select가 필요함.
		//$this->select($sql) 

		$sql = "DELETE FROM $this->db_data_table " ;
		$sql .= $this->sql_where($condition) ;
		if($this->debug) echo("Board::delete [$sql]<br>") ;
		$this->delete($sql) ;
		
		//내용이 남아있는지 확인을 해본다음 내용 건수가 없다면 제거하도록한다.
		$cnt = $this->count_data("data", $condition) ;
		//개수가 남아있다면 갱신한다.
		//
		if($cnt)
		{
			$sql = "UPDATE $this->db_header_table" ; 
			//삭제되는 대상이 처음글이냐 답글이냐에 따라서 글 개수를 감소시키도록 한다.
		}
		else
		{
			$sql = "DELETE FROM $this->db_header_table " ;
			$sql .= $this->sql_where($condition) ;
			if($this->debug) echo("Board::delete [$sql]<br>") ;
			$this->delete($sql) ;
		}
		//조건 부분에서... 같은 키로 가져온다는 조건하에서만 동일한 condition을 사용할 수 있다.

		//MySQL의 경우 여기서 오류가 나서 처리가 안되는경우 header의 데이터를 삭제해주는 기능이 필요.

		$this->timer->end("Board::delete_data") ;
	}


	//다른 보드 테이블로 복사
	function copy_data($dest_board_name="", $condition, $board_name="")
	{
		//삽입
		// INSERT FROM 다른테이블
	}

	//다른 보드 테이블에 기존 데이터로 삽입 후 현재 테이블에서 제거
	function move_data($dest_board_name="", $condition, $board_name="")
	{
		//삽입
		// INSERT FROM 다른테이블
		//제거
		//DELETE
	}

	//목록 받아오기
	//받은 결과물을 따로 배열에 저장하여 줄것인가?
	//그럴 경우라면 따로 스킨 처리는 어떻게 할 것인가?
	function select_list($contidion, $offset = 0, $limit = 0, $sort_field = "", $sort_type = "ASC", $board_name="")
	{
		if($this->debug) echo("Board::select_list()<br>") ;
		$this->timer->start("Board::select_list") ;

		$this->set_table_name($board_name) ;

		//기본 문장 만들기
		$sql = "SELECT * FROM $this->db_header_table " ;
	
		//WHERE처리 필요
		$sql .= $this->sql_where($condition) ;
		$sql .= $this->sql_limit($offset, $limit) ;
		$sql .= $this->sql_orderby($sort_field, $sort_type) ;

		if($this->debug) echo("Board::select_list() SQL[$sql]<br>") ;
		$this->select($sql) ;

		//데이터를 멤버에 복사
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


	//내용 받아오기
	function select_data($condition, $offset = 0, $limit = 0, $sort_field = "", $sort_type = "ASC", $board_name="")
	{
		if($this->debug) echo("Board::select_data()<br>") ;
		$this->timer->start("Board::select_data") ;

		$this->set_table_name($board_name) ;

		//기본 문장 만들기
		$sql = "SELECT * FROM $this->db_data_table " ;
	
		//WHERE처리 필요
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
	해당하는 테이블에 조건에 만족하는 개수를 세어준다.
	*/
	function count_data($table_prefix, $condition, $field = "*",  $board_name="")
	{
		if($this->debug) echo("Board::count_data()<br>") ;
		$this->timer->start("Board::count_data") ;

		$this->set_table_name($board_name) ;

		$table = "wb_{$this->board_name}_{$table_prefix}" ;
		//기본 문장 만들기
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
	전체 데이터의 개수 가져오기.
	페이지바, 페이지 계산을 위해 필요함.
	조건연산이 포함되지 않음.
	*/
	function count_total($table_prefix, $field = "*",  $board_name="")
	{
		if($this->debug) echo("Board::count_total()<br>") ;
		$this->timer->start("Board::count_total") ;

		$this->set_table_name($board_name) ;

		$table = "wb_{$this->board_name}_{$table_prefix}" ;
		//기본 문장 만들기
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
	헤더에서 사용하는 데이터 배열을 생성
	2.x대에서 함수 본체에 데이터를 넣어서 코드 가독성을 떨어트렸던 부분을 극복하자.
	@author apollo@whitebbs.net
	@date 2005/01/21(Fri)
	@todo URL, catetory, page_count 처리 필요.
	*/
	function make_header_row()
	{
		$tot_page = get_total_page( $this->total_count, $conf[nCol] * $conf[nRow] ) ;
		if($this->debug) echo(__FUNCTION__."> [total:{$this->total_count}], TOT_PAGE:[$tot_page]<br>") ;
		// 가지고온 전체 데이터의 개수와 현재 페이지가 일치하지 않으면 현재 페이지를 최초로 reset시킨다.	
		$cur_page = ($cur_page < 0 )?0:$cur_page ;
		$cur_page = ($cur_page >= $tot_page )?0:$cur_page ;

		// offset calc
		$line_begin = $cur_page * ($conf[nCol] * $conf[nRow]) ;
		if($this->debug) echo(__FUNCTION__.">line_begin[$line_begin] cur_page[$cur_page]<br>") ;

		// category list 2001/12/09
		$URL['list'] = "$C_base[url]/board/$conf[list_php]?data=".$this->board_name ;
		$Row[category_list] = category_list($this->board_name, $URL['list']) ;

		//머리말에 들어갈 변수들
		$Row[nTotal]   = $dbi->total ; 
		$Row[cur_page] = empty($cur_page)?1:$cur_page+1 ;
		$Row[tot_page] = $tot_page ;
		$Row[play_list] = $play_list ; //음악 선택곡 목록


		$header_row[total_count] = $this->total_count ; //Skin2.x::$Row[nTotal] 
		//$header_row[cur_page] = $__GET


		return $header_row ;
	}

	/**
	select_list()에서 가지고 온 결과를 스킨을 통해서 출력해주는 역할을 담당함.
	헤더의 처리와 내용을 루프를 돌면서 출력한다. 스킨의 처리는 Skin_Base를 통해 처리한다.
	@todo 3.0에 맞는 변수결정과 정리 
	@author apollo@whitebbs.net
	@date 2005/01/21(Fri)
	@todo $C_base, $hide, $Row등 주요 처리 변수들의 호환성 유지.
	@todo page_bar처리, 자료개수 출력 
	*/
	function show_list()
	{
		$pos = 0 ;
		$cnt = 0 ;
		
		//3번째 파라메터 globals는 반드시 홑따옴표를 사용한다.
		$skin = new Skin_Base("wb_board", "list", '$conf, $C_base') ; 
		eval($skin->globals) ; //스킨에 전역변수 적용을 위해 사용. 전역적용을 원하면 반드시 먼저 호출한다.
		//@warning global 선언후에 변수에 넣어주어야 제대로 들어간다. 

		$conf = $this->conf ;
		$C_base = $this->C_base ;


		if($this->debug) echo("make_header_row<br>") ;
		$header_row = $this->make_header_row() ;

		//링크만들기
		//$URL = make_url($_data, $Row, "board", $conf[list_php]) ;
		//$URL['list'] = "$C_base[url]/board/$conf[list_php]?data=$_data" ;
	
		if($this->debug) echo("header<br>") ;
		$skin->header($header_row) ;

		//count($this->list_rows) ;
		echo("$conf[BOX_START]") ;
		foreach($this->list_rows as $row)
		{

			//빈데이터 걸러내기
			if(empty($row[board_group]) && empty($row[board_id])) 
				continue ;

			echo("$conf[BOX_DATA_START]") ;

			//array_merge($row, $this->make_additional_row($row)) ;
			$row[no] = $this->total_count - $cnt ; //글에 대한 번호 계산



			//$hide = make_comment($row) ;
			$skin->show($row, $hide) ;

			if( ($pos % $conf[nCol]) == ($conf[nCol]-1) )
			{
				echo("$conf[BOX_BR]") ;
			}
			$pos++ ;
			$cnt++ ; //넘버링을 하기위한 변수
		}
		echo("$conf[BOX_END]") ;

		$skin->footer() ;
	}
} ;



/**
2.x대 변수 지원을 위한 전역설정
*/
function set_compat($old_var_list = "")
{
	//eval($wb_default_globals) ;
	return "global $C_skin, ".$old_var_list. "; " ;
}

////////////////////////////////////////////////////////////////////////////////
//Main
//나중에 별도 파일로 아래 내용은 분리가 되어야함. 연습코드라서 아래 있음.
////////////////////////////////////////////////////////////////////////////////
	ob_start() ; // cookie, session 설정시 오류 없애기 위해 사용.
	//시작하는 이 부분은 전역변수의 사용때문에 함수로 만들어서 사용하기가 어려움.
	//다른 좋은 방법이 있다면? 주요하게 쓰는 변수는 파라메터로 넘겨서 사용하는안은?
	////////////////////////////////////////////////////////////////////////////////

	//기본디렉토리와 변수 준비.
	/**
	require_once("../lib/system_ini.php") ;
	require_once("../lib/get_base.php") ;
	$C_base = get_base(1) ; //기준이 되는 주소 받아오기, lib.php에 있음.

	$wb_charset = wb_charset($C_base[language]) ;
	
	//인증, 권한처리.
	require_once("$C_base[dir]/auth/auth.php") ;
	if( $log == "on" ) $auth->login() ; 
	else if( $log == "off" ) $auth->logout() ; 

	//기본라이브러리 include.
	require_once("$C_base[dir]/lib/wb.inc.php") ;

	//환경설정 읽기.
	//$conf = read_board_config("wb_".$board_name) ;
	*/
	////////////////////////////////////////////////////////////////////////////////


	echo("Board Class Test<br>") ;
	$board_name = "board" ;
	$board = new Board($board_name) ;

	//기본라이브러리 include.
	$C_base = $board->C_base ;
	require_once("{$board->C_base[dir]}/lib/wb.inc.php") ;


	$now = time() ;
	$board_group = time() ;
	$board_id = time() ;
	$subject = "안녕하세요".time() ; 
	$subject_color = "green" ;
	$type = 1 ;
	$date_update = $now ;
	$date_write = $now ;
	$name = "아폴로".time() ;
	$uid = 100 ;

	$note = "내용입니다. 무지막지하게 긴 내용일 수도 있습니다".time() ;


	//header_data에서 이렇게 만들어준다고 가정하자.
	//배열의 순서는 어플리케이션의 DB스키마순서와 꼭 일치시키도록 하자. 프로그램 코딩상의 편의를 위한 것임.
	//무순서로 해도 넣어줄 수 있도록 변경 ordered_implode추가
	//@todo 입력될 데이터에 대한 변환 과정이 필요한 경우 이전에 해주도록 해야함.
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


	//추가
	$board->insert_data($header, $data) ;

	//보드에서 conf를 가져온다. var $conf 로 정의 하자.
	//목록을 select_하고...
	//스킨을 따로 호출하는 것이 아니라. $board->lists()를 호출
	//$board->lists()에서 스킨을 생성 사용하도록하자.


	//목록
	$board->select_list("", 0,0,"subject","DESC") ;
	//기본 데이터처리 && blocking && filterling
	//플러그인 처리
	//$board->make_contents() ;


	//스킨 처리
	$board->show_list() ;



	//cat 시뮬레이션
	//
	//$board->select_data("", 0,0,"date_write","DESC") ;

/*
Edit 시뮬레이션 
//갱신...
$now = time() ;
$board_group = time() ;
$board_id = time() ;
$subject = "제목바꾼당".time() ; 
$subject_color = "green" ;
$type = 1 ;
$date_update = $now ;
$date_write = $now ;
$name = "체리토마토".time() ;
$uid = 100 ;

$note = "내용입니다. 무지막지하게 긴 내용일 수도 있습니다".time() ;

//header_data에서 이렇게 만들어준다고 가정하자.
//배열의 순서는 어플리케이션의 DB스키마순서와 꼭 일치시키도록 하자. 프로그램 코딩상의 편의를 위한 것임.
//무순서로 해도 넣어줄 수 있도록 변경 ordered_implode추가
//@todo 입력될 데이터에 대한 변환 과정이 필요한 경우 이전에 해주도록 해야함.
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

//목록
//$board->select_list("", 0,0,"subject","DESC") ;
//$board->select_data("", 0,0,"date_write","DESC") ;
//삭제..
//$board->delete_data($header) ;
*/

//개수 세기 
$condition = "board_id = 32767" ;
$cnt = $board->count_data("data", $condition) ;
echo ("Header num: $cnt<br>") ;


$board->close() ;

?>

