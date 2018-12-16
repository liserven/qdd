<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/20
 * Time: 11:00
 */

namespace api\index\controller;
use think\Db;

class UserSettlement extends Auth
{
    /**
     * 返还佣金测试
     * @return \think\response\Json
     */
    public function test_user_settlement(){
        $orderno=$this->request->param('orderno');
        $ordertype=$this->request->param('ordertype');
//        $returnData = ['status' => 0, 'msg' => '测试 订单号'.$orderno.'测试订单类型'.$ordertype];
//        return json($returnData);
        $returnData=$this->user_settlement($orderno,$ordertype);
//        $sql= "select SUM(Amount) realmoney from pointsflow where UserId='00001'";
//        $returnData= Db::query($sql);
//        echo '<pre>';
//        print_r($returnData[0]);
//        echo '</pre>';
//        exit();
//        $returnData['status'] = 1;
//        $returnData['msg'] = '成功';
//        print_r($returnData);
//        return json($returnData);
        return $returnData;

    }
    /**
     * 产品的佣金结算
     * $orderno         订单号
     * $ordertype       订单类型 inner子订单 outer主订单
     * 推荐关系正确的 测试会员号13598021796，测试主订单20170520133533257104  测试子订单20170520133533257104-210或者20170520133533257104-94
     */
    public function user_settlement($orderno, $ordertype)
    {
        if ($this->loginStatus) {

            $orderData = '';
            if ($ordertype == 'outer') {//主订单号
                $orderData = Db::name('ordermain')->where("OuterOrderId='" . $orderno . "'")->field('UserId,Status,IsRebate')->select();
                $userId = $orderData[0]['UserId'];//订单所属的会员
            } elseif ($ordertype == 'inner') {
                $orderData = Db::name('ordermain')->where("InnerOrderId='" . $orderno . "'")->field('UserId,Status,IsRebate')->find();
                $userId = $orderData['UserId'];//订单所属的会员
            }


            /**
             * 判断订单状态，是否符合返还佣金的条件
             */
            if ($ordertype == 'inner') {//子订单号
                if (empty($orderData)) {
                    $returnData = ['status' => 0, 'msg' => '订单[' . $orderno . ']不存在'];
                    return json($returnData);
                }
                if ($orderData['Status'] == 1) {//订单未支付不允许返佣金
                    $returnData = ['status' => 0, 'msg' => '订单[' . $orderno . ']未支付，请先支付订单再返佣金！'];
                    return json($returnData);
                }
                if ($orderData['IsRebate'] == 2) {//订单已返佣金，不能重复返佣
                    $returnData = ['status' => 0, 'msg' => '订单[' . $orderno . ']已返佣金，不能重复返佣！'];
                    return json($returnData);
                }
            }

            /****开始返还佣金******/
            Db::startTrans();
            try {
                if ($ordertype == 'outer') {//主订单号
                    //汇总该主订单下面的子订单总共要返还的佣金
                    $sql = "select OuterOrderId as oid,sum(moneyOfFirst) as moneyOfFirst,SUM(moneyOfSecond) moneyOfSecond,SUM(moneyOfThird) moneyOfThird,SUM(moneyOfEveryFloor) moneyOfEveryFloor,SUM(moneyOfFirstLeader) moneyOfFirstLeader from ordermain where OuterOrderId='" . $orderno . "' group by OuterOrderId";
                    $queryData = Db::query($sql);
                    $queryData=$queryData[0];
//                    $queryData=Db::name('ordermain')->field('sum(moneyOfFirst) moneyOfFirst ,SUM(moneyOfSecond) moneyOfSecond')->find();
//                    return json($queryData);

                    //调用返佣金的方法
                    $returnData = $this->user_settlement_action($queryData, $userId);


                } elseif ($ordertype == 'inner') {//子订单号
                    $condition['InnerOrderId'] = $orderno;
                    $orderInfo = Db::name('ordermain')->where($condition)->field('InnerOrderId as oid,moneyOfFirst,moneyOfSecond,moneyOfThird,moneyOfEveryFloor,moneyOfFirstLeader')->find();

                    //调用返佣金的方法
                    $returnData = $this->user_settlement_action($orderInfo, $userId);
                }

                if ($returnData['status'] == 1) {
                    $returnData = $this->orderIsRebateChange($orderno,$ordertype);//修改订单为已返还佣金
                    if ($returnData['status'] == 1) {
                        Db::commit();
                        $returnData = $returnData;
                    }else{
                        // 回滚事务
                        Db::rollback();
                        $returnData['status'] = 0;
                        $returnData['msg'] = '修改返佣状态失败';
                    }
                }else{
                    // 回滚事务
                    Db::rollback();
                    $returnData['status'] = 0;
                    $returnData['msg'] = '返还佣金失败';
                }
            }catch (\Exception $e) {
                // 回滚事务
                Db::rollback();
                $returnData['status'] = 0;
                $returnData['msg'] = '返佣失败' . $e->getMessage();
            }
        } else {
            $returnData['status'] = 0;
            $returnData['msg'] = '未登录';
        }

        return json($returnData);
    }

