<?php
if(!defined("__wb_auth__")) define("__wb_auth__","1") ;
else return ;

ob_start() ;

/**
@todo ���α׷��� include�ϴ� ��ο� ���� ���� �޽����� ��������
��� ��ġ�� ���ϱ� ������ �� ��� ��ġ�� ������ �� �־�� �Ѵ�.
@todo ������������ ���ǿ� ����� �ȴ�. 
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
//���Ǽ��� �ʱ�ȭ

//trans_sid�� ���� �������� ������ �����ϰ��� �Ҷ� �����.
//
///PHPSESSID���� ���� �ٴ� ��� �����ϴ� ���.
//ini_set("session.auto_start", false) ;
//ini_set("session.use_trans_sid", false) ;

//ini_set('session.use_cookies', 1);
//ini_set('session.use_only_cookies', 1);

//echo ("uid:[".$HTTP_SESSION_VARS["W_SES"]["uid"]."]") ;
////////////////////////////////////////////////////////////////////////////
/// �Ķ���ͷ� ���� ���� URL�� ���� ������.
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
/// ���� ó�� Ŭ����
class auth
{
	var $debug ;
	
	var $globals ;        ///< ����Ʈ ���� �������� ���� ���ڿ�.
	
	var $mode ; //auth ���
	var $db_type ;
	var $auth_data ; // auth parameter, user, passwd, url
	
	var $msg ;  //���� �޽���
	var $error_html ; // ���� �޽��� ����
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
	var $run_mode_perm ; // ���忡 ���� ����Ǵ� ����.
	
      // base information ;
	var $uid ;
	var $gid ;
	var $user ;
	var $group ;
	
	var $alias ; //����
	var $email ; //���ڿ���
	var $homepage ; //���ڿ���

	// other information ;
	var $member_info ;

	////////////////////////////////////////////////////////////////////////////
	///������.
	function auth($mode ="check", $auth_data = "", $db_type = "old_type", $error_html = "", $run_mode = READ_MODE, $globals = "" )
	{
		$this->debug = 0 ;
		
		// eval���� �ٷ� ����� �� �ֵ��� ���� ����� ����� �д�. 
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
		
		//���� ���� ���� ��� ����.
		eval( $this->globals ) ;
		
		if($this->debug) echo("auth: db_type[{$this->db_type}]<br>auth_mode::[$mode]<br> auth_data[user]::[$auth_data[user]]<br>auth_data[passwd]::[$auth_data[passwd]]<br> auth_data[param]::[$auth_data[param]]<br>run_mode::[$run_mode]<br>") ;
	}
	
	////////////////////////////////////////////////////////////////////////////
	/**
	������� ���� : �⺻���� check�� 
	anonymous_mode�� ����ϰ� ������ : ��бۿ��� ����Ϸ��� �߰�
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
	/** �α� �� �����ֱ� */
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
	/// ���� ������� ��� �˻�.
	@author apollo@whitebbs.net 2005/08/02(ȭ) 
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
			$this->alias = ($C_admin_alias)?$C_admin_alias:"������" ;
			$this->email = $C_admin_email ;
			$this->homepage = $C_admin_homepage ;
			$this->group = "wheel" ;

			$result = true ;
		}

		return $result ;
	}

	/**
	/// postgresql�� ���� �˻�
	@author apollo@whitebbs.net 2005/08/02(ȭ) 
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

			//�������θ� Ȯ���� �� ���ʿ��� �����ʹ� �����ʿ�
			$one_$Row['password'] = "" ;
			$one_row[3] = "" ;

			$this->member_info = $one_row ;
			if($this->debug) print_r($one_row) ;

			$result = true ;
		}

		return $result ;
	}


	////////////////////////////////////////////////////////////////////////////
	// SZmember ���� ���� by ü���丶�� 2003.1.1
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

		if($one_row[5]==0) err_SZ("���� �±��� �̷������ �ʾҽ��ϴ�");  //SZ ���� ���Խ� ������ guest �� ������ ���

		$one_row[1] = chop ($one_row[1]);
		$crypt_password=crypt($this->auth_data[passwd],$one_row[1]);

		if(!($one_row[1] == $crypt_password))
		{
			err_SZ("id �� password �� Ȯ���ϼ���");// password �� Ʋ��, password�� Ʋ������ ������ ��� ������ ���� �� ����
			return false ;
		}

		if( $one_row[5] == 100)  //SZ ���� 100�� ������
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
		else  // �����ڰ� �ƴϸ� ��� ����� ó�� (����� ������ ���� �ʿ䰡 ���� member1, member2)
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
		// SZmember �� ����ϱ� ���� ��Ű�� ����� �α��� ����� �����ϴ� �κ�
		session_reg_SZ($this->auth_data[user],$this->auth_data[passwd],$base_dir,$crypt_password);
		member_log_SZ($one_row[0],$base_dir);


		return true ;
	}


	////////////////////////////////////////////////////////////////////////////
	/**
	@param auth_data: �ܺο��� ���� ����DB����, ex:��ȣ
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
	
		// DBŸ���� �������� ����ϵ��� �ϴ� ���� �������� �������� ������. 2003/05/20
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
    /// ������ �������.
    function make_session()
    {
    	//���� ���� ���� ��� ����.
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
    		// ���������� ����� ��
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
		// �׷� DB�� ������ true return, else false

		// $this->check_group�ȿ� $W_SES[gid]�� �����ϴ��� �˻�

		return false ;
	}

	///////////////////////////////////////////////////////////////////////////
	/**
			//�����ΰ�� auth_data�� ���� ��й�ȣ �˻��� �������
	*/
	function anonymous_auth()
	{
		if($this->debug) echo("anonymous_auth:auth_data[passwd][".$this->auth_data[passwd]."], check_data[passwd][".$this->check_data[passwd]."]<br>") ;
				//�̰��� ��ȣȭ�� �����ϴ� ������ �� �� �ִ�.
				//2002/03/17 ���ȣ�� �˻����� �ʴ´�.
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
    anonymous_auth�� make_session�� ������ ��� ������ �ϸ�
    �׷��� ���� ��� �α��� ������ redirect�Ѵ�.
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

             // ������ ��ȿȭ ��Ű���� �����̷�Ʈ ���Ѿ� �Ѵ�.
             // �ƴϾ����� ���ڴ�.
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
			    
			    //��б� ��尡 �ƴ� ��츸 ��带 �������ش�. 
			    //auth_secret�� ���� �ڽ��� ��带 �������� �ֱ����ؼ�...
			    
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
    �ƹ��� ���ѵ� ���� ��� 
    */
    function no_perm()
    {
		switch($this->no_perm_action)
        {
        case "login" :
			$this->mode = "auth" ;
			$this->login_form() ; // form���� exit��.
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
    // ������ ���� �˻� CHECK
    // super_user�˻�
    // 1. ������ ��ϵǾ� ������ �ش��ϴ��� ��
    // 2. ��ϵǾ� ���� �ʴٸ� error_mode�� ���� ó��
    // error_mode : message, login
    // ���� �˻簡 �Ϸ�Ǹ� �ٷ� return�ϹǷ� ����
            run_mode�� �´� ���Ѱ˻�
    */
    function perm_check()
    {
	    global $W_SES ;
        //run_mode�˻�
	    $have_perm = false ;
	    //������ ������ ������
	    // _anonymous�� ����� �ʿ䰡 ����
	    if($this->debug) echo("[$this->mode][$W_SES[uid]][$W_SES[user]]<br>") ;
	    
	    //���� �˻��ϴ� �κ� ���� �ʿ�.
	    //if( session_is_registered("W_SES") && !empty($W_SES[uid]) )
	    if( isset($W_SES) && !empty($W_SES[uid]) )
	    {
	    	return $this->perm_check_session() ;
	    }
	    else //���ǵ���� �Ǿ����� ������
	    {
	        // err_mode = message, login_form ������ ó���� �� ������ ���ڳ�...
	        if($this->debug) echo("perm_check: no session exist<br>") ;
	        if( $this->perm_have("anonymous") )
	        {
				return $this->perm_check_anonymous() ;
	        }
	        else
	        {
	        	return $this->no_perm();
	        }
	    } //���� �˻� ��
	}

    ///////////////////////////////////////////////////////////////////////////
    /**
    ���� �˻��ϰ����ϴ� ���� ���� �����ڸ� ���Ѵ�.
    �⺻���� perm()���� ������ ����ڰ� �ȴ�.
	*/
    function setuid($setuid)
    {
		if($this->debug) echo("set owner from[$this->setuid] to[$setuid]") ;
		$this->setuid = $setuid ;
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
	���忡 �ش��ϴ� ������ �ִ��� Ȯ��
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
                    // ����ϰ�� other�� �����ڸ� �������־� �� ������ �����ų �� �ִ�.
            if($this->debug) echo("perm_have:[$this->setuid]==[$W_SES[user]]<br>") ;
                    // setuid�� ���ؼ� ����� �ȳ�
            //$perm = ($perm && ($this->setuid == $W_SES[user] ) ) ;
                    //�� ������ �� ������ ������ Ȯ���ϴ� ������ �ʿ���.
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
    ��ϵǾ� �ִ� ������� ��� super_user���� �˻��� �� �־�� �Ѵ�.
    ���Ѿ��� �޽��� ����ʿ�?
    auth_data�� anonymous�� ��� ������ DB�� �ƴ� �ܺο��� ��й�ȣ�� ���������� �ް��� �Ҷ� ����Ѵ�.
    @param auth_mode
    @param error_mode message:�޽�����, login:�α�������, value:������
    */
    function perm($owner, $group = "", $all_perm = "7000", $check_data ="", $no_perm_action = "login")
    {
        eval( $this->globals ) ;
        global $W_SES ;

        if($this->debug) echo("perm:auth_mode[$this->mode] run_mode[$this->run_mode]<br>") ;

         //�⺻ ���ǰ� ���� ������ ��� ����� �������� ���� ����
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
                //������ ������ ���� ���� ǥ�ÿ� �ൿ�� �ʿ���.
                echo("check������ �����ϴ�.<br>") ;
                exit ;
            }
            break ;

		case "check_secret" : //�պκп��� ���� �˻��ϰ� �ڿ� �ٽ� �˻��ϵ��� 
            $this->mode = "on_check_secret" ; 		        
            if( $this->perm_check() == false )
            {
                echo("auth_test������ �����ϴ�.<br>") ;
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
	        perm()�Լ� ȣ�� ���Ŀ� ����Ͽ��� �Ѵ�.
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
            perm()�Լ� ȣ�� ���Ŀ� ����Ͽ��� �Ѵ�.
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
            perm()�Լ� ȣ�� ���Ŀ� ����Ͽ��� �Ѵ�.
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
            perm()�Լ� ȣ�� ���Ŀ� ����Ͽ��� �Ѵ�.
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
            W_SES�� ���� �������� �ʴ� �� ���?
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
	// ���Ǻ��� ��ϰ� ������ ���� session_resister, session_unregister�� ������� �ʱ����.
	// �� ������ �µ��� ���ǵ����...
	function session_set()
	{
		eval( $this->globals ) ;		
		global $_SESSION ;

		//���⼭ ���� ����� �ȵ�. 
		$__SESSION["W_SES"] = $W_SES ;

		// 4.2.1 ���� UNIX���� �׽�Ʈ �غ��� �� _SESSION���� ����� �ȵ�
		//��� _SESSION���� session_register�� ������. 2005/08/02(ȭ) ������ 
		session_register( "W_SES" ) ;

		if($this->debug )
		{
			echo("<pre>") ;
			echo(__FUNCTION__."���ǹ��� ���� __SESSION") ;
			var_dump( $__SESSION );
			echo("W_SES ����\n") ;
			var_dump($W_SES) ;
			echo(__FUNCTION__."��") ;
			echo("</pre>") ;
		}		

		//���� ���������� ����, @note ������ ����ȵ�.
		prepare_server_vars( "SET")	; 		
		
		if($this->debug )
		{
			echo("<pre>") ;
			echo(__FUNCTION__."prepare_server_vars SET�� ���ǳ��� __SESSION\n") ;
			var_dump( $__SESSION );
			echo("_SESSION ���� <br>") ;
			var_dump($_SESSION) ;
			echo(__FUNCTION__."��") ;
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
		//���� ���������� ����
		prepare_server_vars( "SET" )	; 

	}


    /**
    2002/03/17 make.
    ������ ������ ��� ������ ���� �α����� �õ��Ѵ�.  */
	function login()
	{
	    session_destroy() ;
	    
	    ///@todo ġȯ �Ұ�. 
	    global $HTTP_GET_VARS ;
	    //unset($HTTP_GET_VARS[log]) ;
	    $HTTP_GET_VARS[log] = "" ;
	    $http_var_name = "HTTP_GET_VARS" ;
	    $this->auth_data[param] = base64_encode( serialize( ${$http_var_name} ) ) ;
	
	    $this->mode = "auth" ;
	    $this->login_form() ; // form���� exit��.
	}

    /**
    2002/03/17 make.
    ������ ��� �ı��� ���� ���� ������ ��ũ�� ���ƿ´�.  */
    function logout()
    {
        eval( $this->globals ) ;
        $this->session_unset() ;
        /* SZmember �� ��Ű�� �α��� ����� �����ϴ� ��ƾ�� �ʿ�
        SZmember�� message ��ɸ� ������� ������ �������� �ʾƵ� ������ ����
        $SID �� �Ѱ� �޾ƾ� ��Ű ������ ����
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
        
} // class ���� ��
///////////////////////////////////////////////////////////////////////////////


///////////////////////////////////////////////////////////////////////////////
/// ���� ���
///////////////////////////////////////////////////////////////////////////////

	ini_set("session.save_path", $C_base[dir]."/auth/session") ;
	if(! @file_exists( $C_base[dir]."/auth/session"))
	{
		//echo("session directory is not exists<br>") ;
		umask(0000) ;
		if(!mkdir( $C_base[dir]."/auth/session", 0777))
		{
			err_abort("$C_base[dir]/auth ���丮�� ����� �����ϴ�. ������ 777���� Ȯ�����ּ���.") ;
		}
	}

	//������ Cache�ϵ��� ó���ϴ� �κ� ����, 2004/09/13
	//@ini_set("session.cache_limiter", "private") ;
	//@session_cache_limiter('private');

	@session_start() ;
    
    // ���� ��� �ʱ�ȭ �� ����
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

		// �̺κе� ������� 2002/07/12
		// register globals = off�� ���� ó��.
		$http_var_name = "HTTP_GET_VARS" ;
		$auth_param = base64_encode(serialize(${$http_var_name})) ;

		$Row_auth[param] = $auth_param ;
	}
    else
    {
		extract(unserialize(stripslashes(base64_decode($auth_param)))) ;
	}

	// auth_mode�� CGI�������� �ڵ����� ������ �ȴ�.
	// auth_data�� CGI�������� auth_passwd, auth_user (FORM�� ���ؼ�)
    $_auth_data = array("") ;
    $_auth_data[data]   = $auth_data ;
    $_auth_data[user]   = $auth_user ;
    $_auth_data[passwd] = $auth_passwd ;
    $_auth_data[param]  = $auth_param ;

    $_auth_data[try]      = $auth_try ;
    $_auth_data[base_url] = $C_base[url] ; // lib/get_base()�Լ��� ȣ����
    $_auth_data[base_dir] = $C_base[dir] ; // lib/get_base()�Լ��� ȣ����    

    //���� ������ ����ϱ� ������ ���� �������� ����
    unset($W_SES) ;

	// PHP 4.0 �� ������ ȣȯ���� ���ؼ� ���.
	//���� �������� �������� ������ �� �ֵ��� ���� 2005/07/08 ������
	// prepare_server_vars() ���ϰ� �߰��� �� �� ����� ��Ȱ�ϰ�...

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
    	if( $_debug) echo("������ ��ϵǾ� ���� �ʽ��ϴ�.  default ���� ��� �ʿ�...<br>" ) ;
		//$W_SES 
    }

    if(  $_debug )
    {
    	echo("W_SES [") ;
    	print_r($W_SES) ;
    	echo("]<br>") ;

    }

    //���ο��� �������� ����� �� �ֵ��� �߰�, $globals�� prepare_server_vars()���� ���Ϲ��� ���ڿ���.
    $globals = '$W_SES, '.$globals ;
    $auth = new auth($auth_mode, $_auth_data, $C_base[member_db_type],  "", READ_MODE, $globals) ;
    $URL = "" ;
?>
