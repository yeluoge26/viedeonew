<?php

class Domain_Shop {
	public function isShop($uid) {
		$rs = array();

		$model = new Model_Shop();
		$rs = $model->isShop($uid);

		return $rs;
	}
    
	public function getShop($uid) {
		$rs = array();

		$model = new Model_Shop();
		$rs = $model->getShop($uid);

		return $rs;
	}

	public function setGoods($data) {
		$rs = array();

		$model = new Model_Shop();
		$rs = $model->setGoods($data);

		return $rs;
	}

	public function upHits($videoid) {
		$rs = array();

		$model = new Model_Shop();
		$rs = $model->upHits($videoid);

		return $rs;
	}

	public function getGoodsList($uid,$p) {
		$rs = array();

		$model = new Model_Shop();
		$rs = $model->getGoodsList($uid,$p);

		return $rs;
	}
	
}
