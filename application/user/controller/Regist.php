<?php
namespace app\user\controller;
use app\user\model\Users;
use base\Base;
use think\Cache;
use think\Session;
use ku\Verify;

class Regist extends Base{

    /**用户注册页面
     * @return mixed
     */
    public function index(){
          $owner = Session::get('user');
        $owner = isset($owner[0])?$owner[0]:$owner;
        if(!empty($owner)){
           return $this->redirect('/user');
        }
        return $this->fetch();
    }

    /**
     * 用户注册接口
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function i(){
        $owner = Session::get('user');
        $owner = isset($owner[0])?$owner[0]:$owner;
        if(!empty($owner)){
            return $this->returnJson('您已是登陆状态');
        }
        $email = $this->getParam('email','');
        if(!Verify::isEmail($email)){
           return $this->returnJson('邮箱不正确');
        }
        $password = $this->getParam('password','');
        $sure = $this->getParam('sure','');
        $code = $this->getParam('code','');
        $virefyCode = Cache::get('reg'.$email);
        if($virefyCode != $code){
            return $this->returnJson('验证码不正确',1000);
        }
        $model = new Users();
        $owner = $model->where(array('email'=>$email))->find();
        if(!empty($owner)){
            return $this->returnJson('该邮箱已注册',1002);
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
                'email'=>$email,
                'password'=>$pwd,
                'create_time'=>date('Y-m-d H:i:s'),
                )
        );
        $model->save();
        $owner = $model->where(array('email'=>$email))->find();
        Session::push('user',$owner);
        return $this->returnJson('注册成功',1001,true);
    }

}