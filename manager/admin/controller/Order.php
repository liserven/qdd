<?php
/**
 * tpAdmin [a web admin based ThinkPHP5]
 *
 * @author yuan1994 <tianpian0805@gmail.com>
 * @link http://tpadmin.yuan1994.com/
 * @copyright 2016 yuan1994 all rights reserved.
 * @license http://www.apache.org/licenses/LICENSE-2.0
 */

//------------------------
// 公开不授权控制器
//-------------------------

namespace app\admin\controller;

\think\Loader::import('controller/Jump', TRAIT_PATH, EXT);

use app\admin\Controller;
use think\Session;
use think\Db;
use think\Config;
use think\View;
use think\Request;

class Order extends Controller
{
    use \traits\controller\Jump;

    public function index(){
        return $this->orderList();
    }

    /**
     * 订单列表
     * @return string
     */
    public function orderList(){
        $where['query_condition']='1=1';
        $wheres['Id'] = ['>',0];
        if($this->request->param()){
           // var_dump($this->request->param());exit;
            //根据订单号进行搜索
            if($this->request->param('keyorderid')){
                $keyorderid=$this->request->param('keyorderid');
                $map['keyproid']=$keyorderid;
                $where['InnerOrderId']=$keyorderid;
            }
            //根据交易状态进行查找
            if($this->request->param('Tradingclass')!=''){
                $Tradingclass=$this->request->param('Tradingclass');
                $map['Tradingclass']=$Tradingclass;
                $where['Status']=$Tradingclass;
            }
            //根据订单类型进行查找
            if($this->request->param("orderclass")!=""){
                $orderclass=$this->request->param("orderclass");
                $where["OrderType"]=$orderclass;
                $map['orderclass']=$orderclass;
            }

            //根据来源id进行查找
            if($this->request->param("keyvip")){
                $keyvip=$this->request->param("keyvip");
                $where["UserId"]=$keyvip;
                $map['keyvip']=$keyvip;
            }
            // 商品名
            if($this->request->param('ProName')){
              $ProName=$this->request->param('ProName');
              $maps['ProName']=$ProName;
              $wheres['ProName']=['like','%'.$ProName.'%'];
              $parr = [];
              $porder = db::name('orderdetail')->field('InnerOrderId')->where($wheres)->select();
              foreach ($porder as $kp => $vp) {
                $parr[] = $vp['InnerOrderId'];
              }
               if($parr){
                  $where['InnerOrderId'] = ['in',$parr];
                  $map['InnerOrderId']= ['in',$parr];
              }else{
                  $where['InnerOrderId'] = ['in',''];
              }
            }

            //根据商品id进行搜索
            if($this->request->param("keyproid")){
                $keyproid=$this->request->param("keyproid");
                $cond['innerorderid']=Db::name('orderdetail')->where("proid=".$keyproid)->field('innerorderid')->group("innerorderid")->select();
                for($i=0;$i<count($cond['innerorderid']);$i++){
                    $aa[$i]=$cond['innerorderid'][$i]['innerorderid'];
                }
                $where['InnerOrderId']=array('in',implode(',',$aa));
                $map['keyproid']=$keyproid;
            }

            //根据商家id进行查找
            if($this->request->param("keysupid")){
                $keysupid=$this->request->param("keysupid");
                $where["SupplierId"]=$keysupid;
                $map['keysupid']=$keysupid;
            }

            //根据收货人进行查找
            if($this->request->param("keyreiciver")){
                $keyreiciver=$this->request->param("keyreiciver");
                $where["ReceiveName"]=array('like','%'.$keyreiciver.'%');
                $map['keyreiciver']=$keyreiciver;
            }
            //根据日期进行查找
            if($this->request->param("datemin") and $this->request->param("datemax")){
                $requestdate=$this->request->param("datemax");
                $redate=date("Y-m-d",strtotime("$requestdate +1 day"));
                $where["AddDate"]=array(array('egt',$this->request->param("datemin")),array('elt',$redate),'and');
                $map["datemin"]=$this->request->param("datemin");
                $map["datemax"]=$this->request->param("datemax");
            }elseif($this->request->param("datemin")){
                $where["AddDate"]=array('egt',$this->request->param("datemin"));
                $map["datemin"]=$this->request->param("datemin");
            }elseif($this->request->param("datemax")){
                $requestdate=$this->request->param("datemax");
                $redate=date("Y-m-d",strtotime("$requestdate +1 day"));
                $where["AddDate"]=array('elt',$redate);
                $map["datemax"]=$this->request->param("datemax");
            }
            //根据发货进行查找
            if($this->request->param("datemin1") and $this->request->param("datemax1")){
                $requestdate=$this->request->param("datemax1");
                $redate=date("Y-m-d",strtotime("$requestdate +1 day"));
                $where["DeliverDate"]=array(array('egt',$this->request->param("datemin1")),array('elt',$redate),'and');
                $map["datemin1"]=$this->request->param("datemin1");
                $map["datemax1"]=$this->request->param("datemax1");
            }elseif($this->request->param("datemin1")){
                $where["DeliverDate"]=array('egt',$this->request->param("datemin1"));
                $map["datemin1"]=$this->request->param("datemin1");
            }elseif($this->request->param("datemax1")){
                $requestdate=$this->request->param("datemax1");
                $redate=date("Y-m-d",strtotime("$requestdate +1 day"));
                $where["DeliverDate"]=array('elt',$redate);
                $map["datemax1"]=$this->request->param("datemax1");
            }
        }
//var_dump($where);exit;
        if(count($where)==1){
            unset($where['query_condition']);
            $where='1=1';
        }else{
            unset($where['query_condition']);
        }
        $orderlist=Db::name('ordermain')->where($where)->order('Id desc')->paginate();//根据条件分页输出
        $pricesum = Db::name('ordermain')->where($where)->sum("GoodsAmount");
        $data=[];
        foreach($orderlist as $n=>$val){
            $data[$n]=$val=array_change_key_case($val);
            $data[$n]["voo"]=indexToLower(Db::name('orderdetail')->where("innerorderid='".$val["innerorderid"]."'")->where($wheres)->select());
        }
        if(!empty($map)) {
            foreach ($map as $key => $value) {
                $orderlist->appends($key, $value);//分页链接中添加请求的参数
            }
        }

        /*导出功能开始*/

         /*开始导出excel*/
            /*,'付款时间'   ,PayDate 将次放到下单时间后面*/
            if($this->request->param('export')){
              $title=array('订单编号','下单时间','会员','收货人','联系电话','物流类型','交易状态','客户留言','管理员备注','收货时间','供应商','实付款(元)','商品id','商品名称','规格','单价(元)','数量','订单信息','收货地址');
              /*','City','County','Address','UserTel','ReceiveName','ShippingMethod','PayMethod','status','Message','AddDate','PayDate','DeliverDate','ReceiveDate','AWBNo','SupplierId','GoodAmount','OrderAmount'*/
              $list=Db::name('ordermain')->field("InnerOrderId,AddDate,UserId,Province,City,County,Address,ReceiveName,UserTel,ShippingMethod,status,Message,AdminMessage,DeliverDate,ReceiveDate,AWBNo,SupplierId,GoodsAmount,ExpCompanyId")->where($where)->order('AddDate desc')->select();
              set_time_limit(0);
              ini_set('memory_limit', '128M');
              $fileName = date('YmdHis', time());
              header('Content-Type: application/vnd.ms-execl');
              header('Content-Disposition: attachment;filename="' . $fileName . '.csv"');

            //打开php标准输出流
            //以写入追加的方式打开
              $fp = fopen('php://output', 'a');
              foreach($title as $key => $item) {
                $content[$key] = iconv('UTF-8', 'GBK', $item);
              }
             //将数据写到标准输出中
              fputcsv($fp, $content);


              foreach($list as $key=>$value){
                $selectwhere['InnerOrderId'] = $list[$key]["InnerOrderId"];
                $goodsinfo=Db::name('orderdetail')->where($selectwhere)->select();
              //dump($goodsinfo);
                /*将list数组中需要删除的元素存在savearr数组中，以便循环中调用*/
                $savearr['ExpCompanyId'] = $list[$key]['ExpCompanyId'];
                $savearr['AWBNo'] = $list[$key]['AWBNo'];
                $savearr['DeliverDate'] = $list[$key]['DeliverDate'];
                $savearr['Province'] = $list[$key]['Province'];
                $savearr['City'] = $list[$key]['City'];
                $savearr['County'] = $list[$key]['County'];
                $savearr['Address'] = $list[$key]['Address'];
                $savearr['SupplierId'] = $list[$key]['SupplierId'];
                $savearr['UserId'] = $list[$key]['UserId'];

                unset($list[$key]['ExpCompanyId']);
                unset($list[$key]['AWBNo']);
                unset($list[$key]['DeliverDate']);
                unset($list[$key]['Province']);
                unset($list[$key]['City']);
                unset($list[$key]['County']);
                unset($list[$key]['Address']);

                /*订单状态 1未付款 2已付款 3配货中 4已发货 8已收货 10已取消 15用户已申请取消 20后台取消*/
                if($list[$key]['status'] == 1){
                  $list[$key]['status']='未付款';
                }
                if($list[$key]['status'] == 2){
                  $list[$key]['status']='已付款';
                }
                if($list[$key]['status'] == 3){
                  $list[$key]['status']='配货中';
                }
                if($list[$key]['status'] == 4){
                  $list[$key]['status']='已发货';
                }
                if($list[$key]['status'] == 8){
                  $list[$key]['status']='已收货 ';
                }
                if($list[$key]['status'] == 10){
                  $list[$key]['status']='已取消';
                }
                if($list[$key]['status'] == 15){
                  $list[$key]['status']='用户已申请取消';
                }
                if($list[$key]['status'] == 20){
                  $list[$key]['status']='后台取消';
                }

//                if($list[$key]['OrderType'] == 1){
//                  $list[$key]['OrderType'] = '零购积分订单';
//                }elseif($list[$key]['OrderType'] == 2){
//                  $list[$key]['OrderType'] = '消费钱包订单';
//                }elseif($list[$key]['OrderType'] == 3){
//                  $list[$key]['OrderType'] = '商城订单';
//                }
//                $list[$key]['OrderType'] = mb_convert_encoding($list[$key]['OrderType'],'GBK','UTF-8');
                $list[$key]['ReceiveName'] = mb_convert_encoding($list[$key]['ReceiveName'],'GBK','UTF-8');
                $list[$key]['status'] = mb_convert_encoding($list[$key]['status'],'GBK','UTF-8');


                foreach ($goodsinfo as $k => $v) {

                  $list[$key]['UserId'] = $savearr['UserId'];
                  /*拼接商品信息*/
                  $list[$key]['goodsid'] = $goodsinfo[$k]['ProId'];
                  $list[$key]['goodsname'] = $goodsinfo[$k]['ProName'];
                  $list[$key]['goodsstyle'] = $goodsinfo[$k]['StyleName'];
//                  $list[$key]['goodsunit'] = $goodsinfo[$k]['Unit'];
                  $list[$key]['goodsprice'] = $goodsinfo[$k]['Price'];
                  $list[$key]['goodsnum'] = $goodsinfo[$k]['proNum'];
                  $list[$key]['orderinfo'] = '来源：'.$list[$key]['UserId'].'(会员系统账户'.$goodsinfo[$k]['UserId'].')';

                  /*获取供应商名字*/
                  $list[$key]['SupplierId'] = getsupmobilebysupid($savearr['SupplierId']);

                  /*物流信息*/
                  if($list[$key]['ShippingMethod'] == 1){
                    $list[$key]['ShippingMethod']='快递';
                  }else{
                    $list[$key]['ShippingMethod']='物流';
                  }

//                    if($list[$key]['status'] == 4 || $list[$key]['status']==8){
//                        $list[$key]['expstatus'] = '快递/物流公司：'.getexpbyid($savearr['ExpCompanyId']).'运单编号：'.$savearr['AWBNo'].'发货时间：'.$savearr['DeliverDate'];
//                    }else{
//                        $list[$key]['expstatus'] = '';
//                    }

                  /*拼接收货地址*/
                  $list[$key]['trueaddress'] = $savearr['Province'].$savearr['City'].$savearr['County'].$savearr['Address'];
                  /*将老的数据从数组中删除*/
                  
                  foreach($list[$key] as $m => $n) {
                    //$list[$key][$m] = iconv('UTF-8', 'GBK//IGNORE', $n);
                    if($m == 'ReceiveName' || $m == 'status' || $m == 'OrderType'){
                      continue;
                    }
                    $list[$key][$m] = mb_convert_encoding($n,'GBK','UTF-8');
                  }
                   fputcsv($fp, $list[$key]);
                    ob_flush();
                    flush();
                }
              } exit;

            }
        $this->view->assign("orderlist",$data);
        $this->view->assign("pricesum",$pricesum);
        $this->view->assign('page',$orderlist->render());//输出分页的样式
        $this->view->assign('count',$orderlist->total()?$orderlist->total():0);
        return $this->view->fetch('orderList');
    }

