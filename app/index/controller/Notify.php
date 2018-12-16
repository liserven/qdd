<?php
/**
 * Created by 赵晓凡
 * User: zhaoxiaofan
 * Date: 2017/2/18
 * Time: 9:30
 */

namespace app\index\controller;


use think\Db;

/**
 * Class Login
 * @package api\index\controller
 * @return
 */
class Notify extends Auth
{

    /**
     * 仅供客户端使用
     * @throws \Exception
     */
    public function aliPay_notify_app()
    {
//        var_dump('http://'.$_SERVER['SERVER_NAME'].':'.$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"]);exit;
        $data = !empty($_POST)?$_POST:$_GET;
        if (empty($data)) {
            exit("fail_0");
        }
        $appId = $data['app_id'];
        if (empty($appId) || $appId != "2018030202300894") {
            exit("fail_1");
        }
//        $sellerEmail = $data['seller_email'];
//        if (empty($sellerEmail) || $sellerEmail != "m15595282281@163.com") {
//            exit("fail_2");
//        }
        $tradeStatus = $data['trade_status'];
        if (empty($tradeStatus) || $tradeStatus != "TRADE_SUCCESS") {
            exit("fail_3");
        }
        $out_trade_no = $data['out_trade_no'];
        $trade_no = $data['trade_no'];
        $gmt_payment = $data['gmt_payment'];
        $total_amount = $data['total_amount'];
        $result = $this->getAilOrderInfo($out_trade_no, $trade_no);
        if ($result) {
            $parameter['out_trade_no'] = $out_trade_no;
            $parameter['time_end'] = $gmt_payment;
            $parameter['total_fee'] = $total_amount;
            $this->process($parameter, 2);
            exit('success');
        } else {
            exit('fail_4');
        }
    }

