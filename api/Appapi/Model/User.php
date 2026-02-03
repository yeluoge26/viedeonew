<?php

class Model_User extends PhalApi_Model_NotORM {
	/* 用户全部信息 */
	public function getBaseInfo($uid) {

		
		$info=DI()->notorm->users
				->select("id,user_nicename,avatar,avatar_thumb,sex,signature,province,city,area,birthday,age,mobile")
				->where('id=?  and user_type="2"',$uid)
				->fetchOne();

		if($info){

			if($info['age']==-1){
				$info['age']="年龄未填写";
			}else{
				$info['age'].="岁";
			}

			if($info['city']==""){
				$info['city']="城市未填写";
				$info['hometown']="";
			}else{
				$info['hometown']=$info['province'].$info['city'].$info['area'];
			}	

			$info['avatar']=get_upload_path($info['avatar']);
			$info['avatar_thumb']=get_upload_path($info['avatar_thumb']);						
			$info['follows']=getFollows($uid);
			$info['fans']=getFans($uid);
			$info['praise']=getPraises($uid);
			$info['workVideos']=getWorks($uid);
			$info['likeVideos']=getLikes($uid);


		}

		
		
					
		return $info;
	}
			
	/* 判断昵称是否重复 */
	public function checkName($uid,$name){
		$isexist=DI()->notorm->users
					->select('id')
					->where('id!=? and user_nicename=?',$uid,$name)
					->fetchOne();
		if($isexist){
			return 0;
		}else{
			return 1;
		}
	}
	/* 判断手机号码是否重复 */
	public function checkMobile($uid,$mobile){
		$isexist=DI()->notorm->users
					->select('id')
					->where('id!=? and mobile=?',$uid,$mobile)
					->fetchOne();
		if($isexist){
			return 0;
		}else{
			return 1;
		}
	}
	/* 修改信息 */
	public function userUpdate($uid,$fields){
		/* 清除缓存 */
		delCache("userinfo_".$uid);
		
		return DI()->notorm->users
					->where('id=?',$uid)
					->update($fields);
	}

	
	/* 关注 */
	public function setAttent($uid,$touid){

		//判断关注列表情况
		
		$isexist=DI()->notorm->users_attention
					->select("*")
					->where('uid=? and touid=?',$uid,$touid)
					->fetchOne();
		if($isexist){
			DI()->notorm->users_attention
				->where('uid=? and touid=?',$uid,$touid)
				->delete();
			return 0;
		}else{
			DI()->notorm->users_black
				->where('uid=? and touid=?',$uid,$touid)
				->delete();
			DI()->notorm->users_attention
				->insert(array("uid"=>$uid,"touid"=>$touid,"addtime"=>time()));


			$isexist1=DI()->notorm->users_attention_messages
					->select("*")
					->where('uid=? and touid=?',$uid,$touid)
					->fetchOne();

			if($isexist1){
				DI()->notorm->users_attention_messages->where('uid=? and touid=?',$uid,$touid)->update(array("addtime"=>time()));
			}else{

				DI()->notorm->users_attention_messages
					->insert(array("uid"=>$uid,"touid"=>$touid,"addtime"=>time()));
			}

			
			return 1;
		}			 
	}	
	
	/* 拉黑 */
	public function setBlack($uid,$touid){
		$isexist=DI()->notorm->users_black
					->select("*")
					->where('uid=? and touid=?',$uid,$touid)
					->fetchOne();
		if($isexist){
			DI()->notorm->users_black
				->where('uid=? and touid=?',$uid,$touid)
				->delete();
			return 0;
		}else{
			DI()->notorm->users_attention
				->where('uid=? and touid=?',$uid,$touid)
				->delete();
			DI()->notorm->users_black
				->insert(array("uid"=>$uid,"touid"=>$touid,"addtime"=>time()));

			return 1;
		}			 
	}
	
