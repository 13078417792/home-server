<?php
/**
 * Created by PhpStorm.
 * User: 39720
 * Date: 2018/9/9
 * Time: 22:31
 */

namespace app\back\model;


class Role extends \think\Model
{
    public function admin(){
        return $this->hasMany('AdminRoleRelation','role_id');
    }

    public function access(){
        return $this->hasMany('AccessRoleRelation','role_id');
    }

    public function role_list($status=null){
        $where = [];
        if($status!==null){
            $where['status'] = $status;
        }
        $result = $this->where($where)
                    ->where('status<>2')
                    ->field('id,name,FROM_UNIXTIME(add_time) as add_time,update_time,status')
                    ->select();
        return $result;
    }

    public function addRole(array $data){
        $validate = validate('Role');
        if($validate->scene('add')->check($data)){
            $data = array_merge($data,[
                'add_time'=>$_SERVER['REQUEST_TIME'],
                'update_time'=>$_SERVER['REQUEST_TIME'],
                'status'=>1
            ]);
            $this->data($data)->allowField(true)->save();
            $relation_save = [];
            foreach(self::getArrayAccess($data['access']) as $value){
                $relation_save[] = [
                  'role_id'=>$this->id,
                  'access_id'=>$value
                ];
            }
            model('AccessRoleRelation')->saveAll($relation_save);
            return true;
        }else{
            return $validate->getError();
        }
    }

    static public function getArrayAccess(array $data){
        $rtdata = [];
        foreach($data as $value){
            if(gettype($value)==='array'){
                $rtdata = array_merge($rtdata,self::getArrayAccess($value));
            }else{
                if(preg_match('/^\d*$/',$value)){
                    $rtdata[] = (int)$value;
                }else{
                    $rtdata[] = $value;
                }
            }
        }
        return $rtdata;
    }

    public function deleteRole($id){
        if(!$id){
            return '角色不存在';
        }
        $model = self::get($id);
        if(empty($model) || !$model->delete()){
            return '角色不存在';
        }
        $rbac = config('rbac');
        if(in_array($id,[$rbac['SUPER_ROLE_ID'],$rbac['MANAGER_ROLE_ID']])){
            return '此角色无法删除';
        }
        $model->admin()->delete();
        return true;
    }

    public function updateRole(array $data){
        $validate = validate('Role');
        if($validate->scene('update')->check($data)){
            $model = self::get($data['id']);
            $access = self::getArrayAccess($data['access']);
            unset($data['access']);
            $relation_save = [];
            foreach($access as $value){
                $relation_save[] = [
                    'access_id'=>$value
                ];
            }
            $data['update_time'] = $_SERVER['REQUEST_TIME'];
            $model->update($data);
            $model->access()->delete();
            $model->access()->saveAll($relation_save);
            return true;
        }else{
            return $validate->getError();
        }
    }

    public function getRoleDetail($id){
        $model = self::get(function($query) use($id){
            $query->where('id',$id)->field('*,FROM_UNIXTIME(add_time) AS add_time_fmt');
        });
        if(empty($model)){
            return false;
        }else{
            $detail = toArray($model);
//            $detail['access'] = toArray($model->access()->alias('a')->join('access b','a.access_id=b.id')->field('a.access_id,a.role_id,b.type as access_type')->select());
            $detail['access'] = toArray($model->access()
                ->alias('a')
                ->join('access b','a.access_id=b.id')
                ->field('a.access_id,a.role_id,b.type,b.name,b.add_time,FROM_UNIXTIME(b.add_time) as add_time_fmt,b.update_time,b.path,b.status')
                ->select());
            return $detail;
        }
    }
}