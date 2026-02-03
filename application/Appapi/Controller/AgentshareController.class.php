<?php
/**
 * 邀请分享
 */
namespace Appapi\Controller;
use Common\Controller\HomebaseController;
class AgentshareController extends HomebaseController {
	
	function index(){

		$uid=(int)I("uid");

        
        $code=$uid;
        
        $apkurl='http://149.129.81.232:8088/Apk.service?uid='.$uid.'&file=L2RhdGEvd3d3cm9vdC9kZWZhdWx0L3VwbG9hZC9hcGsvYXBwLXJlbGVhc2UuYXBr&code='.$code.'&dir=L2RhdGEvd3d3cm9vdC9kZWZhdWx0L3VwbG9hZC9hcGtz&data_key=1001';
        $res = $this->get_arr($apkurl);
        
        $data = $res['data'];
		$ret = 	$res['code'];
		
		$apk_url = '';
		if($ret==0)
		{
			$apk_url ='http://149.129.81.232:8088/upload/apks'.$data['filePath'];
		}
		$agentcode='techspace-code#'.$code;
        $this->assign('uid',$uid);
        $this->assign('agentcode',$agentcode);
        $this->assign('apk_url',$apk_url);

		$this->display();
	    
	}

	protected function get_arr($url){
		$rs = array('code'=>0,'msg'=>'','data'=>array());
		$ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_TIMEOUT,3);  
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		 
	    $data = curl_exec($ch);
		$errno = curl_errno($ch);
		if ($errno) {
			 try{
				 $rs['code']= $errno;
				 $rs['msg']= curl_error($ch);
				 return $rs;
			 }finally{
				 curl_close($ch); 
			 }
        }
		curl_close($ch);
		$rs['data'] = json_decode($data,true);
	    return  $rs;
	}


}