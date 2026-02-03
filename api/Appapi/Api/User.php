<?php
session_start();
class Api_User extends PhalApi_Api {

	public function getRules() {
		return array(
			'iftoken' => array(
				'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
				'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
			),
			
			'getBaseInfo' => array(
				'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
				'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
				'version_ios' => array('name' => 'version_ios', 'type' => 'string', 'desc' => 'IOS版本号'),
			),
			
			'updateAvatar' => array(
				'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
				'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
				'file' => array('name' => 'file','type' => 'file', 'min' => 0, 'max' => 1024 * 1024 * 30, 'range' => array('image/jpg', 'image/jpeg', 'image/png'), 'ext' => array('jpg', 'jpeg', 'png')),
			),
			
			'updateFields' => array(
				'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
				'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
				'fields' => array('name' => 'fields', 'type' => 'string', 'require' => true, 'desc' => '修改信息，json字符串'),
			),
			
			'setAttent' => array(
				'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
				'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
				'touid' => array('name' => 'touid', 'type' => 'int', 'require' => true, 'desc' => '对方ID'),
			),
			
			'isAttent' => array(
				'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
				'touid' => array('name' => 'touid', 'type' => 'int', 'require' => true, 'desc' => '对方ID'),
			),
			
			'isBlacked' => array(
				'uid' => array('name' => 'uid', 'type' => 'int', 'require' => true, 'desc' => '用户ID'),
				'touid' => array('name' => 'touid', 'type' => 'int', 'require' => true, 'desc' => '对方ID'),
			),
			'checkBlack' => array(
				'uid' => array('name' => 'uid', 'type' => 'int', 'require' => true, 'desc' => '用户ID'),
				'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
				'touid' => array('name' => 'touid', 'type' => 'int', 'require' => true, 'desc' => '对方ID'),
			),

			'setBlack' => array(
				'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
				'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
				'touid' => array('name' => 'touid', 'type' => 'int', 'require' => true, 'desc' => '对方ID'),
			),
			
			'getFollowsList' => array(
				'uid' => array('name' => 'uid', 'type' => 'int', 'require' => true, 'desc' => '用户ID'),
				'touid' => array('name' => 'touid', 'type' => 'int',  'require' => true, 'desc' => '对方ID'),
				'key' => array('name' => 'key', 'type' => 'string', 'desc' => '搜索关键词'),
				'p' => array('name' => 'p', 'type' => 'int', 'min' => 1, 'default'=>1,'desc' => '页数'),
			),
			
			'getFansList' => array(
				'uid' => array('name' => 'uid', 'type' => 'int', 'require' => true, 'desc' => '用户ID'),
				'touid' => array('name' => 'touid', 'type' => 'int', 'require' => true, 'desc' => '对方ID'),
				'p' => array('name' => 'p', 'type' => 'int', 'min' => 1, 'default'=>1,'desc' => '页数'),
			),
			
			'getBlackList' => array(
				'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
				'touid' => array('name' => 'touid', 'type' => 'int', 'require' => true, 'desc' => '对方ID'),
				'p' => array('name' => 'p', 'type' => 'int', 'min' => 1, 'default'=>1,'desc' => '页数'),
			),
			
			'getUserHome' => array(
				'uid' => array('name' => 'uid', 'type' => 'int', 'desc' => '用户ID'),
				'touid' => array('name' => 'touid', 'type' => 'int', 'require' => true, 'desc' => '对方ID'),
			),
			
			'getPmUserInfo' => array(
				'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
				'touid' => array('name' => 'touid', 'type' => 'int', 'require' => true, 'desc' => '对方ID'),
			),
			
			'getMultiInfo' => array(
				'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
				'uids' => array('name' => 'uids', 'type' => 'string', 'min' => 1,'require' => true, 'desc' => '用户ID，多个以逗号分割'),
			),

			'getLikeVideos'=>array(
				'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
				'p' => array('name' => 'p', 'type' => 'int', 'default'=>'1' ,'desc' => '页数'),
			),
			
		);
	}
	/**
	 * 判断token
	 * @desc 用于判断token
	 * @return int code 操作码，0表示成功， 1表示用户不存在
	 * @return array info 
	 * @return string msg 提示信息
	 */
	public function iftoken() {
		$rs = array('code' => 0, 'msg' => '', 'info' => array());
		
		$checkToken=checkToken($this->uid,$this->token);
		if($checkToken==700){
			$rs['code'] = $checkToken;
			$rs['msg'] = '您的登陆状态失效，请重新登陆！';
			return $rs;
		}else if($checkToken==10020){
			$rs['code'] = 700;
			$rs['msg'] = '该账号已被禁用';
			return $rs;
		}

		//获取用户信息存入app本地
		$domain=new Domain_User();
		$info=$domain->getBaseInfo($this->uid);
		$rs['info'][0]=$info;
		return $rs;
	}
	/**
	 * 获取用户信息
	 * @desc 用于获取单个用户基本信息
	 * @return int code 操作码，0表示成功， 1表示用户不存在
	 * @return array info 
	 * @return array info[0] 用户信息
	 * @return int info[0].id 用户ID
	 * @return string info[0].level 等级
	 * @return string info[0].lives 直播数量
	 * @return string info[0].follows 关注数
	 * @return string info[0].fans 粉丝数
	 * @return string info[0].agent_switch 分销开关
	 * @return string info[0].family_switch 家族开关
	 * @return string msg 提示信息
	 */
	public function getBaseInfo() {
		$rs = array('code' => 0, 'msg' => '', 'info' => array());
		
		$checkToken=checkToken($this->uid,$this->token);
		if($checkToken==700){
			$rs['code'] = $checkToken;
			$rs['msg'] = '您的登陆状态失效，请重新登陆！';
			return $rs;
		}else if($checkToken==10020){
			$rs['code'] = 700;
			$rs['msg'] = '该账号已被禁用';
			return $rs;
		}

		$domain = new Domain_User();
		$info = $domain->getBaseInfo($this->uid);

		if(!$info){
            $rs['code'] = 700;
			$rs['msg'] = '您的登陆状态失效，请重新登陆！';
			return $rs;
        }
		
		$configpub=getConfigPub();
		
		$ios_shelves=$configpub['ios_shelves'];
		

		/* 个人中心菜单 */
		$version_ios=$this->version_ios;
		$list=array();
		$shelves=1;
		if($version_ios==$ios_shelves){
			$shelves=0;
		}
        
        /* 店铺信息 */
        $isshop='0';
        $shop_name='';
        $shop_thumb='';
        
        $domain2 = new Domain_Shop();
		$shop = $domain2->getShop($this->uid);
        if($shop){
            $isshop='1';
            $shop_name=$shop['name'];
            $shop_thumb=$shop['thumb'];
        }
        
        $info['isshop']=$isshop;
        $info['shop_name']=$shop_name;
        $info['shop_thumb']=$shop_thumb;
        

		$rs['info'][0] = $info;

		return $rs;
	}

