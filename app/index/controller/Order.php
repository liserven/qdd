<?php
/**
 * Created by
 * User:
 * Date: 2018/3/9
 * Time: 16:30
 */
namespace app\index\controller;

use think\Db;
use think\Session;

/**
 * Class Order
 * @package api\index\controller
 * @return 首页页面
 */
class Order extends Auth
{
    /**
     * 立即购买生成订
     * 请求实例：http://www.XX.com/app.php/order/generatenow
     * 请求方式：post
     * @param   addres  地址id
     * @param  shipping_method  配送方式
     * @param  remarks_supid(多个 商家id)
     * @param proid 产品id
     * @param styleid  规格id
     * @param pronum  商品数量
     * @return   status 状态 |  msg 消息提示
     *
     */
    public function nowBuy_orderGenerate(){
        $info=$this->islogin();
        if($info['status']==0){
            if($this->request->isPost()) {
                $orderData = $this->request->post();
                $userid = $info['data']['UserId'];
                $proid = $orderData['proid'];
                $styleid =  $orderData['styleid'];
                $pronum =  $orderData['pronum'];
                $agentd = $userid;

                if(!empty($orderData["addres"])){
                    $id=$this->request->param('id');
                    $id=(string)$id;
                    $userid=$info['data']['UserId'];
                    $membername=$userid;

                    $relist=array_change_key_case(Db::name("comreceiveinfo")->where("id=".$orderData["addres"])->find());
                    $mainorder=date("YmdHis") . rand(100000, 999999);//生成主订单号
                    $ordermainidArr=[];
                    $orderdetailidArr=[];
                    // 开始
                    $styledata = Db::name('productstock')->where('StyleId=' . $styleid)->field('StyleName,Kucun')->find();

                    $fields = 'ProName,SupplierId,VipPrice,ConsumeIntegral,Unit,BalancePrice,MinPurchase,GiveIntegral';
                    $prodata = Db::name('product')->where('ProId=' . $proid)->field($fields)->find();

                    $goodsAmount=$prodata['VipPrice'] * $pronum;
                    $goodspv=$prodata['ConsumeIntegral'] * $pronum;
                    $givepv=$prodata['GiveIntegral'] * $pronum;
//                    $yuntotal = $this->shipping($pronum,1);
                    $yuntotal=0;
                    $shipping = $yuntotal;
                    $data["Freight"] = $shipping;
                    $price=$data["OrderAmount"] = $goodsAmount + $shipping;
                    //构建主订单数据--start
                    $data["SupplierId"]=$prodata['SupplierId'];
                    $data["Province"]=$relist["province"];
                    $data["City"]=$relist["city"];
                    $data["County"]=$relist["county"];
                    $data["Address"]=$relist["address"];
                    $data["ReceiveName"]=$relist["receivename"];
                    $data["UserTel"]=$relist["mobile"];
                    $data["OuterOrderId"]=$mainorder;
                    $data["InnerOrderId"]=$mainorder.'-'.$prodata['SupplierId'];
                    $data["GoodsAmount"]=$goodsAmount;
                    $data["UserId"]=$membername;

                    $mes_index="remarks_".$prodata['SupplierId'];
                    if(empty($orderData[$mes_index])){
                        $Message='';
                    }else{
                        $Message=$orderData[$mes_index];
                    }
                    $data["Message"]=$Message;
                    $data["ConsumeIntegral"]=$goodspv;  //抵扣积分
                    $data["GiveIntegral"]=0;
                    $data["UseUmoney"]=0;
                    $data["Status"]=1; //普通订单 订单直接为未付款状态
                    $data['OrderType']=1;
                    $data["AddDate"]=date('Y-m-d H:i:s',time());

                    $ordermainidArr[]=Db::name("ordermain")->insert($data);//添加数据到订单主表
                    //构建订单详情的数据--start
                    $datade["ProId"]=$proid;
                    $datade["StyleId"]=$styleid;
                    $datade["proNum"]=$pronum;
                    $datade["ProName"]=$prodata['ProName'];
                    $datade["StyleName"]=$styledata['StyleName'];
                    $datade["Unit"]=$prodata['Unit'];
                    $datade["SupplierId"]=$prodata['SupplierId'];
                    $datade["InnerOrderId"]=$mainorder.'-'.$prodata['SupplierId'];
                    $datade["UserId"]=$membername;
                    $styleData=Db::name("productstock")->where("styleid=".$styleid)->field('Proid as proid')->find();
                    if($styleData){
                        $productData=Db::name("product")->where("proid=".$styleData['proid'])->field('VipPrice,ConsumeIntegral,BalancePrice,GiveIntegral')->find();
                        if(empty($productData)){
                            $returnData['status']=0;
                            $returnData['msg']='ID为('.$styleData['proid'].')的商品不存在！';
                            return json($returnData);
                        }
                    }else{
                        $returnData['status']=0;
                        $returnData['msg']='商品规格('.$styleid.')不存在！';
                        return json($returnData);
                    }
                    $datade["Price"]=$productData['VipPrice'];
                    $datade["ConsumeIntegral"]=$productData['ConsumeIntegral'];
                    $datade["GiveIntegral"]=$productData['GiveIntegral'];
                    $datade["BalancePrice"]=$productData['BalancePrice'];
                    $datade["AddDate"]=date('Y-m-d H:i:s',time());
                    $orderdetailidArr[]=Db::name("orderdetail")->insert($datade);//添加数据到订单详情表

                    //去库存的操作
                    $object=Factory::instance()->getObjectInstance('account');
                    $stockData=$object->stockAction($styleid,0-$pronum,0-$pronum,0);
                    if($stockData['status']==1){
                        return json($stockData);
                    }
                    //构建订单详情的数据--end
                    $redata=$this->accountpayoutVerify($userid,$mainorder);
                    if($redata['status']==0){
                        $returnData["data"]['accountpay']=0;
                    }else{
                        $returnData["data"]['accountpay']=1;
                    }
                    $returnData["data"]['orderno']=$mainorder;
                    $returnData["data"]['numprice']=$price;
                    $returnData['status']=0;
                    $returnData['msg']='生成订单成功！';
                }else{

                    $returnData['status']=3;
                    $returnData['msg']='收货地址不能为空！';
                }
            }else{

                $returnData['status']=2;
                $returnData['msg']='数据提交方式错误！';
            }
        }else{

            $returnData['status']=99;
            $returnData['msg']='未登录';
        }
        return json($returnData);
    }
    /** 立即购买
     * heliping.top/app.php/order/nowBuy
     * @param proid 产品id
     * @param styleid  规格id
     * @param pronum  商品数量
     * @return \think\response\Json
     */
    public function nowBuy(){
        $info = $this->islogin();//判断会员是否登录
        if ($info['status'] == 0) {
            $userid = $info['data']['UserId'];
            $proid = $this->request->param('proid');
            $styleid = $this->request->param('styleid');
            $pronum = $this->request->param('pronum');
            $agentd = $userid;
            $where['ProId'] = $proid;
            $where['IsOnSell'] = 1;
            $procount = Db::name('product')->where($where)->count();//判断该商品是否存在或者已上架
            if ($procount > 0) {
                $styledata = Db::name('productstock')->where('StyleId=' . $styleid)->field('StyleName,Kucun')->find();
                if ($pronum <= $styledata['Kucun']) {
                    $fields = 'ProName,SupplierId,VipPrice,ConsumeIntegral,Unit,BalancePrice,MinPurchase,GiveIntegral';
                    $prodata = Db::name('product')->where('ProId=' . $proid)->field($fields)->find();
                    if ($pronum >= $prodata['MinPurchase'] && (($pronum % $prodata['MinPurchase']) == 0)) {//判断所购的商品数量是否小于最小起订量且为最小起订量的倍数

                        //供应商名称
                        $supInfo = Db::name('supplier')->where('ID=' . $prodata['SupplierId'])->field('Name')->find();
                        $groupcart[0]['supid']=$prodata['SupplierId'];
                        $groupcart[0]['supname']=$supInfo['Name'];
                        $groupcart[0]['sspp']=$prodata['VipPrice'] * $pronum;
                        $groupcart[0]['sspv']=$prodata['ConsumeIntegral'] * $pronum;
                        $groupcart[0]['pronum']=$pronum;
                        $groupcart[0]['voo'][0]['supid']=$prodata['SupplierId'];
                        $groupcart[0]['voo'][0]['supname']=$supInfo['Name'];
                        $groupcart[0]['voo'][0]['proid']=$proid;
                        $groupcart[0]['voo'][0]['proname']=$prodata['ProName'];
                        $groupcart[0]['voo'][0]['styleid']=$styleid;
                        $groupcart[0]['voo'][0]['stylename']=$styledata['StyleName'];
                        $groupcart[0]['voo'][0]['pronum']=$pronum;
                        $groupcart[0]['voo'][0]['shopprice']=$prodata['VipPrice'] * $pronum;
                        $groupcart[0]['voo'][0]['consumeintegral']=$prodata['ConsumeIntegral'] * $pronum;
                        $groupcart[0]['voo'][0]['giveintegral']=$prodata['GiveIntegral'] * $pronum;
                        $groupcart[0]['voo'][0]['unit']=$prodata['Unit'];
                        $groupcart[0]['voo'][0]['carttype']=2;
                        $groupcart[0]['voo'][0]['positioncode']='';
                        $groupcart[0]['voo'][0]['agentd']=$agentd;
                        $groupcart[0]['voo'][0]['addtime']=date('Y-m-d H:i:s', time());
                        $groupcart[0]['voo'][0]['balanceprice']=$prodata['BalancePrice'];
                        $groupcart[0]['voo'][0]['regionofbuy']='APP端';
                        $groupcart[0]['voo'][0]['status']='1';
                        $groupcart[0]['voo'][0]['shipping_id']='1';
                        $groupcart[0]['voo'][0]['img']=getproimgbysid($styleid);

                        //购物车数组
                        $rdata['groupcart']=$groupcart;
                        //运费
//                        $shipping = $this->shipping($pronum,$prodata['shipping_id']);
                        $shipping=0;
                        //收货地址
                        $relist=indexToLower(Db::name('comreceiveinfo')->where("UserId='".$userid."'")->select());
                        $rdata['relist']=$relist;
                        //账户余额
                        $rdata['money']=Db::name('usermsg')->field('Umoney,Pv')->where("UserId='".$userid."'")->find();
                        $rdata['shipping']=$shipping;//运费
                        $rdata['pricetotal']=$prodata['VipPrice'] * $pronum;//总金额
                        $rdata['pvtotal']=$prodata['ConsumeIntegral'] * $pronum;//总积分
                        $rdata['counttotal']=$pronum;//件数
                        $returnData["data"] = $rdata;
                        $returnData['status'] = 0;
                        $returnData['msg'] = '成功';
                    } else {
                        $returnData['status'] = 5;
                        $returnData['msg'] = '商品数量必须大于等于最小起订量且为最小起订量的倍数';
                    }
                } else {

                    $returnData['status'] = 4;
                    $returnData['msg'] = '该商品库存不足';
                }
            } else {

                $returnData['status'] = 2;
                $returnData['msg'] = '产品不存在或者已下架';
            }
        } else {

            $returnData['status'] = 99;
            $returnData['msg'] = '未登录';
        }
        return json($returnData);
    }
    /**
     * 会员订单的确认
     * 请求实例：http://www.XX.com/app.php/order/affirm
     * 请求方式：get
     * 请求参数：id  商品购物车中的id
     * @return 操作状态信息
     */
    public function orderAffirm(){
        $info=$this->islogin();
        if($info['status']==0){
            $id=$this->request->param('id');
            $id=(string)$id;
            $userid=$info['data']['UserId'];
            $where1['Agentd']=$userid;
            $where1['id']=array('in',$id);
            $groupcart=Db::name('shoppingcart')->field('supid,supname,sum(shopprice) as sspp,sum(ConsumeIntegral) as sspv ,sum(pronum) as pronum')->where($where1)->group("supid")->select();
            if($groupcart){
                $data['groupcart']=$groupcart;
                $membername=$where['Agentd']=$userid;
                $where['status']=1;
                $relist=indexToLower(Db::name('comreceiveinfo')->where("UserId='".$membername."'")->select());
                $data['relist']=$relist;
                $data['money']=Db::name('usermsg')->field('Umoney,Pv')->where("UserId='".$membername."'")->find();
                $pricetotal=0;
                $counttotal=0;
                $pvtotal=0;
                $quantity = 0;
                $yuntotal = 0;
                $numtotal = 0;
                foreach($groupcart as $n=>$val){
                    $cart=indexToLower(Db::name('shoppingcart')->where("supid=".$val["supid"]." and agentd='".$membername."' and id in ($id)")->select());
                    foreach ($cart as $cc=>$vb){

                        $cart[$cc]['img']=getproimgbysid($vb['styleid']);
                    }
                    $data['groupcart'][$n]["voo"]=$cart;
//                    $shopcart = Db::name('shoppingcart')->field('sum(ProNum)')->group('shipping_id')->where("supid=".$val["supid"]." and agentd='".$membername."' and id in ($id)")->select();
//                    //计算运费
//                    foreach ($shopcart as $key => $value) {
////                        $yun = $this->shipping($value['sum(ProNum)'],$value['shipping_id']);
////                        $yuntotal += $yun;
//                        $numtotal += $value['sum(ProNum)'];
//                    }
//                    foreach ($cart as $k => $v){
//                        $goods=Db::name('product')->field('shipping_id,shipping_detail_id')->where("ProId=".$v["proid"])->find();
//                        if(empty($goods['shipping_detail_id'])){
//                            $count=$v['pronum'];
//                        }
//                        $quantity+=$count;
//                    }
//                    $shipping = $yuntotal;
                    $pricetotal=$pricetotal+$val["sspp"];
                    $pvtotal=$pvtotal+$val["sspv"];
                    $counttotal = $numtotal;
                }
//                $data['shipping']=$shipping;//运费
                $data['pricetotal']=$pricetotal;//总金额
                $data['pvtotal']=$pvtotal;//总积分
                $data['counttotal']=$counttotal;//件数
                $data['id']=$id;//购物车中商品id
                $returnData["data"] = $data;
                $returnData['status'] = 0;
                $returnData['msg'] = '成功';
            }else {

                $returnData['status'] = 2;
                $returnData['msg'] = '购物车为空';
            }
        } else {

            $returnData['status'] = 99;
            $returnData['msg'] = '未登录';
        }
        return json($returnData);
    }
    /**
     * 订单的生成
     * 请求实例：http://www.XX.com/app.php/order/generate
     * 请求方式：post
     * @param   addres  地址id
     * @param  shipping_method  配送方式
     * @param  id  购物车中商品id
     * @param  remarks_supid(多个 商家id)
     * @return   status 状态 |  msg 消息提示
     *
     */

