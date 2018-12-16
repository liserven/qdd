<?php
/**
 * Created by 赵晓凡
 * User: zhaoxiaofan
 * Date: 2017/2/17
 * Time: 9:30
 */
namespace supplier\index\controller;
\think\Loader::import('controller/Jump', TRAIT_PATH, EXT);

use think\Request;
use think\View;
use think\Config;
use think\Session;



class Auth
{
	use \traits\controller\Jump;//使用trait复用Jump中的方法
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
		
		if(!Session::get('suppliername'))
		{
			$this->redirect(url('Login/index'));
		}
	}
		
}
