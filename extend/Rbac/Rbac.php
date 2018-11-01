<?php
/**
 * Created by PhpStorm.
 * User: 39720
 * Date: 2018/9/9
 * Time: 22:18
 */

namespace Rbac;


class Rbac
{
    static public function getAdminDetail($id){
        $cache = cache('admin_detail_'.$id);
        if(!empty($cache)){
            return $cache;
        }
        $res = db('Admin')
            ->alias('a')
            ->join('admin_role_relation ar','a.id=ar.admin_id','LEFT')
            ->join('role r','r.id=ar.role_id AND r.status=1','LEFT')
            ->join('access_role_relation arc','arc.role_id=r.id','LEFT')
            ->join('access ac','ac.id=arc.access_id AND ac.status=1','LEFT')
            ->where(['a.id'=>$id])
            ->field('a.id as uid,a.username,a.nickname,a.status,r.name as role,r.id as role_id,group_concat(ac.name) as access,group_concat(ac.path) as access_path,a.add_time,FROM_UNIXTIME(a.add_time) as add_time_fmt,a.update_time,FROM_UNIXTIME(a.update_time) as update_time_fmt')
            ->group('r.id')
            // ->cache(3600)
            ->select();

        $detail = [
            'role'=>[],
            'access'=>[],
            'access_name'=>[]
        ];
        foreach($res as $key => $value){

            $detail['uid'] = $value['uid'];
            $detail['username'] = $value['username'];
            $detail['nickname'] = $value['nickname'];
            $detail['status'] = $value['status'];
            $detail['add_time_fmt'] = $value['add_time_fmt'];
            $detail['add_time'] = $value['add_time'];
            $detail['update_time'] = $value['update_time'];
            $detail['update_time_fmt'] = $value['update_time_fmt'];
            if($value['role_id']){
                $detail['role'][$value['role_id']] = $value['role'];
            }
            if($detail['access']){
                $detail['access_name'] = array_merge(empty($detail['access_name'])?[]:$detail['access_name'],explode(',',$value['access']));
                $detail['access'] = array_merge(empty($detail['access'])?[]:$detail['access'],explode(',',$value['access_path']));
            }
        }


        cache('admin_detail_'.$id,$detail,2*60); // 2分钟缓存
        return $detail;
    }

    // 验证权限
    static public function validateAccess($id,$path){
        if(array_key_exists('isWhite',$GLOBALS) && $GLOBALS['isWhite']!==null && gettype($GLOBALS['isWhite'])==='boolean'){
            $ok = $GLOBALS['isWhite'];
        }else{
            $ok = self::isWhite($path);
        }
        if($ok){
            return true;
        }
        if(!$id){
            return false;
        }
        $res = self::getAdminDetail($id);
        if(config('rbac.SUPER_USERNAME')===$res['username']){
            return true;
        }elseif(in_array(config('rbac.SUPER_ROLE'),$res['role'])){
            return true;
        }
        return in_array($path,$res['access']);
    }

    static public function isWhite($path){
        $GLOBALS['isWhite'] = null;
        $white = config('rbac.WHITELIST_PATH');
        $path = preg_replace('/^\//','',$path);
        $path_arr = explode('/',$path);
        $ok = false;
        if(array_key_exists($path_arr[0],$white)){
            $temp = $white[$path_arr[0]];
            if( gettype($temp)==='array' && count($temp)>0 && array_key_exists($path_arr[1],$temp) ){
                $temp = $temp[$path_arr[1]];
                if( gettype($temp)==='array' && count($temp)>0 ){
                    $ok = in_array($path_arr[2],$temp);
                }else{
                    $ok = true;
                }
            }else{
                $ok = true;
            }
        }else{
            $ok = false;
        }
        $GLOBALS['isWhite'] = $ok;
        return $ok;
    }

    // 是否超管
    static public function isSuper($id){
        if(!$id){
            return false;
        }
        if($id===config('rbac.SUPER_UID')){
            return true;
        }
        $detail = self::getAdminDetail($id);
        if(in_array(config('rbac.SUPER_ROLE_ID'),array_values($detail['role']))){
            return true;
        }
        return false;
    }

    // 是否普通管理员
    static public function isManager($id){
        if(!$id){
            return false;
        }
        $detail = self::getAdminDetail($id);
        if(in_array(config('rbac.MANAGER_ROLE_ID'),array_values($detail['role']))){
            return true;
        }
        return false;
    }

}