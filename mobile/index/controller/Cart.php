<?php

/**

 * Created by 赵晓凡

 * User: zhaoxiaofan

 * Date: 2017/3/13

 * Time: 16:10

 */
namespace mobile\index\controller;
use think\Db;
class Cart extends Auth

{
    /**
     * 购物车列表
     * @return mixed
     */
    public function cart_list(){
        return $this->view->fetch('cart_list');
    }
    public function cart_empty_list(){
        $hitgoods=Db::name('product')->field('ProId,MarketPrice,EnjoyPrice,VipPrice,ProName,ProImg')->where('IsOnSell=1 and IsHit=1')->limit(4)->order('sort asc,prosum desc,ProId desc')->select();
        if($hitgoods){
            foreach ($hitgoods as $key=>$val){
                if(strpos($val['ProImg'],'http://')!==false){
                    $hitgoods[$key]['ProImg']=$val['ProImg'];
                }else{
                    $hitgoods[$key]['ProImg']=config('IMAGE_DOMAIN_NAME').'/public/Upload/cpimg/'.$val['ProImg'];
                }
            }
            $hitgoods=indexToLower($hitgoods);
        }
        $this->view->assign('hitgoods',$hitgoods);
        return $this->view->fetch('cart_empty_list');
    }
}