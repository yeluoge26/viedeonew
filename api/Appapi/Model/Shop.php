<?php

class Model_Shop extends PhalApi_Model_NotORM {
    
	/* 是否认证商铺 */
	public function isShop($uid) {

        $isexist=DI()->notorm->shop_apply
                    ->where('uid=?',$uid)
                    ->fetchOne();
        if($isexist && $isexist['status']==1){
            return '1';
        }
            
		return '0';
	}

	/* 商铺信息 */
	public function getShop($uid) {

        $info=DI()->notorm->shop_apply
                    ->where('uid=? and status=1',$uid)
                    ->fetchOne();
        if($info){
            $info['thumb']=get_upload_path($info['thumb']);
            $info['license']=get_upload_path($info['license']);
            $info['certificate']=get_upload_path($info['certificate']);
            $info['other']=get_upload_path($info['other']);
        }
            
		return $info;
	}

	/* 发布商品 */
	public function setGoods($data) {

        if(!isset($data['addtime'])){
            $data['addtime']=time();
        }
        if(!isset($data['old_price']) || !$data['old_price']){
            $data['old_price']=0;
        }
		
        $result=DI()->notorm->shop_goods
                    ->insert($data);
            
		return $result;
	}

	/* 更新点击 */
	public function upHits($videoid) {

        if(!isset($data['addtime'])){
            $data['addtime']=time();
        }
		
        $result=DI()->notorm->shop_goods
                    ->where('videoid=?',$videoid)
                    ->update(array('hits'=> new NotORM_Literal("hits + 1 ")));
            
		return $result;
	}
    
    /* 商品列表 */
    public function getGoodsList($uid,$p){
        if($p<1){
            $p=1;
        }
        
        $nums=50;
        $start=($p-1)*$nums;
        
        $list=DI()->notorm->shop_goods
                ->select("*")
                ->where('uid=?',$uid)
                ->order("id desc")
                ->limit($start,$nums)
                ->fetchAll();
                
        foreach($list as $k=>$v){
            $v['thumb']=get_upload_path($v['thumb']);
            
            $list[$k]=$v;
        }
        
        return $list;
    }

}
