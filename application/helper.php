<?php
/**
 * Created by PhpStorm.
 * User: 39720
 * Date: 2018/9/9
 * Time: 22:05
 */
use \app\back\controller\Base as BackBaseController;
//use crypt;
function dd($var)
{
    $arr=debug_backtrace();
    echo "<div style='border:1px dotted black;'>";
    if($arr)
    {
        echo "文件名：".$arr[0]['file']."<br>";
        echo "文件行号:".$arr[0]['line']."<br>";
    }
    echo "</div>";
    if(is_array($var)||is_object($var))
    {
        echo "<pre><div style='border:1px solid black;color:red'>-----------调试信息开始---------<br>";
        print_r($var);
        echo "-----------调试信息结束---------</div></pre>";
    }
    else
    {
        echo "<pre><div style='border:1px solid black;color:black'>-----------调试信息开始---------<br>";
        var_dump($var);
        echo "-----------调试信息结束---------</div></pre>";
    }
}

function json2($success,$msg='',$extend=[]){
    return BackBaseController::json($success,$msg,$extend);
}

function token2($name,$type='md5'){
    $token = request()->token($name, $type);
    return $token;
}

function toArray($val){
    $type = gettype($val);
    if($type==='array' && count($val)>0 && $val[0] instanceof \think\Model){
        return collection($val)->toArray();
    }elseif($type==='object' && $val instanceof \think\Model){
        return $val->toArray();
    }else{
        return [];
    }
}

function page2Array($obj,$field='data'){
    if(!$obj instanceof \think\paginator\driver\Bootstrap){
        return [];
    }
    $result = json_decode(json_encode($obj),true);
    if($field){
        if(!array_key_exists($field,$result)){
            return [];
        }else{
            return $result[$field];
        }
    }else{
        return $result;
    }

}

function lock($data){
    return crypt::encrypt($data);
}

function de_lock($data){
    return crypt::decrypt($data);
}

function replace($str,$preg=''){
    $str = trim($str);
    if($preg){
        $str = preg_replace($preg,'',$str);
    }
    return $str;

}