	/**
	 * 头像上传 (七牛)[根据后台的配置信息来决定是走七牛云存储还是走腾讯云存储]
	 * @desc 用于用户修改头像
	 * @return int code 操作码，0表示成功
	 * @return array info 
	 * @return string list[0].avatar 用户主头像
	 * @return string list[0].avatar_thumb 用户头像缩略图
	 * @return string msg 提示信息
	 */
	public function updateAvatar() {
		$rs = array('code' => 0 , 'msg' => '', 'info' => array());

		$checkToken=checkToken($this->uid,$this->token);
		if($checkToken==700){
			$rs['code'] = $checkToken;
			$rs['msg'] = '您的登陆状态失效，请重新登陆！';
			return $rs;
		}else if($checkToken==10020){
			$rs['code'] = 700;
			$rs['msg'] = '该账号已被禁用';
			return $rs;
		}

		if (!isset($_FILES['file'])) {
			$rs['code'] = 1001;
			$rs['msg'] = T('miss upload file');
			return $rs;
		}

		if ($_FILES["file"]["error"] > 0) {
			$rs['code'] = 1002;
			$rs['msg'] = T('failed to upload file with error: {error}', array('error' => $_FILES['file']['error']));
			DI()->logger->debug('failed to upload file with error: ' . $_FILES['file']['error']);
			return $rs;
		}

		//$uptype=DI()->config->get('app.uptype');
		
		//获取后台配置的云存储方式
		$configpri=getConfigPri();
		$cloudtype=$configpri['cloudtype'];


		if($cloudtype==1){
			//七牛
			$url = DI()->qiniu->uploadFile($_FILES['file']['tmp_name'],$configpri['qiniu_accesskey'],$configpri['qiniu_secretkey'],$configpri['qiniu_bucket'],$configpri['qiniu_domain_url']);


			if (!empty($url)) {
				$avatar=  $url.'?imageView2/2/w/600/h/600'; //600 X 600
				$avatar_thumb=  $url.'?imageView2/2/w/200/h/200'; // 200 X 200
				$data=array(
					"avatar"=>$avatar,
					"avatar_thumb"=>$avatar_thumb,
				);

				
				/* 统一服务器 格式 */
				/* $space_host= DI()->config->get('app.Qiniu.space_host');
				$avatar2=str_replace($space_host.'/', "", $avatar);
				$avatar_thumb2=str_replace($space_host.'/', "", $avatar_thumb);
				$data2=array(
					"avatar"=>$avatar2,
					"avatar_thumb"=>$avatar_thumb2,
				); */
			}


		}else if($cloudtype==0){
			//本地上传
			//设置上传路径 设置方法参考3.2
			DI()->ucloud->set('save_path','avatar/'.date("Ymd"));

			//新增修改文件名设置上传的文件名称
		   // DI()->ucloud->set('file_name', $this->uid);

			//上传表单名
			$res = DI()->ucloud->upfile($_FILES['file']);
			
			$files='../upload/'.$res['file'];
			$newfiles=str_replace(".png","_thumb.png",$files);
			$newfiles=str_replace(".jpg","_thumb.jpg",$newfiles);
			$newfiles=str_replace(".gif","_thumb.gif",$newfiles); 
			$PhalApi_Image = new Image_Lite();
			//打开图片
			$PhalApi_Image->open($files);
			/**
			 * 可以支持其他类型的缩略图生成，设置包括下列常量或者对应的数字：
			 * IMAGE_THUMB_SCALING      //常量，标识缩略图等比例缩放类型
			 * IMAGE_THUMB_FILLED       //常量，标识缩略图缩放后填充类型
			 * IMAGE_THUMB_CENTER       //常量，标识缩略图居中裁剪类型
			 * IMAGE_THUMB_NORTHWEST    //常量，标识缩略图左上角裁剪类型
			 * IMAGE_THUMB_SOUTHEAST    //常量，标识缩略图右下角裁剪类型
			 * IMAGE_THUMB_FIXED        //常量，标识缩略图固定尺寸缩放类型
			 */

			// 按照原图的比例生成一个最大为150*150的缩略图并保存为thumb.jpg
			
			$PhalApi_Image->thumb(660, 660, IMAGE_THUMB_SCALING);
			$PhalApi_Image->save($files);

			$PhalApi_Image->thumb(200, 200, IMAGE_THUMB_SCALING);
			$PhalApi_Image->save($newfiles);			
			
			$avatar=  $res['url']; //600 X 600
			
			$avatar_thumb=str_replace(".png","_thumb.png",$avatar);
			$avatar_thumb=str_replace(".jpg","_thumb.jpg",$avatar_thumb);
			$avatar_thumb=str_replace(".gif","_thumb.gif",$avatar_thumb); 

			$data=array(
				"avatar"=>$avatar,
				"avatar_thumb"=>$avatar_thumb,
			);
			
		}else if($cloudtype==2){

			//腾讯云存储
			
				require(API_ROOT.'/public/txcloud/include.php');

				$config=array(
					'app_id' => $configpri['txcloud_appid'],
					'secret_id' => $configpri['txcloud_secret_id'],
					'secret_key' => $configpri['txcloud_secret_key'],
					'region' => $configpri['txcloud_region'],   // bucket所属地域：华北 'tj' 华东 'sh' 华南 'gz'
					'timeout' => 60
				);


				$bucket=$configpri['txcloud_bucket'];
				//bucketname
				//uploadlocalpath
				/* $src = $_FILES['file'];//'./hello.txt'; */
				$src = $_FILES["file"]["tmp_name"];//'./hello.txt';
				
				
				//cosfolderpath
				$folder = '/'.$configpri['tximgfolder'];
				//cospath
				$dst = $folder.'/'.$_FILES["file"]["name"];
				//config your information
		 
				
				date_default_timezone_set('PRC');
				
				$cosApi = new 	\QCloud\Cos\Api($config);
				/* $cosApi->delFile($bucket,$dst);  // 上传文件前删除相同的文件 */
				// Upload file into bucket.
				$ret = $cosApi->upload($bucket, $src, $dst);
				
				if($ret['code']==0){
					$rs['code']=0;
				}else{
					$rs['code']=1002;
					$rs['msg']=$ret['message'];
					return $rs;
				} 
				$url = $ret['data']['source_url'];
				if (!empty($url)) {
					$avatar=  $url; //600 X 600
					$avatar_thumb=  $url; // 200 X 200
					$data=array(
						"avatar"=>$avatar,
						"avatar_thumb"=>$avatar_thumb,
					);
				}
		}
		
		@unlink($_FILES['file']['tmp_name']);

		/* 清除缓存 */
		delCache("userinfo_".$this->uid);
		
		$domain = new Domain_User();
		$info = $domain->userUpdate($this->uid,$data);

		$rs['info'][0] = $data;

		return $rs;

	}
	
