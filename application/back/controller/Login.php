<?php
/**
 * Created by PhpStorm.
 * User: 39720
 * Date: 2018/9/10
 * Time: 14:46
 */

namespace app\back\controller;

use \think\Cache;
class Login extends Base{

    protected $mustPost = false;

    public function login(){
        $data = [
            'username'=>request()->post('username/s',null,'strval'),
            'pwd'=>request()->post('password/s',null,'strval'),
            'update_time'=>$_SERVER['REQUEST_TIME']
        ];
//        return json2(false,'测试');
        $model = model('Admin');
        return $model->login($data);
    }
}