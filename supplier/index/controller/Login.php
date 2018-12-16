<?php
/**
 * Created by 赵晓凡
 * User: zhaoxiaofan
 * Date: 2017/2/17
 * Time: 9:30
 */
namespace supplier\index\controller;
\think\Loader::import('controller/Jump', TRAIT_PATH, EXT);

use think\Db;
use think\Request;
use think\View;
use think\Config;
use think\captcha\Captcha;
use think\Session;
use think\Response;

/**
 * Class Login
 * @package supplier\index\controller
 * @return 登录页面
 */
class Login
{
	use \traits\controller\Jump;//使用trait复用Jump中的方法
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
	
	/**
	 * 登录验证
	 * 
	 */
    public function index()
    {
    	if($this->request->isPost()){
    		$submitData=$this->request->post();
    		if(captcha_check($submitData['captcha'])){
    			$where['ID']=$submitData['adminname'];
    			$where['LoginPasswd']=md5($submitData['password']);
    			$supplierInfo = Db::name('supplier')->where($where)->field("ID,Name,IsAudit")->find();
    			if($supplierInfo){
    				if($supplierInfo["IsAudit"]==0){
    					//如果供应商正在合作中，可以登录
    					Session::set('supplierid',$supplierInfo["ID"]);
    					Session::set('suppliername',$supplierInfo["Name"]);   					 
    					return json(['status'=>1,'msg'=>'登录成功!']);    					 
    				}else{
    					//供应商已被取消合作，账户被停用
    					return json(['status'=>0,'msg'=>'登录失败,您的账户已被停用!']);
    				}
    			}else{
    				return json(['status'=>0,'msg'=>'用户名或者密码错误!']);
    			}
    			
    		}else{
    			return json(['status'=>0,'msg'=>'验证码错误!']);
    		}
    	}
    	return $this->view->fetch();
    }  

    /**
     * 输出验证码
     * @return \think\Response
     */
    
    public function captcha(){
    	$config = [
    	'fontSize' => 20,
    	// 验证码字体大小(px)
    	'useCurve' => false,
    	// 是否画混淆曲线
    	'useNoise' => true,
    	// 是否添加杂点
    	'imageH'   => 40,
    	// 验证码图片高度
    	'imageW'   => 150,
    	// 验证码图片宽度
    	'length'   => 4,
    	// 验证码位数
    	'bg'       => [243, 251, 254],
    	// 背景颜色
    	'reset'    => true,
    	// 验证成功后是否重置
    	];
    	$captcha= new Captcha($config);
    	return $captcha->entry();
    }
    
    /**
     * 退出登录
     */
    public function loginOut(){
    	if(Session::get('suppliername')){
    		Session::clear();
    	}
    	$this->success('退出登录',url('Login/index'));
    }
}
