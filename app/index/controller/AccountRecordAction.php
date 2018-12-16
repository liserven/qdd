<?php
/**
 * Created by
 * User:
 * Date: 2018/3/12
 * Time: 10:45
 */

namespace app\index\controller;


use think\Db;

class AccountRecordAction implements ActionInterface
{
    /**账户操作
     * @param $umoney
     * @param $goodspv
     * @param $userid
     * @param $orderno
     * @return mixed
     */
    public function accountAction($param=null)
    {
        $userData=array_change_key_case(Db::name('usermsg')->where("userid='".$param['userid']."'")->find());
        $userData['umoney']=empty($userData['umoney'])?0:$userData['umoney'];
        $userData['pv']=empty($userData['pv'])?0:$userData['pv'];
        $reudata=[];
        $now_time=date('Y-m-d H:i:s',time());
        if(!empty($param['account_umoney'])){
            $umoneyData=$param['account_umoney'];
            if(!empty($umoneyData['umoney'])){
                $yuumoney=$reudata["Umoney"]=$userData["umoney"]+$umoneyData['umoney'];
                if($yuumoney>=0){
                    //在购物币消费记录表里添加记录
                    $datauf["UserId"]=$param['userid'];
                    $datauf["FlowType"]=$umoneyData['flowtype'];
                    $datauf["Amount"]=$umoneyData['umoney'];
                    $datauf["Balance"]=$yuumoney; //剩余购物币
                    $datauf["FromWho"]=$param['formwho'];
                    $datauf["Memo"]=$umoneyData['memo'];
                    $datauf["AddDate"]=$now_time;
                    Db::name('accountrecord')->insert($datauf);
                }else{
                    $returnData['status']=1;
                    $returnData['msg']=$yuumoney;
                    return $returnData;
                }
            }
        }
        if(!empty($param['account_goodspv'])){
            $goodspvData=$param['account_goodspv'];
            if($goodspvData['typename']!='扣积分')
            {
                $yugoodspv=$reudata["Pv"]=$userData["pv"]+$goodspvData['goodspv'];
            }
            if($goodspvData['typename']=='扣积分')
            {
                $yugoodspv=0;
            }
            if(!empty($goodspvData['goodspv'])){
                if($yugoodspv>=0){
                    //在消费积分消费记录表里添加记录
                    $datapf["UserId"]=$param['userid'];
                    $datapf["TypeName"]=$goodspvData['typename'];
                    $datapf["Amount"]=$goodspvData['goodspv'];
                    $datapf["BalancePv"]=$yugoodspv; //剩余消费积分
                    $datapf["FromWho"]=$param['formwho'];
                    $datapf["Memo"]=$goodspvData['memo'];
                    $datapf["AddDate"]=$now_time;
                    Db::name('pointsflow')->insert($datapf);
                }else{
                    $returnData['status']=1;
                    $returnData['msg']='消费积分不足';
                    return $returnData;
                }
            }
        }
        if(!empty($reudata)){
            Db::name('usermsg')->where("userid='".$param['userid']."'")->update($reudata); //扣除相关会员的消费积分和购物币
        }
        $returnData['status']=0;
        $returnData['msg']='操作成功';
        return $returnData;
    }

    /**
     * 订单操作
     * @param $orderno
     * @return mixed
     */

    public function orderAction($orderno=null,$paymethod=null)
    {
        $datastatus["Status"]=2;
        $datastatus["PayDate"]=date('Y-m-d H:i:s',time());
        $datastatus['PayMethod']=$paymethod;
        Db::name('ordermain')->where("innerorderid='".$orderno."'")->update($datastatus);//更新订单的状态为已付款
        $returnData['status']=0;
        $returnData['msg']='操作成功';

        return $returnData;
    }

    /**
     * 库存操作
     * @param $styleid
     * @param $pronum
     * @return mixed
     */
    public function stockAction($styleid=null,$pronum=null,$pronum1=null,$pronum2=null)
    {
        $pcount = array_change_key_case(Db::name('productstock')->where("styleid=" . $styleid)->find());
        $yukucun=$datas["Kucun"]=$pcount["kucun"]+$pronum;
        $datas["kucunWeifukuan"] = $pcount["kucunweifukuan"] + $pronum1;
        $datas["KucunWeifahuo"] = $pcount["kucunweifahuo"] + $pronum2;
        if($yukucun>=0)
        {
            Db::name('productstock')->where("styleid=" . $styleid)->update($datas);
            $returnData['status']=0;
            $returnData['msg']='操作成功';
        }else{
            $returnData['status']=1;
            $returnData['msg']='库存不足';
        }

        return $returnData;
    }

}
