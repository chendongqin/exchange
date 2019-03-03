<?php
/**
 * Created by PhpStorm.
 * User: Viter
 * Date: 2018/3/4
 * Time: 15:41
 */
namespace app\user\controller;
use base\Userbase;
use think\Config;
use think\Session;
use base\Base;
use think\Db;

 class Index extends Userbase {
    public function index(){
      return $this->fetch();
    }
 }