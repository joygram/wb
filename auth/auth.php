<?php
if(!defined("__wb_auth__")) define("__wb_auth__","1") ;
else return ;

ob_start() ;

/**
@todo 프로그램을 include하는 경로에 따라서 폼과 메시지를 가져오는
상대 위치가 변하기 때문에 이 상대 위치를 정해줄 수 있어야 한다.
@todo 전역변수여야 세션에 등록이 된다. 
*/

        // default uid definition
define("__ROOT", "1") ;
define("__ANONYMOUS", "0") ;
define("__WHEEL", "1") ;

        // run mode definition
define("READ_MODE",  4) ;
define("WRITE_MODE", 2) ;
define("EXEC_MODE",  1) ;

$_debug = 0 ;

////////////////////////////////////////////////////////////////////////////
//세션설정 초기화

//trans_sid는 많은 서버에서 세션을 공유하고자 할때 사용함.
//
///PHPSESSID값이 따라 다닐 경우 제거하는 방법.
//ini_set("session.auto_start", false) ;
//ini_set("session.use_trans_sid", false) ;

//ini_set('session.use_cookies', 1);
//ini_set('session.use_only_cookies', 1);

//echo ("uid:[".$HTTP_SESSION_VARS["W_SES"]["uid"]."]") ;
////////////////////////////////////////////////////////////////////////////
/// 파라메터로 들어온 것을 URL로 변경 시켜줌.
function param2url($param)
{
	//$param = base64_decode($param) ;
	$param_array = unserialize(stripslashes($param)) ;
	
	$url_param = "" ;
	while (list($key,$value)=@each($param_array))
	{
		$url_param .= "$key=$value&" ;
	}
	
	return $url_param ;
}

////////////////////////////////////////////////////////////////////////////
/// 인증 처리 클래스
class auth
{
	var $debug ;
	
	var $globals ;        ///< 디폴트 서버 전역변수 설정 문자열.
	
	var $mode ; //auth 모드
	var $db_type ;
	var $auth_data ; // auth parameter, user, passwd, url
	
	var $msg ;  //정보 메시지
	var $error_html ; // 오류 메시지 파일
	var $login_html ;
	
	// check parameter
	var $setuid ;
	var $check_owner ;
	var $check_group ;
	var $check_passwd ;
	var $check_data ;
	var $no_perm_action ;
	
	//permition
	//var $other_perm ;
	var $all_perm ; //
	var $run_mode ; // read, write, execute
	var $run_mode_perm ; // 운영모드에 실제 적용되는 권한.
	
      // base information ;
	var $uid ;
	var $gid ;
	var $user ;
	var $group ;
	
	var $alias ; //별명
	var $email ; //전자우편
	var $homepage ; //전자우편

	// other information ;
	var $member_info ;

	////////////////////////////////////////////////////////////////////////////
	///생성자.
	function auth($mode ="check", $auth_data = "", $db_type = "old_type", $error_html = "", $run_mode = READ_MODE, $globals = "" )
	{
		$this->debug = 0 ;
		
		// eval에서 바로 사용할 수 있도록 최종 명령을 만들어 둔다. 
		$this->globals = "global $globals ;" ;
		
		if( $this->debug ) { echo("this->globals[$this->globals]") ; }
		
		$this->error_html = $error_html ;
		$this->msg = "" ;
		$this->db_type = empty($db_type)?"old_type":$db_type;
		$this->auth_data = $auth_data ;
		
		if(empty($mode))
		{
			$this->mode = "check" ;
		}
		else
		{
			$this->mode = $mode ;
		}
		
		$this->run_mode = $run_mode ;
		$this->all_perm = "7000" ;
		
		//전역 서버 변수 사용 설정.
		eval( $this->globals ) ;
		
		if($this->debug) echo("auth: db_type[{$this->db_type}]<br>auth_mode::[$mode]<br> auth_data[user]::[$auth_data[user]]<br>auth_data[passwd]::[$auth_data[passwd]]<br> auth_data[param]::[$auth_data[param]]<br>run_mode::[$run_mode]<br>") ;
	}
	
	////////////////////////////////////////////////////////////////////////////
	/**
	인증모드 지정 : 기본값은 check임 
	anonymous_mode를 사용하고 싶을때 : 비밀글에서 사용하려고 추가
	*/
	function auth_mode( $mode )
	{
		$this->mode = $mode ;
	}

