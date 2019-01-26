<?php

namespace app\tool\model;

use think\Model;

class UserNetDisk extends Model{

    /** 关联模型 */
    public function disk(){
        return $this->hasOne('NetDiskFile','file_id');
    }
    /** 关联模型 */

}
