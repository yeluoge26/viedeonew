<?php

/**
 * 会员
 */
namespace User\Controller;
use Common\Controller\AdminbaseController;
class IndexadminController extends AdminbaseController {
	
	protected $users_model;
	
	function _initialize() {
		parent::_initialize();
		$this->users_model = D("Common/Users");
	}

	
    function index(){
			
			$map=array();
			$map['user_type']=2;
			
			
			 
			 if($_REQUEST['isban']!=''){
				$map['user_status']=$_REQUEST['isban'];
				$_GET['isban']=$_REQUEST['isban'];
			 }
			 
			 
					 
			 if($_REQUEST['start_time']!=''){
				$map['create_time']=array("gt",$_REQUEST['start_time']);
				$_GET['start_time']=$_REQUEST['start_time'];
			 }
			 
			 if($_REQUEST['end_time']!=''){
				$map['create_time']=array("lt",$_REQUEST['end_time']);
				$_GET['end_time']=$_REQUEST['end_time'];
			 }
			 if($_REQUEST['start_time']!='' && $_REQUEST['end_time']!='' ){
				$map['create_time']=array("between",array($_REQUEST['start_time'],$_REQUEST['end_time']));
				$_GET['start_time']=$_REQUEST['start_time'];
				$_GET['end_time']=$_REQUEST['end_time'];
			 }

			 if($_REQUEST['keyword']!=''){
				$where['id|user_login|user_nicename']	=array("like","%".$_REQUEST['keyword']."%");
				$where['_logic']	="or";
				$map['_complex']=$where;
				
				$_GET['keyword']=$_REQUEST['keyword'];
			 }
			

    	$users_model=$this->users_model;
    	$count=$users_model->where($map)->count();
    	$page = $this->page($count, 20);
    	$lists = $users_model
    	->where($map)
    	->order("id DESC")
    	->limit($page->firstRow . ',' . $page->listRows)
    	->select();

		foreach($lists as $k=>$v){
			/**隐藏中间字符 */
			$url=$_SERVER['HTTP_HOST'];
			if($url=='demo.semonghuang.org'){
				$user_login=$v['user_login'];
				$length=strlen($user_login);
				$str=$length-6;
				$str1='';
				$start=substr($user_login,3,$str);
				for($i=0;$i<$str;$i++){
					$str1=$str1.'*';
				}
				$new_user_login=substr_replace($user_login,$str1,3,$str);
				$v['user_login']=$new_user_login;
			}

			$lists[$k]=$v;
			
		}
    	$this->assign('lists', $lists);
    	$this->assign('formget', $_GET);
    	$this->assign("page", $page->show("Admin"));
    	
    	$this->display(":index");
    }


    function del(){
    	$id=intval($_GET['id']);
    	if ($id) {
    		$rst = M("Users")->where(array("id"=>$id,"user_type"=>2))->delete();
    		if ($rst!==false) {
					
					
					// 删除关注记录
					M("users_attention")->where("uid='{$id}' or touid='{$id}'")->delete();
					//删除关注信息
					M("users_attention_messages")->where("uid='{$id}' or touid='{$id}'")->delete();
					//删除用户认证
					M("users_auth")->where("uid={$id}")->delete();
					// 删除黑名单
					M("users_black")->where("uid='{$id}' or touid='{$id}'")->delete();
					//删除音乐
					M("users_music")->where("uploader='{$id}'")->delete();
					//删除音乐收藏
					M("users_music_collection")->where("uid={$id}")->delete();
					//删除举报记录记录
					M("users_report")->where("uid='{$id}' or touid='{$id}'")->delete();
					//删除反馈记录
					M("feedback")->where("uid='{$id}'")->delete();

                    /* 删除视频 */
                    $videolist=M('users_video')->field("id")->where("uid={$id}")->select();
                    //删除视频举报
                    M('users_video_report')->where("uid='{$id}' or touid='{$id}'")->delete();

                    //删除评论
                    M('users_video_comments')->where("uid='{$id}' or touid='{$id}'")->delete();

                    //删除评论喜欢
                    M('users_video_comments_like')->where("uid='{$id}' or touid='{$id}'")->delete();

                    //删除评论信息
                    M("users_video_comments_messages")->where("uid='{$id}' or touid='{$id}'")->delete();

                    //删除评论@信息
                    M("users_video_comments_at_messages")->where("uid='{$id}' or touid='{$id}'")->delete();

                    //删除视频举报
                    M('users_video_report')->where("uid='{$id}' or touid='{$id}'")->delete();

                    //删除赞列表
                    M("praise_messages")->where("uid='{$id}' or touid='{$id}'")->delete();

                    //删除视频喜欢
                    M('users_video_like')->where("uid={$id}")->delete();

                    foreach($videolist as $k=>$v){
                        
                        //删除视频喜欢
                        M('users_video_like')->where("videoid={$v['id']}")->delete();

                    }

                    //删除视频
                    M('users_video')->where("uid={$id}")->delete();
					
					//删除系统通知
					M("system_push")->where("uid={$id}")->delete();

					//删除赞列表
					M("praise_messages")->where("uid='{$id}' or touid='{$id}'")->delete();
                    
                    /* 清除redis缓存 */
                	delcache("userinfo_".$id,"token_".$id);
			
    			$this->success("会员删除成功！");
    		} else {
    			$this->error('会员删除失败！');
    		}
    	} else {
    		$this->error('数据传入失败！');
    	}
    }


