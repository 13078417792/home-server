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
            'getAccessList',
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
    'isValidate'=>false,
    'SUPER_USERNAME'=>'admin',
    'SUPER_UID'=>1,
    'SUPER_ROLE_ID'=>1, // 超管角色ID
    'MANAGER_ROLE_ID'=>2, // 普通管理员角色ID
    'SUPER_ROLE'=>'超级管理员',

    // 白名单，不检查权限的路由
    'WHITELIST_PATH'=>$WHITELIST_PATH

];