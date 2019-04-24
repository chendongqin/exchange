<?php
/**
 * Created by PhpStorm.
 * User: Viter
 * Date: 2018/3/4
 * Time: 15:41
 */
namespace app\user\controller;
use base\Userbase;
use think\Cache;
use think\Session;
use think\Db;
use ku\Upload;

 class Goods extends Userbase {

    //发布商品列表与order goods 重复了
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


    //发布置换商品
    public function add()
    {
        $user = Session::get('user');
        $user = isset($user[0])?$user[0]:$user;
        $data['name'] = $this->getParam('name');
        $data['price'] = $this->getParam('price');
        $data['describe'] = $this->getParam('describe');
        $data['photo'] = $this->getParam('photo');
        $data['want_to_goods'] = $this->getParam('want_to_goods');
        if(empty($data['name']) || empty($data['describe']) || empty($data['photo'])|| empty($data['want_to_goods'])){
            return $this->returnJson('名称，描述，意向商品，图片不能空');
        }
        $data['user_id'] = $user['id'];
        $data['status'] = 1;
        $res = Db::name('goods')->insert($data);
        if($res){
            return $this->returnJson('失败');
        }
        return $this->returnJson('成功',1001,true);
    }

    //修改
    public function update(){
        $user = Session::get('user');
        $user = isset($user[0])?$user[0]:$user;
        $id = $this->getParam('id');
        $where = ['id'=>$id,'user_id'=>$user['id']];
        if(!$goods = Db::name('goods')->where($where)->find()){
            return $this->returnJson('您没有权限修改');
        }
        $goods['name'] = $this->getParam('name');
        $goods['price'] = $this->getParam('price');
        $goods['describe'] = $this->getParam('describe');
        $goods['photo'] = $this->getParam('photo');
        $goods['want_to_goods'] = $this->getParam('want_to_goods');
        if(empty($goods['name']) || empty($goods['describe']) || empty($goods['photo'])|| empty($goods['want_to_goods'])){
            return $this->returnJson('名称，描述，意向商品，图片不能空');
        }
        $res = Db::name('goods')->update($goods);
        if($res){
            return $this->returnJson('失败');
        }
        return $this->returnJson('成功',1001,true);
    }

     /**
      * 图片上传
      */
     public function imgupload(){
         //防止恶意上传操作
         $user = Session::get('user');
         $user = isset($user[0])?$user[0]:$user;
         $uploadTimes = (int)Cache::get(__CLASS__.__FUNCTION__.$user['id']);
         if($uploadTimes === false){
             Cache::set(__CLASS__.__FUNCTION__.$user['id'],1,600);
         }else{
             Cache::set(__CLASS__.__FUNCTION__.$user['id'],$uploadTimes+1,600);
         }
         if($uploadTimes >30){
             return $this->returnJson('多次操作被限制',1000);
         }
         $upload = new Upload();
         $upload->setFormName('upload_img');
         $result = $upload->exec();
         if(!$result){
             return $this->returnJson('文件未上传',1000);
         }
         $path = $upload->path('/uploads/goods/');
         $upload->buildCode();
         $code = $upload->getRetval();
         $fileName = $path.$code['code'].'.'.$upload->getFileSuffix();
         $result = $upload->moveFile($fileName);
         if(!$result){
             return $this->returnJson('文件上传失败',1000);
         }
         $fileName = str_replace(PUBLIC_PATH,'',$fileName);
         return $this->returnJson('上传成功',true,1,array('fileName'=>$fileName));
     }

 }