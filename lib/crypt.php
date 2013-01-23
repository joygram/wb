<?php
if(!defined("__wb_crypt__")) define("__wb_crypt__","1") ;
else return ;
	/////////////////////////////////
	// md5 encrypt / decrypt function
	/////////////////////////////////
	function keyED($txt,$encrypt_key) 
	{ 
		$encrypt_key = md5($encrypt_key); 
		$ctr=0; 
		$tmp = ""; 

		for ($i=0;$i<strlen($txt);$i++) 
		{ 
			if ($ctr==strlen($encrypt_key)) $ctr=0; 
			$tmp.= substr($txt,$i,1) ^ substr($encrypt_key,$ctr,1); 
			$ctr++; 
		} 
		return $tmp; 
	} 

	function encrypt($txt,$key) 
	{ 
		srand((double)microtime()*1000000); 
		$encrypt_key = md5(rand(0,32000)); 
		$ctr=0; 
		$tmp = ""; 

		for ($i=0;$i<strlen($txt);$i++) 
		{ 
			if ($ctr==strlen($encrypt_key)) $ctr=0; 
			$tmp.= substr($encrypt_key,$ctr,1) . 
			(substr($txt,$i,1) ^ substr($encrypt_key,$ctr,1)); 
			$ctr++; 
		} 
		return keyED($tmp,$key); 
	} 

	function decrypt($txt,$key) 
	{ 
		$txt = keyED($txt,$key); 
		$tmp = ""; 
		for ($i=0;$i<strlen($txt);$i++) 
		{ 
			$md5 = substr($txt,$i,1); 
			$i++; 
			$tmp.= (substr($txt,$i,1) ^ $md5); 
		} 

		return $tmp; 
	} 


	//////////////////////////////
	// encryption / descryption 
	//////////////////////////////
	function wb_encrypt($string, $key2) 
	{
		$key1 = "It's a Real Life @#$!" ;

		// encrypt $string, and store it in $enc_text 
		$enc_text = encrypt(encrypt($string,$key1), $key2); 
		$enc_text = base64_encode($enc_text) ;
		$enc_text = urlencode($enc_text) ;

		return $enc_text ;
	}

	function wb_decrypt($string, $key2)
	{
		$key1 = "It's a Real Life @#$!" ;

		// decrypt the encrypted text $enc_text, and store it in $dec_text 
		$dec_text = urldecode($string) ;
		$dec_text = base64_decode($dec_text) ;
		$dec_text = decrypt(decrypt($dec_text,$key2), $key1) ; 

		return $dec_text ;
	}
?>