    public function orderGenerate(){
        $info=$this->islogin();
//        $info['data']['User_Id']='15907720288';
//        $info['status']=0;
        if($info['status']==0){
            if($this->request->isPost()) {
                $orderData = $this->request->post();
                if(!empty($orderData["addres"])){
                    $id=$this->request->param('id');
                    $id=(string)$id;
                    $userid=$info['data']['UserId'];
                    $membername=$userid;

                    $shopcartchk=Db::name("shoppingcart")->where("status=1 and Agentd='".$membername."'  and id in ($id)")->count();
                    if($shopcartchk>0){
                        $relist=array_change_key_case(Db::name("comreceiveinfo")->where("id=".$orderData["addres"])->find());
                        $groupcart=Db::name("shoppingcart")->where("status=1 and Agentd='".$membername."'  and id in ($id)")->field('SupId as supid')->group('SupId')->select();
                        $mainorder=date("YmdHis") . rand(100000, 999999);//生成主订单号
                        $ordermainidArr=[];
                        $orderdetailidArr=[];
                        // 开始
                        $user_name = Session::get("user_name");
                        $goods_id= indexToLower(Db::name("shoppingcart")->alias("s")->join(' productstock p','s.StyleId=p.StyleId','left')->join('product pd','s.ProId=pd.ProId','left')->where("s.status=1 and s.Agentd='".$membername."'")->select());
                        $Address = Db::name("comreceiveinfo")->where("Id = $orderData[addres]")->find();
                        foreach ($groupcart as $val){
                            $where['SupId']=$val['supid'];
                            $where['Agentd']=$membername;
                            $where['id']=array('in',$id);
                            $where['status']=1;
                            $supplierProData=indexToLower(Db::name("shoppingcart")->where($where)->select());
//                            $shopcart = Db::name('shoppingcart')->field('shipping_id,sum(ProNum)')->group('shipping_id')->where("supid=".$val["supid"]." and agentd='".$membername."'  and id in ($id)")->select();
                            $goodsAmount=0;
                            $goodspv=0;
                            $givepv=0;
                            $quantity = 0;
                            $yuntotal = 0;
                            $numtotal = 0;
//                            foreach ($shopcart as $key => $value) {
//                                $yun = $this->shipping($value['sum(ProNum)'],$value['shipping_id']);
//                                $yuntotal += $yun;
//                                $numtotal += $value['sum(ProNum)'];
//                            }
                            foreach ($supplierProData as $value){
                                $goodsAmount+=$value['shopprice'];
                                $goodspv+=$value['consumeintegral'];
                                $givepv+=$value['giveintegral'];
//                                $goods=Db::name('product')->field('shipping_detail_id')->where("ProId=".$value["proid"])->find();
//                                if(empty($goods['shipping_detail_id'])){
//                                    $count=$value['pronum'];
//                                }
//                                $quantity+=$count;
                            }
//                            $shipping = $yuntotal;
                            $shipping=0;
                            $data["Freight"] = $shipping;
                            $price=$data["OrderAmount"] = $goodsAmount + $shipping;
                            //构建主订单数据--start
                            $data["SupplierId"]=$val["supid"];
                            $data["Province"]=$relist["province"];
                            $data["City"]=$relist["city"];
                            $data["County"]=$relist["county"];
                            $data["Address"]=$relist["address"];
                            $data["ReceiveName"]=$relist["receivename"];
                            $data["UserTel"]=$relist["mobile"];
                            //$data["ShippingMethod"]=$orderData["shipping_method"];
                            $data["OuterOrderId"]=$mainorder;
                            $data["InnerOrderId"]=$mainorder.'-'.$val["supid"];
                            $data["GoodsAmount"]=$goodsAmount;
                            $data["UserId"]=$membername;
                            $mes_index="remarks_".$val["supid"];
                            $data["Message"]=$orderData[$mes_index];
                            $data["ConsumeIntegral"]=$goodspv;  //抵扣积分
                            $data["GiveIntegral"]=0;
                            $data["UseUmoney"]=0;
                            $data["Status"]=1; //普通订单 订单直接为未付款状态
                            $data['OrderType']=1;
                            $data["AddDate"]=date('Y-m-d H:i:s',time());
                            $ordermainidArr[]=Db::name("ordermain")->insert($data);//添加数据到订单主表
                            //构建订单详情的数据--start
                            foreach ($supplierProData as $v){//根据购物车中的供应商id和主订单生成订单详情
                                $datade["ProId"]=$v["proid"];
                                $datade["StyleId"]=$v["styleid"];
                                $datade["proNum"]=$v["pronum"];
                                $datade["ProName"]=$v["proname"];
                                $datade["StyleName"]=$v["stylename"];
                                $datade["Unit"]=$v["unit"];
                                $datade["SupplierId"]=$v["supid"];
                                $datade["InnerOrderId"]=$mainorder.'-'.$val["supid"];
                                $datade["UserId"]=$membername;
                                $styleData=Db::name("productstock")->where("styleid=".$v["styleid"])->field('Proid as proid')->find();
                                if($styleData){
                                    $productData=Db::name("product")->where("proid=".$styleData['proid'])->field('VipPrice,ConsumeIntegral,BalancePrice,GiveIntegral')->find();
                                    if(empty($productData)){
                                        $returnData['status']=0;
                                        $returnData['msg']='ID为('.$styleData['proid'].')的商品不存在！';
                                        return json($returnData);
                                    }
                                }else{
                                    $returnData['status']=0;
                                    $returnData['msg']='商品规格('.$v["styleid"].')不存在！';
                                    return json($returnData);
                                }
                                $datade["Price"]=$productData['VipPrice'];
                                $datade["ConsumeIntegral"]=$productData['ConsumeIntegral'];
                                $datade["GiveIntegral"]=$productData['GiveIntegral'];
                                $datade["BalancePrice"]=$productData['BalancePrice'];
                                $datade["AddDate"]=date('Y-m-d H:i:s',time());
                                $orderdetailidArr[]=Db::name("orderdetail")->insert($datade);//添加数据到订单详情表

                                //去库存的操作
                                $object=Factory::instance()->getObjectInstance('account');
                                $stockData=$object->stockAction($v["styleid"],0-$v["pronum"],0-$v["pronum"],0);
                                if($stockData['status']==1){
                                    return json($stockData);
                                }
                            }
                            //构建订单详情的数据--end
                        }
                        Db::name('shoppingcart')->where("status=1 and Agentd='".$membername."'  and id in ($id)")->delete();
                        $redata=$this->accountpayoutVerify($userid,$mainorder);
                        if($redata['status']==0){
                            $returnData["data"]['accountpay']=0;
                        }else{
                            $returnData["data"]['accountpay']=1;
                        }
                        $returnData["data"]['orderno']=$mainorder;
                        $returnData["data"]['numprice']=$price;
                        $returnData['status']=0;
                        $returnData['msg']='生成订单成功！';
                    }else{

                        $returnData['status']=4;
                        $returnData['msg']='购物车为空！';
                    }
                }else{

                    $returnData['status']=3;
                    $returnData['msg']='收货地址不能为空！';
                }
            }else{

                $returnData['status']=2;
                $returnData['msg']='数据提交方式错误！';
            }
        }else{

            $returnData['status']=99;
            $returnData['msg']='未登录';
        }
        return json($returnData);
    }

