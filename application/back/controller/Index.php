<?php
namespace app\back\controller;
use \Rbac\Rbac;
class Index extends \think\Controller{

    public function index(){
        $r = new Rbac;
        dd($r->getAdminDetail(1));
    }

    public function apitest(){
        if(!request()->isPost()){
            return json2(false,'请求方式错误');
        }
        return json2(true,'成功了',['time'=>$_SERVER['REQUEST_TIME']]);
    }
}
