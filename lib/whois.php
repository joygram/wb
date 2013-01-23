<?php
if(!defined("__wb_whois__")) define("__wb_whois__","1") ;
else return ;
function whois_query($server, $host) 
{
	$port = 43; 
	$fp = fsockopen($server, $port, $errno, $errstr, 30); 
	fputs($fp, "$host\n"); 
	
	$list = "" ;
	while(!feof($fp)) 
	{ 
		$list .= fgets($fp, 1024); 
	} 

	
	//echo("<pre>$list</pre>") ;
	return $list ;
}

function whois($host) 
{
	$_debug = 0 ;
	if (!$host) 
		return false;
	$server = array( 
			"domain" => array(
					"whois.krnic.net",	
					"whois.arin.net",
					"whois.apnic.net",
					"whois.ripe.net",),
			"status" => array(
					"No Match",
					"UNSPECIFIED",
					"ALLOCATED UNSPECIFIED",
					"not registered|No Match|Alllocated to",),
			"infomation" => array(
					"기 관 명|서비스명|기관 주소",
					"OrgName|TechHandle|TechName",	
					"OrgName|TechHandle|TechName",	
					"OrgName|TechHandle|TechName",),	
			) ;

	$result = "" ;	
	for($i = 0 ; $i < sizeof($server['domain']); $i++)
	{
		if($_debug) echo("<h1>$i:{$server['domain'][$i]}</h1>") ;
		$result = whois_query($server['domain'][$i],$host) ;
		if(!eregi($server['status'][$i], $result))
		{
			if($_debug) echo("<pre>$result</pre>") ;
			break ;	
		}
	}
	return $result ;
} 
?>