	////////////////////////////////////////////////////////////////////////////
	/**
      set or get run_mode
      if param $run_mode is empty just return current run_mode
      else set run_mode and return old_run_mode
      @param $run_mode
      @return old or current run_mode
	*/
	function run_mode($run_mode)
	{
		$old_run_mode ;
		if(empty($run_mode))
		{
	    if($this->debug) echo ("get runmode [$this->run_mode] <br>") ;
	    return $this->run_mode ;
		}
		else
		{
			$old_run_mode = $this->run_mode ;
			$this->run_mode = $run_mode ;
			if($this->debug) echo ("run_mode change from [$old_run_mode] to [$run_mode]<br>") ;
			return $old_run_mode ;
		}
	}

	////////////////////////////////////////////////////////////////////////////
	/** 로긴 폼 보여주기 */
	function login_form( $anonymous = "" ) //for test
	{
		$Row_auth[url]       = $GLOBALS[PHP_SELF] ;
		$Row_auth[auth_mode] = $this->mode ;
		$Row_auth[param]     = $this->auth_data[param] ;
		$Row_auth[msg]       = $this->msg ;
		$Row_auth[base_url]  = $this->auth_data[base_url] ;
		$Row_auth[try]       = $this->auth_data[try] ;
		$Row_auth[try]       = (empty($Row_auth[try]))?1:$Row_auth[try]+1 ;
		$Row_auth[back_no]   = - $Row_auth[try] ;

		if($this->debug) echo(__FUNCTION__."> [mode:{$this->mode}]<br>") ;
		if($this->debug) echo(__FUNCTION__."> anonymous[$anonymous]<br>") ;

		if( $anonymous )
		{
			if($this->debug) echo(__FUNCTION__.":comment id<br>") ;
			$hide_auth['id'] = "<!--\n" ;
			$hide_auth['/id'] = "-->\n" ;
		}
		else
		{
			$hide_auth['id'] = "" ;
			$hide_auth['/id'] = "" ;
		}
		$base_dir = $this->auth_data[base_dir] ;
		include("$base_dir/system.ini.php") ;

		$Row_auth[theme_url] = $Row_auth[base_url]."/theme/$C_theme" ;

		include("${base_dir}/theme/$C_theme/login.html") ;
		exit ;
	}
	

	/**
	/// 기존 방식으로 비번 검사.
	@author apollo@whitebbs.net 2005/08/02(화) 
	*/	
	function check_oldtype()
	{
		$base_dir = $this->auth_data[base_dir] ;
		include("${base_dir}/member/admin.php") ;

		if($this->debug) echo("check:auth_user[".$this->auth_data[user]."],passwd[".$this->auth_data[passwd]."]admin_id[$C_admin_id]C_admin_password[$C_admin_password]<br>") ;

		$result = false ;
		if( $this->auth_data[user] == $C_admin_id && $this->auth_data[passwd] == $C_admin_password )
		{
			$this->uid = __ROOT ;
			$this->gid = __WHEEL ;

			$this->user = $C_admin_id ;
			$this->alias = ($C_admin_alias)?$C_admin_alias:"관리자" ;
			$this->email = $C_admin_email ;
			$this->homepage = $C_admin_homepage ;
			$this->group = "wheel" ;

			$result = true ;
		}

		return $result ;
	}

	/**
	/// postgresql로 인증 검사
	@author apollo@whitebbs.net 2005/08/02(화) 
	*/
	function check_postgresql()
	{
		if($this->debug) echo("check:db_type[$this->db_type]<br>") ;

		$dbi = new db_member($data, "member", $mode = "", "", $this->auth_data[user], "uname", $this->db_type, "", $this->auth_data[base_dir]) ;
		$dbi->select_data() ;
		$one_row = $dbi->row_fetch_array(0,"","","member") ;

		if($this->debug) print_r($one_row)."<br>" ;

		$dbi->destroy() ;

		if( empty($one_row) )
		{
			return false ;
		} 

		//$password = wb_decrypt($one_row[password], $one_row[uname]) ;
		$password = $one_$Row['password'] ;

		if($this->debug) echo("check:PASSWORD[".$this->auth_data[passwd]."],password[$password]<br>") ;

		$result = false ;
		if($this->auth_data[passwd] == $password)
		{
			if($this->debug) echo("check:password correct<br>") ;
			$this->uid   = $one_$Row['uid'] ;
			$this->user  = $one_$Row['uname'] ;
			$this->gid   = $one_$Row['gid'] ;
			$this->alias = $one_$Row['alias'] ;
			$this->email = $one_$Row['email'] ;
			$this->homepage = $one_$Row['homepage'] ;
			//$this->group = search_group($this->gid) ;

			//공개여부를 확인한 후 불필요한 데이터는 삭제필요
			$one_$Row['password'] = "" ;
			$one_row[3] = "" ;

			$this->member_info = $one_row ;
			if($this->debug) print_r($one_row) ;

			$result = true ;
		}

		return $result ;
	}


