<?php

/*
	WhiteBoard 게시물 관리 프로그램 manage.php 
	
	2004.2.8	  삭제할 게시물의 제목 표시, 다른 게시판으로 복사
	2004.2.7  삭제 기능 완성
	2004.2.5  일단한번 시작
*/

	function idx_update($mode, $C_base, $data, $nTotal, $no, $idx_content_update)
	{
		$tmp_idx_file = "$C_base[dir]/board/data/$data/".md5(uniqid("")); 			
		$idx_fd = wb_fopen( $tmp_idx_file, "w" ) ;
		fwrite($idx_fd, "$idx_content_update" ) ;
		fclose($idx_fd) ;
		
		$write_cnt = $nTotal + $no;		

		$idx_file = "$C_base[dir]/board/data/$data/data.idx.php" ;
		$idx_backup = "$C_base[dir]/board/data/$data/data.backup.php" ;

		if( filesize($tmp_idx_file) > 0 )
		{
			wb_lock($idx_file) ;
			if($write_cnt > 0 )
			{
				if( filesize($idx_file) > 0 )
				{
					@unlink($idx_backup) ;
					rename($idx_file, $idx_backup) ;
					chmod($idx_backup, 0666) ;
				}
			}		
			if(@file_exists($idx_file))
			{			
				@unlink($idx_file) ;
			}
			rename("$tmp_idx_file", $idx_file) ;
			chmod($idx_file, 0666) ;
			wb_unlock($idx_file) ;
		}
		//echo "$C_base[dir]/board/data/$data/total.cnt<br>";

		$fp = wb_fopen("$C_base[dir]/board/data/$data/total.cnt", "w") ;
		fwrite($fp, $write_cnt ) ;
		fclose($fp) ;

	}

	function copy_data($C_base,$select_group, $_data, $target_data)
	{
		$_debug =0;
		$board_group = explode(",",$select_group);
		$board_group_no = sizeof($board_group);
		
		umask(0000) ;				
		$idx_file = "$C_base[dir]/board/data/$_data/data.idx.php" ;
		
		$idx_content = file($idx_file) ;
		$idx_file_no = sizeof($idx_content);
		
		$cnt_file = file("$C_base[dir]/board/data/$target_data/total.cnt") ;
		$nTotal = $cnt_file[0] ;

		if($_debug) echo "idx_file :  [$idx_file] <br>";		

		for($i = 0 ; $i < $board_group_no ; $i ++)
		{
			
			$flist = new file_list("$C_base[dir]/board/data/$_data/", 1) ;	
			if($_debug) echo "board_group : [$board_group]<br>";
			$flist->read($board_group[$i]) ;
			/*
			while( ($file_name = $flist->next()) )
			{
				copy("data/$_data/$file_name","data/$target_data/$file_name");
			}
			*/
			//board_group 을  바꿔서 저장해야 하는가?
			$new_board_group[$i] = uniqid("D") ;	//새로운 board_gorup 을 생성해서 
			while( ($file_name = $flist->next()) )
			{	
				$new_file_name = str_replace($board_group[$i], $new_board_group[$i], $file_name);
				if($_debug) echo "[$file_name] --> [$new_file_name]";
				copy("data/$_data/$file_name","data/$target_data/$new_file_name");
			} // end of while 
			
		}

		$nTotal_copy = 0;
		$idx_content_copy ="";
		for ($i =0 ; $i < $idx_file_no ; $i++) // 첫줄과 마지막 줄은 생략
		{						
				for($j = 0 ; $j < $board_group_no ; $j ++)
				{
					$pos = strpos ($idx_content[$i],"$board_group[$j]"); // board_group이 포함된 줄을 찾아서
					if ($pos === false)
					{			
					}
					else
					{	
						//move 시에 본문글의 board_group과 board_id 를 구해야 한다. board_group 을 바꾸기 전에 미리 구함
						$tmp = explode("|", $idx_content[$i]);
						$main_article[] = $tmp[0].$tmp[1];  // main 글의 filename

						if($_debug) echo "***$tmp[0]$tmp[1]<br>";

						//새로운 board_group 을 사용한다면
						$idx_content[$i] = str_replace($board_group[$j], $new_board_group[$j], $idx_content[$i]); 
						if($_debug) echo ( " #$idx_content[$i]<br>");		
						
						$target_main_article[] =$new_board_group[$j].$tmp[1];  // 이동된 게시판에서의 main 글의 filename
					
						$idx_content_copy = $idx_content_copy.$idx_content[$i];
						$nTotal_copy++;
						break;
					}		
				}//end of for $j			
		}		

		$target_idx_file =  "$C_base[dir]/board/data/$target_data/data.idx.php" ;
		
		$target_idx_content = file($target_idx_file) ;
		$target_idx_no = sizeof($target_idx_content);
		$target_idx_org ="";
		for($i =0 ; $i < $target_idx_no; $i++)
		{
			if( eregi("<\?php", $target_idx_content[$i]) || eregi("\?>", $target_idx_content[$i]) )
			{
				continue;
			}
			else
			{				
				$target_idx_org = $target_idx_org.$target_idx_content[$i];
			}
		}

		if($_debug) echo "idx_content_copy<br>$idx_content_copy<br>";
		if($_debug) echo "target_idx_org<br>$target_idx_org<br>";

		$idx_content_update  ="<?php /*2.4|\n". $idx_content_copy.$target_idx_org."\?>";	
		if($_debug) echo "idx_content_update<br>$idx_content_update<br>";

		return array($idx_content_update, $main_article, $target_main_article, $nTotal, $nTotal_copy);
 
	}

	function delete_data($C_base,$board_group, $board_group_no, $_data)
	{		
		umask(0000) ;				
		$idx_file = "$C_base[dir]/board/data/$_data/data.idx.php" ;
		$idx_content = file($idx_file) ;		
		$idx_file_no = sizeof($idx_content);

		$cnt_file = file("$C_base[dir]/board/data/$_data/total.cnt") ;
		$nTotal = $cnt_file[0] ;

		if($_debug) echo "idx_file_no :  [$idx_file_no] <br> total : [$nTotal]<br>";

		$nTotal_delete = 0;
		for ($i =0 ; $i < $idx_file_no ; $i++)
		{				
			for($j = 0 ; $j < $board_group_no ; $j ++)
			{			
				$pos = strpos ($idx_content[$i],"$board_group[$j]");
				if ($pos === false)
				{			
				}
				else
				{				
					$idx_content[$i] ="";
					$nTotal_delete--;
					break;
				}		
			}		
		}
		
		$idx_content_update = implode("",$idx_content);

		return array($idx_content_update, $nTotal, $nTotal_delete);

	}

	///////////////////////////
	// 권한검사와 기본환경 읽기 
	// 순서를 지켜주어야 함. 
	// 2002/03/15
	///////////////////////////
	require_once("../lib/system_ini.php") ;
	require_once("../lib/get_base.php") ;
	$C_base = get_base(1) ; //기준이 되는 주소 받아오기, lib.php에 있음.
	require_once("$C_base[dir]/lib/wb.inc.php") ;
	require_once("$C_base[dir]/auth/auth.php") ; //권한,인증모듈 선언및 초기화 실행
	if( $log == "on" ) $auth->login() ; 
	else if( $log == "off" ) $auth->logout() ; 
	///////////////////////////
	umask(0000) ;//웹서버의 기본 umask를 지워준다.
	///////////////////////////
	
	//unset() x-y.net php에서 이상한 오류로 변수 초기화로 변경 2004/08/24 
	$conf[auth_perm] = "" ;
	$conf[auth_cat_perm] = "" ;
	$conf[auth_reply_perm] = "" ;
	$conf[auth_user] = "" ;
	$conf[auth_group] = "" ;
	
	$_debug = 0 ;	

	//메인 글을 지우는 경우에는 답글도 모두 삭제한다.

		//필터링
	$data = eregi_replace("(\.\.|\/|`|'|;|#|~|@|\?|=|&|!)", "", $data) ;
	$board_group = eregi_replace("(\.\.|\/|`|'|;|#|~|@|\?|=|&|!)", "", $board_group) ;
	$board_id = eregi_replace("(\.\.|\/|`|'|;|#|~|@|\?|=|&|!)", "", $board_id) ;
	if( empty($data) )
	{
		err_abort("data %s", _L_INVALID_LINK) ;
	}	
	else
	{
		$_data = $data ;
	}
	$conf = read_board_config($_data) ;
	//C_변수 이전 버젼 호환성 유지
	$C_skin = $conf[skin] ;
	//2002/03/18 기본 권한값지정
	if( !isset($conf[auth_perm]) )
	{
		if($conf[write_admin_only] == 1)
		{
			$conf[auth_perm] = "7555" ; //기본 권한 지정
			$conf[auth_cat_perm] = "7555" ;
			$conf[auth_reply_perm] = "7555" ;
		}
		else
		{
			$conf[auth_perm] = "7667" ; //기본 권한 지정
			$conf[auth_cat_perm] = "7667" ;
			$conf[auth_reply_perm] = "7667" ;
		}

		$conf[auth_user] = "root" ; //기본 관리자 아이디 
		$conf[auth_group] = "wheel" ; //기본 관리자 그룹
	}

