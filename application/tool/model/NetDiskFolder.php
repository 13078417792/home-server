<?php

namespace app\tool\model;

use think\Model;
use app\tool\model\Account as AccountModel;

class NetDiskFolder extends Base{

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
    static public function handleTree(array $data,int $pid=0,$level=1) :array{
        $tree = [];
        foreach($data as $key => $value){
            if($value['pid']==$pid){
                $temp = $value;
                array_splice($data,$key,1);
                $result = self::handleTree($data,$value['id'],$level+1);
                if(!empty($result)){
                    $temp['children'] = $result;
                }
                $tree[] = $temp;
            }
        }
        $tree = array_filter($tree,function($value) use($pid){
            return $value['pid']===$pid;
        });

        return $tree;
    }

    // 引用方式输出树形数据
    static public function handleTreeRef(array $data,int $pid=0) {
        foreach($data as $key => &$value){
            if(!empty($data[$value['pid']])){
                $data[$value['pid']]['children'][] = &$value;
            }
        }
        $data = array_filter($data,function($value) use($pid){
            return $value['pid']===$pid;
        });
        return  $data;
    }

    // 是否有子节点
    static public function getChildrenFolder($id){
        $account = self::getAccount();
        if($id instanceof self){
            $detail = $id;
        }else if(is_int($id) && $id>0){
            $detail = $account->folder()->find($id);
            unset($account);
        }else{
            return false;
        }
        $children_node = $account->folder()->where([
            'parent_key'=>['like',"{$detail->parent_key}{$detail->id}-%"]
        ])->column('id')?:[];

        return $children_node;

    }

    static public function getChildrenFile($folder_id,bool $count=false){
        $account = self::getAccount();
        if($folder_id instanceof self){
            $detail = $folder_id;
        }else if(is_int($folder_id) && $folder_id>0){
            $detail = $account->folder()->find($folder_id);
            unset($account);
        }else{
            return false;
        }
        $model = $detail->alias('a')->join('user_net_disk b','a.id=b.folder_id','LEFT')->where([
            'a.id'=>$detail->id,
            'recycle'=>0
        ]);
        if($count){
            return $model->count();
        }else{
            $model->column('b.id')?:[];
        }
        return $detail->alias('a')->join('user_net_disk b','a.id=b.folder_id','LEFT')->where([
            'a.id'=>$detail->id,
            'recycle'=>0
        ])->column('b.id')?:[];
//        return (bool)$detail->alias('a')->join('user_net_disk b','a.id=b.file_id')->count();
    }


}