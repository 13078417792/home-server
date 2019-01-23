<?php

namespace app\tool\model;

use think\Model;
use app\tool\model\Account as AccountModel;

class Base extends Model{

    protected $user_id = 0;
    static $modelInstance = [];

    protected function initialize(){
        parent::initialize();
        $this->user_id = $GLOBALS['uid'];
    }

    static public function getAccount(int $id=0){
        if($id<=0) $id = $GLOBALS['uid'];
        if(!$id) return null;
        if(empty(self::$modelInstance['Account']) || empty(self::$modelInstance['Account'][$id])){
            self::$modelInstance['Account'][$id] = AccountModel::find($id);
        }
        return self::$modelInstance['Account'][$id];
    }

}
