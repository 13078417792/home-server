<?php

namespace app\tool\model;

use think\Model;

class Account extends Base{

    protected $pk = 'uid';

    protected function initialize(){
        parent::initialize(); // TODO: Change the autogenerated stub
    }

    /**
     * 创建账号
     * @param array $data
     * @return array|bool
     */
    public function createAccount(array $data){
//        $validate = validate('Account');
        $validate = new \app\tool\validate\Account;

        if($validate->scene('create')->check($data)){
            $data['pwd'] = password_hash($data['pwd'],PASSWORD_DEFAULT);
            $this->insertAccountData($data);
            return true;
        }else{
            return $validate->getError();
        }
    }

    public function insertAccountData(array $data,$pk=false){
        $model = new self($data);
        $rows = $model->allowField(true)->save();
        return $pk?$model->id:$rows;
    }

    public function checkAccount(array $data){
        $validate = validate('Account');
        if($validate->scene('signin')->check($data)){
            $result = self::get(['username'=>$data['username']]);
//            return password_verify($data['pwd'],$result->pwd)?'用户名或密码错误':true;
//            return true;
            return password_verify($data['pwd'],$result->pwd)?true:'用户名或密码错误';
        }else{
            return $validate->getError();
        }
    }

    public function folder(){
        return $this->hasMany('NetDiskFolder','account_id');
    }

    public function getCurrentUserDiskFolder(bool $tree=true,bool $obj=false){
        $model = $this->folder()->where(['account_id'=>$this->user_id]);
        $field = 'id,account_id,name,create_time,pid,update_time,parent_key';
        if($obj){
            $folders = $model->field($field)->select();
        }else{
            $folders = $model->column($field,'id');
        }
        if($tree){
            if($obj) $folders = toArray($folders);
//            $folders = NetDiskFolder::handleTree($folders);
            $folders = NetDiskFolder::handleTreeRef($folders);
        }
        return $folders;
    }
}
