<?php

namespace app\tool\model;

use think\Model;

class NetDiskFolder extends Model{

    static public function parseFolderName(string $name) :array{
        $match_arr = [];
        $input = $name;
        preg_match('/(.*)?\((\d*)\)$/',$name,$match_arr);
        $index = empty($match_arr) || empty($match_arr[2])?0:$match_arr[2];
        $main_name = empty($match_arr)?$name:$match_arr[1];
        if($index===0){
            $name = $main_name;
        }
        return [
            'name'=>$name,
            'main_name'=>$main_name,
            'index'=>$index,
            'input'=>$input
        ];
    }

    static public function folder_exists(int $id,int $pid=null){
        $condition = [
            'account_id'=>$GLOBALS['uid'],
            'id'=>$id,
        ];
        if($pid!==null){
            $condition['pid'] = $pid;
        }
//        return $condition;
        return (bool)self::get($condition);
    }

    static public function folder_exists_name(string $name,int $pid=null,bool $is_main=true){
        if(!$is_main) $name = self::parseFolderName($name)['main_name'];
        $condition = [
            'account_id'=>$GLOBALS['uid'],
            'main_name'=>$name,
        ];
        if($pid!==null){
            $condition['pid'] = $pid;
        }

        return (bool)self::get($condition);
    }

    public function createFolder(array $data){
        self::create($data)->save();
    }

    // 递归方式输出树形数据
    static public function handleTree(array $data,int $pid=0) :array{
        $tree = [];
        foreach($data as $key => $value){
            if($value['pid']==$pid){
                $temp = $value;
                array_splice($data,$key,1);
                $result = self::handleTree($data,$value['id']);
                if(!empty($result)){
                    $temp['children'] = $result;
                }
                $tree[] = $temp;
            }
        }
        if($pid===0){
            $tree = array_filter($tree,function($value){
                return $value['pid']===0;
            });
        }
        return $tree;
    }

    // 引用方式输出树形数据
    static public function handleTreeRef(array $data) {
//        $tree = [];
        foreach($data as $key => &$value){
            if(!empty($data[$value['pid']])){
                $data[$value['pid']]['children'][] = &$value;
//                array_splice($data,$key,1);
            }
        }
        $data = array_filter($data,function($value){
            return $value['pid']===0;
        });
        return  $data;
    }


}