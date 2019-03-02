<?php

namespace app\index\controller;
use think\Config;
use think\Cache;
use base\Base;
use ku\Verify;
use ku\Email as kuEmail;
class Email extends Base
{

    //注册邮箱验证码发送
    public function regist()
    {
        $request = $this->request;
        $email = $request->param('email','','string');
        if(!Verify::isEmail($email)){
            return json(array('status'=>false,'msg'=>'邮箱格式不正确'));
        }
        $code = mt_rand(100000,999999);
        $cacheRes = Cache::set('reg'.$email,$code,200);
        if(!$cacheRes){
            return json(array('status'=>false,'msg'=>'验证码存储错误'));
        }
        $subject = '用品置换系统注册';
        $body = '验证码：'.$code;
        $res = kuEmail::sendEmail($email,$subject,$body);
        if($res){
            return json(array('status'=>true,'msg'=>'发送成功'));
        }else{
            return json(array('status'=>false,'msg'=>'发送失败'));
        }
    }

}