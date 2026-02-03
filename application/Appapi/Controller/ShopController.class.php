<?php
/**
 * 店铺认证
 */
namespace Appapi\Controller;
use Common\Controller\HomebaseController;
class ShopController extends HomebaseController {
	
	function index(){       
		$uid=(int)I("uid");
		$token=I("token");
		
		if(checkToken($uid,$token)==700){
			$this->assign("reason",'您的登陆状态失效，请重新登陆！');
			$this->display(':error');
			exit;
		} 
		  
        $shop=M('shop_apply')->where(['uid'=>$uid])->find();
        
        if($shop){
            /* 已提交 */
            $shop['thumb1']=get_upload_path($shop['thumb']);
            $shop['license1']=get_upload_path($shop['license']);
            $shop['certificate1']=get_upload_path($shop['certificate']);
            $shop['other1']=get_upload_path($shop['other']);
            
            $this->assign("uid",$uid);
            $this->assign("token",$token);
            $this->assign("info",$shop);

            $this->display('apply');
            exit;
        }
        
        $fans_ok=0;
        
        $configpri=getConfigPri();
        
        $shop_fans=$configpri['shop_fans'];
        
        $fans=M("users_attention")->where(['touid'=>$uid])->count();
        if($fans>=$shop_fans){
            $fans_ok=1;
        }
        
        $video_ok=0;
        $shop_videos=$configpri['shop_videos'];
        
        $video=M("users_video")->where(['uid'=>$uid,'isdel'=>0,'status'=>1])->count();
        if($video>=$shop_videos){
            $video_ok=1;
        }
        
        $auth_ok=0;
        $auth=M("users_auth")->where(['uid'=>$uid,'status'=>1])->find();
        if($auth){
            $auth_ok=1;
        }
        
        $isapply=0;
        if($fans_ok==1 && $video_ok==1 && $auth_ok==1){
            $isapply=1;
        }

		$this->assign("uid",$uid);
		$this->assign("token",$token);
		$this->assign("shop_fans",$shop_fans);
		$this->assign("fans_ok",$fans_ok);
		$this->assign("shop_videos",$shop_videos);
		$this->assign("video_ok",$video_ok);
		$this->assign("auth_ok",$auth_ok);
		$this->assign("isapply",$isapply);

		$this->display();
	    
	}

	function apply(){       
		$uid=(int)I("uid");
		$token=I("token");
		
		if(checkToken($uid,$token)==700){
			$this->assign("reason",'您的登陆状态失效，请重新登陆！');
			$this->display(':error');
			exit;
		} 
		
        $info=M('shop_apply')->where(['uid'=>$uid])->find();
        
        if($info){
            $info['thumb1']=get_upload_path($info['thumb']);
            $info['license1']=get_upload_path($info['license']);
            $info['certificate1']=get_upload_path($info['certificate']);
            $info['other1']=get_upload_path($info['other']);
        }

		$this->assign("uid",$uid);
		$this->assign("token",$token);
		$this->assign("info",$info);


		$this->display();
	    
	}

	function apply_post(){
        
        $rs = array('code' => 0, 'msg' => '申请提交成功', 'info' => array());
        
		$uid=(int)I("uid");
		$token=checkNull(I("token"));
		$thumb=checkNull(I("thumb"));
		$name=checkNull(I("name"));
		$des=checkNull(I("des"));
		$tel=checkNull(I("tel"));
		$certificate=checkNull(I("certificate"));
		$license=checkNull(I("license"));
		$other=checkNull(I("other"));
		
		if(checkToken($uid,$token)==700){
            $rs['code']=700;
            $rs['msg']='您的登陆状态失效，请重新登陆！';
            echo json_encode($rs);
			exit;
		}
        
        $configpri=getConfigPri();
        
        $shop_fans=$configpri['shop_fans'];
        
        $fans=M("users_attention")->where(['touid'=>$uid])->count();
        if($fans < $shop_fans){
            $rs['code']=1003;
            $rs['msg']='申请条件未达成';
            echo json_encode($rs);
            exit;
        }
        

        $shop_videos=$configpri['shop_videos'];
        
        $video=M("users_video")->where(['uid'=>$uid,'isdel'=>0,'status'=>1])->count();
        if($video < $shop_videos){
            $rs['code']=1003;
            $rs['msg']='申请条件未达成';
            echo json_encode($rs);
            exit;
        }
        
        $auth=M("users_auth")->where(['uid'=>$uid,'status'=>1])->find();
        if(!$auth){
            $rs['code']=1003;
            $rs['msg']='申请条件未达成';
            echo json_encode($rs);
            exit;
        }
        
        
        
        $isexist=M('shop_apply')->where(['uid'=>$uid])->find();
		if($isexist){
            if($isexist['status']==0){
                $rs['code']=1001;
                $rs['msg']='申请审核中';
                echo json_encode($rs);
                exit;
            }
        }
        
        if($thumb==''){
            $rs['code']=1002;
            $rs['msg']='请上传店铺图片';
            echo json_encode($rs);
            exit;
        }
        
        if($name==''){
            $rs['code']=1002;
            $rs['msg']='请输入店铺名称';
            echo json_encode($rs);
            exit;
        }
        
        if($des==''){
            $rs['code']=1002;
            $rs['msg']='请输入店铺简介';
            echo json_encode($rs);
            exit;
        }
        
        if($certificate==''){
            $rs['code']=1002;
            $rs['msg']='请上传营业执照';
            echo json_encode($rs);
            exit;
        }
        
        if($license==''){
            $rs['code']=1002;
            $rs['msg']='请上传许可证';
            echo json_encode($rs);
            exit;
        }
        
        $nowtime=time();
        
        $data=[
            'uid'=>$uid,
            'name'=>$name,
            'thumb'=>$thumb,
            'des'=>$des,
            'tel'=>$tel,
            'certificate'=>$certificate,
            'license'=>$license,
            'other'=>$other,
            'addtime'=>$nowtime,
            'uptime'=>$nowtime,
            'status'=>0,
        ];
        
        $configpri=getConfigPri();
        $show_switch=$configpri['show_switch'];
        if($show_switch==0){
            $data['status']=1;
        }
        
        if($isexist){
            M('shop_apply')->where(['uid'=>$uid])->save($data);
        }else{
            M('shop_apply')->add($data);
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