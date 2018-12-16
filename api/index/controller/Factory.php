<?php
/**
 * Created by 赵晓凡
 * User: zhaoxiaofan
 * Date: 2017/5/2
 * Time: 8:53
 */

namespace api\index\controller;

/**
 * Class Factory
 * 工厂模式：产生对象实例
 * @package api\index\controller
 */
class Factory
{
    private static $instance=null;

    private function __construct()
    {

    }
    public static function instance(){
        if(self::$instance==null){
            self::$instance=new self();
        }
        return self::$instance;
    }

    public function getObjectInstance($name){
        if($name=='account'){
            return new AccountRecordAction();
        }elseif($name=='user'){
            return new User();
        }elseif($name=='settle'){
            return new UserSettlement();
        }elseif ($name=='productphoto'){
            return new ProductPhoto();
        }
    }
}