    /**
     * 仅供客户端使用
     * @param $out_trade_no
     * @param $trade_no
     * @return bool
     * @throws \Exception
     */
    private function getAilOrderInfo($out_trade_no, $trade_no)
    {
        require_once(EXTEND_PATH . 'aliPay_App/AopClient.php');
        require_once(EXTEND_PATH . 'aliPay_App/AlipayTradeQueryRequest.php');
        $aop = new \AopClient();
        $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
        $aop->appId = '2018030202300894';
        $aop->rsaPrivateKey = 'MIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQCmjs5SeS8l3/9pY64Z3Bpfi7LEjlE2fZvyL9eGm/AZDv5DyHhdsqwCF9oH6jkhFbGm7jT/rDx8Odwa78il0KmlzrNLY2gsQvUJPnfC68Auriqmk+FKsCfv1td2bL6NNmiLAHW1CHq4PXVmx/MmYZBK4HCsNGGdDzRWvziakAiG7rrdRiNuWjzmSQ9BzcDjD8HtofPKF8cNFZQ3Y2H0OLr7J4z/CFEolIOS2ox8NyF8Z+4EBuGcyS1MR0Gkn+phzTh5mbY/t99/uAglS9tk/aL2hEf8sN+/V1CVObv4t+vbDoo+HBhNQ5xd6gD7/ktHjEMUJ/zFQUtPVQAISahbFN27AgMBAAECggEAJm8ftzJVqeTFmHkzDK6Yu2GOMAMzRGK54KofjuVfAzlNfTC+bci0HouIeXkYE8VgyEeBnVoOXxAu9VxNrc23yBYpk4Tt3gDZcHekxSsHnv37w5KuU0DRBmxKLf6r/gQHHcROZSb+wpMUC1KqjjnHRTADcAlqWQbQKhBYLHp+LTx7r8RTTOZtl478SEWlhXTEJxW7AsKWjKoa+rz5Xk4nyFV4xfLqKdemX5Ba4Wygw8xtvVvsF5z+6Vb/zLQwVSygMAwklnbcaiXYJRAqedO/4roa2NErS1yWKCo/Rt0EsleH5yZZxB1MNI1TV7rNfc5DxxLM/bx3uaJzlk8vLqRPGQKBgQDmLKmXQZiht1VXUJJZMu+Ik7cstkXL+MfWTX3Y7edCJhC/e3qjifS1RNcH2niHoVszu8TlYPWZ4UboxySNSP8Vf7zf2hEq26B9atz2//xzL0SwUUrPlqIsWgm4oIvgTFFMoP6ASPFJCj28YtdYa4qqqRsBbjA0iLpTvg5DYW6YbQKBgQC5Pt9/AEqYUqSiXzJVz3itv+0lW3c5rJwFtRUeHtrSulsN2XwJl8EzbZsQ0K1H1T182sqANg110oidMOcO3ftLRRWepLMqpRpufoDX744qrMpSzu4TmnKoNHou9zRL5O6ZIZJhaqqU+cKUpi8jVJq7Wf6b5EnBcNPm//TZBMNFxwKBgQDYYBPtBNUe8OFg+3UYNTHIClSfy5lBbdUuOBqd2scTAAWue72GDyHjZZte3pl/pse9kj4+Ay/eQFAQc2SBDKX+59398VlCGMR+If8mku4WudCwjzrseoZpexq4XPQhHp0ulAmrPubT6rSua5xUmoP4eo/QgG0AdafzIS73WJhG4QKBgGywCwnE7f95Xcc2+52FYVTwav+kLsvUDqb6nC1UBSfm103E8RXfyCeYg4bWKRUgdDcxV+bpz/P1VtqDus3qSGrdWdX+5HB89okmCAqqL0uynfMAHoe58tPmFlpACJsO8z89h1lI8FUARb5Z0wZzuWV7Y/urcerhfDLosowyUzA5AoGAAsioGoxpUmfKUkGhvgWV2OmeLMl7Lpn58TKyVjNLAq1RFp7MBfMNI4QnJQKym35XRqDgHOpZ+VK/3StEyu01vaUCe00/9pUzCBDppaqGAWJ8KglKs17t8Kd32ccyCol3hdKuYTdfOxwsQM+2YL5+9pZgssRZ5iIOJeSKu7JqowA=';
        $aop->alipayrsaPublicKey = 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEApo7OUnkvJd//aWOuGdwaX4uyxI5RNn2b8i/XhpvwGQ7+Q8h4XbKsAhfaB+o5IRWxpu40/6w8fDncGu/IpdCppc6zS2NoLEL1CT53wuvALq4qppPhSrAn79bXdmy+jTZoiwB1tQh6uD11ZsfzJmGQSuBwrDRhnQ80Vr84mpAIhu663UYjblo85kkPQc3A4w/B7aHzyhfHDRWUN2Nh9Di6+yeM/whRKJSDktqMfDchfGfuBAbhnMktTEdBpJ/qYc04eZm2P7fff7gIJUvbZP2i9oRH/LDfv1dQlTm7+Lfr2w6KPhwYTUOcXeoA+/5LR4xDFCf8xUFLT1UACEmoWxTduwIDAQAB';
        $aop->apiVersion = '1.0';
        $aop->signType = 'RSA2';
        $aop->postCharset = 'utf-8';
        $aop->format = 'json';
        $request = new \AlipayTradeQueryRequest();
        $request->setBizContent("{" .
            "\"out_trade_no\":\"$out_trade_no\"," .
            "\"trade_no\":\"$trade_no\"" .
            "  }");
        $result = $aop->execute($request);

        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;
        if (!empty($resultCode) && $resultCode == 10000) {
            return true;
        } else {
            return false;
        }
    }

