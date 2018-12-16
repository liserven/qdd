<?php

/**

 * Created by PhpStorm.

 * User: Administrator

 * Date: 2017/5/19

 * Time: 10:46

 */



namespace mobile\index\controller;



use think\Db;

class UserReg  extends Common

{

    /**
     * 会员注册
     * @return mixed
     */

    public function user_reg(){

        $introduceId = $this->request->param('introduceId');
        if(empty($introduceId))
        {
            $introduceId='00001';
        }
        else{
            $introduceId=$this->request->param('introduceId');
        }
        $where['UserId']=$introduceId;
        $username=Db::name('usermsg')->where($where)->field('TrueName')->find();
        $this->view->assign("introduceId",$introduceId);
        $this->view->assign('username',$username);
        return $this->view->fetch('member/user_reg');
    }
    /**
     * 忘记密码手机号验证
     * @return mixed
     */
    public function security_code(){

        return $this->view->fetch('member/security_code');

    }

    /**
     * 重置密码
     * @return mixed
     */
    public function reset_password(){

        return $this->view->fetch('member/reset_password');

    }

}

