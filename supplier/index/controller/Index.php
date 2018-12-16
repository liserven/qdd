<?php
/**
 * Created by 赵晓凡
 * User: zhaoxiaofan
 * Date: 2017/2/17
 * Time: 9:30
 */
namespace supplier\index\controller;

/**
 * Class Index
 * @package home\index\controller
 * @return 商家欢迎页面
 */
class Index extends Auth
{
    public function index()
    {
    	return $this->view->fetch();
    }
    public function welcome(){
    	return $this->view->fetch();
    }  
   
}
