<?
//새창일경우 opener에게 보드그룹을 전달하고.. 그렇지 않을 경우 자신의 폼에 전달하도록 넣어주는 스크립트만 있으면 됨.
//이미지박스 스킨만들고 POPUP지원테스트만 하면됨.
//특수 태그 지원처리 
//폼내용유지 필요. (auth에서 캐쉬콘트롤을 해주는데...그부분을 각각에 필요한 부분에서 캐쉬콘트롤 해주도록 변경필요. eval함수 적용하자.
// list skin complete
// list_header
// write comment recovery needed.
// 새창뜨기로 변경요망
// 업로드후 내용유지 위해 별도 처리가 필요하게 되었슴.

//Write에서 필요한 변수를 기록하여 저장해줄 필요가 있음.
//imgbox.php는 write.php에서 호출하는 방식으로하자.

//imagebox

	function wb_get_ext($file_name)
	{
		$buff = explode(".", $file_name) ;

		if($_debug) print_r($buff) ;

		$count = count($buff) -1 ;
		if(empty($buff[$count])) $count -- ;

		$ext = $buff[$count] ;

		return strtolower($ext) ;
	}

	require_once("../lib/system_ini.php") ;
	require_once("../lib/get_base.php") ;
	//기준이 되는 주소 받아오기, lib.php에 있음.
	$C_base = get_base(1) ; 
	$wb_charset = wb_charset($C_base[language]) ;
 	//권한,인증모듈 선언및 초기화 실행
	require_once("$C_base[dir]/auth/auth.php") ;
	if( $log == "on" ) $auth->login() ; 
	else if( $log == "off" ) $auth->logout() ; 
	require_once("$C_base[dir]/lib/wb.inc.php") ;
	///////////////////////////
	umask(0000) ;//웹서버의 기본 umask를 지워준다.
	///////////////////////////
/*
	echo "ImageBox<br>";
	echo "data : [$data]<br>";
	echo "upload file : [$upload_img_name]<br>";
	echo "saved_path : [$C_base[dir]/board/data/$data/image/$upload_img_name]<br>";
	echo "mode : [$imagebox_mode]<br>";
*/

	$debug_ = 1 ;
	if($debug_) echo "ImageBox[".time()."]<br>";	
	if($debug_) echo("imagebox_mode[$imagebox_mode]") ;

	if(empty($data)) $data = "board" ;
	if($imagebox_mode == "delete")
	{
		$imgbox_dir = "./data/$data/$board_group" ;
		if(file_exists("$imgbox_dir/$img_name"))
		{
			unlink("$imgbox_dir/$img_name") ;
		}
	}
	else if ($imagebox_mode == "upload")
	{

		//보드그룹생성
		if(empty($board_group))
		{
			$board_group = uniqid("D") ;
		}
		$imgbox_dir = "./data/$data/$board_group" ;
		$image_name = uniqid("").".".wb_get_ext($upload_img_name) ;
		if( check_string_pattern( "gif,jpg,png,bmp", $upload_img_name) )
		{
			if(!file_exists($imgbox_dir))        
			{				 
				if(!mkdir($imgbox_dir, 0777))
				{
					echo("$imgbox_dir Create Failed!<br>") ;
				}
				 chmod($imgbox_dir, 0777);						 
			}
			//$C_base[dir]/board/data/
			if($debug_) echo("$upload_img, $image_name") ;
			move_uploaded_file($upload_img, "$imgbox_dir/$image_name") ;		
		}
		else
		{
			err_abort("gif,jpg,png,bmp %s", _L_UPLOAD_LIMIT); 
		}		
	}

	$path = "./data/$data/$board_group";
	if(!empty($board_group) && file_exists($path))
	{
		$dir = dir($path) ;		
		while($entry=$dir->read())
		{
			if($entry == "." || $entry == "..") continue ;
			$imgs[] = $entry;		
			$info_imgs[] = @getimagesize("$path/$entry");
			$filename = $entry.$filename;		

		 }
		$dir->close();	
	}
	
	print_r($imgs) ;
	ob_start() ;
	//$img_box ="<table border=1>";
	include("./list_header.html") ;

	$num_image = sizeof($imgs);
	for ($i =0; $i < $num_image ; $i ++) 
	{
		$no = $i+1 ;
		$img_form = "img_$no" ;
		$img_no = $i ;
		$img_link = "$path/$imgs[$i]" ;
		$img_name = "$imgs[$i]" ;

		include("./imgbox_list.html") ;

	}
	//$img_box .= "</table>";
	include("./list_footer.html") ;
	$imgbox = ob_get_contents() ;
	ob_end_clean() ;

	ob_start() ;
	include("./imgbox.html") ;
	$imgbox_main = ob_get_contents() ;
	ob_end_clean() ;

?>



<!-- imgbox.php를 wirte.php에서 include하도록 하자. 일단 여기서는 TEST-->
<form name=wb_form method=post action='imagebox.php' enctype='multipart/form-data'>
<textarea name='comment' rows=10 cols=40>
<?=$comment?>
</textarea>

<?=$imgbox_main?> 
</form>

