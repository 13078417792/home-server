<?php
/**
 * Created by PhpStorm.
 * User: 39720
 * Date: 2019/1/6
 * Time: 15:34
 */

namespace app\back\validate;


class FileExtInfo extends Base{

    protected $rule = [
        'ext'=>'require|alphaNum|length:1,5',
        'header'=>'require|array',
        'header_index'=>'min:0',
        'end'=>'array'
    ];

    protected $message = [
        'ext.require'=>'必须输入文件后缀扩展名',
        'ext.alphaNum'=>'文件后缀扩展名只能是英文和数字',
        'ext.length'=>'文件后缀扩展名长度为1-5个字符',
        'ext.unique'=>'文件后缀扩展名已存在',

        'header.require'=>'缺少文件头数据',
        'header.array'=>'文件头数据不合法',
        'header_index.min'=>'文件头位置不能小于0',
        'end.array'=>'文件结尾数据不合法',

    ];
}