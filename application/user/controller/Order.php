<?php
/**
 * Created by PhpStorm.
 * User: Viter
 * Date: 2018/3/4
 * Time: 15:41
 */
namespace app\user\controller;
use base\Userbase;
use think\Exception;
use think\Session;
use think\Db;

 class Order extends Userbase {

     private $orderStatus = array('处理中','交易成功','交易失败');

    //用户基本信息
    public function index()
    {
        $pageLimit = $this->getParam('pageLimit',10,'int');
        $page = $this->getParam('page',1,'int');
        $user = Session::get('user');
        $user = isset($user[0])?$user[0]:$user;
        $where = array('user_id'=>$user['id']);
        $pager = Db::name('order')
            ->where($where)
            ->paginate($pageLimit,false,array('page'=>$page))
            ->toArray();
        $data = [];
        foreach ($pager['data'] as $key =>$item){
            $goods = Db::name('goods')->where('id',$item['goods_id'])->find();
            $request = Db::name('request')->where('id',$item['request_id'])->find();
            $data[$key]['status'] = $this->orderStatus[$item['status']];
            $changer = Db::name('users')->where('id',$item['changer_id'])->find();
            $data[$key]['changer'] = empty($changer['nick_name'])?$changer['email']:$changer['nick_name'];
            $data[$key]['goodsName'] = $goods['name'];
            $data[$key]['changeGoodsName'] = $request['change_goods_name'];
        }
        $pager['data'] = $data;
        $this->assign('pager',$pager);
        $this->assign('pageLimit',$pageLimit);
        $this->assign('page',$page);
        return $this->fetch();
    }

    public function detail(){
        $user = Session::get('user');
        $user = isset($user[0])?$user[0]:$user;
        $id= $this->getParam('id',0,'int');
        $order = Db::name('order')->where(array('id'=>$id,'user_id'=>$user['id']))->find();
        if(!$order){
            throw new Exception('没有找到记录');
        }
        $goods = Db::name('goods')->where('id',$order['goods_id'])->find();
        $request = Db::name('request')->where('id',$order['request_id'])->find();
//        if(!empty($request['change_goods_id'])){
//            $requestGoods = Db::name('goods')->where('id',$request['change_goods_id'])->find();
//            $request['change_goods_name'] = $requestGoods['name'];
//        }
        $changer = Db::name('users')->where('id',$item['changer_id'])->find();
        $this->assign('goods',$goods);
        $this->assign('request',$request);
        $this->assign('changer',$changer);
        return $this->fetch();
    }


    public function goods(){
        $user = Session::get('user');
        $user = isset($user[0])?$user[0]:$user;
        $name = $this->getParam('name');
        $status = $this->getParam('status',100,'int');
        $pageLimit = $this->getParam('pageLimit',10,'int');
        $page = $this->getParam('page',1,'int');
        $where = array('isdel'=>0,'user_id'=>$user['id']);
        if($name){
            $where['name'] = array('like',$name.'%');
        }
        if($status != $status){
            $where['status']=$status;
        }
        $pager = Db::name('order')
            ->where($where)
            ->paginate($pageLimit,false,array('page'=>$page))
            ->toArray();
        $this->assign('pager',$pager);
        $this->assign('pageLimit',$pageLimit);
        $this->assign('page',$page);
        return $this->fetch();
    }


     public function request(){
         $user = Session::get('user');
         $user = isset($user[0])?$user[0]:$user;
         $name = $this->getParam('name');
         $status = $this->getParam('status',100,'int');
         $pageLimit = $this->getParam('pageLimit',10,'int');
         $page = $this->getParam('page',1,'int');
         $where = array('isdel'=>0,'user_id'=>$user['id']);
         if($name){
             $where['change_goods_name'] = array('like',$name.'%');
         }
         if($status != $status){
             $where['status']=$status;
         }
         $pager = Db::name('order')
             ->where($where)
             ->paginate($pageLimit,false,array('page'=>$page))
             ->toArray();
         $data = [];
         foreach ($pager['data'] as $key=>$item){
             $data[$key] = $item;
             $data[$key]['goods'] = Db::name('goods')->where('id',$item['goods_id'])->find();
         }
         $pager['data'] = $data;
         $this->assign('pager',$pager);
         $this->assign('pageLimit',$pageLimit);
         $this->assign('page',$page);
         return $this->fetch();
     }

 }