<?php

namespace app\tool\controller;

use think\Controller;
use think\Exception;
use think\Request;
use \think\Cache;
use \GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\RequestException;
use \app\tool\model\Account as AccountModel;

class Disk extends Base{

    // 正式的保存路径
    const SAVE_PATH = DISK_SAVE_PATH;

    /**
     * 临时保存路径
     * example
     * 文件校验MD5为ac59075b964b0715
     * 对应的临时保存路径：TEMP_SAVE / ac59075b964b0715
     * 数据块： 0 1 2 3 4 ...
     * TEMP_SAVE / ac59075b964b0715 / 0
     * TEMP_SAVE / ac59075b964b0715 / 1
     * TEMP_SAVE / ac59075b964b0715 / 2
     * ...
     */
    const TEMP_SAVE = DISK_TEMP_SAVE_PATH;


    const UPLOAD_KEY = 'uploadKey_';
    const UPLOAD_KEY_EXPIRES = 3600*24;

    const MD5_FILE_INFO = 'md5_upload_info_';

//    const CHUNK_SIZE = 10485760;
    const CHUNK_SIZE = 1024*1024;
    const MAX_SIZE = 3221225472; // pow(1024,3)

    // 缺少数据块信息
    const UPLOAD_FAIL_NO_CHUNK_INFO = 5005;

    // 数据块/文件信息不匹配
    const UPLOAD_FAIL_INFO_NOT_MATCH = 5006;

    // 没有文件被上传
    const UPLOAD_FAIL_NO_CHUNK = 5006;

    static $upload_fail_detail = [
        self::UPLOAD_FAIL_NO_CHUNK_INFO=>'缺少数据块信息',
        self::UPLOAD_FAIL_INFO_NOT_MATCH=>'数据块/文件信息不匹配',
        self::UPLOAD_FAIL_NO_CHUNK=>'没有文件被上传'
    ];

    /**
     * 上传前的检查
     * 检查同一文件是否已上传过 || 检查是否续传
     *      |                       |
     *      |                       |
     * 返回字段fast=true        返回字段continue=true
     *
     *
     * @post string md5 文件校验码
     * @post int size 文件大小（byte）
     * @post int count 数据块数量
     * @return \think\response\Json
     */
    public function prepareUpload(){
        if(!$this->TokenID || !$this->Auth) return json2(false,'认证失败！无法上传');
        $data = [
            'md5'=>request()->post('md5/s',''),
            'size'=>request()->post('size/d',0),
            'count'=>request()->post('count/d',0),
            'folder_id'=>request()->post('folder_id/d',0),
        ];
        $folder = $data['folder_id'];

        $hasFolder = true;
        if($folder!==0){
            $folder_detail = $this->account->hasFolder($folder,true);
            $hasFolder = (bool)$folder_detail;
        }
        if(!$hasFolder) return json2(false,API_FAIL,['文件夹不存在']);

        $md5 = $data['md5'];
        $size = $data['size'];
        $count = ceil($size / self::CHUNK_SIZE);

        if(true || !config('app_debug')) {
            if ($size > self::MAX_SIZE) {
                return json2(false, '文件超过3G，无法上传', ['max_size' => self::MAX_SIZE]);
            }
            if ($size <= 0 || $data['count'] != $count) {
                return json2(false, '非法数据，无法上传',['count'=>$count,'size'=>$size]);
            }

            $field = ['md5', 'size'];
            foreach ($field as $value) {
                if (!$data[$value]) {
                    return json2(false, '非法数据，无法上传');
                }
            }
        }

        // 检查临时保存路径是否存在已上传文件数据块
        $uploaded = $this->findTempFile($md5);

        // 秒传 *******************************************
        if($this->isExistFileInDb($md5)){
            $ok = $this->finish($md5,$folder,false);
            return json2(true,'成功',['fast'=>true,'warn'=>$ok===true?'':$ok]);
        }
        if(count($uploaded)===(int)$count){
            $ok = $this->finish($md5,$folder);
            return json2(true,'成功',['fast'=>true,'warn'=>$ok===true?'':$ok]);
        }
        // 秒传 *******************************************


        $upload_key = lock(substr(md5($this->TokenID.$md5.uniqid().time().$this->Auth),8,16));
        $info = $this->getMd5Info($md5);


        if($info===false || count($uploaded)!==count($info['uploaded'])){
            // 缓存可能被删除了

            $info = [
                'md5'=>$md5,
                'uploaded'=>$uploaded, // []int 已上传的数据块索引
                'size'=>$size,
                'count'=>$count
            ];
            $this->setMd5Info($md5,$info);
        }

        // 保存upload_key和文件校验md5的关系
        // 24小时有效期
        Cache::set(self::UPLOAD_KEY.$upload_key,json_encode(['md5'=>$md5,'folder_id'=>$folder]),self::UPLOAD_KEY_EXPIRES);

        return json2(true,'',['upload_key'=>$upload_key,'uploaded'=>$info['uploaded']]);
    }

