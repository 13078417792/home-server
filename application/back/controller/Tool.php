<?php

namespace app\back\controller;

use think\Controller;
use think\Exception;
use think\Request;

class Tool extends Base{

    public function addFileExtInfo(){
        $data = [
            'ext'=>request()->post('ext/s',''),
            'header'=>request()->post('header/s',''),
            'header_index'=>request()->post('header_index/d',0),
            'end'=>request()->post('end/s',''),
            'description'=>request()->post('description/s',''),
        ];
        $data['ext'] = preg_replace('/\s*/','',$data['ext']);

        // 数组用英文逗号连接的字符串
        $data['header'] = strtoupper(preg_replace('/[^a-fA-F0-9,]*/','',$data['header']));
        $data['end'] = strtoupper(preg_replace('/[^a-fA-F0-9,]*/','',$data['end']));

        $data['header_hex'] = $data['header'];
        $data['end_hex'] = $data['end'];

        $data['ext'] = strtolower($data['ext']);
//        dd($data['header']);
        $data['header'] = $data['header']?explode(',',$data['header']):[];
        $data['end'] = $data['end']?explode(',',$data['end']):[];

        $validate = validate('FileExtInfo');
        if(!$validate->check($data)){
            return json2(false,'操作失败',['error'=>$validate->getError()]);
        }
//        dd($data['header']);

        foreach($data['header'] as $key => &$value){
            try{
                $value = hexdec($value);
            }catch(Exception $e){
                return json2(false,'操作失败',['error'=>$e->getMessage()]);
            }

        }

//        dd($data['header']);

        foreach($data['end'] as $key => &$value){
            try{
                $value = hexdec($value);
            }catch(Exception $e){
                return json2(false,'操作失败',['error'=>$e->getMessage()]);
            }
        }

        $data['header'] = implode(',',$data['header']);
        $data['end'] = implode(',',$data['end']);


//        return json2(true,'操作成功',[$data]);
        $rows = db('file_ext_info')->insert($data);
        if($rows){
            return json2(true,'操作成功',[$data]);
        }else{
            return json2(false,'操作失败');
        }

    }

    public function getFileExtInfo(){
        $info = db('file_ext_info')->select();
        return json2(true,'',['info'=>$info]);
    }

    public function deleteFileExtInfo(){
        $id = request()->post('id');
        if(!$id){
            return json2(false,'删除失败');
        }
        $row = db('file_ext_info')->delete($id);
        return  $row?json2(true,'删除成功',['delete_id'=>$id]):json2(false,'删除失败');
    }

    public function getFileExtDetail(){
        $id = request()->post('id');
        $result = db('file_ext_info')->where('id',$id)->find();
        return empty($result)?json2(false,'不存在'):json2(true,'',['detail'=>$result,'id'=>$id]);
    }

    public function editFileExtInfo(){
        $id = request()->post('id/d',0);

        if(!$id){
            return json2(false,'修改失败');
        }

        $result = db('file_ext_info')->where('id',$id)->find();
        if(empty($result)){
            return json2(false,'修改失败');
        }

        $data = [
            'ext'=>request()->post('ext/s',''),
            'header'=>request()->post('header/s',''),
            'header_index'=>request()->post('header_index/d',0),
            'end'=>request()->post('end/s',''),
            'description'=>request()->post('description/s','')
        ];
        $data['ext'] = preg_replace('/\s*/','',$data['ext']);

        // 数组用英文逗号连接的字符串
        $data['header'] = strtoupper(preg_replace('/[^a-fA-F0-9,]*/','',$data['header']));
        $data['end'] = strtoupper(preg_replace('/[^a-fA-F0-9,]*/','',$data['end']));

        $data['header_hex'] = $data['header'];
        $data['end_hex'] = $data['end'];

        $data['ext'] = strtolower($data['ext']);
//        dd($data['header']);
        $data['header'] = $data['header']?explode(',',$data['header']):[];
        $data['end'] = $data['end']?explode(',',$data['end']):[];

        $validate = validate('FileExtInfo');
        if(!$validate->check($data)){
            return json2(false,'操作失败',['error'=>$validate->getError()]);
        }
//        dd($data['header']);

        foreach($data['header'] as $key => &$value){
            try{
                $value = hexdec($value);
            }catch(Exception $e){
                return json2(false,'操作失败',['error'=>$e->getMessage()]);
            }

        }

//        dd($data['header']);

        foreach($data['end'] as $key => &$value){
            try{
                $value = hexdec($value);
            }catch(Exception $e){
                return json2(false,'操作失败',['error'=>$e->getMessage()]);
            }
        }

        $data['header'] = implode(',',$data['header']);
        $data['end'] = implode(',',$data['end']);


        $rows = db('file_ext_info')->where('id',$id)->update($data);
        return json2(true,'操作成功');
        if($rows){
            return json2(true,'操作成功');
        }else{
            return json2(false,'操作失败');
        }
    }
}