	/* 关注列表 */
	public function getFollowsList($uid,$touid,$p,$key){
		$pnum=50;
		$start=($p-1)*$pnum;



		if($key!=0&&!$key){
			$touids=DI()->notorm->users_attention
					->select("touid")
					->where('uid=?',$touid)
					->limit($start,$pnum)
					->fetchAll();
		}else{

			

			$where.="a.uid='{$touid}' and u.user_nicename like '%".$key."%'";
		

			$prefix= DI()->config->get('dbs.tables.__default__.prefix');

			$touids=DI()->notorm->users_attention->queryAll("select a.touid,u.user_nicename from {$prefix}users_attention as a left join {$prefix}users as u on a.touid=u.id where ".$where." limit {$start},{$pnum}");
		}


		foreach($touids as $k=>$v){
			$userinfo=getUserInfo($v['touid']);
			if($userinfo){
				if($uid==$touid){
					$isattent=1;
				}else{
					$isattent=isAttention($uid,$v['touid']);
				}
				$userinfo['isattention']=$isattent;
				$touids[$k]=$userinfo;
			}else{
				DI()->notorm->users_attention->where('uid=? or touid=?',$v['touid'],$v['touid'])->delete();
				unset($touids[$k]);
			}
		}		
		$touids=array_values($touids);
		return $touids;
	}
	
	/* 粉丝列表 */
	public function getFansList($uid,$touid,$p){

		$pnum=50;
		$start=($p-1)*$pnum;
		$touids=DI()->notorm->users_attention
					->select("uid")
					->where('touid=?',$touid)
					->limit($start,$pnum)
					->fetchAll();

		
		foreach($touids as $k=>$v){
			$userinfo=getUserInfo($v['uid']);
			if($userinfo){
				$userinfo['isattention']=isAttention($uid,$v['uid']);
				$touids[$k]=$userinfo;
				$touids[$k]['attentiontime']=datetime($v['addtime']);
			}else{
				DI()->notorm->users_attention->where('uid=? or touid=?',$v['uid'],$v['uid'])->delete();
				unset($touids[$k]);
			}
			
		}		
		$touids=array_values($touids);
		return $touids;
	}	

	/* 黑名单列表 */
	public function getBlackList($uid,$touid,$p){
		$pnum=50;
		$start=($p-1)*$pnum;
		$touids=DI()->notorm->users_black
					->select("touid,addtime")
					->where('uid=?',$touid)
					->limit($start,$pnum)
					->fetchAll();

		foreach($touids as $k=>$v){
			$userinfo=getUserInfo($v['touid']);
			if($userinfo){
				$userinfo['addtime']=datetime($v['addtime']); //拉黑时间
				$touids[$k]=$userinfo;
			}else{
				DI()->notorm->users_black->where('uid=? or touid=?',$v['touid'],$v['touid'])->delete();
				unset($touids[$k]);
			}
		}
		$touids=array_values($touids);
		return $touids;
	}

	
		/* 个人主页 */
	public function getUserHome($uid,$touid){
		$info=getUserInfo($touid);				

		$info['follows']=NumberFormat(getFollows($touid));
		$info['fans']=NumberFormat(getFans($touid));
		$info['isattention']=(string)isAttention($uid,$touid);
		/*$info['isblack']=(string)isBlack($uid,$touid);
		$info['isblack2']=(string)isBlack($touid,$uid);*/

		return $info;
	}
	

	/*获取用户喜欢的视频列表*/
	public function getLikeVideos($uid,$p){


		$pnum=18;

		$start=($p-1)*$pnum;

		//获取用户喜欢的视频列表
		$list=DI()->notorm->users_video_like->where("uid=? and status=1 ",$uid)->limit($start,$pnum)->fetchAll();

		if(!$list){
			return 1001;
		}

		foreach ($list as $k => $v) {
			
			$videoinfo=DI()->notorm->users_video->where("id=? and status=1 and isdel=0 ",$v['videoid'])->fetchOne();

			if(!$videoinfo){
				//DI()->notorm->users_video_like->where("videoid=?",$v['videoid'])->delete();
				unset($list[$k]);
				continue;
			}
            $video=handleVideo($uid,$videoinfo);

			$video['addtime']=date('Y-m-d H:i:s', $v['addtime']);
			$video['datetime']=datetime($v['addtime']);

			$video['isdel']='0';  //暂时跟getAttentionVideo统一(包含下面的)
			$video['isdialect']='0';

			$lista[]=$video;  //因为unset掉某个数组后，k值连不起来了，所以重新赋值给新数组

		}

		if(empty($lista)){
			$lista=array();
		}

		return $lista;
	}
}
