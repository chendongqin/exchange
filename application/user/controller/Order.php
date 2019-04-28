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
     private $goodsStatus = array('待回复','成功','失败');

    //用户发起匹配成功订单列表
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

     //用户请求匹配成功订单列表
     public function pipei()
     {
         $pageLimit = $this->getParam('pageLimit',10,'int');
         $page = $this->getParam('page',1,'int');
         $user = Session::get('user');
         $user = isset($user[0])?$user[0]:$user;
         $where = array('changer_id'=>$user['id']);
         $pager = Db::name('order')
             ->where($where)
             ->paginate($pageLimit,false,array('page'=>$page))
             ->toArray();
         $data = [];
         foreach ($pager['data'] as $key =>$item){
             $goods = Db::name('goods')->where('id',$item['goods_id'])->find();
             $request = Db::name('request')->where('id',$item['request_id'])->find();
             $data[$key]['status'] = $this->orderStatus[$item['status']];
             $changer = Db::name('users')->where('id',$item['user_id'])->find();
             $data[$key]['goodsUser'] = empty($changer['nick_name'])?$changer['email']:$changer['nick_name'];
             $data[$key]['goodsName'] = $goods['name'];
             $data[$key]['changeGoodsName'] = $request['change_goods_name'];
         }
         $pager['data'] = $data;
         $this->assign('pager',$pager);
         $this->assign('pageLimit',$pageLimit);
         $this->assign('page',$page);
         return $this->fetch();
     }


     //订单详情
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
        $changer = Db::name('users')->where('id',$order['changer_id'])->find();
        $this->assign('goods',$goods);
        $this->assign('request',$request);
        $this->assign('changer',$changer);
        return $this->fetch();
    }

    //发布商品列表
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
        $pager = Db::name('goods')
            ->where($where)
            ->paginate($pageLimit,false,array('page'=>$page))
            ->toArray();
        $data = [];
        foreach ($pager['data'] as $key=>$item){
            $data[$key] = $item;
            $data[$key]['requestNum'] = Db::name('request')->where('goods_id',$item['id'])->count();
        }
        $pager['data'] = $data;
        $this->assign('pager',$pager);
        $this->assign('pageLimit',$pageLimit);
        $this->assign('page',$page);
        return $this->fetch();
    }

    //发布商品置换对应请求列表
    public function goodsdetail(){
        $user = Session::get('user');
        $user = isset($user[0])?$user[0]:$user;
        $goodsId = $this->getParam('id','','int');
        if(!$goods = Db::name('goods')->where(array('user_id'=>$user['id'],'id'=>$goodsId))->find()){
            throw new Exception('置换商品不存在');
        }
        $this->assign('goods',$goods);
        $request = Db::name('request')->where('goods_id',$goodsId)->select();
        foreach ($request as $key=>$value){
            $request[$key]['status'] = $this->goodsStatus[$value['status']];
            $user = Db::name('users')->where('id',$value['user_id'])->find();
            $request[$key]['userName'] = empty($user['nick_name'])?$user['email']:$user['nick_name'];
        }
        $this->assign('request',$request);
        return $this->fetch();
    }


    //匹配成功
    public function add(){
        $user = Session::get('user');
        $user = isset($user[0])?$user[0]:$user;
        $goodsId= $this->getParam('goodsId');
        $requestId= $this->getParam('requestId');
        $userAddress = $this->getParam('userAddress');
        $userAddressee = $this->getParam('userAddressee');
        $addressMobile = $this->getParam('addressMobile');
        if(empty($userAddress) ||empty($userAddressee) ||empty($addressMobile))
            return $this->returnJson('收货地址、收件人、收件人手机号不能空');
        if(!$goods = Db::name('goods')->where(array('id'=>$goodsId,'user_id'=>$user['id']))->find()){
            return $this->returnJson('您没有权限操作该商品');
        }
        if(!$request = Db::name('request')->where(array('id'=>$requestId,'goods_id'=>$goodsId))->find())
            return $this->returnJson('请求不存在');
        $data = array(
            'request_id'=>$requestId,
            'goods_id'=>$goodsId,
            'user_id'=>$user['id'],
            'changer_id'=>$request['user_id'],
            'user_address'=>$userAddress,
            'user_addressee'=>$userAddressee,
            'user_mobile'=>$addressMobile
        );
        //开启数据库事务，完成多表操作，全部成功则提交，否则回滚
        Db::startTrans();
        $res = Db::name('order')->insert($data);
        if(!$res){
            Db::rollback();
            return $this->returnJson('失败');
        }
        $res = Db::name('goods')->where('id',$goodsId)->update(array('status'=>2));
        if(!$res){
            Db::rollback();
            return $this->returnJson('失败');
        }
        $res = Db::name('request')->where('id',$requestId)->update(array('status'=>1));
        if(!$res){
            Db::rollback();
            return $this->returnJson('失败');
        }
        Db::commit();
        Db::name('request')->where(array('goods_id'=>$goodsId))->update(array('status'=>2));
        return $this->returnJson('成功',1001,true);
    }

    //匹配成功，请求匹配人填写地址
    public function over(){
        $user = Session::get('user');
        $user = isset($user[0])?$user[0]:$user;
        $orderId = $this->getParam('orderId');
        if(!$order = Db::name('order')->where(array('id'=>$orderId,'changer_id'=>$user['id'],'status'=>0))->find())
            return $this->returnJson('没有订单');
        $userAddress = $this->getParam('userAddress');
        $userAddressee = $this->getParam('userAddressee');
        $addressMobile = $this->getParam('addressMobile');
        if(empty($userAddress) ||empty($userAddressee) ||empty($addressMobile))
            return $this->returnJson('收货地址、收件人、收件人手机号不能空');
        $order['changer_address'] = $userAddress;
        $order['changer_addressee'] = $userAddressee;
        $order['changer_mobile'] = $addressMobile;
        $order['status'] = 1;
        $res = Db::name('order')->update($order);
        if(!$res)
            return $this->returnJson('失败');
        return $this->returnJson('成功',1001,true);
    }

    //取消置换
    public function cancel(){
        $user = Session::get('user');
        $user = isset($user[0])?$user[0]:$user;
        $orderId = $this->getParam('orderId');
        $where = array(
            'id'=>$orderId,
            'status'=>0,
            'user_id|changer_id'=>$user['id']
        );
        if(!$order = Db::name('order')->where($where)->find())
            return $this->returnJson('数据错误或无法取消');
        $order['status'] = 2;
        $res = Db::name('order')->update($order);
        if(!$res)
            return $this->returnJson('失败');
        return $this->returnJson('成功',1001,true);
    }

 }