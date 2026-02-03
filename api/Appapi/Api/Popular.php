<?php

class Api_Popular extends PhalApi_Api {

	public function getRules() {
		return array(
			'getInfo' => array(
				'uid' => array('name' => 'uid', 'type' => 'int','desc' => '用户ID'),
				'token' => array('name' => 'token', 'type' => 'string', 'desc' => '用户Token'),
			),
            
            'getAliOrder' => array(
				'uid' => array('name' => 'uid', 'type' => 'int', 'desc' => '用户ID'),
				'token' => array('name' => 'token', 'type' => 'string', 'desc' => '用户Token'),
				'videoid' => array('name' => 'videoid', 'type' => 'int', 'desc' => '视频ID'),
				'length' => array('name' => 'length', 'type' => 'int', 'desc' => '时长(小时)'),
				'money' => array('name' => 'money', 'type' => 'string', 'desc' => '金额'),
			),
			'getWxOrder' => array( 
				'uid' => array('name' => 'uid', 'type' => 'int','desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'desc' => '用户Token'),
				'videoid' => array('name' => 'videoid', 'type' => 'int', 'desc' => '视频ID'),
				'length' => array('name' => 'length', 'type' => 'int', 'desc' => '时长(小时)'),
				'money' => array('name' => 'money', 'type' => 'string','desc' => '金额'),
			),
            
            'balancePay' => array( 
				'uid' => array('name' => 'uid', 'type' => 'int', 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'desc' => '用户Token'),
				'videoid' => array('name' => 'videoid', 'type' => 'int', 'desc' => '视频ID'),
				'length' => array('name' => 'length', 'type' => 'int', 'desc' => '时长(小时)'),
				'money' => array('name' => 'money', 'type' => 'string', 'desc' => '金额'),
			),
            
            'getPutin'=>array(
            	'uid' => array('name' => 'uid', 'type' => 'int',  'desc' => '用户ID'),
            	'token' => array('name' => 'token', 'type' => 'string',  'desc' => 'token'),
                'p' => array('name' => 'p', 'type' => 'int', 'default'=>'1','desc' => '页码'),
            )
		);
	}
    
	/**
	 * 上热门信息
	 * @desc 用于 上热门价格
	 * @return int code 操作码，0表示成功
	 * @return array info 
	 * @return string info[0].coin 余额
	 * @return string info[0].base 基数（1元一小时多少个）
	 * @return string info[0].tips 提示内容
	 * @return array info[0].moneylist 价格列表
	 * @return string info[0].moneylist[] 价格
	 * @return array info[0].lengthlist 时长列表
	 * @return string info[0].lengthlist[] 时长
	 * @return array info[0].paylist 支付列表
	 * @return string info[0].paylist[].id 
	 * @return string info[0].paylist[].name 名称
	 * @return string info[0].paylist[].thumb 图标
	 * @return string info[0].paylist[].href 
	 * @return string info[0].aliapp_partner 支付宝合作者身份ID
	 * @return string info[0].aliapp_seller_id 支付宝帐号	
	 * @return string info[0].aliapp_key 支付宝密钥
	 * @return string info[0].wx_appid 开放平台账号AppID
	 * @return string msg 提示信息
	 */	
	public function getInfo() {
		$rs = array('code' => 0, 'msg' => '', 'info' => array());
        
		$uid=checkNull($this->uid);
		$token=checkNull($this->token);
		
        $checkToken=checkToken($uid,$token);
		if($checkToken==700){
			$rs['code'] = $checkToken;
			$rs['msg'] = '您的登陆状态失效，请重新登陆！';
			return $rs;
		}
        
        
		$domain = new Domain_Popular();
		$info = $domain->getCoin($uid);
        
        $configpri=getConfigPri();
		
        $info['base']=$configpri['popular_base'];
        $info['tips']=$configpri['popular_tips'];
        
        $moneylist=['100','200','500','5000','10000'];
        
        
        
        $info['moneylist']=$moneylist;
        
        $length_list=['6','12','24'];
        
		$info['length_list']=$length_list;
        
        
		
		$aliapp_switch=$configpri['aliapp_switch'];
		
		$info['aliapp_partner']=$aliapp_switch==1?$configpri['aliapp_partner']:'';
		$info['aliapp_seller_id']=$aliapp_switch==1?$configpri['aliapp_seller_id']:'';
		$info['aliapp_key']=$aliapp_switch==1?$configpri['aliapp_key']:'';
        
        $wx_switch=$configpri['wx_switch'];
        $info['wx_appid']=$wx_switch==1?$configpri['wx_appid']:'';
        $paylist=[];
        
        if($aliapp_switch){
            $paylist[]=[
                'id'=>'ali',
                'name'=>'支付宝支付',
                'thumb'=>get_upload_path("/public/app/pay/ali.png"),
                'href'=>'',
            ];
        }
        
        if($wx_switch){
            $paylist[]=[
                'id'=>'wx',
                'name'=>'微信支付',
                'thumb'=>get_upload_path("/public/app/pay/wx.png"),
                'href'=>'',
            ];
        }
        
        $paylist[]=[
                'id'=>'balance',
                'name'=>'账户余额支付',
                'thumb'=>get_upload_path("/public/app/pay/coin.png"),
                'href'=>'',
            ];
        
        $info['paylist'] =$paylist;
        
		$rs['info'][0]=$info;
		return $rs;
	}
	/* 获取订单号 */
	protected function getOrderid($uid){
		$orderid=$uid.'_'.date('YmdHis').rand(100,999);
		return $orderid;
	}

	/**
	 * 微信支付
	 * @desc 用于 微信支付 获取订单号
	 * @return int code 操作码，0表示成功
	 * @return array info 
	 * @return string info[0] 支付信息
	 * @return string msg 提示信息
	 */
	public function getWxOrder() {
		$rs = array('code' => 0, 'msg' => '', 'info' => array());
		

		$uid=checkNull($this->uid);
		$token=checkNull($this->token);
		$videoid=checkNull($this->videoid);
		$length=checkNull($this->length);
		$money=checkNull($this->money);
        
        if($uid<1 || $token=='' || $videoid<1 || $length<1 || $money<1){
            $rs['code'] = 1001;
			$rs['msg'] = '信息错误';
			return $rs;
        }

        $checkToken=checkToken($uid,$token);
		if($checkToken==700){
			$rs['code'] = $checkToken;
			$rs['msg'] = '您的登陆状态失效，请重新登陆！';
			return $rs;
		}
        
        $domain = new Domain_Popular();
        $video_rs = $domain->checkVideo($uid,$videoid);
        if($video_rs==1){
            $rs['code'] = 1011;
			$rs['msg'] = '视频不存在';
			return $rs;
        }
        if($video_rs==2){
            $rs['code'] = 1012;
			$rs['msg'] = '不是您发布的视频';
			return $rs;
        }
        if($video_rs==3){
            $rs['code'] = 1013;
			$rs['msg'] = '视频未审核通过';
			return $rs;
        }
        if($video_rs==4){
            $rs['code'] = 1014;
			$rs['msg'] = '视频已被下架';
			return $rs;
        }
        if($video_rs==5){
            $rs['code'] = 1015;
			$rs['msg'] = '视频已上热门';
			return $rs;
        }
        
		$orderid=$this->getOrderid($uid);
		$type=2;

		$configpri = getConfigPri(); 
		$configpub = getConfigPub(); 

		 //配置参数检测
					
		if($configpri['wx_appid']== "" || $configpri['wx_mchid']== "" || $configpri['wx_key']== ""){
			$rs['code'] = 1002;
			$rs['msg'] = '微信未配置';
			return $rs;					 
		}
        
        $base=$configpri['popular_base'];
        
        if($base<1){
            $rs['code'] = 1004;
			$rs['msg'] = '配置错误';
			return $rs;	
        }
        
        $nums=$money * $length * $base;
		
		$orderinfo=array(
			"uid"=>$uid,
			"videoid"=>$videoid,
			"money"=>$money,
			"length"=>$length,
			"nums"=>$nums,
			"orderno"=>$orderid,
			"type"=>$type,
			"status"=>0,
			"addtime"=>time()
		);

		
		
		$info = $domain->setOrder($orderinfo);
		if(!$info){
			$rs['code']=1001;
			$rs['msg']='订单生成失败';
            return $rs;	
		}

			 
		$noceStr = md5(rand(100,1000).time());//获取随机字符串
		$time = time();
			
		$paramarr = array(
			"appid"       =>   $configpri['wx_appid'],
			"body"        =>    "购买热门",
			"mch_id"      =>    $configpri['wx_mchid'],
			"nonce_str"   =>    $noceStr,
			"notify_url"  =>    $configpub['site'].'/Appapi/popularback/notify_wx',
			"out_trade_no"=>    $orderid,
			"total_fee"   =>    $money*100, 
			"trade_type"  =>    "APP"
		);
		$sign = $this -> sign($paramarr,$configpri['wx_key']);//生成签名
		$paramarr['sign'] = $sign;
		$paramXml = "<xml>";
		foreach($paramarr as $k => $v){
			$paramXml .= "<" . $k . ">" . $v . "</" . $k . ">";
		}
		$paramXml .= "</xml>";
			 
		$ch = curl_init ();
		@curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查  
		@curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, true);  // 从证书中检查SSL加密算法是否存在  
		@curl_setopt($ch, CURLOPT_URL, "https://api.mch.weixin.qq.com/pay/unifiedorder");
		@curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		@curl_setopt($ch, CURLOPT_POST, 1);
		@curl_setopt($ch, CURLOPT_POSTFIELDS, $paramXml);
		@$resultXmlStr = curl_exec($ch);
		if(curl_errno($ch)){
			//print curl_error($ch);
			file_put_contents('./wxpay.txt',date('y-m-d H:i:s').' 提交参数信息 ch:'.json_encode(curl_error($ch))."\r\n",FILE_APPEND);
		}
		curl_close($ch);

		$result2 = $this->xmlToArray($resultXmlStr);
        
        if($result2['return_code']=='FAIL'){
            $rs['code']=1005;
			$rs['msg']=$result2['return_msg'];
            return $rs;	
        }
		$time2 = time();
		$prepayid = $result2['prepay_id'];
		$sign = "";
		$noceStr = md5(rand(100,1000).time());//获取随机字符串
		$paramarr2 = array(
			"appid"     =>  $configpri['wx_appid'],
			"noncestr"  =>  $noceStr,
			"package"   =>  "Sign=WXPay",
			"partnerid" =>  $configpri['wx_mchid'],
			"prepayid"  =>  $prepayid,
			"timestamp" =>  $time2
		);
		$paramarr2["sign"] = $this -> sign($paramarr2,$configpri['wx_key']);//生成签名
		
		$rs['info'][0]=$paramarr2;
		return $rs;			
	}		
	
