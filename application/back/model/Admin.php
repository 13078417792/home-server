<?php
/**
 * Created by PhpStorm.
 * User: 39720
 * Date: 2018/9/9
 * Time: 22:31
 */

namespace app\back\model;

use \think\Cache;
use \app\back\validate\Admin as AdminValidate;
class Admin extends \think\Model
{
    static public $auth_expires = 3600*24*3;
    static public $INIT_PASSWORD = '123456'; // 初始密码

//    protected function initialize(){
//        $this->updateTime = 'update';
//        config('datetime_format',false);
//    }

    public function role(){
        return $this->hasMany('AdminRoleRelation','admin_id');
    }

    public function deleteUser($id){
        if(!$id){
            return '用户不存在';
        }
        $model = self::get($id);
        if(empty($model) || !$model->delete()){
            return '用户不存在';
        }
        $model->role()->delete();
        return true;
    }

    public function login(array $data){
        $validate = validate('Admin');
        $valid = $validate->scene('login')->check($data);
        if($valid){
            $detail = toArray(self::get(function($query) use($data){
                $query->where(['username'=>$data['username']])->field('pwd',true);
            }));
            $auth_data = json_encode(array_merge([
                'uniqid'=>uniqid(),
                'login_ip'=>$_SERVER['REMOTE_ADDR'],
                'login_time'=>$_SERVER['REQUEST_TIME']
            ],$detail));
            $auth_token = substr(md5(password_hash($auth_data,PASSWORD_DEFAULT)),8,16);
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
            ->where('a.status=1')
            ->where($where)
            ->paginate($size,false,['page'=>$page]);
//            ->paginate(10);
        $list = page2Array($list,false);
        return $list;
    }

    public function addAdmin(array $data){
        $validate = validate('Admin');
        $valid = $validate->scene('add')->check($data);
        if($valid){
            if(empty($data['pwd'])){
                $data['pwd'] = password_hash(self::$INIT_PASSWORD,PASSWORD_DEFAULT);
            }else{
                $data['pwd'] = password_hash($data['pwd'],PASSWORD_DEFAULT);
            }
            $data['status'] = 1;
            $data['add_time'] = $_SERVER['REQUEST_TIME'];
            $data['update_time'] = $_SERVER['REQUEST_TIME'];
            $data['nickname'] = 'User_'.substr(md5($data['username'].'_'.$_SERVER['REQUEST_TIME']),8,16);
            $this->data($data)->allowField(true)->save();
            $role_relation_data = [];
            foreach($data['role'] as $value){
                $role_relation_data[] = [
                    'role_id'=>$value,
                    'admin_id'=>$this->id
                ];
            }
            model('AdminRoleRelation')->saveAll($role_relation_data);
            return true;
        }else{
            return $validate->getError();
        }
    }

    public function updateUser(array $data){
        $validate = validate('Admin');
        if(empty($data['pwd'])){
            unset($data['pwd']);
        }
        if($validate->scene('update')->check($data)){
            if(!empty($data['pwd'])){
                $data['pwd'] = password_hash($data['pwd'],PASSWORD_DEFAULT);
            }
            $role_arr = $data['role'];
            unset($data['role']);
            $model = self::get($data['id']);
            $relation = $model->update($data)->role();
            $relation->delete();
            $relation_data = [];
            foreach($role_arr as $role){
                $relation_data[] = [
                  'admin_id'=>$data['id'],
                  'role_id'=>$role
                ];
            }
            $relation->saveAll($relation_data);
//            $model->role()->delete();
            return true;
        }else{
            return $validate->getError();
        }
    }
}