    /**
 * @param $data     返佣金的订单号以及要返还的 【一级佣金，二级佣金，三级佣金；每层佣金；第一个领导人佣金】 的金额
 * @param $userId   订单所属的会员
 * @return array|mixed
 */
    private function user_settlement_action($data, $userId)
    {
        /**
         * 第一步向上的三级分佣
         */
        $client = ismobile() ? '移动端' : 'PC端';

        /**
         * 第二步向上的10层分佣  普通会员享受0层  奖金积分（返积分总和）大于100的享受7层 领导人享受10层
         */
        $IsFindLeader=0;//是否还要向上查找领导人 0是  1否
        $shareFloor = 10;
        $sql = "select userrecommenddiagram.*,usermsg.userType as pidIsLeader,usermsg.ShareFloor from userrecommenddiagram,usermsg ";
        $sql .= " where userrecommenddiagram.pid=usermsg.UserId and  userrecommenddiagram.UserId=" . $userId . "  AND lay<= " . $shareFloor;
        $sql .= " order by userrecommenddiagram.lay asc";
        $queryData = Db::query($sql);
        if (!empty($queryData)) {

            $shareMoney=0;//享受层奖的会员的钱数
            foreach ($queryData as $key => $value) {
                if ($value['pidIsLeader'] == 2) {
                    $IsFindLeader = 1;//修改为不需要向上查找领导人  是否还要向上查找领导人 0是  1否
//                    $money = $shareFloor * $data['moneyOfEveryFloor'] - ($value['lay'] - 1) * $data['moneyOfEveryFloor']+$NoShareUserNumber*$data['moneyOfEveryFloor'];

                    $money = $shareFloor * $data['moneyOfEveryFloor'] - $shareMoney;
//                    $memo = "({$client})会员" . $userId . "购物返还每层佣金[" . $data['oid'] . "]";
                    $memo = "({$client})会员" . $userId . "购物返还[" . $data['oid'] . "]";
                    $accountData = $this->user_settlement_ope($money, $memo, $value['pid'], $userId);

                    /**
                     * 第三步找到第一个领导人返还见点奖
                     */
                    if($accountData['status']==1){
                        $money = $data['moneyOfFirstLeader'];
                        $memo = "({$client})会员" . $userId . "购物返还业务经理[" . $data['oid'] . "]";
                        //返还佣金并生成记录
                        $accountData = $this->user_settlement_ope($money, $memo, $value['pid'], $userId);
                    }

                    break;//找到第一个领导人的时候结束，不再向上继续分佣

                }else if ($value['pidIsLeader'] == 1  && $value['lay']<=7 &&$shareMoney==0) {
                    //当会员所在的层数小于等于该会员能享受的层数时，才能享受奖金（返积分）
                    if($value['lay']<=$value['ShareFloor']){
                        $money = $data['moneyOfEveryFloor']*$value['lay'];
                        $memo = "({$client})会员" . $userId . "购物返还[" . $data['oid'] . "]";
                        $shareMoney=$money;
                        //返还佣金并生成记录
                        $accountData=$this->user_settlement_ope($money,$memo,$value['pid'],$userId);
                    }
                    else{
                        $returnData['status'] = 1;
                        $returnData['msg'] = '操作成功！';
                    }
                }
                else
                {
                    $returnData['status'] = 1;
                    $returnData['msg'] = '操作成功000000！';
                }
            }
            $returnData = $accountData;
        } else {
            $returnData['status'] = 1;
            $returnData['msg'] = '操作成功！';
        }

        //如果需要向上继续找领导人 就找到第一个领导人给与见点奖
        if($IsFindLeader==0){
            $sql = "select userrecommenddiagram.*,usermsg.userType as pidIsLeader from userrecommenddiagram,usermsg ";
            $sql .= " where userrecommenddiagram.pid=usermsg.UserId and  userrecommenddiagram.UserId=" . $userId . "  and usermsg.userType=2  ";
            $sql .= "  order by userrecommenddiagram.lay asc  LIMIT 1";
            $leaderData = Db::query($sql);
            $leaderData=$leaderData[0];
            if (!empty($leaderData)) {
                $money = $data['moneyOfFirstLeader'];
                $memo = "({$client})会员" . $userId . "购物返还业务经理[" . $data['oid'] . "]";
                //返还佣金并生成记录
                $accountData=$this->user_settlement_ope($money,$memo,$leaderData['pid'],$userId);
            }
            else {
                $returnData['status'] = 1;
                $returnData['msg'] = '操作成功！';
            }
        }

        $money = 0;
        $memo = '';

        $where['userId'] = $userId;
        $where['lay'] = ['<', '4'];//找到购物会员上面的三个推荐人
        $userData = Db::name('userrecommenddiagram')->where($where)->order('lay asc')->select();

        if (!empty($userData)) {
            foreach ($userData as $key => $value) {
                if ($value['lay'] == 1) {
                    $money = $data['moneyOfFirst'];
                    $memo = "({$client})会员" . $userId . "购物返还一级[" . $data['oid'] . "]";
                } else if ($value['lay'] == 2) {
                    $money = $data['moneyOfSecond'];
                    $memo = "({$client})会员" . $userId . "购物返还二级[" . $data['oid'] . "]";
                } else if ($value['lay'] == 3) {
                    $money = $data['moneyOfThird'];
                    $memo = "({$client})会员" . $userId . "购物返还三级[" . $data['oid'] . "]";
                }
                //返还佣金并生成记录
                $accountData=$this->user_settlement_ope($money,$memo,$value['pid'],$userId);
            }
            $returnData = $accountData;
        } else {
            $returnData['status'] = 1;
            $returnData['msg'] = '操作成功！';
        }

        return $returnData;
    }

