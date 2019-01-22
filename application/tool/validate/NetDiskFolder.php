<?php
/**
 * Created by PhpStorm.
 * User: zhouc
 * Date: 2019/1/21
 * Time: 21:07
 */

namespace app\tool\validate;
use \app\tool\model\NetDiskFolder as NetDiskFolderModel;
use \app\tool\model\Account as AccountModel;


class NetDiskFolder extends Base{

    protected $rule = [
        'id'=>'require|number|folderIsExists:id',
        'name'=>'require|length:1,40|folderName|folderNameIndex',
        'pid'=>'require|number|folderIsExists:pid'
    ];

    protected $message = [

        'name.require'=>'必须输入文件夹名',
        'name.length'=>'文件夹名长度必须控制在1-40位',
        'pid.require'=>'非法数据',
        'pid.number'=>'非法数据',

    ];

    protected $messages = [
        'update'=>[
            'id.require'=>'修改失败',
            'id.number'=>'修改失败',
        ]
    ];

    protected $scene = [
        'create'=>['name','pid'],
        'update'=>['id','name'=>'require|length:1,40|folderName|folderNameIndex:1','pid']
    ];

    protected function folderName($value,$rule,$data){
        $preg = "/^[a-z_\x{4e00}-\x{9fa5}\(\)\d]+$/iu";
        return preg_match($preg,$value)?true:'文件夹名只能是中英文、数字、下划线';
    }

    protected function folderNameIndex($value,$rule,$data){
        $is_update = (bool)$rule;
        $index = $data['index'];
        $main_name = $data['main_name'];
        $pid = $data['pid'];
        $input = $data['input'];
        if(!preg_match('/^.*?\((\d*)\)$/',$input) && !$is_update) return true;
        $condition = [
            'main_name'=>$main_name,
            'pid'=>$pid,
            'index'=>(int)$index
        ];
        if($is_update){
            $condition['id'] = ['<>',$data['id']];

        }
        $count = AccountModel::find($GLOBALS['uid'])->folder()->where($condition)->count();
        return $count?'文件夹已存在':true;
    }

    protected $field = [
        'name'=>'文件夹名',
        'id'=>'文件夹ID',
        'pid'=>'父目录'
    ];

    protected function folderIsExists($value,$rule,$data,$field,$desc){
        $type = $field;
        if(!in_array($type,['id','pid'])) return '内部数据验证错误';
        if($value===0) return $type==='id'?'文件夹不存在':true;
        $jt = $desc?:$field;
        if($field==='id'){
            $jt = str_replace(['id','ID'],'',$jt);
        }
        return NetDiskFolderModel::folder_exists($value)?true:"{$jt}不存在";
    }
}