<?php

/**

 * Created by 赵晓凡

 * User: zhaoxiaofan

 * Date: 2017/2/18

 * Time: 9:30

 */

namespace api\index\controller;



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

     * pc端登录验证接口

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

                return json(['data'=>array(),'status'=>0,'msg'=>'用户名不能为空']);

            }else{

                Session::set('membername',$data['username']);

                return json(['status'=>1,'msg'=>'登录成功']);

            }

        }else{

            return json(['data'=>array(),'status'=>0,'msg'=>'请求方式错误']);

        }

	}



    /**

     * pc端判断登录状态的接口

     * 请求方式：http://www.XXX.com/login/auth

     * 请求参数：无

     * @return  @return status 1 成功 0 失败        msg 提示信息

     */

	

	public function auth(){

		if(Session::get('membername')){

			$data=['status'=>1,'username'=>Session::get('membername'),'msg'=>'已登录'];

		}else{

			$data=['status'=>0,'msg'=>'未登录'];

		}

		return json($data);

	}





    /**

     * 移动端登录验证接口

     * 请求方式：http://www.XXX.com/login/mobile_check

     * 请求参数：

     * @param   username 用户名

     * @param   password  密码

     * 请求方式：post

     * @return status:1 成功 0 失败     msg:提示信息

     */

    public function mobile_check(){
        if(Request::instance()->isPost()){

            $data=Request::instance()->post();

            if(empty($data['username'])){

                return json(['data'=>array(),'status'=>0,'msg'=>'用户名不能为空']);

            }

            if(empty($data['password'])){

                return json(['data'=>array(),'status'=>0,'msg'=>'密码不能为空']);

            }

            $userinfo=Db::query('select IsAudit, UserId as userid,ID as id from usermsg where UserId=:userid and Password=:password limit 0,1',['userid'=>$data['username'],'password'=>md5($data['password'])]);

            if(!empty($userinfo)){


                // 判断用户当前状态
                
                // 判断用户是否已填写资料
                
                if($userinfo[0]['IsAudit'] == 2){
                     return json(['url_path'=>2,'status'=>0,'msg'=>'您的账号还未审核，请联系管理员审核！']);
                }
                Session::set('membername',$userinfo[0]['userid']);

                Session::set('memberid',$userinfo[0]['id']);
                if($userinfo[0]['IsAudit'] == 0){
                     return json(['url_path'=>1,'status'=>0,'msg'=>'请先填写认证资料！']);
                }
                if($userinfo[0]['IsAudit'] == 4){
                     return json(['url_path'=>1,'status'=>0,'msg'=>'您填写的资料审核未通过，请修改后重新提交！']);
                }

                return json(['data'=>'','status'=>1,'msg'=>'登录成功']);

            }else{

                return json(['data'=>'','status'=>0,'msg'=>'用户名或者密码错误']);

            }

        }else{

            return json(['data'=>'','status'=>0,'msg'=>'请求方式错误']);

        }

    }



    /**

     * 移动端判断登录状态的接口

     * 请求方式：http://www.XXX.com/login/mobile_auth

     * 请求参数：无

     * @return  @return status 1 成功 0 失败        msg 提示信息

     */



    public function mobile_auth(){

        if(Session::get('username')){

            $data=['status'=>1,'username'=>Session::get('username'),'msg'=>'已登录'];

        }else{

            $data=['status'=>0,'msg'=>'未登录'];

        }

        return json($data);

    }



    /**

     * 登录退出

     * 请求方式：http://www.XXX.com/login/exit

     * 请求参数：无

     * @return  @return status 1 成功 0 失败        msg 提示信息

     */

    public function login_exit(){

        $data = ['data'=>array(),'status'=>0,'msg'=>'未登录'];

        if(Session::get('membername')||Session::get('username')){

            Session::clear();

            $data=['data'=>array(),'status'=>1,'msg'=>'登出成功'];

        }

        return json($data);

    }

}