    /** 计算运费
     * @param $quantity 购物车商品总数量
     * @param $supid 店铺运费模板ID
     * @return mixed 运费金额
     */
    public function shipping($quantity,$supid){
        $where["shipping_temp_id"] = $supid;
        $where["enabled"] = 1;
        $shipping_list = Db::name('shipping_templates')->where($where)->order('sort_order')->find();
        $count=$quantity-1;
        $surplus=$count*$shipping_list['step'];
        $shipping_money=$shipping_list['initail']+$surplus;
        return $shipping_money;
    }

    /**
     * 会员订单支付
     * 请求实例：http://www.XX.com/app.php/order/pay?type=accpunt
     * 请求方式：post或者get
     * @param type 支付类型
     *      参数值：account   账户余额支付
     * @param  orderno 订单号
     * @param LevIIPwd 支付密码
     * @param ordertype    outer主订单   inner子订单
     * @return 操作状态信息
     */

    public function orderPay(){
        $info=$this->islogin();
//        $info['data']['UserId']='15907720288';
//        $info['status']=0;
        if($info['status']==0){
            $type=$this->request->param('type');//获取支付类型 account 账户余额支付
            if($type=='account'){
                $method='order'.ucfirst($type).'Pay';
                $returnData=$this->$method($info);
            }else{
                $method='order'.ucfirst($type);
                $returnData=$this->$method($info);
            }
        }else{
            $returnData['status']=99;
            $returnData['msg']='未登录';
        }
        return json($returnData);
    }
    /**
     * 会员订单账户余额支付
     */
    private function orderAccountPay($info){//账户余额支付
        $payword=$this->request->param('LevIIPwd');
        if(!empty($payword)) {
            $paywords = md5($payword);
        }else{
            $returnData2['status']=1;
            $returnData2['msg']='请输入支付密码';
            return json($returnData2);
        }
        $orderno=$this->request->param('orderno');
        $ordertype=$this->request->param('ordertype');
        $userid=$info['data']['UserId'];
        $membername=$userid;
        $where['UserId']=$membername;
        $userinfoxx=Db::name('usermsg')->where($where)->field('Umoney,Pv,LevIIPwd')->find();
        $userinfoxx['Umoney']=empty($userinfoxx['Umoney'])?0:$userinfoxx['Umoney'];
        $userinfoxx['Pv']=empty($userinfoxx['Pv'])?0:$userinfoxx['Pv'];
        if($userinfoxx){
            if(($userinfoxx['LevIIPwd']!=$paywords)&& $userinfoxx['LevIIPwd']!=''){
                $returnData2['status']=1;
                $returnData2['msg']='支付密码错误';
            }elseif($userinfoxx['LevIIPwd']==''){
                $returnData2['status']=555;
                $returnData2['msg']='你没有密码，请到往会员中心设置支付密码';
            } else {
                Db::startTrans();
                try{
                    $object = Factory::instance()->getObjectInstance('account');

                    if ($ordertype == 'outer') {//主订单号
                        $count= Db::name('ordermain')->where("OuterOrderId='" . $orderno . "' and status=1")->count();
                        if($count>0){
                            $useumoney = Db::name('ordermain')->where("OuterOrderId='" . $orderno . "'")->sum('GoodsAmount');//汇总所需扣除的购物币总额
//                    $usegoodspv = Db::name('ordermain')->where("OuterOrderId='" . $orderno . "'")->sum('ConsumeIntegral');//汇总所需扣除的积分总额
//                    $returnData = $this->accountVerify($userinfoxx, $useumoney, $usegoodspv);
                            $returnData = $this->accountVerify($userinfoxx, $useumoney);
                            if ($returnData['status'] == 0) {
                                $orderData = Db::name('ordermain')->where("OuterOrderId='" . $orderno . "'")->field('InnerOrderId')->select();

                                foreach ($orderData as $k => $val) {
                                    $returnDataa = $this->accountAction($val['InnerOrderId'], $membername, $object);

                                    if ($returnDataa['status'] == 0) {
                                        Db::commit();
                                        $returnData2=$returnDataa;
                                    }else{
                                        Db::rollback();
                                    }
                                }
                            } else {
                                Db::rollback();
                                $returnData2 = $returnData;
                            }
                        }else{
                            $returnData2['status']=3;
                            $returnData2['msg']='该订单已支付';
                        }
                    } elseif ($ordertype == 'inner') {//子订单号
                        $condition['InnerOrderId'] = $orderno;
                        $condition['Status'] = 1;
                        $orderRecord = Db::name('ordermain')->where($condition)->field('GoodsAmount,ConsumeIntegral')->find();
                        if($orderRecord){
                            $useumoney = $orderRecord['GoodsAmount'];//计算需要扣除的购物币的总金额
                            //$usegoodspv = $orderRecord['ConsumeIntegral'];//计算需要扣除的消费积分汇总
//                    $returnData = $this->accountVerify($userinfoxx, $useumoney, $usegoodspv);
                            $returnData = $this->accountVerify($userinfoxx, $useumoney);
                            if ($returnData['status'] == 0) {
                                $returnDataa = $this->accountAction($orderno, $membername, $object);
                                if ($returnDataa['status'] == 0) {
                                    Db::commit();
                                    $returnData2=$returnDataa;
                                }else{
                                    Db::rollback();
                                }
                            } else {
                                Db::rollback();
                                $returnData2 = $returnData;
                            }
                        }else{
                            $returnData2['status']=3;
                            $returnData2['msg']='该订单已支付';
                        }
                    }
                }catch ( \Exception $e){
                    Db::rollback();
                    $returnData2['status']=1;
                    $returnData2['msg']='支付失败';
                }
            }
        }else{
            $returnData2['status']=1;
            $returnData2['msg']='会员不存在';
        }
        return $returnData2;
    }

