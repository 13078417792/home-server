<?php

namespace app\tool\controller;

use think\Controller;
use think\Request;

class FileFormat extends Controller
{
    public function getInfo(){
        $info = db('file_ext_info')->select();
        return json2(true,'',['info'=>$info]);
    }
}
