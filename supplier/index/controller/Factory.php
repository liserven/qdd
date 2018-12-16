<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/6/20
 * Time: 9:39
 */

namespace supplier\index\controller;

use api\index\controller\ProductPhoto;
use api\index\controller\Login;

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
        if($name=='productphoto'){
            require $_SERVER['DOCUMENT_ROOT']  .'/api/index/controller/Auth.php';
            require $_SERVER['DOCUMENT_ROOT']  .'/api/index/controller/ActionInterface.php';
            require $_SERVER['DOCUMENT_ROOT'] .'/api/index/controller/ProductPhoto.php';
            return new ProductPhoto();
        }elseif($name=='Login'){
            require $_SERVER['DOCUMENT_ROOT']  .'/api/index/controller/Auth.php';
            require $_SERVER['DOCUMENT_ROOT']  .'/api/index/controller/ActionInterface.php';
            require $_SERVER['DOCUMENT_ROOT'] .'/api/index/controller/Login.php';
            return new Login();
        }
    }

}