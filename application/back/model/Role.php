<?php
/**
 * Created by PhpStorm.
 * User: 39720
 * Date: 2018/9/9
 * Time: 22:31
 */

namespace app\back\model;


class Role extends \think\Model
{
    public function admin(){
        return $this->hasMany('AdminRoleRelation','role_id');
    }

    public function role_list(){

    }
}