<?php
/**
 * Created by PhpStorm.
 * User: 39720
 * Date: 2018/9/9
 * Time: 22:05
 */
use \app\back\controller\Base as BackBaseController;
use \GuzzleHttp\Client;

// 网盘-文件最终保存文职
if(!realpath(ROOT_PATH.'..'.DS.'..'.DS.'net-disk-save')){
    mkdir(ROOT_PATH.'..'.DS.'..'.DS.'net-disk-save',0770);
}
define('DISK_SAVE_PATH',realpath(ROOT_PATH.'..'.DS.'..'.DS.'net-disk-save'));

// 网盘-上传的数据块的临时保存位置
if(!realpath(DISK_SAVE_PATH.DS.'temp')){
    mkdir(DISK_SAVE_PATH.DS.'temp',0770);
}
define('DISK_TEMP_SAVE_PATH',realpath(DISK_SAVE_PATH.DS.'temp'));

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

function json2($success,$msg='',$extend=[],array $log=[]){
    return BackBaseController::json($success,$msg,$extend);
}

function token2($name,$type='md5'){
    $token = request()->token($name, $type);
    return $token;
}

function toArray($val){
    $type = gettype($val);
//    if($type==='array' && count($val)>0 && $val[0] instanceof \think\Model){
    if($type==='array' && count($val)>0 && $val[array_keys($val)[0]] instanceof \think\Model){
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

function printJson(...$args){
    BackBaseController::printJson(...$args);
}

/**
 * 转换JSON
 * @param string $json JSON字符串
 * @param bool $inside 是否内部接口
 * @return bool|mixed
 */
function convertJsonToArray(string $json,bool $inside=true){
    $result = json_decode($json);
    if(json_last_error()!==JSON_ERROR_NONE) return false;
    if($inside && !array_key_exists('success',$result)) return false;
    return $result;
}


function deleteDir($dir) {
    if(!is_dir($dir)) return;
    $files = scandir($dir);
    array_shift($files);
    array_shift($files);

    foreach($files as $file){
        $current = $dir.DS.$file;
        if(is_dir($current)){
            deleteDir($current);
        }else{
            unlink($current);
        }
    }
    rmdir($dir);
}