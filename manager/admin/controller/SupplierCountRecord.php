<?php
/**
 * Created by 赵晓凡
 * User: zhaoxiaofan
 * Date: 2017/3/1
 * Time: 9:35
 */

namespace app\admin\controller;
\think\Loader::import('controller/Jump', TRAIT_PATH, EXT);

use app\admin\Controller;
use think\Db;

class SupplierCountRecord extends Controller
{
    use \traits\controller\Jump;

    public function index(){
        $where['Id']=['>','0'];

        //根据商家id进行查找
        if($this->request->param('supid')){
            $supid=$this->request->param('supid');
            $where['SupplierId']=$supid;
            $map['supid']=$supid;
        }

        //根据商家名称进行查找
        if($this->request->param('supname')){
            $supname=$this->request->param('supname');
            $chcondition['Name']=array('like','%'.$supname.'%');
            $supidlist=Db::name('supplier')->where($chcondition)->field('id')->select();
            $aa=[];
            for($i=0;$i<count($supidlist);$i++){
                $aa[$i]=$supidlist[$i]['id'];
            }
            if(empty($aa)){
                $where['SupplierId']=0;
            }else{
                $where['SupplierId']=array('in',implode(',',$aa));
                $map['supname']=$supname;
            }
        }

        //根据交易类型查询
        if($this->request->param('flowType')){
            $recordType=$this->request->param('flowType');
            $map['flowType']=$recordType;
            $where['FlowType']=['like','%'.$recordType.'%'];
        }

        //根据金额来源进行查找
        if($this->request->param('fromwho')){
            $fromwho=$this->request->param('fromwho');
            $where['FromWho']=$fromwho;
            $map['fromwho']=$fromwho;
        }

        //根据订单号进行查找
        if($this->request->param('orderno')){
            $orderno=$this->request->param('orderno');
            $where['ORderNo']=$orderno;
            $map['orderno']=$orderno;
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

        $daochu=$this->request->param('daochu');
        if(isset($daochu)&&$daochu=='daochu'){
            set_time_limit(0);
            $count= $list=Db::name('supplieraccountrecord')->where($where)->count();
            if($count>10000){
                $nums=500;
                $totalcount=ceil($count/500);
            }else{
                $nums=100;
                $totalcount=ceil($count/100);
            }
            $obj_phpexcel = new \PHPExcel();
            $obj_phpexcel->getActiveSheet()->setCellValue('a1', '供应商名称');
            $obj_phpexcel->getActiveSheet()->getColumnDimension('a')->setWidth(20);
            $obj_phpexcel->getActiveSheet()->setCellValue('b1', '交易类型');
            $obj_phpexcel->getActiveSheet()->getColumnDimension('b')->setWidth(20);
            $obj_phpexcel->getActiveSheet()->setCellValue('c1', '交易金额');
            $obj_phpexcel->getActiveSheet()->getColumnDimension('c')->setWidth(20);
            $obj_phpexcel->getActiveSheet()->setCellValue('d1', '余额');
            $obj_phpexcel->getActiveSheet()->getColumnDimension('d')->setWidth(20);
            $obj_phpexcel->getActiveSheet()->setCellValue('e1', '来源');
            $obj_phpexcel->getActiveSheet()->getColumnDimension('e')->setWidth(50);
            $obj_phpexcel->getActiveSheet()->setCellValue('f1', '备注');
            $obj_phpexcel->getActiveSheet()->getColumnDimension('f')->setWidth(50);
            $obj_phpexcel->getActiveSheet()->setCellValue('g1', '添加时间');
            $obj_phpexcel->getActiveSheet()->getColumnDimension('g')->setWidth(20);
            $number = 97;
            $i = 2;
            for($s=1;$s<=$totalcount;$s++){
                $star=($s-1)*$nums;
                $modellist=Db::name('supplieraccountrecord')->field('SupplierId,FlowType,Amount,Balance,FromWho,Memo,AddDate,OrderNo')->where($where)->order('Id desc')->select();//根据条件分页输出
                foreach($modellist as $key=>$val){
                    $modellist[$key]['SupplierId']=supnamebysupid($val['SupplierId']);
                    if($val['Memo']=='商家提现'){
                        $modellist[$key]['FromWho']='商家'.$val['SupplierId'];
                    }elseif($val['Memo']=='商家提现申请未通过退款'){
                        $modellist[$key]['FromWho']='管理后台'.$val['FromWho'];
                    }else{
                        $modellist[$key]['FromWho']='会员'.$val['FromWho'].'订单号'.$val['OrderNo'];
                    }
                    unset($modellist[$key]['OrderNo']);
                }
                if(!empty($modellist)){
                    foreach($modellist as $key1 => $value1){
                        foreach($value1 as $key2 => $value2){
                            //将ASKII码换算成普通字符
                            $str = chr($number);
                            $obj_phpexcel->getActiveSheet()->setCellValue($str . $i, $value2);
                            //自增，添加列数据
                            $number++;
                        }
                        //自增，添加行数据
                        $i++;
                        $number = 97;
                    }
                }
            }
            $obj_Writer = \PHPExcel_IOFactory::createWriter($obj_phpexcel, 'Excel5');
            $filename = "结算记录(" . date('Y-m-d H:i:s') . ").xls";
            header("Content-Type: application/force-download");
            header("Content-Type: application/octet-stream");
            header("Content-Type: application/download");
            header('Content-Disposition:inline;filename="' . $filename . '"');
            header("Content-Transfer-Encoding: binary");
            header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Pragma: no-cache");
            $obj_Writer->save('php://output');
            exit();
        }
        $sumData=Db::name('supplieraccountrecord')->where($where)->field('FlowType,sum(Amount) as Amount')->group('FlowType')->select();
        $list=Db::name('supplieraccountrecord')->where($where)->order('Id desc')->paginate(15);//根据条件分页输出


        $staticPageArr=$staticArr=[['FlowType'=>'转入','Amount'=>0],['FlowType'=>'转出','Amount'=>0]];
        if(!empty($sumData)){
            foreach ($sumData as $k=>$v){
                if($v['FlowType']=='转入'){
                    $staticArr[0]=$v;
                }
                if($v['FlowType']=='转出'){
                    $staticArr[1]=$v;
                }
            }
        }

        $data=[];
        foreach($list as $n=>$val){
            if($val['FlowType']=='转入'){
                $staticPageArr[0]['Amount']+=$val['Amount'];
            }
            if($val['FlowType']=='转出'){
                $staticPageArr[1]['Amount']+=$val['Amount'];
            }
            $data[$n]=array_change_key_case($val);
        }
        if(!empty($map)) {
            foreach ($map as $key => $value) {
                $list->appends($key, $value);//分页链接中添加请求的参数
            }
        }

        $this->view->assign("supsetrecordlist",$data);
        $this->view->assign("count",$list->total()?$list->total():0);
        $this->view->assign('page',$list->render());// 赋值分页输出
        $this->view->assign('staticPageArr',$staticPageArr);
        $this->view->assign('staticArr',$staticArr);

        return  $this->view->fetch('supplier/supplierCountRecord');
    }

    //结算金额的明细展示
    public function supplierCountShow(){
        $orderno=$this->request->param('orderno');//结算的订单号
        $supid=$this->request->param('supid');//结算的商家ID

        $where['InnerOrderId']=$orderno;
        $where["SupplierId"]=$supid;
        $where["Status"]=8;

        $odmlist=Db::name('ordermain')->where($where)->paginate(10);

        $data=[];
        foreach($odmlist as $n=>$val){
            $data[$n]=$val=array_change_key_case($val);
            $data[$n]["voo"]=indexToLower(Db::name('orderdetail')->where("innerorderid='".$val['innerorderid']."'")->select());
            $data[$n]['balancesum']=$ooddmm=indexToLower(Db::name('orderdetail')->field('sum(balanceprice*pronum) as bsum')->where("innerorderid='".$val['innerorderid']."'")->group('innerorderid')->select());
        }

        $pricesumlist=Db::query("select sum(odd.balanceprice*odd.pronum) as pricesumbalance from ordermain odm,orderdetail odd where odm.InnerOrderId='".$orderno."' and odm.supplierid=".$supid." and odm.status=8 and  odm.innerorderid=odd.innerorderid");

        $this->view->assign("pricesumbalance",$pricesumlist[0]["pricesumbalance"]);
        $this->view->assign("odmlist",$data);
        $this->view->assign("count",$odmlist->total()?$odmlist->total():0);
        $this->view->assign('page',$odmlist->render());// 赋值分页输出

        return $this->view->fetch('supplier/supplierCountShow');
    }
}