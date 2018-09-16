<?php
/**
 * Created by PhpStorm.
 * User: 39720
 * Date: 2018/9/9
 * Time: 22:08
 */

namespace app\back\controller;

use \Rbac\Rbac;
use \think\Cache;
class Base extends \think\Controller{

    protected $mustPost = true;

    protected function _initialize(){


        if(!config('app_debug') && $this->mustPost && !request()->isPost()){
            self::printJson(false,'请求类型错误',['mustPost'=>$this->mustPost]);
        }
        $auth = config('app_debug')?request()->param('auth_token/s',null,'strval'):request()->post('auth_token/s',null,'strval');
        $auth_detail = $auth?Cache::tag('auth')->get($auth):[];
        $path = '/'.strtolower(request()->module()).'/'.strtolower(request()->controller()).'/'.strtolower(request()->action());

        //self::printJson(false,'没有权限',['detail'=>$auth_detail,'auth'=>$auth,'params'=>$params]);
        if(!Rbac::validateAccess(empty($auth_detail)?0:$auth_detail['id'],$path)){
            self::printJson(false,'没有权限');
        }

//        $this->post_data = request()->post();
//        $this->get_data = request()->get();

    }

    static public function isPost(){
        return request()->isPost();
    }

    static public function isAjax(){
        return request()->isAjax();
    }

    static public function isPostAjax(){
        return request()->isPost() || request()->isAjax();
    }

    static public function isGet(){
        return request()->isGet();
    }

    static public function printJson($success,$msg='',$extend=[]){
        response()->content(json_encode(self::json_format($success,$msg,$extend)))->contentType('application/json')->send();
        exit;
    }

    static public function json_format($success,$msg='',$extend=[]){
        $json_arr = array_merge(['msg'=>$msg,'code'=>200],$extend);
        $json_arr['success'] = $success?true:false;
        return $json_arr;
    }

    static public function json($success,$msg='',$extend=[]){
        return json(self::json_format($success,$msg,$extend));
    }

    // 解析认证token
    static public function unserialize_auth_token($token){
        if(!$token){
            return false;
        }
        $cache = Cache::tag('auth')->get($token);
        if(empty($cache)){
            return [];
        }
        $keys = ['id','username','nickname'];
        foreach($keys as $k => $v){
            if(!array_key_exists($v,$cache)){
                return [];
            }
        }
        $detail = model('Admin')->adminDetail($cache['id']);
        if(empty($detail)){
            return [];
        }else{
            return array_merge($cache,$detail);
        }
    }


}