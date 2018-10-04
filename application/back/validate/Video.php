<?php
/**
 * Created by PhpStorm.
 * User: 39720
 * Date: 2018/9/26
 * Time: 17:24
 */

namespace app\back\validate;

use \app\back\controller\Video as VideoController;

class Video extends Base{


    protected $rule = [

        // 上传
        'file'=>'require|file|fileExt:mp4,mkv,rmvb,avi,flv',
        'index'=>'require|number',
        'end'=>'require|number',
//        'title'=>'require'
    ];

    protected $message = [
        'file.require'=>'缺少必须数据2',
        'index.require'=>'缺少必须数据',
        'end.require'=>'缺少必须数据',
        'title.require'=>'缺少必须数据',


        'file.file'=>'仅允许上传mp4,mkv,rmvb,avi,flv类型',
        'file.fileExt'=>'仅允许上传mp4,mkv,rmvb,avi,flv类型',
        'file.max'=>'上传失败',

        'index.number'=>'数据错误',
        'end.number'=>'数据错误'

    ];

    protected $scene = [
        'upload'=>['file','inedx','end']
    ];



}