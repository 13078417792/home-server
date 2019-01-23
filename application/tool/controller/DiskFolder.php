<?php

namespace app\tool\controller;

use think\Controller;
use think\Request;
use \app\tool\model\NetDiskFolder as NetDiskFolderModel;
use \app\tool\model\Account as AccountModel;
use \think\Db;
use \think\Cache;

class DiskFolder extends Base
{

    protected $is_update = true;
    protected $account;
    const DEL_KEY_REDIS = 'delFolderKey_';

    public function _initialize()
    {
        parent::_initialize();
        $this->account = AccountModel::find($this->uid);
    }

    protected function actionFolder()
    {
        $data = [
            'update_time' => $_SERVER['REQUEST_TIME'],
        ];

        // 文件夹名
        $name = request()->post('name/s', null);
        $name = str_replace(' ', '', $name);
        if ($this->is_update) {
            $id = request()->post('id/d', 0);
            $data['id'] = $id;
            $scene = 'update';
            $tips = '更新';
        } else {
            $data['create_time'] = $_SERVER['REQUEST_TIME'];
            $scene = 'create';
            $tips = '创建';
        }

        $pid = request()->post('parent/d', 0);
        if ($pid < 0) {
            $pid = 0;
        }

        /**
         * 分析文件夹名称
         * name 最终显示的名称
         * main_name 不带索引的名称
         * index 同一目录下相同名称的索引
         */
        $handleName = NetDiskFolderModel::parseFolderName($name);

        $name = $handleName['name'];
        $main_name = $handleName['main_name'];
        $index = $handleName['index'];
        $input = $handleName['input'];

        $data = array_merge([
            'name' => $name,
            'main_name' => $main_name,
            'index' => $index,
            'pid' => $pid,
            'input' => $input
        ], $data);
//        return json2(true,'',[$data,$scene]);

        $validate = validate('NetDiskFolder');
        if (!$validate->scene($scene)->check($data)) {
            return json2(false, "{$tips}失败", ['error' => $validate->getError()]);
        }

        // 根据main_name和pid检查是否存在同名文件夹
        if (NetDiskFolderModel::folder_exists_name($main_name, $pid)) {
            // 找出同名文件夹的索引列表
            $name_index = AccountModel::find($this->uid)->folder()->where([
                'main_name' => $main_name,
                'pid' => $pid
            ])->order('index asc')->column('index');

            if (!$this->is_update || !in_array($data['index'], $name_index)) {


                // 查找断层索引
                foreach ($name_index as $key => $value) {
                    if ($key != $value) {
                        $data['index'] = $key;
                        break;
                    }
                }

                if (!$this->is_update) {
                    // 如果索引重复 添加一个索引
                    if (in_array($data['index'], $name_index)) {
                        $data['index'] = $name_index[count($name_index) - 1] + 1;
                    }
                }
            }


            // 重新拼接文件夹名
            if ($data['index'] != 0) {
                $data['name'] = $data['main_name'] . '(' . $data['index'] . ')';
            }

        }

        $parent_key = '';
        if ($pid != 0) {
            $parent_key = AccountModel::find($this->uid)->folder()->where([
                'id' => $data['pid']
            ])->value('parent_key');
            $parent_key .= "${data['pid']}-";
        }
        $data['parent_key'] = $parent_key;


        unset($data['input']);
        $account = AccountModel::find($this->uid);
        if ($this->is_update) {
            $id = $data['id'];
            unset($data['id']);
            $result = (bool)$account->folder()->where('id', $id)->update($data);
        } else {
            $result = (bool)$account->folder()->save($data);
        }

        if ($result) {
            return json2(true, "{$tips}成功");
        } else {
            return json2(false, "{$tips}失败");
        }
    }

    public function create()
    {
        $this->is_update = false;
        return $this->actionFolder();
    }

    public function update()
    {
        $this->is_update = true;
        return $this->actionFolder();
    }

