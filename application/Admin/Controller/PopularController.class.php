<?php

/**
 * 上热门
 */
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class PopularController extends AdminbaseController {
    var $type=[
        '0'=>'余额',
        '1'=>'支付宝',
        '2'=>'微信',
        '3'=>'苹果',
    ];
    
    var $status=[
        '0'=>'未支付',
        '1'=>'已支付',
    ];
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
             $map['uid|videoid']=array("like","%".$_REQUEST['keyword']."%"); 
             $_GET['keyword']=$_REQUEST['keyword'];
         }		
			
    	$auth=M("popular_orders");
    	$count=$auth->where($map)->count();
    	$page = $this->page($count, 20);
            $lists = $auth
            ->where($map)
            ->order("id DESC")
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();
			
			foreach($lists as $k=>$v){
				   $userinfo=M("users")->field("user_nicename")->where("id='{$v[uid]}'")->find();

				   $v['userinfo']= $userinfo;
                   
				   $v['type_name']= $this->type[$v['type']];
				   $v['status_name']= $this->status[$v['status']];

				   $lists[$k]= $v;
					 
			}			
			
    	$this->assign('lists', $lists);
    	$this->assign('formget', $_GET);
    	$this->assign("page", $page->show('Admin'));
    	
    	$this->display();
    }
    
}
