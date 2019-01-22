<?php

namespace app\tool\controller;

use think\Controller;
use think\Exception;
use think\Request;
use \app\back\controller\Base as BackBaseController;
use \think\Cache;
use \think\Db;
use app\tool\controller\Account as AccountController;

class Base extends Controller{

    const AUTH_ID_REDIS = 'TOOL_USER_AUTH_';
    const AUTH_REDIS_EXPIRES = 86400;

    const TOKEN_VAILD_CODE = 601;
    const AUTH_VAILD_CODE = 602;

//    const TOOL_SERVER_INIT = 'tool_server_init';

    protected $TokenID;
    protected $Auth;

    protected $uid = 0;

    public function _initialize(){
        $GLOBALS['uid'] = 0;
        parent::_initialize();

        $token = request()->header('X-TokenID');


        if(!$token || !Token::CheckToken($token)){
            BackBaseController::printJson(false,'操作失败',['info'=>'token无效','code'=>self::TOKEN_VAILD_CODE,'token'=>$token]);
        }
        if(self::checkNeedAuth()){
            $detail = AccountController::CheckAuth(true);
            if($detail===false){
                BackBaseController::printJson(false,'请先登录');
            }
//            BackBaseController::printJson(true,'',['detail'=>$detail]);
            $this->uid = $detail['uid'];
            $GLOBALS['uid'] = $detail['uid'];
//            printJson(true,'11',['g'=>$detail,'A'=>request()->header('Authorization')]);
        }

        $this->TokenID = $token;
        $this->Auth = request()->header('Authorization');

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
