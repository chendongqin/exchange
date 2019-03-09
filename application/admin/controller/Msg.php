<?php
namespace app\admin\controller;
use base\Adminbase;
use think\Db;
use think\Session;
class Msg extends Adminbase
{
    public function index()
    {
        $name = $this->getParam('title','','string');
        $pageLimit = $this->getParam('pageLimit',15,'int');
        $page = $this->getParam('page','','int');
        $where = [];
        if($name){
            $where['title'] = array('like','%'.$name.'%');
        }
        $pager = Db::name('system_msg')->where($where)->paginate($pageLimit,false,array('page'=>$page))->toArray();
        $this->assign('pager',$pager);
        $this->assign('pageLimit',$pageLimit);
        $this->assign('page',$page);
        return $this->fetch();
    }

    public function add(){
        $title = $this->getParam('title');
        $content = $this->getParam('content');
        $weight = $this->getParam('weight',0,'int');
        $is_issue = $this->getParam('is_issue',0,'int');
        $data = array(
            'title'=>$title,
            'content'=>$content,
            'weight'=>$weight,
            'is_issue'=>$is_issue,
        );
        $res = Db::name('system_msg')->insert($data);
        if($res){
            return $this->returnJson('创建成功',1001,true);
        }
        return $this->returnJson('创建失败');
    }

    public function update(){
        $title = $this->getParam('title');
        $content = $this->getParam('content');
        $weight = $this->getParam('weight',0,'int');
        $is_issue = $this->getParam('is_issue',0,'int');
        $id = $this->getParam('id',0,'int');
        $msg = Db::name('system_msg')->where('id',$id)->find();
        if(!$msg){
            return $this->returnJson('公告不存在');
        }
        $msg['title'] = $title;
        $msg['content'] = $content;
        $msg['weight'] = $weight;
        $msg['is_issue'] = $is_issue;
        $res = Db::name('system_msg')->update($msg);
        if($res){
            return $this->returnJson('成功',1001,true);
        }
        return $this->returnJson('失败');
    }

    public function delete(){
        $id = $this->getParam('id',0,'int');
        $msg = Db::name('system_msg')->where('id',$id)->find();
        if(!$msg){
            return $this->returnJson('公告不存在');
        }
        $res = Db::name('system_msg')->delete(array('id'=>$id));
        if($res){
            return $this->returnJson('成功',1001,true);
        }
        return $this->returnJson('失败');
    }

}