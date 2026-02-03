<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2014 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------

namespace Appapi\Controller;
use Common\Controller\HomebaseController;
/**
 * 支付回调 - 已禁用
 * Payment callbacks - DISABLED
 */
class PayController extends HomebaseController {

	//支付宝 回调 - 已禁用
	public function notify_ali() {
		echo json_encode(array('status' => 'error', 'msg' => 'Payment disabled'));
		exit;
	}

	// 微信支付 回调 - 已禁用
	public function notify_wx(){
		echo json_encode(array('status' => 'error', 'msg' => 'Payment disabled'));
		exit;
	}

	// 苹果支付 回调 - 已禁用
	public function notify_ios(){
		echo json_encode(array('status' => 'error', 'msg' => 'Payment disabled'));
		exit;
	}

}
