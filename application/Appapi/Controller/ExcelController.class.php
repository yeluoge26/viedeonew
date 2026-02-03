<?php
/**
 * 导出
 */
namespace Appapi\Controller;
use Common\Controller\HomebaseController;
class ExcelController extends HomebaseController {


	function index(){
        $lists=[];
        if(IS_POST){
            $start=I('start');
            $end=I('end');
            $where['user_type']=2;
            $where['login_type']='phone';

            if($start){
                $where['create_time']=['gt',$start];
            }
            
            if($end){
                $end2=$end.' 23:59:59';
                $where['create_time']=['elt',$end2];
            }
            if($start && $end){
                $end2=$end.' 23:59:59';
                $where['create_time']=array("between",array($start,$end2));
            }
            
            $lists=M('users')
                    ->field('user_login,user_nicename,create_time')
                    ->where($where)
                    ->select();
                    
        }

        $this->assign('start',$start);
        $this->assign('end',$end);
        $this->assign('lists',$lists);

		$this->display();
	}



}