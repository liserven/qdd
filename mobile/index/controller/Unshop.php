<?php

namespace mobile\index\controller;
\think\Loader::import('controller/Jump', TRAIT_PATH, EXT);

use think\Db;
use think\Session;
class Unshop extends Common
{
    use \traits\controller\Jump;//使用trait复用Jump中的方法

    /**
     * 联盟商家列表
     * @return mixed
     */
     public function unshop_list()
     {
         $arr=file_get_contents(config('API_DOMAIN_NAME')."/api.php/index/Supplier/supplier_list");
         $list=json_decode($arr,true);
         $unshop_list=$list['data']['data_list'];
         $this->view->assign('unshop_list',$unshop_list);
         return $this->view->fetch('unshop_list');
     }

    /**
     * 联盟商家详情
     * @return mixed
     */
    function unshop_show(){
        $sid = $this->request->param('shopid');
        $arr=file_get_contents(config('API_DOMAIN_NAME')."/api.php/index/Supplier/supplier_detail/sid/".$sid);
        $list=json_decode($arr,true);
        $unshop_show=$list['data'];


        $this->view->assign('udetails',$unshop_show);
        return $this->view->fetch('unshop_show');
    }

    /**
     * 商品分类
     * @return mixed
     */
    public function product_category(){
        $cid=$this->request->param('cid');
        empty($cid)?0:$cid;
        if($cid===0){//推荐分类
            $str='/'.$cid;
        }else{//自定义分类
            $str='?cid='.$cid;
        }
        $arr=file_get_contents(config('API_DOMAIN_NAME')."/api.php/goods/category.{$str}");
        $category=json_decode($arr,true);
        $leftTeb=$category['data'];
        $mainCate=$category['data'][0]['child'];
        foreach ($mainCate as $k=>$v)
        {
            $mainCate[$k]['img']=str_replace('/Public','/public/Public',$mainCate[$k]['img']);
        }
        $this->view->assign('leftCate',$leftTeb);
        $this->view->assign('mainCate',$mainCate);

        $this->view->assign('cid',empty($cid)?0:$cid);
        return $this->view->fetch('product_category');
    }

}