	/**
	 * 修改用户信息
	 * @desc 用于修改用户信息
	 * @return int code 操作码，0表示成功
	 * @return array info 
	 * @return string list[0].msg 修改成功提示信息 
	 * @return string msg 提示信息
	 */
	public function updateFields() {
		$rs = array('code' => 0, 'msg' => '', 'info' => array());
		
		$checkToken=checkToken($this->uid,$this->token);
		if($checkToken==700){
			$rs['code'] = $checkToken;
			$rs['msg'] = '您的登陆状态失效，请重新登陆！';
			return $rs;
		}else if($checkToken==10020){
			$rs['code'] = 700;
			$rs['msg'] = '该账号已被禁用';
			return $rs;
		}
		$fields=json_decode($this->fields,true);
		
		$domain = new Domain_User();
		foreach($fields as $k=>$v){
			$fields[$k]=checkNull($v);
		}
		
		if(array_key_exists('user_nicename', $fields)){
			if($fields['user_nicename']==''){
				$rs['code'] = 1002;
				$rs['msg'] = '昵称不能为空';
				return $rs;
			}
			$isexist = $domain->checkName($this->uid,$fields['user_nicename']);
			if(!$isexist){
				$rs['code'] = 1002;
				$rs['msg'] = '昵称重复，请修改';
				return $rs;
			}
			$fields['user_nicename']=filterField($fields['user_nicename']);
		}
		//手机号
		if(array_key_exists('mobile', $fields)){
			if($fields['mobile']==''){
				$rs['code'] = 1002;
				$rs['msg'] = '手机号码不能为空';
				return $rs;
			}
			$isexist = $domain->checkMobile($this->uid,$fields['mobile']);
			if(!$isexist){
				$rs['code'] = 1002;
				$rs['msg'] = '手机号码重复，请修改';
				return $rs;
			}
			$fields['mobile']=filterField($fields['mobile']);
		}

		//根据生日计算年龄
		if(array_key_exists('birthday', $fields)){
			if($fields['birthday']==''){
				$rs['code'] = 1002;
				$rs['msg'] = '请选择生日';
				return $rs;
			}

			$now=time();
			$time1=strtotime($fields['birthday']);

			$nowYear=date("Y",$now);
			$birthdayYear=date("Y",$time1);

			$nowMonth=date("m",$now);
			$month=date("m",$time1);

			if($nowMonth>=$month){
				$cha=0;
			}else{
				$cha=1;
			}

			$age=$nowYear-$birthdayYear-$cha;

			$fields['age']=$age;


		}
		
		
		$info = $domain->userUpdate($this->uid,$fields);
	 
		if($info===false){
			$rs['code'] = 1001;
			$rs['msg'] = '修改失败';
			return $rs;
		}
		/* 清除缓存 */
		delCache("userinfo_".$this->uid);
		$rs['info'][0]['msg']='修改成功';
		return $rs;
	}
	
		
	/**
	 * 判断是否关注
	 * @desc 用于判断是否关注
	 * @return int code 操作码，0表示成功
	 * @return array info 
	 * @return string info[0].isattent 关注信息，0表示未关注，1表示已关注
	 * @return string msg 提示信息
	 */
	public function isAttent() {
		$rs = array('code' => 0, 'msg' => '', 'info' => array());
		$isBlackUser=isBlackUser($this->uid);
		 if($isBlackUser=='0'){
			$rs['code'] = 700;
			$rs['msg'] = '该账号已被禁用';
			return $rs;
		}
		
		$info = isAttention($this->uid,$this->touid);
	 
		$rs['info'][0]['isattent']=(string)$info;
		return $rs;
	}			
	
