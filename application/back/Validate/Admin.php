<?php
/**
 * Created by PhpStorm.
 * User: 39720
 * Date: 2018/9/10
 * Time: 15:31
 */

namespace app\back\validate;
use \app\back\model\Admin as AdminModel;

class Admin extends Base{

    protected $model = null;

    protected $rule = [
        'id'=>'require|user_exist',
        'username'=>'require|length:2,15|unique:admin',
        'nickname'=>'length:2,25|undate_unique',
        'pwd'=>'require|length:5,25',
        'role'=>'require|role_exist'
    ];

    protected $message = [
        'id.require'=>'用户不存在',
        'username.require'=>'请输入用户名',
        'username.length'=>'用户名长度2-15位',
        'nickname.length'=>'昵称长度2-25位',
        'pwd.length'=>'密码长度5-25位',
        'pwd.require'=>'请输入密码',
        'role.require'=>'请选择角色',
        'username.unique'=>'用户名已被使用'
    ];

    protected $messages = [
        'login'=>[
            'username.require'=>'请输入用户名',
            'pwd.require'=>'请输入密码',
        ]
    ];

    protected $field = [
        'username'=>'用户名',
        'pwd'=>'密码',
        'nickname'=>'昵称',
        'role'=>'角色'
    ];

    protected $scene = [
        'login'=>['username'=>'require','pwd'=>'require|authUser'],
        'add'=>['username','pwd'=>'length:5,25','role'],
        'update'=>['id','username'=>'require|length:2,15|undate_unique','nickname','pwd'=>'length','role']
    ];

    protected function getModel($id){
        if(empty($this->model)){
            $this->model = AdminModel::get($id);
        }
        return $this->model;
    }

    protected function authUser($value,$rule,$data){
//        $detail = db('admin')->where(['username'=>$data['username']])->find();
        $detail = toArray(AdminModel::get(['username'=>$data['username']]));
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

    protected function role_exist($value,$rule,$data){
        $role_list = db('Role')->where(['status'=>1])->column('id');
        foreach($value as $v){
            if(!in_array($v,$role_list)){
                return '角色不存在';
            }
        }
        return true;
    }

    protected function user_exist($value,$rule,$data){
        $model = $this->getModel($data['id']);
        return empty($model)?'用户不存在':true;
    }

    protected function undate_unique($value,$rule,$data,$field,$desc){
        $unique = AdminModel::get(['id'=>['neq',$data['id']],$field=>$data[$field]]);
        return empty($unique)?true:"{$desc}已被使用";
    }

}