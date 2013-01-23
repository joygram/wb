<?php
/*
WhiteBoard 2.0 pre 2001/10
Copyright (c) 2001-2002, WhiteBBs.net, All rights reserved.

소스와 바이너리 형태의 재배포와 사용은 수정을 가하건 그렇지 않건 아래의 조건을 만족할 경우에 한해서만 허용합니다:

1. 소스코드를 재배포 할때는 위에 명시된 저작권 표시와 이 목록에 있는 조건와 아래의 성명서를 반드시 표시 해야만 합니다.

2. 실행할 수 있는 형태의 재배포에서는 위의 저작권 표시( 화이트보드팀과 그 공헌자의 이름)와 이 목록의 조건과 아래의 성명서의 내용이 그 문서나 같이 배포되는 다른 것들에도 포함 되어야 합니다.

3. 본 소프트웨어로부터 파생되어 나온 제품에 구체적인 사전 서면 승인 없이 화이트보드팀의 이름이나 그 공헌자의 이름으로 보증이나 제품판매를 촉진할 수 없습니다.  


저작권을 가진사람과 그 공헌자는 본 소프트웨어를 ‘원본 그대로’ 제공할 뿐 어떤 표현이나 이유 등을 갖는 특별한 목적을 위한 매매보증이나 합목적성을 부인합니다. 저작권을 가진 사람이나 공헌자는 어떠한 직접적, 간접적, 부수적, 특정적, 전형적, 필연적인 손상, 즉 상품이나 서비스의 대체, 사용상의 손실, 데이터의 손실, 이득, 영업의 중단 등에 대해 그것이 어떠한 원인에 의한 것이나, 어떠한 책임론에 의하거나, 계약에 의하거나 절대적인 책임, 민사상 불법행위가 과실에 의하거나 그렇지 아니하거나 본 소프트웨어의 사용중에 발생한 손상에 대해서는 비록 그것이 이미 예고된 것이라 할지라도 책임이 없습니다.  

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
	if(!$to) message ("받으실분의 메일계정을 입력하세요");
	if(!$subject) message ("제목을 입력하세요");
	if(!$body) message ("내용을 입력하세요");


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
		echo("<script>message('메일전송이 제대로 되지 않았습니다.');</script>") ;
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