	/**
	 * 关注/取消关注
	 * @desc 用于关注/取消关注
	 * @return int code 操作码，0表示成功
	 * @return array info 
	 * @return string info[0].isattent 关注信息，0表示未关注，1表示已关注
	 * @return string msg 提示信息
	 */
	public function setAttent() {
		$rs = array('code' => 0, 'msg' => '', 'info' => array());

		$uid=$this->uid;
		$token=checkNull($this->token);
		$touid=$this->touid;

		$isBlackUser=isBlackUser($uid);
		 if($isBlackUser=='0'){
			$rs['code'] = 700;
			$rs['msg'] = '该账号已被禁用';
			return $rs;
		}

		$checkToken=checkToken($uid,$token);
		if($checkToken==700){
			$rs['code'] = $checkToken;
			$rs['msg'] = '您的登陆状态失效，请重新登陆！';
			return $rs;
		}else if($checkToken==10020){
			$rs['code'] = 700;
			$rs['msg'] = '该账号已被禁用';
			return $rs;
		}
		
		if($uid==$touid){
			$rs['code']=1001;
			$rs['msg']='不能关注自己';
			return $rs;	
		}
		$domain = new Domain_User();
		$info = $domain->setAttent($uid,$touid);
	 
		$rs['info'][0]['isattent']=(string)$info;
		return $rs;
	}			
	
