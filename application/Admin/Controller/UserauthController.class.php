<?php

/**
 * 提现
 */
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class UserauthController extends AdminbaseController {
    function index(){

					if($_REQUEST['status']!=''){
						  $map['status']=$_REQUEST['status'];
							$_GET['status']=$_REQUEST['status'];
					 }
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
						 $map['uid|real_name|mobile']=array("like","%".$_REQUEST['keyword']."%"); 
						 $_GET['keyword']=$_REQUEST['keyword'];
					 }		
			
    	$auth=M("users_auth");
    	$count=$auth->where($map)->count();
    	$page = $this->page($count, 20);
    	$lists = $auth
    	->where($map)
    	->order("addtime DESC")
    	->limit($page->firstRow . ',' . $page->listRows)
    	->select();
			
			foreach($lists as $k=>$v){
				   $userinfo=M("users")->field("user_nicename")->where("id='$v[uid]'")->find();

				   $v['userinfo']= $userinfo;
				   	/**隐藏中间字符 */
					$url=$_SERVER['HTTP_HOST'];
					if($url=='demo.semonghuang.org'){
						$user_login=$v['mobile'];
						$length=strlen($user_login);
						$str=$length-6;
						$str1='';
						$start=substr($user_login,3,$str);
						for($i=0;$i<$str;$i++){
							$str1=$str1.'*';
						}
						$new_user_login=substr_replace($user_login,$str1,3,$str);
						$v['mobile']=$new_user_login;
					}


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
						$result=M("users_auth")->where("uid='{$id}'")->delete();				
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

		
		function edit(){
			 	$id=intval($_GET['id']);
					if($id){
						$auth=M("users_auth")->where("uid='{$id}'")->find();
                        $auth['front_view']= get_upload_path($auth['front_view']);
                        $auth['back_view']= get_upload_path($auth['back_view']);
                        $auth['handset_view']= get_upload_path($auth['handset_view']);
						$auth['userinfo']=M("users")->field("user_nicename")->where("id='$auth[uid]'")->find();
						$this->assign('auth', $auth);						
					}else{				
						$this->error('数据传入失败！');
					}								  
					$this->display();				
		}
		
		function edit_post(){
				if(IS_POST){		
            /* if($_POST['status']=='0'){							
							  $this->error('未修改状态');			
						} */
				
					 $auth=M("users_auth");
					 $auth->create();
					 $auth->uptime=time();
					 $result=$auth->save(); 
					 if($result){
						/* if($_POST['status']=='1'){							
							M("users")->where("id='".$_POST['uid']."'")->setfield("isrz",'1');
						}else{
							M("users")->where("id='".$_POST['uid']."'")->setfield("isrz",'0');
						} */
						  $this->success('修改成功',U('Userauth/index'));
					 }else{
						  $this->error('修改失败');
					 }
				}			
		}		
    
}
