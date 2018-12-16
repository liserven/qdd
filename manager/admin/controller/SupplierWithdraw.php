<?php
/**
 * Created by 赵晓凡
 * User: zhaoxiaofan
 * Date: 2017/3/7
 * Time: 9:14
 */

namespace app\admin\controller;

\think\Loader::import('controller/Jump', TRAIT_PATH, EXT);
use app\admin\Controller;
use think\Db;
use think\Session;

class SupplierWithdraw extends Controller
{
    use \traits\controller\Jump;

    /**
     * 商家提现记录表
     * @return string
     */
    public function index(){
        $where['query_condition']='1=1';
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
            //根据提现的处理状态进行查找
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

        if(count($where)==1){
            unset($where['query_condition']);
            $where='1=1';
        }else{
            unset($where['query_condition']);
        }
        $daochu=$this->request->param('daochu');
        if(isset($daochu)&&$daochu=='daochu'){
            set_time_limit(0);
            $header = ['商家','手机号码', '银行帐号', '银行信息','可提现金额','实际到账余额','处理状态','处理方式','提现审核','提现时间','处理时间'];
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
               // $data[$k]['SupplierId']=getsupnamebysupid($v['SupplierId']);
                $data[$k]['BankInfo']=$v['BankInfo'].$v['BankName'].'/'.$v['BankSupName'];
                array_shift($data[$k]);
                array_unshift($data[$k],$data[$k]['Mobile']=getMobileByTypeId($v['SupplierId']));
                array_unshift($data[$k],getsupnamebysupid($v['SupplierId']));
                unset($data[$k]['BankName']);
                unset($data[$k]['BankSupName']);
               
            }
             //dump($data[$k]);exit;
            if ($error = \Excel::export($header, $data, "商家提现", '2007')) {
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

        return $this->view->fetch('supplier/supplier_withdraw_record');
    }

    public function supplier_withdraw_action(){
        $action=$this->request->param('action');
        if($action=='yes'){
            $dealtype=$this->request->param('dealtype');
            $id=$this->request->param('id');
            $where['id']=$id;
            if($dealtype==1){
                $data['DealType']='银行转账';
            }elseif($dealtype==2){
                $data['DealType']='现金结款';
            }else{
                $data['DealType']='';
            }
            if(!empty($data['DealType'])){
                $data['IsDeal']=1;
                $data['DealDate']=date('Y-m-d H:i:s',time());
                Db::name('withdrawcash')->where($where)->update($data);
                return json(['status'=>1,'msg'=>'审核通过！']);
            }
        }elseif($action=='no'){
            $dealtype=$this->request->param('dealtype');
            $id=$this->request->param('id');

            $recordInfo=Db::name('withdrawcash')->where('id='.$id)->find();
            $where['ID']=$recordInfo['SupplierId'];
            $supplierData=Db::name('supplier')->where($where)->find();
            if($supplierData['WithdrawalType']=='1'){
                $updateData['Account']=$supplierData['Account']+$recordInfo['ActualCreditedAmount'];
                $updateData['WithdrawalAmount']=$updateData['Account']*$supplierData['WithdrawalAmountRate'];
            }elseif($supplierData['WithdrawalType']=='2'){
                $updateData['WithdrawalAmount']=$supplierData['WithdrawalAmount']+$recordInfo['ActualCreditedAmount'];
            }
            Db::name('supplier')->where($where)->update($updateData);
            //添加商家账户信息变更记录
            $recordData["SupplierId"]=$recordInfo['SupplierId'];
            $recordData["FlowType"]="转入";
            $recordData["Amount"]=(0+$recordInfo['ActualCreditedAmount']);
            $recordData["Balance"]=$updateData['Account']; //账户余额
            $recordData["FromWho"]=Session::get('user_name');
            $recordData["OrderNo"]='';
            $recordData["Memo"]="商家提现申请未通过退款";
            $recordData["AddDate"]=date('Y-m-d H:i:s',time());
            Db::name('supplieraccountrecord')->insert($recordData);

            $data['IsDeal']=2;
            $data['DealType']='取消提现';
            $data['DealDate']=date('Y-m-d H:i:s',time());
            Db::name('withdrawcash')->where('id='.$id)->update($data);
            return json(['status'=>1,'msg'=>'操作成功！']);
        }
    }
}