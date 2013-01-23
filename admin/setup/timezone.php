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

//2002/11/01 2000/xp에서 호환성때문에..
$URL = array("") ;

//이미 설치 되어 있다면: 관리자도구에서 오픈 
if(file_exists("$C_base[dir]/setup{$installed_ver}_{$installed_release_no}.done"))
{
	if ($_debug) echo("setup{$installed_ver} already done.") ;
	require_once("$C_base[dir]/auth/auth.php") ; 
	umask(0000) ;
	$conf[auth_perm] = "7000" ;
	$auth->perm($conf[auth_user], $conf[auth_group], $conf[auth_perm], $check_data) ;
	$hide['setup'] = "<!--\n" ;
	$hide['/setup'] = "-->\n" ;

	$URL['prev'] = "admin.php?upgrade=$upgrade" ;
	$URL['next'] = "timezone.php?upgrade=$upgrade&cmd=save" ;
}
else
{
	$hide['admin_tool']  = "<!--\n" ;
	$hide['/admin_tool'] = "-->\n" ;

	$URL['prev'] = "admin.php?upgrade=$upgrade" ;
	$URL['next'] = "timezone.php?upgrade=$upgrade&cmd=next" ;
}

if( file_exists("$C_base[dir]/system.ini.php") ) 
{
	include("$C_base[dir]/system.ini.php") ; 
}

switch($__GET["cmd"])
{
	case "next" :
		$ini[timezone]   = $timezone ;

		$ini[use_board]   = $C_use_board ;
		$ini[use_counter] = $C_use_counter ;
		$ini[use_member]  =	$C_use_member ;
		$ini[use_mail]    = $C_use_mail ; 

		save_system_ini("$C_base[dir]/system.ini.php", $ini) ;
		$url = "program.php?upgrade=$upgrade" ; 
		echo("<script>document.location.href='$url';</script>") ;
		exit ;
		break ;

	case "save" :
		$ini[timezone]   = $timezone ;

		$ini[use_board]   = $C_use_board ;
		$ini[use_counter] = $C_use_counter ;
		$ini[use_member]  =	$C_use_member ;
		$ini[use_mail]    = $C_use_mail ; 

		save_system_ini("$C_base[dir]/system.ini.php", $ini) ;
		$url = "timezone.php?upgrade=$upgrade" ; 
		echo("<script>document.location.href='$url';</script>") ;
		exit ;
		break ;

	default:
		break ;
}

include("./html/timezone_header.html") ;

$Row[title] = "" ;
$Row[func]  = "" ;

$C_timezone = empty($C_timezone)?" 9,Seoul":$C_timezone ;
$tmp = explode(',',$C_timezone) ;
$selected['value'] = $C_timezone ; 
$selected['name']  = $tmp[1] ; 
$tmp = "" ;

//현재 사용하고 있는 DB타입을 확인하여 기존의 정보를 보여주도록
$Row[title] = _L_CITY_SELECT ;
$Row[func]  = "<select name='timezone' size='1' onChange='ToggleSetting(document.main_form);'>
	<option value='$selected[value]' selected >$selected[name]
    <option value='none'>------------
	<option value=' 1,Amsterdam'>Amsterdam
	<option value=' 2,Ankara'>Ankara
	<option value='-7,Arizona'>Arizona
    <option value=' 2,Athens'>Athens
	<option value='12,Auckland'>Auckland
	<option value=' 3,Bagdad'>Bagdad
    <option value=' 7,Bangkok'>Bangkok
	<option value=' 5,Bangalore'>Bangalore
	<option value=' 1,Barcelona'>Barcelona
	<option value=' 8,Beijing'>Beijing
 	<option value=' 1,Berlin'>Berlin
	<option value=' 1,Berne'>Berne
	<option value='-5,Bogota'>Bogota
	<option value=' 3,Brasilia'>Brasilia
	<option value=' 1,Brussels'>Brussels
	<option value='-3,Buenos Aires'>Buenos Aires
	<option value=' 2,Cairo'>Cairo
    <option value='10,Canberra'>Canberra
	<option value='-6,Chicago'>Chicago
    <option value=' 1,Copenhagen'>Copenhagen
	<option value='-7,Denver'>Denver
	<option value=' 0,Edinburgh'>Edinburgh
	<option value='12,Fiji'>Fiji
	<option value=' 1,Geneva'>Geneva
    <option value='-10,Hawaii'>Hawaii
	<option value=' 2,Helsinki'>Helsinki
	<option value=' 8,Hong Kong'>Hong Kong
	<option value=' 2,Istanbul'>Istanbul
	<option value=' 2,Johannesburg'>Johannesburg
	<option value='-5,Lima'>Lima
	<option value=' 1,Lisbon'>Lisbon
	<option value=' 0,London'>London
	<option value='-8,Los Angeles'>Los Angeles
	<option value=' 1,Madrid'>Madrid
	<option value=' 8,Manila'>Manila
	<option value='10,Melbourne'>Melbourne
	<option value='-6,Mexico City'>Mexico City
	<option value=' 5,Montreal'>Montreal
	<option value=' 3,Moscow'>Moscow
	<option value=' 5,New Delhi'>New Delhi
	<option value='-5,New York'>New York
	<option value=' 2,Nicosia'>Nicosia
    <option value='-5,Ottawa'>Ottawa
	<option value=' 1,Oslo'>Oslo
	<option value=' 1,Paris'>Paris
	<option value=' 8,Perth'>Perth
	<option value=' 1,Prague'>Prague
	<option value=' 2,Pretoria'>Pretoria
	<option value='-2,Reykjavik'>Reykjavik
	<option value='-3,Rio de Janeiro'>Rio de Janeiro
	<option value=' 1,Rome'>Rome
	<option value='-8,San Francisco'>San Francisco
	<option value=' 9,Seoul'>Seoul
	<option value=' 8,Singapore'>Singapore
	<option value=' 1,Stockholm'>Stockholm
	<option value=' 3,St. Petersbury'>St. Petersbury
	<option value='10,Sydney'>Sydney
	<option value=' 3,Tehran'>Tehran
    <option value=' 9,Tokyo'>Tokyo
    <option value=' 1,Valletta'>Valletta
	<option value='-8,Vancouver'>Vancouver
	<option value=' 1,Vienna'>Vienna
	<option value=' 1,Warsaw'>Warsaw
    <option value='-5,Washington DC'>Washington DC      
	</select>" ;

include("./html/timezone_list.html") ;

echo("<script>
	function ToggleSetting(form)
	{
		var i ;
		i = form.timezone.selectedIndex ;

		if(form.timezone.options[i].value == 'none')
		{
			form.timezone.selectedIndex = 0 ;
		}	
		else
		{
			return false ;
		}
	}
	</script>") ;

echo("<script>ToggleSetting(document.main_form);</script>") ;

include("./html/timezone_footer.html") ;
?>