	////////////////////////////////////////////////////////////////////////////
	// SZmember 와의 연동 by 체리토마토 2003.1.1
	function check_szmember()
	{
		include("$base_dir/lib/db_member_sz.php") ;

		if($this->debug) echo(__FUNCTION__.":db_type[$this->db_type]<br>") ;

		if($this->debug) echo(__FUNCTION__.":auth_user[".$this->auth_data[user]."],passwd[".$this->auth_data[passwd]."]<br>") ;

		$sz_user_db = "${base_dir}/SZmember/db/".$this->auth_data[user].".dbf.cgi";
		$one_row = select_data($this->auth_data[user],$this->auth_data[passwd],$sz_user_db) ;
		if($this->debug) echo ("$one_row[0]<br>");

		if(empty($one_row) )
		{
			return false ;
		}

		if($one_row[5]==0) err_SZ("아직 승급이 이루어지지 않았습니다");  //SZ 에서 가입시 권한을 guest 로 설정한 경우

		$one_row[1] = chop ($one_row[1]);
		$crypt_password=crypt($this->auth_data[passwd],$one_row[1]);

		if(!($one_row[1] == $crypt_password))
		{
			err_SZ("id 와 password 를 확인하세요");// password 가 틀림, password만 틀렸음을 보여줄 경우 문제가 있을 수 있음
			return false ;
		}

		if( $one_row[5] == 100)  //SZ 에서 100이 관리자
		{
			$this->uid = __ROOT ;
			$this->gid = __WHEEL ;
			$this->user = $one_row[0];
			$this->alias = $one_row[2] ;
			$this->email = $one_row[3] ;
			$this->homepage =$one_row[4] ;
			$this->group = "wheel" ;
			$this->signature = $one_row[12];
		}
		else  // 관리자가 아니면 모두 멤버로 처리 (멤버의 종류를 나눌 필요가 있음 member1, member2)
		{
			$this->uid = $one_row[0] ;
			$this->gid ="2" ;
			$this->user = $one_row[0];
			$this->alias = $one_row[2] ;
			$this->email = $one_row[3] ;
			$this->homepage =$one_row[4] ;
			$this->group = "wheel" ;
			$this->signature = $one_row[12];
		}
		// SZmember 를 사용하기 위해 쿠키를 만들고 로그인 기록을 저장하는 부분
		session_reg_SZ($this->auth_data[user],$this->auth_data[passwd],$base_dir,$crypt_password);
		member_log_SZ($one_row[0],$base_dir);


		return true ;
	}


	////////////////////////////////////////////////////////////////////////////
	/**
	@param auth_data: 외부에서 받은 인증DB정보, ex:암호
  	auth_info
          uid : user id num
          gid : group id num
          user : user id string
          group : group id string

          super user uid = 1 ;
          super user gid = 1 ;
	*/
	function check()
	{
		if($this->debug) echo(__FUNCTION__."<br>") ;

		$base_dir = $this->auth_data[base_dir] ;
		include("$base_dir/system.ini.php") ;
	
		if($this->debug) echo("[$base_dir]C_db_type[$C_db_type]<br>") ;
	
		// DB타입을 여러개를 사용하도록 하는 것이 정상인지 결정하지 못했음. 2003/05/20
		if(!empty($C_member_db_type))
		{
			$this->db_type = $C_member_db_type ;
		}

    	if($this->debug) echo("check:db_type[$this->db_type]<br>") ;

		if($this->db_type == "old_type" || empty($this->db_type))
		{
			$result = $this->check_oldtype() ;
		}
		else if($this->db_type == "postgresql")
    	{
			$result = $this->check_postgres() ;
    	}
		else if($this->db_type == "szmember")
		{
			$result = $this->check_szmember() ;
		}
	
		return $result ;
	}
		
		
    ///////////////////////////////////////////////////////////////////////////
    /// 세션을 만들어줌.
    function make_session()
    {
    	//전역 서버 변수 사용 설정.
    	eval( $this->globals ) ;

    	//global $W_SES ;

    	if($this->debug) echo("make_session<br>") ;
    	//1. uid, passwd check
    	if( $this->check() == false )
    	{
    		if($this->debug) echo("make_session:CHECK FAILURE") ;
    		$this->mode = "auth" ;
    		$this->msg = "CHECK FAILURE, TRY AGAIN" ;
    		$this->login_form() ;
    	}
    	else
    	{
    		// 전역변수만 등록이 됨
    		$W_SES["uid"] = $this->uid ;
    		$W_SES["gid"] = $this->gid ;

    		$W_SES["user"] = $this->user ;
    		$W_SES["alias"] = $this->alias ;
    		$W_SES["group"] = $this->group ;

    		        // other information
    		$W_SES["email"]    = $this->email ;
    		$W_SES["homepage"] = $this->homepage ;

    		$W_SES["member_info"] = $this->member_info ;

    		if($this->debug) 
			{ 
				echo("W_SES: BEFORE SESSION REGISTER:[");
				print_r( $W_SES ) ; 
				echo("]") ; 
			}

    		$this->session_set() ;

    		if($this->debug) 
			{ 
				echo(__FUNCTION__."> W_SES: SESSION REGISTER COMPLETE:["); 
				print_r( $W_SES ) ; 
				echo("]") ; 
			}
    	} //if( $this->check() == false )
    	return ;
    }


