<?php
/**
	2002/08/04
	system.ini.php파일을 최초로 생성한다.
	@todo 
*/

/**
인증 절차 필요
*/
$_debug = 0 ;
require_once("../../lib/io.php") ;
require_once("../../lib/system_ini.php") ;
require_once("../../lib/get_base.php") ;
$C_base = get_base(2) ;

// register_globals에 관계없이 변수사용과 호환성을 위해서
prepare_server_vars() ;
global $__SERVER, $__GET, $__POST, $__COOKIE, $__FILES, $__ENV, $__SESSION ;

require_once("$C_base[dir]/lib/wb.inc.php") ;
$cont = file("$C_base[dir]/release_no") ;
$installed_release_no = chop($cont[0]) ;
$installed_ver = chop($cont[1]) ;

//2002/11/01
$URL = array("") ;

//이미 설치 되어 있다면: 관리자도구에서 오픈 
if(file_exists("$C_base[dir]/setup{$installed_ver}_{$installed_release_no}.done"))
{
	if ($_debug) echo("setup{$installed_ver} already done.") ;

	//require_once("$C_base[dir]/lib/wb.inc.php") ;
	require_once("$C_base[dir]/auth/auth.php") ; 
	umask(0000) ;
	$conf[auth_perm] = "7000" ;
	$auth->perm($conf[auth_user], $conf[auth_group], $conf[auth_perm], $check_data) ;
	$hide['setup'] = "<!--\n" ;
	$hide['/setup'] = "-->\n" ;

	$URL['prev'] = "language.php?upgrade=$upgrade" ;
	$URL['next'] = "db.php?upgrade=$upgrade&cmd=save" ;
}
else
{
	//PASS
	$hide['admin_tool']  = "<!--\n" ;
	$hide['/admin_tool'] = "-->\n" ;

	$URL['prev'] = "language.php?upgrade=$upgrade" ;
	$URL['next'] = "db.php?upgrade=$upgrade&cmd=next" ;
}

if( file_exists("$C_base[dir]/system.ini.php") ) 
{
	include("$C_base[dir]/system.ini.php") ; 
}

switch($__GET["cmd"])
{
	case "next" :
		$ini[db_type]   = $db_type ;
		$ini[db_uid]    = $db_uid ;
		$ini[db_passwd] = $db_passwd ;
		$ini[db_name]   = $db_name ;

		$ini[use_board]   = $C_use_board ;
		$ini[use_counter] = $C_use_counter ;
		$ini[use_member]  =	$C_use_member ;
		$ini[use_mail]    = $C_use_mail ; 
		save_system_ini("$C_base[dir]/system.ini.php", $ini) ;

		$url = "admin.php?upgrade=$upgrade" ;	
		echo("<script>document.location.href='$url';</script>") ;
		exit ;
		break ;

	case "save" :
		$ini[db_type]   = $db_type ;
		$ini[db_uid]    = $db_uid ;
		$ini[db_passwd] = $db_passwd ;
		$ini[db_name]   = $db_name ;

		$ini[use_board]   = $C_use_board ;
		$ini[use_counter] = $C_use_counter ;
		$ini[use_member]  =	$C_use_member ;
		$ini[use_mail]    = $C_use_mail ; 
		save_system_ini("$C_base[dir]/system.ini.php", $ini) ;

		$url = "db.php?upgrade=$upgrade" ;	
		echo("<script>document.location.href='$url';</script>") ;
		exit ;
		break ;

	default:
		break ;
}

include("./html/db_header.html") ;

$Row[title] = "" ;
$Row[func]  = "" ;

$selected['pgsql'] 	  = ($C_db_type == "pgsql")?"selected":"" ;
$selected['mysql']    = ($C_db_type == "mysql")?"selected":"" ;
$selected['old_type'] = ($C_db_type == "old_type")?"selected":"" ; 

//현재 사용하고 있는 DB타입을 확인하여 기존의 정보를 보여주도록
$Row[title] = _L_DATABASE_SELECTION ; 
$Row[func]  = "<select class='wForm' name='db_type' onChange='ToggleSetting(document.main_form);'>
		<option value='old_type' ${selected['old_type']} >"._L_FILESYSTEM."</option>
		<option value='pgsql' ${selected['pgsql']}>PostgreSQL</option>
		<option value='mysql' ${selected['mysql']}>MySQL</option>
		</select>" ;
include("./html/db_list.html") ;

$Row[title] = _L_DBID ; 
$Row[func] = "<input class='wForm' type='text' name='db_uid' value='$Row[db_uid]'>" ;
include("./html/db_list.html") ;

$Row[title] = _L_DBPASSWORD ; 
$Row[func] = "<input class='wForm' type='password' name='db_passwd' value='$Row[db_uid]'>" ;
include("./html/db_list.html") ;

$Row[title] = _L_DBNAME ; 
$Row[func] = "<input class='wForm' type='text' name='db_name' value='$Row[db_name]'>" ;
include("./html/db_list.html") ;

echo("<script>
	function ToggleSetting(form)
	{
		var i ;
		i = form.db_type.selectedIndex ;

		if(form.db_type.options[i].value == 'old_type')
		{
			form.db_uid.disabled = true ;
			form.db_passwd.disabled = true ;
			form.db_name.disabled = true ;
		}	
		else
		{
			window.alert('DB '+form.db_type.options[i].value+' "._L_NOTSUPPORT." ') ;
			form.db_type.selectedIndex = 0 ;
			return false ;

			form.db_uid.disabled = false ;
			form.db_passwd.disabled = false ;
			form.db_name.disabled = false ;
		}
	}
	</script>") ;

echo("<script>ToggleSetting(document.main_form);</script>") ;

include("./html/db_footer.html") ;
?>