    /**  调用积分账户增减的方法
     * @param $money 积分账户增加的金额
     * @param $memo  备注
     * @param $userId   积分增加的会员
     * @param $formWho  积分的来源
     * @return array|mixed  返回参数
     */
    private function user_settlement_ope($money,$memo,$userId,$formWho){
        $object = Factory::instance()->getObjectInstance('account');
        $accountData = array(
            'account_goodspv' => array(
                'goodspv' => $money,
                'typename' => '返积分',
                'memo' => $memo
            ),
            'userid' => $userId,
            'formwho' => $formWho
        );
        //改变积分账户余额并生成记录
        $accountData = $object->accountAction($accountData);

        return $accountData;

    }
    /** 订单返还佣金状态的修改
     * @param $orderno  要修改返佣状态的订单
     * @param $ordertype 订单的类型
     * @return mixed     返回参数
     */
    private function orderIsRebateChange($orderno,$ordertype)
    {
        $datastatus["IsRebate"] = 2;
        $datastatus["RebateDate"] = date('Y-m-d H:i:s', time());

        if ($ordertype == 'outer') {//主订单号
            Db::name('ordermain')->where("OuterOrderId='" . $orderno . "'")->update($datastatus);//更新订单的状态为已返还佣金
        }elseif ($ordertype == 'inner') {//子订单号
            Db::name('ordermain')->where("InnerOrderId='" . $orderno . "'")->update($datastatus);//更新订单的状态为已返还佣金
        }
        $returnData['status'] = 1;
        $returnData['msg'] = '订单返还佣金成功！';

        return $returnData;
    }
}
