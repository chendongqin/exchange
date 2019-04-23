<?php
/**
 * Created by PhpStorm.
 * User: Viter
 * Date: 2018/3/4
 * Time: 15:41
 */
namespace app\user\controller;
use base\Userbase;
use ku\Verify;
use think\Session;
use think\Db;

 class Index extends Userbase {

    //用户基本信息
    public function index()
    {
        $user = Session::get('user');
        $user = isset($user[0])?$user[0]:$user;
        $address = Db::name('user_address')->where(array('user_id'=>$user['id'],'isdel'=>0))->select();
        $this->assign('user',$user);
        $this->assign('userAddress',$address);
        return $this->fetch();
    }

     /**
      * 修改密码
      * @return \think\response\Json
      * @throws \think\Exception
      * @throws \think\exception\PDOException
      */
     public function changepw()
     {
         $pwd = $this->getParam('pwd','','string');
         $newpwd = $this->getParam('newpwd','','string');
         $sure = $this->getParam('sure','','string');
         $user = Session::get('user');
         $user = isset($user[0])?$user[0]:$user;
         if(!$this->virefyPwd($user['password'],$pwd)){
             return $this->returnJson('原密码不正确');
         }
         if($newpwd != $sure){
             return $this->returnJson('两次密码不一致');
         }
         if(strlen($newpwd)<6){
             return $this->returnJson('密码长度需要大于6位');
         }
         $user['password'] = $this->createPwd($newpwd);
         Db::name('users')->update($user);
         Session::delete('user');
         return $this->returnJson('修改成功',1001,true);

     }

     //设置基本资料
    public function set(){
        $user = Session::get('user');
        $user = isset($user[0])?$user[0]:$user;
        $user['nick_name'] = $this->getParam('nickName','','string');
        $user['shoole'] = $this->getParam('shoole','','string');
        $user['major'] = $this->getParam('major','','string');
        $user['sex'] = $this->getParam('sex',0,'int');
        $res = Db::name('users')->update($user);
        if($res){
            return $this->returnJson('成功',1001,true);
        }
        return $this->returnJson('失败');
    }

    public function addAddress(){
        $user = Session::get('user');
        $user = isset($user[0])?$user[0]:$user;
        $address = $this->getParam('address');
        $isDefault = $this->getParam('isDefault',0,'int');
        $mobile = $this->getParam('mobile');
        $addressee = $this->getParam('addressee');
        if(empty($address) || empty($addressee)){
            return $this->returnJson('地址和收件人不能空');
        }
        if(!Verify::isMobile($mobile)){
            return $this->returnJson('手机格式不正确');
        }
        if($isDefault){
            Db::name('user_address')->where(array('user_id'=>$user['id']))->update(array('is_default'=>0));
        }
        $data = array(
          'user_id'=>$user['id'],
          'is_default'=>$isDefault,
          'mobile'=>$mobile,
          'address'=>$address,
          'addressee'=>$addressee,
        );
        Db::name('user_address')->insert($data);
        return $this->returnJson('添加成功',1001,true);
    }

    public function updateAddress(){
        $user = Session::get('user');
        $user = isset($user[0])?$user[0]:$user;
        $address = $this->getParam('address');
        $isDefault = $this->getParam('isDefault',0,'int');
        $id = $this->getParam('id',0,'int');
        $mobile = $this->getParam('mobile');
        $addressee = $this->getParam('addressee');
        if(empty($address) || empty($addressee)){
            return $this->returnJson('地址和收件人不能空');
        }
        if(!Verify::isMobile($mobile)){
            return $this->returnJson('手机格式不正确');
        }
        $myaddress = Db::name('user_address')->where(array('user_id'=>$user['id'],'id'=>$id))->find();
        if(!$myaddress){
            return $this->returnJson('地址不存在');
        }
        if($isDefault){
            Db::name('user_address')->where(array('user_id'=>$user['id']))->update(array('is_default'=>0));
        }
        $myaddress['user_id']=$user['id'];
        $myaddress['is_default']=$isDefault;
        $myaddress['mobile']=$mobile;
        $myaddress['address']=$address;
        $myaddress['addressee']=$addressee;
        $res = Db::name('user_address')->update($myaddress);
        if($res) 
            return $this->returnJson('成功',1001,true);
        return $this->returnJson('失败');
     }


     public function delAddress(){
         $user = Session::get('user');
         $user = isset($user[0])?$user[0]:$user;
         $id = $this->getParam('id',0,'int');
         $address = Db::name('user_address')->where(array('user_id'=>$user['id'],'id'=>$id))->find();
         if(!$address){
             return $this->returnJson('地址不存在');
         }
         $address['isdel'] = 1;
         Db::name('user_address')->update($address);
         return $this->returnJson('成功',1001,true);
     }

     /**
      * 获取地址
      * @return \think\response\Json
      * @throws \think\db\exception\DataNotFoundException
      * @throws \think\db\exception\ModelNotFoundException
      * @throws \think\exception\DbException
      */
     public function addressList(){
        $user = Session::get('user');
        $user = isset($user[0])?$user[0]:$user;
        $address = Db::name('user_address')->where(array('user_id'=>$user['id'],'isdel'=>0))->order('is_default','desc')->select();
        $this->assign('address',$address);
        return $this->fetch();
     }

 }