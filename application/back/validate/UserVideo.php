<?php
/**
 * Created by PhpStorm.
 * User: 39720
 * Date: 2018/10/7
 * Time: 18:13
 */

namespace app\back\validate;


class UserVideo extends Base{

    protected $rule = [

        'id'=>'require|id_exist',
        'title'=>'require|length:3,100',
        'intro'=>'require|length:3,233',
        'thumb'=>'require'
    ];

    protected $message = [
        'id.require'=>'视频分类不存在',
        'title.require'=>'标题不能为空',
        'title.length'=>'标题长度为3-100字符',
        'title.unique'=>'标题已存在',
        'intro.require'=>'简介不能为空',
        'intro.length'=>'简介长度为3-233字符',
        'thumb.require'=>'必须选择或上传一张封面图'
    ];

    protected $scene = [
        'update'=>['id','title','intro','thumb'],
        'add'=>['title'=>'require|length:3,100|unique:user_video','intro','thumb'],
    ];

    protected $description = [
        'id'=>'视频分类'

    ];
}