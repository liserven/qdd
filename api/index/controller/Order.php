<?php
/**
 * Created by 赵晓凡
 * User: zhaoxiaofan
 * Date: 2017/2/17
 * Time: 9:30
 */
namespace api\index\controller;

use think\Config;
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
     * 会员订单的确认
     * 请求实例：http://www.XX.com/api.php/order/affirm
     * 请求方式：get
     * 请求参数：无
     * @return 操作状态信息
     */
    public function orderAffirm()
    {
        if ($this->loginStatus) {
            $membername=$where['Agentd']=$this->username;
            $where['status']=1;
            $groupcart=Db::name('shoppingcart')->field('supid,sum(shopprice) as sspp,sum(ConsumeIntegral) as sspv,sum(GiveIntegral) as ssgpv')->where($where)->group("supid")->select();
            //var_dump($groupcart);exit;
            if($groupcart){
                $sum_money=0;
                $goods_num=0;
                $sum_integral=0;
                $sum_giveintegral=0;
                $is_show=0;
                $supplerids='';
                foreach($groupcart as $n=>$val){
                    $fields='supid,styleid,proid,shopprice,consumeintegral,giveintegral,pronum,proname,is_pode';
                    $groupcart[$n]["voo"]=indexToLower(Db::name('shoppingcart')->where("supid=".$val["supid"]." and status=1 and  agentd='".$membername."'")->field($fields)->select());                 $groupcart[$n]['deliverytype']=Db::name('deliverytype')->field('name,id')->where('supplierid='.$val['supid'].' and is_on=1')->select();
                    $groupcart[$n]['deliverytype']=Db::name('deliverytype')->field('name,id')->where('supplierid='.$val['supid'].' and is_on=1')->select();
                    $sum_money=$sum_money+$val["sspp"];
                    $sum_integral=$sum_integral+$val["sspv"];
                    $sum_giveintegral=$sum_giveintegral+$val['ssgpv'];
                    $goods_num=$goods_num+count($groupcart[$n]["voo"]);
                    foreach ($groupcart[$n]["voo"] as $k => $v){
                        //获取商品所在店铺信息
                        $condtion['ID'] = $v['supid'];
                        if($v['is_pode']==1){
                            $is_show=1;
                        }else{
                            $is_show=2;
                        }
                        $supdata = Db::name('supplier')->where($condtion)->field('name')->find();
                        //获取商品所对应的规格信息
                        $cond['StyleId'] = $v['styleid'];
                        $prostock = Db::name('productstock')->where($cond)->field('stylename')->find();
                        //获取产品图片信息
                        $where2['ProId'] = $v['proid'];
                        $prodata = Db::name('product')->where($where2)->field('proimg,qiqiuproimgpath')->find();
                        if(is_onqiniu()==true){
                            $groupcart[$n]["voo"][$k]['img']=$prodata['qiqiuproimgpath'];
                        }else{
                            if(strpos($prodata['proimg'],'http://')!==false){
                                $groupcart[$n]["voo"][$k]['img']=$prodata['proimg'];
                                $groupcart[$n]["voo"][$k]['proimg']=$prodata['proimg'];
                            }else{
                                $groupcart[$n]["voo"][$k]['img']=config('IMAGE_DOMAIN_NAME').'/public/Upload/cpimg/'.$prodata['proimg'];
                                $groupcart[$n]["voo"][$k]['proimg']=config('IMAGE_DOMAIN_NAME').'/public/Upload/cpimg/'.$prodata['proimg'];
                            }
//                            $groupcart[$n]["voo"][$k]['img']=$prodata['proimg'];
//                            $groupcart[$n]["voo"][$k]['img']=config('IMAGE_DOMAIN_NAME').'/public/Upload/cpimg/'.$prodata['proimg'];
                        }
//                        $groupcart[$n]["voo"][$k]['proimg'] = config('IMAGE_DOMAIN_NAME') . '/public/Upload/cpimg/' . $prodata['proimg'];
//                        $groupcart[$n]["voo"][$k]['proimg'] = $prodata['proimg'];
                        //
                        $groupcart[$n]['suppliername'] = $supdata['name'];
                        $groupcart[$n]["voo"][$k]['stylename'] = $prostock['stylename'];
                        $groupcart[$n]["voo"][$k]['shopprice']=$v['shopprice']/$v['pronum'];
                        $groupcart[$n]["voo"][$k]['consumeintegral']=intval($v['consumeintegral']/$v['pronum']);
                        $groupcart[$n]["voo"][$k]['giveintegral']=intval($v['giveintegral']/$v['pronum']);
                    }
                    $supplerids.=$val['supid'].',';
                }
                $supplerids=rtrim($supplerids,',');
//                var_dump($supplerids);exit;
                //获取会员的收货地址列表
                $where1['UserId'] = $this->username;
                $where1['IsDefault'] = 1;
                $address = indexToLower(Db::name('comreceiveinfo')->where($where1)->select());
                if(!empty($address)){
                    $addr['receivename']=$address[0]['receivename'];
                    $addr['address']=getcityname($address[0]['province']).getcityname($address[0]['city']).getcityname($address[0]['county']).$address[0]['address'];
                    $addr['id']=$address[0]['id'];
                    $addr['mobile']=$address[0]['mobile'];
                    $addr['isdefault']=1;
                }else{
                    $addr['isdefault']=0;
                }
//                $returnData['suplist'] = $groupcart;
//                $returnData['addresslist'] = $address;
//                $returnData['username'] = $this->username;
//                $returnData['sum_money'] = $sum_money;
//                $returnData['sum_integral'] = $sum_integral;
//                $returnData['sum_giveintegral'] = $sum_giveintegral;
//                $returnData['goods_num'] = $goods_num;

                $returnData["data"]['is_show'] = $is_show;
                $returnData["data"]['suplist'] = $groupcart;
                $returnData["data"]['addresslist'] = $addr;
                $returnData["data"]['username'] = $this->username;
                $returnData["data"]['sum_money'] = $sum_money;
                $returnData["data"]['sum_integral'] = $sum_integral;
                $returnData["data"]['sum_giveintegral'] = $sum_giveintegral;
                $returnData["data"]['goods_num'] = $goods_num;
                $returnData["data"]['supplierids'] = $supplerids;
//                var_dump( $returnData["data"]);exit;
                $returnData['status'] = 1;
                $returnData['msg'] = '成功';
            }else {
                $returnData["data"] = array();
                $returnData['status'] = 0;
                $returnData['msg'] = '购物车为空';
            }
        } else {
            $returnData["data"] = array();
            $returnData['status'] = 0;
            $returnData['msg'] = '未登录';
        }
        return json($returnData);
    }
    public function orderAffirmnow()
    {
        if ($this->loginStatus) {
            $membername=$this->username;
            $proid=$this->request->param('proid');
            $styleid=$this->request->param('styleid');
            $pronum=$this->request->param('pronum');
            $fields='supplierid,proid,enjoyprice,marketprice,vipprice,consumeintegral,giveintegral,ishit,proname,proimg';
            $list=Db::name('product')->field($fields)->where('IsOnSell=1 and ProId="'.$proid.'"')->find();
            if($list){
                $whereuser['UserId']=$membername;
                $usertype=Db::view('usermsg','userType')
                    ->view('usertype','discount','usertype.ID=usermsg.userType')
                    ->where($whereuser)->find();
                if(($list['ishit']==2)&&($usertype['userType']<=1)){
                    $returnData['status'] = 0;
                    $returnData['msg'] = '级别不足，请升级会员级别';
                    return json($returnData);
                }
                if($usertype['userType']==1){
                    $list['price']=$list['vipprice']*$usertype['discount'];
                    $sum_money=$list['vipprice']*$pronum*$usertype['discount'];
                }elseif($usertype['userType']>=2){
                    $list['price']=$list['enjoyprice']*$usertype['discount'];
                    $sum_money=$list['enjoyprice']*$pronum*$usertype['discount'];
                }else{
                    $list['price']=$list['marketprice']*$usertype['discount'];
                    $sum_money=$list['marketprice']*$pronum*$usertype['discount'];
                }
                $goods_num=$pronum;
                $sum_integral=$list['consumeintegral']*$pronum;
                $sum_giveintegral=$list['giveintegral']*$pronum;
                //$sum_integral=$list['ConsumeIntegral']*$pronum;
                $where1['UserId'] = $this->username;
                $where1['IsDefault'] = 1;
                $address = indexToLower(Db::name('comreceiveinfo')->where($where1)->select());
                if(!empty($address)){
                    $addr['receivename']=$address[0]['receivename'];
                    $addr['address']=getcityname($address[0]['province']).getcityname($address[0]['city']).getcityname($address[0]['county']).$address[0]['address'];
                    $addr['id']=$address[0]['id'];
                    $addr['mobile']=$address[0]['mobile'];
                    $addr['isdefault']=1;
                }else{
                    $addr['isdefault']=0;
                }
                //获取商品所在店铺信息
                $condtion['ID'] = $list['supplierid'];
                $supdata = Db::name('supplier')->where($condtion)->field('name')->find();
                //获取商品所对应的规格信息
                $cond['StyleId'] = $styleid;
                $prostock = Db::name('productstock')->where($cond)->field('stylename,stylename1')->find();
                //获取产品图片信息
                if(strpos($list['proimg'],'http://')!==false){
                    $list["voo"][0]['img']=$list['proimg'];
                    $list["voo"][0]['proimg']=$list['proimg'];
                }else{
                    $list["voo"][0]['img']=config('IMAGE_DOMAIN_NAME').'/public/Upload/cpimg/'.$list['proimg'];
                    $list["voo"][0]['proimg']=config('IMAGE_DOMAIN_NAME').'/public/Upload/cpimg/'.$list['proimg'];
                }

                $list['suppliername'] = $supdata['name'];
                $list['deliverytype']=Db::name('deliverytype')->field('name,id')->where('supplierid='.$list['supplierid'].' and is_on=1')->select();
                $list["voo"][0]['stylename'] = $prostock['stylename'].$prostock['stylename1'];
                $list["voo"][0]['shopprice']=$list['price'];
                $list["voo"][0]['consumeintegral']=$list['consumeintegral'];
                $list["voo"][0]['giveintegral']=$list['giveintegral'];

//                $returnData["data"]['is_show'] = $is_show;
                $returnData["data"]['suplist'] = $list;
                $returnData["data"]['addresslist'] = $addr;
                $returnData["data"]['username'] = $this->username;
                $returnData["data"]['sum_money'] = $sum_money;
                $returnData["data"]['sum_integral'] = $sum_integral;
                $returnData["data"]['sum_giveintegral'] = $sum_giveintegral;
                $returnData["data"]['goods_num'] = $goods_num;
//                $returnData["data"]['supplierids'] = $supplerids;
                $returnData['status'] = 1;
                $returnData['msg'] = '成功';
            }else{
                $returnData['status'] = 0;
                $returnData['msg'] = '商品不存在';
            }

        } else {
            $returnData['status'] = 0;
            $returnData['msg'] = '未登录';
        }
        return json($returnData);
    }

    /**
     * 会员订单详情
     * @return 操作状态信息
     */
    public function orderShow(){
        if($this->loginStatus){
            $orderno=$this->request->param('orderno');
            $ordertype=$this->request->param('ordertype');
            if(!empty($orderno)){
                $sum_money=0;
                $sum_integral=0;
                $sum_giveintegral=0;
                $field = "userid,province,city,county,address,message,status,expcompanyid,awbno,adminmessage,goodsamount,consumeintegral,paydate,deliverdate,receivedate,innerorderid,adddate,receivename,usertel,userintegral,Freight";
                if($ordertype=='outer'){
                    $orderlist=indexToLower(Db::name('ordermain')->where("outerorderid='".$orderno."'")->field($field)->select());
                }elseif($ordertype=='inner'){
                    $orderlist=indexToLower(Db::name('ordermain')->where("innerorderid='".$orderno."'")->field($field)->select());
                }
                if(!empty($orderlist)){
                    foreach($orderlist as $n=>$val){
                        $field = "giveintegral,proid,userid,proname,stylename,unit,consumeintegral,price,pronum,supplierid,userintegral";
                        $orderlist[$n]["voo"]=indexToLower(Db::name('orderdetail')->where("innerorderid='".$val["innerorderid"]."'")->field($field)->select());
                        foreach ($orderlist[$n]["voo"] as $k => $v){
                            //获取商品所在店铺信息
                            $condtion['ID'] = $v['supplierid'];
                            $supdata = Db::name('supplier')->where($condtion)->field('name')->find();
                            //获取产品图片信息
                            $where2['ProId'] = $v['proid'];
                            $prodata = Db::name('product')->where($where2)->field('proimg,qiqiuproimgpath')->find();
                            $orderlist[$n]['suppliername'] = $supdata['name'];
                            if(is_onqiniu()==true){
                                $orderlist[$n]["voo"][$k]['img']=$prodata['qiqiuproimgpath'];
                            }else{
                                if(strpos($prodata['proimg'],'http://')!==false){
                                    $orderlist[$n]["voo"][$k]['img']=$prodata['proimg'];
                                    $orderlist[$n]["voo"][$k]['proimg']=$prodata['proimg'];
                                }else{
                                    $orderlist[$n]["voo"][$k]['img']=config('IMAGE_DOMAIN_NAME').'/public/Upload/cpimg/'.$prodata['proimg'];
                                    $orderlist[$n]["voo"][$k]['proimg']=config('IMAGE_DOMAIN_NAME').'/public/Upload/cpimg/'.$prodata['proimg'];
                                }
                            }
                            $sum_giveintegral=$sum_giveintegral+$v['giveintegral']*$v['pronum'];

                        }

                        $sum_money=$sum_money+$val["goodsamount"]+$val["freight"];
                        $sum_integral=$sum_integral+$val["userintegral"];
                    }
                    $returnData["data"]['orderlist'] = $orderlist;
                    $returnData["data"]['mainorder'] = $orderno;
                    $returnData["data"]['sum_money'] = $sum_money;
                    $returnData["data"]['sum_integral'] = $sum_integral;
                    $returnData["data"]['sum_giveintegral'] = $sum_giveintegral;
                    $returnData["data"]['goods_num'] = count($orderlist);

                    $returnData['status']=1;
                    $returnData['msg']='数据记录';
                }else{
                    $returnData['status']=0;
                    $returnData['msg']='订单列表为空';
                }
            }else{
                $returnData['status']=0;
                $returnData['msg']='该订单不存在！';
            }
        }else{
            $returnData['data']=array();
            $returnData['status']=0;
            $returnData['msg']='未登录';
        }
        return json($returnData);


    }

    /**
     * 会员订单详情
     * @return 操作状态信息
     */
    public function orderDetail(){
        if($this->loginStatus){
            $orderno=$this->request->param('orderno');
            if(!empty($orderno)){
                $field = "userid,province,city,county,address,message,status,expcompanyid,awbno,adminmessage,orderamount,goodsamount,consumeintegral,paydate,deliverdate,receivedate,innerorderid,adddate,receivename,usertel,freight,userintegral";
                $orderinfo=array_change_key_case(Db::name('ordermain')->where("innerorderid='".$orderno."'")->field($field)->find());
                if($orderinfo){
//                    $orderinfo['province']=getcityname($orderinfo['province']);
//                    $orderinfo['city']=getcityname($orderinfo['city']);
//                    $orderinfo['county']=getcityname($orderinfo['county']);
                    $field = "proid,userid,proname,stylename,unit,consumeintegral,price,pronum,supplierid";

                    $orderinfo["voo"]=indexToLower(Db::name('orderdetail')->where("innerorderid='".$orderinfo["innerorderid"]."'")->field($field)->select());
                    foreach ($orderinfo["voo"] as $k => $v){
                        //获取商品所在店铺信息
                        $condtion['ID'] = $v['supplierid'];
                        $supdata = Db::name('supplier')->where($condtion)->field('name')->find();

                        //获取产品图片信息
                        $where2['ProId'] = $v['proid'];
                        $prodata = Db::name('product')->where($where2)->field('proimg,qiqiuproimgpath')->find();
                        $orderinfo['suppliername'] = $supdata['name'];
                        if(is_onqiniu()==true){
                            $orderinfo["voo"][$k]['img']=$prodata['qiqiuproimgpath'];
                        }else{
                            if(strpos($prodata['proimg'],'http://')!==false){
                                $orderinfo["voo"][$k]['img']=$prodata['proimg'];
                                $orderinfo["voo"][$k]['proimg']=$prodata['proimg'];
                            }else{
                                $orderinfo["voo"][$k]['img']=config('IMAGE_DOMAIN_NAME').'/public/Upload/cpimg/'.$prodata['proimg'];
                                $orderinfo["voo"][$k]['proimg']=config('IMAGE_DOMAIN_NAME').'/public/Upload/cpimg/'.$prodata['proimg'];
                            }
//                            $orderinfo["voo"][$k]['img']=config('IMAGE_DOMAIN_NAME').'/public/Upload/cpimg/'.$prodata['proimg'];
//                            $orderinfo["voo"][$k]['img']=$prodata['proimg'];
                        }
//                        $orderinfo["voo"][$k]['proimg'] = config('IMAGE_DOMAIN_NAME') . '/public/Upload/cpimg/' . $prodata['proimg'];
//                        $orderinfo["voo"][$k]['proimg'] =  $prodata['proimg'];
                    }
//                    $memberinfo=Db::name('usermsg')->where("UserId='".$orderinfo["voo"][0]['userid']."'")->field('truename')->find();
                    if(!empty($orderinfo['expcompanyid'])){
                        $expcompany=Db::name("expresscompany")->where("id=".$orderinfo['expcompanyid'])->field('expname')->find();
                        $orderinfo['expcompany']=$expcompany['expname'];
                    }else{
                        $orderinfo['expcompany']='';
                    }
//                    $orderinfo['membername'] =$memberinfo['truename'];
                    $returnData['data'] = $orderinfo;
                    $returnData['status']=1;
                    $returnData['msg']='数据记录';
                }else{
                    $returnData['status']=0;
                    $returnData['msg']='订单列表为空';
                }
            }else{
                $returnData['status']=0;
                $returnData['msg']='该订单不存在！';
            }
        }else{
            $returnData['status']=0;
            $returnData['msg']='未登录';
        }
        return json($returnData);


    }
    /**
     * 会员订单列表
     * 请求实例：http://www.XX.com/api.php/order/list
     * 请求方式：get
     * 请求参数：无
     * @return 操作状态信息
     */
    public function orderList(){
        if($this->loginStatus){
            $pagecount = 3;
            $pageTotal=0;
            $where['UserId']=$this->username;
            //$ishit=$this->request->param('ishit');
            //$where['OrderType']=$ishit;
            $keysupname= $this->request->param('keysupname');
            //根据订单id进行搜索
            if($this->request->param('keyorderid')){
                $where['InnerOrderId']=['like','%'.$this->request->param('keyorderid').'%'];
            }
            //根据订单类型进行搜索
            if($this->request->param('ordertype')){
                $where['OrderType']=$this->request->param('ordertype');
            }
            //根据订单的状态进行搜索
            if($this->request->param('status')){
                $where['Status']=$this->request->param('status');
                if($this->request->param('status')==5)
                {
                    $where['Status']=['in',[2,3,4]];
                }
            }
            //根据订单的收货人进行搜索
            if($this->request->param('keyreiciver')){
                $where['ReceiveName']=['like','%'.$this->request->param('keyreiciver').'%'];
            }
            //根据商家名称进行搜索
            if($keysupname){
                $condtion['Name']=['like','%'.$keysupname.'%'];
                $supdata = Db::name('supplier')->where($condtion)->field('id')->select();
                $temp=[];
                if($supdata){
                    foreach ($supdata as $k=>$v){
                        $temp[]=$v['id'];
                    }
                    $where['SupplierId']=['in',$temp];
                }else{
                    $where['SupplierId']=0;
                }
            }
            //根据日期进行查找
            if($this->request->param("datemin") and $this->request->param("datemax")){
                $requestdate=$this->request->param("datemax");
                $redate=date("Y-m-d",strtotime("$requestdate +1 day"));
                $where["AddDate"]=array(array('egt',$this->request->param("datemin")),array('elt',$redate),'and');
            }elseif($this->request->param("datemin")){
                $where["AddDate"]=array('egt',$this->request->param("datemin"));
            }elseif($this->request->param("datemax")){
                $requestdate=$this->request->param("datemax");
                $redate=date("Y-m-d",strtotime("$requestdate +1 day"));
                $where["AddDate"]=array('elt',$redate);
            }
            $list = Db::name('ordermain')->where($where)->field('OrderType,ID,goodsamount,orderamount,supplierid,expcompanyid,expcode,awbno,deliverdate,Status,AddDate,InnerOrderId,Message,ReceiveName,UserTel,Province,City,County,Address,AdminMessage,ConsumeIntegral,userintegral')->order("id desc")->paginate($pagecount);
//            var_dump(Db::name('ordermain')->getLastSql());exit;
//            echo Db::name('ordermain')->getLastSql();
//            exit();
            if($list){
                $pageTotal=ceil($list->total()/$pagecount);//总页数
                $data=[];
                foreach($list as $n=>$val){
                    $data[$n]=$val=array_change_key_case($val);
                    $data[$n]["voo"]=indexToLower(Db::name('orderdetail')->field('ID,styleid,proid,price,pronum,proname,stylename,SupplierId,ConsumeIntegral,innerorderid')->where("innerorderid='".$val["innerorderid"]."'")->select());
                    $totlemoney=0;
                    foreach ($data[$n]["voo"] as $k => $v){
                        //获取商品所在店铺信息
                        $condtion['ID'] = $val['supplierid'];
                        $supdata = Db::name('supplier')->where($condtion)->field('name')->find();

                        //获取产品图片信息
                        $where2['ProId'] = $v['proid'];
                        $prodata = Db::name('product')->where($where2)->field('proimg,qiqiuproimgpath')->find();
                        $data[$n]['suppliername'] = $supdata['name'];
                        if(is_onqiniu()==true){
                            $data[$n]["voo"][$k]['img']=$prodata['qiqiuproimgpath'];
                        }else{
                            if(strpos($prodata['proimg'],'http://')!==false){
                                $data[$n]["voo"][$k]['img']=$prodata['proimg'];
                                $data[$n]["voo"][$k]['proimg']=$prodata['proimg'];
                            }else{
                                $data[$n]["voo"][$k]['img']=config('IMAGE_DOMAIN_NAME').'/public/Upload/cpimg/'.$prodata['proimg'];
                                $data[$n]["voo"][$k]['proimg']=config('IMAGE_DOMAIN_NAME').'/public/Upload/cpimg/'.$prodata['proimg'];
                            }
                        }
                        $totlemoney+=$v['price']*$v['pronum'];
                    }
//                    $data[$n]['goodsnum']=count($data[$n]["voo"]);
                    $data[$n]['goodsnum']=Db::name('orderdetail')->where("innerorderid='".$val["innerorderid"]."'")->sum('proNum');
                    $data[$n]['goodsmoneytotle']=$totlemoney;
                }
                $returnData['data']=$data;
                $returnData['total']=$pageTotal;

                $returnData['data']["data_list"]=$data;
                $returnData["data"]['total']=$pageTotal;

                $returnData['status']=1;
                $returnData['msg']='数据记录';
            }else{
                $returnData['status']=0;
                $returnData['msg']='无订单';
            }
        }else{
            $returnData['status']=0;
            $returnData['msg']='未登录';
        }
        return json($returnData);
    }

    /**
     * 会员订单处理
     * 请求实例：http://www.XX.com/api.php/order/action?action=cancelorder&orderno=20170316150155764233
     * 请求实例：http://www.XX.com/api.php/order/action?action=cancelorderadmin&orderno=20170316150155764233
     * 请求实例：http://www.XX.com/api.php/order/action?action=orderok&orderno=20170316150155764233
     * @param action 对订单的操作
     *          cancelorder 未付款取消订单
     *          cancelorderadmin    已付款但还未发货用户可以申请取消订单
     *          orderok     确认收货
     * @param orderno 订单号
     * @return 操作状态信息
     */
    public function orderAction(){
        if($this->loginStatus){
            $action=$this->request->param('action');
            $orderno=$this->request->param('orderno');
            if($action=='cancelorder'){//未付款取消订单
                //改变库存状态，订单取消库存增加
                $orderdetaillist=indexToLower(Db::name('orderdetail')->where("innerorderid='".$orderno."'")->select());
                foreach($orderdetaillist as $n=>$v){
                    $pcount=array_change_key_case(Db::name('productstock')->where("styleid=".$v["styleid"])->field('kucun,kucunweifukuan')->find());
                    $datas["Kucun"]=0+$pcount["kucun"]+$v["pronum"];
                    $datas["kucunWeifukuan"]=0+$pcount["kucunweifukuan"]+$v["pronum"];
                    Db::name('productstock')->where("styleid=".$v["styleid"])->update($datas);
                }
                $data["Status"]=10;
            }elseif($action=='cancelorderadmin'){ //已付款但还未发货用户可以申请取消订单
                $data["Status"]=15;
            }elseif($action=='orderok'){ //确认收货
                $terminal_type=ismobile()?'移动端':'PC';//获取终端类型
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

                $returnData['data']=array();
                $returnData['status']=1;
                $returnData['msg']='操作成功';
            }else{
                $returnData['data']=array();
                $returnData['status']=0;
                $returnData['msg']='操作失败';
            }
        }else{
            $returnData['data']=array();
            $returnData['status']=0;
            $returnData['msg']='未登录';
        }
        return json($returnData);
    }

    /**
     * 订单的生成
     * 请求实例：http://www.XX.com/api.php/order/generate
     * 请求方式：post
     * @param   addres
     * @param  shipping_method
     * @param  remarks_supid(多个)
     * @return   status 状态 |  msg 消息提示
     *
     */

    public function orderGeneratenow(){
        if($this->loginStatus){
                $orderData = $this->request->param();
//            var_dump($orderData);exit;
                if(!empty($orderData["addres"])){
                    $object=Factory::instance()->getObjectInstance('account');
                    $membername=$this->username;
                    $relist=array_change_key_case(Db::name("comreceiveinfo")->where("id=".$orderData["addres"])->find());
                    $mainorder=date("YmdHis") . rand(100000, 999999);//生成主订单号
                    $ordermainidArr=[];
                    $orderdetailidArr=[];
                    $where['Proid']=$orderData['proid'];
                    $where['IsOnSell']=1;
                    $list=Db::name('product')->where($where)->find();
                    if($list){
                        //构建主订单数据--start
                        $whereuser['UserId']=$membername;
                        $usertype=Db::view('usermsg','userType')
                            ->view('usertype','discount','usertype.ID=usermsg.userType')
                            ->where($whereuser)->find();
                        if($usertype['userType']==1){
                            $goodsAmount=$list['VipPrice']*$orderData['pronum']*$usertype['discount'];
                            $datade["Price"] = $list['VipPrice']*$usertype['discount'];
                        }elseif($usertype['userType']>=2){
                            $goodsAmount=$list['EnjoyPrice']*$orderData['pronum']*$usertype['discount'];
                            $datade["Price"] = $list['EnjoyPrice']*$usertype['discount'];
                        }else{
                            $goodsAmount=$list['MarketPrice']*$orderData['pronum']*$usertype['discount'];
                            $datade["Price"] = $list['MarketPrice']*$usertype['discount'];
                        }
                        $idarr=Db::name('deliverytype')->field('id')->where('supplierid='.$list["SupplierId"].' and is_on=1')->select();
                        //构建主订单数据--start
                        if($idarr){
                            $shipping_index="shipping_method_".$list["SupplierId"];
                            $searchdata['addresid']=$orderData["addres"];
                            $searchdata['supplierid']=$list["SupplierId"];
                            $searchdata['type']=2;
                            $searchdata['proid']=$orderData['proid'];
                            $searchdata['prosum']=$orderData['pronum'];
                            if(empty($orderData[$shipping_index])){
                                $returnData['status']=0;
                                $returnData['msg']='请选择配送方式！';
                                return json($returnData);
                            }
                            $searchdata['id']=$orderData[$shipping_index];;
                            $freght=$object->getfrieght($searchdata);
                            if($freght['status']==0){
                                $data["Freight"]=$freght['fright'];
                                $data["FreightId"]=$orderData[$shipping_index];
                            }else{
                                $returnData['status']=0;
                                $returnData['msg']='生成运费失败！';
                                return json($returnData);
                            }
                        }else{
                            $data["Freight"]=0;
                        }
                        $data["SupplierId"]=$list["SupplierId"];
                        $data["Province"]=getcityname($relist["province"]);
                        $data["City"]=getcityname($relist["city"]);
                        $data["County"]=getcityname($relist["county"]);
                        $data["Address"]=$relist["address"];
                        $data["ReceiveName"]=$relist["receivename"];
                        $data["UserTel"]=$relist["mobile"];
                        //配送方式
                        $data["ShippingMethod1"]=1;
//                                    $data["ShippingMethod"]=1;
                        $data["OuterOrderId"]=$mainorder;
                        $data["InnerOrderId"]=$mainorder.'-'.$list["SupplierId"];
                        $data["GoodsAmount"]=$goodsAmount;
                        $data["UserId"]=$membername;
                        $mes_index="remarks_".$list["SupplierId"];
                        $data["Message"]=$orderData[$mes_index];
                        $data["ConsumeIntegral"]=0;  //抵扣积分
                        $data["UseUmoney"]=0;
                        $data["Status"]=1; //普通订单 订单直接为未付款状态
                        $data['OrderType']=1;
                        $data["AddDate"]=date('Y-m-d H:i:s',time());
                        //构建主订单数据--end
                        $cond['StyleId'] = $orderData['styleid'];
                        $prostock = Db::name('productstock')->where($cond)->find();
                        if($prostock){
                            $id=Db::name("ordermain")->insertGetId($data);//添加数据到订单主表
                            //构建订单详情的数据--start
                            $datade["ProId"]=$list["ProId"];
                            $datade["StyleId"]=$orderData["styleid"];
                            $datade["Txm"]=$prostock["Txm"];
                            $datade["proNum"]=$orderData["pronum"];
                            $datade["ProName"]=$list["ProName"];
                            $datade["StyleName"]=$prostock["StyleName"].$prostock['StyleName1'];
                            $datade["Unit"]=$list["Unit"];
                            $datade["SupplierId"]=$list["SupplierId"];
                            $datade["InnerOrderId"]=$mainorder.'-'.$list["SupplierId"];
                            $datade["UserId"]=$membername;
//                            if($usertype['userType']==1){
//                                $datade["Price"] = $list['VipPrice']*$usertype['discount'];
//                            }elseif($usertype['userType']>=2){
//                                $datade["Price"] = $list['EnjoyPrice']*$usertype['discount'];
//                            }else{
//                                $datade["Price"] = $list['MarketPrice']*$usertype['discount'];
//                            }
//                            $datade["Price"]=$list['VipPrice'];
                            $datade["BalancePrice"]=$list['BalancePrice'];
                            $datade["AddDate"]=date('Y-m-d H:i:s',time());
                            $orderdetailidArr[]=Db::name("orderdetail")->insert($datade);//添加数据到订单详情表
                            //去库存的操作
                            $stockData=$object->stockAction($orderData["styleid"],0-$orderData["pronum"],0-$orderData["pronum"],0);
                            //更新销售数量
                            $sql='update product set prosum=prosum+'.$orderData["pronum"].' where proid="'.$orderData['proid'].'"';
                            Db::execute($sql);
                            if($stockData['status']==0){
                                //库存不足时进行回滚操作
                                foreach ($ordermainidArr as $orderMainVal){
                                    Db::name("ordermain")->where('Id='.$orderMainVal)->delete();
                                }
                                foreach ($orderdetailidArr as $orderDeVal){
                                    Db::name("orderdetail")->where('Id='.$orderDeVal)->delete();
                                }
                                $returnData['status']=0;
                                $returnData['msg']=$stockData['status']['msg'];
                                return json($returnData);
                            }
                            //构建订单详情的数据--end
                            $returnData["data"]['orderno']=$mainorder;
                            $returnData['status']=2;
                            $returnData['msg']='生成订单成功！';
                        }else{
                            $returnData['status']=0;
                            $returnData['msg']='商品规格('.$orderData["styleid"].')不存在！';
                            return json($returnData);
                        }
                    }else{
                        $returnData['status']=0;
                        $returnData['msg']='该商品已下架！';
                    }

                }else{
                    $returnData['status']=0;
                    $returnData['msg']='收货地址不能为空！';
                }
        }else{
            $returnData['status']=0;
            $returnData['msg']='未登录';
        }
        return json($returnData);
    }
    public function orderGenerate(){
        if($this->loginStatus){
            if($this->request->isPost()) {
                $orderData = $this->request->post();
                if(!empty($orderData["addres"])){
                    $object=Factory::instance()->getObjectInstance('account');
                        $membername=$this->username;
                        $where['status']=1;
                        $where['Agentd']=$membername;
                        $shopcartchk=Db::name("shoppingcart")->where($where)->count();
                        if($shopcartchk>0){
                                $relist=array_change_key_case(Db::name("comreceiveinfo")->where("id=".$orderData["addres"])->find());
                                $groupcart=Db::name("shoppingcart")->where($where)->field('SupId as supid')->group('SupId')->select();
                                $mainorder=date("YmdHis") . rand(100000, 999999);//生成主订单号
                                $ordermainidArr=[];
                                $orderdetailidArr=[];
                                foreach ($groupcart as $val){
                                    $wheres['SupId']=$val['supid'];
                                    $wheres['Agentd']=$membername;
                                    $wheres['status']=1;

                                    $supplierProData=indexToLower(Db::name("shoppingcart")->where($wheres)->select());
                                    $goodsAmount=0;
                                    foreach ($supplierProData as $value){
                                        $goodsAmount=$goodsAmount+$value['shopprice'];
//                                        $goodspv=$goodspv+$value['consumeintegral'];
//                                        $givepv=$givepv+$value['giveintegral'];
//                                        $Pv=$Pv+$value['pv'];
                                    }
                                    //构建主订单数据--start
                                    $idarr=Db::name('deliverytype')->field('id')->where('supplierid='.$val['supid'].' and is_on=1')->select();
                                    //构建主订单数据--start
                                    if($idarr){
                                        $shipping_index="shipping_method_".$val["supid"];
                                        $searchdata['addresid']=$orderData["addres"];
                                        $searchdata['supplierid']=$val["supid"];
                                        if(empty($orderData[$shipping_index])){
                                            $returnData['status']=0;
                                            $returnData['msg']='请选择配送方式！';
                                            return json($returnData);
                                        }
                                        $searchdata['id']=$orderData[$shipping_index];;
                                        $freght=$object->getfrieght($searchdata);
                                        if($freght['status']==0){
                                            $data["Freight"]=$freght['fright'];
                                            $data["FreightId"]=$orderData[$shipping_index];
                                        }else{
                                            $returnData['status']=0;
                                            $returnData['msg']='生成运费失败！';
                                            return json($returnData);
                                        }
                                    }else{
                                        $data["Freight"]=0;
                                    }

                                    $data["SupplierId"]=$val["supid"];
                                    $data["Province"]=getcityname($relist["province"]);
                                    $data["City"]=getcityname($relist["city"]);
                                    $data["County"]=getcityname($relist["county"]);
                                    $data["Address"]=$relist["address"];
                                    $data["ReceiveName"]=$relist["receivename"];
                                    $data["UserTel"]=$relist["mobile"];
                                    //配送方式
                                    $data["ShippingMethod1"]=1;
                                    $data["OuterOrderId"]=$mainorder;
                                    $data["InnerOrderId"]=$mainorder.'-'.$val["supid"];
                                    $data["GoodsAmount"]=$goodsAmount;
                                    $data["UserId"]=$membername;
                                    $mes_index="remarks_".$val["supid"];
                                    $data["Message"]=$orderData[$mes_index];
                                    $data["ConsumeIntegral"]=0;  //抵扣积分
//                                    $data["userintegral"]=$goodspv;  //可抵扣积分
//                                    $data["Pv"]=$Pv;  //pv值
//                                    $data["GiveIntegral"]=$givepv;//赠送积分
                                    $data["UseUmoney"]=0;
                                    $data["Status"]=1; //普通订单 订单直接为未付款状态
                                    $data["AddDate"]=date('Y-m-d H:i:s',time());
                                    //构建主订单数据--end
                                    $ordermainidArr[]=Db::name("ordermain")->insertGetId($data);//添加数据到订单主表
                                    //构建订单详情的数据--start
                                    foreach ($supplierProData as $v){//根据购物车中的供应商id和主订单生成订单详情
                                        $datade["ProId"]=$v["proid"];
                                        $datade["StyleId"]=$v["styleid"];
                                        $datade["Txm"]=$v["txm"];
                                        $datade["proNum"]=$v["pronum"];
                                        $datade["ProName"]=$v["proname"];
                                        $datade["StyleName"]=$v["stylename"];
                                        $datade["Unit"]=$v["unit"];
                                        $datade["SupplierId"]=$v["supid"];
                                        $datade["InnerOrderId"]=$mainorder.'-'.$val["supid"];
                                        $datade["UserId"]=$membername;

                                        $styleData=Db::name("productstock")->where("styleid=".$v["styleid"])->field('Proid as proid')->find();
                                        if($styleData){
                                            $productData=Db::name("product")->where("proid=".$styleData['proid'])->field('EnjoyPrice,MarketPrice,VipPrice,ConsumeIntegral,BalancePrice,GiveIntegral,Pv')->find();
                                            if(empty($productData)){
                                                $returnData['data'] = '';
                                                $returnData['status']=0;
                                                $returnData['msg']='ID为('.$styleData['proid'].')的商品不存在！';
                                                return json($returnData);
                                            }
                                        }else{
                                            $returnData['data'] = '';
                                            $returnData['status']=0;
                                            $returnData['msg']='商品规格('.$v["styleid"].')不存在！';
                                            return json($returnData);
                                        }
                                        $whereuser['UserId']=$membername;
                                        $usertype=Db::view('usermsg','userType')
                                            ->view('usertype','discount','usertype.ID=usermsg.userType')
                                            ->where($whereuser)->find();
                                        if($usertype['userType']==1){
                                            $datade["Price"] = $productData['VipPrice']*$usertype['discount'];
                                        }elseif($usertype['userType']>=2){
                                            $datade["Price"] = $productData['EnjoyPrice']*$usertype['discount'];
                                        }else{
                                            $datade["Price"] = $productData['MarketPrice']*$usertype['discount'];
                                        }
                                        $datade["BalancePrice"]=$productData['BalancePrice'];
                                        $datade["AddDate"]=date('Y-m-d H:i:s',time());

                                        $orderdetailidArr[]=Db::name("orderdetail")->insertGetId($datade);//添加数据到订单详情表

                                        //去库存的操作
                                        $stockData=$object->stockAction($v["styleid"],0-$v["pronum"],0-$v["pronum"],0);
                                        //更新销售数量
                                        $sql='update product set prosum=prosum+'.$v["pronum"].' where proid="'.$v['proid'].'"';
                                        Db::execute($sql);
                                        if($stockData['status']==0){
                                            //库存不足时进行回滚操作
                                            foreach ($ordermainidArr as $orderMainVal){
                                                Db::name("ordermain")->where('Id='.$orderMainVal)->delete();
                                            }
                                            foreach ($orderdetailidArr as $orderDeVal){
                                                Db::name("orderdetail")->where('Id='.$orderDeVal)->delete();
                                            }
                                            $returnData['status']=0;
                                            $returnData['msg']=$stockData['msg'];
                                            return json($returnData);
                                        }
                                    }
                                }
//                                var_dump($mainorder);exit;
                                Db::name('shoppingcart')->where("status=1 and Agentd='".$membername."'")->delete();
                                    $returnData["data"]['orderno']=$mainorder;
                                    $returnData['status']=2;
                                    $returnData['msg']='生成订单成功！';

                        }else{
                            $returnData['msg']='购物车为空！';
                        }
                }else{
                    $returnData['status']=0;
                    $returnData['msg']='收货地址不能为空！';
                }
            }else{
                $returnData['status']=0;
                $returnData['msg']='数据提交方式错误！';
            }
        }else{
            $returnData['status']=0;
            $returnData['msg']='未登录';
        }
        return json($returnData);
    }

    /**注册复消订单的生成
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function orderhitGenerate(){
        if($this->loginStatus){
            if($this->request->isPost()) {
                $orderData = $this->request->post();
                $ishit=$this->request->get('ishit');
                if($orderData['receivename']&&$orderData['mobile']&&$orderData['address']&&isset($orderData['proid'])&&$orderData['pwd']&&$orderData['province']!='请选择'&&isset($ishit)){
                    $membername=$this->username;
                    $userinfos=file_get_contents(config('API_URL').'/api.php/order/check?user_name='.$membername);
                    $userinfos=json_decode($userinfos,true);
                    $userinfoxx=$userinfos['data'];
                    $url=config('API_URL').'/api.php/order/pay';
                            $where['product.ProId'] = ['in',$orderData['proid']];
                            $where['product.IsOnSell'] = 1;
                            $productdata = Db::view('product')
                                ->view('productstock', 'StyleId,StyleName', 'productstock.ProId=product.ProId')
                                ->where($where)
                                ->group('ProId')
                                ->select();
                            $suppliers = Db::name('product')
                                ->where($where)
                                ->field('SupplierId as supid')
                                ->group('SupplierId')
                                ->select();
//                            var_dump($productdata);exit;
                            $mainorder = date("YmdHis") . rand(100000, 999999);//生成主订单号
                            if ($productdata&&$suppliers) {
                                    foreach ($suppliers as $val){
                                        $goodsAmount=0;
                                        $givepv=0;
                                        $Pv=0;
                                        $ConsumeIntegral=0;
                                        foreach ($productdata as $value){
                                            $proidnum='number_'.$value['ProId'];
                                            if($value['SupplierId']==$val['supid']){
                                                $goodsAmount=$goodsAmount+($value['VipPrice']*$orderData[$proidnum]);
                                                $Pv=$Pv+($value['Pv']*$orderData[$proidnum]);
                                                $givepv=$givepv+($value['GiveIntegral']*$orderData[$proidnum]);
                                                $ConsumeIntegral=$ConsumeIntegral+($value['ConsumeIntegral']*$orderData[$proidnum]);
                                            }
                                        }
                                        $data["SupplierId"]=$val["supid"];
                                        $data["Province"]=$orderData["province"];
                                        $data["City"]=$orderData["city"];
                                        $data["County"]=$orderData["county"];
                                        $data["Address"]=$orderData["address"];
                                        $data["ReceiveName"]=$orderData["receivename"];
                                        $data["UserTel"]=$orderData["mobile"];
                                        $data["ShippingMethod"]=$orderData['ShippingMethod'];
                                        $data["OuterOrderId"]=$mainorder;
                                        $orderno=$data["InnerOrderId"]=$mainorder.'-'.$val["supid"];
                                        $data["GoodsAmount"]=$goodsAmount;
                                        $data["UserId"]=$membername;
                                        $data["ConsumeIntegral"]=0;  //抵扣积分
                                        $data["userintegral"]=$ConsumeIntegral;  //可抵扣积分
                                        $data["Pv"]=$Pv;  //pv值
                                        $data["GiveIntegral"]=$givepv;//赠送积分
                                        $data["UseUmoney"]=0;
                                        $data["Status"]=1; //普通订单 订单直接为未付款状态
                                        if($ishit==1){
                                            $data['OrderType']=1;
                                        }else{
                                            $data['OrderType']=2;
                                        }

                                        $data["AddDate"]=date('Y-m-d H:i:s',time());
                                        Db::name("ordermain")->insert($data);//添加数据到订单主表
                                        foreach ($productdata as  $v){
                                            if($v['SupplierId']==$val['supid']){
                                                $object = Factory::instance()->getObjectInstance('account');
                                                $stockData = $object->stockAction($v['StyleId'], -1, -1, 0);

                                                if ($stockData['status'] == 0) {
                                                    Db::name('ordermain')->where('OuterOrderId=' . $mainorder)->delete();
                                                    $returnData['status'] = 0;
                                                    $returnData['msg'] = '该套餐库存不足！';
                                                    return json($returnData);
                                                }else{
                                                    $datade["ProId"]=$v['ProId'];
                                                    $datade["StyleId"]=$v["StyleId"];
                                                    $datade["proNum"]=$orderData[$proidnum];
                                                    $datade["ProName"]=$v["ProName"];
                                                    $datade["StyleName"]=$v["StyleName"];
                                                    $datade["Unit"]=$v["Unit"];
                                                    $datade["SupplierId"]=$v["SupplierId"];
                                                    $datade["InnerOrderId"]=$mainorder.'-'.$v["SupplierId"];
                                                    $datade["UserId"]=$membername;
                                                    $datade["Price"]=$v['VipPrice'];
                                                    $datade["ConsumeIntegral"]=0;
                                                    $datade["GiveIntegral"]=$v['GiveIntegral'];
                                                    $datade["Pv"]=$v['Pv'];
                                                    $datade["BalancePrice"]=$v['BalancePrice'];
                                                    $datade["AddDate"]=date('Y-m-d H:i:s',time());
                                                    Db::name("orderdetail")->insert($datade);//添加数据到订单详情表
                                                }
                                            }

                                        }
                                        $senddata['type']='account';
                                        $senddata['mny']=$goodsAmount;
                                        $senddata['uid']=$membername;
                                        $senddata['orderno']=$orderno;
                                        $senddata['ordertype']=2;
                                        $senddata['usePv']=0;
                                        $senddata['pwd']=$orderData['pwd'];
                                        $senddata['goodsmoney']=$goodsAmount;
                                        $senddata['yunfei']=0;
                                        $senddata['order_pv']=$Pv;
                                        $senddata['pay_type']=5;
                                        $senddata['kou_dou']=$ConsumeIntegral;
                                        $senddata['song_dou']=$givepv;
                                        $returndata=getcurl($url,$senddata);
                                        if($returndata['status']==1){

                                            $su = Db::name('ordermain')->where('InnerOrderId="'.$orderno.'"')->count();
                                            if($su>0){
                                                $this->insertdata($userinfoxx,$orderno,$senddata['mny'],0);

                                                $this->add_addres($membername,$orderData['receivename'],$orderData['province'],$orderData['city'],$orderData['county'],$orderData['address'],$orderData['mobile']);
                                            }
                                            $returnData['status'] = 1;
                                            $returnData['msg'] = '付款成功';
                                        }else{
                                            Db::name('orderdetail')->where('InnerOrderId="'.$orderno.'"')->delete();
                                            Db::name('ordermain')->where('InnerOrderId='.$orderno)->delete();
                                            $returnData['status'] = 0;
                                            $returnData['msg'] = $returndata['msg'];
                                        }
                                    }
                            } else {
                                $returnData['status'] = 0;
                                $returnData['msg'] = '商品不存在！';
                            }

                }else{

                    if(empty($orderData['receivename'])){
                        $returnData['status']=0;
                        $returnData['msg']='收货人不能为空！';
                    }elseif(empty($orderData['mobile'])){
                        $returnData['status']=0;
                        $returnData['msg']='联系方式不能为空！';
                    }elseif(empty($orderData['address'])||$orderData['province']=='请选择'){
                        $returnData['status']=0;
                        $returnData['msg']='配送地址不能为空！';
                    }elseif(isset($orderData['proid'])==false){
                        $returnData['status']=0;
                        $returnData['msg']='请选择购买的套餐！';
                    }elseif(empty($orderData['pwd'])){
                        $returnData['status']=0;
                        $returnData['msg']='请输入支付密码！';
                    }
                }
            }else{
                $returnData['status']=0;
                $returnData['msg']='数据提交方式错误！';
            }
        }else{
            $returnData['status']=0;
            $returnData['msg']='未登录';
        }
        return json($returnData);
    }

    public function add_addres($member,$receivename,$province,$city,$county,$address,$mobile){
        $data["UserId"]=$member;
        $data["ReceiveName"]=$receivename;
        $data["Province"]=$province;
        $data["City"]=$city;
        $data["County"]=$county;
        $data["Address"]=$address;
        $data["Mobile"]=$mobile;
        $data["AddDate"]=date('Y-m-d H:i:s',time());
        $data["IsDefault"]=0;
        Db::name('comreceiveinfo')->insertGetId($data);
    }
    /**
     * 会员订单支付
     * 请求实例：http://www.XX.com/api.php/order/pay?type=accpunt
     * 请求方式：post或者get
     * @param type 支付类型
     *      参数值：account   账户余额支付
     * @return 操作状态信息
     */

    public function orderPay(){
        $payword=$this->request->post();
        if($this->loginStatus){
            $type=$this->request->param('type');//获取支付类型 account 账户余额支付
            if($type=='account'){
                $method='order'.ucfirst($type).'Pay';
                $returnData=$this->$method($payword['LevIIPwd']);
            }else{
                $method='order'.ucfirst($type);
                $returnData=$this->$method($payword['LevIIPwd']);
            }
        }else{
            $returnData['data']='';
            $returnData['status']=0;
            $returnData['msg']='未登录';
        }
        return json($returnData);
    }

    public function thirdpay(){
        $orderno=$this->request->param('orderno');
//      	$ishit=session('ishit');
        if($this->loginStatus){
            $where['UserId']=$this->username;
            $where['InnerOrderId']=$orderno;
//            var_dump($where);exit;
//            if($ishit==1){
              $from='http://'.$_SERVER['HTTP_HOST'].'/mobile.php/order/return';
//            }else{
//              $from='http://'.$_SERVER['HTTP_HOST'].'/mobilenew.php/order/return';
//            }
          $order=Db::name('ordermain')->field('id,goodsAmount,Freight,GiveIntegral,OrderType,Pv,userintegral')->where($where)->find();
           $token=md5($orderno.$order['id'].$this->username.$order['goodsAmount'].$order['goodsAmount'].$order['Freight'].'fs5w4wefgca76w4r');  
          $url=Config::get('API_URL').'/mobile.php/order/pay?ordersn='.$orderno.'&goodsmoney='.$order['goodsAmount'].'&yunfei='.$order['Freight'].'&money='.$order['goodsAmount'].'&uid='.$this->username.'&from='.$from.'&orderid='.$order['id'].'&token='.$token;
           	$returnData["data"]['url']=$url;
            $returnData['status']=1;
            $returnData['msg']='成功！';
        }else{
            $returnData['status']=0;
            $returnData['msg']='未登录';
        }
        return json($returnData);
    }
    /**
     *删除会员订单
     *
     */
    public function delete(){
        $where['InnerOrderId']=$this->request->param('id');
        $mainorder = Db::name('ordermain')->where($where)->delete();
        $detailorder=Db::name('orderdetail')->where($where)->delete();

    }
    /**
     * 余额支付
     * @return mixed
     */

    public function orderAccountPay1($payword){//账户余额支付
        $orderno=$this->request->param('orderno');
        $ordertype=$this->request->param('ordertype');
//        $GoodsAmount=$this->request->param('GoodsAmount');
//        $ConsumeIntegral=$this->request->param('ConsumeIntegral');
//        if(empty($ConsumeIntegral)){
//            $ConsumeIntegral=0;
//        }
        $membername = $this->username;
        $where['UserId']= $membername;
        $userinfoxx=Db::name('usermsg')->field('LevIIPwd,Umoney,Pv')->where($where)->find();
        if($userinfoxx['LevIIPwd']!=md5($payword)){
            $returnData['data']="";
            $returnData['status']=0;
            $returnData['msg']='支付密码错误';
        }else{
                    $userinfoxx['Umoney'] = empty($userinfoxx['Umoney']) ? 0 : $userinfoxx['Umoney'];
                    $userinfoxx['Pv'] = empty($userinfoxx['Pv']) ? 0 : $userinfoxx['Pv'];
                    if ($userinfoxx) {
                        if ($ordertype == 'outer') {//主订单号
                            $GoodsAmount=Db::name('ordermain')->where("OuterOrderId='".$orderno."'")->sum('GoodsAmount');//汇总所需扣除的购物币总额
                            $Freght=Db::name('ordermain')->where("OuterOrderId='".$orderno."'")->sum('Freight');//汇总所需扣除的运费总额
                            $useumoney = $GoodsAmount+$Freght;

                            $returnData = $this->accountVerify($userinfoxx, $useumoney);
                            if ($returnData['status'] == 1) {
                                $orderData = Db::name('ordermain')->where("OuterOrderId='" . $orderno . "'")->field('InnerOrderId,GoodsAmount,Freight,userintegral,Pv,GiveIntegral')->select();
                                foreach ($orderData as $k => $val) {
                                    $moneyss=$senddata['mny']=$val['GoodsAmount']+$val['Freight'];
                                    $senddata['uesrId']=$membername;
                                    $senddata['type']='cut';
                                    $senddata['ordersn']=$val['InnerOrderId'];
                                    if($ishit==1){
                                        $senddata['shoptype']='零购积分商城';
                                    }elseif($ishit==2){
                                        $senddata['shoptype']='消费钱包商城';
                                    }

//                                    var_dump($url);
//                                    var_dump($senddata);exit;
                                    $returndata=getcurl($url,$senddata);

                                    if($returndata['status']==1){
                                        $su=Db::name('ordermain')->where("OuterOrderId='" . $orderno . "' and status=1")->count();
                                        if($su>0){
                                            $this->insertdata($userinfoxx,$val['InnerOrderId'],$moneyss);
                                        }
                                    }else{
                                        $returnData['status'] = 0;
                                        $returnData['msg'] = $returndata['msg'];
                                    }
                                }
                                $returnData['status'] = 1;
                                $returnData['msg'] = '付款成功';
                            }
                        } elseif ($ordertype == 'inner') {//子订单号
                            $condition['InnerOrderId']=$orderno;
                            $orderRecord=Db::name('ordermain')->where($condition)->field('GoodsAmount,ConsumeIntegral,Freight,userintegral,Pv,GiveIntegral')->find();
                            $useumoney=$orderRecord['GoodsAmount']+$orderRecord['Freight'];//计算需要扣除的购物币的总金额
//                            $goodspv=$orderRecord['userintegral'];//计算需要最多可以使用的积分汇总
                            $useumoneys = $useumoney;
//                            $useumoneys = $useumoney-$ConsumeIntegral;
//                            $usegoodspv = $ConsumeIntegral;
                            $returnData = $this->accountVerify($userinfoxx, $useumoneys,$ishit);
                            if ($returnData['status'] == 1) {
                                $senddata['mny']=$useumoney;
                                $senddata['uesrId']=$membername;
                                $senddata['type']='cut';
                                $senddata['ordersn']=$orderno;
                                if($ishit==1){
                                    $senddata['shoptype']='零购积分商城';
                                }elseif($ishit==2){
                                    $senddata['shoptype']='消费钱包商城';
                                }
                                $returndata=getcurl($url,$senddata);
//                                var_dump($returndata);exit;
//                                $returndata=json_decode($returndata,true);
                                if($returndata['status']==1){
                                    $su = Db::name('ordermain')->where($condition)->count();
                                    if($su>0){
                                        $this->insertdata($userinfoxx,$orderno,$useumoneys);
                                    }
                                    $returnData['status'] = 1;
                                    $returnData['msg'] = '付款成功';
                                }else{
                                    $returnData['status'] = 0;
                                    $returnData['msg'] = $returndata['msg'];
                                }
                            }
                        }

                    } else {
                        $returnData['data'] = "";
                        $returnData['status'] = 0;
                        $returnData['msg'] = '会员不存在';
                    }

                    /**
                     * 支付成功后执行分润和购物赠积分
                     */
//                    if ($returnData['status'] == 1) {
//            Factory::instance()->getObjectInstance('settle')->user_settlement($orderno, $ordertype);//分润
//            Factory::instance()->getObjectInstance('account')->giveIntegral($orderno,$ordertype,$membername);//购物赠积分
//                    }

        }
//        var_dump($returnData);exit;
        return $returnData;
    }
    public function orderAccountPay($payword){//账户余额支付
        $orderno=$this->request->param('orderno');
        $ordertype=$this->request->param('ordertype');
        $GoodsAmount=$this->request->param('GoodsAmount');
        $ConsumeIntegral=$this->request->param('ConsumeIntegral');
        if(empty($ConsumeIntegral)){
            $ConsumeIntegral=0;
        }
        $membername = $this->username;
        $where['UserId'] = $membername;
        $userinfoxx=Db::name('usermsg')->where($where)->field('Umoney,Pv,LevIIPwd')->find();
        $userinfoxx['Umoney'] = empty($userinfoxx['Umoney']) ? 0 : $userinfoxx['Umoney'];
        $userinfoxx['Pv'] = empty($userinfoxx['Pv']) ? 0 : $userinfoxx['Pv'];
        if($userinfoxx['LevIIPwd']!=md5($payword)){
            $returnData['status']=0;
            $returnData['msg']='支付密码错误';
        }else{
            if(is_numeric($ConsumeIntegral)) {
                $ConsumeIntegral=floor($ConsumeIntegral*100)/100;
                if ($ConsumeIntegral >= 0) {
                    if ($userinfoxx) {
                        $object = Factory::instance()->getObjectInstance('account');
                        if ($ordertype == 'outer') {//主订单号
                            $GoodsAmount=Db::name('ordermain')->where("OuterOrderId='".$orderno."'")->sum('GoodsAmount');//汇总所需扣除的购物币总额
                            $Freght=Db::name('ordermain')->where("OuterOrderId='".$orderno."'")->sum('Freight');//汇总所需扣除的运费总额
                            $goodspv=Db::name('ordermain')->where("OuterOrderId='".$orderno."'")->sum('userintegral');//汇总可以使用积分的总额
                            $useumoney = $GoodsAmount+$Freght-$ConsumeIntegral;
                            $usegoodspv = $ConsumeIntegral;
                            $returnData = $this->accountVerify($userinfoxx, $useumoney, $usegoodspv,$goodspv);

                            if ($returnData['status'] == 1) {
                                $orderData = Db::name('ordermain')->where("OuterOrderId='" . $orderno . "'")->field('InnerOrderId,GoodsAmount,Freight,userintegral')->select();
                                foreach ($orderData as $k => $val) {
                                    $usemoney=$val['GoodsAmount']+$val['Freight'];
                                    $returnData = $this->accountAction($val['InnerOrderId'], $membername, $object, $usemoney, 0);
                                    $returnData['ordernonew']=$val['InnerOrderId'];
                                    if ($returnData['status'] == 0) {
                                        break;
                                    }
                                }
                            }
                        } elseif ($ordertype == 'inner') {//子订单号
                            $condition['InnerOrderId']=$orderno;
                            $orderRecord=Db::name('ordermain')->where($condition)->field('GoodsAmount,ConsumeIntegral,Freight,userintegral')->find();
                            $useumoney=$orderRecord['GoodsAmount']+$orderRecord['Freight'];//计算需要扣除的购物币的总金额
                            $goodspv=$orderRecord['userintegral'];//计算需要最多可以使用的积分汇总
                            $useumoney = $useumoney-$ConsumeIntegral;
                            $usegoodspv = $ConsumeIntegral;
                            $returnData = $this->accountVerify($userinfoxx, $useumoney, $usegoodspv,$goodspv);
                            if ($returnData['status'] == 1) {
                                $returnData = $this->accountAction($orderno, $membername, $object, $useumoney, $usegoodspv);
                                $returnData['ordernonew']=$orderno;
                            }
                        }

                    } else {
                        $returnData['data'] = "";
                        $returnData['status'] = 0;
                        $returnData['msg'] = '会员不存在';
                    }

                    /**
                     * 支付成功后执行分润和购物赠积分
                     */
                    if ($returnData['status'] == 1) {
//            Factory::instance()->getObjectInstance('settle')->user_settlement($orderno, $ordertype);//分润
//            $object->giveIntegral($orderno,$ordertype,$membername);//购物赠积分
                    }
                } else {
                    $returnData['data'] = "";
                    $returnData['status'] = 0;
                    $returnData['msg'] = '积分输入有误';
                }
            }else{
                $returnData['data'] = "";
                $returnData['status'] = 0;
                $returnData['msg'] = '积分输入有误';
            }
        }
        return $returnData;
    }


    /**
     * 验证账户可用购物币和积分是否充足
     */

    private function accountVerify($userinfoxx,$useumoney,$usegoodspv,$goodspv){
        if($goodspv<$usegoodspv){
            $returnData['status']=0;
            $returnData['msg']='最多可以使用'.$goodspv.'积分！';
            return $returnData;
        }
        if($useumoney>$userinfoxx["Umoney"]){
            $returnData['status']=0;
            $returnData['msg']='余额不足！';
            return $returnData;
        }

        if($usegoodspv > $userinfoxx["Pv"]){
            $returnData['status']=0;
            $returnData['msg']='积分不足！';
            return $returnData;
        }

        return ['status'=>1,'余额充足'];
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
    private function accountAction($orderno,$membername,$object,$useumoney,$usegoodspv){
        $orderfk=array_change_key_case(Db::name('ordermain')->where("innerorderid='".$orderno."'")->find());
        if($orderfk["status"]==1){
            $accountData=array(
                'account_umoney'=>array(
                    'umoney'=>0-$useumoney,
                    'flowtype'=>'扣币',
//                    'memo'=>"({$client})购物扣币[订单号：".$orderno."]"
                    'memo'=>"购物扣币[订单号：".$orderno."]"
                ),
                'account_goodspv'=>array(
                    'goodspv'=>0-$usegoodspv,
                    'typename'=>'扣积分',
//                    'memo'=>"({$client})购物扣积分[订单号：".$orderno."]"
                    'memo'=>"购物扣积分[订单号：".$orderno."]"
                ),
                'userid'=>$membername,
                'formwho'=>$membername
            );
            //改变账户余额
            $accountData=$object->accountAction($accountData);
            if($accountData['status']==1){
                //改变支付金额和积分
                $data['OrderAmount']=$useumoney;
                $data['ConsumeIntegral']=$usegoodspv;
                Db::name('ordermain')->where("innerorderid='".$orderno."'")->update($data);
                //改变订单状态
                $orderData=$object->orderAction($orderno,4);
                if($orderData['status']==1){
                    //改变库存状态
                    $orderdetaillist=indexToLower(Db::name('orderdetail')->where("innerorderid='".$orderno."'")->select());
                    foreach($orderdetaillist as $n=>$v){
                        //未付款库存减少，未发货库存增加
                        $object->stockAction($v["styleid"],0,0+$v["pronum"],0-$v["pronum"]);
                        //更新订单详情中产品的结算价及分润数据并汇总到订单主表中
//                        $object->updateOrderDetail($v['proid'],$v['id'],$orderno);
                        $object->updateOrderDetail($v['proid'],$v['id'],$orderno);
                    }
                    if($orderfk['supplierid']==1){
                        $orderinfo['buyer_name']=$orderfk['userid'];
                        $orderinfo['province']=$orderfk['province'];
                        $orderinfo['city']=$orderfk['city'];
                        $orderinfo['county']=$orderfk['county'];
                        $orderinfo['address']=$orderfk['address'];
                        $orderinfo['name']=$orderfk['receivename'];
                        $orderinfo['phone']=$orderfk['usertel'];
                        $wheredetail['UserId']=$membername;
                        $wheredetail['InnerOrderId']=$orderfk['innerorderid'];
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
                        $resu=json_decode($resu,true);
                        if($resu['status']==0){
                            Db::name('ordermain')->where("innerorderid='".$orderno."'")->update(['order_id'=>$resu['data']['order_id']]);
                        }
                    }

                    // 如果该用户不是营销中心，则给推荐人中的营销中心返1%
                    $userinfo_new = db::name('usermsg')->where(['UserId'=>$orderfk['userid']])->find();
                    if($userinfo_new['userType'] != 4){
                        $sql = 'select usermsg.UserId,usermsg.Umoney from usermsg,userrecommenddiagram  where usermsg.UserId=userrecommenddiagram.pid and usermsg.userType=4 and userrecommenddiagram.userId='.$orderfk['userid'];
                        $plist = Db::query($sql);
                        foreach ($plist as $key_new => $value_new) {
                            $datareco['UserId'] = $value_new['UserId'];
                            $datareco['FlowType'] = '购物返利';
                            $datareco['Amount'] = $useumoney * 0.01;
                            $datauser['Umoney'] = $datareco['Balance'] = $datareco['Amount'] + $value_new['Umoney'];
                            $datareco['FromWho'] = $orderfk['userid'];
                            $datareco['Memo'] = '购物返利';
                            $datareco['AddDate'] = date('Y-m-d H:i:s',time());
                            db::name('accountrecord')->insert($datareco);
                            $whereuser['UserId'] = $value_new['UserId'];
                            db::name('usermsg')->where($whereuser)->update($datauser);
                        }
                    }
                    
                }else{
                    $returnData=$orderData;
                    return $returnData;
                }
            }else{
                $returnData=$accountData;
                return $returnData;
            }
            $returnData['data']="";
            $returnData['status']=1;
            $returnData['msg']='订单支付成功！';
            return $returnData;
        }else{
            $returnData['data']="";
            $returnData['status']=0;
            $returnData['msg']='订单已支付！';
            return $returnData;
        }
    }

    /**
     * 付款成功更新订单信息插入记录
     */

    private function insertdata($userinfoxx,$doorderno,$totlemoney){
        $ShippingMethod=Db::name('ordermain')->field('ShippingMethod')->where('InnerOrderId="'.$doorderno.'"')->find();
        if($ShippingMethod['ShippingMethod']==3){
            $updata['Status']=4;
//            $updata['ConsumeIntegral']=$pv;
            $updata['OrderAmount']=$totlemoney;
            $updata['PayDate']=date('Y-m-d H:i:s');
            Db::name('ordermain')->where('InnerOrderId="'.$doorderno.'"')->update($updata);
        }else{
            $updata['Status']=2;
//            $updata['ConsumeIntegral']=$pv;
            $updata['OrderAmount']=$totlemoney;
            $updata['PayDate']=date('Y-m-d H:i:s');
            Db::name('ordermain')->where('InnerOrderId="'.$doorderno.'"')->update($updata);
        }


    }
    /**
     * 验证账户可用购物币和积分是否充足
     */

    private function accountVerify1($userinfoxx,$useumoney){
            if($useumoney>$userinfoxx["Umoney"]){
                $returnData['status']=0;
                $returnData['msg']='余额不足！';
                return $returnData;
            }

//            if($useumoney>$userinfoxx["Pv"]){
//                $returnData['status']=0;
//                $returnData['msg']='积分不足！';
//                return $returnData;
//            }


        return ['status'=>1,'msg'=>'余额充足'];
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
    private function accountAction1($orderno,$membername,$object,$useumoney,$usegoodspv){
        $orderfk=array_change_key_case(Db::name('ordermain')->where("innerorderid='".$orderno."'")->find());
        //var_dump($orderfk);exit;
        if($orderfk["status"]==1){
//            $client=ismobile()?'移动端':'PC端';
            $accountData=array(
                'account_umoney'=>array(
                    'umoney'=>0-$useumoney,
                    'flowtype'=>'扣币',
//                    'memo'=>"({$client})购物扣币[订单号：".$orderno."]"
                    'memo'=>"购物扣币[订单号：".$orderno."]"
                ),
                'account_goodspv'=>array(
                    'goodspv'=>0-$usegoodspv,
                    'typename'=>'扣积分',
//                    'memo'=>"({$client})购物扣积分[订单号：".$orderno."]"
                    'memo'=>"购物扣积分[订单号：".$orderno."]"
                ),
                'userid'=>$membername,
                'formwho'=>$membername
            );
            //改变账户余额
            $accountData=$object->accountAction($accountData);
            if($accountData['status']==1){
                //改变支付金额和积分
                $data['OrderAmount']=$useumoney;
                $data['ConsumeIntegral']=$usegoodspv;
                Db::name('ordermain')->where("innerorderid='".$orderno."'")->update($data);
                //改变订单状态
                $orderData=$object->orderAction($orderno,4);
                if($orderData['status']==1){
                    //改变库存状态
                    $orderdetaillist=indexToLower(Db::name('orderdetail')->where("innerorderid='".$orderno."'")->select());
                    foreach($orderdetaillist as $n=>$v){
                        //未付款库存减少，未发货库存增加
                        $object->stockAction($v["styleid"],0,0+$v["pronum"],0-$v["pronum"]);
                        //更新订单详情中产品的结算价及分润数据并汇总到订单主表中
//                        $object->updateOrderDetail($v['proid'],$v['id'],$orderno);
                        $object->updateOrderDetail($v['proid'],$v['id'],$orderno);
                    }
                }else{
                    $returnData=$orderData;
                    return $returnData;
                }
            }else{
                $returnData=$accountData;
                return $returnData;
            }
            $returnData['data']="";
            $returnData['status']=1;
            $returnData['msg']='订单支付成功！';
            return $returnData;
        }else{
            $returnData['data']="";
            $returnData['status']=0;
            $returnData['msg']='订单已支付！';
            return $returnData;
        }
    }
}