	///////////////////////////////////////////////////////////////////////////
	function search_group()
	{
		// find gid in the group database ;
		// if gid in the group database return true.
		// 그룹 DB에 있으면 true return, else false

		// $this->check_group안에 $W_SES[gid]가 존재하는지 검사

		return false ;
	}

	///////////////////////////////////////////////////////////////////////////
	/**
			//무명씨인경우 auth_data로 들어온 비밀번호 검사후 결과리턴
	*/
	function anonymous_auth()
	{
		if($this->debug) echo("anonymous_auth:auth_data[passwd][".$this->auth_data[passwd]."], check_data[passwd][".$this->check_data[passwd]."]<br>") ;
				//이곳에 암호화를 해제하는 내용이 들어갈 수 있다.
				//2002/03/17 빈암호는 검사하지 않는다.
		if( !empty($this->check_data[passwd]) &&
				$this->auth_data[passwd] == $this->check_data[passwd])
		{
			if($this->debug) echo("ANONYMOUS CHECK SUCESS<br>") ;
			$result = true ;
		}
		else
		{
    		if($this->debug) echo("anonymous CHECK FAILURE<br>") ;
    		$this->mode = "auth_anonymous" ;
    		$this->msg = "CHECK FAILURE, TRY AGAIN" ;
    		$this->login_form("anonymous") ;
    		$result = false ;
		}

		return $result;
	}


    /**
    anonymous_auth와 make_session은 성공한 경우 리턴을 하며
    그렇지 않은 경우 로그인 폼으로 redirect한다.
    */
    function perm_auth()
    {
        if($this->debug) echo("perm_auth:auth_mode[".$this->mode."]<br>") ;
        if( $this->mode == "auth_anonymous" )
        {
            $status = $this->anonymous_auth() ;
        }
        else
        {
            $status = $this->make_session() ;

             // 세션을 유효화 시키려면 리다이렉트 시켜야 한다.
             // 아니었으면 좋겠다.
            $url_param = param2url(base64_decode($this->auth_data[param])) ;
            $redirect_url = $GLOBALS[PHP_SELF]."?".$url_param ;

			$redirect_time = 0 ;	

            if($this->debug) 
			{
				echo ("perm_auth:redirect_url[$redirect_url]<br>") ;
				$redirect_time = 10 ;
			}

            echo("<META HTTP-EQUIV=REFRESH CONTENT=\"$redirect_time; URL='$redirect_url'\"> ") ;
            exit ;
        }

    }

    /**
    */
    function perm_check_session() 
    {
        eval( $this->globals ) ;
		
        if($this->debug) echo(__LINE__.":W_SES[uid]:[$W_SES[uid]], W_SES[gid]:[$W_SES[gid]]<br>") ;

        if( $this->perm_have("root") ) return true ;
        if( $this->perm_have("user") ) return true ;
        if( $this->perm_have("group") ) return true ;
        if( $this->perm_have("other") ) return true ;

        if($this->debug) echo("no other perm have<br>") ;
        if($this->debug) echo("sorry you don't have perm<br>") ;
        return false ;
    }
    
    /**
    */
    function perm_check_anonymous() 
    {
		if($this->debug) echo("perm_check:<b>ANONYMOUS AUTH APPLY</b> run_mode[$this->run_mode]<br>") ;
		// EXEC : if writing ownership have: modify, delete. etc
		if( $this->run_mode == EXEC_MODE )
		{
			if($this->debug) echo("ANONYMOUS AUTH APPLY<br>") ;
			switch($this->no_perm_action)
			{
			case "login" :
			    if($this->debug) echo("anonymous login [mode:{$this->mode}]<br>") ;
			    
			    //비밀글 모드가 아닌 경우만 모드를 지정해준다. 
			    //auth_secret의 경우는 자신의 모드를 유지시켜 주기위해서...
			    
			    if($this->mode != "on_check_secret" ) $this->mode = "auth_anonymous" ;
			    
			    $this->login_form("anonymous") ;
			    break ;
			    
			default:
			    break ;
			}
		}
		return true ;
    }

