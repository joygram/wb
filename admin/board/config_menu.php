<?php
	require_once("../../lib/get_base.php") ;
	require_once("../../lib/get_base.php") ;
	$C_base = get_base(2) ; //기준이 되는 주소 받아오기, lib.php에 있음.
	require_once("$C_base[dir]/lib/wb.inc.php") ;
	// $conf변수 필터링
	$conf_name = eregi_replace("(\.\.|\/|`|'|;|#|~|@|\?|=|&|!|admin\.php|[[:space:]])", "", $conf_name) ;
	$conf_array = explode(".", $conf_name) ;
	$data = $conf_array[0] ;

	if($conf_name == "__global.conf.php")
	{
		$hide["preview"] = "<!--\n" ;
		$hide["/preview"] = "-->\n" ;

		$hide['close'] = "<!--\n";
		$hide['/close'] = "-->\n" ;
	}

	if($dock)
	{
		$hide['close'] = "<!--\n";
		$hide['/close'] = "-->\n" ;
	}
?>
<html>
<head>
<link rel=stylesheet type="text/css" href="../html/style.css">
</head>
<body class="wBody">
<table width='100%' border=0 cellspacing=0 cellpadding=0 height="30" align='center'>
<tr> 
<td valign=top width=100% height=1 class="fline"></td>
</tr>

<form action='GET'>
<tr>
<td align='center' valign='bottom'>
<input type='button' value='<?=_L_BOARD_SAVE?>' onClick='parent.main.enable_func(parent.main.save_form); parent.main.save_form.submit();' class='wButton'>

<?@=$hide["preview"]?>
<input type='button' value='<?=_L_BOARD_PREVIEW?>' onClick="window.open('../../board/list.php?data=<?=$data?>','preview','menubar=yes,toolbar=yes,location=yes,resizable=yes,scrollbars=yes,status=yes');return false; " class='wButton'>
<?@=$hide["/preview"]?>

<?@=$hide["close"]?>
<input type='button' value='<?=_L_WINDOWCLOSE?>' onClick='parent.close();' class='wButton'>
<?@=$hide["/close"]?>
</td>
</tr>
</form>
</table>

</body>
</html>
