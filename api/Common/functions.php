<?php
	/* Redis链接 */
	function connectionRedis(){
		if (!class_exists('Redis')) {
			DI()->redis = null;
			return;
		}
		$REDIS_HOST= DI()->config->get('app.REDIS_HOST');
		$REDIS_AUTH= DI()->config->get('app.REDIS_AUTH');
		$REDIS_PORT= DI()->config->get('app.REDIS_PORT');
		try {
			$redis = new Redis();
			$redis -> pconnect($REDIS_HOST,$REDIS_PORT);
			$redis -> auth($REDIS_AUTH);
			DI()->redis=$redis;
		} catch (Exception $e) {
			DI()->redis = null;
		}
	}
	/* 设置缓存 */
	function setcache($key,$info){
		$config=getConfigPri();
		if($config['cache_switch']!=1){
			return 1;
		}
		if(!DI()->redis) return 1;
		DI()->redis->set($key,json_encode($info));
		DI()->redis->setTimeout($key, $config['cache_time']);

		return 1;
	}
	/* 设置缓存 可自定义时间*/
	function setcaches($key,$info,$time=0){
		if(!DI()->redis) return 1;
		DI()->redis->set($key,json_encode($info));
        if($time>0){
            DI()->redis->setTimeout($key, $time);
        }

		return 1;
	}
	/* 获取缓存 */
	function getcache($key){
		$config=getConfigPri();

		if($config['cache_switch']!=1 || !DI()->redis){
			$isexist=false;
		}else{
			$isexist=DI()->redis->Get($key);
		}

		return json_decode($isexist,true);
	}
	/* 获取缓存 不判断后台设置 */
	function getcaches($key){
		if(!DI()->redis) return null;
		$isexist=DI()->redis->Get($key);

		return json_decode($isexist,true);
	}
	/* 删除缓存 */
	function delcache($key){
		if(!DI()->redis) return 1;
		$isexist=DI()->redis->delete($key);
		return 1;
	}
    
	/* 去除NULL 判断空处理 主要针对字符串类型*/
	function checkNull($checkstr){
		$checkstr=urldecode($checkstr);
		$checkstr=htmlspecialchars($checkstr);
		$checkstr=trim($checkstr);
		//$checkstr=filterEmoji($checkstr);
		if( strstr($checkstr,'null') || (!$checkstr && $checkstr!=0 ) ){
			$str='';
		}else{
			$str=$checkstr;
		}
		return $str;	
	}
	
	/* 去除emoji表情 */
	function filterEmoji($str){
		$str = preg_replace_callback(
			'/./u',
			function (array $match) {
				return strlen($match[0]) >= 4 ? '' : $match[0];
			},
			$str);
		return $str;
	}
    

	/* 检验手机号 */
	function checkMobile($mobile){

		$ismobile = preg_match("/^1[3|4|5|7|8|9]\d{9}$/",$mobile);
		if($ismobile){
			return 1;
		}else{
			return 0;
		}
	}
	/* 随机数 */
	function random($length = 6 , $numeric = 0) {
		PHP_VERSION < '4.2.0' && mt_srand((double)microtime() * 1000000);
		if($numeric) {
			$hash = sprintf('%0'.$length.'d', mt_rand(0, pow(10, $length) - 1));
		} else {
			$hash = '';
			$chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789abcdefghjkmnpqrstuvwxyz';
			$max = strlen($chars) - 1;
			for($i = 0; $i < $length; $i++) {
				$hash .= $chars[mt_rand(0, $max)];
			}
		}
		return $hash;
	}	
	/* 发送验证码 - 互译无线*/
	function sendCode_huyi($mobile,$code){
		$rs=array();
		$config = getConfigPri();

		if(!$config['sendcode_switch']){
            $rs['code']=667;
			$rs['msg']='123456';
            return $rs;
        }

		/* 互亿无线 */
		$target = "http://106.ihuyi.cn/webservice/sms.php?method=Submit";
		
		$post_data = "account=".$config['ihuyi_account']."&password=".$config['ihuyi_ps']."&mobile=".$mobile."&content=".rawurlencode("您的验证码是：".$code."。请不要把验证码泄露给其他人。");
		//密码可以使用明文密码或使用32位MD5加密
		$gets = xml_to_array(Post($post_data, $target));

		if($gets['SubmitResult']['code']==2){
			$rs['code']=0;
		}else{
			$rs['code']=1002;
			//$rs['msg']=$gets['SubmitResult']['msg'];
			$rs['msg']="获取失败";
		} 
		return $rs;
	}
	
	function Post($curlPost,$url){
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_NOBODY, true);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $curlPost);
		$return_str = curl_exec($curl);
		curl_close($curl);
		return $return_str;
	}
	
	function xml_to_array($xml){
		$reg = "/<(\w+)[^>]*>([\\x00-\\xFF]*)<\\/\\1>/";
		if(preg_match_all($reg, $xml, $matches)){
			$count = count($matches[0]);
			for($i = 0; $i < $count; $i++){
			$subxml= $matches[2][$i];
			$key = $matches[1][$i];
				if(preg_match( $reg, $subxml )){
					$arr[$key] = xml_to_array( $subxml );
				}else{
					$arr[$key] = $subxml;
				}
			}
		}
		return $arr;
	}
	/* 发送验证码 */
    
    /* 发送验证码 -- 容联云 */
	function sendCode($mobile,$code){
        
		$rs = array('code' => 0, 'msg' => '', 'info' => array());
        
		$config = getConfigPri();
        
        if(!$config['sendcode_switch']){
            $rs['code']=667;
			$rs['msg']='123456';
            return $rs;
        }
        
        require_once API_ROOT.'/../sdk/ronglianyun/CCPRestSDK.php';
        
        //主帐号
        $accountSid= $config['ccp_sid'];
        //主帐号Token
        $accountToken= $config['ccp_token'];
        //应用Id
        $appId=$config['ccp_appid'];
        //请求地址，格式如下，不需要写https://
        $serverIP='app.cloopen.com';
        //请求端口 
        $serverPort='8883';
        //REST版本号
        $softVersion='2013-12-26';
        
        $tempId=$config['ccp_tempid'];
        
        file_put_contents(API_ROOT.'/../data/sendCode_ccp_'.date('Y-m-d').'.txt',date('Y-m-d H:i:s').' 提交参数信息 post_data: accountSid:'.$accountSid.";accountToken:{$accountToken};appId:{$appId};tempId:{$tempId}\r\n",FILE_APPEND);

        $rest = new REST($serverIP,$serverPort,$softVersion);
        $rest->setAccount($accountSid,$accountToken);
        $rest->setAppId($appId);
        
        $datas=[];
        $datas[]=$code;
        
        $result = $rest->sendTemplateSMS($mobile,$datas,$tempId);
        file_put_contents(API_ROOT.'/../data/sendCode_ccp_'.date('Y-m-d').'.txt',date('Y-m-d H:i:s').' 提交参数信息 result:'.json_encode($result)."\r\n",FILE_APPEND);
        
         if($result == NULL ) {
            $rs['code']=1002;
			$rs['msg']="获取失败";
            return $rs;
         }
         if($result->statusCode!=0) {
            //echo "error code :" . $result->statusCode . "<br>";
            //echo "error msg :" . $result->statusMsg . "<br>";
            //TODO 添加错误处理逻辑
            $rs['code']=1002;
			//$rs['msg']=$gets['SubmitResult']['msg'];
			$rs['msg']="获取失败";
            return $rs;
         }


		return $rs;
	}
	
	/* 检测文件后缀 */
	function checkExt($filename){
		$config=array("jpg","png","jpeg");
		$ext   =   pathinfo(strip_tags($filename), PATHINFO_EXTENSION);
		 
		return empty($config) ? true : in_array(strtolower($ext), $config);
	}	    
	
	/* 密码检查 */
	function passcheck($user_pass) {
		$num = preg_match("/^[a-zA-Z]+$/",$user_pass);
		$word = preg_match("/^[0-9]+$/",$user_pass);
		$check = preg_match("/^[a-zA-Z0-9]{6,12}$/",$user_pass);
		if($num || $word ){
			return 2;
		}else if(!$check){
			return 0;
		}		
		return 1;
	}
	
	/* 密码加密 */
	function setPass($pass){
		$authcode='rCt52pF2cnnKNB3Hkp';
		$pass="###".md5(md5($authcode.$pass));
		return $pass;
	}	
	
	/* 公共配置 */
	function getConfigPub() {
		$key='getConfigPub';
		$config=getcaches($key);

		if(!$config){
			$config= DI()->notorm->options
					->select('option_value')
					->where("option_name='configpub'")
					->fetchOne();
            $config=json_decode($config['option_value'],true);
			setcaches($key,$config);
		}

		if(is_array($config['login_type'])){
            
        }else if($config['login_type']){
            $config['login_type']=preg_split('/,|，/',$config['login_type']);
        }else{
            $config['login_type']=array();
        }
        
        if(is_array($config['share_type'])){
            
        }else if($config['share_type']){
            $config['share_type']=preg_split('/,|，/',$config['share_type']);
        }else{
            $config['share_type']=array();
        }
        
        $config['watermark']=get_upload_path($config['watermark']);
        
		return 	$config;
	}		
	
	/* 私密配置 */
	function getConfigPri() {
		$key='getConfigPri';
		$config=getcaches($key);

		if(!$config){
			$config= DI()->notorm->options
					->select('option_value')
					->where("option_name='configpri'")
					->fetchOne();
            $config=json_decode($config['option_value'],true);
			setcaches($key,$config);
		}
		return 	$config;
	}		
	
	/**
	 * 返回带协议的域名
	 */
	function get_host(){
		$config=getConfigPub();
		return $config['site'];
	}	
	
	/**
	 * 转化数据库保存的文件路径，为可以访问的url
	 */
	function get_upload_path($file){
        if($file==''){
            return $file;
        }
        
		if(strpos($file,"http")===0){
			return html_entity_decode($file);
			//return setTxUrl(html_entity_decode($file)); //腾讯云存储为私有读写时需要调用该方法获取签名验证
		}else if(strpos($file,"/")===0){
			$filepath= get_host().$file;
			return html_entity_decode($filepath);
		}else{
			//$space_host= DI()->config->get('app.Qiniu.space_host');
			//$filepath=$space_host."/".$file;
			
			$configpri=getConfigPri();
			if($configpri['cloudtype']==1){ //七牛存储
				$space_host=$configpri['qiniu_domain_url'];
			}else{
				$space_host="http://";
			}
			
			$filepath=$space_host.$file;
			return html_entity_decode($filepath);
		}
	}
	
	/* 判断是否关注 */
	function isAttention($uid,$touid) {

		if($touid==0){  //系统管理员直接返回1，不让用户关注系统管理员
			return "1";
		}

		if($uid<0||$touid<0){
			return "0";
		}

		$isexist=DI()->notorm->users_attention
					->select("*")
					->where('uid=? and touid=?',$uid,$touid)
					->fetchOne();
		if($isexist){
			return  '1';
		}else{
			return  '0';
		}			 
	}
	/* 是否黑名单 */
	function isBlack($uid,$touid) {	
		$isexist=DI()->notorm->users_black
				->select("*")
				->where('uid=? and touid=?',$uid,$touid)
				->fetchOne();
		if($isexist){
			return '1';
		}else{
			return '0';					
		}
	}	
	
	
	/* 判断token */
	function checkToken($uid,$token) {
		$userinfo=getCache("token_".$uid);
		if(!$userinfo){
			$userinfo=DI()->notorm->users
						->select('token,expiretime')
						->where('id = ? and user_type="2"', $uid)
						->fetchOne();	
			setCache("token_".$uid,$userinfo);								
		}
		
		if($userinfo['token']!=$token || $userinfo['expiretime']<time()){
			return 700;				
		}
         
        $isBlackUser=isBlackUser($uid);         
        if($isBlackUser==0){
			return 10020;//账号被禁用
		}
			
        return 	0;				 		
	}	
	
	/* 用户基本信息 */
	function getUserInfo($uid) {

		if($uid==0){
			if($uid==='dsp_admin_1'){

				$config=getConfigPub(); 

				$info['user_nicename']=$config['app_name']."官方";	
				$info['avatar']=get_upload_path('/officeMsg.png');
				$info['avatar_thumb']=get_upload_path('/officeMsg.png');
				$info['id']="dsp_admin_1";

			}else if($uid==='dsp_admin_2'){

				$info['user_nicename']="系统通知";	
				$info['avatar']=get_upload_path('/systemMsg.png');
				$info['avatar_thumb']=get_upload_path('/systemMsg.png');
				$info['id']="dsp_admin_2";
			}else{

				$info['user_nicename']="系统管理员";	
				$info['avatar']=get_upload_path('/default.png');
				$info['avatar_thumb']=get_upload_path('/default_thumb.png');
				$info['id']="0";
			}

			$info['coin']="0";
			$info['sex']="1";
			$info['signature']='';
			$info['province']='';
			$info['city']='城市未填写';
			$info['birthday']='';
			$info['praise']='0';
			$info['fans']='0';
			$info['follows']='0';
			$info['workVideos']='0'; //作品数
			$info['likeVideos']='0'; //喜欢别人的视频数
			$info['age']="年龄未填写";

		}else{

			$info=getCache("userinfo_".$uid);
			$info=false;
			if(!$info){
				$info=DI()->notorm->users
						->select('id,user_nicename,avatar,avatar_thumb,sex,signature,province,city,birthday,age')
						->where('id=? and user_type="2"',$uid)
						->fetchOne();	
				if($info){

					if($info['age']<0){
						$info['age']="年龄未填写";
					}else{
						$info['age'].="岁";
					}

					if($info['city']==""){
						$info['city']="城市未填写";
					}

					$info['avatar']=get_upload_path($info['avatar']);
					$info['avatar_thumb']=get_upload_path($info['avatar_thumb']);
					//setCache("userinfo_".$uid,$info);
					$info['praise']=getPraises($uid);
					$info['fans']=getFans($uid);
					$info['follows']=getFollows($uid);
					$info['workVideos']=getWorks($uid);
					$info['likeVideos']=getLikes($uid);
				}else{

					$info['user_nicename']='用户';
					$info['avatar']=get_upload_path('/default.png');
					$info['avatar_thumb']=get_upload_path('/default_thumb.png');
					$info['sex']='0';
					$info['signature']='这家伙很懒，什么都没留下';
					$info['province']='省份未填写';
					$info['city']='城市未填写';
					$info['birthday']='';
					$info['age']="年龄未填写";
					$info['praise']='0';
					$info['fans']='0';
					$info['follows']='0';
					$info['workVideos']='0';
					$info['likeVideos']='0';
				}
			
			}


		}
		return 	$info;		
	}
	/* 会员等级信息 */
	function getLevelUserinfo($userlevel){
		if($userlevel){
			$userinfo=DI()->notorm->experlevel
					->select("*")
					->where("levelid=".$userlevel)
					->fetchOne();  
			return $userinfo;
		}
		return '0';
	}
	
	
	/* 统计 关注 */
	function getFollows($uid) {
		$count=DI()->notorm->users_attention
				->where('uid=? and touid>0 ',$uid)  //关注系统管理员不显示
				->count();
		return 	$count;
	}

	/* 统计 个人作品数 */
	function getWorks($uid) {
		$count=DI()->notorm->users_video
				->where('uid=? and isdel=0 and status=1 and is_ad=0',$uid)
				->count();
		return 	$count;
	}

	/* 统计 个人喜欢其他人的作品数 */
	function getLikes($uid) {
		

		$count=DI()->notorm->users_video_like
				->where('uid=? and status=1',$uid)  //status=1表示视频状态正常，未被二次拒绝或被下架
				->count();

		return 	$count;
	}			
	
	/* 统计 粉丝 */
	function getFans($uid) {
		$count=DI()->notorm->users_attention
				->where('touid=? ',$uid)
				->count();
		return 	$count;
	}		
	/**
	*  @desc 根据两点间的经纬度计算距离
	*  @param float $lat 纬度值
	*  @param float $lng 经度值
	*/
	function getDistance($lat1, $lng1, $lat2, $lng2){
		$earthRadius = 6371000; //近似地球半径 单位 米
		 /*
		   Convert these degrees to radians
		   to work with the formula
		 */

		$lat1 = ($lat1 * pi() ) / 180;
		$lng1 = ($lng1 * pi() ) / 180;

		$lat2 = ($lat2 * pi() ) / 180;
		$lng2 = ($lng2 * pi() ) / 180;


		$calcLongitude = $lng2 - $lng1;
		$calcLatitude = $lat2 - $lat1;
		$stepOne = pow(sin($calcLatitude / 2), 2) + cos($lat1) * cos($lat2) * pow(sin($calcLongitude / 2), 2);  $stepTwo = 2 * asin(min(1, sqrt($stepOne)));
		$calculatedDistance = $earthRadius * $stepTwo;
		
		$distance=$calculatedDistance/1000;
		if($distance<10){
			$rs=round($distance,2);
		}else if($distance > 1000){
			$rs='>1000';
		}else{
			$rs=round($distance);
		}
		return $rs.'km';
	}
	/* 判断账号是否禁用 */
	function isBan($uid){
		$status=DI()->notorm->users
					->select("user_status")
					->where('id=?',$uid)
					->fetchOne();
		if(!$status || $status['user_status']==0){
			return 0;
		}
		return 1;
	}
	/* 是否认证 */
	function isAuth($uid){
		$status=DI()->notorm->users_auth
					->select("status")
					->where('uid=?',$uid)
					->fetchOne();
		if($status && $status['status']==1){
			return 1;
		}

		return 0;
	}
	/* 过滤字符 */
	function filterField($field){
		$configpri=getConfigPri();
		
		$sensitive_field=$configpri['sensitive_field'];
		
		$sensitive=explode(",",$sensitive_field);
		$replace=array();
		$preg=array();
		foreach($sensitive as $k=>$v){
			if($v){
				$re='';
				$num=mb_strlen($v);
				for($i=0;$i<$num;$i++){
					$re.='*';
				}
				$replace[$k]=$re;
				$preg[$k]='/'.$v.'/';
			}else{
				unset($sensitive[$k]);
			}
		}
		
		return preg_replace($preg,$replace,$field);
	}
	/* 时间差计算 */
	function datetime($time){
		$cha=time()-$time;
		$iz=floor($cha/60);
		$hz=floor($iz/60);
		$dz=floor($hz/24);
		/* 秒 */
		$s=$cha%60;
		/* 分 */
		$i=floor($iz%60);
		/* 时 */
		$h=floor($hz/24);
		/* 天 */
		
		if($cha<60){
			return $cha.'秒前';
		}else if($iz<60){
			return $iz.'分钟前';
		}else if($hz<24){
			return $hz.'小时前';
		}else if($dz<30){
			return $dz.'天前';
		}else{
			return date("Y-m-d",$time);
		}
	}		
	/* 时长格式化 */
	function getSeconds($cha){		 
		$iz=floor($cha/60);
		$hz=floor($iz/60);
		$dz=floor($hz/24);
		/* 秒 */
		$s=$cha%60;
		/* 分 */
		$i=floor($iz%60);
		/* 时 */
		$h=floor($hz/24);
		/* 天 */
		
		if($cha<60){
			return $cha.'秒';
		}else if($iz<60){
			return $iz.'分'.$s.'秒';
		}else if($hz<24){
			return $hz.'小时'.$i.'分'.$s.'秒';
		}else if($dz<30){
			return $dz.'天'.$h.'小时'.$i.'分'.$s.'秒';
		}
	}	
	
	/* 数字格式化 */
	function NumberFormat($num){
		if($num<10000){

		}else if($num<1000000){
			$num=round($num/10000,2).'万';
		}else if($num<100000000){
			$num=round($num/10000,1).'万';
		}else if($num<10000000000){
			$num=round($num/100000000,2).'亿';
		}else{
			$num=round($num/100000000,1).'亿';
		}
		return $num;
	}



	
	/* ip限定 */
	function ip_limit(){
		$configpri=getConfigPri();
		if($configpri['iplimit_switch']==0){
			return 0;
		}
		$date = date("Ymd");
		$ip= ip2long($_SERVER["REMOTE_ADDR"]) ; 
		
		$isexist=DI()->notorm->getcode_limit_ip
				->select('ip,date,times')
				->where(' ip=? ',$ip) 
				->fetchOne();
		if(!$isexist){
			$data=array(
				"ip" => $ip,
				"date" => $date,
				"times" => 1,
			);
			$isexist=DI()->notorm->getcode_limit_ip->insert($data);
			return 0;
		}elseif($date == $isexist['date'] && $isexist['times'] >= $configpri['iplimit_times'] ){
			return 1;
		}else{
			if($date == $isexist['date']){
				$isexist=DI()->notorm->getcode_limit_ip
						->where(' ip=? ',$ip) 
						->update(array('times'=> new NotORM_Literal("times + 1 ")));
				return 0;
			}else{
				$isexist=DI()->notorm->getcode_limit_ip
						->where(' ip=? ',$ip) 
						->update(array('date'=> $date ,'times'=>1));
				return 0;
			}
		}	
	}

	/**极光推送*/
	function jgsend($uid,$videoid,$type){
		/* 极光推送 */
		$configpri=getConfigPri();
		$app_key = $configpri['jpush_key'];
		$master_secret = $configpri['jpush_secret'];
		$userinfo=getUserInfo($uid);
		
		if($app_key && $master_secret){
			require './JPush/autoload.php';

			// 初始化
			$client = new \JPush\Client($app_key, $master_secret,null);
			
			$anthorinfo=array(
				"uid"=>$userinfo['uid'],
				"avatar"=>$userinfo['avatar'],
				"avatar_thumb"=>$userinfo['avatar_thumb'],
				"user_nicename"=>$userinfo['user_nicename'],
				"title"=>$userinfo['title'],
				"city"=>$userinfo['city'],
				"stream"=>'',
				"pull"=>'',
				"thumb"=>$userinfo['thumb'],
			);
			$fansids = getFansIds($uid,$videoid,$type); 
		
			$uids=array_column($fansids,'uid');
			
			$nums=count($uids);	
			$apns_production=false;
			if($configpri['jpush_sandbox']){
				$apns_production=true;
			}

			for($i=0;$i<$nums;){
				$alias=array();
				for($n=0;$n<1000;$n++,$i++){
					if($uids[$i]){
						$alias[]=$uids[$i].'PUSH';								 
					}else{
						break;
					}
				}	 
				try{

					$result = $client->push()
							->setPlatform('all')
							->addAlias($alias)
							->setNotificationAlert('"'.$anthorinfo['user_nicename'].'"发布了新的视频，快来看看吧')
							->iosNotification('"'.$anthorinfo['user_nicename'].'"发布了新的视频，快来看看吧', array(
								'sound' => 'sound.caf',
								'category' => 'jiguang',
								'extras' => array(
									'userinfo' => $anthorinfo
								),
							))
							->androidNotification('"'.$anthorinfo['user_nicename'].'"发布了新的视频，快来看看吧', array(
								'extras' => array(
									'userinfo' => $anthorinfo
								),
							))
							->options(array(
								'sendno' => 100,
								'time_to_live' => 0,
								'apns_production' =>  $apns_production,
							))
							->send();
				} catch (Exception $e) {   
					//file_put_contents('./jpush.txt',date('y-m-d h:i:s').'提交参数信息 设备名:'.json_encode($alias)."\r\n",FILE_APPEND);
					//file_put_contents('./jpush.txt',date('y-m-d h:i:s').'提交参数信息:'.$e."\r\n",FILE_APPEND);
				}					
			}			
		}
		/* 极光推送 */
	}

	//账号是否禁用
	function isBlackUser($uid){

		$userinfo=DI()->notorm->users->where("id=".$uid." and user_status=0")->fetchOne();
		
		if($userinfo){
			return 0;//禁用
		}
		return 1;//启用


	}

	/*检测手机号是否存在*/
	function checkMoblieIsExist($mobile){
		$res=DI()->notorm->users->select("id,user_nicename,user_type")->where("mobile='{$mobile}'")->fetchOne();


		if($res){
			//判断账号是否被禁用
			if($res['user_status']==0){
				return 0;
			}else{
				return 1;
			}
		}else{
			return 0;
		}
		
	}


	/*检测手机号是否可以发送验证码*/
	function checkMoblieCanCode($mobile){
		$res=DI()->notorm->users->select("id,user_nicename,user_type,user_status")->where("mobile='{$mobile}'")->fetchOne();


		if($res){
			//判断账号是否被禁用
			if($res['user_status']==0){
				return 0;
			}else{
				return 1;
			}
		}else{
			return 1;
		}
		
	}

	/*获取用户的视频点赞总数*/
	function getPraises($uid){
		$res=DI()->notorm->users_video->where("uid=?",$uid)->sum("likes");

		if(!$res){
			$res="0";
		}	

		return $res;
	}

	/*获取音乐信息*/
	function getMusicInfo($user_nicename,$musicid){

		$res=DI()->notorm->users_music->select("id,title,author,img_url,length,file_url,use_nums")->where("id=?",$musicid)->fetchOne();

		if(!$res){
			$res=array();
			$res['id']='0';
			$res['title']='';
			$res['author']='';
			$res['img_url']='';
			$res['length']='00:00';
			$res['file_url']='';
			$res['use_nums']='0';
			$res['music_format']='@'.$user_nicename.'创作的原声';

		}else{
			$res['music_format']=$res['title'].'--'.$res['anchor'];
			$res['img_url']=get_upload_path($res['img_url']);
			$res['file_url']=get_upload_path($res['file_url']);
		}

		

		return $res;

	}

	/*距离格式化*/
	function distanceFormat($distance){
		if($distance<1000){
			return $distance.'米';
		}else{

			if(floor($distance/10)<10){
				return number_format($distance/10,1);  //保留一位小数，会四舍五入
			}else{
				return ">10千米";
			}
		}
	}

	/* 视频是否点赞 */
	function ifLike($uid,$videoid){
		$like=DI()->notorm->users_video_like
				->select("id")
				->where("uid='{$uid}' and videoid='{$videoid}'")
				->fetchOne();
		if($like){
			return 1;
		}else{
			return 0;
		}	
	}

	/* 视频是否踩 */
	function ifStep($uid,$videoid){
		$like=DI()->notorm->users_video_step
				->select("id")
				->where("uid='{$uid}' and videoid='{$videoid}'")
				->fetchOne();
		if($like){
			return 1;
		}else{
			return 0;
		}	
	}

	/* 腾讯COS处理 */
    function setTxUrl($url){
        
        if(!strstr($url,'myqcloud')){
            return $url;
        }
        
        $url_a=parse_url($url);
        
        $file=$url_a['path'];
        $signkey='Shanghai0912'; //腾讯云后台设置（控制台->存储桶->域名管理->CDN鉴权配置->鉴权Key）
        $now_time = time();
        $sign=md5($signkey.$file.$now_time);
        
        return $url.'?sign='.$sign.'&t='.$now_time;
        
    }


    /*极光IM用户名前缀（与APP端统一）*/
	function userSendBefore(){
		$before='';
		return $before;
	}

    /*极光IM*/
    function jMessageIM($test,$uid,$adminName){

        //获取后台配置的极光推送app_key和master_secret

        $configPri=getConfigPri();
        $appKey = $configPri['jpush_key'];
        $masterSecret =  $configPri['jpush_secret'];

        if($appKey&&$masterSecret){

            //var_dump(API_ROOT);

            //极光IM
           include_once(API_ROOT.'/public/jmessage/autoload.php');//导入极光IM类库，注意使用require_once和路径写法

            $jm = new \JMessage\JMessage($appKey, $masterSecret); //注意类文件路径写法
            

            //注册管理员
            $admin = new \JMessage\IM\Admin($jm); //注意类文件路径写法
            $nickname="";
            switch($adminName){
                case "dsp_comment":
                $nickname="评论管理";
                break;
                case "dsp_at":
                $nickname="@管理";
                break;
                case "dsp_like":
                $nickname="赞管理";
                break;
                case "dsp_fans":
                $nickname="粉丝管理";
                break;

            }


            $regInfo = [
                'username' => $adminName,
                'password' => $adminName,
                'nickname'=>$nickname
            ];


            $response = $admin->register($regInfo);



            if($response['body']==""||$response['body']['error']['code']==899001){ //新管理员注册成功或管理员已经存在

                //发布消息
                $message = new \JMessage\IM\Message($jm); //注意类文件路径写法

                $user = new \JMessage\IM\User($jm); //注意类文件路径写法

                $before=userSendBefore(); //获取极光用户账号前缀

                $from = [
                    'id'   => $adminName, //短视频系统规定系统通知必须是该账号（与APP保持一致）
                    'type' => 'admin'
                ];

                $msg = [
                   'text' => $test
                ];

                $notification =[
                    'notifiable'=>false  //是否在通知栏展示
                ];

                $target = [
                    'id'   => $before.$uid,
                    'type' => 'single'
                ];

                $response = $message->sendText(1, $from, $target, $msg,$notification,[]);  //最后一个参数代表其他选项数组，主要是配置消息是否离线存储，默认为true
            }

        }

    }
    
    /* 标签信息 */
    function getLabelInfo($labelid){
        $key='LabelInfo_'.$labelid;
        $info=getcaches($key);
        if(!$info){
            $info=DI()->notorm->label
                ->select("id,name,des,thumb")
                ->where('id=?',$labelid)
                ->fetchOne();
            if($info){
                setcaches($key,$info);
            }
        }
        if($info){
            $info['thumb']=get_upload_path($info['thumb']);
        }
        
        return $info;
    }

    /* 校验签名 */
    function checkSign($data,$sign){
        //return 1;
        if($sign==''){
            return 0;
        }
        $key=DI()->config->get('app.sign_key');
        $str='';
        ksort($data);
        foreach($data as $k=>$v){
            $str.=$k.'='.$v.'&';
        }
        $str.=$key;
        $newsign=md5($str);
        
        if($sign==$newsign){
            return 1;
        }
        return 0;
    }
    
    /* 视频数据处理 */
    function handleVideo($uid,$v){
        
			$userinfo=getUserInfo($v['uid']);
			if(!$userinfo){
				$userinfo['user_nicename']="已删除";
			}

			$v['userinfo']=$userinfo;
			$v['datetime']=datetime($v['addtime']);	
			$v['addtime']=date('Y-m-d H:i:s',$v['addtime']);	
			$v['comments']=NumberFormat($v['comments']);	
			$v['likes']=NumberFormat($v['likes']);	
			$v['steps']=NumberFormat($v['steps']);	
            
            $v['islike']='0';	
            $v['isstep']='0';	
            $v['isattent']='0';
            
			if($uid>0){
				$v['islike']=(string)ifLike($uid,$v['id']);	
				$v['isstep']=(string)ifStep($uid,$v['id']);	
			}
            
            if($uid>0 && $uid!=$v['uid']){
                $v['isattent']=(string)isAttention($uid,$v['uid']);	
            }

			$v['musicinfo']=getMusicInfo($userinfo['user_nicename'],$v['music_id']);	
			$v['thumb']=get_upload_path($v['thumb']);
			$v['thumb_s']=get_upload_path($v['thumb_s']);
			$v['href']=encryption(get_upload_path($v['href']));
			$v['href_w']=encryption(get_upload_path($v['href_w']));
            
            if($v['ad_url']){
				$v['ad_url']=get_upload_path($v['ad_url']);
			}
            if($v['ad_endtime']<time()){
                $v['ad_url']='';
            }
            
            /* 商品 */
            $goodsinfo=(object)[];
            if($v['isgoods']==1){
                $goodsinfo=DI()->notorm->shop_goods
                            ->select("name,href,thumb,old_price,price,des")
                            ->where('videoid=?',$v['id'])
                            ->fetchOne();
                if($goodsinfo){
                    $goodsinfo['thumb']=get_upload_path($goodsinfo['thumb']);
                }else{
                    $v['isgoods']='0';
                }
            }
            $v['goodsinfo']=$goodsinfo;
            
            /* 标签 */
            $label_name='';
            if($v['labelid']>0){
                $labelinfo=getLabelInfo($v['labelid']);
                if($labelinfo){
                    $label_name=$labelinfo['name'];
                }else{
                    $v['labelid']='0';
                }
            }
            $v['label_name']=$label_name;
            
			unset($v['ad_endtime']);
			unset($v['orderno']);
			unset($v['isdel']);
			unset($v['show_val']);

        return $v;
    }
    
    function encryption($code){
		$str = 'HmTPvkJ3otK5gp.COdrAi:q09Z62ash-QGn8VFNIlbfM/D74WjS_EUzYuw?1ecxXyLRB';
		$strl=strlen($str);
        
	   	$len = strlen($code);

      	$newCode = '';
	   	for($i=0;$i<$len;$i++){
         	for($j=0;$j<$strl;$j++){
            	if($str[$j]==$code[$i]){
               		if(($j+1)==$strl){
                   		$newCode.=$str[0];
	               	}else{
	                   	$newCode.=$str[$j+1];
	               	}
	            }
         	}
      	}
      	return $newCode;
	}	
    
    function decrypt($code){
		$str = 'HmTPvkJ3otK5gp.COdrAi:q09Z62ash-QGn8VFNIlbfM/D74WjS_EUzYuw?1ecxXyLRB';
		$strl=strlen($str);

	   	$len = strlen($code);

      	$newCode = '';
	   	for($i=0;$i<$len;$i++){
     		for($j=0;$j<$strl;$j++){
        		if($str[$j]==$code[$i]){
	           		if($j-1<0){
	        			$newCode.=$str[$strl-1];
	               	}else{
						$newCode.=$str[$j-1];
	               	}
            	}
         	}
      	}
      	return $newCode;
	}