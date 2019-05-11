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

class Order extends Userbase
{

    private $orderStatus = array('处理中', '交易成功', '交易失败');
    private $goodsStatus = array('待回复', '成功', '失败');

    //用户发起匹配成功订单列表
    public function index()
    {
        $pageLimit = $this->getParam('pageLimit', 10, 'int');
        $page = $this->getParam('page', 1, 'int');
        $user = Session::get('user');
        $user = isset($user[0]) ? $user[0] : $user;
        $where = ['user_id' => $user['id']];
        $pager = Db::name('order')
            ->where($where)
            ->paginate($pageLimit, false, array('page' => $page))
            ->toArray();
        $data = [];
        foreach ($pager['data'] as $key => $item) {
            $goods = Db::name('goods')->where('id', $item['goods_id'])->find();
            $request = Db::name('request')->where('id', $item['request_id'])->find();
            $data[$key]['id'] = $item['id'];
            $data[$key]['status'] = $this->orderStatus[$item['status']];
            $changer = Db::name('users')->where('id', $item['changer_id'])->find();
            $data[$key]['changer'] = empty($changer['nick_name']) ? $changer['email'] : $changer['nick_name'];
            $data[$key]['goodsName'] = $goods['name'];
            $data[$key]['changeGoodsName'] = $request['change_goods_name'];
            $data[$key]['changer_addressee'] = $item['changer_addressee'];
            $data[$key]['changer_address'] = $item['changer_address'];
            $data[$key]['changer_mobile'] = $item['changer_mobile'];
        }
        $pager['data'] = $data;
        $this->assign('pager', $pager);
        $this->assign('pageLimit', $pageLimit);
        $this->assign('page', $page);
        return $this->fetch();
    }

    //用户请求匹配成功订单列表
    public function pipei()
    {
        $pageLimit = $this->getParam('pageLimit', 10, 'int');
        $page = $this->getParam('page', 1, 'int');
        $user = Session::get('user');
        $user = isset($user[0]) ? $user[0] : $user;
        $address = Db::name('user_address')->where(array('user_id' => $user['id'], 'isdel' => 0))->order('is_default', 'desc')->select();
        $where = array('changer_id' => $user['id']);
        $pager = Db::name('order')
            ->where($where)
            ->paginate($pageLimit, false, array('page' => $page))
            ->toArray();

        $data = [];
        foreach ($pager['data'] as $key => $item) {
            $goods = Db::name('goods')->where('id', $item['goods_id'])->find();
            $request = Db::name('request')->where('id', $item['request_id'])->find();
            $data[$key]['id'] = $item['id'];
            $data[$key]['status'] = $this->orderStatus[$item['status']];
            $changer = Db::name('users')->where('id', $item['user_id'])->find();
            $data[$key]['goodsUser'] = empty($changer['nick_name']) ? $changer['email'] : $changer['nick_name'];
            $data[$key]['goodsName'] = $goods['name'];
            $data[$key]['changeGoodsName'] = $request['change_goods_name'];
            $data[$key]['user_addressee'] = $item['user_addressee'];
            $data[$key]['user_address'] = $item['user_address'];
            $data[$key]['user_mobile'] = $item['user_mobile'];
        }
        $pager['data'] = $data;
        $this->assign('address', $address);
        $this->assign('pager', $pager);
        $this->assign('pageLimit', $pageLimit);
        $this->assign('page', $page);
        return $this->fetch();
    }


    //订单详情
    public function detail()
    {
        $user = Session::get('user');
        $user = isset($user[0]) ? $user[0] : $user;
        $id = $this->getParam('id', 0, 'int');
        $order = Db::name('order')->where('id=' . $id . ' and (changer_id=' . $user['id'] . ' or user_id=' . $user['id'] . ')')->find();
        if (!$order) {
            throw new Exception('没有找到记录');
        }
        //获取请求
        $request = Db::name('request')->where('id', $order['request_id'])->find();
        //判断是发起人还是交换人
        if ($user['id'] == $order['user_id']) {
            $goods = Db::name('goods')->where('id', $order['goods_id'])->find();
            $myGoodsName = $goods['name'];
            if ($request['change_goods_id'] > 0) {
                $changeGoods = Db::name('goods')->where('id', $request['change_goods_id'])->find();
                $changeGoodsName = $changeGoods['name'];
            } else {
                $changeGoodsName = $request['change_goods_name'];
            }
            $changer = Db::name('users')->where('id', $order['changer_id'])->find();
        } else {
            $goods = Db::name('goods')->where('id', $order['goods_id'])->find();
            $changeGoodsName = $goods['name'];
            if ($request['change_goods_id'] > 0) {
                $changeGoods = Db::name('goods')->where('id', $request['change_goods_id'])->find();
                $myGoodsName = $changeGoods['name'];
            } else {
                $myGoodsName = $request['change_goods_name'];
            }
            $changer = Db::name('users')->where('id', $order['user_id'])->find();
        }
        //我的举报
        $order['my_report'] = '';
        $order['my_report_status'] = '';
        //被举报
        $order['reported'] = '';
        $order['reported_status'] = '';
        //获取举报信息
        if ($order['is_report'] > 0) {
            $reports = Db::name('report')->where('id', $order['is_report'])->select();
            foreach ($reports as $report) {
                if ($user['id'] == $report['user_id']) {
                    $order['my_report'] = $report['reason'];
                    $order['my_report_status'] = $report['is_deal'] == 1 ? '已解决' : '未解决';
                } else {
                    $order['reported'] = $report['reason'];
                    $order['reported_status'] = $report['is_deal'] == 1 ? '已解决' : '未解决';
                }
            }
        }
        $this->assign('myGoods', $myGoodsName);
        $this->assign('changeGoods', $changeGoodsName);
        $this->assign('request', $request);
        $this->assign('changer', $changer);
        $this->assign('order', $order);
        $this->assign('orderStatus', $this->orderStatus);
        return $this->fetch();
    }

