<?php
namespace app\tool\controller;

use think\Controller;
use think\Request;
use think\Cache;
//use app\tool\controller\Token;
use app\tool\model\Account as AccountModel;

class Account extends Base {

    public function CheckAuthID(){
        return self::CheckAuth()===false?json2(false,'登录已过期'):json2(true,'');
    }

    static private function _CreateAuthID(string $username,array $data){
        $uuid = uniqid();
        $auth_id = substr(md5(password_hash(json_encode(['uuid'=>$uuid,'username'=>$username]),PASSWORD_DEFAULT)),8,16);
        $data['username'] = $username;
        if(array_key_exists('pwd',$data)){
            unset($data['pwd']);
        }
        Cache::set(self::AUTH_ID_REDIS.$auth_id,json_encode($data),self::AUTH_REDIS_EXPIRES);
        return $auth_id;
    }

    // 登录
    public function SignIn(){
        $data = [
            'username'=>request()->post('username/s',''),
            'pwd'=>request()->post('password/s','')
        ];
//        return json2(true,'',[$data]);
        $result = model('Account')->checkAccount($data);
//        return json2(true,'',[AccountModel::get(['username'=>$data['username']])->uid]);
        if($result===true){

            $auth = self::_CreateAuthID($data['username'],[
                'token'=>request()->get('TokenID/s'),
                'uid'=>AccountModel::get(['username'=>$data['username']])->uid,
                'auth_time'=>$_SERVER['REQUEST_TIME']
            ]);

            return json2(true,'登录成功',['AuthID'=>$auth,'expires'=>self::AUTH_REDIS_EXPIRES]);
        }else{
            return json2(false,'登录失败',['error'=>$result]);
        }
    }

    // 登出
    public function SignOut(){
        Cache::rm(self::AUTH_ID_REDIS.$this->Auth);
        return json2(true);
    }

    // 注册
    public function SignUp(){
        $data = [
            'username'=>request()->post('username/s',''),
            'pwd'=>request()->post('password/s',''),
            'rpwd'=>request()->post('repeat_password/s','')
        ];
        $result = model('Account')->createAccount($data);
        return $result===true? json2(true,'注册成功'): json2(false,'注册失败',['error'=>$result]);
        return json2(true,'注册成功');
    }

    public function modify_password(){
        $old = trim(request()->post('old_pwd/s',''));
        $new = trim(request()->post('password/s',''));
        $rpwd = trim(request()->post('repeat_password/s',''));

        $data = [
            'old'=>$old,
            'pwd'=>$new,
            'rpwd'=>$rpwd
        ];

        array_walk($data,function(&$value){
            $value = str_replace(' ','',$value);
        });

        $validate = validate('Account');


//        return dd($validate);
        $ok = $validate->scene('modify')->check($data);
        if(!$ok){
            return json2(false,API_FAIL,['error'=>$validate->getError()]);
        }


        $hash = password_hash($new,PASSWORD_DEFAULT);
        $rows = $this->account->where([
            'uid'=>$this->uid
        ])->update(['pwd'=>$hash]);

        return $rows?json2(true,API_SUCCESS):json2(false,API_FAIL);

    }




}
