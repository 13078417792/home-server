<?php
/**
 * Created by PhpStorm.
 * User: 39720
 * Date: 2018/9/11
 * Time: 16:05
 */

namespace app\back\controller;

use \think\Cache;
use \Rbac\Rbac;
class Auth extends Base{

    public function checkAuth(){
        $token = request()->post('auth_token/s',null,'strval');
        $invalid = json2(false,'用户认证已过期，请重新登录');
        if(!$token){
            return $invalid;
        }
        $cache = Cache::tag('auth')->get($token);
        return empty($cache)?$invalid:json2(true,'认证通过');
    }

    // 目前用户的基本信息
    public function getAuthUserInfo(){
        $token = request()->post('auth_token/s',null,'strval');
        $invalid = json2(false,'token失效');
        if(!$token){
            return $invalid;
        }
        $detail = self::unserialize_auth_token($token);
        if($detail===false || empty($detail)){
            return $invalid;
        }
        return json2(true,'',['info'=>$detail]);
    }

    // 获取指定用户的详细信息（包括权限信息）
    public function getAuthDetail(){
        $id = request()->post('id/d',0,'intval');
        $not_exist = json2(false,'用户不存在',['detail'=>[]]);
        if(!$id){
            return $not_exist;
        }
        $detail = Rbac::getAdminDetail($id);
        return empty($detail)?$not_exist:json2(true,'查询成功',['detail'=>$detail]);
    }

    // 用户列表
    public function getUserList(){
        $page = request()->get('page/d',1,'intval');
        $size = request()->get('size/d',10,'intval');
        $condition = [];
        $username = request()->post('username/s',null,'strval');
        $nickname = request()->post('nickname/s',null,'strval');
        $status = request()->post('status/d','');
        if($username){
            $condition['username'] = $username;
        }
        if($nickname){
            $condition['nickname'] = $nickname;
        }
        if($status!==''){
            $condition['status'] = $status;
        }
        $result = model('Admin')->adminList($page,$size,$condition);
        $list = $result['data'];
        unset($result['data']);
        return json2(true,'查询成功',['list'=>$list,'page_var'=>$result,'status'=>$status,'post'=>request()->post(),'server'=>$_POST]);
    }
}