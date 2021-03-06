<?php
/*
WhiteBoard 2.1.0 2002/1/2
WhiteBoard 2.0.1 pre 2001/10
WhiteBoard 2.0.0 pre 2001/10
Copyright (c) 2001-2002, WhiteBBs.net, All rights reserved.

소스와 바이너리 형태의 재배포와 사용은 수정을 가하건 그렇지 않건 아래의 조건을 만족할 경우에 한해서만 허용합니다:

1. 소스코드를 재배포 할때는 위에 명시된 저작권 표시와 이 목록에 있는 조건와 아래의 성명서를 반드시 표시 해야만 합니다.

2. 실행할 수 있는 형태의 재배포에서는 위의 저작권 표시( 화이트보드팀과 그 공헌자의 이름)와 이 목록의 조건과 아래의 성명서의 내용이 그 문서나 같이 배포되는 다른 것들에도 포함 되어야 합니다.

3. 본 소프트웨어로부터 파생되어 나온 제품에 구체적인 사전 서면 승인 없이 화이트보드팀의 이름이나 그 공헌자의 이름으로 보증이나 제품판매를 촉진할 수 없습니다.  


저작권을 가진사람과 그 공헌자는 본 소프트웨어를 원본 그대로 제공할 뿐 어떤 표현이나 이유 등을 갖는 특별한 목적을 위한 매매보증이나 합목적성을 부인합니다. 저작권을 가진 사람이나 공헌자는 어떠한 직접적, 간접적, 부수적, 특정적, 전형적, 필연적인 손상, 즉 상품이나 서비스의 대체, 사용상의 손실, 데이터의 손실, 이득, 영업의 중단 등에 대해 그것이 어떠한 원인에 의한 것이나, 어떠한 책임론에 의하거나, 계약에 의하거나 절대적인 책임, 민사상 불법행위가 과실에 의하거나 그렇지 아니하거나 본 소프트웨어의 사용중에 발생한 손상에 대해서는 비록 그것이 이미 예고된 것이라 할지라도 책임이 없습니다.  

WhiteBoard 1.4.2 : 2001/08/15
WhiteBoard 1.4.0 pre : 2001/08/11
WhiteBoard 1.3.0 : 2001/06/17
WhiteBoard 1.2.3 : 2001/05/10
WhiteBoard 1.1.1 2001/4/11
*/
	//2002/03/18 권한검사 필요.
	ob_start() ;
	$data = eregi_replace("(\.\.|\/|`|'|;|#|~|@|\?|=|&|!)", "", $data) ;
	$board_group = eregi_replace("(\.\.|\/|`|'|;|#|~|@|\?|=|&|!|conf)", "", $board_group) ;
	if( substr_count($file, "/") > 3 ) 
	{
		$file = "" ;
	}
	$file = eregi_replace("(^\/|\.\.|`|'|;|#|~|@|\?|=|&|!|php)", "", $file) ;

	include_once("../lib/system_ini.php") ;
	include_once("../lib/get_base.php") ;
	$C_base = get_base(1) ; //기준이 되는 주소 받아오기, lib.php에 있음.

	include("../lib/wb.inc.php") ;
	include_once("${C_base[dir]}/lib/database.php") ;

	$C_data = $data ;
	if($no_image == 1)
	{
		$attach_file = "${file}" ;	
	}
	else
	{
		$attach_file = "${C_base[dir]}/board/data/$C_data/${file}" ;
	}


	if( !is_dir($attach_file) ) 
	{
		$file_size = filesize($attach_file) ;
		$fp_data = wb_fopen($attach_file, "r") ; 
		$buffer = fread($fp_data, $file_size);
		fclose($fp_data);
	}
	else
	{
		echo("잘못된 파일입니다.") ;
		exit ;
	}

		
		//2002/03/21 기존(v 1.x대) download count 데이터를 그대로 사용할 수 있게 하기 위해
	if($count_pos == 3)
	{
		$count_pos = 2 ;
	}
	else if($count_pos == 2)
	{
		$count_pos = 3 ;
	}

	$dbi = new db_interface($C_data, "member", $mode, $filter_type, $key, $field, $C_base[member_db_type], "2", $C_base[dir] ) ;

	$idx_data = array("board_group" => $board_group, "count_pos" => $count_pos ) ; 
	$idx_data = $dbi->update_index($C_data, $index_name, $idx_data, "count") ;	
	$dbi->destroy() ;

	//Header("Content-type: application/x-something"); 
	//Header("Content-type: application/octet-stream"); 
	//Header("Content-type: ${file_type}"); 
	if( eregi("image", $file_type ) )
	{
		Header("Content-type: ${file_type}"); 
	}
	else
	{
		Header("Content-type: Unknown"); 
	}
	//Header("Content-Transfer-Encoding: binary");
	//Header("Content-disposition: attachment; filename=${file_name}"); 
	//Header("Content-disposition: filename=${file_name}"); 
	Header("Content-length: $file_size") ;
	Header("Content-disposition: inline; filename=${file_name}"); 
	Header("Pragma: no-cache"); 
	Header("Expires: 0"); 
	print $buffer ;
?>
