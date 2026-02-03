<?php

class Model_Login extends PhalApi_Model_NotORM {
	
	protected $fields='id,user_nicename,avatar,avatar_thumb,sex,signature,coin,user_status,login_type,province,city,area,birthday,last_login_time,code,age,mobile';

	/* 会员登录 */   	
    public function userLogin($user_login,$source) {
		
		$info=DI()->notorm->users
				->select($this->fields)
				->where('user_login=? and user_type="2"',$user_login) 
				->fetchOne();

		$now=time();
		$nowYear=date("Y",$now);

		if(!$info){

			$birthdayYear=2000;
			
			//新注册该用户
			$user_pass='techspace';
			$user_pass=setPass($user_pass);
			$user_login=$user_login;

			$nickname='手机用户'.substr($user_login,-4);
			
			$avatar='/default.png';
			$avatar_thumb='/default_thumb.png';

			$code=$this->createCode();
			
			$data=array(
				'user_login' => $user_login,
				'user_nicename' =>$nickname,
				'user_pass' =>$user_pass,
				'signature' =>'这家伙很懒，什么都没留下',
				'avatar' =>$avatar,
				'avatar_thumb' =>$avatar_thumb,
				'last_login_ip' =>$_SERVER['REMOTE_ADDR'],
				'create_time' => date("Y-m-d H:i:s"),
				'user_status' => 1,
				"user_type"=>2,//会员
				"code"=>$code,
				"coin"=>0,
				"age"=>$nowYear-$birthdayYear,
				"birthday"=>'2000-01-01',
				"mobile"=>$user_login,
				"login_type"=>'phone'
			);
            if($source){
                $data['source']=$source;
            }
			
			$rs=DI()->notorm->users->insert($data);	
		
			$info['id']=$rs['id'];
			$info['user_nicename']=$data['user_nicename'];
			$info['avatar']=get_upload_path($data['avatar']);
			$info['avatar_thumb']=get_upload_path($data['avatar_thumb']);
			$info['sex']='2';
			$info['signature']=$data['signature'];
			$info['coin']='0';
			$info['login_type']=$data['login_type'];
			$info['province']='';
			$info['city']='';
			$info['birthday']='';
			$info['last_login_time']='';
			$info['code']=$code;
			$info['age']="0";
			$info['mobile']=$user_login;
			$info['isreg']='1';
			$info['hometown']='';
			
		}else{

			//重新计算用户的年龄
			$month=date("m",strtotime($info['birthday']));
			$nowMonth=date("m",$now);
			if($nowMonth>=$month){
				$cha=0;
			}else{
				$cha=1;
			}

			$birthdayYear=date("Y",strtotime($info['birthday']));
			$age=$nowYear-$birthdayYear-$cha;

			DI()->notorm->users->where("id=?",$info['id'])->update(array("age"=>$age));

			if($info['user_status']=='0'){
				return 1002;					
			}
			unset($info['user_status']);
			
		
			
			$info['avatar']=get_upload_path($info['avatar']);
			$info['avatar_thumb']=get_upload_path($info['avatar_thumb']);
			$info['isreg']='0';
			$info['hometown']=$info['province'].$info['city'].$info['area'];

		}

			$token=md5(md5($info['id'].$user_login.time()));
			$info['token']=$token;
			$this->updateToken($info['id'],$token);

			$cache=array("token_".$info['id'],"userinfo_".$info['id']);
			delcache($cache);

		
        return $info;
    }	

		
	/* 第三方会员登录 */
    public function userLoginByThird($openid,$type,$nickname,$avatar,$source) {			
        $info=DI()->notorm->users
            ->select($this->fields)
            ->where('openid=? and login_type=? and user_type="2"',$openid,$type)
            ->fetchOne();
		$configpri=getConfigPri();

		$now=time();
		$nowYear=date("Y",$now);

		if(!$info){

			$birthdayYear=2000;
			/* 注册 */
			$user_pass='techspace';
			$user_pass=setPass($user_pass);
			$user_login=$type.'_'.time().rand(100,999);

			if(!$nickname){
				$nickname=$type.'用户-'.substr($openid,-4);
			}else{
				$nickname=urldecode($nickname);
			}
			if(!$avatar){
				$avatar='/default.png';
				$avatar_thumb='/default_thumb.png';
			}else{
				$avatar=urldecode($avatar);
				$avatar_thumb=$avatar;
			}

			$code=$this->createCode();
			
			$data=array(
				'user_login' => $user_login,
				'user_nicename' =>$nickname,
				'user_pass' =>$user_pass,
				'signature' =>'这家伙很懒，什么都没留下',
				'avatar' =>$avatar,
				'avatar_thumb' =>$avatar_thumb,
				'last_login_ip' =>$_SERVER['REMOTE_ADDR'],
				'create_time' => date("Y-m-d H:i:s"),
				'user_status' => 1,
				'openid' => $openid,
				'login_type' => $type, 
				"user_type"=>2,//会员
				"code"=>$code,
				"age"=>$nowYear-$birthdayYear,
				"birthday"=>'2000-01-01',
				"coin"=>'0',
			);
            if($source){
                $data['source']=$source;
            }
			
			$rs=DI()->notorm->users->insert($data);		
		
			$info['id']=$rs['id'];
			$info['user_nicename']=$data['user_nicename'];
			$info['avatar']=get_upload_path($data['avatar']);
			$info['avatar_thumb']=get_upload_path($data['avatar_thumb']);
			$info['sex']='2';
			$info['signature']=$data['signature'];
			$info['coin']='0';
			$info['login_type']=$data['login_type'];
			$info['province']='';
			$info['city']='';
			$info['birthday']='';
			$info['consumption']='0';
			$info['user_status']=1;
			$info['last_login_time']='';
			$info['isreg']='1';
		}else{

			//重新计算用户的年龄
			$month=date("m",strtotime($info['birthday']));
			$nowMonth=date("m",$now);
			if($nowMonth>=$month){
				$cha=0;
			}else{
				$cha=1;
			}

			$birthdayYear=date("Y",strtotime($info['birthday']));
			$age=$nowYear-$birthdayYear-$cha;

			DI()->notorm->users->where("id=?",$info['id'])->update(array("age"=>$age));

			$info['isreg']='0';

		}
		
		if($info['user_status']=='0'){
			return 1002;					
		}
		unset($info['user_status']);
		
		

		unset($info['last_login_time']);
		
		$token=md5(md5($info['id'].$openid.time()));
		
		$info['token']=$token;
		$info['avatar']=get_upload_path($info['avatar']);
		$info['avatar_thumb']=get_upload_path($info['avatar_thumb']);
		
		$this->updateToken($info['id'],$token);
		
		$cache=array("token_".$info['id'],"userinfo_".$info['id']);
		delcache($cache);
        return $info;
    }		
	