if($_debug) echo "param : [$check[1]]<br>";
if($_debug) echo "param : [$param]<br>";

// $check[] board_group|subject

//$dbi = new db_board($_data, "index", $mode, $filter_type, $key, $field, "file", "2", $C_base[dir] ) ;

 if($mode == "select")
 {
	if($_debug) echo "mode : [$mode]<br>";
	$idx_no = sizeof($check);
	$no =0;
	for($i = 0 ; $i < $idx_no ; $i++ )
	 {
		$idx_tmp[$i] =explode("|", $check[$i]);
		// $idx_tmp[0][0]  , $idx_tmp[0][0]
		if(!($idx_tmp[$i][0] ==""))
		 {
			$board_group[] = $idx_tmp[$i][0];
			$subject[] = $idx_tmp[$i][1];			
			$no++;
		 }			
	 }
	$select= array_reverse($subject);
	$select_subject =implode("<br>",$subject);	
	$select_group = implode(",",$board_group);
	$message = "$select_subject"; 

	$_debug =0;
	$Row[func] = "" ;
	$Row[func] .= "<select name=target_data class='wForm'>\n" ;
	$flist = new file_list("$C_base[dir]/board/data", 1) ;
	$flist->read("*", 0) ;
	while( ($file_name = $flist->next()) )
	{
		$selected = "" ;
		if ($_debug) echo("file_name[$file_name]<br>") ;
		if( $file_name == "$data"||$file_name == "." || $file_name == ".." || $file_name == "CVS" || 
			$file_name == "__global" || eregi("deleted", $file_name)||eregi("done",$file_name))
		{
			if ($_debug) echo("SKIP file_name[$file_name]<br>") ;
			continue ;
		}

		if($C_skin == $file_name)
		{
			$selected = "selected" ;
		}
		$Row[func] .= "<option value='$file_name' $selected>$file_name</option>\n" ;
	}
	$Row[func] .= "</select>\n" ;	

 }
