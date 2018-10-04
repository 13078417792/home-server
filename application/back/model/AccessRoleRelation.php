<?php
/**
 * Created by PhpStorm.
 * User: 39720
 * Date: 2018/9/9
 * Time: 22:33
 */

namespace app\back\model;


class AccessRoleRelation extends \think\Model{

    public function access(){
        return $this->hasOne('Access','access_id','id');
    }
}