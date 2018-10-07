<?php
/**
 * Created by PhpStorm.
 * User: 39720
 * Date: 2018/10/6
 * Time: 22:39
 */

namespace app\back\validate;


class VideoCategory extends Base{

    protected $rule = [

        'name'=>'require|length:2,20',
        'alias'=>'require|length:2,30',
    ];

    protected $message = [
        'name.require'=>'必须输入分类名',
        'name.length'=>'分类名长度在2-20个字符',
        'alias.require'=>'必须输入别名',
        'alias.length'=>'别名长度在2-30个字符',

    ];

    protected $scene = [
        'add'=>['name','alias']
    ];
}