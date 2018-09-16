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

    public function token_add(){

        $token = token2('addAdmin');
        Cache::tag('addAdmin')->set($token,$token);
        return json2(true,'',['token'=>token2('addAdmin')]);
    }

    public function login(){
        $data = [
            'username'=>request()->post('username/s',null,'strval'),
            'pwd'=>request()->post('password/s',null,'strval'),
            'update_time'=>$_SERVER['REQUEST_TIME']
        ];
        $model = model('Admin');
        return $model->login($data);
    }

    public function addAdmin(){
        $data = [
            'username'=>request()->post('username/s',null,'strval'),
            'pwd'=>request()->post('password/s',null,'strval'),
            'add_time'=>$_SERVER['REQUEST_TIME'],
            'nickname'=>request()->post('nickname/s',null,'strval'),
            'token'=>request()->post('token/s',null,'strval'),
        ];

        return json2(true,'成功',['data'=>$data]);
    }

    public function test(){
//        echo password_hash('123456',PASSWORD_DEFAULT);
//        dd(\app\back\model\Admin::get(['username'=>'admin'])->toArray());
//        dd(\app\back\model\Admin::all(['username'=>'admin']));
//        dd(\app\back\model\Admin::get(['username'=>'admin']) instanceof \think\Model);
        dd(toArray(\app\back\model\Admin::get(function($query){
            $query->where(['username'=>'admin'])->field('pwd',true);
        })));
    }

    protected function checkAccess($auth_token){

    }
}