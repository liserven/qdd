<?php/** * Created by 赵晓凡 * User: zhaoxiaofan * Date: 2017/2/17 * Time: 14:30 */namespace api\index\controller;use think\Request;use think\Session;/** * Class Auth 公共认证基类 * @package api\index\controller */class Auth{    // Request实例    protected $request;        protected $loginStatus=false;    protected  $username='';    /**     * Auth constructor.     * 获取并赋值视图和请求类的实例     */    public function __construct()    {        if (null === $this->request) {            $this->request = Request::instance();        }        if(Session::get('membername')){    		$this->loginStatus=true;    		$this->username=Session::get('membername');    	}    }}