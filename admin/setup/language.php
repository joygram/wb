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
require_once("$C_base[dir]/lib/wb.inc.php") ;

// register_globals에 관계없이 변수사용과 호환성을 위해서
prepare_server_vars() ;
global $__SERVER, $__GET, $__POST, $__COOKIE, $__FILES, $__ENV, $__SESSION ;


$upgrade = isset($__GET['upgrade'])?$__GET['upgrade']:"";
$cmd = isset($__GET['cmd'])?$__GET['cmd']:"";

$cont = file("$C_base[dir]/release_no") ;
$installed_release_no = chop($cont[0]) ;
$installed_ver = chop($cont[1]) ;
if ($_debug) echo("[$installed_ver]") ;

//2002/11/01 2000/xp에서 호환성때문에..
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
	$URL['next'] = "language.php?upgrade=$upgrade&cmd=save" ;
}
else
{
	//PASS
	$hide['admin_tool']  = "<!--\n" ;
	$hide['/admin_tool'] = "-->\n" ;

	$URL['prev'] = "language.php?upgrade=$upgrade" ;
	$URL['next'] = "language.php?upgrade=$upgrade&cmd=next" ;
}

if (file_exists("$C_base[dir]/system.ini.php")) 
{
	include("$C_base[dir]/system.ini.php") ; 
}

if($_debug) echo("cmd __GET[cmd]{$__GET["cmd"]}<br>") ;
if($_debug) echo("cmd is [$cmd] upgrade[$upgrade]<br>") ;

switch($cmd)
{
	case "next" :

		$C_base = get_base(1) ;
		$ini['language']  = $__GET['language'] ;
		$ini['base_dir']  = $C_base['dir'] ; 

		$ini['use_board']   = $C_use_board ;
		$ini['use_counter'] = $C_use_counter ;
		$ini['use_member']  =	$C_use_member ;
		$ini['use_mail']    = $C_use_mail ; 

		save_system_ini("$C_base[dir]/system.ini.php", $ini) ;
		$url = "db.php?upgrade=$upgrade" ;	
		echo("<script>document.location.href='$url';</script>") ;
		exit ;
		break ;

	case "save" :
		$C_base = get_base(1) ;
		$ini['language']  = $__GET['language'] ;
		$ini['base_dir']  = $C_base['dir'] ; 

		$ini['use_board']   = $C_use_board ;
		$ini['use_counter'] = $C_use_counter ;
		$ini['use_member']  =	$C_use_member ;
		$ini['use_mail']    = $C_use_mail ; 

		save_system_ini("$C_base[dir]/system.ini.php", $ini) ;
		$url = "language.php" ;	
		echo("<script>document.location.href='$url';</script>") ;
		exit ;
		break ;

	default:
		break ;
}

include("./html/language_header.html") ;

$Row['title'] = "" ;
$Row['func']  = "" ;

@$selected['ko'] = ($C_language == "ko")?"selected":"" ;
@$selected['en'] = ($C_language == "en")?"selected":"" ;
@$selected['jp'] = ($C_language == "jp")?"selected":"" ; 
@$selected['zh'] = ($C_language == "zh")?"selected":"" ; 

//현재 사용하고 있는 DB타입을 확인하여 기존의 정보를 보여주도록
$Row['title'] = "언어(Language,言語)" ;
$Row['func']  = "<select name='language' onChange='ToggleSetting(document.main_form);'>
			<option value='ko' ${selected['ko']}>ko(Korean)</option>
			<option value='en' ${selected['en']}>en(English)</option>
			<option value='jp' ${selected['jp']}>jp(Japanese)</option>
			<option value='zh' ${selected['zh']}>zh(Chinese)</option>
		</select>" ;
include("./html/language_list.html") ;

echo("<script>
	function ToggleSetting(form)
	{
		var i ;
		i = form.language.selectedIndex ;

		if(form.language.options[i].value == 'ko' ||
			form.language.options[i].value == 'en' )
		{
		}	
		else
		{
			str = '언어['+form.language.options[i].value+']은 아직 지원하지 않습니다.\\n\\nLanguage['+form.language.options[i].value+'] is not yet supported.' ;
			window.alert(str) ;
			form.language.selectedIndex = 0 ;
			return false ;
		}
	}
	</script>") ;

echo("<script>ToggleSetting(document.main_form);</script>") ;

include("./html/language_footer.html") ;
?>