    //微信支付通知处理
    public function wxpay_notify_url()
    {
        require_once EXTEND_PATH . 'wxpayh5/WxPayPubHelper.php';
        //使用通用通知接口
        $notify = new \Notify_pub();
        //存储微信的回调
        $xml = isset($GLOBALS['HTTP_RAW_POST_DATA']) ? $GLOBALS['HTTP_RAW_POST_DATA'] : file_get_contents('php://input');
        $notify->saveData($xml);
        $data = empty($notify->data) ? ['参数为空'] : $notify->data;
        $file = 'wxpaylog.txt';
        if (!file_exists($file)) {
            touch($file);
        }
        $string = '';
        foreach ($data as $key => $val) {
            $string .= $key . '=' . $val . '&';
        }
        file_put_contents($file, $string . PHP_EOL, FILE_APPEND);
        //exit();
        //验证签名，并回应微信。
        //对后台通知交互时，如果微信收到商户的应答不是成功或超时，微信认为通知失败，
        //微信会通过一定的策略（如30分钟共8次）定期重新发起通知，
        //尽可能提高通知的成功率，但微信不保证通知最终能成功。
        if ($notify->checkSign() == FALSE) {
            $notify->setReturnParameter("return_code", "FAIL");//返回状态码
            $notify->setReturnParameter("return_msg", "签名失败");//返回信息
        } else {
            $notify->setReturnParameter("return_code", "SUCCESS");//设置返回码
        }
        if ($notify->checkSign() == TRUE) {
            if ($notify->data["return_code"] == "FAIL") {
                return "<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[参数格式校验错误]]></return_msg></xml>";
            } else {
                $this->process($notify->data);
                //处理成功后输出success，微信就不会再下发请求了
                return "<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>";
            }
        } else {
            return "<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[签名失败]]></return_msg></xml>";
        }
    }

    //订单的统一处理
    private function process($parameter, $pay_type = 1)
    {//支付类型(pay_type)：1为微信支付 2为支付宝支付
        //订单更新的数据

        $orderno = $parameter['out_trade_no'];
        $orderdata['trade_time'] = date('Y-m-d H:i:s', strtotime($parameter['time_end']));
        $orderdata['pay_type'] = $pay_type;
        //记录数据的构建
        $recorddata['total_fee'] = $pay_type == 1 ? $parameter['total_fee'] / 100 : $parameter['total_fee'];//微信支付的计量单位为分，支付宝的计量单位为元
        $recorddata['trade_status'] = 1;
        $recorddata['pay_type'] = $pay_type;
        $recorddata['trade_time'] = date('Y-m-d H:i:s', strtotime($parameter['time_end']));
        $recorddata['add_date'] = date('Y-m-d H:i:s', time());
        $recorddata['memo'] = '';
        $object = Factory::instance()->getObjectInstance('account');
        if (strpos($orderno,'-' )===false) {//判断该订单是子订单还是主订单（主订单包含多个子订单）
            $condition['OuterOrderId'] = $orderno;
            $orderlist = Db::name('ordermain')->where($condition)->field('InnerOrderId,UserId,ConsumeIntegral')->select();
            foreach ($orderlist as $key => $val) {//针对主订单下的各个子订单分别进行处理
                $status = Db::name('ordermain')->where("InnerOrderId='" . $val['InnerOrderId'] . "' and Status=1")->count();
                if ($status) {
                    $recorddata['orderno'] = $val['InnerOrderId'];
                    $this->order_pay_record($recorddata);
                    $orderdata['orderno'] = $val['InnerOrderId'];
                    $this->order_status_change($orderdata);
                    $orderdetaillist=indexToLower(Db::name('orderdetail')->where("innerorderid='".$val['InnerOrderId']."'")->select());
                    foreach($orderdetaillist as $n=>$v){
                        //未付款库存减少，未发货库存增加
                        $object->stockAction($v["styleid"],0,0+$v["pronum"],0-$v["pronum"]);
                        //更新商品表中销售数量字段
                        $sql='update product set prosum=prosum+'.$v["pronum"].' where ProId='.$v["proid"];
                        Db::execute($sql);
                    }

                    //更新商品表中销售数量字段
//                    $client = ismobile() ? '移动端' : 'PC端';
//                    $accountData = array(
//                        'account_goodspv' => array(
//                            'goodspv' => 0 - $val["ConsumeIntegral"],
//                            'typename' => '扣积分',
//                            'memo' => "({$client})购物扣积分[订单号：" . $val['InnerOrderId'] . "]"
//                        ),
//                        'userid' => $val['UserId'],
//                        'formwho' => $val['UserId']
//                    );
//                    Factory::instance()->getObjectInstance('account')->accountAction($accountData);
                }
            }

        } else {//针对单个的子订单进行处理
            $status = Db::name('ordermain')->where("InnerOrderId='" . $orderno . "' and Status=1")->count();
            if ($status) {
                $orderlist = Db::name('ordermain')->where("InnerOrderId='" . $orderno . "' and Status=1")->field('InnerOrderId,UserId,ConsumeIntegral')->find();
                $recorddata['orderno'] = $orderno;
                $this->order_pay_record($recorddata);
                $orderdata['orderno'] = $orderno;
                $this->order_status_change($orderdata);
                $orderdetaillist=indexToLower(Db::name('orderdetail')->where("innerorderid='".$orderno."'")->select());
//                    var_dump($orderdetaillist);exit;
                foreach($orderdetaillist as $n=>$v){
                    //未付款库存减少，未发货库存增加
                    $object->stockAction($v["styleid"],0,0+$v["pronum"],0-$v["pronum"]);
                    //更新商品表中销售数量字段
                    $sql='update product set prosum=prosum+'.$v["pronum"].' where ProId='.$v["proid"];
                    Db::execute($sql);
                }
//                $client = ismobile() ? '移动端' : 'PC端';
//                $accountData = array(
//                    'account_goodspv' => array(
//                        'goodspv' => 0 - $orderlist["ConsumeIntegral"],
//                        'typename' => '扣积分',
//                        'memo' => "({$client})购物扣积分[订单号：" . $orderno . "]"
//                    ),
//                    'userid' => $orderlist['UserId'],
//                    'formwho' => $orderlist['UserId']
//                );
//                Factory::instance()->getObjectInstance('account')->accountAction($accountData);
            }
        }
    }