    private function  orderWeixin($info){
        $orderno=$this->request->param('orderno');
        $ordertype=$this->request->param('ordertype');
        $membername =$info['data']['UserId'];;
        $where['UserId'] = $membername;
        $userinfoxx = Db::name('usermsg')->where($where)->field('Umoney,Pv')->find();
        if ($userinfoxx) {
            if($ordertype=='outer'){//主订单号
                $orderData=Db::name('ordermain')->where("OuterOrderId='".$orderno."'")->field('InnerOrderId')->select();
                $useumoney = 0;
                $usegoodspv = 0;
                $goodname='';
                foreach ($orderData as $key => $value) {
                    $condition['InnerOrderId'] = $value['InnerOrderId'];
                    $condition['Status'] = 1;
                    $orderRecord = Db::name('ordermain')->where($condition)->field('Freight,OuterOrderId,InnerOrderId,GoodsAmount,ConsumeIntegral,Province,City,County,Address,ReceiveName,UserTel')->find();
                    if ($orderRecord) {
                        $useumoney = $useumoney + $orderRecord['GoodsAmount']+$orderRecord['Freight'];//计算需要扣除的购物币的总金额
                        $usegoodspv = $usegoodspv + $orderRecord['ConsumeIntegral'];//计算需要扣除的消费积分汇总
                        $orderid = $orderRecord['OuterOrderId'] . '-outer';
                        $addre=$orderRecord['Province'].$orderRecord['City'].$orderRecord['County'].$orderRecord['Address'];
                        $ordernonew=$orderRecord['InnerOrderId'];
                        $ReceiveName=$orderRecord['ReceiveName'];
                        $UserTel=$orderRecord['UserTel'];
                        $goodnames=Db::name('orderdetail')->field('ProName')->where('InnerOrderId='.$value['InnerOrderId'])->select();
                        foreach($goodnames as $k=>$v){
                            $goodname.=$v['ProName'].'/';
                        }
                        $goodname=rtrim($goodname,'/');
                    } else {
                        $returndata['status']=1;
                        $returndata['msg']='订单已支付';
                        $returndata['data']=array();
                        return json($returndata);
                    }
                }
            }elseif($ordertype=='inner'){//子订单号
                $condition['InnerOrderId'] = $orderno;
                $condition['Status'] = 1;
                $orderRecord = Db::name('ordermain')->where($condition)->field('Freight,OuterOrderId,InnerOrderId,GoodsAmount,ConsumeIntegral,Province,City,County,Address,ReceiveName,UserTel')->find();

                if ($orderRecord) {
                    $useumoney = $orderRecord['GoodsAmount']+$orderRecord['Freight'];//计算需要扣除的购物币的总金额
                    $usegoodspv = $orderRecord['ConsumeIntegral'];//计算需要扣除的消费积分汇总
                    $orderid = $orderRecord['InnerOrderId'];
                    $ordernonew=$orderRecord['InnerOrderId'];
                    $addre=$orderRecord['Province'].$orderRecord['City'].$orderRecord['County'].$orderRecord['Address'];
                    $ReceiveName=$orderRecord['ReceiveName'];
                    $UserTel=$orderRecord['UserTel'];
                    $goodnames=Db::name('orderdetail')->field('ProName')->where('InnerOrderId='.$orderno)->find();
                    $goodname=$goodnames['ProName'];

                } else {
                    $returndata['status']=3;
                    $returndata['msg']='订单已支付';
                    $returndata['data']=array();
                    return json($returndata);
                }
            }
            if($userinfoxx['Pv']<$usegoodspv){
                $returndata['status']=2;
                $returndata['msg']='积分不足';
                $returndata['data']=array();
                return json($returndata);
            }

        }else{
            $returndata['status']=1;
            $returndata['msg']='帐号不存在';
            $returndata['data']=array();
            return json($returndata);
        }
        require_once EXTEND_PATH . 'wxpayh5/lib/WxPay.Api.php';
        require_once EXTEND_PATH . 'wxpayh5/WxPay.NativePay.php';
        require_once EXTEND_PATH . 'wxpayh5/lib/WxPay.Config.php';

        $input = new \WxPayUnifiedOrder();
        $input->SetBody("订单支付");
        $input->SetAttach("test");
        $input->SetOut_trade_no($orderid);
        $input->SetTotal_fee($useumoney*100);
        $input->SetTime_start(date("YmdHis"));
        $input->SetTime_expire(date("YmdHis", time() + 600));
        $input->SetGoods_tag("订单支付");
        $input->SetNotify_url(\WxPayConfig::NOTIFY_URL);
        $input->SetTrade_type("APP");
        $order = \WxPayApi::unifiedOrder($input);
        if($order['return_code']=='FAIL'){
            $returndata['status']=1;
            $returndata['msg']=$order['return_msg'];
            $returndata['data']=array();
        }else{
            $object= new \WxPayApi();
            $info = array();
            $info['appid'] = \WxPayConfig::APPID;
            $info['partnerid'] = \WxPayConfig::MCHID;
            $info['noncestr'] =  $object->getNonceStr();//生成随机数,
            $info['timestamp'] = time();
            $info['prepayid'] = $order['prepay_id'];
            $info['sign'] = $input->makeSign($info);//生成签名
            $returndata['status']=0;
            $returndata['msg']='成功';
            $returndata['data']=$info;
        }
        return ($returndata);

    }

