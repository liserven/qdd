<?php

/**

 * Created by 赵晓凡

 * User: zhaoxiaofan

 * Date: 2017/4/27

 * Time: 17:20

 */



namespace api\index\controller;



use think\Request;

use think\Session;



class Qrcode

{

    //产生微信支付的二维码

    public function getWxpayQrcode(){

        $ordertype=Request::instance()->param('ordertype');//订单类型

        $orderno=Request::instance()->param('orderno');//订单号


        header('Content-Type: image/png');

        require_once EXTEND_PATH . 'phpqrcode.php';

        $domain_name='http://pay.999000.cn';

//        $url='http://pay.999000.cn/api.php/order/wxpay?ordertype=outer&orderno=20170427160157406188';

        $url='http://pay.999000.cn/api.php/order/wxpay?ordertype='.$ordertype.'&orderno='.$orderno;

        ob_clean();

        \QRcode::png($url, $outfile=false, $level=QR_ECLEVEL_L, $size=8, $margin=4, $saveandprint=false);

        exit();

    }



    //产生会员的注册二维码

    public function getUserQrcode(){
        header('Content-Type: image/png');
        require_once EXTEND_PATH . 'phpqrcode.php';
        $introduceId=Request::instance()->param('introduceId');
        //$usertype = Request::instance()->param('usertype');
        $url='http://'.$_SERVER['HTTP_HOST'].'/mobile.php/member/reg?introduceId='.$introduceId;
        ob_clean();
        \QRcode::png($url, $outfile=false, $level=QR_ECLEVEL_L, $size=12, $margin=2, $saveandprint=false);
        exit();
    }

}