    /**
     *未付款取消订单
     */
    public function orderCancel(){
        $orderno=$this->request->param("orderno");
        $odata["Status"]=20;
        $where['InnerOrderId']=$orderno;
        Db::name("ordermain")->where($where)->update($odata);

        //改变库存状态，订单取消库存增加
        $orderdetaillist=indexToLower(Db::name('orderdetail')->where("innerorderid='".$orderno."'")->select());
        foreach($orderdetaillist as $n=>$v){
            $pcount=array_change_key_case(Db::name('productstock')->where("styleid=".$v["styleid"])->field('kucun,kucunweifukuan')->find());
            $datas["Kucun"]=0+$pcount["kucun"]+$v["pronum"];
            $datas["kucunWeifukuan"]=0+$pcount["kucunweifukuan"]+$v["pronum"];
            Db::name('productstock')->where("styleid=".$v["styleid"])->update($datas);
        }

        //将取消记录加入OrderCancelList表
        $ocldata["InnerOrderId"]=$_REQUEST["orderno"];
        $ocldata["CancelAdminId"]=Session::get("adminname");
        $ocldata["CancelType"]=2; //1 用户申请后取消 2 后台直接取消
        $ocldata["BackPay"]=1; //是否返还货款 1 没有返还 2 返还
        Db::name("ordercancellist")->insert($ocldata);
        return json(['status'=>1,'msg'=>'取消成功']);
    }

