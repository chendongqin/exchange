<?php
/**
 * Created by PhpStorm.
 * User: Viter
 * Date: 2018/2/14
 * Time: 13:15
 */
namespace base;
use think\Session;

class Teacherbase extends Base {
    protected $_ec = array(
//        'user'=>array( 'index'),
    );
    protected $_ac = array(
//        'index'=>'*',
    );
    protected function _initialize() {
//        parent::_initialize();
        $owner = Session::get('teacher');
        $owner = isset($owner[0])?$owner[0]:$owner;
        //$this->isFilter()判断该访问方法是否为过滤访问方法
        if($this->isFilter()===false){
            if(empty($owner)){
                return $this->returnJson('未登录',9000,false,array('url'=>'/teacher/login'));
                die();
            }
        }
        $this->assign('teacher',$owner);
        Session::delete('teacher');
        Session::push('teacher',$owner);
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
        $owner = Session::get('teacher');
        $owner = isset($owner[0])?$owner[0]:$owner;
        return $owner;
    }

}