<?php
/**
	2002/08/04
	관리자 기본정보 설정
*/
$_debug = 0 ;
require_once("../../lib/io.php") ;
require_once("../../lib/system_ini.php") ;
require_once("../../lib/get_base.php") ;
$C_base = get_base(2) ;
$cont = file("$C_base[dir]/release_no") ;
$installed_release_no = chop($cont[0]) ;
$installed_ver = chop($cont[1]) ;

require_once("$C_base[dir]/lib/wb.inc.php") ;

// register_globals에 관계없이 변수사용과 호환성을 위해서
prepare_server_vars() ;
global $__SERVER, $__GET, $__POST, $__COOKIE, $__FILES, $__ENV, $__SESSION ;

if($_debug) echo("installed_ver[$installed_ver]<br>") ;

//2002/11/01
$URL = array("") ;

//관리자도구에서 실행하는 경우
if(file_exists("$C_base[dir]/setup{$installed_ver}_{$installed_release_no}.done"))
{
	require_once("$C_base[dir]/lib/wb.inc.php") ;
	require_once("$C_base[dir]/auth/auth.php") ; 
	umask(0000) ;
	$conf[auth_perm] = "7000" ;
	$auth->perm($conf[auth_user], $conf[auth_group], $conf[auth_perm], $check_data) ;
	$hide['setup'] = "<!--\n" ;
	$hide['/setup'] = "-->\n" ;

	$URL['prev'] = "db.php?upgrade=$upgrade" ;
	$URL['next'] = "admin.php?upgrade=$upgrade&cmd=save" ;
}
else
{
	$hide['admin_tool']  = "<!--\n" ;
	$hide['/admin_tool'] = "-->\n" ;

	$URL['prev'] = "db.php?upgrade=$upgrade" ;
	$URL['next'] = "admin.php?upgrade=$upgrade&cmd=next" ;
}

if( file_exists("$C_base[dir]/member/admin.php") )
{
	include("$C_base[dir]/member/admin.php") ;
	$Row['uid']   = $C_admin_id ;
	$Row['alias'] = $C_admin_alias ;
	$Row['email'] = $C_admin_email ;
	$Row['homepage'] = $C_admin_homepage ;
	$Row['passwd_exist'] = !empty($C_admin_password)?"1":"" ;
	$Row['avatar'] = $C_admin_avatar ;
}