	/**
	 * 判断是否拉黑
	 * @desc 用于判断是否拉黑
	 * @return int code 操作码，0表示成功
	 * @return array info 
	 * @return string info[0].isattent  拉黑信息,0表示未拉黑，1表示已拉黑
	 * @return string msg 提示信息
	 */
	public function isBlacked() {
			$rs = array('code' => 0, 'msg' => '', 'info' => array());
			$isBlackUser=isBlackUser($this->uid);
			 if($isBlackUser=='0'){
				$rs['code'] = 700;
				$rs['msg'] = '该账号已被禁用';
				return $rs;
			}
			
			$info = isBlack($this->uid,$this->touid);
		 
			$rs['info'][0]['isblack']=(string)$info;
			return $rs;
	}	

	/**
	 * 检测拉黑状态
	 * @desc 用于私信聊天时判断私聊双方的拉黑状态
	 * @return int code 操作码，0表示成功
	 * @return array info 
	 * @return string info[0].u2t  是否拉黑对方,0表示未拉黑，1表示已拉黑
	 * @return string info[0].t2u  是否被对方拉黑,0表示未拉黑，1表示已拉黑
	 * @return string msg 提示信息
	 */
	public function checkBlack() {
			$rs = array('code' => 0, 'msg' => '', 'info' => array());
            
            $uid=checkNull($this->uid);
            $token=checkNull($this->token);
            $touid=checkNull($this->touid);

			$checkToken=checkToken($uid,$token);
			if($checkToken==700){
				$rs['code'] = $checkToken;
				$rs['msg'] = '您的登陆状态失效，请重新登陆！';
				return $rs;
			}else if($checkToken==10020){
				$rs['code'] = 700;
				$rs['msg'] = '该账号已被禁用';
				return $rs;
			}

			/*$isBlackUser=isBlackUser($this->uid);
			 if($isBlackUser=='0'){
				$rs['code'] = 700;
				$rs['msg'] = '该账号已被禁用';
				return $rs;
			}*/
			
			$u2t = isBlack($uid,$touid);
			$t2u = isBlack($touid,$uid);
			$isattent=isAttention($touid,$uid);

		 
			$rs['info'][0]['u2t']=(string)$u2t;
			$rs['info'][0]['t2u']=(string)$t2u;
			$rs['info'][0]['isattent']=(string)$isattent;
			return $rs;
	}			
		
