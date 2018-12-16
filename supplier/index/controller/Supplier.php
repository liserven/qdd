<?php
/**
 * Created by 赵晓凡
 * User: zhaoxiaofan
 * Date: 2017/2/17
 * Time: 9:30
 */
namespace supplier\index\controller;


use think\Db;
use think\Session;

class Supplier extends Auth
{
    /**
     * 供应商信息的显示与修改
     * @return mixed
     */
    public function supplier_admin()
    {
        if($this->request->param("action")=="edit"){//供应商信息的修改
            $supData=$this->request->param();
            $photo = $this->request->file('photo');
            if(!empty($photo)){
                $photoImgArr=[];
                foreach ($photo as $file){
                    $uploaddir=ROOT_PATH . 'public' . DS . 'Upload'. DS .'supplier'. DS .'licence';
                    $info = $file->validate(['size'=>3145728,'ext'=>'jpg,png,gif,jpeg'])->move($uploaddir);
                    if($info){
                        $photoImgArr[]=$info->getPathInfo()->getFilename().'/'.$info->getFilename();
                    }else{
                        $this->error($info->getError(),url('Supplier/supplierEdit',['act'=>'edit','id'=>$id]));
                    }
                }
            }
            $data["Name"]=$supData["name"];
            $data["Mobile"]=$supData["mobile"];
            $data["WeChatName"]=$supData["wechatname"];
            $data["TelPhone"]=$supData["telphone"];
            $data["BankAccount"]=$supData["bankaccount"];
            $data["BankName"]=$supData["bankname"];
            $data["BankInfo"]=$supData["bankinfo"];
            $data["BankSupName"]=$supData["banksupname"];
            $data["SupNetwork"]=$supData["supnetwork"];
            $data["Province"]=$supData["province"];
            $data["City"]=$supData["city"];
            $data["Area"]=$supData["area"];
            $data["Address"]=$supData["address"];
            //$data["Remark"]=$supData["remark"];
            $data["EditDate"]=date('Y-m-d H:i:s',time());
            Db::name('supplier')->where("id=".Session::get('supplierid'))->update($data);//修改数据库
            if(!empty($photoImgArr)){
                foreach ($photoImgArr as $photoPath){
                    $datah["sid"]=Session::get('supplierid');
                    $datah["imgpath"]=$photoPath;
                    Db::name('supplierhornor')->insert($datah);
                }
            }
            $this->success("信息修改成功",url('Supplier/supplier_admin'));
        }else {//供应商信息的显示
            $supplierid = Session::get('supplierid');
            $suplist = array_change_key_case(Db::name('supplier')->where("id=" . $supplierid)->find());
            $this->view->assign("supxx", $suplist);
            $supcates = indexToLower(Db::name('supplierpcat')->where("supid=" . $supplierid . " and pcatelevel=1")->select());
            if ($supcates) {
                foreach ($supcates as $n => $val) {
                    $supcates[$n]['voo'] = indexToLower(Db::name('supplierpcat')->where('pid=' . $val['pcateid'] . ' and supid=' . $supplierid)->select());
                    foreach ($supcates[$n]['voo'] as $m => $valm) {
                        $supcates[$n]['voo'][$m]['boo'] = indexToLower(Db::name('supplierpcat')->where('pid=' . $valm['pcateid'] . ' and supid=' . $supplierid)->select());
                    }
                }
            }
            $this->view->assign("listcate", $supcates);
            $suphornors = Db::name('supplierhornor')->where("sid=" . $supplierid)->select();
            if ($suphornors) {
                $this->view->assign("listhornor", $suphornors);
            } else {
                $this->view->assign("listhornor", []);
            }
            return $this->view->fetch('supplier_admin');
        }
    }

    /**
     * 供应商资质图片的删除
     * @return mixed
     */
   	public function supplier_hornor_del(){
        if($this->request->param("action")=="delimg"){//供应商资质图片的删除
            $id=$this->request->param("id");
            $suphordel=Db::name('supplierhornor')->where("id=".$id)->find();
            $horpath= "./Upload/supplier/licence/".$suphordel["imgpath"];
            Db::name('supplierhornor')->where("id=".$id)->delete();
            if(file_exists($horpath)){
                unlink($horpath);
            }
            return json(['code'=>200,'删除成功']);
            //$this->redirect(url("Supplier/supplier_hornor_del"));
        }else{
            $suphornors=Db::name('supplierhornor')->where("sid=".Session::get('supplierid'))->select();
            if($suphornors){
                $this->view->assign("listhornor",$suphornors);
            }
            return $this->view->fetch('supplier_hornor_del');
        }
    }

