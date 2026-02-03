<?php

class Model_Popular extends PhalApi_Model_NotORM {
	/* 获取余额 */
	public function getCoin($uid) {
        
		$info= DI()->notorm->users
                ->select('coin')
                ->where('id=?',$uid)
                ->fetchOne();

		return $info;
	}

	/* 检测视频 */
	public function checkVideo($uid,$videoid) {
		
        $nowtime=time();
        
		$isexist= DI()->notorm->users_video
                ->select('id,uid,status,isdel,p_expire')
                ->where('id=?',$videoid)
                ->fetchOne();
        if(!$isexist){
            return '1';
        }

        if($isexist['uid']!=$uid){
            return '2';
        }
        
        if($isexist['status']!=1){
            return '3';
        }
        
        if($isexist['isdel']!=0){
            return '4';
        }
        
        if($isexist['p_expire']> $nowtime){
            return '5';
        }

		return '0';
	}

	/* 订单号 */
	public function setOrder($data) {
		
		$result= DI()->notorm->popular_orders->insert($data);

		return $result;
	}

	/* 余额购买 */
	public function balancePay($data) {
		$rs = array('code' => 0, 'msg' => '购买成功', 'info' => array());
        
        $uid=$data['uid'];
        $videoid=$data['videoid'];
        $money=$data['money'];
        $length=$data['length'];
        $nums=$data['nums'];
        
        $ifok =DI()->notorm->users
                    ->where('id = ? and coin >=?', $uid,$money)
                    ->update(array('coin' => new NotORM_Literal("coin - {$money}") ) );
        if(!$ifok){
            $rs['code'] = 1005;
			$rs['msg'] = '余额不足';
			return $rs;	
        }

        $nowtime=time();
        $expire=$nowtime + $length*60*60;

        
        DI()->notorm->users_video->where("uid='{$uid}' and id={$videoid}")->update(array("p_nums"=>$nums,"p_expire"=>$expire,"p_add"=>$nowtime));
        
        $data2=[
            'uid'=>$uid,
            'videoid'=>$videoid,
            'money'=>$money,
            'length'=>$length,
            'nums'=>$nums,
            'type'=>0,
            'addtime'=>$nowtime,
            'status'=>1,
        ];
        DI()->notorm->popular_orders->insert($data2);

		return $rs;
	}

    /* 投放视频 */
    public function getPutin($uid,$p){
        
        if($p<1){
            $p=1;
        }
        
        $nums=50;
        $start=($p-1)*$nums;
        
        $list=DI()->notorm->popular_orders
                ->select("uid,videoid,money,addtime")
                ->where('uid=? and status=1',$uid)
                ->order("id desc")
                ->limit($start,$nums)
                ->fetchAll();
        foreach($list as $k=>$v){
            $videoinfo=DI()->notorm->users_video
                ->select("*")
                ->where('id=?',$v['videoid'])
                ->fetchOne();
            
            $videoinfo=handleVideo($uid,$videoinfo);
            
            $videoinfo['money']=$v['money'];
            $videoinfo['paytime']=date('Y-m-d',$v['addtime']);
            
            $list[$k]=$videoinfo;
        }
        
        return $list;
        
    }
}