	/**
	 * 拉黑/取消拉黑
	 * @desc 用于拉黑/取消拉黑
	 * @return int code 操作码，0表示成功
	 * @return array info 
	 * @return string info[0].isblack 拉黑信息,0表示未拉黑，1表示已拉黑
	 * @return string msg 提示信息
	 */
	public function setBlack() {
			$rs = array('code' => 0, 'msg' => '', 'info' => array());

			$uid=$this->uid;
			$token=checkNull($this->token);
			$touid=$this->touid;

			$isBlackUser=isBlackUser($uid);
			 if($isBlackUser=='0'){
				$rs['code'] = 700;
				$rs['msg'] = '该账号已被禁用';
				return $rs;
			}

			$checkToken=checkToken($uid,$token);
			if($checkToken==700){
				$rs['code'] = $checkToken;
				$rs['msg'] = '您的登陆状态失效，请重新登陆！';
				return $rs;
			}else if($checkToken==10020){
				$rs['code'] = 700;
				$rs['msg'] = '该账号已被禁用';
				return $rs;
			}
			
			$domain = new Domain_User();
			$info = $domain->setBlack($uid,$touid);
		 
			$rs['info'][0]['isblack']=(string)$info;
			return $rs;
	}		
	
	
	/**
	 * 关注列表
	 * @desc 用于获取用户的关注列表
	 * @return int code 操作码，0表示成功
	 * @return array info 
	 * @return string info[].isattent 是否关注,0表示未关注，1表示已关注
	 * @return string msg 提示信息
	 */
	public function getFollowsList() {
		$rs = array('code' => 0, 'msg' => '', 'info' => array());
		$isBlackUser=isBlackUser($this->uid);
		 if($isBlackUser=='0'){
			$rs['code'] = 700;
			$rs['msg'] = '该账号已被禁用';
			return $rs;
		}

		$key=checkNull($this->key);
		
		$domain = new Domain_User();
		$info = $domain->getFollowsList($this->uid,$this->touid,$this->p,$key);
	 
		$rs['info']=$info;
		return $rs;
	}		
	