    /**
     * 供应商密码的修改
     */
    public function supplier_password(){
        if($this->request->param("action")=="modify"){
            $supplierid=Session::get('supplierid');
            $chkpass=Db::name('supplier')->where("loginpasswd='".md5($this->request->param("oldpassword"))."' and id=".$supplierid)->find();
            if(!$chkpass){
                return json(['status'=>0,'msg'=>'原始密码输入错误！']);
            }else{
                $data["LoginPasswd"]=md5($this->request->param("newpassword"));
                Db::name('supplier')->where("id=".$supplierid)->update($data);
                return json(['status'=>1,'msg'=>'密码修改成功！']);
            }
        }else{
            $this->view->assign('name',Session::get('suppliername'));
            return $this->view->fetch('supplier_password');
        }
    }

    public function supplier_count_record(){
        $where['SupplierId']=Session::get('supplierid');
//var_dump($this->request->param());exit;
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
            $where['OrderNo']=$orderno;
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
        $list=Db::name('supplieraccountrecord')->where($where)->order('Id desc')->paginate(15);//根据条件分页输出
        $pricesum=Db::name('supplieraccountrecord')->where($where)->sum("amount");
        $data=[];
        foreach($list as $n=>$val){
            $data[$n]=array_change_key_case($val);
        }
        if(!empty($map)) {
            foreach ($map as $key => $value) {
                $list->appends($key, $value);//分页链接中添加请求的参数
            }
        }

        $this->view->assign("supsetrecordlist",$data);
        $this->view->assign("pricesum",$pricesum);
        $this->view->assign("count",$list->total()?$list->total():0);
        $this->view->assign('page',$list->render());// 赋值分页输出
        return  $this->view->fetch('supplier_count_record');
    }

    public function supplier_count_show(){
        $orderno=$this->request->param('orderno');//结算的订单号
        $supid=Session::get('supplierid');//结算的商家ID

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

        $pricesumlist=Db::query("select sum(odd.balanceprice*odd.pronum) as pricesumbalance from ordermain odm,orderdetail odd where odm.InnerOrderId='".$orderno."' and odm.supplierid=".$supid." and odm.status=8 and odm.isdepot=0 and odm.innerorderid=odd.innerorderid");

        $this->view->assign("pricesumbalance",$pricesumlist[0]["pricesumbalance"]);
        $this->view->assign("odmlist",$data);
        $this->view->assign("count",$odmlist->total()?$odmlist->total():0);
        $this->view->assign('page',$odmlist->render());// 赋值分页输出

        return $this->view->fetch('supplier_count_show');
    }

    /**
     * 供应商提现
     * @return array
     */
    public function supplier_withdraw(){
        $act=$this->request->param('act');
        if(!empty($act)&&$act=='submit'){
            $withdrawmoney=$this->request->param('withdrawmoney');
            if(empty($withdrawmoney)){
                return ['status'=>0,'msg'=>'提现金额不能为空或者为零!'];
            }
            if($withdrawmoney<0){
                return ['status'=>0,'msg'=>'提现金额必须为非负数字!'];
            }
            $where['ID']=Session::get('supplierid');
            $supplierData=Db::name('supplier')->where($where)->find();
            $temp=$withdrawmoney/$supplierData['WithdrawalAmountLimit'];
            if(!(floor($temp)==$temp&&ceil($temp)==$temp&&$temp*$supplierData['WithdrawalAmountLimit']==$withdrawmoney)){
                return ['status'=>0,'msg'=>'提现金额必须为的'.$supplierData['WithdrawalAmountLimit'].'倍数!'];
            }
            if($withdrawmoney>$supplierData['WithdrawalAmount']){
                return ['status'=>0,'msg'=>'提现金额不能大于可提现金额!'];
            }
            if($supplierData['WithdrawalAmount']>$supplierData['Account']){
                return ['status'=>0,'msg'=>'可提现金额不能大于账户余额!'];
            }
            if($supplierData['WithdrawalType']=='1'){
                $updateData['Account']=$supplierData['Account']-$withdrawmoney;
                $updateData['WithdrawalAmount']=$updateData['Account']*$supplierData['WithdrawalAmountRate'];
            }elseif($supplierData['WithdrawalType']=='2'){
                $updateData['Account']=$supplierData['Account']-$withdrawmoney;
                $updateData['WithdrawalAmount']=$supplierData['WithdrawalAmount']-$withdrawmoney;
            }
            Db::name('supplier')->where($where)->update($updateData);
            //添加商家账户信息变更记录
            $recordData["SupplierId"]=Session::get('supplierid');
            $recordData["FlowType"]="转出";
            $recordData["Amount"]=(0-$withdrawmoney);
            $recordData["Balance"]=$updateData['Account']; //账户余额
            $recordData["FromWho"]=Session::get('supplierid');
            $recordData["OrderNo"]='';
            $recordData["Memo"]="商家提现";
            $recordData["AddDate"]=date('Y-m-d H:i:s',time());
            Db::name('supplieraccountrecord')->insert($recordData);
            //添加提现记录
            $wdrecordData['SupplierId']=Session::get('supplierid');
            $wdrecordData['BankAccount']=$supplierData['BankAccount'];
            $wdrecordData['BankInfo']=$supplierData['BankInfo'];
            $wdrecordData['BankName']=$supplierData['BankName'];
            $wdrecordData['BankSupName']=$supplierData['BankSupName'];
            $wdrecordData['WithdrawalAmount']=$updateData['WithdrawalAmount'];
            $wdrecordData['ActualCreditedAmount']=$withdrawmoney;
            $wdrecordData['IsDeal']=0;
            $wdrecordData['AddDate']=date('Y-m-d H:i:s',time());
            Db::name('withdrawcash')->insert($wdrecordData);
            return ['status'=>1,'msg'=>'提现成功，请等待管理后台处理!'];
        }else{
            $where['ID']=Session::get('supplierid');
            $supplierData=Db::name('supplier')->where($where)->field('Name,Mobile,Account,WithdrawalAmount,BankName,BankSupName,BankAccount,BankInfo')->find();
            $this->view->assign('supinfo',$supplierData);
            return $this->view->fetch('supplier_withdraw');
        }
    }

