<?php
namespace app\admin\controller;
use base\Adminbase;
use think\Db;
use think\Session;
class Index extends Adminbase
{
    public function index()
    {
        $name = $this->getParam('name','','string');
        $pageLimit = $this->getParam('pageLimit',15,'int');
        $page = $this->getParam('page',1,'int');
        $where = [];
        if($name){
            $where['login_name|admin_name'] = array('like','%'.$name.'%');
        }
        $admins = Db::name('admin')->where($where)->paginate($pageLimit,false,array('page'=>$page))->toArray();
        $this->assign('pager',$admins);
        $this->assign('pageLimit',$pageLimit);
        $this->assign('page',$page);
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
        $admin = Session::get('admin_user');
        $admin = $admin[0];
        if(!$this->virefyPwd($admin['password'],$pwd)){
            return $this->returnJson('原密码不正确');
        }
        if($newpwd != $sure){
            return $this->returnJson('两次密码不一致');
        }
        if(strlen($newpwd)<6){
            return $this->returnJson('密码长度需要大于6位');
        }
        $admin['password'] = $this->createPwd($newpwd);
        Db::name('admin')->update($admin);
        Session::delete('admin_user');
        return $this->returnJson('修改成功',1001,true);

    }

    public function delete()
    {
        $id = $this->getParam('id',0,'int');
        $admin = Db::name('admin')->where('id',$id)->find();
        if(!$admin){
            return $this->returnJson('没有该管理员');
        }
        $res = Db::name('admin')->delete(array('id'=>$id));
        if($res){
            return $this->returnJson('删除成功',1001,true);
        }
        return $this->returnJson('删除失败');
    }

    public function add(){
        $name = $this->getParam('name');
        $password = $this->getParam('password');
        if(empty($name) || empty($password)){
            return $this->returnJson('用户名和密码不能为空');
        }
        $data = array(
          'login_name'=>$name,
          'admin_name'=>$name,
          'password'=>$this->createPwd($password),
        );
        $res = Db::name('admin')->insert($data);
        if($res){
            return $this->returnJson('创建成功',1001,true);
        }
        return $this->returnJson('创建失败');
    }

}