	/**
	 * 粉丝列表
	 * @desc 用于获取用户的关注列表
	 * @return int code 操作码，0表示成功
	 * @return array info 
	 * @return string info[].isattent 是否关注,0表示未关注，1表示已关注
	 * @return string msg 提示信息
	 */
	public function getFansList() {
		$rs = array('code' => 0, 'msg' => '', 'info' => array());
		$isBlackUser=isBlackUser($this->uid);
		 if($isBlackUser=='0'){
			$rs['code'] = 700;
			$rs['msg'] = '该账号已被禁用';
			return $rs;
		}
		
		$domain = new Domain_User();
		$info = $domain->getFansList($this->uid,$this->touid,$this->p);
	 
		$rs['info']=$info;
		return $rs;
	}	

	/**
	 * 黑名单列表
	 * @desc 用于获取用户的名单列表
	 * @return int code 操作码，0表示成功
	 * @return array info 用户基本信息
	 * @return string msg 提示信息
	 */
	public function getBlackList() {
		$rs = array('code' => 0, 'msg' => '', 'info' => array());
		$isBlackUser=isBlackUser($this->uid);
		 if($isBlackUser=='0'){
			$rs['code'] = 700;
			$rs['msg'] = '该账号已被禁用';
			return $rs;
		}
		
		$domain = new Domain_User();
		$info = $domain->getBlackList($this->uid,$this->touid,$this->p);
	 
		$rs['info']=$info;
		return $rs;
	}		
	
	/**
	 * 直播记录
	 * @desc 用于获取用户的直播记录
	 * @return int code 操作码，0表示成功
	 * @return array info 
	 * @return string info[].nums 观看人数
	 * @return string info[].datestarttime 格式化的开播时间
	 * @return string info[].dateendtime 格式化的结束时间
	 * @return string info[].video_url 回放地址
	 * @return string info[].file_id 回放标示
	 * @return string msg 提示信息
	 */
	public function getLiverecord() {
		$rs = array('code' => 0, 'msg' => '', 'info' => array());
		
		$domain = new Domain_User();
		$info = $domain->getLiverecord($this->touid,$this->p);
	 
		$rs['info']=$info;
		return $rs;
	}	


	/**
	 * 个人主页 
	 * @desc 用于获取个人主页数据
	 * @return int code 操作码，0表示成功
	 * @return array info 
	 * @return string info[0].follows 关注数
	 * @return string info[0].fans 粉丝数
	 * @return string info[0].isattention 是否关注，0表示未关注，1表示已关注
	 * @return string info[0].isblack 我是否拉黑对方，0表示未拉黑，1表示已拉黑
	 * @return string info[0].isblack2 对方是否拉黑我，0表示未拉黑，1表示已拉黑
	 * @return array info[0].contribute 贡献榜前三
	 * @return array info[0].contribute[].avatar 头像
	 * @return string info[0].islive 是否正在直播，0表示未直播，1表示直播
	 * @return array info[0].liveinfo 直播信息
	 * @return array info[0].liverecord 直播记录
	 * @return array info[0].video 视频列表
	 * @return string msg 提示信息
	 */
	public function getUserHome() {
		$rs = array('code' => 0, 'msg' => '', 'info' => array());
		$isBlackUser=isBlackUser($this->uid);
		 if($isBlackUser=='0'){
			$rs['code'] = 700;
			$rs['msg'] = '该账号已被禁用';
			return $rs;
		}
		
		$domain = new Domain_User();
		$info=$domain->getUserHome($this->uid,$this->touid);
        
        /* 店铺信息 */
        $isshop='0';
        $shop_name='';
        $shop_thumb='';
        
        $domain2 = new Domain_Shop();
		$shop = $domain2->getShop($this->touid);
        if($shop){
            $isshop='1';
            $shop_name=$shop['name'];
            $shop_thumb=$shop['thumb'];
        }
        
        $info['isshop']=$isshop;
        $info['shop_name']=$shop_name;
        $info['shop_thumb']=$shop_thumb;
		
		$rs['info'][0]=$info;
		return $rs;
	}			
	
