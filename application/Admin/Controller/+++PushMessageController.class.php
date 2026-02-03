<?php

/**
 * 极光推送
 */
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
use JMessage\JMessage;
use JMessage\IM\Admin;
use JMessage\IM\Message;
use JMessage\IM\User;
use JMessage\IM\Group;



class PushMessageController extends AdminbaseController {


	public $groupIds=[];
	public $getNums=500; //API规定一次最多查询500个群组

	/*推送发送*/
	function add(){

		$this->display();
	}

	function add_post(){
		$rs=array("code"=>0,"msg"=>"","info"=>array());

		$title=I("title");
		$synopsis=I("synopsis");
		$msg_type=I("msg_type");
		$content=I("content");
		$url=I("url");

		if($title==""){
			$rs['code']=1001;
			$rs['msg']="请填写标题";
			echo json_encode($rs);
			exit;
		}

		if($synopsis==""){
			$rs['code']=1001;
			$rs['msg']="请填写简介";
			echo json_encode($rs);
			exit;
		}

		if($msg_type==2&&$url==""){
			$rs['code']=1002;
			$rs['msg']="请填写链接地址";
			echo json_encode($rs);
			exit;
		}

		$id=$_SESSION['ADMIN_ID'];
		$user=M("Users")->where("id='{$id}'")->find();

		$result=M("admin_push")->add(array("title"=>$title,"synopsis"=>$synopsis,"type"=>$msg_type,"content"=>htmlspecialchars_decode($content),"url"=>$url,"admin"=>$user['user_login'],"addtime"=>time(),"ip"=>$_SERVER['REMOTE_ADDR']));

		if($result!==false){
			$rs['info']['id']=$result;
			$rs['info']['count']=M("users")->where("user_type=2 and user_status=1")->count();

			echo json_encode($rs);
			exit;
		}else{
			$rs['code']=1002;
			$rs['msg']="推送失败";
			echo json_encode($rs);

		}


	}

	/*推送记录*/
	public function index(){

		$map=array();
			
		if($_REQUEST['keyword']!=''){
			$map['title']=array("like","%".$_REQUEST['keyword']."%");
			$_GET['keyword']=$_REQUEST['keyword'];
		}
		
    	$push=M("admin_push");
    	$count=$push->where($map)->count();
    	$page = $this->page($count, 20);
    	$lists = $push
			->where($map)
			->order("addtime desc")
			->limit($page->firstRow . ',' . $page->listRows)
			->select();

		//var_dump($push->getLastSql());
		
		//获取私密配置信息
		/*$config=getConfigPri();
		$timestamp=time();
		$random_str="022cd9fd995849b58b3ef0e943421ed9";
		$signature = md5("appkey={$config['jpush_key']}&timestamp={timestamp}&random_str={$random_str}&key={$config['jpush_secret']}");
*/		
		//获取当前用户总数
		$count=M("users")->where("user_type=2 and user_status=1")->count();
		
    	$this->assign('lists', $lists);
    	$this->assign('formget', $_GET);
    	$this->assign("page", $page->show('Admin'));
    	$this->assign("count", $count);
    	
    	/*$this->assign("jpush_key",$config['jpush_key']);
    	$this->assign("random_str",$random_str);
    	$this->assign("signature",$signature);
    	$this->assign("timestamp",$timestamp);
*/
    	$this->display();
	}

	/*将原来的信息重新获取一份新加入数据库*/
	public function push_add(){

		$res=array("code"=>0,"msg"=>"","info"=>array());
		$id=I("id");
		if($id==""){
			$res['code']=1001;
			$res['msg']="数据传入失败";
			echo json_encode($res);
			exit;
		}

		//判断id信息是否存在
		$info=M("admin_push")->where("id={$id}")->find();
		if(!$info){
			$res['code']=1001;
			$res['msg']="数据传入失败";
			echo json_encode($res);
			exit;
		}

		unset($info['id']);
		$info['addtime']=time();
		$result=M("admin_push")->add($info);
		
		if($result==false){
			$res['code']=1001;
			$res['msg']="写入数据失败";
			echo json_encode($res);
			exit;
		}

		//获取当前用户的总数
		/*$count=M("users")->where("user_type=2 and user_status=1")->count();
		$res['info']=$count;*/

		echo json_encode($res);

	}


