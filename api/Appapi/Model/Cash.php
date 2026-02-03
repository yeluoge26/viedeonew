<?php

class Model_Cash extends PhalApi_Model_NotORM {
    
    /* 提现账号列表 */
    public function getAccountList($uid){
        
        $list=DI()->notorm->users_cash_account
                ->select("*")
                ->where('uid=?',$uid)
                ->order("addtime desc")
                ->fetchAll();
                
        return $list;
    }

    /* 设置提账号 */
    public function setAccount($data){
        
        $rs=DI()->notorm->users_cash_account
                ->insert($data);
                
        return $rs;
    }

    /* 删除提账号 */
    public function delAccount($data){
        
        $rs=DI()->notorm->users_cash_account
                ->where($data)
                ->delete();
                
        return $rs;
    }    

	/* 我的收益 */
	public function getProfit($uid){
		$info= DI()->notorm->users
				->select("votes")
				->where('id=?',$uid)
				->fetchOne();

		$config=getConfigPri();
		
		//提现比例
		$cash_rate=$config['cash_rate'];
		//剩余票数
		$votes=$info['votes'];
        
		//总可提现数
		$total=(string)floor($votes/$cash_rate);

        
		$rs=array(
			"votes"=>$votes,
			"total"=>$total,
		);
		return $rs;
	}
    
	/* 提现  */
	public function setCash($data){
        
        $nowtime=time();
        
        $uid=$data['uid'];
        $accountid=$data['accountid'];
        $money=$data['money'];
        
        $config=getConfigPri();
        
        /* 钱包信息 */
		$accountinfo=DI()->notorm->users_cash_account
				->select("*")
				->where('id=?',$accountid)
				->fetchOne();
        if(!$accountinfo){
            return 1006;
        }
        

		//提现比例
		$cash_rate=$config['cash_rate'];
		/* 最低额度 */
		$cash_min=$config['cash_min'];
        
        if($money < $cash_min){
			return 1004;
		}
		
		//提现钱数
        $max_money=floor($cashvote/$cash_rate);
		
		$cashvotes=$money*$cash_rate;
        
        
        $ifok=DI()->notorm->users
            ->where('id = ? and votes>=?', $uid,$cashvotes)
            ->update(array('votes' => new NotORM_Literal("votes - {$cashvotes}")) );
        if(!$ifok){
            return 1001;
        }
		
		
		
		$data=array(
			"uid"=>$uid,
			"money"=>$money,
			"votes"=>$cashvotes,
			"orderno"=>$uid.'_'.$nowtime.rand(100,999),
			"status"=>0,
			"addtime"=>$nowtime,
			"uptime"=>$nowtime,
			"type"=>$accountinfo['type'],
			"account_bank"=>$accountinfo['account_bank'],
			"account"=>$accountinfo['account'],
			"name"=>$accountinfo['name'],
		);
		
		$rs=DI()->notorm->users_cashrecord->insert($data);
		if(!$rs){
            return 1002;
		}	        
        
		return $rs;
	}

}
