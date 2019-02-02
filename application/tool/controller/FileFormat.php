<?php

namespace app\tool\controller;

use think\Controller;
use think\Request;
use \think\Db;

class FileFormat extends Controller
{
    public function getInfo(){
        $info = Db::connect('back_db_config')->name('file_ext_info')->select();
        return json2(true,'',['info'=>$info]);
    }
}