	public function push(){
		
		$res=array("code"=>0,"msg"=>"","info"=>array());
		$id=I("msgid");
		$lastid=I("lastid");
		$num=I("num");

		if($id==""){
			$res['code']=1001;
			$res['msg']="数据传入失败";
			echo json_encode($res);
			exit;
		}

		//判断id信息是否存在
		$info=M("admin_push")->where("id={$id}")->find();
		if(!$info){
			$res['code']=1001;
			$res['msg']="数据传入失败";
			echo json_encode($res);
			exit;
		}


		//获取后台配置的极光推送app_key和master_secret
		$configPri=getConfigPri();
		$appKey = $configPri['jpush_key'];
		$masterSecret =  $configPri['jpush_secret'];

		if($appKey&&$masterSecret){


			//极光IM
			vendor('jmessage.autoload');//导入极光IM类库		

			$jm = new JMessage($appKey, $masterSecret);

			//注册管理员
			$admin = new Admin($jm);
			$regInfo = [
			    'username' => 'dsp_admin_1',
			    'password' => 'dsp_admin_1',
			    'nickname'=>'视频官方'
			];

			$response = $admin->register($regInfo);

			//var_dump($response['body']);
			if($response['body']==""||$response['body']['error']['code']==899001){ //新管理员注册成功或管理员已经存在

				//发布消息
				$message = new Message($jm);

				$user = new User($jm);
				

				//创建群组对象
				$group = new Group($jm);

				$name = 'jiguang';
				$desc = 'jiguang gtoup';
				$members=[];

				/*for ($i=13657; $i >(13656-100) ; $i--) {
					$userinfo=$user->show($i); //获取用户信息
					if($userinfo['body']['error']['code']==899002){  //极光用户不存在
						continue;
					}

					$group->create($i, $name, $desc, $members);

					//$members[]=$i;
				}*/



				$response = $group->listAll($this->getNums,0);

				//$before=userSendBefore(); //获取极光用户账号前缀
				$groupCount=$response['body']['count'];
				$groupTotal=$response['body']['total'];

				$start=$groupCount;

				/*var_dump("cccc");
				die;*/

				if($groupCount>0){  //群组有列表

					$groups=$response['body']['groups'];

					for ($i=0; $i <$groupCount; $i++) { 
						$this->groupIds[]=$groups[$i]['gid'];
					}

					if($groupTotal>$groupCount){
						$this->getMorGroups($this->getNums,$start);
					}


					$from = [
					    'id'   => 'dsp_admin_1', //短视频系统规定视频官方必须是该账号（与APP保持一致）
					    'type' => 'admin'
					];

					$notification =[
						'notifiable'=>false  //是否在通知栏展示
					];

					file_put_contents("groupSend.txt", "时间：".date("Y-m-d:H:i:s",time()).PHP_EOL.PHP_EOL,FILE_APPEND);//换行追加


					for ($i=0; $i <count($this->groupIds) ; $i++) { 

						$target = [
						    'id'   => $this->groupIds[$i],
						    'type' => 'group'
						];

						$msg = [
						   'text' => $info['title']
						];

						$response = $message->sendText(1, $from, $target, $msg,$notification,[]);

						file_put_contents("groupSend.txt", "时间：".date("Y-m-d:H:i:s",time()).PHP_EOL,FILE_APPEND);//换行追加

					}

					file_put_contents("groupSend.txt", "时间：".date("Y-m-d:H:i:s",time()).PHP_EOL.PHP_EOL,FILE_APPEND);//换行追加

				}else{

					$res['code']=1001;
					$res['msg']="消息推送失败";
				}
				

				$res['msg']="消息推送成功";
				$res['info']=$lastid;

			}else{
				$res['code']=1001;
				$res['msg']="消息推送失败";
			}

			echo json_encode($res);
			exit;
				
		}else{

			$res['code']=1001;
			$res['msg']="消息推送失败";
			echo json_encode($res);
			exit;
		}

		
	}

	public function del(){
		$id=I("id");
		if($id==""){
			$this->error("数据传入失败");
			exit;
		}

		$result=M("admin_push")->where("id={$id}")->delete();
		if($result!==false){
			$this->success("删除成功");
		}else{
			$this->error("删除失败");
		}
	}

	//获取更多群组列表
	public function getMorGroups($getNums,$start,$i=1){

		$start=$i*$getNums;

		$i++;

		//获取后台配置的极光推送app_key和master_secret
		$configPri=getConfigPri();
		$appKey = $configPri['jpush_key'];
		$masterSecret =  $configPri['jpush_secret'];

		//极光IM
		vendor('jmessage.autoload');//导入极光IM类库		

		$jm = new JMessage($appKey, $masterSecret);

		$group = new Group($jm);
		$response = $group->listAll($getNums,$start);
		$groupCount=$response['body']['count'];
		$groupTotal=$response['body']['total'];

		if($groupCount>0){  //群组有列表

			$groups=$response['body']['groups'];

			for ($i=0; $i <$groupCount; $i++) { 
				$this->groupIds[]=$groups[$i]['gid'];
			}

		}

		if(($groupTotal>$groupCount)&&$groupCount>0){
			
			$this->getMorGroups($getNums,$start,$i);
		}
	}

	

}
