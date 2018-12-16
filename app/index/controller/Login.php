<?php
/**
 * Created by
 * User: zhaoxiaofan
 * Date: 2018/3/8
 * Time: 14:09
 */
namespace app\index\controller;

use think\Request;
use think\Session;
use think\Db;
/**
 * Class Login
 * @package home\index\controller
 * @return 
 */
class Login
{
    /**
     * app端登录验证接口
     * 请求方式：http://www.XXX.com/login/check
     * 请求参数：
     * @param   username 用户名
     * @param   password  密码
     * 请求方式：post
     * @return status:1 成功 0 失败     msg:提示信息
     */
	public function check(){
        if(Request::instance()->isPost()){
            $data=Request::instance()->post();
            if(empty($data['username'])){
                return json(['status'=>1,'msg'=>'用户名不能为空']);
            }
            if(empty($data['password'])){
                return json(['status'=>2,'msg'=>'密码不能为空']);
            }
            $where['UserId']=$data['username'];
            $where['Password']=md5($data['password']);
            $userinfo=Db::name('usermsg')->field('UserId as userid,Nickname as nickname')->where($where)->find();
            if(!empty($userinfo)){
                $user=Db::name('token')->where('User_Id='.$userinfo['userid'])->find();
                if($user){
                    $token['token']=$updata['Token']=md5($userinfo['userid'].$userinfo['nickname'].time());
                    $updata['AddTime']=time();
                    Db::name('token')->where('User_Id='.$userinfo['userid'])->update($updata);
                }else{
                    $innerdata['User_Id']=$userinfo['userid'];
                    $token['token']=$innerdata['Token']=md5($userinfo['userid'].$userinfo['nickname'].time());
                    $innerdata['AddTime']=time();
                    Db::name('token')->insert($innerdata);
                }
                return json(['data'=>$token,'status'=>0,'msg'=>'登录成功']);
            }else{
                return json(['status'=>3,'msg'=>'用户名或者密码错误']);
            }
        }else{
            return json(['status'=>4,'msg'=>'请求方式错误']);
        }
	}

}