    /**
    아무런 권한도 없을 경우 
    */
    function no_perm()
    {
		switch($this->no_perm_action)
        {
        case "login" :
			$this->mode = "auth" ;
			$this->login_form() ; // form에서 exit함.
			break ;
                
        case "message" :
                break ;
        default:
                break ;
        }
        
        return false ;
    }
    
    ///////////////////////////////////////////////////////////////////////////
    /**
    // 인증전 권한 검사 CHECK
    // super_user검사
    // 1. 세션이 등록되어 있으면 해당하는지 비교
    // 2. 등록되어 있지 않다면 error_mode에 따라 처리
    // error_mode : message, login
    // 권한 검사가 완료되면 바로 return하므로 주의
            run_mode에 맞는 권한검사
    */
    function perm_check()
    {
	    global $W_SES ;
        //run_mode검사
	    $have_perm = false ;
	    //세션을 가지고 있을때
	    // _anonymous는 고려할 필요가 없다
	    if($this->debug) echo("[$this->mode][$W_SES[uid]][$W_SES[user]]<br>") ;
	    
	    //세션 검사하는 부분 변경 필요.
	    //if( session_is_registered("W_SES") && !empty($W_SES[uid]) )
	    if( isset($W_SES) && !empty($W_SES[uid]) )
	    {
	    	return $this->perm_check_session() ;
	    }
	    else //세션등록이 되어있지 않으면
	    {
	        // err_mode = message, login_form 등으로 처리할 수 있으면 좋겠네...
	        if($this->debug) echo("perm_check: no session exist<br>") ;
	        if( $this->perm_have("anonymous") )
	        {
				return $this->perm_check_anonymous() ;
	        }
	        else
	        {
	        	return $this->no_perm();
	        }
	    } //세션 검사 끝
	}

