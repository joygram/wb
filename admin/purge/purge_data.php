<?
	///////////////////////////
	// ���Ѱ˻�� �⺻ȯ�� �б� 
	// ������ �����־�� ��. 
	// 2002/03/15
	///////////////////////////
	include("../lib.php") ;
	$C_base = get_base(1) ; //������ �Ǵ� �ּ� �޾ƿ���, lib.php�� ����.
	include($C_base[dir]."/auth/auth.php") ; //����,������� ����� �ʱ�ȭ ����

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

	echo "��� ���ʿ��� ������ ����Ÿ�� �����Ͽ����ϴ�. ";
?>
