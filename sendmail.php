<?php
/*
WhiteBoard 2.0 pre 2001/10
Copyright (c) 2001-2002, WhiteBBs.net, All rights reserved.

�ҽ��� ���̳ʸ� ������ ������� ����� ������ ���ϰ� �׷��� �ʰ� �Ʒ��� ������ ������ ��쿡 ���ؼ��� ����մϴ�:

1. �ҽ��ڵ带 ����� �Ҷ��� ���� ��õ� ���۱� ǥ�ÿ� �� ��Ͽ� �ִ� ���ǿ� �Ʒ��� ������ �ݵ�� ǥ�� �ؾ߸� �մϴ�.

2. ������ �� �ִ� ������ ����������� ���� ���۱� ǥ��( ȭ��Ʈ�������� �� �������� �̸�)�� �� ����� ���ǰ� �Ʒ��� ������ ������ �� ������ ���� �����Ǵ� �ٸ� �͵鿡�� ���� �Ǿ�� �մϴ�.

3. �� ����Ʈ����κ��� �Ļ��Ǿ� ���� ��ǰ�� ��ü���� ���� ���� ���� ���� ȭ��Ʈ�������� �̸��̳� �� �������� �̸����� �����̳� ��ǰ�ǸŸ� ������ �� �����ϴ�.  


���۱��� ��������� �� �����ڴ� �� ����Ʈ��� ������ �״�Ρ� ������ �� � ǥ���̳� ���� ���� ���� Ư���� ������ ���� �Ÿź����̳� �ո������� �����մϴ�. ���۱��� ���� ����̳� �����ڴ� ��� ������, ������, �μ���, Ư����, ������, �ʿ����� �ջ�, �� ��ǰ�̳� ������ ��ü, ������ �ս�, �������� �ս�, �̵�, ������ �ߴ� � ���� �װ��� ��� ���ο� ���� ���̳�, ��� å�ӷп� ���ϰų�, ��࿡ ���ϰų� �������� å��, �λ�� �ҹ������� ���ǿ� ���ϰų� �׷��� �ƴ��ϰų� �� ����Ʈ������ ����߿� �߻��� �ջ� ���ؼ��� ��� �װ��� �̹� ����� ���̶� ������ å���� �����ϴ�.  

WhiteBoard 1.4.2 : 2001/08/15
WhiteBoard 1.4.0 pre : 2001/08/11
WhiteBoard 1.3.0 : 2001/06/17
WhiteBoard 1.2.3 : 2001/05/10
WhiteBoard 1.1.1 2001/4/11
*/
?>
<html>
<head>
<title>WhiteBoard Mail</title>
<meta http-equiv='Content-Type' content='text/html; charset=euc-kr'>
<link rel="stylesheet" href="admin/html/style.css" type="text/css">
<?php
function message ($message)
{
echo("
	<script>
	window.alert (\"$message\");
	history.go(-1);
	</script>
	");
exit;
}

$userfile = eregi_replace("(\.\.|\/|`|'|;|#|~|@|\?|=|&|!|admin)", "", $userfile) ;

if($mode=='mailsend')
{
	if(!$to) message ("�����Ǻ��� ���ϰ����� �Է��ϼ���");
	if(!$subject) message ("������ �Է��ϼ���");
	if(!$body) message ("������ �Է��ϼ���");


	$mailheaders .= "Return-Path: $from\r\n";
	$mailheaders .= "From: $name <$from>\r\n";
	$mailheaders .= "X-Mailer: WhiteBBS Mailer\r\n";

	if ($userfile && $userfile_size) 
	{
		$filename=basename($userfile_name);
		$result=fopen($userfile,"r");
		$file=fread($result,$userfile_size);
		fclose($result);

		if ($userfile_type == "") 
		{
			$userfile_type = "application/octet-stream";
		}

		$boundary = "--------" . uniqid("part");

		$mailheaders .= "MIME-Version: 1.0\r\n";
		$mailheaders .= "Content-Type: multipart/mixed; boundary=\"$boundary\"";

		$bodytext  = "This is a multi-part message in MIME format.\r\n\r\n";
		$bodytext .= "--$boundary\r\n";

		$bodytext .= "Content-Type: text/html; charset=euc-kr\r\n";
		$bodytext .= "Content-Transfer-Encoding: 8bit\r\n\r\n";

		$bodytext .= nl2br(stripslashes($body)) . "\r\n\r\n";
		$bodytext .= "--$boundary\r\n";
		$bodytext .= "Content-Type: $userfile_type; name=\"$filename\"\r\n";
		$bodytext .= "Content-Transfer-Encoding: base64\r\n\r\n";
		$bodytext .= ereg_replace("(.{80})","\\1\r\n",base64_encode($file));
		$bodytext .= "\r\n--$boundary" . "\r\n";
	}
	else 
	{
		$bodytext  = stripslashes($body);
	}

	if($_debug) echo("[$to][$subject][$bodytext][$mailheaders]") ;

	if(!mail($to,$subject,$bodytext,$mailheaders))
	{
		echo("<script>message('���������� ����� ���� �ʾҽ��ϴ�.');</script>") ;
	}
	echo("<script>self.close() ;</script>") ;		
	exit;
}

$to = base64_decode($to) ;
echo("
<script>
function prepare_load()
{
	window.resizeTo( 400, 530 ) ;
	window.focus() ;
}
</script>
</head>

<body onLoad='prepare_load();' leftmargin='0' topmargin='8' marginwidth='0' marginheight='0' class='wBody'>

<table width='100%' border='0' cellpadding='0' cellspacing='0'>
<tr> 
<td width='28' valign='top' background='mail/images/left_bg.gif'>
<img src='mail/images/left_top.gif' width='28' height='38'></td>
<td background='mail/images/top_bg.gif'></td>
<td valign='top' background='mail/images/right_bg.gif'><img src='mail/images/right_top.gif' width='28' height='38'></td>
</tr>

<tr>
<td background='mail/images/left_bg.gif'>
</td>
<td valign='top'>

	<table width='100%' border='0' cellspacing='0' cellpadding='0'>
	<tr>
	<td><a href='http://whitebbs.net' target='_blank' onFocus='this.blur();'><img src='mail/images/top_logo.gif' width='130' height='24' border='0'></a><br><br>
	
		<table border=0 cellspacing=1 align='center' cellpadding='1'>
		
		<tr> 
		<form enctype='multipart/form-data' method='post' action='$PHP_SELF?mode=mailsend'>
		<td class='mDefault'> TO 
		</td>
		<td>
		<input name=to size=45 value='$to' class='cForm'>
		</td>
		</tr>
		
		<tr> 
		<td class='mDefault'> FROM 
		</td>
		<td>
		<input name=from size=45 value='' class='cForm'>
		</td>
		</tr>
		
		<tr> 
		<td class='mDefault'> SUBJECT </td>
		<td>
		<input name=subject size=45 value='' class='cForm'>
		</td>
		</tr>
		
		<tr> 
		<td class='mDefault'> FILE ADD </td>
		<td>
		<input type=file name=userfile size=27 value='' class='cForm'>
		</td>
		</tr>
		
		<tr>
		<td colspan=2 align=center>
		<textarea name=body cols=55 rows=15 wrap=hard class='cForm'></textarea>
		</td>
		</tr>
		
		<tr> 
		<td colspan=2 height=22 align='right'> 
		<input type=submit name=action value='+ SEND +' class='cButton'>&nbsp;
		</td>
		</form>
		</tr>
		
		</table>
	</td>
	</tr>
	</table>
	
</td>
<td background='mail/images/right_bg.gif'><img src='mail/images/right_bg.gif' width='28' height='1'></td>
</tr>

<tr> 
<td height='15'><img src='mail/images/left_bottom.gif' width='28' height='15'></td>
<td background='mail/images/bg_bottom.gif'><img src='mail/images/bg_bottom.gif' width='1' height='15'></td>
<td width='28' align='right' background='mail/images/bg_bottom.gif'><img src='mail/images/right_bottom.gif' width='28' height='15'></td>
</tr>

<tr>
<td colspan='3' align='right' class='copy' height='35'>Copyright 2001-2002 <a href='http://whitebbs.net' target='_blank' onFocus='this.blur();'>Whitebbs.net</a>. All rights reserved.&nbsp;<img src='mail/images/c.gif'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
</td>
</tr>
<tr>
<td colspan='3' class='cTah7' align='center'>
<a href='javascript:parent.close()'>Close</a><br><br>
</td>
</tr>

</table>

</body>
</html>
");

?>
