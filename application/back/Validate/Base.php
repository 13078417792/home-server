<?php
/**
 * Created by PhpStorm.
 * User: 39720
 * Date: 2018/9/10
 * Time: 15:31
 */

namespace app\back\validate;


class Base extends \think\Validate{

    protected $messages = [];
    protected $dbName = '';
    protected $pk = 'id';
    protected $dbInstance = [];

    public function __construct(array $rules = [], $message = [], $field = []){
        parent::__construct($rules,$message,$field);
        if(!$this->dbName){
            $this->dbName = strtolower(basename(get_class($this)));
            $this->dbName = preg_replace('/.+\\\\/i','',$this->dbName);
        }
    }

    public function check($data, $rules = [], $scene = '')
    {
        if(!$scene){
            $scene = $this->currentScene;
        }
        if($scene && !empty($this->messages) && array_key_exists($scene,$this->messages)){
            $this->message = array_merge($this->messages[$scene],$this->message);
        }
        return parent::check($data,$rules,$scene);
    }

    protected function notNullValidate($value,$data){
        return $value?true:false;
    }

    protected function getDb($id,$dbName=null){
        if(!$dbName){
            $dbName = $this->dbName;
        }
        if(empty($this->dbInstance[$dbName][$id])){
            $model = ('\app\back\model\\'.ucfirst($dbName))::get($id);
            $this->dbInstance[$dbName][$id] = $model;
        }
        return $this->dbInstance[$dbName][$id];
    }

    protected function id_exist($value,$rule,$data,$field,$desc){
//        $model = ('\app\back\model\\'.ucfirst($rule?$rule:$this->dbName))::get($data[$this->pk]);
        $model = $this->getDb($data[$this->pk]);
        if(empty($model)){
            return $desc?:$field.'不存在';
        }else{
            return true;
        }
    }

    protected function update_unique($value,$rule,$data,$field,$desc){
        $model = ('\app\back\model\\'.ucfirst($rule?$rule:$this->dbName))::get([$this->pk=>['neq',$data[$this->pk]],$field=>$value]);
        return empty($model)?true:($desc?:$field).'已存在';
    }
}