<?php

/**

 * Created by 赵晓凡

 * User: zhaoxiaofan

 * Date: 2017/3/13

 * Time: 16:10

 */



namespace mobile\index\controller;



use think\Db;

use think\Session;



class Order extends Auth

{

    /**

     * 订单确认页面

     * @return mixed

     */

    public function order_affirm(){

        return $this->view->fetch('order_affirm');

    }
    public function order_affirmnow(){
        return $this->view->fetch('order_affirmnow');

    }



    /**

     * 订单支付页面

     * @return mixed

     */

    public function order_pay(){

        $orderno=$this->request->param('orderno');
        $ordertype=$this->request->param('ordertype');
        $this->view->assign('orderno',empty($orderno)?'':$orderno);
        $this->view->assign('ordertype',empty($ordertype)?'outer':$ordertype);
        $paymentmethod=Db::name('paymentmethod')->where(array('Enabled'=>1))->order('SortOrder asc')->select();
        $userinfos=Db::name('usermsg')->where('UserId="'.Session::get('membername').'"')->field('Umoney')->find();
        foreach ($paymentmethod as $k=>$v){
            if($v['PaymentCode']=='yue'){
                $paymentmethod[$k]['PaymentName']=$v['PaymentName'].'(余额:'.$userinfos['Umoney'].')';
            }
        }
        $this->view->assign('paymentmethod',$paymentmethod);
        return $this->view->fetch('order_pay');
    }

    /**

     * 支付宝同步通知

     * @return mixed

     */

    public function alipay_return(){



        require_once EXTEND_PATH.'alipaywap\AlipayTradeService.php';

        require_once EXTEND_PATH.'alipaywap\config.php';

        $arr=$_GET;



        $alipaySevice = new \AlipayTradeService($config);

        $result = $alipaySevice->check($arr);

        if($result) {//验证成功

            /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

            //请在这里加上商户的业务逻辑程序代码



            //——请根据您的业务逻辑来编写程序（以下代码仅作参考）——

            //获取支付宝的通知返回参数，可参考技术文档中页面跳转同步通知参数列表



            //商户订单号



            $out_trade_no = htmlspecialchars($_GET['out_trade_no']);

            if(in_array('outer',explode('-',$out_trade_no))) {//判断该订单是子订单还是主订单（主订单包含多个子订单）

                $orderArr = explode('-', $out_trade_no);

                $condition['OuterOrderId'] = $orderArr[0];

                $goods = Db::name('ordermain')->field('InnerOrderId')->where($condition)->find();

                $orderno=$goods['InnerOrderId'];

            }else{

                $orderno=$out_trade_no;

            }



            $this->view->assign('orderno',$orderno);

            return $this->view->fetch();

            //——请根据您的业务逻辑来编写程序（以上代码仅作参考）——



            /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        }else {

            //验证失败

            echo "验证失败";

        }



    }



    /**

     * 订单列表页面

     * @return mixed

     */

    public function order_list(){

        $param='';

        if($this->request->param('keyorderid')){

            $param.='keyorderid='.$this->request->param('keyorderid').'&';

        }

        if($this->request->param('ordertype')){

            $param.='ordertype='.$this->request->param('ordertype').'&';

        }

        if($this->request->param('status')||$this->request->param('status')==='0'){

            $param.='status='.$this->request->param('status').'&';

        }

        if($this->request->param('keyreiciver')){

            $param.='keyreiciver='.$this->request->param('keyreiciver').'&';

        }

        if($this->request->param('keysupname')){

            $param.='keysupname='.$this->request->param('keysupname').'&';

        }

        if($this->request->param('datemin')){

            $param.='datemin='.$this->request->param('datemin').'&';

        }

        if($this->request->param('datemax')){

            $param.='datemax='.$this->request->param('datemax').'&';

        }

        $this->view->assign('param',$param);

        return $this->view->fetch('order_list');

    }



    /**

     * 订单展示

     * @return mixed

     */

    public function order_show(){

        return $this->view->fetch('order_show');

    }



    public function order_return()
    {
//        var_dump(111);exit;
        $orderno = $this->request->param('orderno');
        $userid = $this->request->param('username');
        if ($orderno && $userid) {
//            var_dump(Session::get('membername'));exit;
//            if ($userid == Session::get('membername')) {
                if (strpos($orderno, '-') !== false) {
                    $where['InnerOrderId'] = $orderno;
                } else {
                    $where['OuterOrderId'] = $orderno;
                }
                $where['Status'] = 1;
                $where['UserId'] = $userid;
                $orderdata = Db::name('ordermain')->where($where)->select();

                if ($orderdata) {
                    $updata['Status'] = 2;
                    $updata['PayDate'] = date('Y-m-d H:i:s');
                    $res = Db::name('ordermain')->where($where)->update($updata);
                    foreach ($orderdata as $k=>$v){
                        if($v['SupplierId']==1){
//                            $orderinfo['buyer_name']=$v['UserId'];
//                            $orderinfo['order_id']=$v['Id'];
//                            $orderinfo['order_sn']=$v['OuterOrderId'];
                            $orderinfo['province']=$v['Province'];
                            $orderinfo['city']=$v['City'];
                            $orderinfo['county']=$v['County'];
                            $orderinfo['address']=$v['Address'];
                            $orderinfo['name']=$v['ReceiveName'];
                            $orderinfo['phone']=$v['UserTel'];
                            $wheredetail['UserId']=$userid;
                            $wheredetail['InnerOrderId']=$v['InnerOrderId'];
                            $wheredetail['SupplierId']=1;
                            $detaildata=Db::name('orderdetail')->where($wheredetail)->select();
                            $Goods_list=[];
                            foreach ($detaildata as $kk=>$vv){
                                $Goods_list[$kk]['rec_id']=$vv['Id'];
                                $Goods_list[$kk]['sku']=$vv['Txm'];
                                $Goods_list[$kk]['count']=$vv['proNum'];
                            }
                            $Goods_list=json_encode($Goods_list);
                            $orderinfo=json_encode($orderinfo);
                            $url='http://test.999000.cn/api/third/cart/index.php?act=ThirdStoreSubmitOrder';
                            $datas['Goods_list']=$Goods_list;
                            $datas['addr']=$orderinfo;
                            $datas['appid']='zhnewlg';
                            $datas['key']=md5($Goods_list.$orderinfo.'zhnewlg'.'zhnewlg999000jl');
                            $resu=http_request($url,$datas);
//                            var_dump($resu);exit;
                            $resu=json_decode($resu,true);
//var_dump($resu);exit;
                            if($resu['status']==0){
                                unset($where['Status']);
                                Db::name('ordermain')->where($where)->update(['order_id'=>$resu['data']['order_id']]);
                            }
                        }
                    }


//                    if ($res) {
                        $this->redirect('/mobile.php/order/list?status=5');
//                    }
                }
//            }

        }
    }


}

