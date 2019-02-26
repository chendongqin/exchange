<?php

namespace app\admin\controller;
use base\Base;
use think\Db;
use think\Session;

class Login extends Base{

    /**后台登陆页面
     * @return mixed
     */
    public function index(){
        return $this->fetch();
    }

    public function i(){
        $admin = Session::get('admin_user');
        $admin = isset($admin[0])?$admin[0]:$admin;
        if(!empty($admin)){
            return $this->returnJson('用户已登陆',1001,true);
        }
        $useName = $this->getParam('userName','','string');
        $password = $this->getParam('password','','string');
        $admin = Db::name('admin')->where(array('login_name'=>$useName))->find();
        if(empty($admin)){
            return $this->returnJson('用户名不存在',1000);
        }
        $res = $this->virefyPwd($admin['password'],$password);
        if(!$res){
            return $this->returnJson('密码错误',1000);
        }
        Session::push('admin_user',$admin);
        $admin['login_at'] = date('Y-m-d H:i:s');
        Db::name('admin')->update($admin);
        return $this->returnJson('登陆成功',1001,true);
    }

}