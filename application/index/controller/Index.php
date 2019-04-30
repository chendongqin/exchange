<?php
/**
 * Created by PhpStorm.
 * User: Viter
 * Date: 2018/3/4
 * Time: 15:41
 */
namespace app\index\controller;
use think\Config;
use think\Session;
use base\Base;
use think\Db;
 class Index extends Base{

     /**首页数据
      * @return mixed
      * @throws \think\db\exception\DataNotFoundException
      * @throws \think\db\exception\ModelNotFoundException
      * @throws \think\exception\DbException
      */
     public function index(){
         $pageLimit = $this->getParam('pageLimit',10,'int');
         $page = $this->getParam('page',1,'int');
         $name = $this->getParam('name');
         $wantToGoods = $this->getParam('wantToGoodsName');
         $where = array('status'=>1);
         if($name)
             $where['name'] = array('like',$name.'%');
         if($wantToGoods)
             $where['want_to_goods'] = array('like',$wantToGoods.'%');
         $pager = Db::name('goods')->where($where)
             ->paginate($pageLimit,false,array('page'=>$page))
             ->toArray();
         $data = [];
         foreach ($pager['data'] as $key=>$item){
             $data[$key] = $item;
             $user = Db::name('users')->where('id',$item['user_id'])->find();
             $data[$key]['userName'] = empty($user['nick_name'])?$user['email']:$user['nick_name'];
         }
         $pager['data'] = $data;
         $this->assign('pager',$pager);
         $this->assign('pageLimit',$pageLimit);
         $this->assign('page',$page);
         return $this->fetch();
     }

     //获取系统公告
     public function sysmsg(){
         $pageLimit = $this->getParam('pageLimit',10,'int');
         $page = $this->getParam('page',1,'int');
         $pager = Db::name('system_msg')->order(array('weight'=>'desc','id'=>'desc'))
             ->paginate($pageLimit,false,array('page'=>$page))
             ->toArray();
         $data = $pager['data'];
         return $this->returnJson('成功',1001,true,$data);
     }


     public function is_login()
     {
         $user = Session::get('user');
         $user = isset($user[0])?$user[0]:$user;
         if(empty($user)){
             return $this->returnJson('您没有登录');
         }
         return $this->returnJson('已登录',1001,true);
     }

 }