<?
//��â�ϰ�� opener���� ����׷��� �����ϰ�.. �׷��� ���� ��� �ڽ��� ���� �����ϵ��� �־��ִ� ��ũ��Ʈ�� ������ ��.
//�̹����ڽ� ��Ų����� POPUP�����׽�Ʈ�� �ϸ��.
//Ư�� �±� ����ó�� 
//���������� �ʿ�. (auth���� ĳ����Ʈ���� ���ִµ�...�׺κ��� ������ �ʿ��� �κп��� ĳ����Ʈ�� ���ֵ��� �����ʿ�. eval�Լ� ��������.
// list skin complete
// list_header
// write comment recovery needed.
// ��â�߱�� ������
// ���ε��� �������� ���� ���� ó���� �ʿ��ϰ� �Ǿ���.

//Write���� �ʿ��� ������ ����Ͽ� �������� �ʿ䰡 ����.
//imgbox.php�� write.php���� ȣ���ϴ� �����������.

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
	//������ �Ǵ� �ּ� �޾ƿ���, lib.php�� ����.
	$C_base = get_base(1) ; 
	$wb_charset = wb_charset($C_base[language]) ;
 	//����,������� ����� �ʱ�ȭ ����
	require_once("$C_base[dir]/auth/auth.php") ;
	if( $log == "on" ) $auth->login() ; 
	else if( $log == "off" ) $auth->logout() ; 
	require_once("$C_base[dir]/lib/wb.inc.php") ;
	///////////////////////////
	umask(0000) ;//�������� �⺻ umask�� �����ش�.
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

		//����׷����
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



<!-- imgbox.php�� wirte.php���� include�ϵ��� ����. �ϴ� ���⼭�� TEST-->
<form name=wb_form method=post action='imagebox.php' enctype='multipart/form-data'>
<textarea name='comment' rows=10 cols=40>
<?=$comment?>
</textarea>

<?=$imgbox_main?> 
</form>