    // 传输结束
    protected function finish(string $md5,int $folder_id,bool $check_db=true){
        if($check_db){
            $exist = $this->isExistFileInDb($md5);
            if(!$exist){
                db('net_disk_file')->insert([
                    'id'=>$md5,
                    'size'=>0,
                    'time'=>time(),
                    'is_merge'=>0
                ]);
            }
        }
        db('user_net_disk')->insert([
            'file_id'=>$md5,
            'time'=>$_SERVER['REQUEST_TIME'],
            'folder_id'=>$folder_id,
            'account_id'=>$this->uid,
            'recycle'=>0
        ]);
        $this->clearMd5Info($md5);

        $client = new Client([
            'base_uri' => 'http://192.168.1.7:9001',
        ]);


        // 请求node.js端
        // 合并文件，合并完成更新数据库
        try{
            $response = $client->request('POST','/NetDisk/merge',[
                'multipart'=>[
                    [
                        'name'=>'id',
                        'contents'=>$md5
                    ]
                ]
            ]);
        }catch(\GuzzleHttp\Exception\GuzzleException $e){
            return $e->getMessage();
        }
        return json_decode($response->getBody(),true);


    }

    protected function isExistFileInDb(string $md5){
        $result = db('net_disk_file')->where('id',$md5)->count();
        return (bool)$result;
    }

    protected function getMd5Info(string $md5){
        $info = Cache::get(self::MD5_FILE_INFO.$md5);
        if(empty($info)) return false;
        $info = json_decode($info,true);
        if(json_last_error()!==JSON_ERROR_NONE) return false;
        return $info;
    }

    /**
     * 更新文件MD5对应的信息
     * 上传前初始化用 || 数据块上传完成后调用
     * @param string $md5
     * @param array $info
     */
    protected function setMd5Info(string $md5,array $info) :void{
        Cache::set(self::MD5_FILE_INFO.$md5,json_encode($info));
    }

    protected function clearMd5Info(string $md5){
        Cache::rm(self::MD5_FILE_INFO.$md5);
    }

    /**
     * 查找临时保存路径下是否存在已上传过的数据块,返回数据块列表
     * @param string $md5
     * @return array
     */
    protected function findTempFile(string $md5) {
        $path = self::TEMP_SAVE.DS.$md5;
//        return $path;
        if(!is_dir($path)) return [];
        $file_list = scandir($path);
//        return $file_list;
        array_shift($file_list);
        array_shift($file_list);

        if(empty($file_list)) return [];

        foreach($file_list as $key => $value){
            if(is_dir($value) || !preg_match('/^[0-9]+$/',$value)){
                $tt[] =  $file_list[$key];
                array_splice($file_list,$key,1);
            }
        }
        return $file_list;
    }

    protected function parseUploadKey(string $upload_key){
        $data = Cache::get(self::UPLOAD_KEY.$upload_key);
        $data = json_decode($data,true);
        if(json_last_error()!==JSON_ERROR_NONE) return false;

        return $data;
    }

    protected function checkUploadKeyStatus(string $upload_key,string $md5) :bool{
//        $data = Cache::get(self::UPLOAD_KEY.$upload_key);
//        $data = json_decode($data,true);
        $data = $this->parseUploadKey($upload_key);
        if($data===false && !empty($data['md5'])) return false;

        // 延长
        Cache::set(self::UPLOAD_KEY.$upload_key,Cache::get(self::UPLOAD_KEY.$upload_key),self::UPLOAD_KEY_EXPIRES);
        return !empty($data) && $data['md5']===$md5;
    }

    protected function clearUploadKey(string $key){
        Cache::rm(self::UPLOAD_KEY.$key);
    }

    public function upload(){
        // 数据块索引
        $part = request()->post('part/d',null);

        // upload_key
        $key = request()->post('upload_key/s',null);

        $md5 = request()->post('md5/s',null);

        if(in_array($part,['',null],true) || !$key || !$md5) return json2(false,'上传失败',['code'=>self::UPLOAD_FAIL_NO_CHUNK_INFO]);

        if(!$this->checkUploadKeyStatus($key,$md5)) return json2(false,'上传失败',['code'=>self::UPLOAD_FAIL_INFO_NOT_MATCH,'key'=>$key,'md5'=>$md5]);

        $info = $this->getMd5Info($md5);

        if($part>=$info['count']) return json2(false,'上传失败',['code'=>self::UPLOAD_FAIL_INFO_NOT_MATCH]);

        if(in_array($part,$info['uploaded'])){
            return json2(true,'成功');
        }

        // 数据块
        $file = request()->file('file');
        if(!$file) return json2(false,'上传失败',['code'=>self::UPLOAD_FAIL_NO_CHUNK]);
        $path = self::TEMP_SAVE.DS.$md5.DS;
        $move_info = $file->move($path,'');
        $file_name = $move_info->getFilename();

        rename($path.$file_name,$path.$part);
        chmod($path,0770);
        chmod($path.$part,0770);

//        $path .= $part;

        if($info['count']-1===count($info['uploaded'])){
            $extend = [];
            $uploadKeyInfo = $this->parseUploadKey($key);
            if($uploadKeyInfo!==false){
                $ok = $this->finish($md5,$uploadKeyInfo['folder_id']);
                if($ok!==true){
                    $extend['info'] = '文件合并失败';
                }
            }else{
                $extend['info'] = '保存文件失败';
            }
            $this->clearUploadKey($key);

            return json2(true,'成功',$extend);


        }else{

            $info['uploaded'][] = $part;
            $this->setMd5Info($md5,$info);

        }

        return json2(true,'成功',$info);


    }

    public function getSavePath(){
        return json2(true,'',['path'=>DISK_SAVE_PATH,'temp'=>self::TEMP_SAVE]);
    }

    // 单个数据块的大小
    public function getChunkSize(){
        return json2(true,'',['size'=>self::CHUNK_SIZE]);
    }

    public function getMaxSize(){
        return json2(true,'',['size'=>self::MAX_SIZE]);
    }

}
