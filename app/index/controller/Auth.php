<?php
/**
 * Created by 赵晓凡
 * User: zhaoxiaofan
 * Date: 2017/2/17
 * Time: 14:30
 */
namespace app\index\controller;

use think\Request;
use think\Session;
use think\Db;

/**
 * Class Auth 公共认证基类
 * @package api\index\controller
 */
class Auth
{
    // Request实例
    protected $request;
    
    protected $loginStatus=false;

    protected  $username='';

    protected  $secretKey = '123456';

    /**
     * Auth constructor.
     * 获取并赋值视图和请求类的实例
     */
    public function __construct()
    {
        if (null === $this->request) {
            $this->request = Request::instance();
        }
        if(Session::get('membername')){
    		$this->loginStatus=true;
    		$this->username=Session::get('membername');
    	}
    }
    /*用户登录验证
     * */
    public function islogin(){
        if(isset($_COOKIE['token'])){
            $token=$_COOKIE['token'];
            $where['Token']=$token;
//        $where['Token']='383267ed5b6aa0357f0115e48dd57b94';
            $userid=Db::name('token')->field('User_Id')->where($where)->find();
            if($userid){
                $wherenow['UserId']=$userid['User_Id'];
                $userinfo=Db::name('usermsg')->where($wherenow)->find();
                if($userinfo){
                    $returnData['data']=$userinfo;
                    $returnData['status']=0;
                    $returnData['msg']='成功';
                }else{

                    $returnData['status']=3;
                    $returnData['msg']='用户不存在';
                }

            }else{

                $returnData['status']=1;
                $returnData['msg']='不存在';
            }
        }else{

            $returnData['status']=2;
            $returnData['msg']='不存在';
        }
        return $returnData;
    }
    /*商家登录验证
     * */
    public function supplierislogin(){
        if(isset($_COOKIE['token'])){
            $token=$_COOKIE['token'];
            $where['Token']=$token;
            $userid=Db::name('token')->field('User_Id')->where($where)->find();
            if($userid){
                $wherenow['UserId']=$userid['User_Id'];
                $userinfo=Db::view('usermsg')
                    ->view('supplier','ID','suppier.usermasg_id=usermsg.ID')
                    ->where($wherenow)
                    ->find();
                if($userinfo){
                    $returnData['data']=$userinfo;
                    $returnData['status']=0;
                    $returnData['msg']='成功';
                }else{

                    $returnData['status']=3;
                    $returnData['msg']='该用户未开通商家';
                }

            }else{
                $returnData['status']=1;
                $returnData['msg']='会员不存在';
            }
        }else{

            $returnData['status']=2;
            $returnData['msg']='未登录';
        }
        return $returnData;
    }
}