    /**
     * 主动取消已付款未发货的订单
     */

    public function orderCancelNoDeliver(){
        $orderno=$this->request->param("orderno");
        $where['InnerOrderId']=$orderno;
        $orderxx=array_change_key_case(Db::name("ordermain")->where($where)->find());
        if($orderxx["status"]!=20) {
            $orderalliance = $orderxx["ordertype"]; //订单类型

            $goodsamount = $orderxx["goodsamount"]; //商品金额
            $goodspv = $orderxx["consumeintegral"]; //商品积分
            $givepv=$orderxx["giveintegral"]; //赠送积分
            $userid = $orderxx["userid"]; //订购人ID
            $data["Status"] = 20;  //通过申请后，订单状态变为后台取消
            Db::name("ordermain")->where($where)->update($data);

            $object=Factory::instance()->getObjectInstance('account');
//            $accountData=array(
//                'account_umoney'=>array(
//                    'umoney'=>0+$goodsamount,
//                    'flowtype'=>'返币',
//                    'memo'=>"管理员主动取消订单".$orderno."返还购物币"
//                ),
//                'account_goodspv'=>array(
//                    'goodspv'=>0+$goodspv,
//                    'typename'=>'返积分',
//                    'memo'=>"管理员主动取消订单".$orderno."返还积分"
//                ),
//                'userid'=>$userid,
//                'formwho'=>'admin'
//            );
//            $object->accountAction($accountData);
//
//            $accountData1=array(
//                'account_goodspv'=>array(
//                    'goodspv'=>0-$givepv,
//                    'typename'=>'扣积分',
//                    'memo'=>"管理员主动取消订单".$orderno."扣购物赠送的积分"
//                ),
//                'userid'=>$userid,
//                'formwho'=>'admin'
//            );
//            $object->accountAction($accountData1);

            //取消订单后，将库存返还
            $orderdetaillist = indexToLower(Db::name("orderdetail")->where($where)->select());
            foreach ($orderdetaillist as $n => $val) {
                $object->stockAction($val["styleid"],0+$val["pronum"],0,0+$val["pronum"]);
            }
            return json(['status'=>1,'msg'=>'已申请取消']);
        }else{
            return json(['status'=>0,'msg'=>'请不要重复提交']);
        }
    }
    /**
     * 取消会员申请的已付款未发货的订单
     */
    public function orderCancelConfirm(){
        $orderno=$this->request->param("orderno");
        $where['InnerOrderId']=$orderno;
        $orderxx=array_change_key_case(Db::name("ordermain")->where($where)->find());
        if($orderxx["status"]!=20){
            $now_time=date('Y-m-d H:i:s',time());
            $orderalliance=$orderxx["ordertype"]; //订单类型
            if($orderalliance==1){
                $url=config('API_URL').'/api.php/ope_cre/zero_ope';
            }elseif($orderalliance==2){
                $url=config('API_URL').'/api.php/ope_cre/xiaofei_purse_ope';
            }
            $goodsamount=$orderxx["goodsamount"]; //商品金额
//            $goodspv=$orderxx["consumeintegral"]; //商品积分
//            $givepv=$orderxx["giveintegral"]; //赠送积分
            $userid=$orderxx["userid"]; //订购人ID
            $data["Status"]=20;  //通过申请后，订单状态变为后台取消
            Db::name("ordermain")->where($where)->update($data);
            $senddata['mny']=$goodsamount;
            $senddata['uesrId']=$userid;
            $senddata['type']='add';
            $senddata['ordersn']=$orderno;
            if($orderalliance==1){
                $senddata['shoptype']='零购积分商城';
            }elseif($orderalliance==2){
                $senddata['shoptype']='消费钱包商城';
            }

//                                    var_dump($url);
//                                    var_dump($senddata);exit;
            $returndata=getcurl($url,$senddata);
            if($returndata['status']==1){
                $object=Factory::instance()->getObjectInstance('account');
                $orderdetaillist = indexToLower(Db::name("orderdetail")->where($where)->select());
                foreach ($orderdetaillist as $n => $val) {
                    $object->stockAction($val["styleid"],0+$val["pronum"],0,0+$val["pronum"]);
                }
                return json(['status'=>1,'msg'=>'已通过申请']);
            }else{
                return json(['status'=>0,'msg'=>'操作失败']);
            }
//            $object=Factory::instance()->getObjectInstance('account');
//            $accountData=array(
//                'account_umoney'=>array(
//                    'umoney'=>0+$goodsamount,
//                    'flowtype'=>'返币',
//                    'memo'=>"管理员通过客户申请取消订单".$orderno."返还购物币"
//                ),
//                'account_goodspv'=>array(
//                    'goodspv'=>0+$goodspv,
//                    'typename'=>'返积分',
//                    'memo'=>"管理员通过客户申请取消订单".$orderno."返还积分"
//                ),
//                'userid'=>$userid,
//                'formwho'=>'admin'
//            );
//            $object->accountAction($accountData);
//
//            $accountData1=array(
//                'account_goodspv'=>array(
//                    'goodspv'=>0-$givepv,
//                    'typename'=>'扣积分',
//                    'memo'=>"管理员通过客户申请取消订单".$orderno."扣购物赠送的积分"
//                ),
//                'userid'=>$userid,
//                'formwho'=>'admin'
//            );
//            $object->accountAction($accountData1);

            //取消订单后，将库存返还
//            $orderdetaillist = indexToLower(Db::name("orderdetail")->where($where)->select());
//            foreach ($orderdetaillist as $n => $val) {
//                $object->stockAction($val["styleid"],0+$val["pronum"],0,0+$val["pronum"]);
//            }
//            return json(['status'=>1,'msg'=>'已通过申请']);
        }else{
            return json(['status'=>0,'msg'=>'请不要重复提交']);
        }
    }

