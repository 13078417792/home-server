<?php

namespace app\tool\controller;

use think\Controller;
use think\Exception;
use think\Request;
use \app\back\controller\Base as BackBaseController;
use \think\Cache;
use \think\Db;
use app\tool\controller\Account as AccountController;
use \app\tool\model\Account as AccountModel;

class Base extends Controller{

    const AUTH_ID_REDIS = 'TOOL_USER_AUTH_';
    const AUTH_REDIS_EXPIRES = 86400;

    const TOKEN_VAILD_CODE = 601;
    const AUTH_VAILD_CODE = 602;

//    const TOOL_SERVER_INIT = 'tool_server_init';

    protected $TokenID;
    protected $Auth;

    protected $uid = 0;
    protected $account;

    public function _initialize(){
        $GLOBALS['uid'] = 0;
        parent::_initialize();

        $token = request()->header('X-TokenID');


//        if(!$token || !Token::CheckToken($token)){
//            BackBaseController::printJson(false,'操作失败',['info'=>'token无效','code'=>self::TOKEN_VAILD_CODE,'token'=>$token]);
//        }
        if(self::checkNeedAuth()){
            $detail = self::CheckAuth(true);
            if($detail===false){
                BackBaseController::printJson(false,'请先登录');
            }
//            BackBaseController::printJson(true,'',['detail'=>$detail]);
//            $this->uid = $detail['uid'];
//            $GLOBALS['uid'] = $detail['uid'];
//            $this->account = AccountModel::find($this->uid);
            $this->uid = $detail->uid;
            $GLOBALS['uid'] = $detail->uid;
            $this->account = $detail;
//            printJson(true,'11',['g'=>$detail,'A'=>request()->header('Authorization')]);
        }

        $this->TokenID = $token;
        $this->Auth = request()->header('Authorization');
        $GLOBALS['Auth'] = $this->Auth;

    }

    static public function CheckAuth($detail=false,string $auth=''){
        if(!$auth) $auth = request()->header('Authorization');
        if(!$auth) return false;
        $result = Cache::get(self::AUTH_ID_REDIS.$auth);

        if(!$result) return false;
        if(!$detail) return true;
        $result = json_decode($result,true);
        $uid = $result['uid'];
        if(!$uid) return false;
        $model = AccountModel::get($uid);
        if(!$model) return false;
        return $model;
//        $result = [
//            'uid'=>$model->uid,
//            'username'=>$model->username,
//            'status'=>$model->status
//        ];
//        return $result;
    }

    // 检查当前请求是否需要登录认证
    static public function checkNeedAuth(){
        $condition = [
            'module'=>request()->module(),
            'controller'=>request()->controller(),
            'action'=>request()->action()
        ];
        $result = Db::connect([
            'type'        => 'mysql',
            // 数据库连接DSN配置
            'dsn'         => '',
            // 服务器地址
            'hostname'    => '192.168.1.5',
            // 数据库名
            'database'    => 'home-server',
            // 数据库用户名
            'username'    => 'vm',
            // 数据库密码
            'password'    => 'root',
            // 数据库连接端口
            'hostport'    => 3306,
            // 数据库连接参数
            'params'      => [],
            // 数据库编码默认采用utf8
            'charset'     => 'utf8',
        ])->table('func_access_control')->where($condition)->find();
        return !empty($result);
    }
}
