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
    protected $statusKey = 'status';
    protected $statusConvertBoolean = true; // 状态值自动转换布尔值

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
                if($this->statusConvertBoolean){
                    $temp[$this->statusKey] = !!$temp[$this->statusKey];
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

    public function getDeepChildren(array $data=[]){
        if(empty($data)){
            $data = $this->data;
        }
        if(count($data)>1){
            $data = $data[0];
        }
        foreach($data as $key => $value){
            if(!empty($value[$this->childKey]) && is_array($value[$this->childKey])){
                $this->result = $this->getDeepChildren($value[$this->childKey])->result();
                return $this;
            }else{
                $this->result = $value;
                return $this;
            }
        }
        return $this;
    }
}