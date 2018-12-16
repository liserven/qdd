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

namespace supplier\index\controller;

use think\Session;
use think\Db;
class Order extends Auth
{
    /**
     * 订单列表
     * @return string
     */
    public function order_list(){

        //dump($this->request->param());exit;
      $where['SupplierId']=Session::get('supplierid');
      $wheres['Id'] = ['>',0];
      if($this->request->param()){
            //根据订单号进行搜索
        if($this->request->param('keyorderid')){
          $keyorderid=$this->request->param('keyorderid');
          $map['keyproid']=$keyorderid;
          $where['InnerOrderId']=$keyorderid;
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
            
            //根据订单类型进行查找
        if($this->request->param("orderclass")!=""){
          $orderclass=$this->request->param("orderclass");
          if($orderclass==1){
            $where["OrderType"]=1;
          }elseif($orderclass==2){
            $where["OrderType"]=2;
          }elseif($orderclass==3){
            $where["OrderType"]=3;
          }
          $map['orderclass']=$orderclass;
        }

            //根据会员id进行查找
        if($this->request->param("keyvip")){
          $keyvip=$this->request->param("keyvip");
          $where["UserId"]=$keyvip;
          $map['keyvip']=$keyvip;
        }

            //根据是否发货进行查找
        if($this->request->param("deliver")){
          $deliver=$this->request->param("deliver");
          if($deliver=='yes'){
            $where["Status"]=4;
            $map['deliver']=$deliver;
          }elseif($deliver=='no'){
            $where["Status"]=['not in',[4,8]];
            $map['deliver']=$deliver;
          }
        }

        //根据交易状态进行查找
        if($this->request->param('Tradingclass')!=''){
          $Tradingclass=$this->request->param('Tradingclass');
          $map['Tradingclass']=$Tradingclass;
          $where['Status']=$Tradingclass;
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
      }
        $orderlist=Db::name('ordermain')->where($where)->order('Id desc')->paginate();//根据条件分页输出
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

//                  if($list[$key]['status'] == 4 || $list[$key]['status']==8){
//                      $list[$key]['expstatus'] = '快递/物流公司：'.getexpbyid($savearr['ExpCompanyId']).'运单编号：'.$savearr['AWBNo'].'发货时间：'.$savearr['DeliverDate'];
//                  }else{
//                      $list[$key]['expstatus'] = '';
//                  }

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

        $this->view->assign("isdeliver",$this->request->param("deliver"));
        $this->view->assign("orderlist",$data);
        $this->view->assign('page',$orderlist->render());//输出分页的样式
        $this->view->assign('count',$orderlist->total()?$orderlist->total():0);
        return $this->view->fetch('order_list');
      }

    /**
     * 配货与发货
     * @return \think\response\Json
     */
    public function order_action(){
     $orderno=$this->request->param("orderno");
     $act=$this->request->param('act');
     if($act=='allocate'){
       if(!empty($orderno)){
         $data["Status"]=3;
         Db::name('ordermain')->where("innerorderid='".$orderno."'")->update($data);
         return json(['status'=>1,'msg'=>'操作成功！']);
       }else{
         return json(['status'=>0,'msg'=>'请求参数错误！']);
       }
     }elseif($act=='deliver'){
       if($this->request->param('type')&&$this->request->param('type')=='add'){
        $deliverData=$this->request->param();
        if(empty($deliverData["supptypes"])){
         return json(['status'=>0,'msg'=>'发货选择错误！']);
       }
       if(empty($deliverData["awbno"])){
         return json(['status'=>0,'msg'=>'发货单号不能为空！']);
       }
       if($deliverData["supptypes"]==1){
         $data["ExpCompanyId"]=$deliverData["classifykd"];
       }else{
         $data["ExpCompanyId"]=$deliverData["classifywl"];
       }
       $kuaidi=Db::name('expresscompany')->where('id='.$deliverData["classifykd"])->find();
       $orddxx=array_change_key_case(Db::name('ordermain')->where("innerorderid='".$deliverData["orderno"]."'")->find());
       if($orddxx["status"]!=4){
         $data["AWBNo"]=$deliverData["awbno"];
         $data["ExpCode"]=$kuaidi["ExpCode"];
         $data["Status"]=4;
         $now_time = date('Y-m-d H:i:s',time());
         $data["DeliverDate"]=$now_time;
         Db::name('ordermain')->where("innerorderid='".$deliverData["orderno"]."'")->update($data);
         $orderxx=array_change_key_case(Db::name('ordermain')->where("innerorderid='".$deliverData["orderno"]."'")->find());
         $orderkcxx=indexToLower(Db::name('orderdetail')->where("innerorderid='".$deliverData["orderno"]."'")->select());
         foreach ($orderkcxx as $val){
           $condition['StyleId']=$val['styleid'];
           $condition['SupplierId']=Session::get('supplierid');
           $stockData=Db::name('productstock')->where($condition)->field('KucunWeifahuo')->find();
           $updateData['KucunWeifahuo']=$stockData['KucunWeifahuo']+$val['pronum'];
           Db::name('productstock')->where($condition)->update($updateData);
         }
                  // $model=Factory::instance()->getObjectInstance('Login');
                  // $mobile= $orddxx['usertel'];
//                   $re=Db::name('sendmodel')->where('id=3')->find();
//                   if($re){
//                       if($re['issend']==0){
//                           $content=$re['content'];
//                           $content=preg_replace('/[#]([a-zA-Z0-9]+)_([a-zA-Z0-9]+)[#]/',$deliverData["orderno"],$content);
//                           $model->sendsms($content,$mobile);
//                       }
//                   }

         return json(['status'=>1,'msg'=>'恭喜您发货成功！']);
       }else{
         return json(['status'=>0,'msg'=>'请不要重复提交！']);
       }
     }else{
       $kd=Db::name('expresscompany')->where("exptype=1")->field('expname,id')->order('id asc')->select();
       $wl=Db::name('expresscompany')->where("exptype=2")->field('expname,id')->order('id asc')->select();
       $this->view->assign("kd",$kd);
       $this->view->assign("wl",$wl);
       return $this->view->fetch('order_delivery');
     }
   }

 }

    /**
     * 订单详情的展示
     * @param $orderno
     */
    public function order_show(){
      $orderno=$this->request->param("orderno");
      if(empty($orderno)){
        $this->error('订单号不能为空');
      }
      $orderinfo=array_change_key_case(Db::name("ordermain")->where("innerorderid='".$orderno."'")->find());

      $this->view->assign("orderinfo",$orderinfo);
      $orderdetaillist=indexToLower(Db::name("orderdetail")->where("innerorderid='".$orderno."'")->select());
      $this->view->assign("orderdetail",$orderdetaillist);

//        print_r($orderinfo);
//        exit();
      return $this->view->fetch('order_show');
    }
    public function order_express(){
        $kuaidi = $this->request->file('kuaidi');
        if($kuaidi){
            vendor("phpoffice.phpexcel.Classes.PHPExcel");
            $objPHPExcel = new \PHPExcel();
            $uploaddir=ROOT_PATH . 'public' . DS . 'Upload'. DS .'kuaidi';
            $info = $kuaidi->validate(['ext'=>'xlsx,xls,csv'])->move($uploaddir);
            $exclePath = $info->getSaveName();
            $file_name = ROOT_PATH . 'public' . DS . 'Upload' . DS .'kuaidi'.DS. $exclePath;   //上传文件的地址
            $type=substr(strrchr($exclePath, '.'), 1);
            if($type=='xlsx'){
                $objReader =\PHPExcel_IOFactory::createReader('excel2007');
            }else{
                $objReader =\PHPExcel_IOFactory::createReader('Excel5');
            }
            $obj_PHPExcel =$objReader->load($file_name, $encode = 'utf-8');  //加载文件内容,编码utf-8

            $excel_array=$obj_PHPExcel->getsheet(0)->toArray();   //转换为数组格式
            array_shift($excel_array);  //删除第一个数组(标题);
            $data = [];

            foreach($excel_array as $k=>$v) {
                if(!empty($v[0])){
                    $where['InnerOrderId'] = $v[0];
                    $where['Status'] = 3;
                    $where['SupplierId'] = Session::get('supplierid');
                    $where1['ExpName'] = ['like', $v[1]];
                    $where1['ExpType'] = 1;
                    $where1['IsShow'] = 1;
                    $code = Db::name('expresscompany')->field('ExpCode,id')->where($where1)->find();
                    $order = Db::name('ordermain')->where($where)->find();
                    if ($order && $code) {
                        $updata['Status'] = 4;
                        $updata['ExpCompanyId'] = $code['id'];
                        $updata['ExpCode'] = $code['ExpCode'];
                        $updata['AWBNo'] = $v[2];
                        $updata['DeliverDate'] = date('Y-m-d H:i:s');
                        $re = Db::name('ordermain')->where($where)->update($updata);
                        if (!$re) {
                            $res = Db::name('kuaidino')->where('orderno="' . $v[0] . '"')->find();
                            if (!$res) {
                                $data[$k]['orderno'] = $v[0];
                                $data[$k]['kuaidi'] = $v[1];
                                $data[$k]['danhao'] = $v[2];
                                $data[$k]['supid'] = Session::get('supplierid');
                            }
                        }
                    } else {
                        if ($order) {
                            $updata['Status'] = 4;
                            $updata['ExpCompanyId'] = 0;
                            $updata['ExpCode'] = '';
                            $updata['AWBNo'] = $v[2];
                            $updata['DeliverDate'] = date('Y-m-d H:i:s');
                            $re = Db::name('ordermain')->where($where)->update($updata);
                            if (!$re) {
                                $res = Db::name('kuaidino')->where('orderno="' . $v[0] . '"')->find();
                                if (!$res) {
                                    $data[$k]['orderno'] = $v[0];
                                    $data[$k]['kuaidi'] = $v[1];
                                    $data[$k]['danhao'] = $v[2];
                                    $data[$k]['supid'] = Session::get('supplierid');
                                }
                            }
                        } else {
                            $res = Db::name('kuaidino')->where('orderno="' . $v[0] . '"')->find();
                            if (!$res) {
                                $data[$k]['orderno'] = $v[0];
                                $data[$k]['kuaidi'] = $v[1];
                                $data[$k]['danhao'] = $v[2];
                                $data[$k]['supid'] = Session::get('supplierid');
                            }
                        }
                    }
                }

            }
//            var_dump($data);exit;
            if(!empty($data)){
                $reup=Db::name('kuaidino')->insertAll($data);
                if($reup){
                    $this->success('成功','order_express');
//                    $this->redirect('order_express');
                }else{
                    $this->error('导入失败');
                }
            }else{
                $this->success('成功','order_express');
            }

//                unlink('./public' . DS . 'Upload' . DS .'kuaidi'.DS. $exclePath);
        }
        $list=Db::name('kuaidino')->where('supid='.Session::get('supplierid'))->paginate();
        $this->view->assign('list',$list);
        $this->view->assign('page',$list->render());
        return $this->view->fetch('order_express');
    }

    public function del_express(){
        $id=$this->request->param('id');
        Db::name('kuaidino')->delete($id);
        $this->redirect(url('Order/order_express'));
    }
  }


