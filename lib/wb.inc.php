<?php
if(!defined("__wb_inc__")) define("__wb_inc__","1") ;
else return ;
/**
������ ȣ���ϴ� ���α׷����� get_base()�Լ��� ��ΰ� ������ �Ǿ��־�� �Ѵ�.
$C_base�� get_base()ȣ�����ķ� ����� �����̴�.
�׷��Ƿ� wb.inc.php�� �ݵ�� get_base()�Լ��� ȣ�� �Ŀ� include�ϵ��� �Ѵ�.
*/

/**
//�����쿡�� ��μ��� �ϴ� �� ���� �ʿ�.
//openbase dir restrict�ϴ� ��� ���...
if( !ini_get("open_basedir") )
	$prev_include_path = ini_get("include_path") ;
else
	$prev_include_path = "." ; 

if (substr(PHP_OS, 0, 3) == 'WIN') 
	ini_set("include_path","$prev_include_path;$C_base[dir]") ;
else
	ini_set("include_path","$prev_include_path:$C_base[dir]") ;
*/

$lib = "$C_base[dir]/lib" ;
require_once("$lib/message.php") ;
require_once("$lib/get_base.php") ;
require_once("$lib/crypt.php") ;
require_once("$lib/lock.php") ;
require_once("$lib/category.php") ;
require_once("$lib/file_list.php") ;
require_once("$lib/filter.php") ;
require_once("$lib/io.php") ;
require_once("$lib/license.php") ;
require_once("$lib/lock.php") ;
require_once("$lib/make_comment.php") ;
require_once("$lib/gradition_color.php") ;
require_once("$lib/make_news.php") ;
require_once("$lib/make_url.php") ;
require_once("$lib/notice.php") ;
require_once("$lib/page.php") ;
require_once("$lib/plugin.php") ;
require_once("$lib/qsort.php") ;
require_once("$lib/reply_list.php") ;
require_once("$lib/string.php") ;
require_once("$lib/system_ini.php") ;
require_once("$lib/total_cnt.php") ;
require_once("$lib/util.php") ;
require_once("$lib/whois.php") ;
require_once("$lib/zlib.php") ;
require_once("$lib/config.php") ;
require_once("$lib/spam.php") ;

$_type = ($C_base["db_type"] == "old_type"||empty($C_base["db_type"]))?"file":$C_base["db_type"] ;
require_once("$lib/db_board_{$_type}.php") ;
require_once("$lib/db_counter_{$_type}.php") ;
require_once("$lib/db_member_{$_type}.php") ;

$C_base[language] = empty($C_base[language])?"ko":$C_base[language] ;
require_once("$C_base[dir]/locale/{$C_base[language]}.php") ;

define("HTML_NOTUSE", 0) ;
define("HTML_USE", 1) ;
define("HTML_FILTER", 2) ;

?>
