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

class Disk extends Base
{

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
    const UPLOAD_KEY_EXPIRES = 3600 * 24;

    const MD5_FILE_INFO = 'md5_upload_info_';

//    const CHUNK_SIZE = 10485760;
    const CHUNK_SIZE = 1024 * 1024;
    const MAX_SIZE = 3221225472; // pow(1024,3)

    // 缺少数据块信息
    const UPLOAD_FAIL_NO_CHUNK_INFO = 5005;

    // 数据块/文件信息不匹配
    const UPLOAD_FAIL_INFO_NOT_MATCH = 5006;

    // 没有文件被上传
    const UPLOAD_FAIL_NO_CHUNK = 5006;

    static $upload_fail_detail = [
        self::UPLOAD_FAIL_NO_CHUNK_INFO => '缺少数据块信息',
        self::UPLOAD_FAIL_INFO_NOT_MATCH => '数据块/文件信息不匹配',
        self::UPLOAD_FAIL_NO_CHUNK => '没有文件被上传'
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
    public function prepareUpload()
    {
        if (!$this->TokenID || !$this->Auth) return json2(false, '认证失败！无法上传');
        $data = [
            'md5' => request()->post('md5/s', ''),
            'size' => request()->post('size/d', 0),
            'count' => request()->post('count/d', 0),
            'folder_id' => request()->post('folder_id/d', 0),
            'name' => request()->post('name/s', '')
        ];
        $folder = $data['folder_id'];
        $name = $data['name'];

        $name = preg_replace("/^[^a-z_\x{4e00}-\x{9fa5}\(\)\d\-]+$/iu", '', trim($name));
        if (!$name) return json2(false, '非法文件名');

        if (strlen($name) > 200) $name = substr($name, 0, 200);
        $data['name'] = $name;

        $account = $this->account;
        $same = $account->hasSameFileName($name, $data['folder_id']);
//        return json2(false,'',['same'=>$same]);
        if ($same) return json2(false, '已存在相同文件名');

        $hasFolder = true;
        if ($folder !== 0) {
            $folder_detail = $this->account->hasFolder($folder, true);
            $hasFolder = (bool)$folder_detail;
        }
        if (!$hasFolder) return json2(false, '文件夹不存在');

        $md5 = $data['md5'];
        $size = $data['size'];
        $count = ceil($size / self::CHUNK_SIZE);

        if (true || !config('app_debug')) {
            if ($size > self::MAX_SIZE) {
                return json2(false, '文件超过3G，无法上传', ['max_size' => self::MAX_SIZE]);
            }
            if ($size <= 0 || $data['count'] != $count) {
                return json2(false, '非法数据，无法上传', ['count' => $count, 'size' => $size]);
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
        array_walk($uploaded, function (&$value) {
            $value = (int)$value;
        });
        $uploadKeySaveData = [
            'md5' => $md5,
            'folder_id' => $folder,
            'file_name' => $name
        ];

        // 秒传 *******************************************
        if ($this->isExistFileInDb($md5)) {
            $ok = $this->finish($uploadKeySaveData, false);
            return json2(true, '成功', ['fast' => true, 'warn' => $ok === true ? '' : $ok]);
        }
        if (count($uploaded) === (int)$count) {
            $ok = $this->finish($uploadKeySaveData);
            return json2(true, '成功', ['fast' => true, 'warn' => $ok === true ? '' : $ok]);
        }
        // 秒传 *******************************************


        $upload_key = lock(substr(md5($this->TokenID . $md5 . uniqid() . time() . $this->Auth), 8, 16));
        $info = $this->getMd5Info($md5);

//        return json2(false,'test',['info'=>$info]);
        if ($info === false || count($uploaded) !== count($info['uploaded'])) {
            // 缓存可能被删除了

            $info = [
                'md5' => $md5,
                'uploaded' => $uploaded, // []int 已上传的数据块索引
                'size' => $size,
                'count' => $count
            ];
            $this->setMd5Info($md5, $info);
        }

        // 保存upload_key和文件校验md5的关系
        // 24小时有效期
        Cache::set(self::UPLOAD_KEY . $upload_key, json_encode($uploadKeySaveData), self::UPLOAD_KEY_EXPIRES);

        return json2(true, '', ['upload_key' => $upload_key, 'uploaded' => $info['uploaded']]);
    }

    // 传输结束
//    protected function finish(string $md5,int $folder_id,bool $check_db=true){
    protected function finish_old($key, bool $check_db = true)
    {
        if (is_array($key) && !empty($key)) {
            $info = $key;
        } else if (is_string($key) && !empty($key)) {
            $info = $this->parseUploadKey($key);
        } else {
            return false;
        }
        if ($info === false || empty($key)) {
            return false;
        }

        $md5 = $info['md5'];
        $folder_id = $info['folder_id'];
        if ($check_db) {
            $exist = $this->isExistFileInDb($md5);
            if (!$exist) {
                db('net_disk_file')->insert([
                    'id' => $md5,
                    'size' => 0,
                    'time' => time(),
                    'is_merge' => 0
                ]);
            }
        }
        db('user_net_disk')->insert([
            'file_id' => $md5,
            'time' => $_SERVER['REQUEST_TIME'],
            'folder_id' => $folder_id,
            'account_id' => $this->uid,
            'recycle' => 0,
            'name' => $info['file_name']
        ]);
        $this->clearMd5Info($md5);

        $client = new Client([
            'base_uri' => 'http://192.168.1.7:9001',
        ]);


        // 请求node.js端
        // 合并文件，合并完成更新数据库
        try {
            $response = $client->request('POST', '/NetDisk/merge', [
                'multipart' => [
                    [
                        'name' => 'id',
                        'contents' => $md5
                    ]
                ]
            ]);
        } catch (\GuzzleHttp\Exception\GuzzleException $e) {
            return $e->getMessage();
        }
        return json_decode($response->getBody(), true);


    }

    protected function finish($key, bool $check_db = true)
    {
        if (is_array($key) && !empty($key)) {
            $info = $key;
        } else if (is_string($key) && !empty($key)) {
            $info = $this->parseUploadKey($key);
        } else {
            return false;
        }
        if ($info === false || empty($key)) {
            return false;
        }

        $md5 = $info['md5'];
        $folder_id = $info['folder_id'];

        $merge = self::TEMP_SAVE . DS . $md5 . DS . 'merge' . DS . 'merge';
        if (is_file($merge)) {
            $new_merge = self::SAVE_PATH . DS . $md5;
            rename($merge, $new_merge);
            $merge = $new_merge;
            unset($new_merge);
        } else {
            $merge = self::SAVE_PATH . DS . $md5;
        }


        if ($check_db) {
            $exist = $this->isExistFileInDb($md5);
            if (!$exist) {
                db('net_disk_file')->insert([
                    'id' => $md5,
                    'size' => filesize($merge),
                    'time' => time(),
                    'is_merge' => 1
                ]);
            }
        } else {
            try {
                db('net_disk_file')->where(['id' => $md5])->update([
                    'size' => filesize($merge),
                    'time' => time(),
                    'is_merge' => 1
                ]);
            } catch (Exception $e) {
                return false;
            }
        }
        db('user_net_disk')->insert([
            'file_id' => $md5,
            'time' => $_SERVER['REQUEST_TIME'],
            'folder_id' => $folder_id,
            'account_id' => $this->uid,
            'recycle' => 0,
            'name' => $info['file_name']
        ]);
        $this->clearMd5Info($md5);

        deleteDir(self::TEMP_SAVE . DS . $md5);


        return true;


    }

    protected function isExistFileInDb(string $md5)
    {
        $result = db('net_disk_file')->where('id', $md5)->count();
        return (bool)$result;
    }

    protected function getMd5Info(string $md5)
    {
        $info = Cache::get(self::MD5_FILE_INFO . $md5);
        if (empty($info)) return false;
        $info = json_decode($info, true);
        if (json_last_error() !== JSON_ERROR_NONE) return false;
        return $info;
    }

    /**
     * 更新文件MD5对应的信息
     * 上传前初始化用 || 数据块上传完成后调用
     * @param string $md5
     * @param array $info
     */
    protected function setMd5Info(string $md5, array $info): void
    {
        Cache::set(self::MD5_FILE_INFO . $md5, json_encode($info));
    }

    protected function clearMd5Info(string $md5)
    {
        Cache::rm(self::MD5_FILE_INFO . $md5);
    }

    /**
     * 查找临时保存路径下是否存在已上传过的数据块,返回数据块列表
     * @param string $md5
     * @return array
     */
    protected function findTempFile(string $md5)
    {
        $path = self::TEMP_SAVE . DS . $md5;
//        return $path;
        if (!is_dir($path)) return [];
        $file_list = scandir($path);
//        return $file_list;
        array_shift($file_list);
        array_shift($file_list);

        if (empty($file_list)) return [];

        foreach ($file_list as $key => $value) {
            if (is_dir($value) || !preg_match('/^[0-9]+$/', $value)) {
                $tt[] = $file_list[$key];
                array_splice($file_list, $key, 1);
            }
        }
        return $file_list;
    }

    protected function parseUploadKey(string $upload_key)
    {
        $data = Cache::get(self::UPLOAD_KEY . $upload_key);
        $data = json_decode($data, true);
        if (json_last_error() !== JSON_ERROR_NONE) return false;

        return $data;
    }

    protected function checkUploadKeyStatus(string $upload_key, string $md5): bool
    {
//        $data = Cache::get(self::UPLOAD_KEY.$upload_key);
//        $data = json_decode($data,true);
        $data = $this->parseUploadKey($upload_key);
        if ($data === false && !empty($data['md5'])) return false;

        // 延长
        Cache::set(self::UPLOAD_KEY . $upload_key, Cache::get(self::UPLOAD_KEY . $upload_key), self::UPLOAD_KEY_EXPIRES);
        return !empty($data) && $data['md5'] === $md5;
    }

    protected function clearUploadKey(string $key)
    {
        Cache::rm(self::UPLOAD_KEY . $key);
    }

    public function upload()
    {
        // 数据块索引
        $part = request()->post('part/d', null);

        // upload_key
        $key = request()->post('upload_key/s', null);

        $md5 = request()->post('md5/s', null);

        if (in_array($part, ['', null], true) || !$key || !$md5) return json2(false, '上传失败', ['code' => self::UPLOAD_FAIL_NO_CHUNK_INFO]);

        if (!$this->checkUploadKeyStatus($key, $md5)) return json2(false, '上传失败', ['code' => self::UPLOAD_FAIL_INFO_NOT_MATCH, 'key' => $key, 'md5' => $md5]);

        $info = $this->getMd5Info($md5);

        if ($part >= $info['count']) return json2(false, '上传失败', ['code' => self::UPLOAD_FAIL_INFO_NOT_MATCH]);
//        37c9991a3f2919b4186fca5383a55be4
        if (in_array($part, $info['uploaded'])) {
            return json2(true, '成功');
        }

        // 数据块
        $file = request()->file('file');
        if (!$file) return json2(false, '上传失败', ['code' => self::UPLOAD_FAIL_NO_CHUNK]);
        $path = self::TEMP_SAVE . DS . $md5 . DS;

        if (!is_dir($path)) {
            mkdir($path, 0770);
        }

        $move_info = $file->move($path, '');
        $file_name = $move_info->getFilename();

        rename($path . $file_name, $path . $part);
        chmod($path, 0770);
        chmod($path . $part, 0770);

        $merge_dir = $path . 'merge' . DS;
        $merge_file = $merge_dir . 'merge';
        if (!is_dir($merge_dir)) {
            mkdir($merge_dir, 0770);
        }
//        return json2(false,'',['dir'=>$merge_dir]);

//        $path .= $part;

        if (!is_file($merge_file)) {
            $fp = fopen($merge_file, 'x');
            fclose($fp);
        }

        $fp = fopen($merge_file, 'rb+');
        fseek($fp, $part * self::CHUNK_SIZE);
        $tempFp = fopen($path . $part, 'rb');
        fwrite($fp, fread($tempFp, filesize($path . $part)));
        fclose($tempFp);
        fclose($fp);
        chmod($merge_dir, 0770);
        chmod($merge_file, 0770);
//        unlink($path.$part);

        sleep(0.5);
        if ($info['count'] - 1 === count($info['uploaded'])) {
            $ok = $this->finish($key);
            if (!$ok) return json2(false, API_FAIL);
            $this->clearUploadKey($key);

            return json2(true, '成功');


        } else {

            $info['uploaded'][] = $part;
            $this->setMd5Info($md5, $info);

        }

        return json2(true, '成功', $info);


    }

    public function getSavePath()
    {
        return json2(true, '', ['path' => DISK_SAVE_PATH, 'temp' => self::TEMP_SAVE]);
    }

    // 单个数据块的大小
    public function getChunkSize()
    {
        return json2(true, '', ['size' => self::CHUNK_SIZE]);
    }

    public function getMaxSize()
    {
        return json2(true, '', ['size' => self::MAX_SIZE]);
    }

    // 删除文件
    public function deleteFile()
    {
        $fid = request()->post('id/d', 0);
        if (!$fid) return json2(false, API_FAIL);

        $rows = $this->account->userDisk()->where(['id' => $fid])->update([
            'recycle'=>1
        ]);
        return $rows ? json2(true, API_SUCCESS) : json2(false, API_FAIL);
    }

    // 从回收站删除指定文件
    public function deleteFileFromRecycle(){
        $fid = request()->post('id/d', 0);
        if (!$fid) return json2(false, API_FAIL);

        $rows = $this->account->userDisk()->where([
            'id'=>$fid
        ])->delete();
        return $rows?json2(true,API_SUCCESS):json2(false,API_FAIL.',文件可能已被永久删除');
    }

    // 清空回收站
    public function clearRecycle(){
        $this->account->userDisk()->where([
            'recycle'=>1
        ])->delete();
        return json2(true,API_SUCCESS);
    }

    public function move()
    {
        $file = request()->post('file_id/d', 0);
        $folder = request()->post('folder_id/d', 0);
        if (!$file) return json2(false, API_FAIL);

        $account = $this->account;
        if ($folder && !$account->hasFolder($folder)) {
            return json2(false, API_FAIL, ['error' => '目标文件夹不存在或已被删除']);
        }

        $file_detail = $account->getUserFileDetail($file);
        if (!$file_detail) {
            return json2(false, API_FAIL, ['error' => '目标文件不存在或已被删除']);
        }

        if ($file_detail->folder_id === $folder) {
            return json2(true, API_SUCCESS);
        }

        if ($account->hasSameFileName($file_detail->name, $folder)) {
            return json2(false, API_FAIL, ['error' => '已存在同名文件']);
        }

        $rows = $account->userDisk()->where(['id' => $file])->update([
            'folder_id' => $folder
        ]);


        return $rows ? json2(true, API_SUCCESS) : json2(false, API_FAIL);

    }

    public function download()
    {
        $file = request()->param('file/s', '');
        if (!$file) {
            header("HTTP/1.0 404 Not Found");
            exit;
        }
        $data = de_lock($file);
        $data = json_decode($data, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            header("HTTP/1.0 404 Not Found");
            exit;
        }
        if (empty($data['name']) || empty($data['file_id']) || empty($data['auth']) ) {
            header("HTTP/1.0 404 Not Found");
            exit;
        }

        $auth = $data['auth'];

        $account = self::CheckAuth(true,$auth);

        $detail = $account->getUserFileDetail($data['file_id']);

        if (empty($detail)) {
            header("HTTP/1.0 404 Not Found");
            exit;
        }

        $md5 = $detail->file_id;
//        dd($md5);

        $path = DISK_SAVE_PATH . DS . $md5;

//        dd(basename(DISK_SAVE_PATH) . DS . $data['name']);
//        exit;
        if (!is_file($path)) {
            header("HTTP/1.0 404 Not Found");
            exit;
        }

//        return $data['name'];
        header('Content-Type:application/octet-stream');
        Header("Accept-Ranges: bytes");
        Header("Accept-Length: " . filesize($path));
        Header("Content-Disposition: attachment; filename=" . $data['name']);
        header('Content-Description:File Transfer');

//        header('X-Accel-Redirect: ' .basename(DISK_SAVE_PATH) . DS . $data['name']);
//        header('X-Accel-Redirect: ' .FILE_DOWNLOAD_DOMAIN.'/'.$md5);
        header('X-Accel-Redirect: ' .'/net-disk-save/'.$md5);

//        set_time_limit(0);
//        $fp = fopen($path,'rb');
//        while(!feof($fp)){
//            print fread($fp,1024);
//            @ob_end_clean();
//        }

//        ob_end_clean();
        exit;

    }

//    public function move(){
//        $file = request()->post('file_id/d',0);
//        $folder = request()->post('folder_id/d',0);
//        if(!$file) return json2(false,API_FAIL);
//
//        $account = $this->account;
//        // 文件是否存在
////        $account->hasUserFile()
//    }



}