    // 移动文件夹
    // 问题：未判断是否相同文件夹名（代码已改，未多次测试）
    public function move()
    {
        $account = $this->account;
        $id = request()->post('id/d', 0);
        $pid = request()->post('pid/d', 0);
        if (!$id || $id < 0 || $pid < 0 || $pid==$id) return json2(false, API_FAIL);

        $result = $account->folder()->find($id);
        if (!(bool)$result) return json2(false, API_FAIL, ['error' => '文件夹不存在']);


        // 判断位置是否有变动，没有变动就不做任何改变，提示操作成功
        if ($result->pid === $pid) return json2(true, API_SUCCESS);

        /* 文件夹名称冲突检测 */
        $name_parse = NetDiskFolderModel::parseFolderName($result->name);
        $same_name_arr = $account->folder()->where([
            'pid' => $pid,
            'main_name' => $name_parse['main_name']
        ])->column('id,index');
        if (count($same_name_arr) > 0) {
            if (in_array($name_parse['index'], $same_name_arr)) {
                return json2(false, API_FAIL . '！已存在相同文件夹名称');
            }
        }
        /* 文件夹名称冲突检测 */

        if ($pid != 0) {
            $parent = $account->folder()->find([
                'id' => $pid
            ]);
            if (empty($parent)) return json2(false, API_FAIL, ['error' => '目标文件夹不存在']);
//            return $result;
//            if ($result->parent_key && strpos($parent->parent_key, "{$result->parent_key}{$result->id}-") === 0) return json2(false, API_FAIL, ['error' => '父子节点重复嵌套']);
            if (strpos($parent->parent_key, "{$result->parent_key}{$result->id}-") === 0) return json2(false, API_FAIL, ['error' => '父子节点重复嵌套']);
            $target_parent_key = "{$parent->parent_key}{$pid}-";
        } else {
            $target_parent_key = '';
        }


        // 当前节点的parent_key
        $parent_key = $result->parent_key;

        // 子节点的parent_key
        $children_node = $account->folder()->where([
            'parent_key' => ['like', "{$parent_key}{$id}-%"]
        ])->column('id,parent_key');

        // 子节点的更新内容
        $children_update = [];
        foreach ($children_node as $_id => $_parent_key) {
            // $_parent_key -> 各个子节点的原父节点路径
            if (empty($parent_key)) {
                // 如果是最顶级节点，父节点路径为空，即最终生成的父节点路径为：目标父节点的自身的父节点路径+自身ID+各子节点的父节点路径（各子节点的的父节点路径已包含当前节点ID）
                $children_update[$_id] = $target_parent_key . $_parent_key;
            } else {
                $children_update[$_id] = str_replace($parent_key, $target_parent_key, $_parent_key);
            }
        }

        // 影响行数
        $rows = 0;
        foreach ($children_update as $key => $value) {
//            $update_model = NetDiskFolderModel::get($key);
            $update_model = NetDiskFolderModel::get([
                'id'=>$key,
                'account_id'=>$this->uid
            ]);
            $update_model->parent_key = $value;
            $rows += $update_model->save();
        }

//        $update_model = NetDiskFolderModel::get($id);
        $update_model = NetDiskFolderModel::get([
            'id'=>$id,
            'account_id'=>$this->uid
        ]);
        $update_model->pid = $pid;
//        $update_model->name = $update_name;
        $update_model->parent_key = $target_parent_key;
        $rows += $update_model->save();
        unset($update_model);

        if((bool)$rows){
            Cache::rm(self::DEL_KEY_REDIS.$id);
        }
        // 优化点：每次更新数据后影响行数===0时回退所有操作并返回API_FAIL
        return (bool)$rows ? json2(true, API_SUCCESS) : json2(false, API_FAIL);
    }

    public function userFolder()
    {
        $folders = AccountModel::find($this->uid)->getCurrentUserDiskFolder();
        return json2(true, '', ['uid' => $this->uid, 'folder' => $folders]);
    }

    // 申请删除文件夹
    public function requestDel(){
        $id = request()->post('id/d', 0);
        if (!$id) return json2(false, API_FAIL);
        $account = $this->account;
        $detail = $account->folder()->find($id);

        if(empty($detail)) return json2(false,'文件夹不存在');
        $delKey = cache(self::DEL_KEY_REDIS.$id);

        if($delKey){
            $lockData = de_lock($delKey);
            $lockData = json_decode($lockData,true);
            if(json_last_error()!==JSON_ERROR_NONE) $delKey = '';
        }

        if(!$delKey){
            // 是否有子文件夹
            $children_node = NetDiskFolderModel::hasChildren($detail);
            $lockData = ['has_child'=>(bool)$children_node,'children_node'=>$children_node,'uuid'=>uniqid(),'time'=>$_SERVER['REQUEST_TIME'],'id'=>$id];
            $delKey = lock(json_encode($lockData));
        }
        cache(self::DEL_KEY_REDIS.$id,$delKey,60);

        $lockData['delKey'] = $delKey;
        unset($lockData['uuid']);
        unset($lockData['time']);
        return json2(true,'',$lockData);
    }

    // 删除文件夹
    // 优化点：同步删除当前文件夹及子文件夹下的文件
    public function del()
    {
        $id = request()->post('id/d', 0);
        $delKey = request()->post('del_key/s', '');
        if (!$id || !$delKey) return json2(false, API_FAIL);
        $account = $this->account;
        $detail = $account->folder()->find($id);
        if(empty($detail)) return json2(false,'文件夹不存在');
        $cache = cache(self::DEL_KEY_REDIS.$id);
        if(!$cache || $cache!==$delKey) return json2(false, API_FAIL,[11,$cache]);

        $lockData = de_lock($delKey);
        $lockData = json_decode($lockData,true);
        if(json_last_error()!==JSON_ERROR_NONE || $lockData['id']!==$id) return json2(false, API_FAIL,[22]);

        $rows = $account->folder()->where([
            'parent_key'=>['like',"{$detail->parent_key}{$id}-%"]
        ])->whereOr([
            'id'=>$id
        ])->delete();
        Cache::rm(self::DEL_KEY_REDIS.$id);

        return (bool)$rows?json2(true,API_SUCCESS):json2(false,API_FAIL);
    }
}
