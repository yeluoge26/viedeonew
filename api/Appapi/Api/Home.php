<?php

class Api_Home extends PhalApi_Api {  

	public function getRules() {
		return array(
			'search' => array(
				'uid' => array('name' => 'uid', 'type' => 'int', 'desc' => '用户ID'),
				'key' => array('name' => 'key', 'type' => 'string', 'default'=>'' ,'desc' => '用户ID'),
				'p' => array('name' => 'p', 'type' => 'int', 'default'=>'1' ,'desc' => '页数'),
			),

            'videoSearch' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'desc' => '用户ID'),
                'key' => array('name' => 'key', 'type' => 'string', 'default'=>'' ,'desc' => '关键词'),
                'p' => array('name' => 'p', 'type' => 'int', 'default'=>'1' ,'desc' => '页数'),
            ),
		);
	}
	
    /**
     * 配置信息
     * @desc 用于获取配置信息
     * @return int code 操作码，0表示成功
     * @return array info 
     * @return array info[0] 配置信息
     * @return string msg 提示信息
     */
    public function getConfig() {
        $rs = array('code' => 0, 'msg' => '', 'info' => array());
		$configpri = getConfigPri();
        $info = getConfigPub();
		$info['tximgfolder']=$configpri['tximgfolder'];//腾讯云图片存储目录
        $info['txvideofolder']=$configpri['txvideofolder'];//腾讯云视频存储目录
        $info['cloudtype']=$configpri['cloudtype'];//视频云存储类型
		$info['qiniu_domain']=$configpri['qiniu_domain_url'];//七牛云存储空间地址（后台配置）
        $info['private_letter_switch']=$configpri['private_letter_switch']; //未关注时可发送私信开关
        $info['private_letter_nums']=$configpri['private_letter_nums']; //未关注时可发送私信条数
        $info['video_audit_switch']=$configpri['video_audit_switch']; //视频审核是否开启


        $info['txcloud_appid']=$configpri['txcloud_appid'];
        $info['txcloud_region']=$configpri['txcloud_region'];
        $info['txcloud_bucket']=$configpri['txcloud_bucket'];

        /* 引导页 */
        $domain = new Domain_Guide();
		$guide_info = $domain->getGuide();
        
        $info['guide']=$guide_info;
        
        $rs['info'][0] = $info;

        return $rs;
    }	

    /**
     * 登录方式开关信息
     * @desc 用于获取登录方式开关信息
     * @return int code 操作码，0表示成功
     * @return array info 
     * @return string info[0].login_qq qq登录，0表示关闭，1表示开启
     * @return string info[0].login_wx 微信登录，0表示关闭，1表示开启
     * @return string info[0].login_sina 新浪微博登陆，0表示关闭，1表示开启
     * @return string info[0].login_fb facebook登陆，0表示关闭，1表示开启
     * @return string info[0].login_tw twitter登陆，0表示关闭，1表示开启
     * @return array info[0].login_type 开启的登录方式
     * @return string info[0].login_type[][0] 登录方式标识

     * @return string msg 提示信息
     */
    public function getLogin() {
        $rs = array('code' => 0, 'msg' => '', 'info' => array());

        $info = getConfigPub();
        $rs['info'][0]['login_type'] = $info['login_type'];

        return $rs;
    }		
	
	
		
	/**
     * 搜索
     * @desc 用于首页搜索会员
     * @return int code 操作码，0表示成功
     * @return array info 会员列表
     * @return string info[].id 用户ID
     * @return string info[].user_nicename 用户昵称
     * @return string info[].avatar 头像
     * @return string info[].sex 性别
     * @return string info[].signature 签名
     * @return string info[].level 等级
     * @return string info[].isattention 是否关注，0未关注，1已关注
     * @return string msg 提示信息
     */
    public function search() {

        $rs = array('code' => 0, 'msg' => '', 'info' => array());

		$isBlackUser=isBlackUser($this->uid);

		 if($isBlackUser=='0'){
			$rs['code'] = 700;
			$rs['msg'] = '该账号已被禁用';
			return $rs;
		}
		
		$uid=checkNull($this->uid);
		$key=checkNull($this->key);
		$p=checkNull($this->p);
		if($key==''){
			$rs['code'] = 1001;
			$rs['msg'] = "请填写关键词";
			return $rs;
		}

		
		if(!$p){
			$p=1;
		}

        
		
        $domain = new Domain_Home();
        $info = $domain->search($uid,$key,$p);
        
        $rs['info'] = $info;

        return $rs;
    }	
		
	

    /**
     * 视频搜索
     * @desc 视频搜索
     * @return int code 状态码 0表示成功
     * @return string msg 提示信息
     * @return array info 返回信息
     * @return 
     */
    public function videoSearch(){


        $rs = array('code' => 0, 'msg' => '', 'info' => array());
        $isBlackUser=isBlackUser($this->uid);


         if($isBlackUser=='0'){
            $rs['code'] = 700;
            $rs['msg'] = '该账号已被禁用';
            return $rs;
        }

  
        
        $uid=checkNull($this->uid);
        $key=checkNull($this->key);
        $p=checkNull($this->p);
        if($key==''){
            $rs['code'] = 1001;
            $rs['msg'] = "请填写关键词";
            return $rs;
        }
        
        if(!$p){
            $p=1;
        }

        $key1='videoSearch'.'_'.$key.'_'.$p;

        $info=getcache($key1);
        $info=false;
        if(!$info){
            $domain = new Domain_Home();
            $info = $domain->videoSearch($uid,$key,$p);
            setcaches($key1,$info,2);
        }
        
        $rs['info'] = $info;

        return $rs;
    }
	
} 