    /**
     * 取消已申请取消的订单
     * @return \think\response\Json
     */

    public function orderCancelRefuse(){
        $orderno=$this->request->param("orderno");
        $where['InnerOrderId']=$orderno;
        if(!empty($orderno)){
            $data["Status"]=2;  //取消申请后，订单状态变为已付款
            Db::name("ordermain")->where($where)->update($data);
            return json(['status'=>1,'msg'=>'已取消申请']);
        }else{
            return json(['status'=>1,'msg'=>'请求参数错误']);
        }
    }

    /**
     * 为订单添加备注
     * @return \think\response\Json
     */

    public function orderAddRemarks(){
        $orderno=$this->request->param("orderno");
        if(!empty($orderno)){
            $ordermsg=$this->request->param("ordermsg");
            $data["AdminMessage"]=$ordermsg;
            Db::name("ordermain")->where("innerorderid='".$orderno."'")->update($data);
            return json(['status'=>1,'msg'=>'订单备注添加成功']);
        }else{
            return json(['status'=>0,'msg'=>'请求参数错误']);
        }
    }

    /**
     * 订单详情的展示
     * @param $orderno
     */
    public function orderShow(){
        $orderno=$this->request->param("orderno");
        $orderinfo=array_change_key_case(Db::name("ordermain")->where("innerorderid='".$orderno."'")->find());
        $this->view->assign("orderinfo",$orderinfo);
        $orderdetaillist=indexToLower(Db::name("orderdetail")->where("innerorderid='".$orderno."'")->select());
        $this->view->assign("orderdetail",$orderdetaillist);

        return $this->view->fetch('orderShow');
    }

    public function changeorderstatus(){
        $orderno=$this->request->param('orderno');
        $userid=$this->request->param('userid');
        if(isset($orderno)or isset($userid)){
            if($orderno&&$userid){
                $url='https://'.$_SERVER['HTTP_HOST'].'/api.php/order/orderstatus/orderno/'.$orderno.'/username/'.$userid;
                $data=file_get_contents($url);
                $data=json_decode($data,true);
                if($data['status']==1){
                    return json(['status'=>1,'msg'=>$data['msg']]);
                }else{
                    return json(['status'=>0,'msg'=>$data['msg']]);
                }
            }else{
                return json(['status'=>0,'msg'=>'传递参数有误']);
            }
        }

        return $this->view->fetch('changeorderstatus');
    }
}


