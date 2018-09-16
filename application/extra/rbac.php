<?php
/**
 * Created by PhpStorm.
 * User: 39720
 * Date: 2018/9/10
 * Time: 10:57
 */
$WHITELIST_PATH = [
    'back'=>[
        'Login',
        'Auth'=>[
            'checkAuth',
            'getAuthUserInfo',
            'getUserList',
        ]
    ],
];


function lower_path($path){
    $data = [];
    foreach($path as $key => $val){
        if(gettype($val)==='array'){
            $data[strtolower($key)] = lower_path($val);
        }else{
            $data[] = strtolower($val);
        }
    }
    return $data;
}
$WHITELIST_PATH = lower_path($WHITELIST_PATH);
return [
    'SUPER_USERNAME'=>'admin',
    'SUPER_ROLE'=>'超级管理员',

    // 白名单，不检查权限的路由
    'WHITELIST_PATH'=>$WHITELIST_PATH

];