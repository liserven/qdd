<?php
/**
 * Created by 赵晓凡
 * User: zhaoxiaofan
 * Date: 2017/2/17
 * Time: 9:30
 */
namespace mobile\index\controller;
\think\Loader::import('controller/Jump', TRAIT_PATH, EXT);

use think\Db;
use think\Session;
use think\Config;

/**
 * Class Index
 * @package mobile\index\controller
 * @return 首页页面
 */
class Index extends Common
{
    use \traits\controller\Jump;
    /**
     * 首页
     * @return mixed
     */
    public function index(){
        $banner=Db::name('adlist')->where('CategoryId=1')->order('sort asc')->select();
        if($banner){
            foreach ($banner as $k=>$v){
                $datas['banner'][$k]['poster_name']=$v['AdTitle'];
                $datas['banner'][$k]['poster_url']=$v['AdLinkUrl'];
                $datas['banner'][$k]['poster_picpath']=$v['AdPicture'];
            }
        }else{
            $datas['banner']=[];
        }

        $fast=Db::name('adlist')->where('CategoryId=2')->order('sort asc')->select();
        if($fast){
            foreach ($fast as $kk=>$vv){
                $datas['gcategory'][$kk]['cate_name']=$vv['AdTitle'];
                $datas['gcategory'][$kk]['poster_url']=$vv['AdLinkUrl'];
                $datas['gcategory'][$kk]['poster_picpath']=$vv['AdPicture'];
            }
        }else{
            $datas['gcategory']=[];
        }
        $recondgoods=Db::name('product')->field('ProId,MarketPrice,EnjoyPrice,VipPrice,ProName,ProImg')->where('IsOnSell=1 and IsIndex=1 and IsHit=1')->limit(5)->order('sort asc,ProId desc')->select();
        if($recondgoods){
            foreach ($recondgoods as $key=>$val){
                if(strpos($val['ProImg'],'http://')!==false){
                    $recondgoods[$key]['ProImg']=$val['ProImg'];
                }else{
                    $recondgoods[$key]['ProImg']=config('IMAGE_DOMAIN_NAME').'/public/Upload/cpimg/'.$val['ProImg'];
                }
            }
        }
//        $hitgoods=Db::name('product')->field('ProId,MarketPrice,EnjoyPrice,VipPrice,ProName,ProImg')->where('IsOnSell=1 and IsHit=1')->limit(10)->order('sort asc,prosum desc,ProId desc')->select();
//        if($hitgoods){
//            foreach ($hitgoods as $key=>$val){
//                if(strpos($val['ProImg'],'http://')!==false){
//                    $hitgoods[$key]['ProImg']=$val['ProImg'];
//                }else{
//                    $hitgoods[$key]['ProImg']=config('IMAGE_DOMAIN_NAME').'/public/Upload/cpimg/'.$val['ProImg'];
//                }
//            }
//        }
        $this->view->assign('recondgoods',$recondgoods);
//        $this->view->assign('hitgoods',$hitgoods);
        $this->view->assign('banner',$datas['banner']);
        $this->view->assign('gcategory',$datas['gcategory']);
        return $this->view->fetch();
    }
    public function index1(){
//        var_dump(Session::get('ishit'));exit;
        $url = config('API_DOMAIN_NAME').'/api.php/index/all?ishit='.Session::get('ishit');//调用充值/扣币的接口
//        var_dump($url);exit;
		$result = http_request($url);
        $returnData=json_decode($result,true);
//        var_dump($returnData['data']['recommend']);exit;
        if($returnData['status'] == "1") {
            //首页轮播图
           // var_dump($returnData['data']['lianjie']);exit;
         $this->view->assign('lianjie', $returnData['data']['lianjie']);
           $this->view->assign('guohe', $returnData['data']['guohe']);
            $this->view->assign('rexiao', $returnData['data']['rexiao']);
            $this->view->assign('youxuan', $returnData['data']['youxuan']);
            $this->view->assign('dongtai', $returnData['data']['dongtai']);
            $this->view->assign('xianchang', $returnData['data']['xianchang']);
            $this->view->assign('xinde', $returnData['data']['xinde']);
            $this->view->assign('adlist', $returnData['data']['banner']);//详情页图片合集轮播
            $this->view->assign('adshishang', $returnData['data']['shishang']);//详情页图片合集轮播
            $this->view->assign('dapai', $returnData['data']['dapai']);//详情页图片合集轮播
            $this->view->assign('quanqiu', $returnData['data']['quanqiu']);//详情页图片合集轮播
            $this->view->assign('dashang', $returnData['data']['dashang']);//详情页图片合集轮播
            $this->view->assign('shangxin', $returnData['data']['shangxin']);//详情页图片合集轮播
            $this->view->assign('indexnav', $returnData['data']['indexnav']);//快速入口
            $this->view->assign('recommend', $returnData['data']['recommend']);//首页展示商品
//            print_r($returnData['data']['shishang']);exit;
          $this->view->assign('name',Session::get('membername'));
            return $this->view->fetch('index');
        }else{
            echo $returnData['msg'];
//            return $this->view->fetch('index');
        }
    }

    /*
     * 登录注册页
     * @return mixed
     */
    public function login_reg(){
        return $this->view->fetch('login_reg');
    }


    /**
     * 登录页
     * @return mixed
     */
    public function login(){
        if(Session::get('membername')){
            return $this->redirect('Member/user_info');
        }else{
            return $this->view->fetch('login');
        }
    }

    /*
     * 获取验证码
     * @return mixed
     */
    public function iden(){
        return $this->view->fetch('index/iden');
    }


}
