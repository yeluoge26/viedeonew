<?php

class Api_Shop extends PhalApi_Api {

	public function getRules() {
		return array(
            'upHits' => array(
				'uid' => array('name' => 'uid', 'type' => 'int', 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'desc' => '用户token'),
                'videoid' => array('name' => 'videoid', 'type' => 'int', 'desc' => '视频ID'),
			),
            
            'getGoodsList' => array(
				'uid' => array('name' => 'uid', 'type' => 'int', 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'desc' => '用户token'),
                'p' => array('name' => 'p', 'type' => 'int', 'default'=>'1', 'desc' => '页码'),
			),
            
            'getShop' => array(
				'touid' => array('name' => 'touid', 'type' => 'int', 'desc' => '用户ID'),
                'p' => array('name' => 'p', 'type' => 'int', 'default'=>'1', 'desc' => '页码'),
			),
		);
	}
	

	/**
	 * 查看
	 * @desc 用于更新商品查看次数
	 * @return int code 操作码，0表示成功
	 * @return array info 
	 * @return string msg 提示信息
	 */
	public function upHits() {
		$rs = array('code' => 0, 'msg' => '', 'info' => array());
		
		$uid=checkNull($this->uid);
        $token=checkNull($this->token);
        $videoid=checkNull($this->videoid);
        
        if($uid<1 || $token=='' || $videoid<1 ){
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
        
		$domain = new Domain_Shop();
		$info = $domain->upHits($videoid);

		return $rs;			
	}

	/**
	 * 商品记录
	 * @desc 用于获取商品列表
	 * @return int code 操作码，0表示成功
	 * @return array info 
	 * @return string info[].videoid 视频ID
	 * @return string info[].name 商品名
	 * @return string info[].thumb 商品封面
	 * @return string info[].hits 查看次数
	 * @return string info[].old_price 原价
	 * @return string info[].price 现价
	 * @return string info[].des 描述
	 * @return string msg 提示信息
	 */
	public function getGoodsList() {
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
        
		$domain = new Domain_Shop();
		$info = $domain->getGoodsList($uid,$p);

        $rs['info']=$info;
		return $rs;			
	}

	/**
	 * 店铺信息
	 * @desc 用于获取店铺信息
	 * @return int code 操作码，0表示成功
	 * @return array info 
	 * @return object info[0].shopinfo 店铺信息
	 * @return string info[0].shopinfo.name 店铺名称
	 * @return string info[0].shopinfo.thumb 封面
	 * @return string info[0].shopinfo.des 描述
	 * @return string info[0].shopinfo.tel 电话
	 * @return array info[0].list 商品列表
	 * @return string info[0].list[].videoid 视频ID
	 * @return string info[0].list[].name 商品名
	 * @return string info[0].list[].thumb 商品封面
	 * @return string info[0].list[].hits 查看次数
	 * @return string info[0].list[].old_price 原价
	 * @return string info[0].list[].price 现价
	 * @return string info[0].list[].des 描述
	 * @return string msg 提示信息
	 */
	public function getShop() {
		$rs = array('code' => 0, 'msg' => '', 'info' => array());
		
		$touid=checkNull($this->touid);
        $p=checkNull($this->p);
        

        
		$domain = new Domain_Shop();
		$info = $domain->getShop($touid);
        
		$list = $domain->getGoodsList($touid,$p);

        $rs['info'][0]['shopinfo']=$info;
        $rs['info'][0]['list']=$list;
        
		return $rs;			
	}
	

}