else if($mode == "delete")
{
	if($_debug) echo "data : [$data]<br>  mode : [$mode]<br>  select_group  : [$select_group]<br> no : [$no]<br>" ;

	$board_group = explode(",",$select_group);	
	$board_group_no = sizeof($board_group);

	list($idx_content_update, $nTotal, $nTotal_delete) = delete_data($C_base,$board_group, $board_group_no, $_data);
	
	idx_update($mode, $C_base, $_data, $nTotal, $nTotal_delete, $idx_content_update);	

	for($i = 0 ; $i < $board_group_no ; $i ++)
	{
		$flist = new file_list("$C_base[dir]/board/data/$_data/", 1) ;
		$flist->read($board_group[$i]) ;		
		while( ($file_name = $flist->next()) )
		{							
			unlink("data/$_data/$file_name") ;
		}
	}

	make_news($_data, $Row)  ;
	//echo"<script> opener.window.history.go(0); window.close(); </script>";
}

else if($mode =="copy")
{
	if($_debug) echo "mode : [$mode]<br>";	
	if($_debug) echo "data : [$_data]<br>  mode : [$mode]<br>  select_group  : [$select_group]<br> no : [$no]<br>target : [$target_data]<br>" ;

	$_debug =1;

	list($idx_content_update, $main_article, $target_main_article, $nTotal, $nTotal_copy) = copy_data($C_base,$select_group, $_data, $target_data);		

	idx_update($mode, $C_base, $target_data, $nTotal, $nTotal_copy, $idx_content_update);		
	make_news($target_data, $Row)  ;
	//echo"<script> window.close(); </script>";
}

