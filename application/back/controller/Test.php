<?php
/**
 * Created by PhpStorm.
 * User: zhouc
 * Date: 2018/12/24
 * Time: 15:22
 */

namespace app\back\controller;


class Test extends \think\Controller{

    public function buffer(){
//        $file = request()->file('blob');
//        $info = $file->move('/home/share/www/');
        $result = ['post'=>request()->post(),'file'=>$_FILES];
//        if(!$info){
//            $result['error'] = $file->getError();
//        }else{
//            $path = '/home/share/www/'.$info->getSaveName();
//            $context = stream_context_create();
//            $result['resource'] = mb_convert_encoding(file_get_contents($path,false,$context),'utf-8','utf-8');
//        }

        return json2(true,'',$result);
    }
}