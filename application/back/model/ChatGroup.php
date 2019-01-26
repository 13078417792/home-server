<?php
declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: 39720
 * Date: 2018/10/30
 * Time: 19:12
 */

namespace app\back\model;
use \app\back\model\ChatGroupUserRelation as ChatGroupUserRelationModel;


class ChatGroup extends \think\Model{

    public function groupUserRelation(){

        return $this->hasMany('ChatGroupUserRelation','gid','id');
    }

    public function add(array $data){
        $validate = validate('ChatGroup');
        $valid = $validate->scene('add')->check($data);
        if($valid){
//            $this->save($data);
            $data['creator'] = $GLOBALS['USER_DETAIL']['id'];
            $data['time'] = $_SERVER['REQUEST_TIME'];
            $member = $data['member'];
            unset($data['member']);
            if($this->data($data)->save()){
                $relation_data[] = [
                    'uid'=>$GLOBALS['USER_DETAIL']['id'],
                    'gid'=>$this->id
                ];
                foreach($member as $value){
                    if($value!=$GLOBALS['USER_DETAIL']['id']){
                        $relation_data[] = [
                            'gid'=>$this->id,
                            'uid'=>$value
                        ];
                    }
                }
                $relation = model('ChatGroupUserRelation');
                $relation->saveAll($relation_data);
                foreach($relation_data as $value){
                    cache('chat-group-list-'.$value['uid'],null);
                }
                return true;
            }else{
                return '添加失败';
            }
        }else{
            return $this->getError();
        }

    }

    // 群组列表
    public function getList(){
        if(!(
            array_key_exists('USER_DETAIL',$GLOBALS) &&

            !empty($GLOBALS['USER_DETAIL']) &&

            array_key_exists('id',$GLOBALS['USER_DETAIL']) &&

            !empty($GLOBALS['USER_DETAIL']['id'])
        )){
            return [];
        }
        $uid = $GLOBALS['USER_DETAIL']['id'];
//        if($cache = cache('chat-group-list-'.$uid)){
//            return $cache;
//        }
        $result = model('ChatGroupUserRelation')
            ->alias('a')
            ->join('chat_group b','a.gid=b.id')
            ->join('admin c','b.creator=c.id')
            ->where(['a.uid'=>$uid])
            ->field('b.id,b.name,b.thumb,FROM_UNIXTIME(b.time) as time,c.nickname as creator_nickname,c.username as creator_username,b.creator as creator_uid')
            ->order('b.id asc,b.time asc')
//            ->cache('chat-group-list-'.$uid,300)
            ->select();
        return $result;
    }

    public function getGroupMember(int $group_id,int $uid){
        $model = self::get($group_id);
        if(empty($model)){
            return [];
        }
        $relation = $model->groupUserRelation()->alias('a')
            ->join('admin b','a.uid=b.id')
            ->where(['b.status'=>1])
            ->field('b.id,b.nickname,b.username')
            ->select();
        $result = toArray($model);
        $result['member'] = toArray($relation);
        return $result;
    }
}