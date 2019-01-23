<?php

namespace app\tool\model;

use think\Model;
use app\tool\model\Account as AccountModel;

class Base extends Model{

    protected $user_id = 0;

    protected function initialize(){
        parent::initialize();
        $this->user_id = $GLOBALS['uid'];
    }

    static public function getAccount(){
        return AccountModel::find($GLOBALS['uid']);
    }

}