    /**
     * 添加微信或者支付宝的支付记录
     * @param $data
     */
    public function order_pay_record($data)
    {
        Db::name('orderpayrecord')->insert($data);
    }

    /**
     * 根据微信或者支付宝的支付状态进行状态的修改
     * @param $data
     */
    public function order_status_change($data)
    {
        $orderdata["Status"] = 2;
        $orderdata["PayDate"] = $data['trade_time'];
        $orderdata['PayMethod'] = $data['pay_type'];
        $where['InnerOrderId'] = $data['orderno'];
        $his['pay_type'] = $data['pay_type'];
        $his['qian'] = 1;
        $cha = Db::name('ordermain')->where($where)->find();
        $orderno = $cha['OuterOrderId'];
        if ($cha) {
            Db::name('goods_history')->where('OuterOrderId=' . $cha['OuterOrderId'])->update($his);
        }
        $result = Db::query('select sum(cash) cash,supplier_key_id,InnerOrderId,ProId,StyleId,ProName,StyleName,ProNum,Price,UserId from orderdetail where `InnerOrderId`="' . $orderno . '" and `supplier_key_id`>=1 GROUP BY `supplier_key_id`');
        foreach ($result as $key => $value) {

            $list = Db::name('supplier')->where('ID=' . $value['supplier_key_id'])->field('ID,cash')->find();
            $data1['cash'] = $list['cash'] + $value['cash'];

            //记录获取利润的表格
            $time = date('Y-m-d H:i:s', time()); //时间
            $data2['time'] = $time; //时间
            $data2['cash'] = $value['cash']; //提成
            $data2['cash_list'] = $data1['cash']; //提成
            $data2['supplier_id'] = $value['supplier_key_id']; //商家ID
            $data2['order'] = $value['InnerOrderId']; //订单号
            $data2['pro_id'] = $value['ProId'];    //商品ID
            $data2['style_id'] = $value['StyleId']; //分类
            $data2['pro_name'] = $value['ProName']; //商品名字
            $data2['style_name'] = $value['StyleName']; //规格名字
            $data2['pro_price'] = $value['Price']; //商品价格
            $data2['pro_num'] = $value['ProNum']; //商品数量
            $data2['user_id'] = $value['UserId']; //会员ID
            $data2['state'] = '0';//获取

            Db::startTrans(); //启动事务
            try {

                $reg = Db::name('supplier')->where('ID=' . $list['ID'])->update($data1);
                $reg1 = Db::name('supplier_cash_list')->insert($data2);

                Db::commit(); //提交事务
            } catch (\PDOException $e) {

                Db::rollback(); //回滚事务

                return json(['status' => 0, 'msg' => '当前操作人数过多请稍后尝试！']);
            }


        }
        Db::name('ordermain')->where($where)->update($orderdata);
    }


}

