<?php
/**
 * Created by PhpStorm.
 * User: 39720
 * Date: 2018/9/11
 * Time: 16:05
 */

namespace app\back\controller;

use \think\Cache;
use \Rbac\Rbac;
use \app\back\model\Admin as AdminModel;
use \app\back\model\Access as AccessModel;
class Auth extends Base{

    public function checkAuth(){
//        $token = request()->post('auth_token/s',null,'strval');
        $token = $this->auth;
        $invalid = json2(false,'用户认证已过期，请重新登录');
        if(!$token){
            return $invalid;
        }
        $cache = Cache::tag('auth')->get($token);
        return empty($cache)?$invalid:json2(true,'认证通过');
    }

    // 目前用户的基本信息
    public function getAuthUserInfo(){
//        $token = request()->post('auth_token/s',null,'strval');
        $token = $this->auth;
        $invalid = json2(false,'token失效');
        if(!$token){
            return $invalid;
        }
        $detail = self::unserialize_auth_token($token);
        if($detail===false || empty($detail)){
            return $invalid;
        }
        return json2(true,'',['info'=>$detail]);
    }

    // 获取指定用户的详细信息（包括权限信息）
    public function getAuthDetail(){
        $id = request()->post('id/d',0,'intval');
        $not_exist = json2(false,'用户不存在',['detail'=>[]]);
        if(!$id){
            return $not_exist;
        }
        $detail = Rbac::getAdminDetail($id);
        return empty($detail)?$not_exist:json2(true,'查询成功',['detail'=>$detail]);
    }

    // 用户列表
    public function getUserList(){
        $page = request()->get('page/d',1,'intval');
        $size = request()->get('size/d',10,'intval');
        $condition = [];
        $username = request()->post('username/s',null,'strval');
        $nickname = request()->post('nickname/s',null,'strval');
        $status = request()->post('status/d','');
        if($username){
            $condition['username'] = $username;
        }
        if($nickname){
            $condition['nickname'] = $nickname;
        }
        if($status!==''){
            $condition['status'] = $status;
        }
        $result = model('Admin')->adminList($page,$size,$condition);
        $list = $result['data'];
        unset($result['data']);
        return json2(true,'查询成功',['list'=>$list,'page_var'=>$result,'status'=>$status,'post'=>request()->post(),'server'=>$_POST]);
    }

    // 获取角色列表
    public function getRoleList(){
        $status = request()->post('status/d',null);
        $result = model('Role')->role_list($status);
        return json2(true,'查询成功',['role_list'=>$result]);
    }

    // 添加用户
    public function addUser(){
        $data = [
            'username'=>request()->post('username/s',null,'strval'),
            'pwd'=>request()->post('password/s',null,'strval'),
            'role'=>request()->post('role/a',[])
        ];
        $result = model('Admin')->addAdmin($data);
        return $result===true?json2(true,'添加成功'):json2(false,'添加失败',['error'=>$result,'data'=>$data]);
    }

    // 更新用户状态
    public function updateUserStatus(){
        $this->dbName = 'admin';
        $data = [
            'status'=>request()->post('status/d',null),
            'id'=>request()->post('id/d',null,'intval')
        ];
        if($data['status']===null || !$data['id']){
            return json2(false,'操作失败',['error'=>'缺少数据']);
        }
        if($data['id']==config('rbac.SUPER_UID') && $data['status']!=1){
            return json2(false,'操作失败',['error'=>'此用户不可以被更新状态','data'=>$data]);
        }
        return $this->status($data['id'],$data['status']);
    }

    // 更新权限状态
    public function updateAccessStatus(){
        $this->dbName = 'access';
        $data = [
            'status'=>request()->post('status/d',null),
            'id'=>request()->post('id/d',null,'intval')
        ];
        if($data['status']===null || !$data['id']){
            return json2(false,'操作失败',['error'=>'缺少数据']);
        }
        return $this->status($data['id'],$data['status']);
    }

    // 更新角色状态
    public function updateRoleStatus(){
        $this->dbName = 'role';
        $data = [
            'status'=>request()->post('status/d',null),
            'id'=>request()->post('id/d',null,'intval')
        ];
        if($data['status']===null || !$data['id']){
            return json2(false,'操作失败',['error'=>'缺少数据']);
        }
        $rbac = config('rbac');
        if( in_array($data['id'],[$rbac['SUPER_ROLE_ID'],$rbac['MANAGER_ROLE_ID']]) && $data['status']!=1){
            return json2(false,'操作失败',['error'=>'此角色不能修改状态']);
        }
        return $this->status($data['id'],$data['status']);
    }

    protected function controller_list(){
        $dir = dirname(__FILE__);
        $file = scandir($dir);
        array_shift($file);
        array_shift($file);
        $files = [];
        foreach($file as $value){
            $controller = str_replace('.php','',$value);
            if(!in_array($controller,['Base'])){
                $files[$controller] = $this->action_list($controller);
            }
        }
        return $files;
    }

    protected function action_list($controller){
        $base_list = get_class_methods('app\back\controller\Base');
        $list = get_class_methods('app\\'.request()->module().'\\controller\\'.$controller);
        return array_diff($list,$base_list);
    }

