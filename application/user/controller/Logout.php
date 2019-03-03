<?php
/**
 * Created by PhpStorm.
 * User: Viter
 * Date: 2018/3/4
 * Time: 14:56
 */
namespace app\user\controller;
use base\Userbase;
use think\Session;
class Logout extends Userbase{

    //退出登陆
    public function index(){
        $session = new Session();
        $session->delete('user');
        return $this->redirect('/user/login');
    }
}