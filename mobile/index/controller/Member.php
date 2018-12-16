<?php
/**
 * Created by 赵晓凡
 * User: zhaoxiaofan
 * Date: 2017/3/13
 * Time: 16:10
 */
namespace mobile\index\controller;
use think\Session;
use think\Db;
use think\Request;
class Member extends Auth
{
    /**
     * 会员地址添加、修改页面
     * @return mixed
     */
    public function member_address(){
        $type=$this->request->param('type');
        if($type=='add'){
            $province=Db::name('provincecitycounty')->field('Code,AreaName')->where('ParentCode=0')->select();
            $this->view->assign('province',$province);
            return $this->view->fetch('address_add');
        }elseif($type=='edit'){
            $where['Id']=$this->request->param('id');
            $addxx=Db::name('comreceiveinfo')->where($where)->find();
            $addxx=array_change_key_case($addxx);
            $province=Db::name('provincecitycounty')->field('Code,AreaName')->where('ParentCode=0')->select();
            $this->view->assign('province',$province);
            $city=Db::name('provincecitycounty')->field('Code,AreaName')->where('ParentCode='.$addxx['province'])->select();
            $this->view->assign('city',$city);
//            var_dump($city);exit;
            $county=Db::name('provincecitycounty')->field('Code,AreaName')->where('ParentCode='.$addxx['city'])->select();
            $this->view->assign('county',$county);
            $this->view->assign("addxx",$addxx);
            return $this->view->fetch('address_edit');
        }
    }
    /**
     * 会员中心页面
     * @return mixed
     */
    public function user_info(){
        $url = config('API_DOMAIN_NAME').'/api.php/index/all?ishit=1';//调用充值/扣币的接口
        $result = http_request($url);
        $returnData=json_decode($result,true);
        if($returnData['status'] == "1") {
            $this->view->assign('recommend', $returnData['data']['recommend']);//首页展示商品
        }else{
            echo $returnData['msg'];
//            return $this->view->fetch('index');
        }
        // 查询用户信息
        $where['UserId'] = Session::get('membername');
        $userinfo = db::name('usermsg')->where($where)->find();

        // 查询联盟商信息
        $whereunion['userType'] = 3;
        $whereunion['ReferrerID'] = Session::get('membername');
        $union_number = db::name('usermsg')->where($whereunion)->count();

        // 查询企业信息
        // 直属企业用户
        $company_number = $company_number2 = $zhunxiang_company_number = $biaozhun_company_number = $zhunxiang_company_number2 = $biaozhun_company_number2 = 0;

        // 尊享版
        $zhunxiang_wherecompany['userType'] = 2;
        $zhunxiang_wherecompany['ReferrerID'] = Session::get('membername');
        $zhunxiang_company_number += db::name('usermsg')->where($zhunxiang_wherecompany)->count();
        // 标注版
        $biaozhun_wherecompany['userType'] = 1;
        $biaozhun_wherecompany['ReferrerID'] = Session::get('membername');
        $biaozhun_company_number = db::name('usermsg')->where($biaozhun_wherecompany)->count();
        // 合计
        $company_number += $zhunxiang_company_number + $biaozhun_company_number;

        // 联盟商企业用户
        $unions = db::name('usermsg')->where($whereunion)->select();
        if(!empty($unions)){
            foreach ($unions as $key => $value) {
                // 尊享版
                $zhunxiang_wherecompany2['userType'] = 2;
                $zhunxiang_wherecompany2['ReferrerID'] = $value['UserId'];
                $zhunxiang_company_number2 += db::name('usermsg')->where($zhunxiang_wherecompany2)->count();
                // 标注版
                $biaozhun_wherecompany2['userType'] = 1;
                $biaozhun_wherecompany2['ReferrerID'] = $value['UserId'];
                $biaozhun_company_number2 += db::name('usermsg')->where($biaozhun_wherecompany2)->count();
            }
        }
        // 合计
        $company_number2 = $zhunxiang_company_number2 + $biaozhun_company_number2;
        

        $this->view->assign('userinfo',$userinfo)->assign('union_number',$union_number)->assign('zhunxiang_company_number',$zhunxiang_company_number)->assign('biaozhun_company_number',$biaozhun_company_number)->assign('company_number',$company_number)->assign('zhunxiang_company_number2',$zhunxiang_company_number2)->assign('biaozhun_company_number2',$biaozhun_company_number2)->assign('company_number2',$company_number2);
        return $this->view->fetch('user_info');
    }
    /**
     * 个人资料
     * @return mixed
     */
    public function user_infos(){
        return $this->view->fetch('user_infos');
    }
    /**
     * 会员信息编辑页面
     * @return mixed
     */
    public function user_edit(){
        return $this->view->fetch('user_edit');
    }
    /**
     * 会员密码的重置
     * @return mixed
     */
    public function user_password_reset(){
        return $this->view->fetch('user_password_reset');
    }
    /**
     * 会员的收货地址列表
     * @return mixed
     */
    public function address_list(){

        $http_referer = $_SERVER['HTTP_REFERER'];
        //dump($http_referer);exit;
        if($http_referer){
            $this->view->assign('http_referer',$http_referer);
        }
        $type=$this->request->param('return');
//        var_dump($type);exit;
        if($type){
            if($type=='nowbuy'){
                $proid=$this->request->param('proid');
                $styleid=$this->request->param('styleid');
                $pronum=$this->request->param('pronum');
                $url='/mobile.php/order/affirmnew?proid='.$proid.'&styleid='.$styleid.'&pronum='.$pronum;
            }else{
                $url='/mobile.php/order/affirm';
            }
        }else{
            $url='/mobile.php/order/affirm';
        }

        $this->view->assign('url',$url);
        return $this->view->fetch('address_list');
    }
    /**
     * 会员账户余额信息
     * @return mixed
     */
    public function user_account(){
        return $this->view->fetch('user_account');
    }
    /**
     * 我的客户
     * @return mixed
     */
    /*public function user_subordinate(){
        return $this->view->fetch('user_subordinate');
    }*/
    /*
     * 我的消息
     * @return mixed
     */
    public function user_message(){
        return $this->view->fetch('user_message');
    }
    /**
     * 我的二维码
     * @return mixed
     */
    public function user_qrcode(){
        $where['UserId'] = Session::get('membername');
        $userinfo = db::name('usermsg')->where($where)->find();
        $url='http://'.$_SERVER['HTTP_HOST'].'/mobile.php/member/reg?introduceId='.$userinfo['invitecode'];
        $this->view->assign('introduceId',$userinfo['invitecode'])->assign('url',$url);
        return $this->view->fetch('user_qrcode');
    }
    public function recharge_umoney(){
        return $this->view->fetch('recharge_umoney');
    }
    public function receive_account(){
        return $this->view->fetch('receive_account');
    }
    // 会员充值年费
    public function pay_annual_free(){
        // 查询用户及推荐人的信息
        $where['UserId'] = Session::get('membername');
        $userinfo= db::name('usermsg')->where($where)->find();
        $wherepid['UserId'] =  $pid = $userinfo['ReferrerID'];
        $puserinfo = db::name('usermsg')->where($wherepid)->find();
        // 查询会员类型的年费价格
        $usertype_info = db::name('usertype')->select();
        foreach ($usertype_info as $key => $value) {
            $priceArr[] = $value['annualfee_price'];
        }
        $this->view->assign('priceArr',$priceArr);


        if($this->request->param('userType')){
            $data['userType'] = intval($this->request->param('userType'));
            if($data['userType'] < 1 || $data['userType'] > 4){
                return json(['status'=>0,'msg'=>'会员类型选择错误！']);
            }
            // 查询用户类型列表，并扣除推荐人的推荐名额
            $usertype_info = db::name('usertype')->select();
            

            if(!$pid){
                 return json(['status'=>0,'msg'=>'注册失败，推荐人有误！']);
            }
            switch ($data['userType']) {
                case '1':
                    // 判断推荐人推荐名额是否充足
                    if($puserinfo['one_level_places'] < 1){
                        return json(['status'=>0,'msg'=>'注册失败，推荐人标准版名额不足！']);
                    }
                    // 扣除推荐人推荐名额
                    /*$rs1 = db::name('usermsg')->where($wherepid)->setDec('one_level_places');
                    $datar['Balance'] = $puserinfo['one_level_places'] - 1;
                    // 赠送注册人推荐名额
                    $datau['one_level_places'] = $usertype_info['0']['one_level_places'];
                    $data2['Amount'] = $usertype_info['0']['one_level_places'];
                    $data2['Balance'] = $userinfo['one_level_places']+ $usertype_info['0']['one_level_places'];*/
                    break;
                case '2':
                    if($puserinfo['two_level_places'] < 1){
                        return json(['status'=>0,'msg'=>'注册失败，推荐人尊享版名额不足！']);
                    }
                   /* $rs1 = db::name('usermsg')->where($wherepid)->setDec('two_level_places');
                    $datar['Balance'] = $puserinfo['two_level_places'] - 1;

                    $datau['two_level_places'] = $usertype_info['1']['two_level_places'];

                    $data2['Amount'] = $usertype_info['1']['two_level_places'];
                    $data2['Balance'] = $userinfo['two_level_places']+ $usertype_info['1']['two_level_places'];*/
                    break;
                case '3':
                    if($puserinfo['three_level_places'] < 1){
                        return json(['status'=>0,'msg'=>'注册失败，推荐人联盟商名额不足！']);
                    }
                   /* $rs1 = db::name('usermsg')->where($wherepid)->setDec('three_level_places');
                    $datar['Balance'] = $puserinfo['three_level_places'] - 1;

                    $datau['three_level_places'] = $usertype_info['2']['three_level_places'];

                    $data2['Amount'] = $usertype_info['2']['three_level_places'];
                    $data2['Balance'] = $userinfo['three_level_places']+ $usertype_info['2']['three_level_places'];*/
                    break;
                case '4':
                    if($puserinfo['four_level_places'] < 1){
                        return json(['status'=>0,'msg'=>'注册失败，推荐人营销中心名额不足！']);
                    }
                    /*$rs1 = db::name('usermsg')->where($wherepid)->setDec('four_level_places');
                    $datar['Balance'] = $puserinfo['four_level_places'] - 1;

                    $datau['four_level_places'] = $usertype_info['3']['four_level_places'];

                    $data2['Amount'] = $usertype_info['3']['four_level_places'];
                    $data2['Balance'] = $userinfo['four_level_places']+ $usertype_info['3']['four_level_places'];*/
                    break;
            }
            //生成扣除推荐人名额记录
            /*$datar['UserId'] = $pid;
            $datar['FlowType'] = 5;
            $datar['Amount'] = -1;
            //$data['Balance'] = $puserinfo['one_level_places'] - 1;
            $datar['FromWho'] = Session::get('membername');
            $datar['Memo'] = '推荐'.Session::get('membername').'消耗一个'.$usertype_info[$data['userType']-1]['Name'].'名额';
            $datar['AddDate'] = date('Y-m-d H:i:s',time());
            $rs3 = db::name('accountplaces')->insert($datar);

            //如果注册人赠送有推荐名额，则生成增加注册人推荐名额记录
           
            if($usertype_info[$data['userType']-1]['one_level_places'] || $usertype_info[$data['userType']-1]['two_level_places'] || $usertype_info[$data['userType']-1]['three_level_places'] || $usertype_info[$data['userType']-1]['four_level_places']){
                $data2['UserId'] = Session::get('membername');
                $data2['FlowType'] = 6;
                //$data['Amount'] = $add_places;
                //$data['Balance'] = $puserinfo['one_level_places'] - 1;
                $data2['FromWho'] = Session::get('membername');
                $data2['Memo'] = '注册获得'.$data2['Amount'].'个'.$usertype_info[$data['userType']-1]['Name'].'名额';
                $data2['AddDate'] = date('Y-m-d H:i:s',time());
                $rs3 = db::name('accountplaces')->insert($data2);

            }
            

            $data['IsAudit'] = 2;
            $rs2 = db::name('usermsg')->where($where)->update($data);
            Db::startTrans();
            if($rs1 && $rs2 && $rs3 ){
                Db::commit();    
            }else{
                Db::rollback();
            }*/
            // 将用户选择的会员类型，更新至数据库
            $rs2 = db::name('usermsg')->where($where)->update($data);
            // 将订单信息记录至订单表
            $datao['orderid'] = date('YmdHis').rand(1111,9999);
            $datao['UserId'] = $where['UserId'];
            $datao['usertype'] = $data['userType'];
            $datao['number'] = 1;
            $datao['price'] = $usertype_info[$data['userType']-1]['annualfee_price'];
            $datao['totleprice'] = $datao['number'] * $usertype_info[$data['userType']-1]['annualfee_price'];
            $datao['order_time'] = date('Y-m-d H:i:s',time());
            $datao['order_type'] = 1;
            $rs = db::name('order_places')->insert($datao);
            // 拼接支付信息，发起支付请求
            $payinfo = 1;
            if($payinfo){
                return json(['status'=>1,'orderid'=>$datao['orderid'],'msg'=>'注册成功，正在跳向支付界面……！']);
            }else{
                return json(['status'=>0,'msg'=>'注册失败！']);
            }

        }
        $this->view->assign('puserinfo',$puserinfo);
        return $this->view->fetch();
    }

