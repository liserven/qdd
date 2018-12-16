<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/19
 * Time: 11:07
 */

namespace app\admin\controller;
\think\Loader::import('controller/Jump', TRAIT_PATH, EXT);

use app\admin\Controller;
use think\Db;

class RecommendTreeChart extends Controller
{
    public function index(){
        $userid=urldecode($this->request->param('userid')) ;
        if (empty($userid) || $userid == '' || $userid == 'index.php'){
            $userid='00001';
        }
        return $this->recommend_tree($userid);//只显示一级
//        return $this->recommend_tree_new($userid);//显示三级，可以使用
    }
    public function recommend_tree($userid){

        $where['ReferrerID']=$userid;
        $list=Db::name('usermsg')->field('UserId,TrueName')->where($where)->order('ID')->select();
        if($userid!=00001){
            $where1['UserId']=$userid;
            $ReferrerID=Db::name('usermsg')->field('ReferrerID')->where($where1)->find();
            $this->view->assign('referrerid',$ReferrerID['ReferrerID']);
            if(empty($ReferrerID)){
                $userid="";
            }
        }else{
            $this->view->assign('referrerid','');
        }


        $this->view->assign('list',$list);
        $this->view->assign('first',$userid);
        $first_name=Db::name('usermsg')->field('TrueName')->where(array('UserId'=>$userid))->find();
        $this->view->assign('first_name',$first_name['TrueName']);

        return $this->view->fetch('user_network/recommend_tree1');
    }

    public function recommend_tree_new($userid){
        $where['pid']=$userid;
        $where['lay']=1;
        $yiceng=Db::name('userrecommenddiagram')->where($where)->select();
        $list=array();
        foreach($yiceng as $k=>$v){
            $yiceng[$k]['TrueName']=$this->truename($v['userId']);
            $where2['pid']=$v['userId'];
            $where2['lay']=1;
            $erceng=Db::name('userrecommenddiagram')->field('userId')->where($where2)->select();
            foreach ($erceng as $k2=>$v2){
                $erceng[$k2]['TrueName']=$this->truename($v2['userId']);
                $where3['pid']=$v2['userId'];
                $where3['lay']=1;
                $sanceng=Db::name('userrecommenddiagram')->field('userId')->where($where3)->select();
                foreach ($sanceng as $k3=>$v3){
                    $sanceng[$k3]['TrueName']=$this->truename($v3['userId']);
                }
                $erceng[$k2]['erji']=$sanceng;
            }
            $yiceng[$k]['xiaji']=$erceng;
            $list=$yiceng;
        }
        //查询条件
        if($userid!=00001){
            $this->view->assign('referrerid',$userid);
        }else{
            $this->view->assign('referrerid','');
        }

        $this->view->assign('fu_id',$userid);//父级id
        $this->view->assign('fu_name',$this->truename($userid));

        $this->view->assign('list',$list);
        return $this->view->fetch('user_network/recommend_tree_new');
    }

    public function truename($userid){
        $first_name=Db::name('usermsg')->field('TrueName')->where(array('UserId'=>$userid))->find();
        return $first_name['TrueName'];
    }

}