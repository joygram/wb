<?php
/* $Id: localtime.php,v 1.2 2005/07/30 07:34:09 lovjesus Exp $ */

/*
Copyright (c) 2001, 2002 by Martin Tsachev. All rights reserved.
http://www.mtweb.org

Redistribution and use in source and binary forms,
with or without modification, are permitted provided
that the conditions available at
http://www.opensource.org/licenses/bsd-license.html
are met.
*/

// you can start the session in the calling page too
@session_start();


function set_timezone($offset) {
	if ($offset) {
		$offset = -$offset;
		$_SESSION['GMT_offset'] = 60 * $offset;
		$GMT_offset_str = ( $offset > 0 ) ? '+' : '-';
		$GMT_offset_str .= floor($offset / 60) . ':';
		$GMT_offset_str .= (($offset % 60) < 10 ) ? '0' . $offset % 60 : $offset % 60;
		$_SESSION['GMT_offset_str'] = $GMT_offset_str;
	}
}


function format_datetime($date) {
	return (gmdate('j M Y g:ia', $date + $_SESSION['GMT_offset']) . ' GMT ' . $_SESSION['GMT_offset_str']);
}


function format_date($date) {
	return date('j M Y', $date);
}


/////////////////////////////////////////////////////////////////////////////////////


if (!isset($_SESSION['GMT_offset']) ) {
	$_SESSION['GMT_offset'] = 0;
	$_SESSION['GMT_offset_str'] = '';
}


if (isset($_GET['offset']) ) {
	$_SESSION['offset'] = $_GET['offset'];
	set_timezone($_GET['offset']);
}



if ( !isset($_SESSION['offset']) ) {
?>
	<script type="text/javascript">
		window.onload = setLinks

		function setLinks() {
			var base_url = location.protocol + '//' + location.hostname;
			var now = new Date()
			var offset = now.getTimezoneOffset();

			for (i = 0; document.links.length > i; i++) {
				with (document.links[i]) {
					if (href.indexOf(base_url) == 0) {
						if (href.indexOf('?') == -1) {
							href += '?offset=' + offset;
						} else {
							href += ';offset=' + offset;
						}
					}
				}
			}
		}
	</script>

<?php
}

?>