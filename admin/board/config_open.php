<?	
	require_once("../../lib/get_base.php") ;
	//locale ������ �־� �߰���(2004.12.14) by ü���丶��
	$C_base = get_base(2) ; //������ �Ǵ� �ּ� �޾ƿ���, lib.php�� ����.
	require_once("$C_base[dir]/lib/wb.inc.php") ;
?>
<head>
<title><?=_L_FUNCTION_SETUP?>  [<?=$conf_name?>]</title>
</head>
<frameset rows="*,32" cols="1*" border="0">
    <frame src="./config_read.php?conf_name=<?=$conf_name?>" name="main" scrolling="auto" marginwidth="0" marginheight="0">

    <frame src="./config_menu.php?conf_name=<?=$conf_name?>&dock=<?=$dock?>" name="menu" scrolling="no" marginwidth="0" marginheight="0"> 

	<noframes>
    <body bgcolor="white" text="black" link="blue" vlink="purple" alink="red">
    <p><?=_L_FRAMEENABLE_BROWSER_NEED?></p>
	</body>
	</noframes>
</frameset>