    ///////////////////////////////////////////////////////////////////////////
    /**
    권한 검사하고자하는 글의 원래 소유자를 정한다.
    기본값은 perm()에서 지정한 사용자가 된다.
	*/
    function setuid($setuid)
    {
		if($this->debug) echo("set owner from[$this->setuid] to[$setuid]") ;
		$this->setuid = $setuid ;
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
	운영모드에 해당하는 권한이 있는지 확인
	@return
    */
    function perm_have( $mode )
    {
        eval( $this->globals ) ;

        $perm = false ;
        if($this->debug) echo ("<b>perm_have:[$mode]</b><br>") ;

        switch($mode)
        {
		case "root":
        	if( $W_SES[uid] == __ROOT ) 
			{
				if( $this->debug ) echo("perm_have:YOU ARE SUPER USER<br>") ;
				return true ;
			}
			break ;

        case "user" :
            $perm = ($this->all_perm & "7000") ;
            if($this->debug) echo("perm_have:--1st pass[$perm]<br>") ;
            $perm = ($perm & $this->run_mode."000") ;
            if($this->debug) echo("perm_have:--2nd pass[$perm]<br>") ;
            if($this->debug) echo("perm_have:[$this->check_owner][$W_SES[user]]<br>") ;
            $perm = $perm && ($this->check_owner == $W_SES[user]) ;
            if($this->debug) echo("perm_have:--3rd pass[$perm]<br>") ;
            break ;

        case "group" :
            $perm = ($this->all_perm & "0700") ;
            if($this->debug) echo("perm_have:--1st pass[$perm]<br>") ;
            $perm = ($perm & "0".$this->run_mode."00") ;
            if($this->debug) echo("perm_have:--2nd pass[$perm]<br>") ;
            $perm = (empty($W_SES[group]))?false:true ;
            if($this->debug) echo("perm_have:--3rd pass[$perm]<br>") ;
            $perm = $perm && ($this->check_group == $W_SES[group]) ;
            if($this->debug) echo("perm_have:--4rd pass[$perm]<br>") ;
            break ;

        case "other" :
            $perm = ($this->all_perm & "0070") ;
            if($this->debug) echo("perm_have:--1st pass[$perm]<br>") ;
            $perm = ($perm & "00".$this->run_mode."0") ;
            if($this->debug) echo("perm_have:--2nd pass[$perm]<br>") ;
                    // 멤버일경우 other의 소유자를 설정해주어 그 권한을 적용시킬 수 있다.
            if($this->debug) echo("perm_have:[$this->setuid]==[$W_SES[user]]<br>") ;
                    // setuid에 대해서 기억이 안남
            //$perm = ($perm && ($this->setuid == $W_SES[user] ) ) ;
                    //글 수정할 때 본인의 글인지 확인하는 과정이 필요함.
            if($this->debug) echo("perm_have:[{$this->check_data[uid]}] == [$W_SES[uid]]<br>") ;
            if(isset($this->check_data[uid]))
            {
                    $perm = ($perm && ($this->check_data[uid] == $W_SES[uid])) ;
            }
            if($this->debug) echo("perm_have:--3rd pass[$perm]<br>") ;
            break ;

		case "anonymous" :
            $perm = ($this->all_perm & "0007") ;
            if($this->debug) echo("perm_have:--1st pass[$perm]<br>") ;
            $perm = ($perm & "000".$this->run_mode) ;
            if($this->debug) echo("perm_have:--2nd pass[$perm]<br>") ;
            break ;

        default:
            break ;
        } // switch($mode)
        
        $perm = ($perm == 0)?false:true ;
        if($this->debug) echo("perm_have::mode[$mode] perm[$perm] run_mode[$this->run_mode]<br>") ;
        return $perm ;
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
    등록되어 있는 사용자의 경우 super_user인지 검사할 수 있어야 한다.
    권한없음 메시지 출력필요?
    auth_data는 anonymous의 경우 변수로 DB가 아닌 외부에서 비밀번호나 인정정보를 받고자 할때 사용한다.
    @param auth_mode
    @param error_mode message:메시지로, login:로그인으로, value:값으로
    */
    function perm($owner, $group = "", $all_perm = "7000", $check_data ="", $no_perm_action = "login")
    {
        eval( $this->globals ) ;
        global $W_SES ;

        if($this->debug) echo("perm:auth_mode[$this->mode] run_mode[$this->run_mode]<br>") ;

         //기본 정의가 들어가지 않으면 모든 사용자 접근으로 권한 설정
        $this->check_owner = $owner ;
        $this->check_group = $group ;
        $this->all_perm = $all_perm ;
        $this->check_data = $check_data ;
        $this->no_perm_action = $no_perm_action ;

        if($this->debug) echo("perm:all_perm[$this->all_perm]<br>") ;
        if($this->debug) echo("check_data:[") ;
        if($this->debug) print_r($this->check_data) ;
        if($this->debug) echo("]<br>") ;
        
        switch( $this->mode )
        {
        case "auth" :
        case "auth_anonymous" :
            $this->perm_auth() ;
            break ;
          
        case "check" :
        case "on_check_secret" :
            if( $this->perm_check() == false )
            {
                //권한이 없음에 대한 오류 표시와 행동이 필요함.
                echo("check권한이 없습니다.<br>") ;
                exit ;
            }
            break ;

		case "check_secret" : //앞부분에서 권한 검사하고 뒤에 다시 검사하도록 
            $this->mode = "on_check_secret" ; 		        
            if( $this->perm_check() == false )
            {
                echo("auth_test권한이 없습니다.<br>") ;
                exit ;
            }
			break ;
            
        default :
            if($this->debug) echo("unknown mode<br>") ;
            break ;
        }
    }

	///////////////////////////////////////////////////////////////////////////
	/**
	        perm()함수 호출 이후에 사용하여야 한다.
	*/
	function is_superuser()
	{
	    global $W_SES ;
	    if( $W_SES[uid] == __ANONYMOUS || empty($W_SES[uid]) )
	    {
            return false ;
	    }
	
	    if( $W_SES[uid] == __ROOT )
	    {
	        return true ;
	    }
	    return false ;
	}

    ///////////////////////////////////////////////////////////////////////////
    /**
            perm()함수 호출 이후에 사용하여야 한다.
    */
    function is_admin()
    {
            global $W_SES ;
            if( $W_SES[uid] == __ANONYMOUS || empty($W_SES[uid]) )
            {
                    return false ;
            }

            if( $W_SES[uid] == __ROOT || $W_SES[user] == $this->owner )
            {
                    return true ;
            }
            return false ;
    }

        function is_group()
        {
                return false ;
        }

    ///////////////////////////////////////////////////////////////////////////
    /**
            perm()함수 호출 이후에 사용하여야 한다.
    */
    function is_member()
    {
            global $W_SES ;

            if( $W_SES[uid] != __ROOT &&
                    $W_SES[user] != $this->owner &&
                    !empty($W_SES[uid]) )
            {
                    return true ;
            }
            return false ;
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
            perm()함수 호출 이후에 사용하여야 한다.
    */
    function is_anonymous()
    {
            global $W_SES ;
            if( empty($W_SES[uid]) || $W_SES[uid] == _ANONYMOUS )
            {
                    return true ;
            }
            return false ;
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
            user_id return
    */
    function uid()
    {
            global $W_SES ;
            return $W_SES['uid'] ;
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
            user_name return
            W_SES를 직접 접근하지 않는 건 어떨까?
    */
    function user()
    {
            global $W_SES ;
            return $W_SES['user'] ;
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
            user alias return
    */
    function alias()
    {
            global $W_SES ;
            return $W_SES['alias'] ;
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
    */
    function name()
    {
            global $W_SES ;
            return $W_SES['member_info']['lastname'].$W_SES['member_info']['firstname'] ;
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
    */
    function email()
    {
            global $W_SES ;
            return $W_SES['email'] ;
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
    */
    function homepage()
    {
            global $W_SES ;
            return $W_SES['homepage'] ;
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
    */
    function member_info()
    {
            global $W_SES ;
            return $W_SES['member_info'] ;
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
    */
    function session_data()
    {
            global $W_SES ;
            return $W_SES ;
    }

	///////////////////////////////////////////////////////////////////////////
	// 세션변수 등록과 해제를 위해 session_resister, session_unregister를 사용하지 않기로함.
	// 각 버젼에 맞도록 세션등록을...
	function session_set()
	{
		eval( $this->globals ) ;		
		global $_SESSION ;

		//여기서 변수 등록이 안됨. 
		$__SESSION["W_SES"] = $W_SES ;

		// 4.2.1 버젼 UNIX에서 테스트 해봤을 때 _SESSION으로 등록이 안됨
		//고로 _SESSION말고 session_register를 쓰겠음. 2005/08/02(화) 아폴로 
		session_register( "W_SES" ) ;

		if($this->debug )
		{
			echo("<pre>") ;
			echo(__FUNCTION__."세션번수 내용 __SESSION") ;
			var_dump( $__SESSION );
			echo("W_SES 내용\n") ;
			var_dump($W_SES) ;
			echo(__FUNCTION__."끝") ;
			echo("</pre>") ;
		}		

		//원래 서버변수로 갱신, @note 세션은 적용안됨.
		prepare_server_vars( "SET")	; 		
		
		if($this->debug )
		{
			echo("<pre>") ;
			echo(__FUNCTION__."prepare_server_vars SET후 세션내용 __SESSION\n") ;
			var_dump( $__SESSION );
			echo("_SESSION 내용 <br>") ;
			var_dump($_SESSION) ;
			echo(__FUNCTION__."끝") ;
			echo("</pre>") ;
		}
		return ;
	}


	///////////////////////////////////////////////////////////////////////////
	function session_unset()
	{
		eval( $this->globals ) ;

		$W_SES = array() ;
		$__SESSION["W_SES"] = $W_SES ; 				
		//원래 서버변수로 갱신
		prepare_server_vars( "SET" )	; 

	}


    /**
    2002/03/17 make.
    기존의 세션을 모두 제거한 다음 로그인을 시도한다.  */
	function login()
	{
	    session_destroy() ;
	    
	    ///@todo 치환 할것. 
	    global $HTTP_GET_VARS ;
	    //unset($HTTP_GET_VARS[log]) ;
	    $HTTP_GET_VARS[log] = "" ;
	    $http_var_name = "HTTP_GET_VARS" ;
	    $this->auth_data[param] = base64_encode( serialize( ${$http_var_name} ) ) ;
	
	    $this->mode = "auth" ;
	    $this->login_form() ; // form에서 exit함.
	}

    /**
    2002/03/17 make.
    세션을 모두 파괴한 다음 원래 지신의 링크로 돌아온다.  */
    function logout()
    {
        eval( $this->globals ) ;
        $this->session_unset() ;
        /* SZmember 의 쿠키와 로그인 기록을 삭제하는 루틴이 필요
        SZmember의 message 기능만 사용하지 않으면 삭제하지 않아도 문제는 없음
        $SID 를 넘겨 받아야 쿠키 삭제가 가능
        if($this->db_type == "szmember")
        {
        }
        */

		session_destroy() ;

        $url_param = param2url(base64_decode($this->auth_data[param])) ;
        $url_param = eregi_replace("log=off&","", $url_param) ;
        $redirect_url = $GLOBALS[PHP_SELF]."?".$url_param ;
		$redirect_time = 0 ;
	
        if($this->debug) 
		{
			echo ("perm_auth:redirect_url[$redirect_url]<br>") ;
			$redirect_time = 10 ;
		}

        echo("<META HTTP-EQUIV=REFRESH CONTENT=\"$redirect_time; URL='$redirect_url'\">") ;
        exit ;
    }
        
} // class 정의 끝
///////////////////////////////////////////////////////////////////////////////


///////////////////////////////////////////////////////////////////////////////
/// 메인 모듈
///////////////////////////////////////////////////////////////////////////////

	ini_set("session.save_path", $C_base[dir]."/auth/session") ;
	if(! @file_exists( $C_base[dir]."/auth/session"))
	{
		//echo("session directory is not exists<br>") ;
		umask(0000) ;
		if(!mkdir( $C_base[dir]."/auth/session", 0777))
		{
			err_abort("$C_base[dir]/auth 디렉토리를 만들수 없습니다. 권한이 777인지 확인해주세요.") ;
		}
	}

	//폼값을 Cache하도록 처리하는 부분 수정, 2004/09/13
	//@ini_set("session.cache_limiter", "private") ;
	//@session_cache_limiter('private');

	@session_start() ;
    
    // 인증 모듈 초기화 및 생성
    if( empty($auth_param) )
    {
		if($filter_type)
		{
			if($_debug) echo("AUTH:filter_type[$filter_type]<br>") ;
		}

		if($_debug)
		{
			echo("auth.php:auth_param is empty<br>") ;
		}

		//echo("<br>auth_perm empty create serlize...<br>") ;
		//$http_var_name = "HTTP_".${REQUEST_METHOD}."_VARS" ;

		// 이부분도 수정요망 2002/07/12
		// register globals = off에 대한 처리.
		$http_var_name = "HTTP_GET_VARS" ;
		$auth_param = base64_encode(serialize(${$http_var_name})) ;

		$Row_auth[param] = $auth_param ;
	}
    else
    {
		extract(unserialize(stripslashes(base64_decode($auth_param)))) ;
	}

	// auth_mode는 CGI변수에서 자동으로 삽입이 된다.
	// auth_data는 CGI변수에서 auth_passwd, auth_user (FORM을 통해서)
    $_auth_data = array("") ;
    $_auth_data[data]   = $auth_data ;
    $_auth_data[user]   = $auth_user ;
    $_auth_data[passwd] = $auth_passwd ;
    $_auth_data[param]  = $auth_param ;

    $_auth_data[try]      = $auth_try ;
    $_auth_data[base_url] = $C_base[url] ; // lib/get_base()함수의 호출결과
    $_auth_data[base_dir] = $C_base[dir] ; // lib/get_base()함수의 호출결과    

    //세션 변수를 사용하기 쉽도록 세션 변수에서 복사
    unset($W_SES) ;

	// PHP 4.0 대 버젼과 호환성을 위해서 사용.
	//서버 변수들을 전역으로 설정할 수 있도록 개정 2005/07/08 아폴로
	// prepare_server_vars() 리턴값 추가로 좀 더 사용을 원활하게...

	if($_debug) {echo("<pre>_SESSION") ; print_r($_SESSION) ; echo("</pre>"); } ;
	// temp  
	$__SERVER = "" ; 
	$__GET = "" ;
	$__POST = "" ; 
	$__COOKIE = "" ; 
	$__FILES = "" ; 
	$__ENV = "" ; 
	$__SESSION = "" ; 
	$globals = prepare_server_vars() ;
	eval( "global $globals ;" ) ;


	if($_debug) {echo("<pre> globals :: $globals" ) ; } ;
	if($_debug) {echo("<pre>_GET") ; print_r($__GET) ; echo("</pre>"); } ;
	if($_debug) {echo("<pre>_POST") ; print_r($__POST) ; echo("</pre>"); } ;
	if($_debug) {echo("<pre>_COOKIE") ; print_r($__COOKIE) ; echo("</pre>"); } ;
	if($_debug) {echo("<pre>_SERVER") ; print_r($__SERVER) ; echo("</pre>"); } ;
	if($_debug) {echo("<pre>_SESSION") ; print_r($__SESSION) ; echo("</pre>"); } ;

    $W_SES = $__SESSION["W_SES"] ;
    if( ! isset( $W_SES ) )
    {
    	if( $_debug) echo("세션이 등록되어 있지 않습니다.  default 세션 등록 필요...<br>" ) ;
		//$W_SES 
    }

    if(  $_debug )
    {
    	echo("W_SES [") ;
    	print_r($W_SES) ;
    	echo("]<br>") ;

    }

    //내부에서 전역으로 사용할 수 있도록 추가, $globals는 prepare_server_vars()에서 리턴받은 문자열임.
    $globals = '$W_SES, '.$globals ;
    $auth = new auth($auth_mode, $_auth_data, $C_base[member_db_type],  "", READ_MODE, $globals) ;
    $URL = "" ;
?>
