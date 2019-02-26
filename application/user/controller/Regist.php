<?php
namespace app\user\controller;
use app\index\model\Users;
use base\Base;
use think\Cache;
use think\Session;

class Regist extends Base{

    /**用户注册页面
     * @return mixed
     */
    public function index(){
        return $this->fetch();
    }

    /**用户注册接口
     * @return \think\response\Json|void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function i(){
        $owner = Session::get('teacher');
        $owner = isset($owner[0])?$owner[0]:$owner;
        if(!empty($owner)){
            return $this->redirect('/teacher');
        }
        $mobile = $this->getParam('mobile','');
        $password = $this->getParam('password','');
        $sure = $this->getParam('sure','');
        $code = $this->getParam('code','');
        $virefyCode = Cache::get('userReg'.$mobile);
        if($virefyCode != $code){
            return $this->returnJson('手机验证码不正确',1000);
        }
        $model = new Users();
        $owner = $model->where(array('mobile'=>$mobile))->find();
        if(!empty($owner)){
            return $this->returnJson('该手机已注册',1002);
        }
        if($sure !=$password){
            return $this->returnJson('两次密码不一致',1000);
        }
        if(strlen($password) <6 or strlen($password) >30){
            return $this->returnJson('密码长度在6到30位之间',1000);
        }
        $pwd = $this->createPwd($password);
        $model->data(
            array(
                'mobile'=>$mobile,
                'password'=>$pwd,
                'createTime'=>date('Y-m-d H:i:s'),
                'loginTime'=>date('Y-m-d H:i:s'),
                )
        );
        $model->save();
        $owner = $model->where(array('mobile'=>$mobile))->find();
        Session::push('teacher',$owner);
        return $this->returnJson('注册成功',1001,true);
    }

}