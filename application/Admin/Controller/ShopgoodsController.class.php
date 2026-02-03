<?php

/**
 * 商品
 */
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class ShopgoodsController extends AdminbaseController {
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
             $map['uid|videoid|name']=array("like","%".$_REQUEST['keyword']."%"); 
             $_GET['keyword']=$_REQUEST['keyword'];
         }		
			
    	$auth=M("shop_goods");
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
                   
				   $v['thumb']= get_upload_path($v['thumb']);

				   $lists[$k]= $v;
					 
			}			
			
    	$this->assign('lists', $lists);
    	$this->assign('formget', $_GET);
    	$this->assign("page", $page->show('Admin'));
    	
    	$this->display();
    }
		
		function del(){
            $id=intval($_GET['id']);
            if($id){
                $result=M("shop_goods")->where("id='{$id}'")->delete();
                    if($result){
                            $this->success('删除成功');
                     }else{
                            $this->error('删除失败');
                     }
            }else{				
                $this->error('数据传入失败！');
            }
            $this->display();
		}
    
}
