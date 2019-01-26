<?php
/**
 * Created by PhpStorm.
 * User: 39720
 * Date: 2018/10/30
 * Time: 19:31
 */

namespace app\back\validate;
use \app\back\model\Admin as AdminModel;

class ChatGroup extends Base{

    protected $rule = [
        'name'=>'require|length:2,30',
        'member'=>'require|hasMember',
        'thumb'=>'require|url'
    ];

    protected $message = [
        'name.require'=>'必须输入群组名称',
        'name.length'=>'群组长度为2-30个字符',
        'member'=>'没有群组成员',
        'thumb.require'=>'必须上传群组头像',
        'thumb.activeUrl'=>'群组头像不合法',
        'thumb.url'=>'群组头像不合法'
    ];

    protected $scene = [
        'add'=>['name','member','thumb']
    ];

    protected function hasMember($value,$rule,$data){
        $result = AdminModel::where(['id'=>['in',$value],'status'=>1])->column('id');
        if(count($result)===count($value)){
            return true;
        }else{
            return '成员不存在';
        }
    }


}