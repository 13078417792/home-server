<?php

namespace app\tool\model;

use think\Model;

class Base extends Model{

    protected $user_id = 0;

    protected function initialize(){
        parent::initialize();
        $this->user_id = $GLOBALS['uid'];
    }
}
