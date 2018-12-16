<?php
/**
 * Created by 赵晓凡
 * User: zhaoxiaofan
 * Date: 2017/2/17
 * Time: 14:30
 */
namespace mobile\index\controller;

use think\View;
use think\Config;
use think\Request;

/**
 * Class Common 公共基类
 * @package mobile\index\controller
 */
class Common
{
    // 视图类实例
    protected $view;
    // Request实例
    protected $request;

    /**
     * Common constructor.
     * 获取并赋值视图和请求类的实例
     */
    public function __construct()
    {
        if (null === $this->view) {
            $this->view = View::instance(Config::get('template'), Config::get('view_replace_str'));
        }
        if (null === $this->request) {
            $this->request = Request::instance();
        }
    }
}