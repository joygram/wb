<?
	///////////////////////////
	// 권한검사와 기본환경 읽기 
	// 순서를 지켜주어야 함. 
	// 2002/03/15
	///////////////////////////
	include("../lib.php") ;
	$C_base = get_base(1) ; //기준이 되는 주소 받아오기, lib.php에 있음.
	include($C_base[dir]."/auth/auth.php") ; //권한,인증모듈 선언및 초기화 실행

	include("../file_list.php") ;
	include("../conf/config.php") ;

	$C_auth_perm = "7000" ;
	$auth->perm($C_auth_user, $C_auth_group, $C_auth_perm, $check_data) ;
	///////////////////////////


	$dir=opendir("../data/");
	while ($file = readdir($dir)) 
	{
		if(eregi("deleted",$file))
		{
			$list[]=$file;
		}//if
	}//while
	closedir($dir);

	for($i=0; $i<count($list); $i++)
	{
		$dir2=opendir("../data/$list[$i]");
		while ($file2 = readdir($dir2)) 
		{
			if(($file2 != ".") && ($file2 != ".."))
			{
				unlink ("../data/$list[$i]/$file2");
			}
		}
		closedir($dir2);
		rmdir ("../data/$list[$i]");
	}


	$dir2=opendir("../conf/");
	while ($file3 = readdir($dir2)) 
	{
		if(eregi("deleted",$file3))
		{
			unlink ("../conf/$file3");
		}//if
	}//while
	closedir($dir2);

	echo "모든 불필요한 폴더와 데이타를 삭제하였습니다. ";
?>
