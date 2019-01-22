<?php

namespace app\tool\controller;

use think\Controller;
use think\Request;
use think\Cache;

class Token extends Controller{

    const NORMAL_TOKEN_NAME_REDIS = 'TOOL_NORMAL_TOKEN_';

    static function createToken(){
        $token = substr(md5(uniqid().'_'.$_SERVER['REQUEST_TIME']),8,16);
        return $token;
    }

    // 有效期3天
    const NORMAL_TOKEN_EXPIRES = 259200;

    /**
     * 生成普通token
     * @return bool|string
     */
    public function InitToken(){
        $token = self::createToken();
        Cache::set(self::NORMAL_TOKEN_NAME_REDIS.$token,1,self::NORMAL_TOKEN_EXPIRES);
        return json2(true,'',['token'=>$token,'expires'=>self::NORMAL_TOKEN_EXPIRES]);
    }

    /**
     * 验证普通token
     * @param string $token
     * @return bool
     */
    static public function CheckToken(string $token){
        $result = Cache::get(self::NORMAL_TOKEN_NAME_REDIS.$token);
        return (bool)$result;
    }

    public function ValidateToken(){
        $token = request()->get('TokenID/s','');
        if(!$token || !self::CheckToken($token)) return json2(false,'',['code'=>Base::TOKEN_VAILD_CODE]);
        return json2(true,'');
    }
}
