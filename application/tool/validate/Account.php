<?php
/**
 * Created by PhpStorm.
 * User: 39720
 * Date: 2019/1/11
 * Time: 15:54
 */

namespace app\tool\validate;
use \app\tool\model\Account as AccountModel;
//use \app\back\validate\Base;

class Account extends Base{

    protected $rule = [
        'old'=>'require|checkPassword',
        'username'=>'require|length:8,30',
        'pwd'=>'require|length:6,30',
        'rpwd'=>'require|confirm:pwd'
    ];


    protected $messages = [

        'create'=>[
            'username.require'=>'请输入用户名',
            'username.length'=>'用户名长度为8-30个字符',
            'username.unique'=>'用户名已存在',
            'username.regex'=>'允许的字符：大小写英文/数字/下划线',

            'pwd.require'=>'请输入密码',
            'pwd.length'=>'密码长度为6-30个字符',
            'pwd.regex'=>'允许的字符：大小写英文/数字,特殊字符：*.-%&@#$&+_,',

            'rpwd.require'=>'请再次输入密码',
            'rpwd.confirm'=>'两次输入的密码不一致'
        ],

        'signin'=>[
            'username.require'=>'请输入用户名',
            'username.length'=>'用户名或密码错误',
            'username.zip'=>'用户名或密码错误',

            'pwd.require'=>'请输入密码',
            'pwd.length'=>'用户名或密码错误',
            'pwd.zip'=>'用户名或密码错误',
        ],

        'modify'=>[
            'old.require'=>'必须输入旧密码',
            'pwd.require'=>'请输入新密码',
            'pwd.length'=>'新密码长度为6-30个字符',
            'pwd.regex'=>'允许的字符：大小写英文/数字,特殊字符：*.-%&@#$&+_,',

            'rpwd.require'=>'请再次输入新密码',
            'rpwd.confirm'=>'两次输入的密码不一致'
        ]

    ];

    protected $scene = [
        'create'=>[
            'username'=>'require|length:8,30|unique:account|regex:^[a-zA-Z0-9_]+$',
            'pwd'=>'require|length:6,30|regex:^[a-zA-Z0-9\*\.\-%&@#$&+,_]+$',
            'rpwd'=>'require|confirm:pwd'
        ],
        'signin'=>['username','pwd'],
        'modify'=>[
            'old',
            'pwd'=>'require|length:6,30|regex:^[a-zA-Z0-9\*\.\-%&@#$&+,_]+$',
            'rpwd'
        ]
    ];

    protected function checkPassword($value,$rule,$data){
//        dd($data);
//        exit;
        if($value===$data['pwd']) return '输入的旧密码和新密码相同';
        $account = AccountModel::getAccount();
        if(!password_verify($value,$account->pwd)) return '旧密码错误';

//        if(empty($data['pwd'])) return true;


        return password_verify($data['pwd'],$account->pwd)?'新旧密码一致':true;

    }
}