<?php
session_start();
class Api_Login extends PhalApi_Api { 
	public function getRules() {
        return array(
			'userLogin' => array(
                'user_login' => array('name' => 'user_login', 'type' => 'string', 'desc' => '账号'),
				'code' => array('name' => 'code', 'type' => 'string', 'require' => true,   'desc' => '验证码'),
				'source' => array('name' => 'source', 'type' => 'string',  'desc' => '注册来源android/ios'),
				'agentcode' => array('name' => 'agentcode', 'type' => 'string',  'desc' => '邀请标识'),
            ),
			
			
			'userLoginByThird' => array(
                'openid' => array('name' => 'openid', 'type' => 'string', 'min' => 1, 'require' => true,   'desc' => '第三方openid'),
                'type' => array('name' => 'type', 'type' => 'string', 'min' => 1, 'require' => true,   'desc' => '第三方标识'),
                'nicename' => array('name' => 'nicename', 'type' => 'string',   'default'=>'',  'desc' => '第三方昵称'),
                'avatar' => array('name' => 'avatar', 'type' => 'string',  'default'=>'', 'desc' => '第三方头像'),
                'source' => array('name' => 'source', 'type' => 'string',  'desc' => '注册来源android/ios'),
                'agentcode' => array('name' => 'agentcode', 'type' => 'string',  'desc' => '邀请标识'),
            ),
			

			'getLoginCode' => array(
				'mobile' => array('name' => 'mobile', 'type' => 'string', 'min' => 1, 'require' => true,  'desc' => '手机号'),
			),
        );
	}
	
    /**
     * 会员登陆 需要密码
     * @desc 用于用户登陆信息
     * @return int code 操作码，0表示成功
     * @return array info 用户信息
     * @return string info[0].id 用户ID
     * @return string info[0].user_nicename 昵称
     * @return string info[0].avatar 头像
     * @return string info[0].avatar_thumb 头像缩略图
     * @return string info[0].sex 性别
     * @return string info[0].signature 签名
     * @return string info[0].coin 用户余额
     * @return string info[0].login_type 注册类型
     * @return string info[0].level 等级
     * @return string info[0].province 省份
     * @return string info[0].city 城市
     * @return string info[0].birthday 生日
     * @return string info[0].token 用户Token
     * @return string msg 提示信息
     */
    public function userLogin() {
        $rs = array('code' => 0, 'msg' => '', 'info' => array());
		$user_login=checkNull($this->user_login);
		$code=checkNull($this->code);
		$source=checkNull($this->source);
		$agentcode=checkNull($this->agentcode);

		if($code==''){
			$rs['code'] = 1001;
            $rs['msg'] = '请填写验证码';
             return $rs;
		}

		if(!$_SESSION['login_mobile']){
			$rs['code'] = 1001;
            $rs['msg'] = '请获取验证码';
            return $rs;
		}

		if($user_login!=$_SESSION['login_mobile']){
			$rs['code'] = 1001;
            $rs['msg'] = '手机号码错误';
            return $rs;
		}
		if($code!=$_SESSION['login_mobile_code']){
			$rs['code'] = 1001;
            $rs['msg'] = '验证码错误';
            return $rs;
		}

        $domain = new Domain_Login();
        $info = $domain->userLogin($user_login,$source);

		if($info==1002){
			$rs['code'] = 1002;
            $rs['msg'] = '该账号已被禁用';
            return $rs;	
		}

        if($info['isreg']==1 && $agentcode!=''){
            $domain2 = new Domain_Agent();
            $domain2->setAgent($info['id'],$agentcode);
        }
        $rs['info'][0] = $info;
				
		
        return $rs;
    }		
   	

	
    /**
     * 第三方登录
     * @desc 用于用户登陆信息
     * @return int code 操作码，0表示成功
     * @return array info 用户信息
     * @return string info[0].id 用户ID
     * @return string info[0].user_nicename 昵称
     * @return string info[0].avatar 头像
     * @return string info[0].avatar_thumb 头像缩略图
     * @return string info[0].sex 性别
     * @return string info[0].signature 签名
     * @return string info[0].coin 用户余额
     * @return string info[0].login_type 注册类型
     * @return string info[0].level 等级
     * @return string info[0].province 省份
     * @return string info[0].city 城市
     * @return string info[0].birthday 生日
     * @return string info[0].token 用户Token
     * @return string msg 提示信息
     */
    public function userLoginByThird() {
        $rs = array('code' => 0, 'msg' => '', 'info' => array());
		$openid=checkNull($this->openid);
		$type=checkNull($this->type);
		$nicename=checkNull($this->nicename);
		$avatar=checkNull($this->avatar);
		$source=checkNull($this->source);
		$agentcode=checkNull($this->agentcode);
		
        $domain = new Domain_Login();
        $info = $domain->userLoginByThird($openid,$type,$nicename,$avatar,$source);
		
        if($info==1002){
            $rs['code'] = 1002;
            $rs['msg'] = '该账号已被禁用';
            return $rs;					
		}

        if($info['isreg']==1 && $agentcode!=''){
            $domain2 = new Domain_Agent();
            $domain2->setAgent($info['id'],$agentcode);
        }

        $rs['info'][0] = $info;

        return $rs;
    }



	/**
	 * 获取登录短信验证码
	 * @desc 用于登录获取短信验证码
	 * @return int code 操作码，0表示成功,2发送失败
	 * @return array info 
	 * @return string msg 提示信息
	 */
	 
	public function getLoginCode() {
		$rs = array('code' => 0, 'msg' => '', 'info' => array());
		
		$mobile = $this->mobile;
		
		$ismobile=checkMobile($mobile);
		if(!$ismobile){
			$rs['code']=1001;
			$rs['msg']='请输入正确的手机号';
			return $rs;	
		}

		//验证手机号是否被禁用
		$status=checkMoblieCanCode($mobile);

		if($status==0){
			$rs['code']=1001;
			$rs['msg']='该账号已被禁用';
			return $rs;	
		}

		if($_SESSION['login_mobile']==$mobile && $_SESSION['login_mobile_expiretime']> time() ){
			$rs['code']=1002;
			$rs['msg']='验证码1分钟有效，请勿多次发送';
			return $rs;
		}
		
        $limit = ip_limit();	
		if( $limit == 1){
			$rs['code']=1003;
			$rs['msg']='您已当日发送次数过多';
			return $rs;
		}		
		$mobile_code = random(6,1);
		
		/* 发送验证码 */
 		$result=sendCode($mobile,$mobile_code);
		if($result['code']===0){
			$_SESSION['login_mobile'] = $mobile;
			$_SESSION['login_mobile_code'] = $mobile_code;
			$_SESSION['login_mobile_expiretime'] = time() +60*5;

		}else if($result['code']==667){
			$_SESSION['login_mobile'] = $mobile;
            $_SESSION['login_mobile_code'] = $result['msg'];
            $_SESSION['login_mobile_expiretime'] = time() +60*5;
            
            $rs['code']=$result['code'];
			$rs['msg']='验证码为：'.$result['msg'];

			return $rs;
		}else{
			$rs['code']=1002;
			$rs['msg']=$result['msg'];

			return $rs;
		}
		
		$rs['msg']="发送成功";
		return $rs;
	}		

}
