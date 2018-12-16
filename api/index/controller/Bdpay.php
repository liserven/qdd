<?php
/**
 * Created by
 * User: zhaoxiaofan
 * Date: 2017/2/18
 * Time: 9:30
 */
namespace api\index\controller;

use think\View;
use think\Config;
use think\Db;
use think\Session;
/**
 * Class Login
 * @package api\index\controller
 * @return
 */
class Bdpay extends Auth
{
    protected $view;

    public function __construct()
    {
        parent::__construct();

        if (null === $this->view) {
            $this->view = View::instance(Config::get('template'), Config::get('view_replace_str'));
        }
    }

    public function jsApiCall()
    {
        if ($this->loginStatus) {
            $orderno=$_GET['orderno'];
            $ordertype=$_GET['ordertype'];
            $GoodsAmount=$_GET['GoodsAmount'];
            $ConsumeIntegral=$_GET['ConsumeIntegral'];
            if(empty($ConsumeIntegral)){
                $ConsumeIntegral=0;
            }
            if(is_numeric($ConsumeIntegral)) {
                $ConsumeIntegral=floor($ConsumeIntegral*100)/100;
            }else{
                $string = '<script type="text/javascript">';
                $string .= 'alert("积分参数错误！");';
                $string .= 'location.href="/mobile.php";';
                $string .= '</script>';
                return $string;
            }
            if($ConsumeIntegral<0){
                $string = '<script type="text/javascript">';
                $string .= 'alert("积分参数错误！");';
                $string .= 'location.href="/mobile.php";';
                $string .= '</script>';
                return $string;
            }
                $membername =$this->username;
                $where['UserId'] = $membername;
                $userinfoxx = Db::name('usermsg')->where($where)->field('Pv')->find();
                if ($userinfoxx) {
                    if($userinfoxx['Pv']<$ConsumeIntegral){
                        $string = '<script type="text/javascript">';
                        $string .= 'alert("您的积分不足！");';
                        $string .= 'location.href="/mobile.php";';
                        $string .= '</script>';
                        return $string;
                    }
                    if($ordertype=='outer'){//主订单号
                        $orderData=Db::name('ordermain')->where("OuterOrderId='".$orderno."'")->field('Id,InnerOrderId')->select();
                        $useumoney = 0;
                        $usegoodspv = 0;
                        foreach ($orderData as $key => $value) {
                            $condition['InnerOrderId'] = $value['InnerOrderId'];
                            $condition['Status'] = 1;
                            $orderRecord = Db::name('ordermain')->where($condition)->field('Id,OuterOrderId,InnerOrderId,GoodsAmount,ConsumeIntegral')->find();
                            if ($orderRecord) {
                                $useumoney = $useumoney + $orderRecord['GoodsAmount']-$ConsumeIntegral;//计算需要扣除的购物币的总金额
                                $usegoodspv = $usegoodspv + $ConsumeIntegral;//计算需要扣除的消费积分汇总
                                $orderid = $orderRecord['OuterOrderId'] . '-outer';
                                $id=$orderRecord['Id'];
                            } else {
                                $string = '<script type="text/javascript">';
                                $string .= 'alert("订单已支付！");';
                                $string .= 'location.href="/mobile.php";';
                                $string .= '</script>';
                                return $string;
                            }
                        }
                        $updatedate['ConsumeIntegral']=$usegoodspv;
                        $orderData=Db::name('ordermain')->where("OuterOrderId='".$orderno."'")->update($updatedate);
                    }elseif($ordertype=='inner'){//子订单号
                        $condition['InnerOrderId'] = $orderno;
                        $condition['Status'] = 1;
                        $orderRecord = Db::name('ordermain')->where($condition)->field('Id,OuterOrderId,InnerOrderId,GoodsAmount,ConsumeIntegral')->find();
                        if ($orderRecord) {
                            $useumoney = $orderRecord['GoodsAmount']-$ConsumeIntegral;//计算需要扣除的购物币的总金额
                            $usegoodspv =$ConsumeIntegral;//计算需要扣除的消费积分汇总
                            $orderid = $orderRecord['InnerOrderId'];
                            $id=$orderRecord['Id'];
                        } else {
                            $string = '<script type="text/javascript">';
                            $string .= 'alert("订单已支付！");';
                            $string .= 'location.href="/mobile.php";';
                            $string .= '</script>';
                            return $string;
                        }
                        $updatedate['ConsumeIntegral']=$usegoodspv;
                        $orderData=Db::name('ordermain')->where($condition)->update($updatedate);
                    }elseif($ordertype=='other'){//其他类型的订单
                        $condition['InnerOrderId'] = $orderno;
                        $condition['Status'] = 1;
                        $orderRecord = Db::name('ordermain')->where($condition)->field('Id,OuterOrderId,InnerOrderId,GoodsAmount')->find();
                        if ($orderRecord) {
                            $useumoney = $orderRecord['GoodsAmount']-$ConsumeIntegral;
                            $usegoodspv =$ConsumeIntegral;//计算需要扣除的消费积分汇总
                            $orderid = $orderRecord['InnerOrderId'];
                            $id=$orderRecord['Id'];
                        } else {
                            $string = '<script type="text/javascript">';
                            $string .= 'alert("订单已支付！");';
                            $string .= 'location.href="/mobile.php";';
                            $string .= '</script>';
                            return $string;
                        }
                        $updatedate['ConsumeIntegral']=$usegoodspv;
                        $orderData=Db::name('ordermain')->where($condition)->update($updatedate);
                    }
                    $parameter = array(
                        'total'=>$useumoney/4,//总金额
                        'return_url'=>'http://'.$_SERVER['HTTP_HOST'].'/api.php/order/bdpay_notify',//回调页面
                        'cancel_url'=>'http://'.$_SERVER['HTTP_HOST'].'/mobile.php/order/pay',//取消支付的返回地址
                        'orderid'=>$orderid,//订单号
                        'account'=>$membername,//用户名
                        'order_name'=>'购物订单-'.$orderid,//订单描述
                    );
                    $html=$this->buildRequestForm($parameter,"post", "确认");
                    echo $html;
                }
        }else{
            $string = '<script type="text/javascript">';
            $string .= 'alert("未登录");';
            $string .= 'location.href="/mobile.php/login";';
            $string .= '</script>';
            return $string;
        }
    }
    /**
     * 建立请求，以表单HTML形式构造（默认）
     * @param $para_temp 请求参数数组
     * @param $method 提交方式。两个值可选：post、get
     * @param $button_name 确认按钮显示文字
     * @return 提交表单HTML文本
     */
    function buildRequestForm($para_temp, $method, $button_name) {
        //待请求参数数组
        $para = $para_temp;

        $sHtml = "<form id='bodunsumbit' name='bodunsumbit' action='http://intcomsync.com/subdomain/bolton/paymentnpay/index.php?route=common/progress/login' method='".$method."'>";
        while (list ($key, $val) = each ($para)) {
            $sHtml.= "<input type='hidden' name='".$key."' value='".$val."'/>";
        }
        //submit按钮控件请不要含有name属性
        $sHtml = $sHtml."<input type='submit' value='".$button_name."'></form>";

        $sHtml = $sHtml."<script>document.forms['bodunsumbit'].submit();</script>";

        return $sHtml;
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

