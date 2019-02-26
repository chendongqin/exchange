<?php
/**
 * Created by PhpStorm.
 * User: Viter
 * Date: 2018/3/4
 * Time: 14:56
 */
namespace app\admin\controller;
use base\Adminbase;
use think\Session;
class Logout extends Adminbase{

    //退出登陆
    public function index(){
        $session = new Session();
        $session->delete('admin_user');
        return $this->returnJson('退出成功',1001,true);
    }
}