<?php
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class MainController extends AdminbaseController {
	
    public function index(){
    	//会员统计
			$users=M("users");
			$users_auth=M("users_auth");
			$users_admin=array();
			$users_admin['register']=$users->where("id>0 and user_type=2")->count();
			$users_admin['auth']=$users_auth->where("status=1")->count();
			$this->assign('users_admin', $users_admin);
				
    	$this->display();
    }
}