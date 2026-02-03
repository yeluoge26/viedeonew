<?php

/**
 * 管理员手动充值记录
 */
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class ManualController extends AdminbaseController {
    function index(){
			if($_REQUEST['start_time']!=''){
				$map['addtime']=array("gt",strtotime($_REQUEST['start_time']));
				$_GET['start_time']=$_REQUEST['start_time'];
			}			 
			if($_REQUEST['end_time']!=''){	 
				$map['addtime']=array("lt",strtotime($_REQUEST['end_time']));
				$_GET['end_time']=$_REQUEST['end_time'];
			}
			if($_REQUEST['start_time']!='' && $_REQUEST['end_time']!='' ){	 
				$map['addtime']=array("between",array(strtotime($_REQUEST['start_time']),strtotime($_REQUEST['end_time'])));
				$_GET['start_time']=$_REQUEST['start_time'];
				$_GET['end_time']=$_REQUEST['end_time'];
			}
			if($_REQUEST['keyword']!=''){
				$map['touid']=array("like","%".$_REQUEST['keyword']."%"); 
				$_GET['keyword']=$_REQUEST['keyword'];
			}
			
    	$live=M("users_charge_admin");
    	$count=$live->where($map)->count();
    	$page = $this->page($count, 20);
    	$lists = $live
    	->where($map)
    	->order("id DESC")
    	->limit($page->firstRow . ',' . $page->listRows)
    	->select();
			$coin = $live->where($map)->sum("coin");
			foreach($lists as $k=>$v){
				 $userinfo=M("users")->field("user_login,user_nicename")->where(["id"=>$v['touid']])->find();
				 $lists[$k]['user_login']=$userinfo['user_login'];
				 $lists[$k]['user_nicename']=$userinfo['user_nicename'];
				
			}
    	$this->assign('lists', $lists);
    	$this->assign('coin', $coin);
			$this->assign('formget', $_GET);
    	$this->assign("page", $page->show('Admin'));
    	
    	$this->display();
    }
		
		function add(){	  
					$this->display();				
		}		
    
    public function add_post() { 
		
        $user_login = (int)I('user_login');
        $coin = I('coin');
				$users_obj= M("Users");
				if($user_login=='' || $coin==''){
					$this->error("信息不全，请填写完整");
					
				}
				$uid=$users_obj->where(["id"=>$user_login])->getField("id");
				if(!$uid){
					$this->error("会员不存在，请更正");
					
				}
				
    		$id=$_SESSION['ADMIN_ID'];
    		$user=$users_obj->where(["id"=>$id])->find();			
				
        $result=M("users_charge_admin")->add(array("touid"=>$uid,"coin"=>$coin,"addtime"=>time(),"admin"=>$user['user_login'],"ip"=>get_client_ip(0,true)));

        if ($result) {
					  M("users")->where(["id"=>$user_login])->setInc("coin",$coin);
					  
            $this->success("充值成功！");
        } else {
            $this->error("充值失败！");
        }
    }
    

}
