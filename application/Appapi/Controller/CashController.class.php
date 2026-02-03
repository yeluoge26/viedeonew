<?php
/**
 * 提现记录
 */
namespace Appapi\Controller;
use Common\Controller\HomebaseController;
class CashController extends HomebaseController {
    
    var $action=array(
        '1'=>'完成邀请',
        '2'=>'观看视频',
    );

	function record(){
        
        $uid=(int)I('uid');
		$token=I("token");       
		if(checkToken($uid,$token)==700){
			$this->assign("reason",'您的登陆状态失效，请重新登陆！');
			$this->display(':error');
			exit;
		}
        
        $list=M("votes_record")->where(["uid|touid"=>$uid])->order("id desc")->limit(0,50)->select();
		foreach($list as $k=>$v){
            
            $userinfo=getUserInfo($v['uid']);
            $v['userinfo']=$userinfo;
            
            if($uid==$v['uid']){
                /* 自己的收益 */
                $total=$v['votes'];
            }else{
                /* 下级奖励 */
                $total=$v['touid_votes'];
            }
            $v['total']=$total;
			$v['action_name']=$this->action[$v['action']];
            
            $list[$k]=$v;
		}
        
        $this->assign('list',$list);

		$this->display();
        

	}

	function record_more(){
		$uid=(int)I('uid');
		$token=I('token');
		
		$result=array(
			'data'=>array(),
			'nums'=>0,
			'isscroll'=>0,
		);
	
		if(checkToken($uid,$token)==700){
			echo json_encode($result);
			exit;
		} 
		
		$p=I('page');
		$pnums=50;
		$start=($p-1)*$pnums;

        $list=M("votes_record")->where(["uid|touid"=>$uid])->order("id desc")->limit($start,$pnums)->select();
		foreach($list as $k=>$v){
            
            $userinfo=getUserInfo($v['uid']);
            
            if($uid==$v['uid']){
                /* 自己的收益 */
                $total=$v['votes'];
            }else{
                /* 下级奖励 */
                $total=$v['touid_votes'];
            }
            $v['total']=$total;
			$v['action_name']=$this->action[$v['action']];
            
            $list[$k]=$v;
		}

		
		$nums=count($list);
		if($nums<$pnums){
			$isscroll=0;
		}else{
			$isscroll=1;
		}
		
		$result=array(
			'data'=>$list,
			'nums'=>$nums,
			'isscroll'=>$isscroll,
		);

		echo json_encode($result);
		exit;
	}

    var $status=array(
        '0'=>'审核中',
        '1'=>'成功',
        '2'=>'失败',
    );
    
	function cash(){
        
        $uid=(int)I('uid');
		$token=I("token");       
		if(checkToken($uid,$token)==700){
			$this->assign("reason",'您的登陆状态失效，请重新登陆！');
			$this->display(':error');
			exit;
		}
        
        $list=M("users_cashrecord")->where(["uid"=>$uid])->order("addtime desc")->limit(0,50)->select();
		foreach($list as $k=>$v){

			$list[$k]['addtime']=date('Y.m.d',$v['addtime']);
			$list[$k]['status_name']=$this->status[$v['status']];
		}
        
        $this->assign('list',$list);

		$this->display();
	}

	function cash_more(){
		$uid=(int)I('uid');
		$token=I('token');
		
		$result=array(
			'data'=>array(),
			'nums'=>0,
			'isscroll'=>0,
		);
	
		if(checkToken($uid,$token)==700){
			echo json_encode($result);
			exit;
		} 
		
		$p=I('page');
		$pnums=50;
		$start=($p-1)*$pnums;

        $list=M("users_cashrecord")->where(["uid"=>$uid])->order("addtime desc")->limit($start,$pnums)->select();
		foreach($list as $k=>$v){

			$list[$k]['addtime']=date('Y.m.d',$v['addtime']);
			$list[$k]['status_name']=$this->status[$v['status']];
		}
		
		$nums=count($list);
		if($nums<$pnums){
			$isscroll=0;
		}else{
			$isscroll=1;
		}
		
		$result=array(
			'data'=>$list,
			'nums'=>$nums,
			'isscroll'=>$isscroll,
		);

		echo json_encode($result);
		exit;
	}



}