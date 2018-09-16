<?php
/**
 * Created by PhpStorm.
 * User: 39720
 * Date: 2018/9/10
 * Time: 15:31
 */

namespace app\back\Validate;


class Base extends \think\Validate{

    protected $messages = [];

    public function check($data, $rules = [], $scene = '')
    {
        if($scene && !empty($this->messagrs) && array_key_exists($scene,$this->messages)){
            $this->message = $this->messages[$scene];
        }
        return parent::check($data,$rules,$scene);
    }
}