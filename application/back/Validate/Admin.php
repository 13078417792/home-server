<?php
/**
 * Created by PhpStorm.
 * User: 39720
 * Date: 2018/9/10
 * Time: 15:31
 */

namespace app\back\Validate;


class Admin extends Base{

    protected $rule = [
        'username'=>'require',
        'pwd'=>'require'
    ];

    protected $messages = [
        'login'=>[
            'username.require'=>'请输入用户名',
            'pwd.require'=>'请输入密码',
        ]
    ];

    protected $field = [
        'username'=>'用户名',
        'pwd'=>'密码'
    ];

    protected $scene = [
        'login'=>['username','pwd'=>'require|authUser']
    ];

    protected function authUser($value,$rule,$data){
        $detail = db('admin')->where(['username'=>$data['username']])->find();
        if(empty($detail)){
            return '用户不存在';
        }
        if(!password_verify($value,$detail['pwd'])){
            return '密码错误';
        }
        if($detail['status']==0){
            return '此用户不可用';
        }elseif($detail['status']==2){
            return '用户不存在';
        }elseif($detail['status']==1){
            return true;
        }else{
            return '登录失败！未知用户';
        }
    }

}