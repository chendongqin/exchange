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

 class Request extends Userbase {

    //用户基本信息
    public function index()
    {
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


    public function add(){
        $user = Session::get('user');
        $user = isset($user[0])?$user[0]:$user;
        $goodsId=  $this->getParam('goodsId',0,'int');
        $goods = Db::name('goods')->where(array('id'=>$goodsId,'status'=>1))->find();
        if(empty($goods))
            return $this->returnJson('没有该置换信息');
        if($goods['user_id'] == $user['id'])
            return $this->returnJson('您不能匹配自己的请求');
        $changeGoodsId = $this->getParam('changeId',0,'int');
        $changeGoodsName = $this->getParam('changeName');
        $changeGoodsDesc = $this->getParam('changeDescribe');
        if(!empty($changeGoodsId)){
            $changeGoods = Db::name('goods')->where(array('id'=>$goodsId,'status'=>1,'user_id'=>$user['id']))->find();
            if(empty($changeGoods))
                return $this->returnJson('您没有对应置换信息配匹配');
            $changeGoodsName = $changeGoods['name'];
            $changeGoodsDesc = $changeGoods['describe'];
        }
        if(empty($changeGoodsName)){
            return $this->returnJson('匹配的置换商品名称不能为空');
        }
        $data = [];
        $data['change_goods_id'] = $changeGoodsId;
        $data['change_goods_name'] = $changeGoodsId;
        $data['describe'] = $changeGoodsDesc;
        $data['user_id'] = $user['id'];
        $data['goods_id'] = $goodsId;
        $data['goods_user_id'] = $goods['user_id'];
        $res = Db::name('request')->insert($data);
        if($res)
            return $this->returnJson('成功',1001,true);
        return $this->returnJson('失败');

    }

    public function cancel(){
        $id =$this->getParam('id');
        $user = Session::get('user');
        $user = isset($user[0])?$user[0]:$user;
        $where =array('id'=>$id,'user_id'=>$user['id'],'status'=>0);
        if(!$request = Db::name('request')->where($where)->find())
            return $this->returnJson('删除的请求不存在');
        Db::name('request')->delete(array('id'=>$id));
        return $this->returnJson('成功',1001,true);
    }


 }