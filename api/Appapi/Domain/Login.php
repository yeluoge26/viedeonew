<?php

class Domain_Login {

    public function userLogin($user_login,$source) {
        $rs = array();

        $model = new Model_Login();
        $rs = $model->userLogin($user_login,$source);

        return $rs;
    }

	

    public function userLoginByThird($openid,$type,$nickname,$avatar,$source) {
        $rs = array();

        $model = new Model_Login();
        $rs = $model->userLoginByThird($openid,$type,$nickname,$avatar,$source);

        return $rs;
    }			

}
