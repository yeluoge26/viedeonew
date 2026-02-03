<?php

class Model_Agent extends PhalApi_Model_NotORM {
    
    /* 设置邀请关系 */
	public function setAgent($uid,$code) {
        
        $isexist=DI()->notorm->agent
                        ->select('*')
                        ->where('uid=?',$uid)
                        ->fetchOne();
        if($isexist){
            return 1001;
        }
        
        $isexist2=DI()->notorm->users
                        ->select('*')
                        ->where('id=?',$code)
                        ->fetchOne();
        if(!$isexist2){
            return 1002;
        }
        
        $data=[
            'uid'=>$uid,
            'one'=>$code,
            'addtime'=>time(),
        ];
        
        $result=DI()->notorm->agent
                    ->insert($data);
            
		return '1';
	}
    
	/* 观看奖励 */
	public function setViewLength($uid,$length) {
        
        $nowtime=time();
        //当天0点
        $today=date("Ymd",$nowtime);
        $today_start=strtotime($today);
        //当天 23:59:59
        $today_end=strtotime("{$today} + 1 day");
        

        $isexist=DI()->notorm->view_reward
                    ->select('*')
                    ->where('uid=?',$uid)
                    ->fetchOne();
        if($isexist){
            /* 有记录 */
            if($nowtime>$isexist['addtime']){
                /* 新一天 */
                DI()->notorm->view_reward
                    ->where('uid=?',$uid)
                    ->update(['length'=>$length,'addtime'=>$today_end,'status'=>'0']);
                
            }else{
                /* 累计 */
                DI()->notorm->view_reward
                    ->where('uid=?',$uid)
                    ->update(array('length'=> new NotORM_Literal("length + {$length} ")));
            }
        }else{
            /* 无记录 */
            DI()->notorm->view_reward
                    ->insert(['uid'=>$uid,'length'=>$length,'addtime'=>$today_end,'status'=>'0']);
        }
        
        /* 判断是否奖励 */
        $info=DI()->notorm->view_reward
                    ->select('*')
                    ->where('uid=? and status=0',$uid)
                    ->fetchOne();
        if($info){
            $configPri=getConfigPri();
            $agent_v_l=$configPri['agent_v_l'] * 60;
            if($info['length'] >= $agent_v_l){
                
                $rs=DI()->notorm->view_reward
                    ->where('uid=?',$uid)
                    ->update(['status'=>'1']);
                if(!$rs){
                    return '1';
                }    
                /* 添加奖励 */
                $agent_v_a=$configPri['agent_v_a'];
                
                DI()->notorm->users
                    ->where('id=?',$uid)
                    ->update(array('votes'=> new NotORM_Literal("votes + {$agent_v_a} ")));
                $data=[
                    'action'=>'2',
                    'uid'=>$uid,
                    'votes'=>$agent_v_a,
                    'addtime'=>$nowtime,
                ];
                
                $agent=DI()->notorm->agent
                            ->select('*')
                            ->where('uid=?',$uid)
                            ->fetchOne();
                if($agent){
                    
                    $one=0;
                    if($agent['one']>0){
                        $agent_a=$configPri['agent_a'];
                        DI()->notorm->users
                            ->where('id=?',$agent['one'])
                            ->update(array('votes'=> new NotORM_Literal("votes + {$agent_a} ")));
                            
                        $data['touid']=$agent['one'];
                        $data['touid_votes']=$agent_a;
                        $one=$agent_a;
                        
                        $rs2=DI()->notorm->agent_profit
                            ->where('uid=?',$agent['one'])
                            ->update(array('one_p'=> new NotORM_Literal("one_p + {$agent_a} ")));
                        if(!$rs2){
                            DI()->notorm->agent_profit
                                ->insert(array('uid'=> $agent['one'],'one_p'=> $agent_a));
                        }
                        
                        $rs3=DI()->notorm->agent_profit
                            ->where('uid=?',$uid)
                            ->update(array('one'=> new NotORM_Literal("one + {$agent_a} ")));
                        if(!$rs3){
                            DI()->notorm->agent_profit
                                ->insert(array('uid'=> $uid,'one'=> $agent_a));
                        }
                    }
                    
                    
                    
                }
                
                DI()->notorm->votes_record
                    ->insert($data);
            }
        }
            
		return '1';
	}


}