    // 推荐人购买推荐名额
    public function buy_places(){
         // 查询用户及推荐人的信息
        $UserId = $where['UserId'] = Session::get('membername');
        $userinfo= db::name('usermsg')->where($where)->find();
        $wherepid['UserId'] =  $pid = $userinfo['ReferrerID'];

        // 查询会员类型的年费价格
        $usertype_info = db::name('usertype')->select();
        foreach ($usertype_info as $key => $value) {
            $priceArr[] = $value['buy_price'];
        }
        $this->view->assign('priceArr',$priceArr)->assign('userinfo',$userinfo);

        if($this->request->param('usertype')){
            $usertype = intval($this->request->param('usertype'));
            $pay_way = intval($this->request->param('payway'));
            $buy_nubmer = intval($this->request->param('number'));
            if(!$usertype){
                return json(['status'=>0,'msg'=>'选择推荐名额类型出错！']);
            }
            if(!$pay_way){
                return json(['status'=>0,'msg'=>'必须选择支付类型！']);
            }
             if(!$buy_nubmer){
                return json(['status'=>0,'msg'=>'必须选择购买数量！']);
            }
            // 查新要购买的名额信息
            $wheretypy['ID'] = $usertype;
            $usertype_info = db::name('usertype')->where($wheretypy)->find();
            // 生成购买订单
            $data['orderid'] = date('YmdHis').rand(1111,9999);
            $data['UserId'] = $UserId;
            $data['usertype'] = $usertype;
            $data['number'] = intval($this->request->param('number'));
            $data['price'] = $usertype_info['buy_price'];
            $data['totleprice'] = $data['number'] * $usertype_info['buy_price'];
            $data['order_time'] = date('Y-m-d H:i:s',time());
            $data['order_type'] = 2;
            $rs = db::name('order_places')->insert($data);
            if($rs){
                return json(['status'=>1,'orderid'=>$data['orderid'],'msg'=>'生成订单成功！正在跳转支付页面……']);
            }else{
                return json(['status'=>0,'msg'=>'生成订单失败，请重试！']);
            }
        }
        
        return $this->view->fetch();
    }
    // 支付成功后，通知订单处理
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
                    $datar['Balance'] = $dateu['one_level_places'] = $userinfo['one_level_places'] + $orderinfo['number'];

