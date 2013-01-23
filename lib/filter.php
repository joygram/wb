<?php
if(!defined("__wb_filter__")) define("__wb_filter__","1") ;
else return ;
/**
	�̸� ����
	filter�ؾ��ϴ� ��� true ����
	����ص� ���� ��� false ����
*/
function filter_name($_data, $name, $wb_prg="board")
{
	global $C_base ;
	$_debug = 0 ;
	require_once("$C_base[dir]/lib/config.php") ;
	$read_config = "read_{$wb_prg}_config" ;
	$conf = $read_config($_data) ;
	$C_skin = $conf[skin] ;

	$result = 0 ;
	$conf[filter_name]  = eregi_replace("[[:space:]]+", "", $conf[filter_name]);
   	$filter_array = explode(",", $conf[filter_name]);

	for( $i = 0 ; $i < sizeof($filter_array) ; $i++)
	{
		if(empty($filter_array[$i]))
		{
			continue ;
		}
		if(eregi($filter_array[$i], $name))
		{
			$result = 1; 
			break ;
		}
	}

	return $result ;	

}

/**
	���� ����
	2003/11/09
	filter�ؾ��ϴ� ��� true ����
	����ص� ���� ��� false ����
*/
function filter_subject($_data, $subject, $wb_prg="board")
{
	global $C_base ;
	$_debug = 0 ;
	require_once("$C_base[dir]/lib/config.php") ;
	$read_config = "read_{$wb_prg}_config" ;
	$conf = $read_config($_data) ;
	$C_skin = $conf[skin] ;

	$result = 0 ;
	$conf[filter_subject]  = eregi_replace("[[:space:]]+", "", $conf[filter_subject]);
   	$filter_array = explode(",", $conf[filter_subject]);

	for( $i = 0 ; $i < sizeof($filter_array) ; $i++)
	{

		if(empty($filter_array[$i]))
		{
			continue ;
		}
		if($_debug) echo("{$filter_array[$i]}, $subject<br>") ;
		if(eregi($filter_array[$i], $subject))
		{
			$result = 1; 
			break ;
		}
	}
	return $result ;	
}

/**
��ɼ����� �߰��� ������ �˻��Ͽ� ���͸��� ���θ� �˷���
filter�ؾ��ϴ� ��� true ����
����ص� ���� ��� false ����
*/
function filter_txt($_data, $comment, $wb_prg="board") 
{
	$_debug = 0 ;
	global $C_base ;

	require_once("$C_base[dir]/lib/config.php") ;
	$read_config = "read_{$wb_prg}_config" ;
	$conf = $read_config($_data) ;
	$C_skin = $conf[skin] ;
	//�⺻������ �������ش�. 2002/04/02
	if( !isset($conf[filter_txt_use]))
	{
		$conf[filter_txt_use] = 1 ;
		$conf[filter_txt] = "����,����,���\n������,Ȩ������,����\n��õ,������\n��õ,����,��\n����,���,��\n�չ�,��,��\n����,��õ,����\n����,����\n����,�ſ�,����\n�ſ�,ī��,��û,�߱�\n���Ӵ�,��,��\ndonjunda.net\ngoindols.com\nnewmoney.co.kr\nadhappy.co.kr\nadhappy.com\nnetpoints.co.kr\nassaweb.com\ndonnamu.co.kr\ngamebusiness.net\ncash,surfer\nalladvantage\ngetpaid4\nadity,cash\ngoldemail\nmintmail.com\n����,����\n����,����\n��ģ,����" ;
	}
		//������� �ʴ� ��� ���
	if( $conf[filter_txt_use] == 0)
	{
		if($_debug) echo("NO FILTER USE<br>") ;
		return false ;
	}

	$comment = str_replace(" ","", $comment) ;
	$comment = str_replace("\n","", $comment) ;

		//���ڿ��� \n�� ��� �迭������.
	$filter = false ;
	$conf[filter_txt] = str_replace(" ","", $conf[filter_txt]) ;
	$filter_list = explode("\n", $conf[filter_txt]) ;
	for($i = 0 ; $i < sizeof($filter_list) ; $i++ )
	{
		if(empty($filter_list[$i])) continue ;	
		if($_debug) echo("filter_txt: [$i]<br>") ;
		$filter_list[$i] = chop($filter_list[$i]) ;
			//���Խ� ǥ�� ���� 2002/05/05
		$filter_list[$i] = str_replace("[","\[", $filter_list[$i]) ;
		$filter_list[$i] = str_replace("]","\]", $filter_list[$i]) ;
		$filter_list[$i] = str_replace("|","\|", $filter_list[$i]) ;
		$filter_list[$i] = str_replace("*","\*", $filter_list[$i]) ;

		if($_debug) echo("filter_txt: [$filter_list[$i]]<br>") ;

		if(empty($filter_list[$i]))
		{
			continue ;
		}

		$word_array = explode(",", $filter_list[$i]) ;

		$filter = false ;
		$total_match = sizeof($word_array) ;
		$match = 0 ;

/*		for($j = 0 ; $j < sizeof($word_array); $j++)
		{
			if(empty($word_array[$j]))
			{
				if($_debug) echo("empty $j th word_array, total match minus<br>") ;
				$total_match--;
				continue ;
			}

				// strpos()�� ��ã���� ��쿡�� boolean�� false�� ����
			//$ok = strpos($comment, $word_array[$j])  ;
			if($_debug) echo("word_array[$j]:[$word_array[$j]]<br>") ;
			if( eregi($word_array[$j], $comment ) )
			{
				if($_debug) echo("match $word_array[$j]<br>") ;
				$match++ ;
			}
			else // �� ã����
			{
				break ;
			}
		}

		if($_debug) echo("[$filter_list[$i]][$match][$total_match]<br>") ;
		if($match >= $total_match && $match > 0)
		{
			$filter = true ;
			break ;
		}
*/
		//���� �ܾ� ����Ʈ�� ó�� �ܾ �˻��ؼ� comment �� ��� �ִ� ��쿡�� ������ �ܾ���� ��� �ִ����� Ȯ���ؼ�, �ܾ ��� ��� ������ $filter ������ true �� �ѱ�� �ݺ����� ��Ŵ
		// ��� ������ üũ�ʿ�...
		
		// preg_match() �� strpos() �� �ٲ� 2004.1.18
		//�������� -  Tip: Do not use preg_match() if you only want to check if one string is contained in another string. Use strpos() or strstr() instead as they will be faster.  from http://php.net		
		
		$pos = strpos($comment,"$word_array[0]");
		if (!($pos === false))		
		{
			$match=1;	
			$filtered_word = $word_array[0];
			for($j =1 ; $j < sizeof($word_array); $j++)
				{
					if(!(strpos($comment,"$word_array[$j]")===false))
					{
						$match++;
						if($_debug) echo("$word_array[$j]<br>");						
						if($_debug) echo("$match<br>");
					}
					else
					{
						break;
					}
				}
			if($match == sizeof($word_array))
			{				
				$filter = implode("," ,$word_array);				
				break;
			}
		}		
		// ������� ����
	}
	return $filter;  // ���͸��� �ܾ� ���� ���Ͻ�Ŵ --> write.php ������ ���� 
}

