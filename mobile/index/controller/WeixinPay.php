<?php 
namespace mobile\index\controller;

\think\Loader::import('controller/Jump', TRAIT_PATH, EXT);

use think\Db;
use think\Config;
use think\Session;

class WeixinPay  extends Common{
    use \traits\controller\Jump;

    // 微信支付回调通知
    public function notify($type = null,$orderid){
        //echo $type;
        // 测试注册回调
        if($type == 1){
            $result['attach'] = '激活支付';
            $result['out_trade_no'] = $orderid;
            $result['total_fee'] = '6000.00';
            $result['result_code'] = 'SUCCESS';
        }

        // 测试购买会员回调
        if($type == 2){
            $result['attach'] = '购买推荐名额';
            $result['out_trade_no'] = $orderid;
            $result['total_fee'] = '2500';
            $result['result_code'] = 'SUCCESS';
        }
       

    	// 获得微信返回的数据
    	//$result = $this->xmlToArray(file_get_contents('php://input'));
        //dump($result);
    	if($result['result_code'] != 'SUCCESS'){
    		return ;
    	}

    	// 校验签名
    	
    	// 校验订单号
    	
    	// 校验金额
    	// 若是attach为激活支付，则按照激活处理
    	if($result['attach'] == '激活支付'){
            // 获取订单信息
            $where['orderid'] = $result['out_trade_no'];
            $orderinfo = db::name('order_places')->where($where)->find();
            dump($orderinfo);

    		$whereu['UserId'] = $UserId = $orderinfo['UserId'];
    		// 获取个人信息
    		// 若用户不存在，直接返回，不做后续处理
    		$userinfo = db::name('usermsg')->where($whereu)->find();
            
    		if(!$userinfo){
    			return;
    		}
    		// 获取用户类型列表
    		$usertype_info = db::name('usertype')->select();
    		// 校验金额失败，订单金额与支付金额不一致，直接返回，不做后续处理
    		/*if($usertype_info[$userinfo['userType']-1]['annualfee_price'] != $result['total_fee']){
    			return;
    		}*/
            
            // 更新订单状态
            $datao['status'] = 2;
            $datao['pay_way'] = '微信';
            $datao['pay_time'] = date('Y-m-d H:i:s',time());
            $rs = db::name('order_places')->where($where)->update($datao);
    		$this->pay_annual_free($userinfo);


    	}
    	// 若是attach为购买推荐名额，则按照购买处理
    	if($result['attach'] == '购买推荐名额'){
    		// 校验订单号
	        $where['orderid'] = $result['out_trade_no'];
	        $orderinfo = db::name('order_places')->where($where)->find();
	        // 订单号不存在，直接返回，不做后续处理
	        if(!$orderinfo){
	        	return;
	        }
	        // 校验金额失败，订单金额与支付金额不一致，直接返回，不做后续处理
	        /*if($orderinfo['totleprice'] != $result['total_fee']){
	        	return;
	        }*/

    		$this->deal_places_order($result['out_trade_no']);
    	}



    	//echo  "SUCCESS";
    }

