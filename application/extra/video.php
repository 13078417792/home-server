<?php
/**
 * Created by PhpStorm.
 * User: 39720
 * Date: 2018/9/26
 * Time: 15:33
 */

//$path = realpath(ROOT_PATH . '..'.DS.'home-server-video');
$path = ROOT_PATH . '..'.DS.'home-server-video';
//echo $path;
if(!is_dir($path)){
//    mkdir($path,0775);
    mkdir($path,0777);
}
$path = realpath($path);
return [

    'PATH'=> $path,
    'block_max_size'=>1024*1024*4
];