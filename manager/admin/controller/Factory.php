<?php
/**
 * Created by 赵晓凡
 * User: zhaoxiaofan
 * Date: 2017/5/2
 * Time: 8:53
 */

namespace app\admin\controller;

use api\index\controller\AccountRecordAction;
use api\index\controller\ProductPhoto;
use api\index\controller\User;


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
            require $_SERVER['DOCUMENT_ROOT']  .'/api/index/controller/ActionInterface.php';
            require $_SERVER['DOCUMENT_ROOT'] .'/api/index/controller/AccountRecordAction.php';
            return new AccountRecordAction();
        }elseif ($name=='productphoto'){
            require $_SERVER['DOCUMENT_ROOT']  .'/api/index/controller/Auth.php';
            require $_SERVER['DOCUMENT_ROOT']  .'/api/index/controller/ActionInterface.php';
            require $_SERVER['DOCUMENT_ROOT'] .'/api/index/controller/ProductPhoto.php';
            return new ProductPhoto();
        }
    }
}