    //会员充值年费激活账户
    public function pay_annual_free($userinfo){
      
        // 查询用户类型列表，并扣除推荐人的推荐名额
        $usertype_info = db::name('usertype')->select();
        $where['UserId'] = Session::get('membername');

        $wherepid['UserId'] =  $pid = $userinfo['ReferrerID'];

        if(!$pid){
            return json(['status'=>0,'msg'=>'注册失败，推荐人有误！']);
        }
        $puserinfo = db::name('usermsg')->where($wherepid)->find();
        switch ($userinfo['userType']) {
            case '1':
                // 判断推荐人推荐名额是否充足
                if($puserinfo['one_level_places'] < 1){
                    return json(['status'=>0,'msg'=>'注册失败，推荐人标准版名额不足！']);
                }
                // 扣除推荐人推荐名额
                $rs1 = db::name('usermsg')->where($wherepid)->setDec('one_level_places');
                $datar['Balance'] = $puserinfo['one_level_places'] - 1;
                // 赠送注册人推荐名额
                $datau['one_level_places'] = $usertype_info['0']['one_level_places'];
                $data2['Amount'] = $usertype_info['0']['one_level_places'];
                $data2['Balance'] = $userinfo['one_level_places']+ $usertype_info['0']['one_level_places'];
                break;
            case '2':
                if($puserinfo['two_level_places'] < 1){
                    return json(['status'=>0,'msg'=>'注册失败，推荐人尊享版名额不足！']);
                }
                $rs1 = db::name('usermsg')->where($wherepid)->setDec('two_level_places');
                $datar['Balance'] = $puserinfo['two_level_places'] - 1;

                $datau['two_level_places'] = $usertype_info['0']['two_level_places'];

                $data2['Amount'] = $usertype_info['1']['two_level_places'];
                $data2['Balance'] = $userinfo['two_level_places']+ $usertype_info['1']['two_level_places'];
                break;
            case '3':
                if($puserinfo['three_level_places'] < 1){
                    return json(['status'=>0,'msg'=>'注册失败，推荐人联盟商名额不足！']);
                }
                $rs1 = db::name('usermsg')->where($wherepid)->setDec('three_level_places');
                $datar['Balance'] = $puserinfo['three_level_places'] - 1;

                $datau['three_level_places'] = $usertype_info['1']['three_level_places'];

                $data2['Amount'] = $usertype_info['2']['three_level_places'];
                $data2['Balance'] = $userinfo['three_level_places']+ $usertype_info['2']['three_level_places'];
                break;
            case '4':
                if($puserinfo['four_level_places'] < 1){
                    return json(['status'=>0,'msg'=>'注册失败，推荐人营销中心名额不足！']);
                }
                $rs1 = db::name('usermsg')->where($wherepid)->setDec('four_level_places');
                $datar['Balance'] = $puserinfo['four_level_places'] - 1;

                $datau['four_level_places'] = $usertype_info['2']['four_level_places'];

                $data2['Amount'] = $usertype_info['3']['four_level_places'];
                $data2['Balance'] = $userinfo['four_level_places']+ $usertype_info['3']['four_level_places'];
                break;
        }
        //生成扣除推荐人名额记录
        $datar['UserId'] = $pid;
        $datar['FlowType'] = 5;
        $datar['Amount'] = -1;
        //$data['Balance'] = $puserinfo['one_level_places'] - 1;
        $datar['FromWho'] = Session::get('membername');
        $datar['Memo'] = '推荐'.Session::get('membername').'消耗一个'.$usertype_info[$userinfo['userType']-1]['Name'].'名额';
        $datar['AddDate'] = date('Y-m-d H:i:s',time());
        $rs3 = db::name('accountplaces')->insert($datar);

        //如果注册人赠送有推荐名额，则生成增加注册人推荐名额记录
       
        if($usertype_info[$userinfo['userType']-1]['one_level_places'] || $usertype_info[$userinfo['userType']-1]['two_level_places'] || $usertype_info[$userinfo['userType']-1]['three_level_places'] || $usertype_info[$userinfo['userType']-1]['four_level_places']){
            $data2['UserId'] = Session::get('membername');
            $data2['FlowType'] = 6;
            //$data['Amount'] = $add_places;
            //$data['Balance'] = $puserinfo['one_level_places'] - 1;
            $data2['FromWho'] = Session::get('membername');
            $data2['Memo'] = '注册获得'.$data2['Amount'].'个'.$usertype_info[$userinfo['userType']-1]['Name'].'名额';
            $data2['AddDate'] = date('Y-m-d H:i:s',time());
            $rs3 = db::name('accountplaces')->insert($data2);
        }
        

        $datau['IsAudit'] = 3;
        $rs2 = db::name('usermsg')->where($where)->update($datau);
        Db::startTrans();
        if($rs1 && $rs2){
            Db::commit();    
        }else{
            Db::rollback();
        }
        
    }


     // 购买推荐名额支付成功后，通知订单处理
    public function deal_places_order($orderid){
        // 获取订单信息
        $where['orderid'] = $orderid;
        $orderinfo = db::name('order_places')->where($where)->find();
        // 获取用户信息
        $whereu['UserId'] = $orderinfo['UserId'];
        $userinfo = db::name('usermsg')->where($whereu)->find();
        // 查询用户类型列表
        $usertype_info = db::name('usertype')->select();
        if($orderinfo['status'] == 1){
            // 更新订单状态
            $datao['status'] = 2;
            $datao['pay_way'] = '微信';
            $datao['pay_time'] = date('Y-m-d H:i:s',time());
            $rs = db::name('order_places')->where($where)->update($datao);

            // 给购买人添加推荐名额
            switch($orderinfo['usertype']){
                case 1 : 
                    $dateu['one_level_places'] = $userinfo['one_level_places'] + $orderinfo['number'];

                    break;
                case 2 : $datar['Amount'] = $dateu['two_level_places'] = $userinfo['two_level_places'] + $orderinfo['number'];break;
                case 3 :  $datar['Amount'] =$dateu['three_level_places'] = $userinfo['three_level_places'] + $orderinfo['number'];break;
                case 4 :  $datar['Amount'] =$dateu['four_level_places'] = $userinfo['four_level_places'] + $orderinfo['number'];break;
            }
            db::name('usermsg')->where($whereu)->update($dateu);
            // 将购买名额添加到记录表
            $datar['UserId'] = Session::get('membername');
            $datar['FlowType'] = 7;
            $datar['FromWho'] = Session::get('membername');
            $datar['Amount'] = $orderinfo['number'];
            $datar['Memo'] = '购买获得'.$datar['Amount'].'个'.$usertype_info[$orderinfo['usertype']-1]['Name'].'名额';
            $datar['AddDate'] = date('Y-m-d H:i:s',time());
            $rs3 = db::name('accountplaces')->insert($datar);
        }
    }

    // 注册激活支付成功页面
    public function active_pay_successful(){
        Session::set('membername',null);
        //$orderid = $this->request->param('orderid');
        //$this->notify(1,$orderid);
        return $this->view->fetch();
    }

    // 购买推荐名额支付成功页面
    public function buy_places_pay_successful(){
        $orderid = $this->request->param('orderid');
        $this->notify(2,$orderid);
        return $this->view->fetch();
    }

    // 将数组转化为XML
	public function arrayToXml($arr){
	    $xml = "<xml>";
	    foreach ($arr as $key=>$val){
	        if (is_numeric($val)){
	            $xml.="<".$key.">".$val."</".$key.">";
	        }else{
	             $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
	        }
	    }
	    $xml.="</xml>";
	    return $xml;
	}

	//将XML转为array
	public function xmlToArray($xml){
	    //禁止引用外部xml实体
	    libxml_disable_entity_loader(true);
	    $values = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
	    return $values;
	}
}
