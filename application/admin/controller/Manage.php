<?php
namespace app\admin\controller;
use base\Adminbase;
use think\Db;
use think\Session;
class Manage extends Adminbase
{

    public function index()
    {
        //置换请求数量
        $goodsWaitNum = Db::name('goods')->where(array('status'=>0))->count();
        $goodsIssueNum = Db::name('goods')->where(array('status'=>1))->count();
        //置换匹配请求数量
        $requestWaitNum = Db::name('request')->where(array('status'=>0))->count();
        $requestSuccessNum = Db::name('request')->where(array('status'=>1))->count();
        $requestfailNum = Db::name('request')->where(array('status'=>2))->count();
        //置换匹配成功数量
        $success = Db::name('order')->where(array('status'=>1))->count();
        //取消失败数量
        $fail = Db::name('order')->where(array('status'=>2))->count();
        //用户总数
        //男
        $man = Db::name('users')->where(array('isdel'=>0,'sex'=>0))->count();
        $nv = Db::name('users')->where(array('isdel'=>0,'sex'=>1))->count();
        $this->assign('goodsWaitNum',$goodsWaitNum);
        $this->assign('goodsIssueNum',$goodsIssueNum);
        $this->assign('requestWaitNum',$requestWaitNum);
        $this->assign('requestSuccessNum',$requestSuccessNum);
        $this->assign('requestfailNum',$requestfailNum);
        $this->assign('success',$success);
        $this->assign('fail',$fail);
        $this->assign('man',$man);
        $this->assign('nv',$nv);
        return $this->fetch();

    }

    /**
     * 置换请求列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function goods()
    {
        $name = $this->getParam('name','','string');
        $pageLimit = $this->getParam('pageLimit','','int');
        $page = $this->getParam('page','','int');
        $status = $this->getParam('status',1,'int');
        $where = ['status'=>$status ,'isdel'=>0];
        if($name){
            $where['name'] = array('like',$name.'%');
        }
        $goods = Db::name('goods')->where($where)->paginate($pageLimit,false,array('page'=>$page))->toArray();
        $this->assign('pager',$goods);
        $this->assign('pageLimit',$pageLimit);
        $this->assign('page',$page);
        return $this->fetch();
    }

    /**
     * 下线置换请求
     * @param $id
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function downgoods()
    {
        $id = $this->getParam('id','','int');
        $where =array('id'=>$id,'status'=>1 ,'isdel'=>0);
        $goods = Db::name('goods')->where($where)->find();
        if(empty($goods)){
            return $this->returnJson('置换请求不存在或不在发布状态');
        }
        $goods['isdel'] = 1;
        $res = Db::name('goods')->update($goods);
        if($res){
            return $this->returnJson('成功',1001,true);
        }
        return $this->returnJson('失败');
    }

    //举报列表
    public function report(){
        $type = $this->getParam('type',100,'int');
        $userId = $this->getParam('userId',0,'int');
        $pageLimit = $this->getParam('pageLimit',15,'int');
        $isdeal = $this->getParam('isDeal',100,'int');
        $page = $this->getParam('page',0,'int');
        $reportUserId = $this->getParam('reportuserId',0,'int');
        $where = [];
        if($isdeal != 100){
            $where['is_deal'] = $isdeal;
        }
        if($userId){
            $where['user_id'] = $userId;
        }
        if($reportUserId){
            $where['report_user_id'] = $reportUserId;
        }
        if($type !=100){
            $where['type'] = $type;
        }
        $pager = Db::name('report')->where($where)->paginate($pageLimit,false,array('page'=>$page))->toArray();
        $this->assign('pager',$pager);
        $this->assign('pageLimit',$pageLimit);
        $this->assign('page',$page);
        return $this->fetch();
    }

    /**
     * 更新已处理举报
     * @param $id
     * @param int $del
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function dealreport($id , $del = 0){
        $report = Db::name('report')->where(array('id'=>$id,'is_deal'=>0))->find();
        if(!$report){
            return $this->returnJson('没有举报需要处理');
        }
        //如果是删除
        if($del){
            $res = Db::name('report')->where('id',$id)->delete(true);
            if(!$res){
                Db::rollback();
                return $this->returnJson('失败');
            }
            return $this->returnJson('删除成功',1001,true);
        }
        $report['is_deal'] = 1;
        Db::startTrans();
        $res = Db::name('report')->update($report);
        if(!$res){
            Db::rollback();
            return $this->returnJson('失败');
        }
        $report_user = Db::name('users')->where('id', $report['report_user_id'])->find();
        $user_credit = $report_user['credit'] > 20 ? 20 : $report_user['credit'];
        $res = Db::name('users')->where('id',$report['report_user_id'])->setDec('credit',$user_credit);
        if (!$res) {
            Db::rollback();
            return $this->returnJson('失败');
        }
        Db::commit();
        return $this->returnJson('成功',1001,true);
    }

    public function userlist(){
        $isEffect = $this->getParam('isEffect',1,'int');
        $name = $this->getParam('name','','string');
        $pageLimit = $this->getParam('pageLimit',15,'int');
        $page = $this->getParam('page','','int');
        $where = array('isdel'=>0 ,'is_effect'=>$isEffect);
        if($name){
            $where['nick_name'] = array('like',$name.'%');
        }
        $pager = Db::name('users')->where($where)->paginate($pageLimit,false,array('page'=>$page))->toArray();
        $this->assign('pager',$pager);
        $this->assign('pageLimit',$pageLimit);
        $this->assign('page',$page);
        return $this->fetch();
    }

    //审核用户
    public function auidtuser(){
        $userId = $this->getParam('userId',0,'int');
        $user = Db::name('users')->where(array('id'=>$userId,'isdel'=>0))->find();
        $user['is_effect'] = 1;
        $res = Db::name('users')->update($user);
        if($res){
            return $this->returnJson('成功',1001,true);
        }
        return $this->returnJson('失败');
    }

    //删除|冻结用户
    public function deluser(){
        $userId = $this->getParam('userId',0,'int');
        $user = Db::name('users')->where(array('id'=>$userId,'isdel'=>0))->find();
        $user['isdel'] = 1;
        $res = Db::name('users')->update($user);
        if($res){
            return $this->returnJson('成功',1001,true);
        }
        return $this->returnJson('失败');
    }


}