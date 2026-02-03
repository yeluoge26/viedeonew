<?php
session_start();
class Model_Home extends PhalApi_Model_NotORM {

		/* 搜索 */
    public function search($uid,$key,$p) {
		$pnum=50;
		$start=($p-1)*$pnum;
		$where=' user_type="2" and ( id=? or user_nicename like ?) and id!=?';


		if($p!=1){
			$id=$_SESSION['search'];
			$where.=" and id < {$id}";
		}

		
		$result=DI()->notorm->users
				->select("id,user_nicename,avatar,avatar_thumb,sex,signature,province,city,birthday,age")
				->where($where,$key,'%'.$key.'%',$uid)
				->order("id desc")
				->limit($start,$pnum)
				->fetchAll();


		foreach($result as $k=>$v){

			$result[$k]['isattention']=(string)isAttention($uid,$v['id']);
			$result[$k]['avatar']=get_upload_path($v['avatar']);
			$result[$k]['avatar_thumb']=get_upload_path($v['avatar_thumb']);
			if($v['age']<0){
				$result[$k]['age']="年龄未填写";
			}else{
				$result[$k]['age'].="岁";
			}

			if($v['city']==""){
				$result[$k]['city']="城市未填写";
			}

			$result[$k]['praise']=getPraises($v['id']);
			$result[$k]['fans']=getFans($v['id']);					
			$result[$k]['follows']=getFollows($v['id']);
			$result[$k]['coin']="0";


			unset($result[$k]['consumption']);
		}

		if($result){
			$last=array_slice($result,-1,1);

			$_SESSION['search']=$last[0]['id'];
		}

		
		return $result;
    }
	



    public function videoSearch($uid,$key,$p) {
		$pnum=50;
		$start=($p-1)*$pnum;

		$where="v.isdel=0 and v.status=1 and v.is_ad=0";

		$where.=" and v.title like '%".$key."%' or u.user_nicename like '%".$key."%'";
		/*if($p!=1){
			$id=$_SESSION['videosearch'];
			$where.=" and v.id < {$id}";
		}*/

		$prefix= DI()->config->get('dbs.tables.__default__.prefix');

		$result=DI()->notorm->users_video
				->queryAll("select v.*,u.user_nicename,u.avatar from {$prefix}users_video v left join {$prefix}users u on v.uid=u.id where {$where} order by v.addtime desc limit {$start},{$pnum}");

		/*if($result){
			$last=array_slice($result,-1,1);
			$_SESSION['videosearch']=$last['id'];
		}*/



		foreach ($result as $k => $v) {
            
            $v=handleVideo($uid,$v);
            
            $result[$k]=$v;

		}
        
        

		
		return $result;
    }


}
