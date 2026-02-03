<?php

/**
 * 话题标签
 */
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class LabelController extends AdminbaseController {


    function index(){
        $map=[];
        if($_REQUEST['keyword']!=''){
			$map['name']=array("like","%".$_REQUEST['keyword']."%");  
			$_GET['keyword']=$_REQUEST['keyword'];
		}
        
    	$label=M("label");
    	$count=$label->where($map)->count();
    	$page = $this->page($count, 20);
    	$lists = $label
            ->where($map)
            ->order("orderno asc, id desc")
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();
            
    	$this->assign('lists', $lists);
    	$this->assign('formget', $_GET);

    	$this->assign("page", $page->show('Admin'));
    	
    	$this->display();
    }
		
    function del(){
        $id=intval($_GET['id']);
        if($id){
            $result=M("label")->delete($id);				
                if($result){
                    $this->success('删除成功');
                 }else{
                    $this->error('删除失败');
                 }			
        }else{				
            $this->error('数据传入失败！');
        }								  
        $this->display();				
    }		
    //排序
    public function listorders() { 
		
        $ids = $_POST['listorders'];
        foreach ($ids as $key => $r) {
            $data['orderno'] = $r;
            M("label")->where(array('id' => $key))->save($data);
        }
				
        $status = true;
        if ($status) {
            $this->success("排序更新成功！");
        } else {
            $this->error("排序更新失败！");
        }
    }	
    

    function add(){
        
        $this->display();				
    }
    
    function add_post(){
        if(IS_POST){
            $label=M("label");
            $name=I('name');
             if($name==''){
                 $this->error('请填写名称');
             }
             
             $map['name']=$name;
             $isexist=$label->where($map)->find();
             if($isexist){
                 $this->error('已存在相同名称');
             }
             
             $thumb=I('thumb');
             
             if($thumb==''){
                 $this->error('请上传封面');
             }
             
             $des=I('des');
             
             if($des==''){
                 $this->error('请填写描述');
             }
             
             
             $label->create();
             
             $result=$label->add(); 
             if($result){
                $this->success('添加成功');
             }else{
                $this->error('添加失败');
             }
        }			
    }		
    function edit(){
        $id=intval($_GET['id']);
        if($id){
            $data=M("label")->find($id);
            $this->assign('data', $data);						
        }else{				
            $this->error('数据传入失败！');
        }								  
        $this->display();				
    }
    
    function edit_post(){
        if(IS_POST){
            $label=M("label");
            $id=I('id');
            $name=I('name');
            if($name==''){
                 $this->error('请填写名称');
             }
             
             $map['name']=$name;
             $map['id']=array('neq',$id);
             $isexist=$label->where($map)->find();
             if($isexist){
                 $this->error('已存在相同名称');
             }
             
             $thumb=I('thumb');
             
             if($thumb==''){
                 $this->error('请上传封面');
             }
             
             $des=I('des');
             
             if($des==''){
                 $this->error('请填写描述');
             }
             
             
             
             $label->create();

             $result=$label->save(); 
             if($result!==false){
                  $this->success('修改成功');
             }else{
                  $this->error('修改失败');
             }
        }			
    }
        

}