                    break;
                case 2 : $datar['Balance'] = $dateu['two_level_places'] = $userinfo['two_level_places'] + $orderinfo['number'];break;
                case 3 :  $datar['Balance'] =$dateu['three_level_places'] = $userinfo['three_level_places'] + $orderinfo['number'];break;
                case 4 :  $datar['Balance'] =$dateu['four_level_places'] = $userinfo['four_level_places'] + $orderinfo['number'];break;
            }
            db::name('usermsg')->where($whereu)->update($dateu);
            // 将购买名额添加到记录表
            $datar['UserId'] = Session::get('membername');
            $datar['FlowType'] = 7;
            $datar['FromWho'] = Session::get('membername');
            $datar['Amount'] = $orderinfo['number'];
            $datar['Memo'] = '购买获得'.$datar['Amount'].'个'.$usertype_info[$orderinfo['usertype']]['Name'].'名额';
            $datar['AddDate'] = date('Y-m-d H:i:s',time());
            $rs3 = db::name('accountplaces')->insert($datar);
        }
    }

    // 补充用户资料步骤一
    public function add_userinfo_one(){
        $UserId = $where['UserId'] = Session::get('membername');
        $userinfo = db::name('usermsg')->where($where)->find();
        $this->view->assign('userinfo',$userinfo);
        if($this->request->param('TrueName')){
            // 检验信息完整性
            $data['TrueName'] = $this->request->param('TrueName');
            $data['IdCardNo'] = $this->request->param('IdCardNo');
            $data['company_name'] = $this->request->param('company_name');
            $data['company_license'] = $this->request->param('company_license');
            foreach ($data as $key => $value) {
                if($value == ''){
                    return json(['status'=>0,'msg'=>'填写的信息不完整，请重新填写！']);
                }
            }
            // 更新用户数据
            db::name('usermsg')->where($where)->update($data);
            if(1){
                return json(['status'=>1,'msg'=>'添加成功，跳转到下一步……']);
            }else{
                return json(['status'=>0,'msg'=>'出现错误，请重试！']);
            }

        }

        return $this->view->fetch();
    }

     // 补充用户资料步骤二
    public function add_userinfo_two(){
        $UserId = $where['UserId'] = Session::get('membername');
        $userinfo = db::name('usermsg')->where($where)->find();
        $this->view->assign('userinfo',$userinfo);
        if($this->request->param('bank_user_name')){
            // 判断是否至少上传了一张图片
            if(!$this->request->file('company_pic1')){
                 return json(['status'=>0,'msg'=>'至少要上传一张图片！']);
            }
            // 检验信息完整性
            $data['bank_user_name'] = $this->request->param('bank_user_name');
            $data['bank_account'] = $this->request->param('bank_account');
            $data['bank_name'] = $this->request->param('bank_name');
            $data['account_type'] = $this->request->param('account_type');
            foreach ($data as $key => $value) {
                if($value == ''){
                    return json(['status'=>0,'msg'=>'填写的信息不完整，请重新填写！']);
                }
            }

            // 上传图片
            $uploaddir = ROOT_PATH . 'public' . DS . 'Upload' . DS . 'company_pic';
            $file = $this->request->file('company_pic1');
            //dump($this->request->file('company_pic1'));
            if (!empty($file)) {
                $info = $file->validate(['size' => 2048000, 'ext' => 'jpg,png,gif,jpeg'])->move($uploaddir);
               //dump($info);
                if ($info) {
                    $filename = $info->getSaveName();
                    //dump($filename);
                    //  对图片进行压缩
                    if($filename){
                        $image = \think\Image::open($uploaddir.'/'.$filename);
                        //dump($image);
                        $image->thumb(100, 100)->save($uploaddir.'/thumb/'.$filename,$uploaddir.'/thumb/');
                        //exit;
                    }
                    
                } else {
                    return json(['status' => 1, 'msg' => '上传图片要小于2M']);
                }
                $data["license_pic1"] = $filename;
            }
            if($this->request->file('company_pic2')){
                $file1 = $this->request->file('company_pic2');
                if (!empty($file1)) {
                    $info1 = $file1->validate(['size' => 2048000, 'ext' => 'jpg,png,gif,jpeg'])->move($uploaddir);
                    if ($info1) {
                        $filename1 = $info1->getSaveName();
                        //  对图片进行压缩
                        if($filename1){
                            $image = \think\Image::open($uploaddir.'/'.$filename1);
                            $image->thumb(100, 100)->save($uploaddir.'/thumb/'.$filename1,$uploaddir.'/thumb/');
                        }
                        
                    } else {
                        return json(['status' => 1, 'msg' => '上传图片要小于2M']);
                    }
                    $data["license_pic2"] = $filename1;
                }
            }else{
                $data["license_pic2"] = '';
            }
           
            if($this->request->file('company_pic3')){
                $file2 = $this->request->file('company_pic3');
                if (!empty($file2)) {
                    $info2 = $file2->validate(['size' => 2048000, 'ext' => 'jpg,png,gif,jpeg'])->move($uploaddir);
                    if ($info2) {
                        $filename2 = $info2->getSaveName();
                        //  对图片进行压缩
                        if($filename2){
                            $image = \think\Image::open($uploaddir.'/'.$filename2);
                            $image->thumb(100, 100)->save($uploaddir.'/thumb/'.$filename2,$uploaddir.'/thumb/');
                        }
                        
                    } else {
                        return json(['status' => 1, 'msg' => '上传图片要小于2M']);
                    }
                    $data["license_pic3"] = $filename2;
                }
            }else{
                $data["license_pic3"] = '';
            }
            
            $data['IsAudit'] = 2;
            // 更新用户数据
            $rs = db::name('usermsg')->where($where)->update($data);
            if($rs){
                return json(['status'=>1,'msg'=>'资料填写完成，跳转到激活页面……']);
            }else{
                return json(['status'=>0,'msg'=>'出现错误，请重试！']);
            }

        }

        return $this->view->fetch();
    }


    /**
     * 我的分享
     * @return mixed
     */
    public function user_subordinate(){
        $map=[];
        $whereu['UserId'] =  $where['pid']=Session::get('membername');
        $userinfo = db::name('usermsg')->where($whereu)->find();
        $this->view->assign('userinfo',$userinfo);
        $where['lay']=1;

        $list=Db::name('userrecommenddiagram')->where($where)->paginate();
        $count=Db::name('userrecommenddiagram')->where($where)->count();//获取满足条件的总记录数
        $dataArr=[];
        foreach ($list as $key=>$val){
            $where7['UserId']=$val['userId'];
            $userinfo1=Db::table('usermsg')->where($where7)->find();

            if(empty($userinfo1['avatarurl'])){
                $userinfo['avatarurl']='/public/Public/mobile/images/tu.png';//默认头像
            }else{
                $userinfo['avatarurl'] = config('IMAGE_DOMAIN_NAME').'/Upload/userimg/'.$userinfo1['avatarurl'];
            }
            if(empty($userinfo1['truename'])){
                $userinfo['name']=$val['userId'].'（匿名）';
            }else{
                $userinfo['name']=$val['userId'].'（'.$userinfo1['truename'].'）';
            }

            // 对用户信息进行部分屏蔽
            /*$userinfo['UserId'] = substr($val['userId'], 0, 3).'****'.substr($val['userId'],7);
            $userinfo['TrueName'] = mb_substr($userinfo1['TrueName'], 0, 1).'**';
            $userinfo['Mobile'] = substr($userinfo1['Mobile'], 0, 3).'****'.substr($userinfo1['Mobile'],7);*/
            $userinfo['UserId'] = $val['userId'];
            $userinfo['TrueName'] = $userinfo1['TrueName'];
            $userinfo['Mobile'] = $userinfo1['Mobile'];
            $userinfo['company_name'] = $userinfo1['company_name'];
            $userinfo['AddDate'] = $userinfo1['AddDate'];
            $userinfo['IsAudit'] = $userinfo1['IsAudit'];
            $userinfo['three_level_places'] = $userinfo1['three_level_places'];
            $userinfo['two_level_places'] = $userinfo1['two_level_places'];
            $userinfo['userType'] = $userinfo1['userType'];
            $dataArr[]=$userinfo;
        }


        if(!empty($map)) {
            foreach ($map as $key => $value) {
                $list->appends($key, $value);//分页链接中添加请求的参数
            }
        }

       
        $this->view->assign('list',$dataArr);
        $this->view->assign('page',$list->render());//输出分页的样式
        $this->view->assign('count',$count);
        //dump($dataArr);
        return $this->view->fetch('user_subordinate');
    }

    // 推荐人激活下一级
    public function active_member(){

        $where['UserId'] = $UserId = $this->request->param('UserId');
        $userinfo = db::name('usermsg')->where($where)->find();
        $datau['userType'] = $userinfo['userType'] = $this->request->param('usertype');
        // 查询用户类型列表，并扣除推荐人的推荐名额
        $usertype_info = db::name('usertype')->select();
        //dump($usertype_info);

        $wherepid['UserId'] =  $pid = Session::get('membername');

        if(!$pid){
            return json(['status'=>0,'msg'=>'推荐人有误！']);
        }
        $puserinfo = db::name('usermsg')->where($wherepid)->find();
        switch ($userinfo['userType']) {
            case '1':
                // 判断推荐人推荐名额是否充足
                if($puserinfo['one_level_places'] < 1){
                    return json(['status'=>0,'msg'=>'推荐人标准版名额不足！']);
                }
                // 扣除推荐人推荐名额
                $rs1 = db::name('usermsg')->where($wherepid)->setDec('one_level_places',1);
                $datar['Balance'] = $puserinfo['one_level_places'] - 1;
                // 赠送注册人推荐名额
                $datau['one_level_places'] = 0;
                $data2['Amount'] = 0;
                $data2['Balance'] = 0;

                break;
            case '2':
                if($puserinfo['two_level_places'] < 1){
                    return json(['status'=>0,'msg'=>'推荐人尊享版名额不足！']);
                }
                $rs1 = db::name('usermsg')->where($wherepid)->setDec('two_level_places',1);
                $datar['Balance'] =  $puserinfo['two_level_places'] - 1;

                $datau['two_level_places'] = 0;

                $data2['Amount'] = 0;
                $data2['Balance'] = 0;

                $data_add_r['Amount'] = $usertype_info[1]['annualfee_price'] * 0.5;
                $datau['Umoney'] = $data_add_r['Balance'] = $puserinfo['Umoney'] + $data_add_r['Amount'];
                break;
            case '3':
                if($puserinfo['three_level_places'] < 1){
                    return json(['status'=>0,'msg'=>'推荐人联盟商名额不足！']);
                }
                $rs1 = db::name('usermsg')->where($wherepid)->setDec('three_level_places',1);
                $datar['Balance'] = $puserinfo['three_level_places'] - 1;

                $datau['two_level_places'] = $userinfo['two_level_places'] + $usertype_info['2']['two_level_places'];

                $data2['Amount'] = $usertype_info['2']['two_level_places'];
                $data2['Balance'] = $userinfo['two_level_places']+ $usertype_info['2']['two_level_places'];

                $data_add_r['Amount'] = $usertype_info[2]['annualfee_price'] * 0.5;
                $datau['Umoney'] = $data_add_r['Balance'] = $puserinfo['Umoney'] + $data_add_r['Amount'];

                $data2['Amount'] = $usertype_info['2']['two_level_places'];
                $data2['Balance'] = $userinfo['two_level_places']+ $usertype_info['2']['two_level_places'];
                
                break;
            case '4':
                if($puserinfo['four_level_places'] < 1){
                    return json(['status'=>0,'msg'=>'推荐人营销中心名额不足！']);
                }
                $rs1 = db::name('usermsg')->where($wherepid)->setDec('four_level_places',1);
                $datar['Balance'] = $puserinfo['four_level_places'] - 1;

                $datau['three_level_places'] = $userinfo['three_level_places'] + $usertype_info['3']['three_level_places'];

                $data2['Amount'] = $usertype_info['3']['three_level_places'];
                $data2['Balance'] = $userinfo['three_level_places']+ $usertype_info['3']['three_level_places'];

                $data_add_r['Amount'] = $usertype_info[3]['annualfee_price'] * 0.5;
                $datau['Umoney'] = $data_add_r['Balance'] = $puserinfo['Umoney'] + $data_add_r['Amount'];

                $data2['Amount'] = $usertype_info['3']['three_level_places'];
                $data2['Balance'] = $userinfo['three_level_places']+ $usertype_info['3']['three_level_places'];

                break;
        }
        //生成扣除推荐人名额记录
        $datar['UserId'] = $pid;
        $datar['FlowType'] = 5;
        $datar['Amount'] = -1;
        //$data['Balance'] = $puserinfo['one_level_places'] - 1;
        $datar['FromWho'] = Session::get('membername');
        $datar['Memo'] = '推荐'.$UserId.'消耗一个'.$usertype_info[$userinfo['userType']-2]['Name'].'名额';
        $datar['AddDate'] = date('Y-m-d H:i:s',time());
        $rs3 = db::name('accountplaces')->insert($datar);


        // 生成分享金额记录并加到账户
        $data_add_r['UserId'] = Session::get('membername');
        $data_add_r['FlowType'] = '分享返利';
       
        $data_add_r['FromWho'] = $UserId;
        $data_add_r['Memo'] = '分享返利';
        $data_add_r['AddDate'] = date('Y-m-d H:i:s',time());
        db::name('accountrecord')->insert($data_add_r);

        //如果注册人赠送有推荐名额，则生成增加注册人推荐名额记录
       
        if($usertype_info[$userinfo['userType']-1]['one_level_places'] || $usertype_info[$userinfo['userType']-1]['two_level_places'] || $usertype_info[$userinfo['userType']-1]['three_level_places'] || $usertype_info[$userinfo['userType']-1]['four_level_places']){
            $data2['UserId'] = $UserId;
            $data2['FlowType'] = 6;
            //$data['Amount'] = $add_places;
            //$data['Balance'] = $puserinfo['one_level_places'] - 1;
            $data2['FromWho'] = Session::get('membername');
            $data2['Memo'] = '注册获得'.$data2['Amount'].'个'.$usertype_info[$userinfo['userType']-1]['Name'].'名额';
            $data2['AddDate'] = date('Y-m-d H:i:s',time());

            $rs3 = db::name('accountplaces')->insert($data2);
        }
        

        $datau['IsAudit'] = 1;
        //dump($datau);exit;
        $rs2 = db::name('usermsg')->where($where)->update($datau);

       /* $sql = 'select usermsg.UserId from usermsg,userrecommenddiagram  where usermsg.UserId=userrecommenddiagram.pid and usermsg.userType=4 and userrecommenddiagram.userId='.购买人;
        循环给查出的人加 总金额的1%到账户
*/

        if($rs2){
            return json(['status'=>1,'msg'=>'激活完成']);
        }else{
            return json(['status'=>0,'msg'=>'激活失败']);        
        }
    }
}