	/**
     * 私信用户信息
     * @desc 用于获取其他用户基本信息
     * @return int code 操作码，0表示成功，1表示用户不存在
     * @return array info   
     * @return string info[0].id 用户ID
     * @return string info[0].isattention 我是否关注对方，0未关注，1已关注
     * @return string info[0].isattention2 对方是否关注我，0未关注，1已关注
     * @return string msg 提示信息
     */
    public function getPmUserInfo() {
        $rs = array('code' => 0, 'msg' => '', 'info' => array());
		$isBlackUser=isBlackUser($this->uid);
		 if($isBlackUser=='0'){
			$rs['code'] = 700;
			$rs['msg'] = '该账号已被禁用';
			return $rs;
		}

        $info = getUserInfo($this->touid);
		 if (empty($info)) {
            $rs['code'] = 1001;
            $rs['msg'] = T('user not exists');
            return $rs;
        }
        $info['isattention2']= (string)isAttention($this->touid,$this->uid);
        $info['isattention']= (string)isAttention($this->uid,$this->touid);
       
        $rs['info'][0] = $info;

        return $rs;
    }		

	/**
	 * 获取多用户信息 
	 * @desc 用于获取获取多用户信息
	 * @return int code 操作码，0表示成功
	 * @return array info 排行榜列表
	 * @return string info[].utot 是否关注，0未关注，1已关注
	 * @return string info[].ttou 对方是否关注我，0未关注，1已关注
	 * @return string msg 提示信息
	 */
	public function getMultiInfo() {
		$rs = array('code' => 0, 'msg' => '', 'info' => array());
		$isBlackUser=isBlackUser($this->uid);
		 if($isBlackUser=='0'){
			$rs['code'] = 700;
			$rs['msg'] = '该账号已被禁用';
			return $rs;
		}
		
		$uids=explode(",",$this->uids);

		foreach ($uids as $k=>$userId) {
			if($userId){
				$userinfo= getUserInfo($userId);
				if($userinfo){
					/*$userinfo['utot']= isAttention($this->uid,$userId);
					
					$userinfo['ttou']= isAttention($userId,$this->uid);*/
					$userinfo['isattent']= isAttention($this->uid,$userId);
											
					$rs['info'][]=$userinfo;
																
				}					
			}
		}

		return $rs;
	}	
	

	/**
	 * 获取喜欢的视频
	 * @desc 用户获取用户喜欢的视频
	 * @return int code 状态码，0表示成功
	 * @return string msg 提示信息
	 * @return array info
	 * @return string info[0].uid 视频发布者id
	 * @return string info[0].title 视频标题
	 * @return string info[0].thumb 视频封面图
	 * @return string info[0].thumb_s 视频封面小图
	 * @return string info[0].href 视频地址
	 * @return string info[0].likes 视频被喜欢总数
	 * @return string info[0].views 视频被浏览数
	 * @return string info[0].comments 视频评论数
	 * @return string info[0].shares 视频被分享数
	 * @return string info[0].addtime 视频发布时间
	 * @return string info[0].city 视频发布城市
	 * @return string info[0].datetime 视频发布时间（格式化为汉字形式）
	 * @return string info[0].userinfo 视频发布者信息
	 */
	public function getLikeVideos(){
		$uid=$this->uid;
		$p=$this->p;

		

		$rs = array('code' => 0, 'msg' => '', 'info' => array());

		$domain = new Domain_User();
		$res=$domain->getLikeVideos($uid,$p);
		if($res==1001){
			$rs['code']=0;
			$rs['msg']="暂无视频列表";
			return $rs;
		}

		$rs['info']=$res;

		return $rs;
	}


}
