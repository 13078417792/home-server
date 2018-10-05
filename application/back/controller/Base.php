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
    protected $auth = '';
    protected $dbName = '';

    protected $uid = null;
    protected $isSuper = false;
    protected $isManager = false;

    protected function _initialize(){

        if(!config('app_debug') && $this->mustPost && !request()->isPost()){
            self::printJson(false,'请求类型错误',['mustPost'=>$this->mustPost]);
        }


        $auth = request()->header('authorization');
        $this->auth = $auth?:'';
        $auth_detail = $auth?Cache::tag('auth')->get($auth):[];
        $path = '/'.strtolower(request()->module()).'/'.strtolower(request()->controller()).'/'.strtolower(request()->action());

        if(config('rbac.isValidate') && !Rbac::validateAccess(empty($auth_detail)?0:$auth_detail['id'],$path)){
            self::printJson(false,'没有权限');
        }
        // empty($auth_detail) && self::printJson(false,'用户认证失败');
        $this->user_auth_info = $auth_detail;

        if(!empty($auth_detail)){
            $this->uid = $auth_detail['id'];
            $this->isSuper = Rbac::isSuper($auth_detail['id']);
            $this->isManager = Rbac::isManager($auth_detail['id']);
        }


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

//        dd(Cache::tag('video')->get('auth'));
//        dd(Cache::tag('auth')->get('auth'));
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

    protected function status($id,$status){
        if(!$this->dbName){
            return json2(false,'操作失败');
        }
        $db = $this->dbName;
        $model = model($db);
        $result = toArray($model->where(['id'=>$id])->field('pwd,add_time,update_time',true)->find());
        if(empty($result)){
            return json2(false,'操作失败',['error'=>'数据不存在']);
        }
        if(!array_key_exists('status',$result)){
            return json2(false,'操作失败',['error'=>'该数据不可更新状态']);
        }
        if($status!=$result['status']){
            $result = $model->save(['status'=>$status],['id'=>$id]);
            return $result>0?json2(true,'更新状态成功',['status'=>$status]):json2(false,'操作失败',['error'=>'操作失败']);
        }else{
            return json2(true,'更新状态成功',['status'=>$status]);
        }
    }

    protected function del($id,$key='id'){
        if(!$this->dbName){
            return json2(false,'操作失败');
        }
        $db = $this->dbName;
        $model = ('\app\back\model\\'.ucfirst($db))::get([$key=>$id]);
        if(empty($model)){
            return json2(false,'删除失败',['error'=>'数据不存在']);
        }else{
            $model->delete();
            return json2(true,'删除成功');
        }
    }

}