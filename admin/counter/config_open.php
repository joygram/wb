<?require_once("../../lib/get_base.php") ; ?>
<head>
<title>��ɼ���  [<?=$conf_name?>]</title>
</head>
<frameset rows="*,32" cols="1*" border="0">
    <frame src="./config_read.php?conf_name=<?=$conf_name?>" name="main" scrolling="auto" marginwidth="0" marginheight="0">

    <frame src="./config_menu.php?conf_name=<?=$conf_name?>&dock=<?=$dock?>" name="menu" scrolling="no" marginwidth="0" marginheight="0"> 

	<noframes>
    <body bgcolor="white" text="black" link="blue" vlink="purple" alink="red">
    <p>�� �������� ������, �������� �� �� �ִ� �������� �ʿ��մϴ�.</p>
	</body>
	</noframes>
</frameset>
