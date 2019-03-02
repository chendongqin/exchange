<?php
/**
 * Created by PhpStorm.
 * User: Viter
 * Date: 2018/3/4
 * Time: 14:48
 */
namespace app\index\controller;
use base\Base;
use ku\Verify;
use ku\Tool;
use think\Session;
use think\Cache;
class Captcha extends Base{

    public function index(){
        $request = $this->request;
        $channel = $request->param('channel','','string');
        Tool::captcha(120,34,$channel);
    }
    

}