    //发布商品列表
    public function goods()
    {
        $user = Session::get('user');
        $user = isset($user[0]) ? $user[0] : $user;
        $name = $this->getParam('name');
        $status = $this->getParam('status', 100, 'int');
        $pageLimit = $this->getParam('pageLimit', 10, 'int');
        $page = $this->getParam('page', 1, 'int');
        $where = array('isdel' => 0, 'user_id' => $user['id']);
        if ($name) {
            $where['name'] = array('like', $name . '%');
        }
        if ($status != $status) {
            $where['status'] = $status;
        }
        $pager = Db::name('goods')
            ->where($where)
            ->paginate($pageLimit, false, array('page' => $page))
            ->toArray();
        $data = [];
        foreach ($pager['data'] as $key => $item) {
            $data[$key] = $item;
            $data[$key]['requestNum'] = Db::name('request')->where('goods_id', $item['id'])->count();
        }
        $pager['data'] = $data;
        $this->assign('pager', $pager);
        $this->assign('pageLimit', $pageLimit);
        $this->assign('page', $page);
        return $this->fetch();
    }

    //发布商品置换对应请求列表
    public function goodsdetail()
    {
        $user = Session::get('user');
        $user = isset($user[0]) ? $user[0] : $user;
        $address = Db::name('user_address')->where(array('user_id' => $user['id'], 'isdel' => 0))->order('is_default', 'desc')->select();
        $this->assign('address', $address);
        $goodsId = $this->getParam('id', '', 'int');
        if (!$goods = Db::name('goods')->where(array('user_id' => $user['id'], 'id' => $goodsId))->find()) {
            throw new Exception('置换商品不存在');
        }
        $this->assign('goods', $goods);
        $request = Db::name('request')->where('goods_id', $goodsId)->select();
        foreach ($request as $key => $value) {
            $user = Db::name('users')->where('id', $value['user_id'])->find();
            $request[$key]['userName'] = empty($user['nick_name']) ? $user['email'] : $user['nick_name'];
        }
        $this->assign('request', $request);
        return $this->fetch();
    }


    //匹配成功
    public function add()
    {
        $user = Session::get('user');
        $user = isset($user[0]) ? $user[0] : $user;
        $goodsId = $this->getParam('goodsId');
        $requestId = $this->getParam('requestId');
        $userAddress = $this->getParam('userAddress');
        $userAddressee = $this->getParam('userAddressee');
        $addressMobile = $this->getParam('addressMobile');
        if (empty($userAddress) || empty($userAddressee) || empty($addressMobile))
            return $this->returnJson('收货地址、收件人、收件人手机号不能空');
        if (!$goods = Db::name('goods')->where(array('id' => $goodsId, 'user_id' => $user['id']))->find()) {
            return $this->returnJson('您没有权限操作该商品');
        }
        if (!$request = Db::name('request')->where(array('id' => $requestId, 'goods_id' => $goodsId))->find())
            return $this->returnJson('请求不存在');
        $data = array(
            'request_id'     => $requestId,
            'goods_id'       => $goodsId,
            'user_id'        => $user['id'],
            'changer_id'     => $request['user_id'],
            'user_address'   => $userAddress,
            'user_addressee' => $userAddressee,
            'user_mobile'    => $addressMobile
        );
        //开启数据库事务，完成多表操作，全部成功则提交，否则回滚
        Db::startTrans();
        $res = Db::name('order')->insert($data);
        if (!$res) {
            Db::rollback();
            return $this->returnJson('失败');
        }
        $res = Db::name('goods')->where('id', $goodsId)->update(array('status' => 2));
        if (!$res) {
            Db::rollback();
            return $this->returnJson('失败');
        }
        $res = Db::name('request')->where('id', $requestId)->update(array('status' => 1));
        if (!$res) {
            Db::rollback();
            return $this->returnJson('失败');
        }
        Db::name('request')->where(array('goods_id' => $goodsId))->update(array('status' => 1));
        Db::name('goods')->where('id', $request['change_goods_id'])->update(array('status' => 2));
        Db::commit();
        return $this->returnJson('成功', 1001, true);
    }

