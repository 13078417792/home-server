<?php
/**
 * Created by PhpStorm.
 * User: 39720
 * Date: 2018/10/6
 * Time: 20:33
 */

class tree{

    public $data = [];

    protected $idKey = 'id';
    protected $pidKey = 'pid';
    protected $separate = '';
    protected $separateKey = '';
    protected $childKey = 'child';

    protected $result = [];

    public function __construct(array $data,array $config=[]){
        $this->data = $data;
        foreach($config as $key => $value){
            $this->$key = $value;
        }
    }

    // 处理成树形
    public function create($pid=0){
        $this->result = $this->handle($this->data,$pid);
        return $this;
    }

    protected function handle($data,$pid=0,$level=1){
        $result = [];
        foreach($data as $key => $value){
            if($value[$this->pidKey]==$pid){
                $temp = $value;

                if($this->separate && !empty($this->separateKey)){
                    if(gettype($this->separateKey)==='string'){
                        $temp[$this->separateKey] = (str_repeat($this->separate,$level-1)).$temp[$this->separateKey];
                    }elseif(gettype($this->separateKey)==='array'){
                        foreach($this->separateKey as $sub){
                            $temp[$sub] = (str_repeat($this->separate,$level-1)).$temp[$sub];
                        }
                    }
                }

                $children = $this->handle($data,$value[$this->idKey],$level+1);
                if(!empty($children)){
                    $temp[$this->childKey] = $children;
                }
                $result[] = $temp;
            }
        }
        return $result;
    }

    public function result(){
        return $this->result;
    }
}