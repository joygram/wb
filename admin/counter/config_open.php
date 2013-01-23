<?require_once("../../lib/get_base.php") ; ?>
<head>
<title>기능설정  [<?=$conf_name?>]</title>
</head>
<frameset rows="*,32" cols="1*" border="0">
    <frame src="./config_read.php?conf_name=<?=$conf_name?>" name="main" scrolling="auto" marginwidth="0" marginheight="0">

    <frame src="./config_menu.php?conf_name=<?=$conf_name?>&dock=<?=$dock?>" name="menu" scrolling="no" marginwidth="0" marginheight="0"> 

	<noframes>
    <body bgcolor="white" text="black" link="blue" vlink="purple" alink="red">
    <p>이 페이지를 보려면, 프레임을 볼 수 있는 브라우저가 필요합니다.</p>
	</body>
	</noframes>
</frameset>
