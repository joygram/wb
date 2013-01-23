<?php
if(!defined("__wb_gradition__")) define("__wb_gradition__","1") ;
else return ;
	//////////////////////////////
	// gradition color generation 
	//////////////////////////////
	function make_gradation_color($_data, $nTotal, $nOnePage, $app)
	{
		global $C_base ;
		static $scale ;
		static $init;
		static $gradation1 ; 
		static $gradation2 ; 
		static $gradation3 ;
		static $k ;
		static $color1_r, $color1_g, $color1_b ;
		static $color2_r, $color2_g, $color2_b ;

		require_once("$C_base[dir]/lib/config.php") ;
		$conf = read_board_config($_data) ; 
		$C_skin = $conf[skin] ;

		if( empty($conf[grad_start_color]) )
		{
			$color1 = "ffffff" ; 
			$color2 = "333333" ;
		}
		else
		{
			$conf[grad_start_color] = ereg_replace("#", "", $conf[grad_start_color]) ;
			$conf[grad_end_color] = ereg_replace("#", "", $conf[grad_end_color]) ;
			$color1 = $conf[grad_start_color] ;
			$color2 = $conf[grad_end_color] ;
		}

		if( empty($init) ) 
		{
			$color1_r = hexdec(substr($color1,0,2)); // 事1 Red 
			$color1_g = hexdec(substr($color1,2,2)); // 事1 Green
			$color1_b = hexdec(substr($color1,4,2)); // 事1 Blue
			$color2_r = hexdec(substr($color2,0,2)); // 事2 Red
			$color2_g = hexdec(substr($color2,2,2)); // 事2 Green
			$color2_b = hexdec(substr($color2,4,2)); // 事2 Blue

			$scale = ($nOnePage > $nTotal)?$nTotal:$nOnePage ; 
			$k=$scale ;

			$gradation1  = abs((int)(($color2_r-$color1_r)/$scale));
			$gradation2  = abs((int)(($color2_g-$color1_g)/$scale));
			$gradation3  = abs((int)(($color2_b-$color1_b)/$scale));

			$init = "1" ;
		}

		$gc_1 = ($color1_r>$color2_r)?$color1_r-($gradation1*$k):$color1_r+($gradation1*$k); 
		$gc_2 = ($color1_g>$color2_g)?$color1_g-($gradation2*$k):$color1_g+($gradation2*$k);
		$gc_3 = ($color1_b>$color2_b)?$color1_b-($gradation3*$k):$color1_b+($gradation3*$k);
		$color = sprintf("%02x%02x%02x",$gc_1,$gc_2,$gc_3); 
		$k--;

		return $color ;
	}
?>