else if($mode == "move")
{
	if($_debug) echo "mode : [$mode]<br>";	
	if($_debug) echo "data : [$data]<br>  mode : [$mode]<br>  select_group  : [$select_group]<br> no : [$no]<br>target : [$target_data]<br>" ;
	if($_debug) echo "opt1 : [$opt1]<br>opt2 : [$opt2]<br>";	

	list($idx_content_update,  $main_article, $target_main_article,  $nTotal, $nTotal_copy) = copy_data($C_base,$select_group, $_data, $target_data);	
	
	idx_update($mode, $C_base, $target_data, $nTotal, $nTotal_copy, $idx_content_update);	
	make_news($target_data, $Row)  ;

	// 이동된 글에 이동된 글임을 표시 : $opt1 =="1"
	if ($opt1 =="1")
	{
		for($i=0; $i < $nTotal_copy; $i++)
		{
			$fp_body = wb_fopen("$C_base[dir]/board/data/$target_data/$target_main_article[$i]", "a") ; 
			fwrite($fp_body,"\n\n <b>$data</b> 게시판에서 <b>[$W_SES[alias]]</b>에 의해 이동된 게시물입니다.");	
		}
	}			

	//원래의 게시판에서의 글 삭제나 변경
	if($opt2 == "1") // 원래의 게시판에 글에 내용을 지우고 이동되었음을 표시
	{	
		for($i=0; $i < $nTotal_copy; $i++)
		{	
			$data_file = $main_article[$i];
			$fp_body = wb_fopen("$C_base[dir]/board/data/$_data/$data_file", "r") ; 
			
			$i = 0 ;			
			$comment = "" ;
			while( !feof($fp_body) )
			{
				$line = fgets($fp_body, 8192) ;
				if( $i == 0 )
				{
					$line = chop($line) ;
					$head = explode("|", $line) ;	
				}
				else
				{	
					$line = stripslashes( $line ) ;				
					$comment = $comment.$line ; 
				}
				$i++ ;
			}			
			fclose($fp_body) ;
			
			$head[1] = $name ; 
			$head[6] = "" ;
			$head[7] =  "" ;
			$head[8] =  "" ;
			$head[9] =  "" ;
			$head[10] =  "" ;
			$head[11] =  "" ;
			$head[12] =  "" ;
			$head[13] = $remote_ip ;
			$head[14] = $encode_type ;
			$head[15] = $timestamp ;
			$head[16] = $uid ;
			$head[17] =  "" ;
			$head[18] =  "" ;
			$head[19] = $br_use ;
			$opt['html_use'] = $html_use ;

			$comment = "이동되었습니다." ; 

			$cont_head = implode("|", $head) ; 
			$tmp_file = "$C_base[dir]/board/data/$_data/".md5(uniqid("")); 
			$fp = @fopen($tmp_file, "w") ;
			if( !$fp )
			{
				err_abort("[$C_base[dir]/board/data/$_data/$tmp_file]%s", _L_NOWRITE_PERM) ;
			}
			fwrite($fp, "$cont_head\n$comment") ;
			fclose($fp) ;

			if( @file_exists("$C_base[dir]/board/data/$_data/$data_file") )
			{
				unlink("$C_base[dir]/board/data/$_data/$data_file") ;
			}
			rename("$tmp_file", "$C_base[dir]/board/data/$_data/$data_file") ;			

		}
		
	}
	else	//원래 게시판의 글을 삭제하는 경우 
	{

		$board_group = explode(",",$select_group);	
		$board_group_no = sizeof($board_group);

		list($idx_content_update, $nTotal, $nTotal_delete) = delete_data($C_base,$board_group, $board_group_no, $_data);
		
		idx_update($mode, $C_base, $_data, $nTotal, $nTotal_delete, $idx_content_update);	

		for($i = 0 ; $i < $board_group_no ; $i ++)
		{
			$flist = new file_list("$C_base[dir]/board/data/$_data/", 1) ;
			$flist->read($board_group[$i]) ;		
			while( ($file_name = $flist->next()) )
			{							
				unlink("data/$_data/$file_name") ;
			}
		}
		make_news($_data, $Row)  ;		
	}	

}

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title> WhiteBoard 게시물 관리 </title>


<script>
function change_board_name()
{
 select.board_name.value=select.select_board_name.value;
}

function del_selected()
{
 var check;
 manage.mode.value="delete";
 check=confirm("삭제하시겠습니까?\n\다시 복구하실 수 없습니다.");
 if(check==true) {document.manage.submit();} 
}

function move_selected()
{
 var check;
 manage.mode.value="move";
 check=confirm("게시물을이동 하시겠습니까?");
 if(check==true) {document.manage.submit();} 
}