    public function supplier_withdraw_record(){
        $where['SupplierId']=Session::get('supplierid');
        if ($this->request->param("isdeal")||$this->request->param("isdeal")==='0'){
            $isdeal=$this->request->param("isdeal");
            $where["IsDeal"]=$isdeal;
            $map['isdeal']=$isdeal;
        }
        //根据日期进行查找
        if ($this->request->param("datemin") and $this->request->param("datemax")) {
            $requestdate = $this->request->param("datemax");
            $redate = date("Y-m-d", strtotime("$requestdate +1 day"));
            $where["AddDate"] = array(array('egt', $this->request->param("datemin")), array('elt', $redate), 'and');
            $map["datemin"] = $this->request->param("datemin");
            $map["datemax"] = $this->request->param("datemax");
        } elseif ($this->request->param("datemin")) {
            $where["AddDate"] = array('egt', $this->request->param("datemin"));
            $map["datemin"] = $this->request->param("datemin");
        } elseif ($this->request->param("datemax")) {
            $requestdate = $this->request->param("datemax");
            $redate = date("Y-m-d", strtotime("$requestdate +1 day"));
            $where["AddDate"] = array('elt', $redate);
            $map["datemax"] = $this->request->param("datemax");
        }
        $daochu=$this->request->param('daochu');
        if(isset($daochu)&&$daochu=='daochu'){
            set_time_limit(0);
            $header = ['商家','手机号码', '银行帐号', '银行信息','可提现金额','实际到账余额','处理状态','处理方式','提现时间','处理时间'];
            $data = Db::name("withdrawcash")->where($where)->order('id desc')->select();
            foreach($data as $k=>$v){
                unset($data[$k]['id']);
                if($v['IsDeal']==0){
                    $data[$k]['IsDeal']='未处理';
                }elseif($v['IsDeal']==1){
                    $data[$k]['IsDeal']='已通过';
                }else{
                    $data[$k]['IsDeal']='未通过';
                }
                $data[$k]['BankAccount']=$v['BankAccount'].'('.$v['BankSupName'].')';
                $data[$k]['SupplierId']=$data[$k]['SupplierId'].getsupnamebysupid($v['SupplierId']);
                $data[$k]['BankInfo']=$v['BankInfo'].$v['BankName'];
                unset($data[$k]['BankName']);
                unset($data[$k]['BankSupName']);
                $suid = $data[$k]['SupplierId'];
                unset($data[$k]['SupplierId']);
                array_unshift($data[$k],getMobilebysupid($v['SupplierId']));
                array_unshift($data[$k],$suid);
            }
            //dump($data[$k]);exit;
            if ($error = \Excel::export($header, $data, "提现记录", '2007')) {
                throw new Exception($error);
            }
        }
        $cashRecord=Db::name('withdrawcash')->where($where)->order('id desc')->paginate(15);
        $data=[];
        foreach($cashRecord as $n=>$val){
            $data[$n]=$val=array_change_key_case($val);
        }
        if(!empty($map)) {
            foreach ($map as $key => $value) {
                $cashRecord->appends($key, $value);//分页链接中添加请求的参数
            }
        }
        $this->view->assign("cashrecord",$data);
        $this->view->assign('page',$cashRecord->render());//输出分页的样式
        $this->view->assign('count',$cashRecord->total()?$cashRecord->total():0);
        return $this->view->fetch('supplier_withdraw_record');
    }
}
