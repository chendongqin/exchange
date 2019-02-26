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
         $priceType = $this->getParam('priceType',0,'int');
         $this->assign('priceType',$priceType);
         $where = array('status'=>1,'priceType'=>$priceType);
         $roomType = $this->getParam('roomType',0,'int');
         if($roomType){
             $where['roomType'] = $roomType;
         }
         $this->assign('roomType',$roomType);
         $price = $this->getParam('price',0,'string');
         if($price){
             $where['price'] = $price;
         }
         $this->assign('$price',$price);
         $address = $this->getParam('address','','string');
         if ($address){
             $hourses = Db::name('house')->where('address', 'like', $address)->field('id')->select();
             if(empty($hourses)){
                 $where[] = '1=2';
             }else{
                 $where[] = 'houseId in('.implode(',',$hourses).')';
             }
         }
         $this->assign('address',$address);
         $page = $this->getParam('page',1,'int');
         $pageLimit = $this->getParam('pageLimit',8,'int');
         $pager  = Db::name('rooms')->where($where)->page($page,$pageLimit);
         $this->assign('pager',$pager);
         return $this->fetch();
     }


 }