function copy_selected()
{
 var check;
 manage.mode.value="copy";
 check=confirm("게시물을 복사하시겠습니까?");
 if(check==true) {document.manage.submit();} 
}
</script>
<STYLE>

.wDefault {font-family:굴림; font-size:9pt; line-height:15pt; color:#606060}
.wVer7 {  font-family: "Verdana", "Arial", "Helvetica", "sans-serif"; font-size: 7pt; line-height: 15pt; color: #666666}
.wVer8 {  font-family: "Verdana", "Arial", "Helvetica", "sans-serif"; font-size: 8pt; line-height: 15pt; color: #666666}
.wVer9 {  font-family: "Verdana", "Arial", "Helvetica", "sans-serif"; font-size: 9pt; line-height: 15pt; color: #666666}
.wTah7 {  font-family: "Verdana", "Arial", "Helvetica", "sans-serif"; font-size: 7pt; line-height: 15pt; color: #666666}
.wTah8 {  font-family: "Verdana", "Arial", "Helvetica", "sans-serif"; font-size: 8pt; line-height: 15pt; color: #666666}
.wTah9 {  font-family: "Verdana", "Arial", "Helvetica", "sans-serif"; font-size: 9pt; color: #666666}
.wButton { background:#E5E5E5; font-size:9pt; color:#666666}
.wSave { background:#E5E5E5;  font-family: Verdana; font-size: 7pt; line-height: 12pt; color: #666666}
.wForm { border:solid 1; border-color:#E5E5E5; background:#FFFFFF; font-family: Verdana; font-size:9pt;color:#666666}
.line { background-color:#A3A3A3; height:1;}

#kicbox {width: 200; height: 100; overflow: auto; padding:2px; border:1 solid #E5E5E5; background-color:white;}
BODY 
{
	scrollbar-face-color:#F7F7F7; 
	scrollbar-shadow-color:#cccccc ;
	scrollbar-highlight-color: #FFFFFF;
	scrollbar-3dlight-color: #FFFFFF;
	scrollbar-darkshadow-color: #FFFFFF;
	scrollbar-track-color: #FFFFFF;
	scrollbar-arrow-color: #cccccc;
}

</STYLE>
</head>

<body leftmargin="10" topmargin="10">
<form  name = manage action = 'manage.php'  method = 'get'>
	<input type=hidden name=data         value='<?=$data?>' >
	<input type=hidden name=select_group         value='<?=$select_group?>' >
	<input type=hidden name=no         value='<?=$no?>' >
	<input type=hidden name=mode         value='' >
<table width="300" border="0" cellspacing="0" cellpadding="3" style="border:solid 1; border-color:#666666;") align="cneter">
  <tr> 
    <td class ="wDefault" colspan ="3"><strong>W</strong>hite <strong>B</strong>oard 게시물 관리</td>
  </tr>
   <tr> 
    <td class ="line" width="100"></td>
	<td class ="line" width="100"></td>
	<td class ="line" width="100"></td>
  </tr>
  <tr> 
    <td width="100" class ="wDefault">선택한 게시물</td>
    <td colspan="2" class ="wDefault"><DIV id=kicbox style="LEFT: 0px; POSITION: relative; TOP: 0px"><?=$message?></div></td>
  </tr>
  <tr> 
    <td class ="line" width="100" colspan="3"></td>	
  </tr>
  <tr> 
    <td colspan="2" class ="wDefault">&nbsp;선택한 <b><?=$no?> </b>개의 게시물을 </td>
    <td width="100" align="center"><input type=button value='삭 제' class=wForm onclick="del_selected()"></td>
  </tr>
  <tr> 
    <td class ="line" width="100" colspan="3"></td>	
  </tr>
  <tr> 
    <td colspan="2" class ="wDefault"><?=$Row[func]?> 게시판으로 </td>
    <td align="center">
		<input type=button value='복 사' class=wForm onclick="copy_selected()">
	</td>
  </tr>
  <!--
  <tr> 
    <td class ="line" width="100" colspan="3"></td>	
  </tr>
  <tr> 
    <td colspan="2" class ="wDefault">
		<input type="checkbox" name="opt1" value ="1" checked> 이동된 게시물에 표시하고<br>
		<input type="checkbox" name="opt2" value ="1"> 원래의 게시물에 표시하고
	</td>
    <td align="center">
		<input type=button value='이 동' class=wForm onclick="move_selected()">
	</td>
  </tr>
  -->
</table>
</form>

</body>
</html>