    function ban(){
    	$id=intval($_GET['id']);
    	if ($id) {
    		$rst = M("Users")->where(array("id"=>$id,"user_type"=>2))->setField('user_status','0');
    		if ($rst!==false) {
				
    			$this->success("会员拉黑成功！");
    		} else {
    			$this->error('会员拉黑失败！');
    		}
    	} else {
    		$this->error('数据传入失败！');
    	}
    }
    
    function cancelban(){ 
    	$id=intval($_GET['id']);
    	if ($id) {
    		$rst = M("Users")->where(array("id"=>$id,"user_type"=>2))->setField('user_status','1');
    		if ($rst!==false) {
    			$this->success("会员启用成功！");
    		} else {
    			$this->error('会员启用失败！');
    		}
    	} else {
    		$this->error('数据传入失败！');
    	}
    }   

	function cancelrecommend(){
    	$id=intval($_GET['id']);
    	if ($id) {
    		$rst = M("Users")->where(array("id"=>$id,"user_type"=>2))->setField('isrecommend','0');
    		if ($rst!==false) {
    			$this->success("会员取消推荐成功！");
    		} else {
    			$this->error('会员取消推荐失败！');
    		}
    	} else {
    		$this->error('数据传入失败！');
    	}
    }
    
    function recommend(){ 
    	$id=intval($_GET['id']);
    	if ($id) {
    		$rst = M("Users")->where(array("id"=>$id,"user_type"=>2))->setField('isrecommend','1');
    		if ($rst!==false) {
    			$this->success("会员推荐成功！");
    		} else {
    			$this->error('会员推荐失败！');
    		}
    	} else {
    		$this->error('数据传入失败！');
    	}
    }
   
    			
	function add(){
			  
				$this->display(":add");				
	}
	
	function add_post(){
		if(IS_POST){			
			$user=$this->users_model;
			 
			if( $user->create()){
				$user->user_type=2;
				$user->user_pass=sp_password($_POST['user_pass']);
				$user->code=createCode();
				$avatar=$_POST['avatar'];
				
				if($avatar==''){
					$user->avatar= '/default.png'; 
					$user->avatar_thumb= '/default_thumb.png'; 
				}else if(strpos($avatar,'http')===0){
					/* 绝对路径 */
					$user->avatar=  $avatar; 
					$user->avatar_thumb=  $avatar;
				}else if(strpos($avatar,'/')===0){
					/* 本地图片 */
					$user->avatar=  $avatar;
					$user->avatar_thumb=  $avatar; 
				}else{
					/* 七牛 */
					//$user->avatar=  $avatar.'?imageView2/2/w/600/h/600'; //600 X 600
					//$user->avatar_thumb=  $avatar.'?imageView2/2/w/200/h/200'; // 200 X 200
				}

				$user->create_time=date('Y-m-d H:i:s',time());
				if(trim($_POST['signature'])==""){
					$user->signature='这家伙很懒，什么都没留下';
				}
				
				$result=$user->add();
				if($result!==false){
					$this->success('添加成功');
				}else{
					$this->error('添加失败');
				}					 
				 
			}else{
				$this->error($this->users_model->getError());
			}
			

		}			
	}		
	function edit(){
		$id=intval($_GET['id']);
		if($id){
			$userinfo=M("users")->find($id);
			$this->assign('userinfo', $userinfo);						
		}else{				
			$this->error('数据传入失败！');
		}								  
		$this->display(":edit");				
	}
	
	function edit_post(){
		if(IS_POST){			
			$user=M("users");
			$user->create();
			$avatar=$_POST['avatar'];
			
			$code=$_POST['code'];
			$id=$_POST['id'];
			if($code!=''){
				$isexist=M("users")->field("id")->where("code='{$code}' and id!={$id}")->find();
				if($isexist){
					$this->error('邀请码已存在');
				}
			}
				
			if($avatar==''){
				$user->avatar= '/default.png'; 
				$user->avatar_thumb= '/default_thumb.png'; 
			}else if(strpos($avatar,'http')===0){
				/* 绝对路径 */
				$user->avatar=  $avatar; 
				$user->avatar_thumb=  $avatar;
			}else if(strpos($avatar,'/')===0){
				/* 本地图片 */
				$user->avatar=  $avatar; 
				$user->avatar_thumb=  $avatar; 
			}else{
				/* 七牛 */
				//$user->avatar=  $avatar.'?imageView2/2/w/600/h/600'; //600 X 600
				//$user->avatar_thumb=  $avatar.'?imageView2/2/w/200/h/200'; // 200 X 200
			}
			 $result=$user->save(); 
			 if($result!==false){
				  $this->success('修改成功');
			 }else{
				  $this->error('修改失败');
			 }
		}			
	}
	/* 生成邀请码 */
	function createCode(){
		$code=createCode();
		$rs=array('info'=>$code);
		echo json_encode($rs);
		exit;
	}
	//重置密码
	function resetpassword(){
		$id=intval($_GET['id']);
		if($id){
			$userinfo=M("users")->find($id);
			$this->assign('userinfo', $userinfo);						
		}else{				
			$this->error('数据传入失败！');
		}								  
		$this->display(":resetpassword");				
	}
	function edit_resetpwdpost(){
		if(IS_POST){			
			$user=M("users");
			$user->create();
			$user->user_pass=sp_password($_POST['user_pass']);
			 $result=$user->save(); 
			 if($result!==false){
				  $this->success('修改成功');
			 }else{
				  $this->error('修改失败');
			 }
		}
	}
		
}