    //拒绝请求
    public function refund()
    {
        $user = Session::get('user');
        $user = isset($user[0]) ? $user[0] : $user;
        $id = $this->getParam('id');
        $request = Db::name('request')->where(['id' => $id, 'status' => 0, 'goods_user_id' => $user['id']])->find();
        if (!$request) {
            return $this->returnJson('您没有权力拒绝该请求');
        }
        $request['status'] = 2;
        Db::name('request')->update($request);
        return $this->returnJson('成功', 1001, true);
    }

    //匹配成功，请求匹配人填写地址
    public function over()
    {
        $user = Session::get('user');
        $user = isset($user[0]) ? $user[0] : $user;
        $orderId = $this->getParam('orderId');
        if (!$order = Db::name('order')->where(array('id' => $orderId, 'changer_id' => $user['id'], 'status' => 0))->find())
            return $this->returnJson('没有订单');
        $userAddress = $this->getParam('userAddress');
        $userAddressee = $this->getParam('userAddressee');
        $addressMobile = $this->getParam('addressMobile');
        if (empty($userAddress) || empty($userAddressee) || empty($addressMobile))
            return $this->returnJson('收货地址、收件人、收件人手机号不能空');
        $order['changer_address'] = $userAddress;
        $order['changer_addressee'] = $userAddressee;
        $order['changer_mobile'] = $addressMobile;
        $order['status'] = 1;
        Db::startTrans();
        $res = Db::name('order')->update($order);
        if (!$res) {
            Db::rollback();
            return $this->returnJson('失败');
        }
        $user = Db::name('user')->where('id', $order['user_id'])->find();
        $changer = Db::name('user')->where('id', $order['changer_id'])->find();
        $user_credit = 100 - $user['credit'] > 0 ? 100 - $user['credit'] : 0;
        $changer_credit = 100 - $changer['credit'] > 0 ? 100 - $user['credit'] : 0;
        $user_credit = $user_credit > 10 ? 10 : $user_credit;
        $changer_credit = $changer_credit > 10 ? 10 : $changer_credit;
        $res = Db::name('user')->where('id', $order['user_id'])->setInc('credit', $user_credit);
        if (!$res) {
            Db::rollback();
            return $this->returnJson('失败');
        }
        $res = Db::name('user')->where('id', $order['changer_id'])->setInc('credit', $changer_credit);
        if (!$res) {
            Db::rollback();
            return $this->returnJson('失败');
        }
        Db::commit();
        return $this->returnJson('成功', 1001, true);
    }

    //取消置换
    public function cancel()
    {
        $user = Session::get('user');
        $user = isset($user[0]) ? $user[0] : $user;
        $orderId = $this->getParam('orderId');
        $where = array(
            'id'                 => $orderId,
            'status'             => 0,
            'user_id|changer_id' => $user['id']
        );
        if (!$order = Db::name('order')->where($where)->find())
            return $this->returnJson('数据错误或无法取消');
        $order['status'] = 2;
        $res = Db::name('order')->update($order);
        if (!$res)
            return $this->returnJson('失败');
        return $this->returnJson('成功', 1001, true);
    }

    //订单举报
    public function report()
    {
        $user = Session::get('user');
        $user = isset($user[0]) ? $user[0] : $user;
        $order_id = $this->getParam('order_id', 0, 'int');
        $order = Db::name('order', ['id' => $order_id, 'status' => 1])->find();
        if (empty($order)) {
            return $this->returnJson('米有该订单');
        }
        if ($user['id'] == $order['user_id']) {
            $report_user_id = $order['changer_id'];
        } elseif ($user['id'] == $order['changer_id']) {
            $report_user_id = $order['user_id'];
        } else {
            return $this->returnJson('您没有权限');
        }
        $reason = $this->getParam('reason');
        $type = $this->getParam('type', 0, 'int');
        if ($type > 2 or $type < 0) {
            return $this->returnJson('举报类型错误');
        }
        if (empty($reason)) {
            return $this->returnJson('举报类型不能为空');
        }
        $data = [
            'reason'         => $reason,
            'order_id'       => $order_id,
            'type'           => $type,
            'report_user_id' => $report_user_id,
            'user_id'        => $user['id'],
        ];
        Db::startTrans();
        $res = Db::name('report')->insert($data);
        if (!$res) {
            Db::rollback();
            return $this->returnJson('失败');
        }
        $report_id = Db::name('report')->getLastInsID();
        $order['is_report'] = $report_id;
        $res = Db::name('order')->update($order);
        if (!$res) {
            Db::rollback();
            return $this->returnJson('失败');
        }
        Db::commit();
        return $this->returnJson('举报成功', 1001, true);
    }

}