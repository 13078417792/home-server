<?php
/**
 * Created by PhpStorm.
 * User: 39720
 * Date: 2018/9/19
 * Time: 16:48
 */

namespace app\back\validate;

use \app\back\model\Role as RoleModel;
class Role extends Base{

    protected $rule = [
        'id'=>'require|id_exist',
        'name'=>'require|length:2,20|unique:role',
        'access'=>'require|array|access_exist'
    ];

    protected $message = [
        'name.require'=>'必须输入角色名',
        'name.length'=>'角色名长度2-20个字符',
        'name.unique'=>'角色已存在',
        'access.require'=>'必须选择角色权限',
        'access.array'=>'角色权限数据错误',
    ];

    protected $scene = [
        'add'=>['name','access'],
        'update'=>['id','name'=>'require|length:2,20|update_unique','access']
    ];

    protected $field = [
        'id'=>'角色ID',
        'name'=>'角色名称',
        'access'=>'角色权限'
    ];


    protected function access_exist($value,$rule,$data){
        $access = [];
        $access = RoleModel::getArrayAccess($value);
        $count = db('access')->where(['id'=>['in',$access]])->field('id')->count();
        return count($access)===$count?true:'所选权限不存在';
    }
}