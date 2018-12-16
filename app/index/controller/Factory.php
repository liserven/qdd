<?php
/**
 * Created by
 * User:
 * Date: 2018/3/12
 * Time: 8:53
 */

namespace app\index\controller;

/**
 * Class Factory
 * 工厂模式：产生对象实例
 * @package app\index\controller
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
        }
    }
}