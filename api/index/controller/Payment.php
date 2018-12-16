<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/17
 * Time: 10:28
 */

namespace api\index\controller;
use think\Db;


class Payment   extends Auth
{
  public function queryOrderStatus()
  {
      if($this->loginStatus) {
        $orderno=$this->request->param('orderno');
        $data=Db::name('ordermain')->field('Status')->where('InnerOrderId='.$orderno)->find();
          if($data['Status']==2){
              return json(['status'=>1,'msg'=>'支付成功','orderno'=>$orderno]);
          }else{
            return;
          }
      }else{
          $returnArr['status']=0;
          $returnArr['msg']='登录失效';
      }
    return json($returnArr);

  }
}
