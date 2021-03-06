<?php
/**
 * Created by 赵晓凡
 * User: zhaoxiaofan
 * Date: 2017/2/18
 * Time: 9:30
 */
namespace api\index\controller;


use think\Db;
/**
 * Class Login
 * @package api\index\controller
 * @return 
 */
class Alipay extends Auth
{
//支付宝付款
    public function orderAlipay(){
        $orderno=$_GET['orderno'];
        $ordertype=$this->request->param('ordertype');
        $GoodsAmount=$this->request->param('amount');
        $ConsumeIntegral=$this->request->param('ConsumeIntegral');
        if(empty($orderno)&&empty($ordertype)){
            $total_fee= $GoodsAmount;
            if(!empty($total_fee)&&$total_fee>0){
                $returnData=$this->generateOrder($total_fee);
                if($returnData['status']==1){
                    $out_trade_no=$returnData['orderno'];
                    $this->alipay_submit($out_trade_no,$total_fee);
                    $returnData['status']=1;
                    $returnData['msg']='支付成功';
                }
            }else{
                $returnData['status'] = 0;
                $returnData['msg'] = '总金额必须大于零';
            }
        }else{
            $membername = $this->username;
            $where['UserId'] = $membername;
            $userinfoxx = Db::name('usermsg')->where($where)->field('Umoney,Pv')->find();
            if(empty($ConsumeIntegral)){
                $ConsumeIntegral=0;
            }
            if(is_numeric($ConsumeIntegral)) {
                $ConsumeIntegral=floor($ConsumeIntegral*100)/100;
            }else{
                $returnData['status'] = 0;
                $returnData['msg'] = '积分参数错误';
            }
            if($ConsumeIntegral<0){
                $returnData['status'] = 0;
                $returnData['msg'] = '积分不能为负';
            }
            if ($userinfoxx) {
                if($ordertype=='outer'){//主订单号
                    $orderData=Db::name('ordermain')->where("OuterOrderId='".$orderno."'")->field('InnerOrderId')->select();
                    $useumoney = 0;
                    $usegoodspv = 0;
                    $outerorderid = '';
                    foreach ($orderData as $key => $value) {
                        $condition['InnerOrderId'] = $value['InnerOrderId'];
                        $orderRecord = Db::name('ordermain')->where($condition)->field('GoodsAmount,OuterOrderId')->find();
                        $useumoney = $useumoney + $orderRecord['GoodsAmount']-$ConsumeIntegral;//计算需要扣除的购物币的总金额
                        $useumoney=number_format($useumoney,2);
                        $usegoodspv = $usegoodspv + $ConsumeIntegral;//计算需要扣除的消费积分汇总
                        if (empty($outerorderid)) {
                            $outerorderid = $orderRecord['OuterOrderId'];
                        }
                    }
                    $updatedate['ConsumeIntegral']=$usegoodspv;
                    $orderData=Db::name('ordermain')->where("OuterOrderId='".$orderno."'")->update($updatedate);
                    $this->alipay_submit($outerorderid . '-outer', $useumoney);
                    $returnData['status']=1;
                    $returnData['msg']='支付成功';
                }elseif($ordertype=='inner'){//子订单号
                    $condition['InnerOrderId'] = $orderno;
                    $condition['Status'] = 1;
                    $orderRecord = Db::name('ordermain')->where($condition)->field('GoodsAmount')->find();
                    if ($orderRecord) {
                        $useumoney = $orderRecord['GoodsAmount']-$ConsumeIntegral;//计算需要扣除的购物币的总金额
                        $useumoney=number_format($useumoney,2);

                        $usegoodspv =$ConsumeIntegral;//计算需要扣除的消费积分汇总
                        $this->alipay_submit($orderno, $useumoney);

                        $updatedate['ConsumeIntegral']=$usegoodspv;
                        $orderData=Db::name('ordermain')->where($condition)->update($updatedate);
                        $returnData['status']=1;
                        $returnData['msg']='支付成功';
                    } else {
                        $returnData['status'] = 0;
                        $returnData['msg'] = '订单已支付或者不存在';
                    }
                }
            } else {
                $returnData['status'] = 0;
                $returnData['msg'] = '会员不存在';
            }
        }

        if($returnData['status']==0){
            $url='http://byg.zzfeidu.com/mobile.php';
        }else{
            $url='http://byg.zzfeidu.com/mobile.php/order/list';
        }

        $string = '<script type="text/javascript">';
        $string .= 'alert("'.$returnData['msg'].'");';
        $string .= 'location.href="'.$url.'";';
        $string .= '</script>';
        //return $string;
    }

    //构建提交到支付宝网关的数据并进行提交
    public function alipay_submit($orderno,$total_money){
        require_once EXTEND_PATH.'alipaypc\AlipayTradeService.php';
        require_once EXTEND_PATH.'alipaypc\AlipayTradePagePayContentBuilder.php';
        require_once EXTEND_PATH.'alipaypc\config.php';
        //建立请求
        $out_trade_no = $orderno;
        //订单名称，必填
        $subject = '订单支付';
        //付款金额，必填
        $total_amount = $total_money;
        //商品描述，可空
        $body = '订单支付';
        //超时时间
        $timeout_express="1m";
        $payRequestBuilder = new \AlipayTradePagePayContentBuilder();
        $payRequestBuilder->setBody($body);
        $payRequestBuilder->setSubject($subject);
        $payRequestBuilder->setOutTradeNo($out_trade_no);
        $payRequestBuilder->setTotalAmount($total_amount);
        $payRequestBuilder->setTimeExpress($timeout_express);
        $payResponse = new \AlipayTradeService($config);

        $result=$payResponse->pagePay($payRequestBuilder,$config['return_url'],$config['notify_url']);
        var_dump($result);
        return ;
    }

    private function generateOrder($amount){
        $mainorder=date("YmdHis") . rand(100000, 999999);//生成主订单号
        $data["UserId"]=$this->username;
        $data["OuterOrderId"]=$mainorder;
        $data["InnerOrderId"]=$mainorder.'-other';
        $data["GoodsAmount"]=$amount;
        $data["Status"]=1;
        $data['OrderType']=2;//1普通订单 2购物币充值订单
        $data["Message"]='支付宝支付';
        $data["AddDate"]=date('Y-m-d H:i:s',time());

        $id=Db::name("ordermain")->insertGetId($data);//添加数据到订单主表
        if(!empty($id)){
            $returnData['status']=1;
            $returnData['msg']='支付成功';
            $returnData['orderno']=$data["InnerOrderId"];
        }else{
            $returnData['status']=0;
            $returnData['msg']='订单生成失败';
        }
        return $returnData;
    }
}

