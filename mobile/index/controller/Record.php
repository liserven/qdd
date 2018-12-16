<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/17
 * Time: 10:18
 */

namespace mobile\index\controller;


class Record  extends  Common
{
//    购物币记录
    public  function  accountrecord()
    {

        $param='';
        if($this->request->param('flowtype')){
            $param.='flowtype='.$this->request->param('flowtype').'&';
        }
        if($this->request->param('UserFrom')){
            $param.='UserFrom='.$this->request->param('UserFrom').'&';
        }
        //根据日期进行查找
        if($this->request->param("datemin") and $this->request->param("datemax")){
            $param.='datemin='.$this->request->param('datemin').'&';
            $param.='datemax='.$this->request->param('datemax').'&';

        }elseif($this->request->param("datemin")){
            $param.='datemin='.$this->request->param('datemin').'&';
        }elseif($this->request->param("datemax")){
            $param.='datemax='.$this->request->param('datemax').'&';
        }

        $this->view->assign('param',$param);
        return $this->view->fetch('accountrecord');

    }
    /**
     * 购物币记录详情
     * @return mixed
     */
    public function account_show(){
        return $this->view->fetch('account_show');
    }

    /**
     * 交易记录菜单
     * @return mixed
     */
    public function record_menu(){
        return $this->view->fetch('record_menu');
    }

    /**会员积分记录列表
     * @return mixed
     */
    public function integral_record(){
        $param='';
        if($this->request->param('typename')){
            $param.='typename='.$this->request->param('typename').'&';
        }
        if($this->request->param('datemin')){
            $param.='datemin='.$this->request->param('datemin').'&';
        }
        if($this->request->param('datemax')){
            $param.='datemax='.$this->request->param('datemax').'&';
        }
        $this->view->assign('param',$param);
        return $this->view->fetch('integral_record');
    }
    /**奖金记录列表
     * @return mixed
     */
    public function prize_list(){
        $param='';
        if($this->request->param('datemin')){
            $param.='datemin='.$this->request->param('datemin').'&';
        }
        if($this->request->param('datemax')){
            $param.='datemax='.$this->request->param('datemax').'&';
        }
        $this->view->assign('param',$param);
        return $this->view->fetch('prize_list');
    }

    /**会员积分详情列表
     * @return mixed
     */
    public function integral_detail(){
        return $this->view->fetch('integral_detail');
    }
    /**会员奖金详情列表
     * @return mixed
     */
    public function prize_detail(){
        return $this->view->fetch('prize_detail');
    }
}
