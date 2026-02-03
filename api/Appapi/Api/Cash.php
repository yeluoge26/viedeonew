<?php
/**
 * 提现/充值 API - 已禁用
 * Cash/Charge API - DISABLED
 */
class Api_Cash extends PhalApi_Api {

	public function getRules() {
		return array(
			'getAccountList' => array(
				'uid' => array('name' => 'uid', 'type' => 'int', 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'desc' => '用户token'),
			),
            'setAccount' => array(
				'uid' => array('name' => 'uid', 'type' => 'int', 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'desc' => '用户token'),
			),
            'delAccount' => array(
				'uid' => array('name' => 'uid', 'type' => 'int','desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'desc' => '用户token'),
			),
			'getProfit' => array(
				'uid' => array('name' => 'uid', 'type' => 'int','desc' => '用户ID'),
				'token' => array('name' => 'token', 'type' => 'string', 'desc' => '用户token'),
			),
			'setCash' => array(
				'uid' => array('name' => 'uid', 'type' => 'int','desc' => '用户ID'),
				'token' => array('name' => 'token', 'type' => 'string', 'desc' => '用户token'),
			),
		);
	}

	// 已禁用 - Payment disabled
	public function getAccountList() {
		return array('code' => 1000, 'msg' => '功能已禁用', 'info' => array());
	}

	// 已禁用 - Payment disabled
	public function setAccount() {
		return array('code' => 1000, 'msg' => '功能已禁用', 'info' => array());
	}

	// 已禁用 - Payment disabled
	public function delAccount() {
		return array('code' => 1000, 'msg' => '功能已禁用', 'info' => array());
	}

	// 已禁用 - Payment disabled
	public function getProfit() {
		return array('code' => 1000, 'msg' => '功能已禁用', 'info' => array());
	}

	// 已禁用 - Payment disabled
	public function setCash() {
		return array('code' => 1000, 'msg' => '功能已禁用', 'info' => array());
	}
}
