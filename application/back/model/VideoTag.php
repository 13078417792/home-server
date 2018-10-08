<?php
/**
 * Created by PhpStorm.
 * User: 39720
 * Date: 2018/10/7
 * Time: 22:06
 */

namespace app\back\model;


class VideoTag extends \think\Model{

    protected $resultSetType  = 'collection';

    // 数据库没有的标签就添加并保存ID，没有就留下ID，和刚刚添加的标签返回的ID合并成一个新数组
    public function saveTag(array $data){
        $result = self::where(['name'=>['in',$data]])->column('id,name');
        $tagIDArray = [];

        foreach($result as $key => $value){
            $tagIDArray[] = $key;
        }
        $diff = array_diff($data,$result);
        if(!empty($diff)){
            $save = [];
            foreach($diff as $value){
                $save[] = [
                    'name'=>$value
                ];
            }
            $add_result = $this->saveAll($save);
            foreach($add_result as $value){
                $tagIDArray[] = $value->id;
            }
        }
        return $tagIDArray;
    }
}