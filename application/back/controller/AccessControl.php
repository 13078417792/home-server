<?php

namespace app\back\controller;

use think\Controller;
use think\Request;

class AccessControl extends Base{

    public static $modules = [
        'tool'
    ];

    public function getModules(){
        return json2(true,'',['modules'=>self::$modules]);
    }

    protected function controllerFile(string $module){
        $path = APP_PATH.DS.$module;
        $controller_path = $path.DS.'controller';
        if(!$module || !is_dir($path) || !IS_DIR($controller_path)) return [];
        $files = scandir($controller_path);
        array_shift($files);
        array_shift($files);
        foreach($files as $key => &$file){
            if(!preg_match('/^[A-Z]{1}.+\.php$/',$file)){
                array_splice($files,$key,1);
                break;
            }
            if(strtolower($file)==='base.php'){
                array_splice($files,$key,1);
                continue;
            }
            $file = str_replace('.php','',$file);
        }
        return $files;

    }

    public function getControllers(){
        $module = request()->post('module/s','');
        $files = $this->controllerFile($module);
        return json2(true,'',['controllers'=>$files]);
    }

    protected function actionList(string $module,string $controller){
        if(!$module || !in_array($module,static::$modules) || !$controller) return [];
        if(!in_array($controller,$this->controllerFile($module))) return [];
        $path = realpath(APP_PATH.DS.$module.DS.'controller');
        $file = $path.DS.$controller.'.php';
        $class_name = "\app\\$module\\controller\\$controller";
        if(!is_file($file) || !class_exists($class_name)) return [];
        $base_class = "\app\\$module\\controller\\Base";
        if(!class_exists($base_class)){
            $base_class = '\think\\Controller';
        }
        // 父类的方法，需要过滤掉
        $base_methods = get_class_methods($base_class);



        // new一个反射
        $ref = new \ReflectionClass($class_name);
        $all_methods = $ref->getMethods(\ReflectionMethod::IS_PUBLIC);
        $methods = [];
        foreach($all_methods as $key => $method){
            if(!in_array($method->name,$base_methods) && !$method->isStatic()){
                $methods[] = $method->name;
            }
        }


        return $methods;
    }

    public function getActions(){
        $module = request()->post('module/s','');
        $controller = request()->post('controller/s','');
        $result = $this->actionList($module,$controller);
        return json2(true,'',['actions'=>$result,'module'=>$module,'controller'=>$controller]);
    }

    // 获取数据库中的操作
    public function getCurrentControlActions(){
        $module = request()->post('module/s','');
        $controller = request()->post('controller/s','');
        if(!$module || !$controller) return [];
        $actions = db('func_access_control')->where(['module'=>$module,'controller'=>$controller])->column('action');
        return json2(true,'',['current_actions'=>$actions]);
    }

    // 设置访问需要登录认证的方法
    public function setControl(){
        $module = request()->post('module/s',null);
        $controller = request()->post('controller/s',null);
        $actions = request()->post('actions/a',[]);
//        $actions = explode(',',$actions);

        if( !$module || !$controller || empty($actions) ||
            !in_array($module,self::$modules) || !in_array($controller,$this->controllerFile($module))
        ){
            return json2(false,'设置失败');
        }

        // 所有方法
        $actionsList = $this->actionList($module,$controller);

        // 找出不存在的方法
        $fail = array_values(array_diff($actions,$actionsList));

        // 过滤不存在的方法
        $actions = array_filter($actions,function($value) use($fail){
            return !in_array($value,$fail);
        });

        $condition = ['module'=>$module,'controller'=>$controller];

        $current_actions = db('func_access_control')->where($condition)->column('action');
        if(!empty($current_actions)){

            // 需要在数据库中移除的的方法
            $del_data = array_diff($current_actions,$actions);
            if(!empty($del_data)){
                db('func_access_control')->where(array_merge($condition,[
                    'action'=>['in',$del_data]
                ]))->delete();
            }
            // 需要在数据库中添加的方法
            $actions = array_filter($actions,function($value) use($current_actions){
                return !in_array($value,$current_actions);
            });
        }
        $data = [];
        foreach($actions as $action){
            $data[] = array_merge($condition,['action'=>$action]);
        }
        if(!empty($data)){
            db('func_access_control')->insertAll($data);
        }


        return json2(true,'成功');



    }
}
