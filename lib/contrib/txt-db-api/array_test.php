<?php
//Pops out a single item from a class member ($items) 
//two-dimensional array. 
//Where 'item_id_num' == the argument $item_id
/*
   function removeItem($item_id)
   { 
       $num_items = count($this->items);
       for($i = 0; $i < $num_items; $i++)
       {
           if($this->items[$i]['item_id_num'] == $item_id)
           {
               array_splice($this->items,$i,1);
           }
       }
   }
   */
///////////////////////////////////////////
//Yeah, I know it's simple. ; )
////////////////////////////////////////// 

//$colNames = array("", "name", "addr", "age", "") ;
$colNames = array("name", "addr", "age") ;
$rows = array() ;

function PRINT_ROWS($rows)
{
	global $colNames ;
	$size = sizeof($rows[$colNames[0]]);
	echo("size:$size<br>") ;
	echo("row[j][i]<br>") ;
	for($i = 0; $i < count($rows[$colNames[0]]); ++$i)
	{
		for($j = 0; $j < count($colNames); ++$j)
		{
			echo("rows[$j][$i] = [{$rows[$colNames[$j]][$i]}]<br>") ;
		}
		echo("----<br>") ;

	}
}

function DELETE_COLS($colNr)
{
	global $colNames ;
	global $rows ;
	$size=count($rows[$colNames[$colNr]]) ;
	$colName = $colNames[$colNr] ;
	echo("size:$size colNr:$colNr {$colNames[$colNr]}<br>") ;
	for($i = 0; $i < $size; ++$i)
	{
		$rows[$colName][$i] = "" ;
	}
}

function a_sort($sort_cmd, $orderTypes, $colNr) 
{
	global $rows ;
	global $colNames ;

	$colName = $colNames[$colNr] ;

	for($i =0; $i < count($orderTypes); ++$i) 
	{
		if($orderTypes[$i] == ORDER_ASC)
			$evalString .= "\$rows[\"".$colName."\"], SORT_ASC, ";
		else
			$evalString .= "\$rows[\"".$colName."\"], SORT_DESC, ";
	}
	
	$a_size = count($colNames) ;
	echo("::a_size:[$a_size]<br>") ;
	for($i=0 ;$i < count($colNames); ++$i)
	{
		$colName = $colNames[$i] ;
		echo(":::$i,$colNr:colName:[$colName][{$colNames[$i]}]<br>") ;
		if($i != $colNr)
		{
			$evalString .= "\$rows[\"".$colName."\"]" ;
			echo("::::i:[$i,count(colNames)]:[".count(colNames)."]") ;
			if($i < count($colName) ) 
			{
				$evalString .= ", " ;
			}
		}
	}


		
	$evalString = "array_multisort(".$evalString.");";
		//$this->rows["board_group"]

	echo("evalString[$evalString]<br>") ;
	eval($evalString);
	
}

echo("2차원 배열 복사/제거 테스트<br>") ;


echo("data Push<br>") ;
echo("colNames[0]:{$colNames[0]}<br>") ;
echo("colNames[0]:{$colNames[1]}<br>") ;
$max_size = 10 ;
for($i = 0; $i < $max_size ; ++$i)
{
	$rows[$colNames[0]][$i] = "name_$i" ;
	$rows[$colNames[1]][$i] = "addr_".($max_size - $i) ;
	$rows[$colNames[2]][$i] = "age_$i" ;
	echo("{$rows[$colNames[0]][$i]}<br>") ;
}

echo("TEST:{$rows[name][0]}<br>") ;


echo("insert Column<br>") ;
PRINT_ROWS($rows) ;

/*
echo("delete Column<br>") ;
$colNr = 2 ;
DELETE_COLS($colNr) ;
PRINT_ROWS($rows) ;


echo("size test:") ;
echo sizeof($colNames) ;
echo("<br>") ;

echo("size test:") ;
unset($colNames[0]) ;
echo sizeof($colNames) ;
echo("<br>") ;
*/

echo("sort test<br>") ;
$orderTypes[0] = "ORDER_ASC" ;
$colNr = 1;
a_sort($sort_cmd, $orderTypes, $colNr) ;
PRINT_ROWS($rows) ;


echo("sort desc<br>") ;
$orderTypes[0] = "ORDER_DESC" ;
$colNr = 2 ;
a_sort($sort_cmd, $orderTypes, $colNr) ;
PRINT_ROWS($rows) ;

if(!isset($rows["__ROW_ID"][0])) 
{
	echo("not set...<br>") ;
}

$rows["__ROW_ID"][0] = -1 ;
if(!isset($rows["__ROW_ID"][0])) 
{
	echo("not set...<br>") ;
}

unset($rows["__ROW_ID"][0]) ;

$size = count($rows["__ROW_ID"]) ;
echo("size:$size<br>") ;

?>