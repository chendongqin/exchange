<?php
/**
 * Created by PhpStorm.
 * User: Viter
 * Date: 2018/2/14
 * Time: 13:15
 */
namespace base;
use think\Session;
use think\Db;
class Userbase extends Base {
    protected $_ec = array(
//        'user'=>array( 'index'),
    );
    protected $_ac = array(
//        'index'=>'*',
    );
    protected function _initialize() {
//        parent::_initialize();
        $user = Session::get('user');
        $user = isset($user[0])?$user[0]:$user;
        //$this->isFilter()判断该访问方法是否为过滤访问方法
        if($this->isFilter()===false){
            if(empty($user)){
                return $this->redirect('/user/login');
            }
        }
        $user = Db::name('users')->where('id',$user['id'])->find();
        $this->assign('user',$user);
        Session::delete('user');
        Session::push('user',$user);
    }

    protected function isFilter(){
        $request = $this->request;
        $module = strtolower($request->module());
        $controller = strtolower($request->controller());
        $action = strtolower($request->action());
        if(!isset($this->_ec[$module])){
            return false;
        }
        if(!in_array($controller,$this->_ec[$module])){
            return false;
        }
        if($this->_ac[$controller]== '*'){
            return true;
        }elseif(is_array($this->_ac[$controller]) and in_array($action,$this->_ac[$controller])){
            return true;
        }else{
            return false;
        }
    }

    protected function getLoginUser(){
        $loginUser = Session::get('user');
        $loginUser = isset($loginUser[0])?$loginUser[0]:$loginUser;
        return $loginUser;
    }

}