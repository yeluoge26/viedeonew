<?php
/**
 * 分销
 */
namespace Appapi\Controller;
use Common\Controller\HomebaseController;
class AgentController extends HomebaseController {
	
	function index(){       
		$uid=(int)I("uid");
		$token=I("token");
		
		if(checkToken($uid,$token)==700){
			$this->assign("reason",'您的登陆状态失效，请重新登陆！');
			$this->display(':error');
			exit;
		} 
		  
		$nowtime=time();
        
        //当天0点
        $today=date("Ymd",$nowtime);
        $today_start=strtotime($today);
        //昨天0点
        $yes_start=strtotime("{$today} - 1 day");

		$User=M("users");
		$Agent=M("agent");

		$agentinfo=array();
		
        $count=$Agent->where(['one'=>$uid])->count();
        
        $map=[];
        $map['touid']=$uid;
        $map['addtime']=array("between",array($yes_start,$today_start));
        
        $votes_record=M("votes_record");
        $total=$votes_record->where($map)->sum('touid_votes');
        if(!$total){
            $total=0;
        }
        
		
		$Agent_profit=M("agent_profit");
		
		$agentprofit=$Agent_profit->where(["uid"=>$uid])->find();
		
		$one_p=$agentprofit['one_p'];
		if(!$one_p){
			$one_p=0;
		}


		$agnet_profit=array(
			'count'=>$count,
			'total'=>$total,
			'one_p'=>$one_p,
		);

		$this->assign("uid",$uid);
		$this->assign("token",$token);
		$this->assign("agnet_profit",$agnet_profit);

		$this->display();
	    
	}

	
	function one(){
		$uid=(int)I("uid");
		$token=I("token");
		
		if(checkToken($uid,$token)==700){
			$this->assign("reason",'您的登陆状态失效，请重新登陆！');
			$this->display(':error');
			exit;
		} 
		  
		$Agent_profit=M("users_agent_profit_recode");
		
		$list=$Agent_profit->field("uid,sum(one_profit) as total")->where(["one_uid"=>$uid])->group("uid")->order("addtime desc")->limit(0,50)->select();
		foreach($list as $k=>$v){
			$list[$k]['userinfo']=getUserInfo($v['uid']);
			$list[$k]['total']=NumberFormat($v['total']);
		}
		$this->assign("uid",$uid);
		$this->assign("token",$token);
		$this->assign("list",$list);
		$this->display();
	}

	function one_more(){
		$uid=(int)I("uid");
		$token=I("token");
		
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

		$Agent_profit=M("users_agent_profit_recode");
		
		$list=$Agent_profit->field("uid,sum(one_profit) as total")->where(["one_uid"=>$uid])->group("uid")->order("addtime desc")->limit($start,$pnums)->select();
		foreach($list as $k=>$v){
			$list[$k]['userinfo']=getUserInfo($v['uid']);
			$list[$k]['total']=NumberFormat($v['total']);
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