    /**
     * 验证账户可用购物币和积分是否充足
     */

    private function accountVerify($userinfoxx,$useumoney,$usegoodspv=null){
        if($useumoney>$userinfoxx["Umoney"]){
            $returnData['status']=1;
            $returnData['msg']='购物币不足！';
            return $returnData;
        }
//        if($usegoodspv>$userinfoxx["Pv"]){
//            $returnData['status']=2;
//            $returnData['msg']='消费积分不足！';
//            return $returnData;
//        }

        return ['status'=>0,'余额充足'];
    }
    /**
     * 账户余额支付时修改订单和库存的方法
     * @param $orderno  订单号
     * @param $userinfoxx   会员信息
     * @param $useumoney    应扣的购物币
     * @param $usegoodspv   应扣的消费积分
     * @param $membername   会员名称
     * @return mixed
     */
    private function accountAction($orderno,$membername,$object){
        $orderfk=array_change_key_case(Db::name('ordermain')->where("innerorderid='".$orderno."'")->find());
        if($orderfk["status"]==1){
            $client='APP端';
            $accountData=array(
                'account_umoney'=>array(
                    'umoney'=>0-$orderfk["goodsamount"]-$orderfk["freight"],
                    'flowtype'=>'扣币',
                    'memo'=>"({$client})购物扣币[订单号：".$orderno."]"
                ),
//                'account_goodspv'=>array(
//                    'goodspv'=>0-$orderfk["consumeintegral"],
//                    'typename'=>'扣积分',
//                    'memo'=>"({$client})购物扣积分[订单号：".$orderno."]"
//                ),
                'userid'=>$membername,
                'formwho'=>$membername
            );
            //改变账户余额
            $accountData=$object->accountAction($accountData);

            if($accountData['status']==0){
                //改变订单状态
                $orderData=$object->orderAction($orderno,4);

                if($orderData['status']==0){
                    //改变库存状态
                    $orderdetaillist=indexToLower(Db::name('orderdetail')->where("innerorderid='".$orderno."'")->select());
//                    var_dump($orderdetaillist);exit;
                    foreach($orderdetaillist as $n=>$v){
                        //未付款库存减少，未发货库存增加
                        $object->stockAction($v["styleid"],0,0+$v["pronum"],0-$v["pronum"]);
                        //更新商品表中销售数量字段
                        $sql='update product set prosum=prosum+'.$v["pronum"].' where ProId='.$v["proid"];
                        Db::execute($sql);
                        //更新订单详情中产品的结算价及分润数据并汇总到订单主表中
                        //$object->updateOrderDetail($v['proid'],$v['id'],$orderno);
                    }
                }else{
                    $returnData=$orderData;
                    return $returnData;
                }
            }else{
                $returnData=$accountData;
                return $returnData;
            }

            $returnData['status']=0;
            $returnData['msg']='订单支付成功！';
            return $returnData;
        }else{
            $returnData['status']=1;
            $returnData['msg']='订单已支付！';
            return $returnData;
        }
    }