    // 获取API接口列表
    public function getApiList(){
        $result = $this->controller_list();
        return json2(true,'查询成功',['list'=>$result]);
    }

    // 添加权限
    public function addAccess(){
        $data = [
            'name'=>request()->post('name/s',null,'strval'),
            'type'=>request()->post('type/s','api','strval'),
            'path'=>request()->post('path/s',null,'strval'),
        ];
        $result = model('Access')->addAccess($data);
        return $result===true?json2(true,'添加成功'):json2(false,'添加失败',['error'=>$result]);
    }

    // 获取所有类型权限列表
    public function getAccessList(){
        $list = model('Access')->getAccessList();
        return json2(true,'查询成功',['access_list'=>$list]);
    }

    // 获取接口类型权限列表
    public function getApiAccessList(){
        $list = model('Access')->getApiAccessList();
        return json2(true,'查询成功',['access_list'=>$list]);
    }

    // 添加角色
    public function addRole(){
        $data = [
            'name'=>request()->post('name/s',null,'strval'),
            'access'=>request()->post('access/a',[])
        ];
        // return json2(true,['data'=>$data]);
        $result = model('Role')->addRole($data);
        return $result===true?json2(true,'添加成功'):json2(false,'添加失败',['error'=>$result]);
        return json2(true,'test',['data'=>$data]);
    }

    // 删除用户
    public function deleteUser(){
        $this->dbName = 'admin';
        $id = request()->post('id/d',null,'intval');
        if(!$id){
            return json2(false,'删除失败',['error'=>'缺少数据']);
        }
        if($id==config('rbac.SUPER_UID')){
            return json2(false,'删除失败',['error'=>'不能删除此用户']);
        }
        $result = model('Admin')->deleteUser($id);
        return $result?json2(true,'删除成功'):json2(false,'删除失败',['error'=>$result]);
        exit;
        $model = AdminModel::get($id);
        if(empty($model)){
            return json2(false,'删除失败',['error'=>'用户不存在或已被删除']);
        }
        $result = $model->delete();
        if($result > 0){
            $model->role()->delete();
            return json2(true,'删除成功');
        }else{
            return json2(false,'删除失败',['error'=>'用户不存在或已被删除']);
        }
    }

    // 删除权限
    public function deleteAccess(){
        $id = request()->post('id/d',null,'intval');
        if(!$id){
            return json2(false,'删除失败',['error'=>'缺少数据']);
        }
        $result = model('Access')->deleteAccess($id);
        return $result===true?json2(true,'删除成功'):json2(false,'删除失败',['error'=>$result]);
    }

    // 删除角色
    public function deleteRole(){
        $id = request()->post('id/d',null,'intval');
        if(!$id){
            return json2(false,'删除失败',['error'=>'缺少数据']);
        }
        $rbac = config('rbac');
        if(in_array($id,[$rbac['SUPER_ROLE_ID'],$rbac['MANAGER_ROLE_ID']])){
            return json2(false,'删除失败',['error'=>'此角色无法删除']);
        }
        $result = model('Role')->deleteRole($id);
        return $result===true?json2(true,'删除成功'):json2(false,'删除失败',['error'=>$result]);
    }

    public function getRoleDetail(){
        $id = request()->post('id/d',null,'intval');
        if(!$id){
            return json2(false,'没有角色ID');
        }
        $result = model('Role')->getRoleDetail($id);
        return $result===false?json2(false,'查询失败',['error'=>'角色不存在']):json2(true,'查询成功',['detail'=>$result]);
    }

    // 修改用户信息
    public function updateUser(){
        $data = [
            'id'=>request()->post('id/d',null,'intval'),
            'username'=>request()->post('username/s',null,'strval'),
            'nickname'=>request()->post('nickname/s',null,'strval'),
            'pwd'=>request()->post('password/s',null,'strval'),
            'role'=>request()->post('role/a',[])
        ];
        $result = model('Admin')->updateUser($data);
        return $result===true?json2(true,'修改成功',['data'=>$data]):json2(false,'修改失败',['error'=>$result]);
    }

    // 修改角色信息
    public function updateRole(){
        $data = [
            'id'=>request()->post('id/d',null,'intval'),
            'name'=>request()->post('name/s',null,'strval'),
            'access'=>request()->post('access/a',[]),
        ];
        $result = model('Role')->updateRole($data);
        return $result===true?json2(true,'修改成功',['data'=>$data]):json2(false,'修改失败',['error'=>$result]);
    }

    public function getAccessDetail(){
        $id = request()->post('id/d',null,'intval');
        if(!$id){
            return json2(false,'获取权限ID失败');
        }
        $result = model('Access')->getAccessDetail($id);
        return $result===false?json2(false,'查询失败',['error'=>'权限不存在']):json2(true,'查询成功',['detail'=>$result]);
    }

    public function updateAccess(){
        $data = [
            'id'=>request()->post('id/d',null,'intval'),
            'name'=>request()->post('name/s',null,'strval'),
            'type'=>request()->post('type/s',null,'strval'),
            'path'=>request()->post('path/s',null,'strval'),
        ];
        $result = model('Access')->updateAccess($data);
        return $result===true?json2(true,'修改成功',['data'=>$data]):json2(false,'修改失败',['error'=>$result]);
    }

}