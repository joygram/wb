<?php
/**
현재 설치할 패키지의 버젼 / 릴리즈번호
패키지 버젼 형식 : 버젼 [A-B] , A알파, B베타
릴리즈번호 형식 : 년 월 회수 
*/
$_debug = 0 ;
{//MAIN
	echo("
	<style>
	.wBody 
	{
		background-color: #FFFFFF;
		scrollbar-face-color:#F7F7F7; 
		scrollbar-shadow-color:#cccccc ;
		scrollbar-highlight-color: #FFFFFF;
		scrollbar-3dlight-color: #FFFFFF;
		scrollbar-darkshadow-color: #FFFFFF;
		scrollbar-track-color: #FFFFFF;
		scrollbar-arrow-color: #cccccc
		font-family: Verdana, 굴림체;
		font-size: 12px;
		line-height: 20px;
	};
	.copyright {font-family:Verdana; font-size:8pt; color:#c0c0c0}
	.wButton {height:18; background: #D6D3CE; font-family: Verdana,tahoma;font-size:9pt;color:#000000; border-top: #E7E3E7 1px solid; border-right: #737173 1px solid; border-left: #E7E3E7 1px solid; border-bottom: #737173 1px solid;}
	</style>
	<body class='wBody'>
	") ;

	$C_setup_ver = "2910" ;
	$C_setup_ver_str = "2.9.10" ;
	$C_setup_release_no = "200511073" ; 


	//2002/10/26 stat() lstat()정보 클리어 
	clearstatcache();
	///////////////////////////////
	// 흐름제어 
	///////////////////////////////
    if (isset($_GET['cmd']))
    {
	    switch($_GET['cmd'])
	    {
		    case "exit":
			    echo("화이트보드 $C_setup_ver_str 버젼 설치 프로그램을 종료합니다.<br>애용해 주셔서 감사합니다.<br>") ;
			    if( !file_exists("setup{$C_setup_ver_str}_{$C_setup_release_no}.done") ) 
			    {
				    echo("<br>프로그램 설치가 완료되지 않았습니다.<br> 다시 시도하시려면 <input type=button class='wButton' value='설치시작' onClick=\"document.location.href='./setup.php'\"> 을 누르세요.") ; 
			    }
			    exit ;
		    default :
			    break ;
	    }
    }

	///////////////////////////////
	// 설치 여부 검사  
	///////////////////////////////
	if(file_exists("setup{$C_setup_ver_str}_{$C_setup_release_no}.done"))
	{
		echo("<script>
			alert('화이트보드의 설치가 이미 완료되었습니다.\\n\\n새로 설치를 원하신다면 .done파일을 제거,\\n\\n패키지 파일을 업로드 하신후 setup하십시오.\\n\\n기능 설정은 관리자 도구를 이용하세요.') ;
			document.location.href = 'setup.php?cmd=exit' ;
			</script>") ;
		exit ;
	}

	///////////////////////////////
	// 기본 권한 검사 
	///////////////////////////////
	if(!is_writeable("./"))
	{
		echo("현재 디렉토리에 쓰기 권한이 없습니다. 권한을 [777]로 부여해주 시고 다시 시도해주세요.<br>") ;

		echo("<br><input type=button class='wButton' value='다시 시도' onClick='document.location.reload();'>") ;
		exit ;
	}


	///////////////////////////////
	// 설치 버젼 검사
	///////////////////////////////
	$installed_ver = "" ;

	// setup.done이 없으면 설치가 끝난게 아니므로 installed_ver를 참고한다.
	// 2002/10/15 이부분 개선필요. 새버젼 설치시 empty
	// 자료 내용 추가 필요 version, release_no등으로
	// setup을 한번이라도 시행하면 생김.
	$tmp_installed_ver = "" ;
	if (file_exists("installed_ver")) 
	{
		$cont = file("installed_ver") ;
		$tmp_installed_ver        = chop($cont[0]) ;
		$tmp_installed_release_no = chop($cont[1]) ;
	}

	if(!empty($tmp_installed_ver))
	{
		if($_debug) echo("tmp_installed_Ver[$tmp_installed_ver] C_setup_ver[$C_setup_ver]<br>") ;
		if ($tmp_installed_ver < $C_setup_ver)
		{
			$install_method = "upgrade" ;
		} 
		else if($tmp_installed_ver == $C_setup_ver)
		{
			if(!empty($tmp_installed_release_no))
			{
				if($C_setup_release_no == $tmp_installed_release_no)
				{
					$install_method = "reinstall" ;
				}
				else if($C_setup_release_no > $tmp_installed_release_no)
				{
					$install_method = "upgrade" ;
				}
				else
				{
					$install_method = "downgrade" ;
				}
			}
			else 
			{
				$install_method = "reinstall" ;
			}
		}
		else
		{
			$install_method = "downgrade" ;
		}
		$installed_ver = $tmp_installed_ver ;
		if($_debug) echo("install_method[$install_method]<br>") ;
	}
	// 2.1.2이하 버젼
	else if(!file_exists("./board") && file_exists("./data"))
	{
		$installed_ver = "212" ;	
		$install_method = "upgrade" ;
	}
	// 2.5.0이상의 버젼일 경우
	// 압축만 풀렸을 경우가 있다.
	else if(file_exists("release_no")) 
	{
		//버젼 검사
		$cont = file("release_no") ;
		$installed_release_no = chop($cont[0]) ;
		$installed_ver_str = chop($cont[1]) ;
		$installed_ver = chop($cont[2]) ;

		if( $C_setup_release_no < $installed_release_no )
		{
			$install_method = "downgrade" ;
		}
		else if( $C_setup_release_no == $installed_release_no )
		{
			$install_method = "reinstall" ;
		}
		else
		{
			$install_method = "upgrade" ;
		}
	}
	//2.4.5이하 버젼일경우
	//2002/10/15 파일을 미리 지우는 경우는 VERSION 파일이 없을 수 도 있다.
	else if (file_exists("./board"))
	{
		$installed_ver = "245" ;
		$install_method = "upgrade" ;
	}
	else if ($installed_ver == $C_setup_ver)
	{
		$install_method = "reinstall" ;
	}

	if($_debug) echo("install_method[$install_method]<br>") ;

	///////////////////////////////
	// installed ver파일 만들어내기
	///////////////////////////////
	if(!file_exists("installed_ver"))
	{
		$fd = fopen("installed_ver", "w") ;
		if (!$fd)
		{
			echo("installed_ver파일을 정상적으로 기록할 수 없습니다. 설치가 정상적으로 되지 않을 수 있습니다.<br>") ;
		}
		else
		{
			fwrite($fd, "$installed_ver\n") ;
			fwrite($fd, "$installed_release_no\n") ;
			fclose($fd) ;	
		}
	}
	else
	{
		$cont = file("installed_ver") ;
		$installed_ver = chop($cont[0]) ;	
	}


	if($_debug) echo("install_method[$install_method]<br>") ;
	/**
	*/
	switch ($install_method)
	{
		case "upgrade" :
			$mesg = "화이트보드 $installed_ver 대 버젼이 설치되어 있습니다. 업그레이드 하시겠습니까?" ;
			break ;
		case "downgrade" :
			$mesg = "설치하려는 버젼이 $installed_ver 보다 낮은 버젼입니다. 설치를 진행하면 문제가 발생할 수 있습니다. 설치하시겠습니까?" ;
			break ;
		case "reinstall" :
			$mesg = "화이트보드 $installed_ver 버전을 재설치하시겠습니까?\\n\\n주의: 재설치중 오류가 발생하면 다시한번 실행시켜주시기 바랍니다." ;
			break ;
		case "install" :
			$mesg = "$C_setup_ver_str 버젼을 새로 설치 하시겠습니까?";
			break ;
		default :
			$mesg = "$C_setup_ver_str 버젼을 새로 설치 하시겠습니까?";
			break ;
	}
	echo("<script>
		if(window.confirm('$mesg') )
		{
		}
		else
		{
			document.location.href = 'setup.php?cmd=exit' ;
		}
		</script>") ;


			
	if ($installed_ver < 250 && !empty($installed_ver))
	{
		echo("[$installed_ver]") ;
		check_delfile($installed_ver) ;
	}
			
	///////////////////////////////
	// 패키지 파일 풀기
	///////////////////////////////
	if (file_exists("setup.pkz") && !file_exists("unpkz{$C_setup_ver_str}_{$C_setup_release_no}.done"))
	{
		if(! wb_untar("setup.pkz", "./") )
		{
			echo("패키지 setup.pkz를 푸는데 실패했습니다.<br>") ; 
		} 
		else
		{
			touch("unpkz{$C_setup_ver}_{$C_setup_release_no}.done") ;
			/*
			if(!@unlink("setup.pkz"))
			{
				echo("<script>window.alert('setup.pkz 파일은 더이상 필요하지 않습니다. 디스크 공간 절약을 위해서 삭제 해주세요.');</script>") ;
			}
			*/
		}
	}

	///////////////////////////////
	// 버젼에 맞는 설치가이드 보여주기 
	///////////////////////////////
	if($install_method == "upgrade")
	{
		$url = "doc/upgrade{$C_setup_ver}.txt" ;
	}
	else
	{
		$url ="doc/install{$C_setup_ver}.txt" ;
	}
	//echo("<script>window.open('$url', 'HelpWin', 'width=320,height=300,scrollbars=yes');</script>") ;

	///////////////////////////////
	// 버젼에 맞는 설치/업그레이드 실행 
	///////////////////////////////
	//2002/10/15 임시로 250의 경우 upgrae가 안되도록 막음
	if($install_method == "upgrade" && $installed_ver < 250)
	{
		$url = "admin/upgrade{$installed_ver}.php" ;
	}
	else
	{
		$url = "admin/setup/language.php" ;
	}

	echo("<script>document.location.href='$url';</script>") ;
	exit ;
}


////////////////////////////////////////////////////////////////////////////
/**
*/
function draw_line()
{
	echo("<table width='100%'><tr><td bgcolor=#e3e3e3 height=1></td></tr></table>\n") ;
}

/**
*/
function check_delfile($installed_ver) 
{
	if (file_exists("checkfile{$installed_ver}.done"))
	{
		return ;
	}
	///////////////////////////////
	// 212의 경우  파일 삭제 권고 / 권한 검사
	///////////////////////////////
	if ($installed_ver == "212")
	{
		$del_filelist_212 = array("block_tag.php", "cat.php",
			"confirm.php", "delete.php", "download.php", "file_list.php",
			"goto.php", "lib.php", "list.php", "news.php", "sendmail.php",
			"write.php", "VERSION", "readme.txt", "filelist.txt", 
			"history.txt", "todo.txt", "admin", ) ;
		$found = 0 ;
		clearstatcache() ;
		for	($i = 0; $i < sizeof($del_filelist_212); $i++)
		{
			if (file_exists($del_filelist_212[$i]))
			{
				$found = 1 ;
				break ;
			}
		}

		if ($found)
		{
			echo("현재 디렉토리에서 skin/, conf/, data/, setup.pkz, setup.php를  제외한 파일과 디렉토리을 제거해 주셔야 업그레이드를 할 수 있습니다.<br> 파일의 제거후 다시 시도해주시기 바랍니다.<br>") ;
			echo("삭제 하여야 할 파일/디렉토리들은 아래와 같습니다.<br>") ;
			draw_line() ;

			for	($i = 0; $i < sizeof($del_filelist_212); $i++)
			{
				if (file_exists($del_filelist_212[$i]))
				{
					if (is_dir($del_filelist_212[$i]))
					{
						echo("디렉토리: $del_filelist_212[$i]/<br>") ;
					}
					else
					{
						echo("$del_filelist_212[$i]<br>") ;
					}
				}
			}
			draw_line() ;
			echo("<br><input type=button class='wButton' value='다시 시도' onClick='document.location.reload();'>") ;
			exit ;
		}

		///////////////////////////////
		// check permission
		///////////////////////////////
		$dir_list = "" ;
		$dir_list .= ($data_writeable = is_writeable("./data"))?"":"data, " ;
		$dir_list .= ($skin_writeable = is_writeable("./skin"))?"":"skin, " ;
		$dir_list .= ($conf_writeable = is_writeable("./conf"))?"":"conf " ;

		if( !$data_writeable || !$skin_writeable || !$conf_writeable )
		{
			echo("디렉토리 [$dir_list]에 쓰기 권한 이 없습니다.<br>[$dir_list] 디렉토리의 권한을 777로 바꿔주신 후 다시 시도해주세요.<br>") ;
			echo("<br><input type=button class='wButton' value='다시 시도' onClick='document.location.reload();'>") ;
			exit ;
		}
	}
	else if ($installed_ver == "245")
	{
		$del_filelist_245 = array("README.TXT", "VERSION",
			"cat.php", "list.php", "write.php", "sendmail.php",
			"setup", "admin", "auth", "lib", "theme", 
			"lib/category.php", "lib/config.php", "lib/contrib",
			"lib/crypt.php", "lib/database.php", "lib/file_list.php",
			"lib/filter.php", "lib/get_base.php", "lib/gradition_color.php",
			"lib/io.php", "lib/license.php", "lib/lock.php",
			"lib/make_comment.php", "lib/make_news.php", "lib/make_url.php",
			"lib/message.php", "lib/notice.php", "lib/page.php",
			"lib/qsort.php", "lib/reply_list.php", "lib/string.php",
			"lib/system_ini.php", "lib/total_cnt.php", "lib/util.php",
			"lib/wb.inc.php", "lib/zlib.php",
			"board/HISTORY.TXT", "board/INSTALL.TXT",
			"board/README.TXT", "board/TODO.TXT", "board/VERSION", 
			"board/database.php", "board/delete.php", "board/download.php",
			"board/goto.php", "board/news.php", "board/write.php", 
			"borad/list.php", "board/cat.php", 
			"board/html", "board/images", ) ;
		$found = 0 ;
		clearstatcache() ;
		for	($i = 0; $i < sizeof($del_filelist_245); $i++)
		{
			if (file_exists($del_filelist_245[$i]))
			{
				$found = 1 ;
				break ;
			}
		}

		if ($found)
		{
			echo("현재 디렉토리에서 setup.pkz, setup.php, board/skin/, board/conf/, board/data/ 를 제외한 파일과 디렉토리을 제거해 주셔야 업그레이드를 할 수 있습니다.<br> 파일의 제거후 다시 시도해주시기 바랍니다.<br>") ;
			echo("삭제 하여야 할 파일/디렉토리들은 아래와 같습니다.<br>") ;
			draw_line() ;

			for	($i = 0; $i < sizeof($del_filelist_245); $i++)
			{
				if (file_exists($del_filelist_245[$i]))
				{
					if (is_dir($del_filelist_245[$i]))
					{
						echo("디렉토리: $del_filelist_245[$i]/<br>") ;
					}
					else
					{
						echo("$del_filelist_245[$i]<br>") ;
					}
				}
			}
			draw_line() ;
			echo("<br><input type=button class='wButton' value='다시 시도' onClick='document.location.reload();'>") ;
			exit ;
		}

		///////////////////////////////
		// check permission
		///////////////////////////////
		$dir_list = "" ;
		$dir_list .= ($data_writeable = is_writeable("./board/data"))?"":"board/data, " ;
		$dir_list .= ($skin_writeable = is_writeable("./board/skin"))?"":"board/skin, " ;
		$dir_list .= ($conf_writeable = is_writeable("./board/conf"))?"":"board/conf " ;

		if( !$data_writeable || !$skin_writeable || !$conf_writeable )
		{
			echo("디렉토리 [$dir_list]에 쓰기 권한 이 없습니다.<br>[$dir_list] 디렉토리의 권한을 777로 바꿔주신 후 다시 시도해주세요.<br>") ;
			echo("<br><input type=button class='wButton' value='다시 시도' onClick='document.location.reload();'>") ;
			exit ;
		}
	}
	touch("checkfile{$installed_ver}.done", 0755) ;
}


/**
*/
function wb_untar( $_package, $_dest_dir = ".", $_compress = false )
{
	global $__SERVER, $__GET, $__POST, $__COOKIE, $__FILES, $__ENV, $__SESSION ;

	umask(0000) ;

	$tar = new Archive_Tar($_package, $_compress) ;
	if(!$tar)
	{
		return false ;
	}

	if (!$tar->extract($_dest_dir)) 
	{
		echo('an error ocurred during package extract');
		return false ;
	}

	return true ;
}

//
// +----------------------------------------------------------------------+
// | PHP version 4.0                                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2001 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the PHP license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Sterling Hughes <sterling@php.net>                          |
// |          Stig Bakken <ssb@fast.no>                                   |
// |          Tomas V.V.Cox <cox@idecnet.com>                             |
// |                                                                      |
// +----------------------------------------------------------------------+
//
// $Id: setup.php,v 1.91 2005/11/14 07:08:38 lovjesus Exp $
//

define('PEAR_ERROR_RETURN',   1);
define('PEAR_ERROR_PRINT',    2);
define('PEAR_ERROR_TRIGGER',  4);
define('PEAR_ERROR_DIE',      8);
define('PEAR_ERROR_CALLBACK', 16);

if (substr(PHP_OS, 0, 3) == 'WIN') {
    define('OS_WINDOWS', true);
    define('OS_UNIX',    false);
    define('PEAR_OS',    'Windows');
} else {
    define('OS_WINDOWS', false);
    define('OS_UNIX',    true);
    define('PEAR_OS',    'Unix'); // blatant assumption
}

$GLOBALS['_PEAR_default_error_mode']     = PEAR_ERROR_RETURN;
$GLOBALS['_PEAR_default_error_options']  = E_USER_NOTICE;
$GLOBALS['_PEAR_default_error_callback'] = '';
$GLOBALS['_PEAR_destructor_object_list'] = array();

//
// Tests needed: - PEAR inheritance
//               - destructors
//

/**
 * Base class for other PEAR classes.  Provides rudimentary
 * emulation of destructors.
 *
 * If you want a destructor in your class, inherit PEAR and make a
 * destructor method called _yourclassname (same name as the
 * constructor, but with a "_" prefix).  Also, in your constructor you
 * have to call the PEAR constructor: $this->PEAR();.
 * The destructor method will be called without parameters.  Note that
 * at in some SAPI implementations (such as Apache), any output during
 * the request shutdown (in which destructors are called) seems to be
 * discarded.  If you need to get any debug information from your
 * destructor, use error_log(), syslog() or something similar.
 *
 * @since PHP 4.0.2
 * @author Stig Bakken <ssb@fast.no>
 */
class PEAR
{
    // {{{ properties

    /**
     * Whether to enable internal debug messages.
     *
     * @var     bool
     * @access  private
     */
    var $_debug = false;

    /**
     * Default error mode for this object.
     *
     * @var     int
     * @access  private
     */
    var $_default_error_mode = null;

    /**
     * Default error options used for this object when error mode
     * is PEAR_ERROR_TRIGGER.
     *
     * @var     int
     * @access  private
     */
    var $_default_error_options = null;

    /**
     * Default error handler (callback) for this object, if error mode is
     * PEAR_ERROR_CALLBACK.
     *
     * @var     string
     * @access  private
     */
    var $_default_error_handler = '';

    /**
     * Which class to use for error objects.
     *
     * @var     string
     * @access  private
     */
    var $_error_class = 'PEAR_Error';

    /**
     * An array of expected errors.
     *
     * @var     array
     * @access  private
     */
    var $_expected_errors = array();

    // }}}

    // {{{ constructor

    /**
     * Constructor.  Registers this object in
     * $_PEAR_destructor_object_list for destructor emulation if a
     * destructor object exists.
     *
     * @param string      (optional) which class to use for error objects,
     *                    defaults to PEAR_Error.
     * @access public
     * @return void
     */
    function PEAR($error_class = null)
    {
        $classname = get_class($this);
        if ($this->_debug) {
            print "PEAR constructor called, class=$classname\n";
        }
        if ($error_class !== null) {
            $this->_error_class = $error_class;
        }
        while ($classname) {
            $destructor = "_$classname";
            if (method_exists($this, $destructor)) {
                global $_PEAR_destructor_object_list;
                $_PEAR_destructor_object_list[] = &$this;
                break;
            } else {
                $classname = get_parent_class($classname);
            }
        }
    }

    // }}}
    // {{{ destructor

    /**
     * Destructor (the emulated type of...).  Does nothing right now,
     * but is included for forward compatibility, so subclass
     * destructors should always call it.
     *
     * See the note in the class desciption about output from
     * destructors.
     *
     * @access public
     * @return void
     */
    function _PEAR() {
        if ($this->_debug) {
            printf("PEAR destructor called, class=%s\n", get_class($this));
        }
    }

    // }}}
    // {{{ isError()

    /**
     * Tell whether a value is a PEAR error.
     *
     * @param   mixed   the value to test
     * @access  public
     * @return  bool    true if parameter is an error
     */
    function isError($data) {
        return (bool)(is_object($data) &&
                      (get_class($data) == 'pear_error' ||
                      is_subclass_of($data, 'pear_error')));
    }

    // }}}
    // {{{ setErrorHandling()

    /**
     * Sets how errors generated by this DB object should be handled.
     * Can be invoked both in objects and statically.  If called
     * statically, setErrorHandling sets the default behaviour for all
     * PEAR objects.  If called in an object, setErrorHandling sets
     * the default behaviour for that object.
     *
     * @param int $mode
     *        One of PEAR_ERROR_RETURN, PEAR_ERROR_PRINT,
     *        PEAR_ERROR_TRIGGER, PEAR_ERROR_DIE or
     *        PEAR_ERROR_CALLBACK.
     *
     * @param mixed $options
     *        When $mode is PEAR_ERROR_TRIGGER, this is the error level (one
     *        of E_USER_NOTICE, E_USER_WARNING or E_USER_ERROR).
     *
     *        When $mode is PEAR_ERROR_CALLBACK, this parameter is expected
     *        to be the callback function or method.  A callback
     *        function is a string with the name of the function, a
     *        callback method is an array of two elements: the element
     *        at index 0 is the object, and the element at index 1 is
     *        the name of the method to call in the object.
     *
     *        When $mode is PEAR_ERROR_PRINT or PEAR_ERROR_DIE, this is
     *        a printf format string used when printing the error
     *        message.
     *
     * @access public
     * @return void
     * @see PEAR_ERROR_RETURN
     * @see PEAR_ERROR_PRINT
     * @see PEAR_ERROR_TRIGGER
     * @see PEAR_ERROR_DIE
     * @see PEAR_ERROR_CALLBACK
     *
     * @since PHP 4.0.5
     */

    function setErrorHandling($mode = null, $options = null)
    {
        if (isset($this)) {
            $setmode     = &$this->_default_error_mode;
            $setoptions  = &$this->_default_error_options;
            //$setcallback = &$this->_default_error_callback;
        } else {
            $setmode     = &$GLOBALS['_PEAR_default_error_mode'];
            $setoptions  = &$GLOBALS['_PEAR_default_error_options'];
            //$setcallback = &$GLOBALS['_PEAR_default_error_callback'];
        }

        switch ($mode) {
            case PEAR_ERROR_RETURN:
            case PEAR_ERROR_PRINT:
            case PEAR_ERROR_TRIGGER:
            case PEAR_ERROR_DIE:
            case null:
                $setmode = $mode;
                $setoptions = $options;
                break;

            case PEAR_ERROR_CALLBACK:
                $setmode = $mode;
                if ((is_string($options) && function_exists($options)) ||
                    (is_array($options) && method_exists(@$options[0], @$options[1])))
                {
                    $setoptions = $options;
                } else {
                    trigger_error("invalid error callback", E_USER_WARNING);
                }
                break;

            default:
                trigger_error("invalid error mode", E_USER_WARNING);
                break;
        }
    }

    // }}}
    // {{{ expectError()

    /**
     * This method is used to tell which errors you expect to get.
     * Expected errors are always returned with error mode
     * PEAR_ERROR_RETURN.  Expected error codes are stored in a stack,
     * and this method pushes a new element onto it.  The list of
     * expected errors are in effect until they are popped off the
     * stack with the popExpect() method.
     *
     * @param mixed    a single error code or an array of error codes
     *                 to expect
     *
     * @return int     the new depth of the "expected errors" stack
     */
    function expectError($code = "*")
    {
        if (is_array($code)) {
            array_push($this->_expected_errors, $code);
        } else {
            array_push($this->_expected_errors, array($code));
        }
        return sizeof($this->_expected_errors);
    }

    // }}}
    // {{{ popExpect()

    /**
     * This method pops one element off the expected error codes
     * stack.
     *
     * @return array   the list of error codes that were popped
     */
    function popExpect()
    {
        return array_pop($this->_expected_errors);
    }

    // }}}
    // {{{ raiseError()

    /**
     * This method is a wrapper that returns an instance of the
     * configured error class with this object's default error
     * handling applied.  If the $mode and $options parameters are not
     * specified, the object's defaults are used.
     *
     * @param $message  a text error message or a PEAR error object
     *
     * @param $code     a numeric error code (it is up to your class
     *                  to define these if you want to use codes)
     *
     * @param $mode     One of PEAR_ERROR_RETURN, PEAR_ERROR_PRINT,
     *                  PEAR_ERROR_TRIGGER, PEAR_ERROR_DIE or
     *                  PEAR_ERROR_CALLBACK.
     *
     * @param $options  If $mode is PEAR_ERROR_TRIGGER, this parameter
     *                  specifies the PHP-internal error level (one of
     *                  E_USER_NOTICE, E_USER_WARNING or E_USER_ERROR).
     *                  If $mode is PEAR_ERROR_CALLBACK, this
     *                  parameter specifies the callback function or
     *                  method.  In other error modes this parameter
     *                  is ignored.
     *
     * @param $userinfo If you need to pass along for example debug
     *                  information, this parameter is meant for that.
     *
     * @param $error_class The returned error object will be instantiated
     *                  from this class, if specified.
     *
     * @param $skipmsg  If true, raiseError will only pass error codes,
     *                  the error message parameter will be dropped.
     *
     * @access public
     * @return object   a PEAR error object
     * @see PEAR::setErrorHandling
     * @since PHP 4.0.5
     */
    function &raiseError($message = null,
                         $code = null,
                         $mode = null,
                         $options = null,
                         $userinfo = null,
                         $error_class = null,
                         $skipmsg = false)
    {
        // The error is yet a PEAR error object
        if (is_object($message)) {
            $code        = $message->getCode();
            $userinfo    = $message->getUserInfo();
            $error_class = $message->getType();
            $message     = $message->getMessage();
        }

        if (isset($this) && isset($this->_expected_errors) && sizeof($this->_expected_errors) > 0 && sizeof($exp = end($this->_expected_errors))) {
            if ($exp[0] == "*" ||
                (is_int(reset($exp)) && in_array($code, $exp)) ||
                (is_string(reset($exp)) && in_array($message, $exp))) {
                $mode = PEAR_ERROR_RETURN;
            }
        }

        if ($mode === null) {
            if (isset($this) && isset($this->_default_error_mode)) {
                $mode = $this->_default_error_mode;
            } else {
                $mode = $GLOBALS['_PEAR_default_error_mode'];
            }
        }

        if ($mode == PEAR_ERROR_TRIGGER && $options === null) {
            if (isset($this)) {
                if (isset($this->_default_error_options)) {
                    $options = $this->_default_error_options;
                }
            } else {
                $options = $GLOBALS['_PEAR_default_error_options'];
            }
        }

        if ($mode == PEAR_ERROR_CALLBACK) {
            if (!is_string($options) &&
                !(is_array($options) && sizeof($options) == 2 &&
                  is_object($options[0]) && is_string($options[1])))
            {
                if (isset($this) && isset($this->_default_error_options)) {
                    $options = $this->_default_error_options;
                } else {
                    $options = $GLOBALS['_PEAR_default_error_options'];
                }
            }
        } else {
            if ($options === null) {
                if (isset($this)) {
                    if (isset($this->_default_error_options)) {
                        $options = $this->_default_error_options;
                    }
                } else {
                    $options = $GLOBALS['_PEAR_default_error_options'];
                }
            }
        }
        if ($error_class !== null) {
            $ec = $error_class;
        } elseif (isset($this) && isset($this->_error_class)) {
            $ec = $this->_error_class;
        } else {
            $ec = 'PEAR_Error';
        }
        if ($skipmsg) {
            return new $ec($code, $mode, $options, $userinfo);
        } else {
            return new $ec($message, $code, $mode, $options, $userinfo);
        }
    }

    // }}}
    // {{{ pushErrorHandling()

    /**
    * Push a new error handler on top of the error handler options stack. With this
    * you can easely override the actual error handler for some code and restore
    * it later with popErrorHandling.
    *
    * @param $mode mixed (same as setErrorHandling)
    * @param $options mixed (same as setErrorHandling)
    *
    * @return bool Always true
    *
    * @see PEAR::setErrorHandling
    */
    function pushErrorHandling($mode, $options = null)
    {
        $stack = &$GLOBALS['_PEAR_error_handler_stack'];
        if (!is_array($stack)) {
            if (isset($this)) {
                $def_mode = &$this->_default_error_mode;
                $def_options = &$this->_default_error_options;
                // XXX Used anywhere?
                //$def_callback = &$this->_default_error_callback;
            } else {
                $def_mode = &$GLOBALS['_PEAR_default_error_mode'];
                $def_options = &$GLOBALS['_PEAR_default_error_options'];
                // XXX Used anywhere?
                //$def_callback = &$GLOBALS['_PEAR_default_error_callback'];
            }
            $stack = array();
            $stack[] = array($def_mode, $def_options);
        }

        if (isset($this)) {
            $this->setErrorHandling($mode, $options);
        } else {
            PEAR::setErrorHandling($mode, $options);
        }
        $stack[] = array($mode, $options);
        return true;
    }

    // }}}
    // {{{ popErrorHandling()

    /**
    * Pop the last error handler used
    *
    * @return bool Always true
    *
    * @see PEAR::pushErrorHandling
    */
    function popErrorHandling()
    {
        $stack = &$GLOBALS['_PEAR_error_handler_stack'];
        array_pop($stack);
        list($mode, $options) = $stack[sizeof($stack) - 1];
        if (isset($this)) {
            $this->setErrorHandling($mode, $options);
        } else {
            PEAR::setErrorHandling($mode, $options);
        }
        return true;
    }

    // }}}
}

// {{{ _PEAR_call_destructors()

function _PEAR_call_destructors()
{
    global $_PEAR_destructor_object_list;
    if (is_array($_PEAR_destructor_object_list) &&
        sizeof($_PEAR_destructor_object_list))
    {
        reset($_PEAR_destructor_object_list);
        while (list($k, $objref) = each($_PEAR_destructor_object_list)) {
            $classname = get_class($objref);
            while ($classname) {
                $destructor = "_$classname";
                if (method_exists($objref, $destructor)) {
                    $objref->$destructor();
                    break;
                } else {
                    $classname = get_parent_class($classname);
                }
            }
        }
        // Empty the object list to ensure that destructors are
        // not called more than once.
        $_PEAR_destructor_object_list = array();
    }
}

// }}}

class PEAR_Error
{
    // {{{ properties

    var $error_message_prefix = '';
    var $mode                 = PEAR_ERROR_RETURN;
    var $level                = E_USER_NOTICE;
    var $code                 = -1;
    var $message              = '';
    var $userinfo             = '';

    // Wait until we have a stack-groping function in PHP.
    //var $file    = '';
    //var $line    = 0;


    // }}}
    // {{{ constructor

    /**
     * PEAR_Error constructor
     *
     * @param $message error message
     *
     * @param $code (optional) error code
     *
     * @param $mode (optional) error mode, one of: PEAR_ERROR_RETURN,
     * PEAR_ERROR_PRINT, PEAR_ERROR_DIE, PEAR_ERROR_TRIGGER or
     * PEAR_ERROR_CALLBACK
     *
     * @param $level (optional) error level, _OR_ in the case of
     * PEAR_ERROR_CALLBACK, the callback function or object/method
     * tuple.
     *
     * @access public
     *
     */
    function PEAR_Error($message = 'unknown error', $code = null,
                        $mode = null, $options = null, $userinfo = null)
    {
        if ($mode === null) {
            $mode = PEAR_ERROR_RETURN;
        }
        $this->message   = $message;
        $this->code      = $code;
        $this->mode      = $mode;
        $this->userinfo  = $userinfo;
        if ($mode & PEAR_ERROR_CALLBACK) {
            $this->level = E_USER_NOTICE;
            $this->callback = $options;
        } else {
            if ($options === null) {
                $options = E_USER_NOTICE;
            }
            $this->level = $options;
            $this->callback = null;
        }
        if ($this->mode & PEAR_ERROR_PRINT) {
            if (is_null($options) || is_int($options)) {
                $format = "%s";
            } else {
                $format = $options;
            }
            printf($format, $this->getMessage());
        }
        if ($this->mode & PEAR_ERROR_TRIGGER) {
            trigger_error($this->getMessage(), $this->level);
        }
        if ($this->mode & PEAR_ERROR_DIE) {
            $msg = $this->getMessage();
            if (is_null($options) || is_int($options)) {
                $format = "%s";
                if (substr($msg, -1) != "\n") {
                    $msg .= "\n";
                }
            } else {
                $format = $options;
            }
            die(sprintf($format, $msg));
        }
        if ($this->mode & PEAR_ERROR_CALLBACK) {
            if (is_string($this->callback) && strlen($this->callback)) {
                call_user_func($this->callback, $this);
            } elseif (is_array($this->callback) &&
                      sizeof($this->callback) == 2 &&
                      is_object($this->callback[0]) &&
                      is_string($this->callback[1]) &&
                      strlen($this->callback[1])) {
                      @call_user_method($this->callback[1], $this->callback[0],
                                 $this);
            }
        }
    }

    // }}}
    // {{{ getMode()

    /**
     * Get the error mode from an error object.
     *
     * @return int error mode
     * @access public
     */
    function getMode() {
        return $this->mode;
    }

    // }}}
    // {{{ getCallback()

    /**
     * Get the callback function/method from an error object.
     *
     * @return mixed callback function or object/method array
     * @access public
     */
    function getCallback() {
        return $this->callback;
    }

    // }}}
    // {{{ getMessage()


    /**
     * Get the error message from an error object.
     *
     * @return  string  full error message
     * @access public
     */
    function getMessage ()
    {
        return ($this->error_message_prefix . $this->message);
    }


    // }}}
    // {{{ getCode()

    /**
     * Get error code from an error object
     *
     * @return int error code
     * @access public
     */
     function getCode()
     {
        return $this->code;
     }

    // }}}
    // {{{ getType()

    /**
     * Get the name of this error/exception.
     *
     * @return string error/exception name (type)
     * @access public
     */
    function getType ()
    {
        return get_class($this);
    }

    // }}}
    // {{{ getUserInfo()

    /**
     * Get additional user-supplied information.
     *
     * @return string user-supplied information
     * @access public
     */
    function getUserInfo ()
    {
        return $this->userinfo;
    }

    // }}}
    // {{{ getDebugInfo()

    /**
     * Get additional debug information supplied by the application.
     *
     * @return string debug information
     * @access public
     */
    function getDebugInfo ()
    {
        return $this->getUserInfo();
    }

    // }}}
    // {{{ addUserInfo()

    function addUserInfo($info)
    {
        if (empty($this->userinfo)) {
            $this->userinfo = $info;
        } else {
            $this->userinfo .= " ** $info";
        }
    }

    // }}}
    // {{{ toString()

    /**
     * Make a string representation of this object.
     *
     * @return string a string with an object summary
     * @access public
     */
    function toString() {
        $modes = array();
        $levels = array(E_USER_NOTICE  => 'notice',
                        E_USER_WARNING => 'warning',
                        E_USER_ERROR   => 'error');
        if ($this->mode & PEAR_ERROR_CALLBACK) {
            if (is_array($this->callback)) {
                $callback = get_class($this->callback[0]) . '::' .
                    $this->callback[1];
            } else {
                $callback = $this->callback;
            }
            return sprintf('[%s: message="%s" code=%d mode=callback '.
                           'callback=%s prefix="%s" info="%s"]',
                           get_class($this), $this->message, $this->code,
                           $callback, $this->error_message_prefix,
                           $this->userinfo);
        }
        if ($this->mode & PEAR_ERROR_CALLBACK) {
            $modes[] = 'callback';
        }
        if ($this->mode & PEAR_ERROR_PRINT) {
            $modes[] = 'print';
        }
        if ($this->mode & PEAR_ERROR_TRIGGER) {
            $modes[] = 'trigger';
        }
        if ($this->mode & PEAR_ERROR_DIE) {
            $modes[] = 'die';
        }
        if ($this->mode & PEAR_ERROR_RETURN) {
            $modes[] = 'return';
        }
        return sprintf('[%s: message="%s" code=%d mode=%s level=%s '.
                       'prefix="%s" info="%s"]',
                       get_class($this), $this->message, $this->code,
                       implode("|", $modes), $levels[$this->level],
                       $this->error_message_prefix,
                       $this->userinfo);
    }

    // }}}
}

register_shutdown_function("_PEAR_call_destructors");
/* vim: set ts=4 sw=4: */
// +----------------------------------------------------------------------+
// | PHP version 4.0                                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2001 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.02 of the PHP license,      |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Vincent Blavet <vincent@blavet.net>                          |
// +----------------------------------------------------------------------+
//
// $Id: setup.php,v 1.91 2005/11/14 07:08:38 lovjesus Exp $

/**
* Creates a (compressed) Tar archive
*
* @author   Vincent Blavet <vincent@blavet.net>
* @version  $Revision: 1.91 $
* @package  Archive
*/
class Archive_Tar extends PEAR
{
    /**
    * @var string Name of the Tar
    */
    var $_tarname;

    /**
    * @var boolean if true, the Tar file will be gzipped
    */
    var $_compress;

    /**
    * @var file descriptor
    */
    var $_file;

    // {{{ constructor
    /**
    * Archive_Tar Class constructor. This flavour of the constructor only
    * declare a new Archive_Tar object, identifying it by the name of the
    * tar file.
    * If the compress argument is set the tar will be read or created as a
    * gzip compressed TAR file.
    *
    * @param    string  $p_tarname  The name of the tar archive to create
    * @param    boolean $p_compress if true, the archive will be gezip(ped)
    * @access public
    */
    function Archive_Tar($p_tarname, $p_compress = false)
    {
        $this->PEAR();
        $this->_tarname = $p_tarname;
        if ($p_compress) { // assert zlib extension support
            $extname = 'zlib';
            if (!extension_loaded($extname)) {
                $dlext = (substr(PHP_OS, 0, 3) == 'WIN') ? '.dll' : '.so';
                @dl($extname . $dlext);
            }
            if (!extension_loaded($extname)) {
				echo("CAN'T USE ZIP, your system may not support zlib<br>") ;
				/*
                die("The extension '$extname' couldn't be loaded. ".
                    'Probably you don\'t have support in your PHP '.
                    'to this extension');
				*/
                return false;
            }
        }
        $this->_compress = $p_compress;
    }
    // }}}

    // {{{ destructor
    function _Archive_Tar()
    {
        $this->_close();
        $this->_PEAR();
    }
    // }}}

    // {{{ create()
    /**
    * This method creates the archive file and add the files / directories
    * that are listed in $p_filelist.
    * If the file already exists and is writable, it is replaced by the
    * new tar. It is a create and not an add. If the file exists and is
    * read-only or is a directory it is not replaced. The method return
    * false and a PEAR error text.
    * The $p_filelist parameter can be an array of string, each string
    * representing a filename or a directory name with their path if
    * needed. It can also be a single string with names separated by a
    * single blank.
    * See also createModify() method for more details.
    *
    * @param array  $p_filelist An array of filenames and directory names, or a single
    *                           string with names separated by a single blank space.
    * @return                   true on success, false on error.
    * @see createModify()
    * @access public
    */
    function create($p_filelist)
    {
        return $this->createModify($p_filelist, "", "");
    }
    // }}}

    // {{{ add()
    function add($p_filelist)
    {
        return $this->addModify($p_filelist, "", "");
    }
    // }}}

    // {{{ extract()
    function extract($p_path="")
    {
        return $this->extractModify($p_path, "");
    }
    // }}}

    // {{{ listContent()
    function listContent()
    {
        $v_list_detail = array();

        if ($this->_openRead()) {
            if (!$this->_extractList("", $v_list_detail, "list", "", "")) {
                unset($v_list_detail);
                return(0);
            }
            $this->_close();
        }

        return $v_list_detail;
    }
    // }}}

    // {{{ createModify()
    /**
    * This method creates the archive file and add the files / directories
    * that are listed in $p_filelist.
    * If the file already exists and is writable, it is replaced by the
    * new tar. It is a create and not an add. If the file exists and is
    * read-only or is a directory it is not replaced. The method return
    * false and a PEAR error text.
    * The $p_filelist parameter can be an array of string, each string
    * representing a filename or a directory name with their path if
    * needed. It can also be a single string with names separated by a
    * single blank.
    * The path indicated in $p_remove_dir will be removed from the
    * memorized path of each file / directory listed when this path
    * exists. By default nothing is removed (empty path "")
    * The path indicated in $p_add_dir will be added at the beginning of
    * the memorized path of each file / directory listed. However it can
    * be set to empty "". The adding of a path is done after the removing
    * of path.
    * The path add/remove ability enables the user to prepare an archive
    * for extraction in a different path than the origin files are.
    * See also addModify() method for file adding properties.
    *
    * @param array  $p_filelist     An array of filenames and directory names, or a single
    *                               string with names separated by a single blank space.
    * @param string $p_add_dir      A string which contains a path to be added to the
    *                               memorized path of each element in the list.
    * @param string $p_remove_dir   A string which contains a path to be removed from
    *                               the memorized path of each element in the list, when
    *                               relevant.
    * @return boolean               true on success, false on error.
    * @access public
    * @see addModify()
    */
    function createModify($p_filelist, $p_add_dir, $p_remove_dir="")
    {
        $v_result = true;

        if (!$this->_openWrite())
            return false;

        if ($p_filelist != "") {
            if (is_array($p_filelist))
                $v_list = $p_filelist;
            elseif (is_string($p_filelist))
                $v_list = explode(" ", $p_filelist);
            else {
                $this->_cleanFile();
                $this->_error("Invalid file list");
                return false;
            }

            $v_result = $this->_addList($v_list, "", "");
        }

        if ($v_result) {
            $this->_writeFooter();
            $this->_close();
        } else
            $this->_cleanFile();

        return $v_result;
    }
    // }}}

    // {{{ addModify()
    /**
    * This method add the files / directories listed in $p_filelist at the
    * end of the existing archive. If the archive does not yet exists it
    * is created.
    * The $p_filelist parameter can be an array of string, each string
    * representing a filename or a directory name with their path if
    * needed. It can also be a single string with names separated by a
    * single blank.
    * The path indicated in $p_remove_dir will be removed from the
    * memorized path of each file / directory listed when this path
    * exists. By default nothing is removed (empty path "")
    * The path indicated in $p_add_dir will be added at the beginning of
    * the memorized path of each file / directory listed. However it can
    * be set to empty "". The adding of a path is done after the removing
    * of path.
    * The path add/remove ability enables the user to prepare an archive
    * for extraction in a different path than the origin files are.
    * If a file/dir is already in the archive it will only be added at the
    * end of the archive. There is no update of the existing archived
    * file/dir. However while extracting the archive, the last file will
    * replace the first one. This results in a none optimization of the
    * archive size.
    * If a file/dir does not exist the file/dir is ignored. However an
    * error text is send to PEAR error.
    * If a file/dir is not readable the file/dir is ignored. However an
    * error text is send to PEAR error.
    * If the resulting filename/dirname (after the add/remove option or
    * not) string is greater than 99 char, the file/dir is
    * ignored. However an error text is send to PEAR error.
    *
    * @param array      $p_filelist     An array of filenames and directory names, or a single
    *                                   string with names separated by a single blank space.
    * @param string     $p_add_dir      A string which contains a path to be added to the
    *                                   memorized path of each element in the list.
    * @param string     $p_remove_dir   A string which contains a path to be removed from
    *                                   the memorized path of each element in the list, when
    *                                   relevant.
    * @return                           true on success, false on error.
    * @access public
    */
    function addModify($p_filelist, $p_add_dir, $p_remove_dir="")
    {
        $v_result = true;

        if (!@is_file($this->_tarname))
            $v_result = $this->createModify($p_filelist, $p_add_dir, $p_remove_dir);
        else {
            if (is_array($p_filelist))
                $v_list = $p_filelist;
            elseif (is_string($p_filelist))
                $v_list = explode(" ", $p_filelist);
            else {
                $this->_error("Invalid file list");
                return false;
            }

            $v_result = $this->_append($v_list, $p_add_dir, $p_remove_dir);
        }

        return $v_result;
    }
    // }}}

    // {{{ extractModify()
    /**
    * This method extract all the content of the archive in the directory
    * indicated by $p_path. When relevant the memorized path of the
    * files/dir can be modified by removing the $p_remove_path path at the
    * beginning of the file/dir path.
    * While extracting a file, if the directory path does not exists it is
    * created.
    * While extracting a file, if the file already exists it is replaced
    * without looking for last modification date.
    * While extracting a file, if the file already exists and is write
    * protected, the extraction is aborted.
    * While extracting a file, if a directory with the same name already
    * exists, the extraction is aborted.
    * While extracting a directory, if a file with the same name already
    * exists, the extraction is aborted.
    * While extracting a file/directory if the destination directory exist
    * and is write protected, or does not exist but can not be created,
    * the extraction is aborted.
    * If after extraction an extracted file does not show the correct
    * stored file size, the extraction is aborted.
    * When the extraction is aborted, a PEAR error text is set and false
    * is returned. However the result can be a partial extraction that may
    * need to be manually cleaned.
    *
    * @param string $p_path         The path of the directory where the files/dir need to by
    *                               extracted.
    * @param string $p_remove_path  Part of the memorized path that can be removed if
    *                               present at the beginning of the file/dir path.
    * @return boolean               true on success, false on error.
    * @access public
    * @see extractList()
    */
    function extractModify($p_path, $p_remove_path)
    {
        $v_result = true;
        $v_list_detail = array();

        if ($v_result = $this->_openRead()) {
            $v_result = $this->_extractList($p_path, $v_list_detail, "complete", 0, $p_remove_path);
            $this->_close();
        }

        return $v_result;
    }
    // }}}

    // {{{ extractList()
    /**
    * This method extract from the archive only the files indicated in the
    * $p_filelist. These files are extracted in the current directory or
    * in the directory indicated by the optional $p_path parameter.
    * If indicated the $p_remove_path can be used in the same way as it is
    * used in extractModify() method.
    * @param array  $p_filelist     An array of filenames and directory names, or a single
    *                               string with names separated by a single blank space.
    * @param string $p_path         The path of the directory where the files/dir need to by
    *                               extracted.
    * @param string $p_remove_path  Part of the memorized path that can be removed if
    *                               present at the beginning of the file/dir path.
    * @return                       true on success, false on error.
    * @access public
    * @see extractModify()
    */
    function extractList($p_filelist, $p_path="", $p_remove_path="")
    {
        $v_result = true;
        $v_list_detail = array();

        if (is_array($p_filelist))
            $v_list = $p_filelist;
        elseif (is_string($p_filelist))
            $v_list = explode(" ", $p_filelist);
        else {
            $this->_error("Invalid string list");
            return false;
        }

        if ($v_result = $this->_openRead()) {
            $v_result = $this->_extractList($p_path, $v_list_detail, "complete", $v_list, $p_remove_path);
            $this->_close();
        }

        return $v_result;
    }
    // }}}

    // {{{ _error()
    function _error($p_message)
    {
        // ----- To be completed
        $this->raiseError($p_message);
    }
    // }}}

    // {{{ _warning()
    function _warning($p_message)
    {
        // ----- To be completed
        $this->raiseError($p_message);
    }
    // }}}

    // {{{ _openWrite()
    function _openWrite()
    {
        if ($this->_compress)
            $this->_file = @gzopen($this->_tarname, "w");
        else
            $this->_file = @fopen($this->_tarname, "w");

        if ($this->_file == 0) {
            $this->_error("Unable to open in write mode '".$this->_tarname."'");
            return false;
        }

        return true;
    }
    // }}}

    // {{{ _openRead()
    function _openRead()
    {
        if ($this->_compress)
            $this->_file = @gzopen($this->_tarname, "rb");
        else
            $this->_file = @fopen($this->_tarname, "rb");

        if ($this->_file == 0) {
            $this->_error("Unable to open in read mode '".$this->_tarname."'");
            return false;
        }

        return true;
    }
    // }}}

    // {{{ _openReadWrite()
    function _openReadWrite()
    {
        if ($this->_compress)
            $this->_file = @gzopen($this->_tarname, "r+b");
        else
            $this->_file = @fopen($this->_tarname, "r+b");

        if ($this->_file == 0) {
            $this->_error("Unable to open in read/write mode '".$this->_tarname."'");
            return false;
        }

        return true;
    }
    // }}}

    // {{{ _close()
    function _close()
    {
        if ($this->_file) {
            if ($this->_compress)
                @gzclose($this->_file);
            else
                @fclose($this->_file);

            $this->_file = 0;
        }

        return true;
    }
    // }}}

    // {{{ _cleanFile()
    function _cleanFile()
    {
        _close();
        @unlink($this->tarname);

        return true;
    }
    // }}}

    // {{{ _writeFooter()
    function _writeFooter()
    {
      if ($this->_file) {
          // ----- Write the last 0 filled block for end of archive
          $v_binary_data = pack("a512", "");
          if ($this->_compress)
            @gzputs($this->_file, $v_binary_data);
          else
            @fputs($this->_file, $v_binary_data);
      }
      return true;
    }
    // }}}

    // {{{ _addList()
    function _addList($p_list, $p_add_dir, $p_remove_dir)
    {
      $v_result=true;
      $v_header = array();

      if (!$this->_file) {
          $this->_error("Invalid file descriptor");
          return false;
      }

      if (sizeof($p_list) == 0)
          return true;

      for ($j=0; ($j<count($p_list)) && ($v_result); $j++) {
        $v_filename = $p_list[$j];

        // ----- Skip the current tar name
        if ($v_filename == $this->_tarname)
            continue;

        if ($v_filename == "")
            continue;

        if (!file_exists($v_filename)) {
            $this->_warning("File '$v_filename' does not exist");
            continue;
        }

        // ----- Add the file or directory header
        if (!$this->_addFile($v_filename, $v_header, $p_add_dir, $p_remove_dir))
            return false;

        if (@is_dir($v_filename)) {
            if (!($p_hdir = opendir($v_filename))) {
                $this->_warning("Directory '$v_filename' can not be read");
                continue;
            }
            $p_hitem = readdir($p_hdir); // '.' directory
            $p_hitem = readdir($p_hdir); // '..' directory
            while ($p_hitem = readdir($p_hdir)) {
                if ($v_filename != ".")
                    $p_temp_list[0] = $v_filename."/".$p_hitem;
                else
                    $p_temp_list[0] = $p_hitem;

                $v_result = $this->_addList($p_temp_list, $p_add_dir, $p_remove_dir);
            }

            unset($p_temp_list);
            unset($p_hdir);
            unset($p_hitem);
        }
      }

      return $v_result;
    }
    // }}}

    // {{{ _addFile()
    function _addFile($p_filename, &$p_header, $p_add_dir, $p_remove_dir)
    {
      if (!$this->_file) {
          $this->_error("Invalid file descriptor");
          return false;
      }

      if ($p_filename == "") {
          $this->_error("Invalid file name");
          return false;
      }

      // ----- Calculate the stored filename
      $v_stored_filename = $p_filename;
      if ($p_remove_dir != "") {
          if (substr($p_remove_dir, -1) != '/')
              $p_remove_dir .= "/";

          if (substr($p_filename, 0, strlen($p_remove_dir)) == $p_remove_dir)
              $v_stored_filename = substr($p_filename, strlen($p_remove_dir));
      }
      if ($p_add_dir != "") {
          if (substr($p_add_dir, -1) == "/")
              $v_stored_filename = $p_add_dir.$v_stored_filename;
          else
              $v_stored_filename = $p_add_dir."/".$v_stored_filename;
      }

      if (strlen($v_stored_filename) > 99) {
          $this->_warning("Stored file name is too long (max. 99) : '$v_stored_filename'");
          fclose($v_file);
          return true;
      }

      if (is_file($p_filename)) {
          if (($v_file = @fopen($p_filename, "rb")) == 0) {
              $this->_warning("Unable to open file '$p_filename' in binary read mode");
              return true;
          }

          if (!$this->_writeHeader($p_filename, $v_stored_filename))
              return false;

          while (($v_buffer = fread($v_file, 512)) != "") {
              $v_binary_data = pack("a512", "$v_buffer");
              if ($this->_compress)
                  @gzputs($this->_file, $v_binary_data);
              else
                  @fputs($this->_file, $v_binary_data);
          }

          fclose($v_file);

      } else {
          // ----- Only header for dir
          if (!$this->_writeHeader($p_filename, $v_stored_filename))
              return false;
      }

      return true;
    }
    // }}}

    // {{{ _writeHeader()
    function _writeHeader($p_filename, $p_stored_filename)
    {
        if ($p_stored_filename == "")
            $p_stored_filename = $p_filename;
        $v_reduce_filename = $this->_pathReduction($p_stored_filename);

        $v_info = stat($p_filename);
        $v_uid = sprintf("%6s ", DecOct($v_info[4]));
        $v_gid = sprintf("%6s ", DecOct($v_info[5]));
        $v_perms = sprintf("%6s ", DecOct(fileperms($p_filename)));

        clearstatcache();
        $v_size = sprintf("%11s ", DecOct(filesize($p_filename)));

        $v_mtime = sprintf("%11s", DecOct(filemtime($p_filename)));

        if (@is_dir($p_filename))
          $v_typeflag = "5";
        else
          $v_typeflag = "";

        $v_linkname = "";

        $v_magic = "";

        $v_version = "";

        $v_uname = "";

        $v_gname = "";

        $v_devmajor = "";

        $v_devminor = "";

        $v_prefix = "";

        $v_binary_data_first = pack("a100a8a8a8a12A12", $v_reduce_filename, $v_perms, $v_uid, $v_gid, $v_size, $v_mtime);
        $v_binary_data_last = pack("a1a100a6a2a32a32a8a8a155a12", $v_typeflag, $v_linkname, $v_magic, $v_version, $v_uname, $v_gname, $v_devmajor, $v_devminor, $v_prefix, "");

        // ----- Calculate the checksum
        $v_checksum = 0;
        // ..... First part of the header
        for ($i=0; $i<148; $i++)
            $v_checksum += ord(substr($v_binary_data_first,$i,1));
        // ..... Ignore the checksum value and replace it by ' ' (space)
        for ($i=148; $i<156; $i++)
            $v_checksum += ord(' ');
        // ..... Last part of the header
        for ($i=156, $j=0; $i<512; $i++, $j++)
            $v_checksum += ord(substr($v_binary_data_last,$j,1));

        // ----- Write the first 148 bytes of the header in the archive
        if ($this->_compress)
            @gzputs($this->_file, $v_binary_data_first, 148);
        else
            @fputs($this->_file, $v_binary_data_first, 148);

        // ----- Write the calculated checksum
        $v_checksum = sprintf("%6s ", DecOct($v_checksum));
        $v_binary_data = pack("a8", $v_checksum);
        if ($this->_compress)
          @gzputs($this->_file, $v_binary_data, 8);
        else
          @fputs($this->_file, $v_binary_data, 8);

        // ----- Write the last 356 bytes of the header in the archive
        if ($this->_compress)
            @gzputs($this->_file, $v_binary_data_last, 356);
        else
            @fputs($this->_file, $v_binary_data_last, 356);

        return true;
    }
    // }}}

    // {{{ _readHeader()
    function _readHeader($v_binary_data, &$v_header)
    {
        if (strlen($v_binary_data)==0) {
            $v_header[filename] = "";
            return true;
        }

        if (strlen($v_binary_data) != 512) {
            $v_header[filename] = "";
            $this->_error("Invalid block size : ".strlen($v_binary_data));
            return false;
        }

        // ----- Calculate the checksum
        $v_checksum = 0;
        // ..... First part of the header
        for ($i=0; $i<148; $i++)
            $v_checksum+=ord(substr($v_binary_data,$i,1));
        // ..... Ignore the checksum value and replace it by ' ' (space)
        for ($i=148; $i<156; $i++)
            $v_checksum += ord(' ');
        // ..... Last part of the header
        for ($i=156; $i<512; $i++)
           $v_checksum+=ord(substr($v_binary_data,$i,1));

        $v_data = unpack("a100filename/a8mode/a8uid/a8gid/a12size/a12mtime/a8checksum/a1typeflag/a100link/a6magic/a2version/a32uname/a32gname/a8devmajor/a8devminor", $v_binary_data);

        // ----- Extract the checksum
        $v_header[checksum] = OctDec(trim($v_data[checksum]));
        if ($v_header[checksum] != $v_checksum) {
            $v_header[filename] = "";

            // ----- Look for last block (empty block)
            if (($v_checksum == 256) && ($v_header[checksum] == 0))
                return true;

            $this->_error("Invalid checksum : $v_checksum calculated, ".$v_header[checksum]." expected");
            return false;
        }

        // ----- Extract the properties
        $v_header[filename] = trim($v_data[filename]);
        $v_header[mode] = OctDec(trim($v_data[mode]));
        $v_header[uid] = OctDec(trim($v_data[uid]));
        $v_header[gid] = OctDec(trim($v_data[gid]));
        $v_header[size] = OctDec(trim($v_data[size]));
        $v_header[mtime] = OctDec(trim($v_data[mtime]));
        $v_header[typeflag] = $v_data[typeflag];
        /* ----- All these fields are removed form the header because they do not carry interesting info
        $v_header[link] = trim($v_data[link]);
        $v_header[magic] = trim($v_data[magic]);
        $v_header[version] = trim($v_data[version]);
        $v_header[uname] = trim($v_data[uname]);
        $v_header[gname] = trim($v_data[gname]);
        $v_header[devmajor] = trim($v_data[devmajor]);
        $v_header[devminor] = trim($v_data[devminor]);
        */

        return true;
    }
    // }}}

    // {{{ _extractList()
    function _extractList($p_path, &$p_list_detail, $p_mode, $p_file_list, $p_remove_path)
    {
    $v_result=true;
    $v_nb = 0;
    $v_extract_all = true;
    $v_listing = false;        

    if ($p_path == "" || (substr($p_path, 0, 1) != "/" && substr($p_path, 0, 3) != "../" && substr($p_path, 1, 3) != ":\\")) {
      $p_path = "./".$p_path;
    }

    // ----- Look for path to remove format (should end by /)
    if (($p_remove_path != "") && (substr($p_remove_path, -1) != '/'))
		$p_remove_path .= '/';
    $p_remove_path_size = strlen($p_remove_path);
    switch ($p_mode) 
	{
      case "complete" :
        $v_extract_all = TRUE;
        $v_listing = FALSE;
      break;
      case "partial" :
          $v_extract_all = FALSE;
          $v_listing = FALSE;
      break;
      case "list" :
          $v_extract_all = FALSE;
          $v_listing = TRUE;
      break;
      default :
        $this->_error("Invalid extract mode ($p_mode)");
        return false;
    }
    clearstatcache();
    While (!($v_end_of_file = ($this->_compress?@gzeof($this->_file):@feof($this->_file))))
	{
		$v_extract_file = FALSE;
		$v_extraction_stopped = 0;

		if ($this->_compress)
			$v_binary_data = @gzread($this->_file, 512);
		else
			$v_binary_data = @fread($this->_file, 512);

		if (!$this->_readHeader($v_binary_data, $v_header))
			return false;

		if ($v_header[filename] == "")
			continue;

		if ((!$v_extract_all) && (is_array($p_file_list))) 
		{
			// ----- By default no unzip if the file is not found
			$v_extract_file = false;

			for ($i=0; $i<sizeof($p_file_list); $i++) 
			{
				// ----- Look if it is a directory
				if (substr($p_file_list[$i], -1) == "/") 
				{
					// ----- Look if the directory is in the filename path
					if ((strlen($v_header[filename]) > strlen($p_file_list[$i])) && (substr($v_header[filename], 0, strlen($p_file_list[$i])) == $p_file_list[$i])) 
					{
						$v_extract_file = TRUE;
						break;
					}
				}
				// ----- It is a file, so compare the file names
				elseif ($p_file_list[$i] == $v_header[filename]) 
				{
					$v_extract_file = TRUE;
					break;
				}
			}
		} 
		else 
		{
			$v_extract_file = TRUE;
		}

		// ----- Look if this file need to be extracted
		if (($v_extract_file) && (!$v_listing))
		{              
			if (($p_remove_path != "")
				&& (substr($v_header[filename], 0, $p_remove_path_size) == $p_remove_path))
				$v_header[filename] = substr($v_header[filename], $p_remove_path_size);
			if (($p_path != "./") && ($p_path != "/")) {
				while (substr($p_path, -1) == "/")
				$p_path = substr($p_path, 0, strlen($p_path)-1);

			if (substr($v_header[filename], 0, 1) == "/")
				$v_header[filename] = $p_path.$v_header[filename];
			else
				$v_header[filename] = $p_path."/".$v_header[filename];
        }

        if (file_exists($v_header[filename])) 
		{
			if ((@is_dir($v_header[filename])) && ($v_header[typeflag] == "")) 
			{
				$this->_error("File $v_header[filename] already exists as a directory");
				return false;
			}
			if ((is_file($v_header[filename])) && ($v_header[typeflag] == "5")) 
			{
				$this->_error("Directory $v_header[filename] already exists as a file");
				return false;
			}
			if (!is_writeable($v_header[filename])) 
			{
				//$this->_error("File $v_header[filename] already exists and is write protected");
				$this->_error("파일 $v_header[filename] 이미 존재하며 쓰기 권한이 없어 덮어쓰기가 불가능합니다.");
				return false;
			}
			
			if (filemtime($v_header[filename]) > $v_header[mtime]) 
			{
				// To be completed : An error or silent no replace ?
				// I want silent replace
			}

			// 존재하는 파일을 제거하로록 한다.
			// added by lovjesus 2002/11/10
			if(is_file($v_header[filename]))
			{
				@unlink($v_header[filename]) ;
			}
        }
        // ----- Check the directory availability and create it if necessary
        elseif (($v_result = $this->_dirCheck(($v_header[typeflag] == "5"?$v_header[filename]:dirname($v_header[filename])))) != 1) 
		{
			$this->_error("Unable to create path for $v_header[filename]");
			return false;
        }

        if ($v_extract_file) 
		{
			if ($v_header[typeflag] == "5") 
			{
				if (!@file_exists($v_header[filename])) 
				{
					if (!@mkdir($v_header[filename], 0777)) 
					{
						$this->_error("Unable to create directory $v_header[filename]");
						return false;
					}
				}
			} 
			else 
			{
				if (($v_dest_file = @fopen($v_header[filename], "wb")) == 0) 
				{
					$this->_error("Error while opening $v_header[filename] in write binary mode");
					return false;
				} 
				else 
				{
					$n = floor($v_header[size]/512);
					for ($i=0; $i<$n; $i++) 
					{
						if ($this->_compress)
							$v_content = @gzread($this->_file, 512);
						else
							$v_content = @fread($this->_file, 512);
						fwrite($v_dest_file, $v_content, 512);
					}
					if (($v_header[size] % 512) != 0) 
					{
						if ($this->_compress)
							$v_content = @gzread($this->_file, 512);
						else
							$v_content = @fread($this->_file, 512);
						fwrite($v_dest_file, $v_content, ($v_header[size] % 512));
					}
					@fclose($v_dest_file);

					// ----- Change the file mode, mtime
					@touch($v_header[filename], $v_header[mtime]);
					// To be completed
					//chmod($v_header[filename], DecOct($v_header[mode]));
				}

				// added by lovjesus 2002/11/07	
				clearstatcache() ;
				// ----- Check the file size
				if ( file_exists($v_header[filename]) && (filesize($v_header[filename]) != $v_header[size]) ) 
				{
					$this->_error("Extracted file $v_header[filename] does not have the correct file size '".filesize($v_header[filename])."' ($v_header[size] expected). Archive may be corrupted.");
					return false;
				}
			}
        }
		else 
		{
          // ----- Jump to next file
          if ($this->_compress)
              @gzseek($this->_file, @gztell($this->_file)+(ceil(($v_header[size]/512))*512));
          else
              @fseek($this->_file, @ftell($this->_file)+(ceil(($v_header[size]/512))*512));
		}
	} 
	else 
	{
		// ----- Jump to next file
        if ($this->_compress)
          @gzseek($this->_file, @gztell($this->_file)+(ceil(($v_header[size]/512))*512));
        else
          @fseek($this->_file, @ftell($this->_file)+(ceil(($v_header[size]/512))*512));
	}

	if ($this->_compress)
        $v_end_of_file = @gzeof($this->_file);
	else
        $v_end_of_file = @feof($this->_file);

      if ($v_listing || $v_extract_file || $v_extraction_stopped) {
        // ----- Log extracted files
        if (($v_file_dir = dirname($v_header[filename])) == $v_header[filename])
          $v_file_dir = "";
        if ((substr($v_header[filename], 0, 1) == "/") && ($v_file_dir == ""))
          $v_file_dir = "/";

        $p_list_detail[$v_nb++] = $v_header;
      }
    }

        return true;
    }
    // }}}

    // {{{ _append()
    function _append($p_filelist, $p_add_dir="", $p_remove_dir="")
    {
        if ($this->_compress) {
            $this->_close();

            if (!@rename($this->_tarname, $this->_tarname.".tmp")) {
                $this->_error("Error while renaming '".$this->_tarname."' to temporary file '".$this->_tarname.".tmp'");
                return false;
            }

            if (($v_temp_tar = @gzopen($this->_tarname.".tmp", "rb")) == 0) {
                $this->_error("Unable to open file '".$this->_tarname.".tmp' in binary read mode");
                @rename($this->_tarname.".tmp", $this->_tarname);
                return false;
            }

            if (!$this->_openWrite()) {
                @rename($this->_tarname.".tmp", $this->_tarname);
                return false;
            }

            $v_buffer = @gzread($v_temp_tar, 512);

            // ----- Read the following blocks but not the last one
            if (!@gzeof($v_temp_tar)) {
                do{
                    $v_binary_data = pack("a512", "$v_buffer");
                    @gzputs($this->_file, $v_binary_data);
                    $v_buffer = @gzread($v_temp_tar, 512);

                } while (!@gzeof($v_temp_tar));
            }

            if ($this->_addList($p_filelist, $p_add_dir, $p_remove_dir))
                $this->_writeFooter();

            $this->_close();
            @gzclose($v_temp_tar);

            if (!@unlink($this->_tarname.".tmp")) {
                $this->_error("Error while deleting temporary file '".$this->_tarname.".tmp'");
            }

            return true;
        }

        // ----- For not compressed tar, just add files before the last 512 bytes block
        if (!$this->_openReadWrite())
           return false;

        $v_size = filesize($this->_tarname);
        fseek($this->_file, $v_size-512);

        if ($this->_addList($p_filelist, $p_add_dir, $p_remove_dir))
           $this->_writeFooter();

        $this->_close();

        return true;
    }
    // }}}

    // {{{ _dirCheck()
    function _dirCheck($p_dir)
    {
        if ((@is_dir($p_dir)) || ($p_dir == ""))
            return true;

        $p_parent_dir = dirname($p_dir);

        if (($p_parent_dir != $p_dir) &&
            ($p_parent_dir != "") &&
            (!$this->_dirCheck($p_parent_dir)))
             return false;

        if (!@mkdir($p_dir, 0777)) {
            $this->_error("Unable to create directory '$p_dir'");
            return false;
        }

        return true;
    }
    // }}}

    // {{{ _pathReduction()
    function _pathReduction($p_dir)
    {
        $v_result = "";

        // ----- Look for not empty path
        if ($p_dir != "") {
            // ----- Explode path by directory names
            $v_list = explode("/", $p_dir);

            // ----- Study directories from last to first
            for ($i=sizeof($v_list)-1; $i>=0; $i--) {
                // ----- Look for current path
            }
        }
        return $v_result;
    }
    // }}}

}


?>