    /**
     * 会员订单列表
     * 请求实例：http://www.XX.com/app.php/order/list
     * 请求方式：get
     * 请求参数：orderlx 1代付款 2待发货  3 待收货  0 所有订单
     * @return 操作状态信息
     */
    public function orderList()
    {
        $info = $this->islogin();
//        $info['data']['User_Id']='15907720288';
//        $info['status']=0;
        if ($info['status'] == 0) {
            $condition['UserId'] = $info['data']['UserId'];
            $orderlx = $this->request->param('orderlx');

            if($this->request->param('page')){
                $page = $this->request->param('page');
            }else{
                $page=1;
            }
            if ($orderlx != 4) {
                if ($orderlx == 1) {
                    $condition['Status'] = $orderlx;
                } elseif ($orderlx == 2) {
                    $condition['Status'] = array(array('egt', 2), array('lt', 4));
                } elseif ($orderlx == 3) {
//                    $condition['Status'] = array(array('eq', 10), array('eq', 20), 'or');
                    $condition['Status'] = array('eq', 4);
                }
            }
            $list = Db::name('ordermain')
                ->field('ID,goodsamount,orderamount,Freight,supplierid,expcompanyid,awbno,deliverdate,Status,AddDate,InnerOrderId,Message,ReceiveName,UserTel,Province,City,County,Address,AdminMessage')
                ->where($condition)
                ->order("id desc")
                ->limit(($page-1)*10,10)
                ->select();

            $data = [];
            if(!empty($list)){
                foreach ($list as $n => $val) {
                    $data[$n] = $val = array_change_key_case($val);
                    $data[$n]['supname']=getsuppliername($val['supplierid']);
                    $data[$n]['wuliudanhao']='11111';
                    $data[$n]['wuliubianma']='sadad';
                    if($data[$n]['status']==1){
                        $data[$n]['staname']='待付款';
                    }elseif($data[$n]['status']==2){
                        $data[$n]['staname']='已付款';
                    }elseif($data[$n]['status']==3){
                        $data[$n]['staname']='配货中';
                    }elseif($data[$n]['status']==4){
                        $data[$n]['staname']='已发货';
                    }elseif($data[$n]['status']==8){
                        $data[$n]['staname']='已收货';
                    }elseif($data[$n]['status']==10){
                        $data[$n]['staname']='已取消';
                    }elseif($data[$n]['status']==15){
                        $data[$n]['staname']='用户申请取消';
                    }elseif($data[$n]['status']==20){
                        $data[$n]['staname']='后台取消';
                    }
                    $list=indexToLower(Db::name('orderdetail')->field('ID,styleid,proid,price,pronum,proname,stylename')->where("innerorderid='" . $val["innerorderid"] . "'")->select());
                    foreach ($list as $k=>$v){
                        $list[$k]['img']=getproimgbysid($v['styleid']);
                    }
                    $data[$n]["voo"] =$list   ;
                }
                $returnData["data"] = $data;
                $returnData['status'] = 0;
                $returnData['msg'] = '成功';
            }else{
                $returnData['status'] = 1;
                $returnData['msg'] = '暂无数据';
            }
        }else{
            $returnData['status'] = 99;
            $returnData['msg'] = '未登录';
        }
        return json($returnData);
    }