	/* 更新token 登陆信息 */
    public function updateToken($uid,$token) {
		$expiretime=time()+60*60*24*300;
		
        DI()->notorm->users
			->where('id=?',$uid)
            ->update(array("token"=>$token, "expiretime"=>$expiretime ,'last_login_time' => date("Y-m-d H:i:s"), "last_login_ip"=>$_SERVER['REMOTE_ADDR'] ));
		return 1;
    }	
	
	/* 生成邀请码 */
	public function createCode(){
		$code = 'ABCDEFGHIJKLMNPQRSTUVWXYZ';
		$rand = $code[rand(0,25)]
			.strtoupper(dechex(date('m')))
			.date('d').substr(time(),-5)
			.substr(microtime(),2,5)
			.sprintf('%02d',rand(0,99));
		for(
			$a = md5( $rand, true ),
			$s = '123456789ABCDEFGHIJKLMNPQRSTUV',
			$d = '',
			$f = 0;
			$f < 6;
			$g = ord( $a[ $f ] ),
			$d .= $s[ ( $g ^ ord( $a[ $f + 6 ] ) ) - $g & 0x1F ],
			$f++
		);
		if(mb_strlen($d)==6){
			$oneinfo=DI()->notorm->users
					->select("id")
					->where('code=?',$d)
					->fetchOne();
			if(!$oneinfo){
				return $d;
			}
		}
        $d=$this->createCode();
		return $d;
	}

}
