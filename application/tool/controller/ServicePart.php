<?php
/**
 * Created by PhpStorm.
 * User: 39720
 * Date: 2019/1/31
 * Time: 18:26
 */

namespace app\tool\controller;


class ServicePart extends Base{

    static $part = [
        'home'
    ];

    public function update_upload_ip(string $token='',string $part=''){
//        return 123;
        if(empty($token) || empty($part) || !in_array($part,self::$part)) return json2(false);
        $newest_token = db('update_ip_token')->where([
            'part'=>$part
        ])->order('id desc')->value('token');
        if($token!==$newest_token) return json2(false);
        $ip = request()->ip();
        $newest_ip = db('second_ip')->where([
            'part'=>$part
        ])->order('id desc')->value('ip');
        if(empty($newest_ip) || $newest_ip!==$ip){
            db('second_ip')->insert([
                'ip'=>$ip,
                'time'=>$_SERVER['REQUEST_TIME'],
                'part'=>$part,
            ]);
        }
        return json2(true);
//        db('')
//        return json2(true,'',['test_msg'=>'ok']);
    }

    static public function part_exists(string $part){
        return in_array($part,self::$part);
    }

    public function getIP(string $part=''){
        if( empty($part) || !self::part_exists($part)) return json2(false);
        $ip = db('second_ip')->where([
            'part'=>$part
        ])->order('id desc')->value('ip');
        if(empty($ip)) return json2(false);
        return json2(true,'',['server_ip'=>$ip]);
    }

}