    /**
     * 会员订单处理
     * 请求实例：http://www.XX.com/app.php/order/action?action=cancelorder&orderno=20170316150155764233
     * 请求实例：http://www.XX.com/app.php/order/action?action=orderok&orderno=20170316150155764233
     * @param action 对订单的操作
     *          cancelorder 未付款取消订单
     *          orderok     确认收货
     * @param orderno 订单号
     * @return 操作状态信息
     */
    public function orderAction(){
        $info = $this->islogin();
//        $info['data']['User_Id']='15907720288';
//        $info['status']=0;
        if ($info['status'] == 0) {
            $action=$this->request->param('action');
            $orderno=$this->request->param('orderno');
            if($action=='cancelorder'){//未付款取消订单

                $where["InnerOrderId"] = $orderno;
                $order_info = Db::name('ordermain')->where($where)->find();
                //改变库存状态，订单取消库存增加
                $orderdetaillist=indexToLower(Db::name('orderdetail')->where("innerorderid='".$orderno."'")->select());
                foreach($orderdetaillist as $n=>$v){
                    $pcount=array_change_key_case(Db::name('productstock')->where("styleid=".$v["styleid"])->field('kucun,kucunweifukuan')->find());
                    $datas["Kucun"]=0+$pcount["kucun"]+$v["pronum"];
                    $datas["kucunWeifukuan"]=0+$pcount["kucunweifukuan"]+$v["pronum"];
                    Db::name('productstock')->where("styleid=".$v["styleid"])->update($datas);
                }
                $data["Status"]=10;
            }elseif($action=='orderok'){ //确认收货
                //同步调用久零的取消订单接口
                $where["InnerOrderId"] = $orderno;
                $order_info = Db::name('ordermain')->where($where)->find();
                $terminal_type='APP';//获取终端类型
                $data["Status"]=8;
                $data["ReceiveDate"]=date('Y-m-d H:i:s',time());
                //供应商自动结算
                $condition['InnerOrderId']=$orderno;
                $countprice=Db::name('orderdetail')->where($condition)->sum('balanceprice*pronum');
                $orderData=Db::name('orderdetail')->where($condition)->field('supplierid')->find();
                $supplierData=Db::name('supplier')->where('ID='.$orderData['supplierid'])->field('Account')->find();
                $yuaccount=$updataData['Account']=$supplierData['Account']+$countprice;
                Db::name('supplier')->where('ID='.$orderData['supplierid'])->update($updataData);

                //在购物币消费记录表里添加记录
                $recordData["SupplierId"]=$orderData['supplierid'];
                $recordData["FlowType"]="转入";
                $recordData["Amount"]=(0+$countprice);
                $recordData["Balance"]=$yuaccount; //账户余额
                $recordData["FromWho"]=$this->username;
                $recordData["OrderNo"]=$orderno;
                $recordData["Memo"]="(".$terminal_type."端)供应商账户转入";
                $recordData["AddDate"]=date('Y-m-d H:i:s',time());
                Db::name('supplieraccountrecord')->insert($recordData);
            }
            if(!empty($data)&&!empty($orderno)){
                $where['InnerOrderId']=$orderno;
                Db::name('ordermain')->where($where)->update($data);
                $returnData['status']=0;
                $returnData['msg']='操作成功';
            }else{
                $returnData['status']=2;
                $returnData['msg']='操作失败';
            }
        }else{
            $returnData['status']=99;
            $returnData['msg']='未登录';
        }
        return json($returnData);
    }

