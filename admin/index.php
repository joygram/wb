<?php
	require_once("../lib/system_ini.php") ;
	require_once("../lib/get_base.php") ;
	$C_base = get_base(1, "on") ; //������ �Ǵ� �ּ� �޾ƿ���, lib.php�� ����.
	require_once("$C_base[dir]/lib/wb.inc.php") ;
	require_once("$C_base[dir]/auth/auth.php") ; //����,������� ����� �ʱ�ȭ ����
	umask(0000) ;
	$conf[auth_perm] = "7000" ;
	$auth->perm($conf[auth_user], $conf[auth_group], $conf[auth_perm], $check_data) ;
?>

<html>
<head>
<title>WhiteBBS Administration/ȭ��Ʈ���� �����ڵ���</title>
<meta http-equiv="Content-Type" content="text/html; charset=euc-kr">
</head>
<frameset rows="74,*" frameborder="NO" border="0" framespacing="0"> 
  <frame name="topFrame" scrolling="NO" noresize src="html/top.html" >
  <frameset rows="*,60" frameborder="NO" border="0" framespacing="0"> 
    <frameset cols="202,*" frameborder="NO" border="0" framespacing="0"> 
      <frame name="leftFrame" scrolling="AUTO" noresize src="menu.php">
      <frameset cols="*,15" frameborder="NO" border="0" framespacing="0"> 
        <frame name="mainFrame" src="main.php">
        <frame name="rightFrame" scrolling="NO" noresize src="html/right.html">
      </frameset>
    </frameset>
    <frame name="bottomFrame" scrolling="NO" noresize src="html/bottom.html">
  </frameset>
</frameset>
<noframes> 
<body bgcolor="#FFFFFF" text="#000000">
		<p>Sorry, your browser doesn't seem to support frames</p>
</body>
</noframes> 
</html>