	/**
	* sign拼装获取
	*/
	protected function sign($param,$key){
		$sign = "";
		foreach($param as $k => $v){
			$sign .= $k."=".$v."&";
		}
		$sign .= "key=".$key;
		$sign = strtoupper(md5($sign));
		return $sign;
	
	}
	/**
	* xml转为数组
	*/
	protected function xmlToArray($xmlStr){
		$msg = array(); 
		$postStr = $xmlStr; 
		$msg = (array)simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA); 
		return $msg;
	}	
		
	/**
	 * 支付宝支付
	 * @desc 用于支付宝支付 获取订单号
	 * @return int code 操作码，0表示成功
	 * @return array info 
	 * @return string info[0].orderid 订单号
	 * @return string msg 提示信息
	 */
	public function getAliOrder() {
		$rs = array('code' => 0, 'msg' => '', 'info' => array());
		
		$uid=checkNull($this->uid);
		$token=checkNull($this->token);
		$videoid=checkNull($this->videoid);
		$length=checkNull($this->length);
		$money=checkNull($this->money);
        
        if($uid<1 || $token=='' || $videoid<1 || $length<1 || $money<1){
            $rs['code'] = 1001;
			$rs['msg'] = '信息错误';
			return $rs;
        }

        $checkToken=checkToken($uid,$token);
		if($checkToken==700){
			$rs['code'] = $checkToken;
			$rs['msg'] = '您的登陆状态失效，请重新登陆！';
			return $rs;
		}
        
        $domain = new Domain_Popular();
        $video_rs = $domain->checkVideo($uid,$videoid);
        if($video_rs==1){
            $rs['code'] = 1011;
			$rs['msg'] = '视频不存在';
			return $rs;
        }
        if($video_rs==2){
            $rs['code'] = 1012;
			$rs['msg'] = '不是您发布的视频';
			return $rs;
        }
        if($video_rs==3){
            $rs['code'] = 1013;
			$rs['msg'] = '视频未审核通过';
			return $rs;
        }
        if($video_rs==4){
            $rs['code'] = 1014;
			$rs['msg'] = '视频已被下架';
			return $rs;
        }
        if($video_rs==5){
            $rs['code'] = 1015;
			$rs['msg'] = '视频已上热门';
			return $rs;
        }
		
		$orderid=$this->getOrderid($uid);
		$type=1;
        
        $configpri=getConfigPri();
		
		$base=$configpri['popular_base'];
        
        if($base<1){
            $rs['code'] = 1004;
			$rs['msg'] = '配置错误';
			return $rs;	
        }
        
        $nums=$money * $length * $base;
		
		$orderinfo=array(
			"uid"=>$uid,
			"videoid"=>$videoid,
			"money"=>$money,
			"length"=>$length,
			"nums"=>$nums,
			"orderno"=>$orderid,
			"type"=>$type,
			"status"=>0,
			"addtime"=>time()
		);

		
		
		$info = $domain->setOrder($orderinfo);

		if(!$info){
			$rs['code']=1001;
			$rs['msg']='订单生成失败';
		}
		
		$rs['info'][0]['orderid']=$orderid;
		return $rs;
	}

	/**
	 * 余额支付
	 * @desc 用于余额支付
	 * @return int code 操作码，0表示成功
	 * @return array info 
	 * @return string info[0].orderid 订单号
	 * @return string msg 提示信息
	 */
	public function balancePay() {
		$rs = array('code' => 0, 'msg' => '', 'info' => array());
		
		$uid=checkNull($this->uid);
		$token=checkNull($this->token);
		$videoid=checkNull($this->videoid);
		$length=checkNull($this->length);
		$money=checkNull($this->money);
        
        if($uid<1 || $token=='' || $videoid<1 || $length<1 || $money<1){
            $rs['code'] = 1001;
			$rs['msg'] = '信息错误';
			return $rs;
        }

        $checkToken=checkToken($uid,$token);
		if($checkToken==700){
			$rs['code'] = $checkToken;
			$rs['msg'] = '您的登陆状态失效，请重新登陆！';
			return $rs;
		}
        
        $domain = new Domain_Popular();
        $video_rs = $domain->checkVideo($uid,$videoid);
        if($video_rs==1){
            $rs['code'] = 1011;
			$rs['msg'] = '视频不存在';
			return $rs;
        }
        if($video_rs==2){
            $rs['code'] = 1012;
			$rs['msg'] = '不是您发布的视频';
			return $rs;
        }
        if($video_rs==3){
            $rs['code'] = 1013;
			$rs['msg'] = '视频未审核通过';
			return $rs;
        }
        if($video_rs==4){
            $rs['code'] = 1014;
			$rs['msg'] = '视频已被下架';
			return $rs;
        }
        if($video_rs==5){
            $rs['code'] = 1015;
			$rs['msg'] = '视频已上热门';
			return $rs;
        }
		
		$configpri=getConfigPri();
		
		$base=$configpri['popular_base'];
        
        if($base<1){
            $rs['code'] = 1004;
			$rs['msg'] = '配置错误';
			return $rs;	
        }
        
        $nums=$money * $length * $base;
		
		$orderinfo=array(
			"uid"=>$uid,
			"videoid"=>$videoid,
			"money"=>$money,
			"length"=>$length,
			"nums"=>$nums,
		);
		
		$info = $domain->balancePay($orderinfo);

		return $info;
	}

	/**
	 * 投放订单
	 * @desc 用于获取投放订单
	 * @return int code 操作码，0表示成功
	 * @return array info 
	 * @return string info[0].coin 余额
	 * @return array info[0].list 列表
	 * @return string info[0].list[].money 价格
	 * @return string info[0].list[].paytime 下单时间
	 * @return string msg 提示信息
	 */
    public function getPutin() {
		$rs = array('code' => 0, 'msg' => '', 'info' => array());
        
		$uid=checkNull($this->uid);
		$token=checkNull($this->token);
		$p=checkNull($this->p);
		
        $checkToken=checkToken($uid,$token);
		if($checkToken==700){
			$rs['code'] = $checkToken;
			$rs['msg'] = '您的登陆状态失效，请重新登陆！';
			return $rs;
		}
        
        $domain = new Domain_Popular();
		$info = $domain->getCoin($uid);
        
		$list = $domain->getPutin($uid,$p);
        
        
		$rs['info'][0]['coin']=$info['coin'];
		$rs['info'][0]['list']=$list;
		return $rs;
	}
}
