<?php
/**
 * Created by PhpStorm.
 * User: 39720
 * Date: 2019/1/11
 * Time: 15:27
 */
use \think\Env;

$dev = [
    'hostname'        => Env::get('local_database.hostname'),
    'username'        => Env::get('local_database.username'),
    'password'        => Env::get('local_database.password'),
];

$pro = [
    'hostname'        => Env::get('online_database.hostname'),
    'username'        => Env::get('online_database.username'),
    'password'        => Env::get('online_database.password'),
];

//Env::get(Env::get('env').'_database.host')
$tool_db = array_merge([
    // 数据库类型
    'type'            => 'mysql',
    // 服务器地址
//    'hostname'        => '192.168.1.5',
//    // 用户名
//    'username'        => 'vm',
//    // 密码
//    'password'        => 'root',
    // 数据库名
    'database'        => 'tool',
    // 端口
    'hostport'        => '',
    // 连接dsn
    'dsn'             => '',
    // 数据库连接参数
    'params'          => [],
    // 数据库编码默认采用utf8
    'charset'         => 'utf8',
    // 数据库表前缀
    'prefix'          => '',
    // 数据库调试模式
    'debug'           => true,
    // 数据库部署方式:0 集中式(单一服务器),1 分布式(主从服务器)
    'deploy'          => 0,
    // 数据库读写是否分离 主从式有效
    'rw_separate'     => false,
    // 读写分离后 主服务器数量
    'master_num'      => 1,
    // 指定从服务器序号
    'slave_no'        => '',
    // 自动读取主库数据
    'read_master'     => false,
    // 是否严格检查字段是否存在
    'fields_strict'   => true,
    // 数据集返回类型
    'resultset_type'  => 'array',
    // 自动写入时间戳字段
    'auto_timestamp'  => false,
    // 时间字段取出后的默认时间格式
    'datetime_format' => 'Y-m-d H:i:s',
//    'datetime_format'=>false,
    // 是否需要进行SQL性能分析
    'sql_explain'     => false,
],Env::get('env')==='online'?$pro:$dev);

return $tool_db;