<?php

class Api_Agent extends PhalApi_Api {

	public function getRules() {
		return array(
            'setViewLength' => array(
				'uid' => array('name' => 'uid', 'type' => 'int', 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'desc' => '用户token'),
                'length' => array('name' => 'length', 'type' => 'int', 'desc' => '时长（秒）'),
                'sign' => array('name' => 'sign', 'type' => 'string', 'string' => '签名'),
			),
		);
	}
	

	/**
	 * 观看视频奖励
	 * @desc 用于 获取引导页信息
	 * @return int code 操作码，0表示成功
	 * @return array info 
	 * @return string msg 提示信息
	 */
	public function setViewLength() {
		$rs = array('code' => 0, 'msg' => '', 'info' => array());
		
		$uid=checkNull($this->uid);
        $token=checkNull($this->token);
        $length=checkNull($this->length);
        $sign=checkNull($this->sign);
        
        if($uid<1 || $token=='' || $length<1 || $sign=='' ){
            $rs['code'] = 1001;
			$rs['msg'] = '信息错误';
			return $rs;
        }
        
        $checkdata=array(
            'uid'=>$uid,
            'token'=>$token,
            'length'=>$length,
        );
        
        $issign=checkSign($checkdata,$sign);
        if(!$issign){
            $rs['code']=1001;
			$rs['msg']='签名错误';
			return $rs;	
        }
        
		$domain = new Domain_Agent();
		$info = $domain->setViewLength($uid,$length);

		return $rs;			
	}		
	

}
