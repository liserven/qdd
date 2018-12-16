<?php
namespace mobile\index\widget;
use think\Db;
use think\View;
use think\Config;
use think\Request;

/**
 * 该插件实现二级菜单渲染模板的内容输出
 */
header("Content-type: text/html; charset=utf-8");
class Common{
    // 视图类实例
    protected $view;
    // Request实例
    protected $request;

    public function __construct()
    {
        if (null === $this->view) {
            $this->view = View::instance(Config::get('template'), Config::get('view_replace_str'));
        }
        if (null === $this->request) {
            $this->request = Request::instance();
        }
    }


    public function mobile_header()
    {
        $ishit=$this->request->param('ishit');
        if(empty($ishit)){
            $this->view->assign('title','首页');
        }else{
            $title=['1'=>'创客空间','2'=>'劲爆抢购','4'=>'商城优品','6'=>'积分兑购'];
            $this->view->assign('title',$title[$ishit]);
        }
        $adlist=indexToLower(Db::name('adlist')->where('CategoryId=3')->order('ID')->select());
        $this->view->assign('adlist',$adlist);
        return $this->view->fetch('public/mobile_header');
    }

    public function mobile_footer()
    {
        return $this->view->fetch('public/mobile_footer');
    }

    public function login_auth(){
        return $this->view->fetch('public/login_auth');
    }


}
