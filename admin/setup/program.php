<?php
/**
	@todo
*/
$_debug = 0 ;
require_once("../../lib/io.php") ;
include("../../lib/system_ini.php") ;
require_once("../../lib/get_base.php") ;
$C_base = get_base(2) ;
require_once("$C_base[dir]/lib/wb.inc.php") ;

// register_globals�� ������� �������� ȣȯ���� ���ؼ�
prepare_server_vars() ;
global $__SERVER, $__GET, $__POST, $__COOKIE, $__FILES, $__ENV, $__SESSION ;

$cont = file("$C_base[dir]/release_no") ;
$installed_release_no = chop($cont[0]) ;
$installed_ver = chop($cont[1]) ;

//2002/11/01 2000/xp���� ȣȯ��������..
$URL = array("") ;

//�̹� ��ġ �Ǿ� �ִٸ�: �����ڵ������� ���� 
if(file_exists("$C_base[dir]/setup{$installed_ver}_{$installed_release_no}.done"))
{
	if ($_debug) echo("setup{$installed_ver} already done.") ;

	require_once("$C_base[dir]/auth/auth.php") ; 
	umask(0000) ;
	$conf[auth_perm] = "7000" ;
	$auth->perm($conf[auth_user], $conf[auth_group], $conf[auth_perm], $check_data) ;
	$hide['setup'] = "<!--\n" ;
	$hide['/setup'] = "-->\n" ;

	$URL['prev'] = "timezone.php?upgrade=$upgrade";
	$URL['next'] = "program.php?upgrade=$upgrade&cmd=save";
	$reload = true ; //������ �޴� ���ε�...
}
else
{
	//PASS
	$hide['admin_tool']  = "<!--\n" ;
	$hide['/admin_tool'] = "-->\n" ;

	$URL['prev'] = "timezone.php?upgrade=$upgrade";
	$URL['next'] = "program.php?upgrade=$upgrade&cmd=next";
	$reload = false ;
}

$check_cnt = 0 ;
if( file_exists("$C_base[dir]/system.ini.php") )
{
	include("$C_base[dir]/system.ini.php") ;
	$checked[board]   = ($C_use_board=="on")?"checked":"" ;
	$checked[counter] = ($C_use_counter=="on")?"checked":"" ;
	$checked[member]  = ($C_use_member=="on")?"checked":"" ;
	$checked[mail]    = ($C_use_mail=="on")?"checked":"" ;
}

switch($__GET["cmd"])
{
	case "next" :
		$ini[use_board]   = $use_board ;
		$ini[use_counter] = $use_counter ;
		$ini[use_member]  = $use_member ;
		$ini[use_mail]    = $use_mail ;

		save_system_ini("$C_base[dir]/system.ini.php", $ini) ;

		@touch("$C_base[dir]/setup{$installed_ver}_{$installed_release_no}.done", "0644") ;
		//2002/10/15 �����ϸ� �����Ѵ�.
		@unlink("$C_base[dir]/installed_ver") ;		

		$mode = ($upgrade)?_L_UPGRADE:_L_INSTALL ;

		echo("<script>alert('$mode"._L_SETUP_COMPLETE."');</script>") ;

		$url = "../index.php" ;
		echo("<script>document.location.href='$url';</script>") ;
		exit ;
		break ;

	case "save" :
		$ini[use_board]   = $use_board ;
		$ini[use_counter] = $use_counter ;
		$ini[use_member]  = $use_member ;
		$ini[use_mail]    = $use_mail ;
		save_system_ini("$C_base[dir]/system.ini.php", $ini) ;

		$url = "program.php?upgrade=$upgrade" ;
		if($reload)
		{
			echo("<script>parent.leftFrame.location.reload();</script>") ;
		}
		echo("<script>document.location.href='$url';</script>") ;
		exit ;
		break ;

	default:
		break ;
}


echo("<script>
	function enable_all(form)
	{
		for( var i = 0; i < form.elements.length; i++)
		{
			form.elements[i].disabled = false ;
		}
	}
	</script>") ;


include("./html/program_header.html") ;

$Row[title] = "" ;
$Row[func] = "" ;

$Row[title] = _L_BOARD ; 
$Row[func] = "<input type='checkbox' name='use_board' ${checked['board']} >" ; 
include("./html/program_list.html") ;


$Row[title] = _L_MEMBER ; 
$Row[func] = "<input type='checkbox' name='use_member' ${checked['member']} disabled >" ; 
include("./html/program_list.html") ;


$Row[title] = _L_COUNTER ; 
$Row[func] = "<input type='checkbox' name='use_counter' ${checked['counter']} >" ; 
include("./html/program_list.html") ;


$Row[title] = _L_MAILER ;
$Row[func] = "<input type='checkbox' name='use_mail' ${checked['mail']} disabled >" ; 
include("./html/program_list.html") ;

include("./html/program_footer.html") ;
?>
