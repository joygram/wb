<?php
if(!defined("__wb_qsort__")) define("__wb_qsort__","1") ;
else return ;
/**
	Quick sort
	from: www.php.net, reference sort, Richard.C.Mitchell@Boeing.com 
*/
function qsort_multiarray(&$array, $column = 0, $order = SORT_ASC, $first = 0, $last = -2)
{
	$_debug = 0 ;

	if($_debug) echo("qsort_multiarray:order[$order] column[$column]<br>") ;
	if($last == -2) $last = count($array) - 1; 
	if($_debug) echo("qsort_multiarray:first[$first],last[$last]<br>") ;

	if( $last > $first )
	{
		$alpha = $first ;
		$omega = $last ;
		$guess = $array[$alpha][$column] ;

		if($_debug) echo("qsort_multiarray:alpha[$alpha]omega[$omega]<br>") ;
		while($omega >= $alpha)
		{
			if($_debug) echo("qsort_multiarray:<b>compare and change</b><br>") ;
			
			if($order == SORT_ASC)
			{
				if($_debug) echo("qsort_multiarray:order ASC<br>") ;
			
				if($_debug) echo("qsort_multiarray:[".$array[$alpha][$column]."][$guess]<br>") ;
				while($array[$alpha][$column] < $guess) 
				{
					if($_debug) echo("qsort_multiarray:[".$array[$alpha][$column]."][$guess]<br>") ;
					$alpha++ ;
				}
				while($array[$omega][$column] > $guess) 
				{
					if($_debug) echo("[".$array[$omega][$column]."][$guess]<br>") ;
					$omega-- ;
				}
			}
			else // SORT_DESC
			{
				while($array[$alpha][$column] > $guess) $alpha++ ;
				while($array[$omega][$column] < $guess) $omega-- ;
			}
			if($alpha > $omega) break;
			$temporary       = $array[$alpha] ;
			$array[$alpha++] = $array[$omega] ;
			$array[$omega--] = $temporary ;
		}

		qsort_multiarray($array,$column,$order, $first, $omega);
		qsort_multiarray($array,$column,$order, $alpha, $last);
	}
}
?>
