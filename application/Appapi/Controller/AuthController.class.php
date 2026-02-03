<?php
/**
 * 会员认证
 */
namespace Appapi\Controller;
use Common\Controller\HomebaseController;

class AuthController extends HomebaseController {
	
	public function index(){
		$uid=I('uid');
		$token=I("token");       
		if(checkToken($uid,$token)==700){
			$this->assign("reason",'您的登陆状态失效，请重新登陆！');
			$this->display(':error');
			exit;
		}       
		      
		$this->assign("uid",$uid);
		$this->assign("token",$token);
			 
		$info=M("users_auth")->where(['uid'=>$uid])->find();

		if($info){

			$info['front1']=get_upload_path($info['front_view']);
			$info['back1']=get_upload_path($info['back_view']);
			$info['hand1']=get_upload_path($info['handset_view']);
            
            $this->assign("info",$info);

		}

		$this->display();
	    
	}


	/*认证保存*/
	public function auth_save(){

		$rs=array("code"=>0,"msg"=>"申请成功","info"=>array());
        
		$uid=checkNull(I("uid"));
		$token=checkNull(I("token"));
		$realname=checkNull(I("realname"));
		$phone=checkNull(I("phone"));
		$cardno=checkNull(I("cardno"));
		$front=checkNull(I("front"));
		$back=checkNull(I("back"));
		$hand=checkNull(I("hand"));
        
        if(checkToken($uid,$token)==700){
            $rs['code']=700;
            $rs['msg']='您的登陆状态失效，请重新登陆！';
            echo json_encode($rs);
			exit;
		}
        
        $auth=M("users_auth")->where(['uid'=>$uid])->find();
        if($auth && $auth['status']==0){
            $rs['code']=1001;
			$rs['msg']="认证审核中，不能申请";
			echo json_encode($rs);
            exit;
        }

		if($realname==""){
			$rs['code']=1001;
			$rs['msg']="请输入姓名";
			echo json_encode($rs);
            exit;
		}

        if($cardno==""){
			$rs['code']=1002;
			$rs['msg']="请输入身份证号码";
			echo json_encode($rs);
            exit;
		}

		if($phone==""){
			$rs['code']=1002;
			$rs['msg']="请输入手机号码";
			echo json_encode($rs);
            exit;
		}
        
        if($front==""){
			$rs['code']=1002;
			$rs['msg']="请上传证件正面";
			echo json_encode($rs);
            exit;
		}
        
        if($back==""){
			$rs['code']=1002;
			$rs['msg']="请上传证件反面";
			echo json_encode($rs);
            exit;
		}
        
        if($hand==""){
			$rs['code']=1002;
			$rs['msg']="请上传手持证件正面照";
			echo json_encode($rs);
            exit;
		}
        
        $nowtime=time();
        
        $data=[
            'uid'=>$uid,
            'real_name'=>$realname,
            'cer_no'=>$cardno,
            'mobile'=>$phone,
            'front_view'=>$front,
            'back_view'=>$back,
            'handset_view'=>$hand,
            'addtime'=>$nowtime,
            'uptime'=>$nowtime,
            'status'=>0,
            'reason'=>'',
        ];


		if($auth){
			$result=M("users_auth")->where(['uid'=>$uid])->save($data);
		}else{
			$result=M("users_auth")->add($data);
		}

		echo json_encode($rs);
        exit;
	}
    
    /* 图片上传 */
	public function upload(){
	
        $files["file"]=$_FILES["file"];
        $src=$files["tmp_name"];
        $type='img';
        
        $rs=adminUploadFiles($files,$src,$type);
        if($rs['code']!=0){
            echo json_encode(array("code"=>0,'file'=>'','msg'=>$rs['msg']));
            exit;
        }
        
        echo json_encode(array("code"=>200,'data'=>array("url"=>$rs['data']['url']),'msg'=>''));
        exit;
	}
}