<?php
namespace app\user\controller;
use app\index\model\Users;
use base\Base;
use think\Cache;
use think\Session;

class login extends Base{

    public function index(){
        return $this->fetch();
    }

    public function i(){
        $owner = Session::get('user');
        $owner = isset($owner[0])?$owner[0]:$owner;
        if(!empty($owner)){
            return $this->returnJson('已登陆',9999);
        }
        $username = $this->getParam('userName','');
        $password = $this->getParam('password','');
        $code = $this->getParam('code','');
        $loginCode = Cache::get('userLogin'.$username);
        if($loginCode != $code){
            return $this->returnJson('验证码不正确',1000);
        }
        $model = new Users();
        $owner = $model->where(array('email'=>$username))->find();
        if(empty($owner)){
            return $this->returnJson('用户名不存在',1000);
        }
        if(!$this->virefyPwd($owner['password'],$password)){
            return $this->returnJson('密码不正确',1000);
        }
        Session::push('user',$owner);
        return $this->returnJson('登陆成功',1001,true);
    }

}
