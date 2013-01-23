<?php
include("txt-db-api.php") ;

$db = new Database("myDB") ;

/*
$rs = $db->executeQuery("CREATE TABLE people (prename str DEFAULT 'john', id inc, name str DEFAULT 'good');") ;

echo("insert BEGIN<br>") ;
$max_size = 1000000 ;
$max_exec_time = ini_get("max_execution_time") ;

ini_set("max_execution_time", $max_size/1000) ;


for($i=0; $i < $max_size; ++$i)
{
	$rs = $db->executeQuery("INSERT INTO people (prename, name) VALUES ('pre_$i','name_$i');") ;

}

echo("insert complete...<br>") ;
exit ;
*/




$rs = $db->executeQuery("SELECT prename, name FROM people WHERE prename = 'pre_100' ORDER BY prename DESC; ") ;

echo ("select completed<br>") ;

$i = 0 ;
while($rs->next())
{
	$row = $rs->getCurrentValues();
	echo("$i-th {$row[0]}, {$row[1]}, {$row[2]}<br>") ;

	$i++ ;
	if($i== 100)break ;
}


?>