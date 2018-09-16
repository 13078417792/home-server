<?php
/**
 * Created by PhpStorm.
 * User: 39720
 * Date: 2018/9/9
 * Time: 22:31
 */

namespace app\back\model;

use \think\Cache;
class Admin extends \think\Model
{
    static public $auth_expires = 3600*24*3;

//    protected function initialize(){
//        $this->updateTime = 'update';
//        config('datetime_format',false);
//    }

    public function role(){
        $this->hasMany('AdminRoleRelation','admin_id');
    }

    public function login(array $data){
        $validate = validate('Admin');
        $valid = $validate->scene('login')->check($data);
        if($valid){
            $detail = toArray(self::get(function($query) use($data){
                $query->where(['username'=>$data['username']])->field('pwd',true);
            }));
            $auth_data = array_merge([
                'uniqid'=>uniqid(),
                'login_ip'=>$_SERVER['REMOTE_ADDR'],
                'login_time'=>$_SERVER['REQUEST_TIME']
            ],$detail);
            $auth_token = password_hash(serialize($auth_data),PASSWORD_DEFAULT);
            Cache::tag('auth')->set($auth_token,$auth_data,self::$auth_expires);
            return json2(true,'登陆成功',['auth_token'=>$auth_token,'expires'=>self::$auth_expires]);
        }else{
            return json2(false,'登录失败',['error'=>$validate->getError()]);
        }
    }

    public function adminDetail($id,$hasPassword=false){
        if(!$id){
            return [];
        }
        $res = self::get(function($query) use ($id,$hasPassword){
            if($hasPassword){
                $query->where(['id'=>$id]);
            }else{
                $query->where(['id'=>$id])->field('pwd',true);
            }

        });
        if(empty($res)){
            return [];
        }else{
            $res = toArray($res);
            if(empty($res)){
                return [];
            }else{
                return $res;
            }
        }
    }

    // 所有用户列表
    public function adminList($page=1,$size=10,array $condition=[]){
        if($size<=0){
            $size = 10;
        }
        if($page<=0){
            $page = 1;
        }
        $where = [];
        foreach($condition as $key => $value){
            $where['a.'.$key] = $value;
        }
        $list = $this
            ->alias('a')
            ->join('admin_role_relation ar','a.id=ar.admin_id','LEFT')
            ->join('role r','r.id=ar.role_id and r.status=1','LEFT')
            ->group('a.id')
            ->field('a.id as uid,a.username,a.add_time,a.update_time,a.nickname,a.status,GROUP_CONCAT(r.name) as role_name,FROM_UNIXTIME(a.add_time) as add_time_fmt')
            ->order('a.id asc,r.id asc')
            ->where('a.status<>2')
            ->where($where)
            ->paginate($size,false,['page'=>$page]);
//            ->paginate(10);
        $list = page2Array($list,false);
        return $list;
    }
}