/**
��ɼ����� �߰��� ������ �˻��Ͽ� ���͸��� ���θ� �˷���
*/
function filter_ip($_data, $remote_ip, $wb_prg="board") 
{
	$_debug = 0 ;
	global $C_base ;
	require_once("$C_base[dir]/lib/config.php") ;
	$read_config = "read_{$wb_prg}_config" ;
	$conf = $read_config($_data) ;
	$C_skin = $conf[skin] ;
	//�⺻������ �������ش�. 2002/04/02
	if( !isset($conf[filter_ip_use]))
	{
		$conf[filter_ip_use] = 1 ;
	}
		//������� �ʴ� ��� ���
	if( $conf[filter_ip_use] == 0)
	{
		if($_debug) echo("NO FILTER USE<br>") ;
		return false ;
	}

	$remote_ip   = str_replace(" ","", $remote_ip) ;
	$conf[filter_ip] = str_replace(" ","", $conf[filter_ip]) ;

		//���ڿ��� \n�� ��� �迭������.
	$filter = false ;
	$filter_list = explode("\n", $conf[filter_ip]) ;

	$filter = false ;
	for($i = 0 ; $i < sizeof($filter_list) ; $i++ )
	{
		if($_debug) echo("filter_ip: [$i]<br>") ;
		$filter_list[$i] = chop($filter_list[$i]) ;
			//���Խ� ǥ�� ���� 2002/05/05
		$filter_list[$i] = str_replace("*", "", $filter_list[$i]) ;
		$filter_list[$i] = str_replace("[","\[", $filter_list[$i]) ;
		$filter_list[$i] = str_replace("]","\]", $filter_list[$i]) ;
		$filter_list[$i] = str_replace("|","\|", $filter_list[$i]) ;
		$filter_list[$i] = str_replace("*","\*", $filter_list[$i]) ;

		if($_debug) echo("filter_ip: [$filter_list[$i]]<br>") ;

		if(empty($filter_list[$i]))
		{
			continue ;
		}

		if($_debug) echo("filter_ip:remote_ip:$remote_ip<br>") ;
		if(eregi($filter_list[$i],$remote_ip))
		{
			if($_debug) echo("filter_ip:it's filtered<br>") ;
			$filter = true ;
		}
	}
	return $filter ;
}

?>