    /**
     *删除会员订单
     * 请求实例：http://www.XX.com/app.php/order/del?orderno=20170316150155764233
     * @param orderno 订单号
     */
    public function orderdelete()
    {
        $info = $this->islogin();
//        $info['data']['User_Id']='15907720288';
//        $info['status']=0;
        if ($info['status'] == 0) {
//            var_dump(111);exit;
            $where['InnerOrderId'] = $this->request->param('orderno');
            $where['UserId'] = $info['data']['UserId'];
//            var_dump($where);exit;
            $mainorder = Db::name('ordermain')->where($where)->delete();
            if(!$mainorder){
                $returnData['status'] = 1;
                $returnData['msg'] = '订单不存在';
                return json($returnData);
            }
            $detailorder = Db::name('orderdetail')->where($where)->delete();
            if(!$detailorder){
                $returnData['status'] = 1;
                $returnData['msg'] = '订单不存在';
                return json($returnData);
            }
            $returnData['status'] = 0;
            $returnData['msg'] = '删除成功';
        } else {
            $returnData['status'] = 99;
            $returnData['msg'] = '未登录';
        }
        return json($returnData);
    }

    /**
     * 获取快递信息接口
     * 请求实例：http://www.XX.com/app.php/order/wuliu
     * $param type 物流编码
     * $param no 订单号
     */
    public function getwuliumsg(){
        $type=$this->request->param('type');
        $no=$this->request->param('no');
        $url='http://m.kuaidi100.com/query?type='.$type.'&postid='.$no;
        $data=@file_get_contents($url);   //屏蔽此处报错信息
        $data = json_decode($data,true);
        if($data["message"] != "ok"){
            $returnData["status"] = 1;
            $returnData["msg"] = "查询过程发生错误";
        }elseif($data["message"] == "ok" && empty($data["data"])){
            $returnData["status"] = 1;
            $returnData["msg"] = "没有查到快递信息";
        }elseif($data["message"] == "ok" && !empty($data["data"])){
            //如果查询到信息，对信息做二次处理，去掉无用数据
            foreach ($data["data"] as $key => $val){
                $temp_array = array();
                $temp_array["time"] = $val["time"];
                $temp_array["context"] = str_replace("]","] ",$val["context"]);
                $returnData["data"][] = $temp_array;

            }
            $returnData["status"] = 0;
            $returnData["msg"] = "快递信息";
        }
        return json($returnData);
    }


    /**
     * 验证账户可用购物币和积分是否充足
     * 请求实例：http://www.XX.com/app.php/order/checkmoney
     */

    public function accountpayVerify(){
        $info=$this->islogin();
        if($info['status']==0){
            $orderno=$this->request->param('orderno');
            $userid=$info['data']['UserId'];
            $membername=$userid;
            $where['UserId']=$membername;
            $userinfoxx=Db::name('usermsg')->where($where)->field('Umoney,Pv,LevIIPwd')->find();
            $userinfoxx['Umoney']=empty($userinfoxx['Umoney'])?0:$userinfoxx['Umoney'];
            if($userinfoxx){
                $condition['InnerOrderId'] = $orderno;
                $orderRecord = Db::name('ordermain')->where($condition)->field('OrderAmount')->find();
                if($orderRecord){
                    $useumoney = $orderRecord['OrderAmount'];
                    if($useumoney>$userinfoxx["Umoney"]){
                        $returnData2['status']=1;
                        $returnData2['msg']='余额不足！';
                    }else{
                        $returnData2['status']=0;
                        $returnData2['msg']='余额充足！';
                    }
                }else{
                    $returnData2['status']=3;
                    $returnData2['msg']='订单号不存在';
                }

            }else{
                $returnData2['status']=4;
                $returnData2['msg']='会员不存在';
            }
        }else{
            $returnData2['status']=99;
            $returnData2['msg']='未登录';
        }
        return json($returnData2);
    }


    /**
     * 生产订单时购物币和积分是否充足
     * 请求实例：http://www.XX.com/app.php/order/checkmoney
     */

    private function accountpayoutVerify($userid,$mainorder){
        $orderno=$mainorder;
        $membername=$userid;
        $where['UserId']=$membername;
        $userinfoxx=Db::name('usermsg')->where($where)->field('Umoney,Pv,LevIIPwd')->find();
        $userinfoxx['Umoney']=empty($userinfoxx['Umoney'])?0:$userinfoxx['Umoney'];
        if($userinfoxx){
            $count= Db::name('ordermain')->where("OuterOrderId='" . $orderno . "'")->count();
            if($count>0){
                $useumoney = Db::name('ordermain')->where("OuterOrderId='" . $orderno . "'")->sum('GoodsAmount');//汇总所需扣除的购物币总额
                if($useumoney>$userinfoxx["Umoney"]){
                    $returnData2['status']=1;
                    $returnData2['msg']='余额不足！';
                }else{
                    $returnData2['status']=0;
                    $returnData2['msg']='余额充足！';
                }
            }else{
                $returnData2['status']=3;
                $returnData2['msg']='订单不存在';
            }
        }else{
            $returnData2['status']=0;
            $returnData2['msg']='会员不存在';
        }

        return $returnData2;
    }

}
