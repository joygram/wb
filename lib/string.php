<?php
if(!defined("__wb_string__")) define("__wb_string__","1") ;
else return ;
$my_version = "WhiteBoard 2.0.6 2001/11/20" ;
/**
		//전체 내용을  block시킨다음 해당하는 해당하지 않는 태그만 복구 시킴 
*/
	function block_tags($str, $tag_str, $on = "on")
	{
		$_debug = 0 ;
		$result = $str ;

		//2002/10/20 전부다 block하고 싶은 경우
		if($tag_str == "ALL")
		{
			$result = str_replace("<", "&lt;", $result) ;
            $result = str_replace(">", "&gt;", $result) ;
			return $result ;
		}

		$tag_str = eregi_replace("[[:space:]]+", "", $tag_str);
    	$tag_list = explode(",", $tag_str);

		$result = str_replace("&", "&&amp;", $result) ;
		//HTML태그외에는 < 를 보이게 하기위해서, 이런 중노동을 ㅜ.ㅜ.
		//짧은 것일 수록 뒤로 가게 한다.
		$result = str_replace("<", "&lt;", $result) ;
		$result = eregi_replace("&lt;[[:space:]]*(!--|abbr|acronym|address|applet|area|a|base|basefont|bdo|bgsound|big|blockquote|body|br|button|b|caption|center|cite|code|colgroup|col|dd|del|dfn|dir|div|dl|dt|embed|em|fieldset|font|form|frame|frameset|h|head|hr|html|iframe|img|input|ins|isindex|i|kbd|label|link|li|legend|map|marque|menu|meta|noframes|noscript|object|ol|optgroup|option|param|pre|p|q|samp|script|select|small|span|strike|strong|style|sub|sup|s|table|tbody|td|textarea|tfoot|thead|th|title|tr|tt|ul|u|var)", "<\\1", $result) ;

		$result = eregi_replace("&lt;\/[[:space:]]*(abbr|acronym|address|applet|area|a|base|basefont|bdo|bgsound|big|blockquote|body|br|button|b|caption|center|cite|code|colgroup|col|dd|del|dfn|dir|div|dl|dt|embed|em|fieldset|font|form|frame|frameset|h|head|hr|html|iframe|img|input|ins|isindex|i|kbd|label|link|li|legend|map|marque|menu|meta|noframes|noscript|object|ol|optgroup|option|param|pre|p|q|samp|script|select|small|span|strike|strong|style|sub|sup|s|table|tbody|td|textarea|tfoot|thead|th|title|tr|tt|ul|u|var)[[:space:]]*", "<\/\\1", $result) ;

		$result = str_replace("&&amp;", "&", $result) ;

		if( $on == "on" )
		{
			for( $i = 0 ; $i < sizeof($tag_list) ; $i++)
			{
				if( empty($tag_list[$i]) ) 
				{
					continue ;
				}
				if($_debug) echo("::STEP1:[$tag_list[$i]][$result]::\n") ;
				$result=eregi_replace("<[[:space:]]*${tag_list[$i]}[[:space:]]+(.*)>","&lt;${tag_list[$i]} \\1&gt;", $result);

				if($_debug) echo("::STEP2:[$tag_list[$i]][$result]::\n") ;
				$result=eregi_replace("<[[:space:]]*/[[:space:]]*${tag_list[$i]}[[:space:]]+(.*)>","&lt;/${tag_list[$i]}&gt;", $result);
				if($_debug) echo("::STEP3:[$tag_list[$i]][$result]::\n") ;
			}
		}
		else // off일경우
		{
		}

		return $result ;
	}

	/**	한글 포함문자 cutting
		phpschool.com에서 하얀부엉이 님의 소스 참조
		길이가 넘어가는 경우 빈공백으로 날아오는 버그 수정	
	function cutting($tt,$cut_length)
	{ 
		$text_len=strlen($tt); 
		if( $cut_length >= $text_len )
		{
				// just return ;
			return $tt;
		}

		$trim_len=strlen(substr($tt,0,$cut_length)); 
		if($text_len > $trim_len)
		{ 
			for($jj=0;$jj < $trim_len;$jj++)
			{ 
				$uu=ord(substr($tt, $jj, 1)); 
				if( $uu > 127 )
				{ 
					$jj++; 
				} 
			} 
		} 
		$text2=substr($tt,0,$jj); 
		$text2="$text2"." "; 
		return $text2; 
	}
	*/

 //아루시엔 (arucien@hanmail.net) 님 소스 http://phpschool.com/bbs2/inc_view.html?id=6167&code=tnt2
	function cutting($str,$cut_length) 
	{
		if ($cut_length >= strlen($str)) return $str;
		$klen = $cut_length - 1;
		while(ord($str[$klen]) & 0x80) $klen--;
		return substr($str, 0, $cut_length -((($cut_length + $klen) & 1) ^ 1)). "...";
    }

	/**
		문자열 패턴이 있는지 검사
	*/
	function check_string_pattern( $string_pattern, $string, $delimeter = "," )
	{

		$string_pattern = eregi_replace("[[:space:]]+", "", $string_pattern);
        $ext_array = explode($delimeter, $string_pattern ) ;
		$pattern = "" ;
        for( $i = 0 ; $i < sizeof($ext_array) ; $i++ )
        {
            if( empty($pattern) )
            {
                $pattern = $ext_array[$i] ;
            }
            else
            {
                $pattern = "$pattern|$ext_array[$i]" ;
            }
        }

		$found = eregi( $pattern, $string ) ;
		return $found ;
	}



	//////////////////////////
	// 주소를 링크로 변환하기
	//////////////////////////
	function link_func($str)
	{
		$str = "<a href='goto.php?url=".base64_encode($str)."&encoded=1' target='_blank'>$str</a>" ;
		return $str ;
	}

	function url2link($str) 
	{
		global $C_base ;
		$temp = strip_tags($str) ;
		$exist = eregi("(ftp|http|https|mms)://([^[:space:]]*)([[:alnum:]#?/&=]+[^&<])", $temp); 
		if(!$exist)
		{
			return $str;
		}

		//$str = ereg_replace("http://([-/.a-zA-Z0-9_~#%$?&=:\200-\377\(\)]+)","<a href='http://\\1' target=_blank>http://\\1</a>",$str); 
		//$str = ereg_replace("((www.)([a-zA-Z0-9@:%_.~#-\?&]+[a-zA-Z0-9@:%_~#\?&/]))", "http://\\1", $str);
		//$str = ereg_replace("((ftp://|http://|https://){2})([a-zA-Z0-9@:%_.~#-\?&]+[a-zA-Z0-9@:%_~#\?&/])", "http://\\3", $str);

		//$str = ereg_replace("([_\.0-9a-z-]+@([0-9a-z][0-9a-z-]+\.)+[a-z]{2,3})","<a href=\"mailto:\\1\">\\1</A>", $str);

		//@todo 개선필요.
		//$str = eregi_replace("([[:alnum:]]+)://([^[:space:]'><]*)([[:alnum:]#?/&=]+[^<&>])", "<a href=\"\\1://\\2\\3\" target=\"_blank\">\\1://\\2\\3</a>", $str); 
		if(function_exists("fsockopen") && function_exists("preg_replace"))
		{
			//2002/10/20 여러개 링크가 들어가는 경우 문제가 있음. 
			//여러개 링크 처리 필요.
			$str = preg_replace("#([a-zA-Z]+?)://([a-zA-Z0-9!%:;~,$@=\#&\_\.\-\?\*\\\/\+]+[^ '<>])#e", "link_func('$1://$2')", $str) ; 
			//$str = preg_replace("#([\n ]?)([a-zA-Z]+?)://([a-zA-Z0-9!%:;~,$@=\#&\_\.\-\?\*\\\/\+]+[^ '<>])#e", "link_func('$2://$3')", $str) ; 

			//$str = eregi_replace("([[:alnum:]]+)://([[:alnum:]?~%&#:=/\_\.\-\+]+[^ '<>])", "<a href=\"$C_base[url]/board/goto.php?url=$url&encoded=1\" target=\"_blank\">\\1://\\2</a>", $str) ; 
		}
		else
		{
			$str = eregi_replace("([[:alnum:]]+)://([[:alnum:]?~%&#:=/\_\.\-\+]+[^ '<>])", "<a href=\"\\1://\\2\" target=\"_blank\">\\1://\\2</a>", $str) ; 
		}
   

		//$str = ereg_replace("(ftp://|http://|https://|mms://)([a-zA-Z0-9@:%_.~#-\?&=\200-\377\(\)]+[a-zA-Z0-9:@%_~#\?/=])", "<a href='\\1' target=\"_blank\">\\1</a>", $str) ;
		return $str;
	}

	// 2002/10/19 php.net refer
	function wb_highlight($string)
	{
		$array_contenido = explode("[w_code]",$string);
		$final = $array_contenido[0];
		for($i = 1;$i <= count($array_contenido);$i++)
		{
			$array_contents = explode("[/w_code]",$array_contenido[$i]);
			if( empty($array_contents[0]) ) 
				continue ;

			// w_code전후에 엔터입력시 엔터 문자 제거
			$pos1 =  strpos($array_contents[0],"\r\n") ;
			if(is_integer($pos1) && $pos1 == 0)
				$array_contents[0] = substr($array_contents[0], $pos1+2, strlen($array_contents[0])-1) ;
			$pos2 = strrpos($array_contents[0], "\r\n") ;
			if(is_integer($pos2) && $pos2 == strlen($array_contents[0])-2)
				$array_contents[0] = substr($array_contents[0], $pos1, strlen($array_contents[0])-2) ;

			$array_contents[0] = "<?$array_contents[0]?>" ;
			$old_set = error_reporting(E_ERROR|E_PARSE) ;
			ob_start();
			highlight_string($array_contents[0]);
			$array_contents[0] = ob_get_contents();
			error_reporting($old_set) ;
			ob_end_clean();
				
			$pos1 = strpos ($array_contents[0],"&lt;?"); 
			$pos2 = strrpos ($array_contents[0],"?&gt;"); 
			$array_contents[0] = "<table width='100%' class='wCode'><tr><td class='wCode'>\n".substr($array_contents[0], $pos1+5, $pos2-($pos1+5))."\n</td></tr></table>\n" ;
			$final .= $array_contents[0].$array_contents[1];
		}
		return $final;
	}

	// 2002/10/20 
	// 현재는 w_code밖에 없지만 추후에 개선 요망
	// 여러개 있을 경우 처리
	// 문자열을 토큰 단위로 끊고 추가 데이터를 넣어 주는 방향으로..?
	function wb_token($string)
	{
		$array_contenido = explode("[w_code]",$string);

		$pos = 0 ;
		$final["cont"][$pos] = $array_contenido[0];
		$final["attr"][$pos] = "NORMAL" ;
		$pos++ ;

		for($i = 1;$i <= count($array_contenido);$i++)
		{
			$array_contents = explode("[/w_code]",$array_contenido[$i]);
			if( empty($array_contents[0]) ) 
				continue ;
			$final["cont"][$pos] = "[w_code]".$array_contents[0]."[/w_code]" ;
			$final["attr"][$pos] = "W_CODE" ;
			$pos++ ;

			$final["cont"][$pos] = $array_contents[1];
			$final["attr"][$pos] = "NORMAL" ;
			$pos++ ;
		}
		return $final;
	}

 // table 사용시에 nl2br 에 의해 생기는 <br /> 제거 ( phpschool.com 의 행인님 소스 참고 )  --> 체크 필요할런지도
	function clear_br($string)
	{
        $pattern = "<(/[T]|[T])(ABLE|R|D)([[:graph:] ]*)(>|>[[:space:]]*)<br />";
        Return eregi_replace($pattern,"<\\1\\2\\3>",$string);
	}
?>
