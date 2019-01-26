<?php
/**
 * Created by PhpStorm.
 * User: 39720
 * Date: 2018/10/30
 * Time: 23:03
 */

namespace app\back\model;


class ChatGroupUserRelation extends \think\Model{

    public function groupRelation(){

    }

    public function aboutUser(){
        return $this->hasOne('admin','uid','id');
    }
}