switch($__GET['cmd'])
{
	case "next" :
		if( empty($passwd) )
		{
			$passwd = $C_admin_password ;
		}

		//파일을 받았는지 check
		//파일을 원하는 디렉토리에 복사한 후 삭제한다.
		if( !empty($__FILES[InputFile][name]) )
		{
			$attach_ext = "jpg,jpeg,gif,png" ;
			if( !check_string_pattern( $attach_ext, $__FILES[InputFile][name] ) )
			{
				err_abort("{$__FILES[InputFile][name]}:[$attach_ext]%s", _L_UPLOAD_LIMIT );
			}
			// 2002/03/26 open_basedir 제한때문에 수정.
			move_uploaded_file($__FILES[InputFile][tmp_name], "$C_base[dir]/member/avatar_1") ;
		}
		if( $__FILES[InputFile][size] == 0 )
		{
			$__FILES[InputFile][name] = "" ;
		}

		$fd = fopen("$C_base[dir]/member/admin.php", "w") ;
		fwrite( $fd, "<?php\n") ;
		fwrite( $fd, "\$C_admin_id       = \"$uid\";\n" ) ;
		fwrite( $fd, "\$C_admin_password = \"$passwd\";\n" ) ;
		fwrite( $fd, "\$C_admin_alias    = \"$alias\";\n" ) ;
		fwrite( $fd, "\$C_admin_email    = \"$email\";\n" ) ;
		fwrite( $fd, "\$C_admin_homepage = \"$homepage\";\n" ) ;
		fwrite( $fd, "\$C_admin_avatar   = \"avatar_1\";\n") ;
		fwrite( $fd, "?>\n") ;
		fclose( $fd ) ;

		$url = "timezone.php?upgrade=$upgrade" ;	
		echo("<script>document.location.href='$url';</script>") ;
		exit ;
		break ;

	case "save" :
		if($_debug) echo("SAVE [$InputFile_name]") ;
		if( empty($passwd) )
		{
			$passwd = $C_admin_password ;
		}

		
		//파일을 받았는지 check
		//파일을 원하는 디렉토리에 복사한 후 삭제한다.
		if( !empty($__FILES[InputFile][name]) )
		{
			$attach_ext = "jpg,jpeg,gif,png" ;
			if( !check_string_pattern( $attach_ext, $__FILES[InputFile][name] ) )
			{
				err_abort("{$__FILES[InputFile][name]}:[$attach_ext]%s", _L_UPLOAD_LIMIT );
			}
			// 2002/03/26 open_basedir 제한때문에 수정.
			move_uploaded_file($__FILES[InputFile][tmp_name], "$C_base[dir]/member/avatar_1" ) ;
			$avatar = "avatar_1" ;
		}
		else 
		{
			$avatar = "" ;
		}

		if( $__FILES[InputFile][size] == 0 )
		{
			$__FILES[InputFile][name] = "" ;
			$avatar = "" ;
		}

		if($remove_image == "on")
		{
			//
			if(file_exists("$C_base[dir]/member/avatar_1"))
			{
				unlink("$C_base[dir]/member/avatar_1") ;
			}
			$avatar = "" ;
		}

		$fd = fopen("$C_base[dir]/member/admin.php", "w") ;
		fwrite( $fd, "<?php\n") ;
		fwrite( $fd, "\$C_admin_id       = \"$uid\";\n" ) ;
		fwrite( $fd, "\$C_admin_password = \"$passwd\";\n" ) ;
		fwrite( $fd, "\$C_admin_alias    = \"$alias\";\n" ) ;
		fwrite( $fd, "\$C_admin_email    = \"$email\";\n" ) ;
		fwrite( $fd, "\$C_admin_homepage = \"$homepage\";\n" ) ;
		fwrite( $fd, "\$C_admin_avatar   = \"$avatar\";\n") ;
		fwrite( $fd, "?>\n") ;
		fclose( $fd ) ;

		$url = "admin.php" ;	
		echo("<script>document.location.href='$url';</script>") ;
		exit ;
		break ;

	default :
		break ;
}

include("./html/admin_header.html") ;

$Row[title] = "" ; $Row[func] = "" ;

$Row[title] = _L_ID ;
$Row[func] = "<input class='wForm' type='text' name='uid' value='$Row[uid]'>" ;
include("./html/admin_list.html") ;

$Row[title] = _L_PASSWORD ; 
$Row[func] = "<input class='wForm' type='password' name='passwd' value=''>" ;
include("./html/admin_list.html") ;

$Row[title] = _L_PASSWORD_RETRY ; 
$Row[func] = "<input class='wForm' type='password' name='passwd_retry' value=''>" ;
include("./html/admin_list.html") ;

$Row[title] = _L_ALIAS ; 
$Row[func] = "<input class='wForm' type='text' name='alias' value='$Row[alias]'>" ;
include("./html/admin_list.html") ;

$Row[title] = _L_EMAIL ; 
$Row[func] = "<input class='wForm' type='text' name='email' value='$Row[email]'>" ;
include("./html/admin_list.html") ;

$Row[title] = _L_HOMEPAGE ; 
$Row[func] = "<input class='wForm' type='text' name='homepage' value='$Row[homepage]'>" ;
include("./html/admin_list.html") ;

$Row[title] = _L_ADMINIMAGE ; 
$Row[func] = "<input class='wForm' type='file' name='InputFile'>" ;
if(!empty($C_admin_avatar))
{
	$Row[func] .= "<br><input class='wDefault' type='checkbox' name='remove_image'>"._L_REMOVE_ADMINIMAGE."" ;
}

if( file_exists("$C_base[dir]/member/avatar_1") )
{
	$Row[func] .= " <img src='$C_base[url]/member/avatar_1'><br>" ;
	$Row[func] .= "$C_admin_avatar" ;
}
include("./html/admin_list.html") ;

include("./html/admin_footer.html") ;
?>
