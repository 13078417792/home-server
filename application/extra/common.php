<?php
/**
 * Created by PhpStorm.
 * User: 39720
 * Date: 2018/10/30
 * Time: 17:05
 */
$image_path = ROOT_PATH.'..'.DS.'home-server-image';
if(!file_exists($image_path)){
    mkdir($image_path);
}
$image_path = realpath($image_path);
return [
    'path'=>[
        'image'=>$image_path
    ]
];