<?php
/**
 * Created by 赵晓凡
 * User: zhaoxiaofan
 * Date: 2017/3/15
 * Time: 10:30
 */
namespace app\index\controller;

use think\Db;
use think\Session;


/**
 * Class Member
 * @package api\index\controller
 * @return 会员
 */
class Supplier extends Auth
{
    /**
     * 申请开店接口
     * 请求方式：http://www.XXX.com/app.php/supplier/supplieradd
     * @param   token
     * @param   name  店铺名称
     * @param   mobile  手机号码
     * @param   wechatname  微信号码
     * @param   truename   真实姓名
     * @param   idcord  身份证号
     * @param   province  省
     * @param   city  市
     * @param   area  县
     * @param   address  详细地址
     * @param   supinfo  证件
     * @param   zhizhao  执照
     */
    public function supplieradd(){
        $info=$this->islogin();
        if($info['status']==0){
            $userid=$info['data']['UserId'];
            $user = Db::name('usermsg')->where('UserId='.$userid)->field('ID,business_type,supplier_idd')->find();
            if ($user['business_type']!=='2') {
                $postdata=$this->request->param();
                if(!empty($postdata['name'])){
                    if(getsuppliernamebyname($postdata['name'])===false){
                        $rule = '/^((13[0-9])|147|(15[0-35-9])|180|182|(18[0-9]))[0-9]{8}$/A';
                        if(!empty($postdata['mobile'])){
                            if (preg_match($rule, $postdata['mobile'])) {
                                $time = date('Y-m-d H:i:s', time());
                                $time1 = $time.time();
                                //商家信息
                                $data['Name']           = $postdata['name']; //商家名称
                                $data['Mobile']         = $postdata['mobile']; //手机
                                if(isset($postdata['wechatname'])){
                                $data['WeChatName']     = $postdata['wechatname']; //微信
                                }
                                if(isset($postdata['province'])) {
                                    $data['Province'] = $postdata['province']; //省份
                                }
                                if(isset($postdata['city'])) {
                                    $data['City'] = $postdata['city']; //城市
                                }
                                if(isset($postdata['area'])) {
                                    $data['Area'] = $postdata['area']; //县
                                }
                                if(isset($postdata['address'])) {
                                    $data['Address'] = $postdata['address']; //商家详细地址
                                }
                                $data['usermasg_id']    = $user['ID']; //会员id
                                $data['IsAudit']        = '0';//待审核
                                $data['AddDate']        = $time;//待添加时间
                                $data['key']            = hash("sha256",$time1);//key
                                $file = $this->request->file('supinfo');
                                if(!empty($file)){
                                    // 移动到框架应用根目录/public/uploads/ 目录下
                                    $uploaddir=ROOT_PATH . 'public' . DS . 'Upload'. DS .'userimg';
                                    $info = $file->validate(['size'=>1024000,'ext'=>'jpg,png,gif,jpeg'])->move($uploaddir);
                                    if($info){
                //                    $mempicture=Db::name('usermsg')->where($where)->field('AvatarUrl')->find();
                //                    if(file_exists($uploaddir.'/'.$mempicture['AvatarUrl'])){
                //                        unlink($uploaddir.'/'.$mempicture['AvatarUrl']);//删除原有图片
                //                    }
                                        $filename=$info->getPathInfo()->getFilename().'/'.$info->getFilename();
                                    }else{
                                        $filename='default.jpg';
                                    }
                                    $data['SupInfo']=$filename;
                                }
                                Db::startTrans();
                                try{
                                    $id= Db::name('supplier')->insertGetId($data);
                                    if($id){
                                        $update['supplier_idd']  = $id; //商家id
                                        $update['business_type'] = '2';//修改状态为商家
                                        $whereuser['ID']=$user['ID'];
                                        $re=Db::name('usermsg')->where($whereuser)->update($update);
                                        if($re){
                                            $returnData['status']=0;
                                            $returnData['msg']='添加商家成功!';
                                        }else{
                                            Db::rollback();
                                            $returnData['status']=2;
                                            $returnData['msg']='添加商家失败!';
                                        }
                                    }else{
                                        Db::rollback();
                                        $returnData['status']=2;
                                        $returnData['msg']='添加商家失败!';
                                    }

                                }catch (\Exception $e){

                                }
                            }else{
                                $returnData['status']=2;
                                $returnData['msg']='电话号码格式错误!';
                            }
                        }else{
                            $returnData['status']=2;
                            $returnData['msg']='手机号码不能为空!';
                        }
                    }else{
                        $returnData['status']=2;
                        $returnData['msg']='商家名称已存在!';
                    }
                }else{
                    $returnData['status']=2;
                    $returnData['msg']='商家名称不能为空!';
                }

            }else{
                $returnData['status']=2;
                $returnData['msg']='你已是商家，或你已申请商家！';
            }

        }else{
            $returnData['status']=1;
            $returnData['msg']='未登录';
        }
        return json($returnData);
    }
    /**
     * 转账给商家
     * 请求方式：http://www.XXX.com/app.php/supplier/coupontosupplier
     * @param   token
     * @param   type  2转账
     * @param   supplierid  商家帐号
     * @param   amount  转账金额
     * 请求方式：http://www.XXX.com/app.php/supplier/coupontosupplier
     * @param   token
     * @param   type  1展示页面
     * @return \think\response\Json
     */
  public function coupontosupplier(){
      $info = $this->supplierislogin();
//      $info['data']['ID']=306;
//      $info['status'] = 0;
      if ($info['status'] == 0) {
          if($this->request->param('type')){
              $type=$this->request->param('type');
              $where['ID']=$info['data']['ID'];
              $coupon=Db::name('supplier')->field('coupon,Name,ID')->where($where)->find();
              if($type==1){
                  $returnData['data']=$coupon;
                  $returnData['status']=0;
                  $returnData['msg']='获取零购券成功';
              }elseif($type==2){
                  if($this->request->param('name') && $this->request->param('amount')){
                      $suppliername=$this->request->param('name');
                      $amount=$this->request->param('amount');
                      if(empty($suppliername)){
                          $returnData['status']=3;
                          $returnData['msg']='接收商家名称不能为空';
                      }else{
                          $list = Db::name('supplier')->where("Name='$suppliername'")->field('ID,Name,coupon')->find(); //查询数据库有没有需要充值的商家
                          if($list){
                              if($list['Name']!=$coupon['Name']){
                                if(empty($amount)){
                                    $returnData['status']=5;
                                    $returnData['msg']='转账金额不能为空';
                                }else{
                                    if(is_numeric($amount)&&$amount>0){
                                        $where['ID']=$info['data']['ID'];
                                        $coupons=Db::name('supplier')->field('ID,Name,coupon')->where($where)->find();
//                                        var_dump(Db::name('supplier')->getLastSql());exit;
                                        $retudata=$this->couponviery($amount,$coupons['coupon']);
                                        if($retudata['status']==0){

                                            $resdata=$this->couponaction($amount,$coupons,$list,$info['data']['ID']);
                                            $returnData=$resdata;
                                        }else{
                                            $returnData=$retudata;
                                        }
                                    }else{
                                        $returnData['status']=6;
                                        $returnData['msg']='转账金额只能为大于0的数字';
                                    }
                                }
                              }else{
                                  $returnData['status']=6;
                                  $returnData['msg']='不能给自己充值';
                              }
                          }else{
                              $returnData['status']=4;
                              $returnData['msg']='商家不存在';
                          }
                      }

                  }else{
                      $returnData['status']=2;
                      $returnData['msg']='未传递接收商家帐号';
                  }
              }
          }else{
              $returnData['status']=1;
              $returnData['msg']='传递参数有误';
          }
      }else{
          $returnData=$info;
      }
      return json($returnData);
  }

/**验证零购券是否充足
*/
  private function couponviery($amount,$coupon){
    if(($coupon-$amount)>=0){
          $returnData['status']=0;
          $returnData['msg']='零购券充足';
      }else{
          $returnData['status']=1;
          $returnData['msg']='零购券不足';
      }
      return $returnData;
  }
    /**扣除本人零购券
     * 收款人增加零购券
     *$amount转账金额
     * $coupon转账人拥有的零购券
     * $tosupplier收款商家id
     * $fromsupplier转账商家id
     */
  private function couponaction($amount,$coupon,$list,$fromsupplier){

      //发卷商家字段
      $senddata['coupon'] = $coupon['coupon']-$amount; //商家金额减去充值金额
      $senddata['ID']     = $coupon['ID'];            //更新ID
      //收卷商家字段
      $todata['coupon'] = $list['coupon']+$amount; //发放金额加上本来的余额
      $todata['ID']     = $list['ID'];             //接收金额的ID
      $time =  date('Y-m-d H:i:s', time()); //发放时间
      $time1 = time();
      $rand = rand();  //生成随机订单
      //记录发卷情况
      $todata1['supplier_id'] =$list['ID'];
      $todata1['time']        =$time;        //转账时间
      $todata1['supplier_id_etid'] = $coupon['ID']; //发放商家ID
      $todata1['supplier_id_add'] = $list['ID']; //接收商家ID
      $todata1['state']       = '已完成';        //1状态成功
      $todata1['coupon']      ='-'.$amount;//转出去的零件
      $todata1['personnel_id'] = $coupon['Name']; //发放人员
      $todata1['coupon_total'] = $senddata['coupon']; //发放后的金额
      $todata1['type']         = '2';             //2为商家发卷
      $todata1['type1']        = '2';             //1为充值 2为转账
      $todata1['transaction']   = $rand.$time1;    //生成订单随机数


      //接收方记录
      $todata2['supplier_id'] =$list['ID'];
      $todata2['time']        =$time;        //转账时间
      $todata2['supplier_id_etid'] = $coupon['ID']; //发放商家ID
      $todata2['supplier_id_add'] = $list['ID']; //接收商家ID
      $todata2['coupon']      =$amount;//接收的零购劵
      $todata2['state']       = '已完成';        //1状态成功
      $todata2['personnel_id'] = $coupon['Name']; //发放人员
      $todata2['coupon_total'] = $todata['coupon']; //发放后的金额
      $todata2['type']         = '2';             //2为商家发卷
      $todata2['type1']        = '1';             //1为充值 2为转账
      $todata2['transaction']   = $rand.$time1;    //生成订单随机数
      Db::startTrans(); //启动事务
      try {

          $reg =  Db::name('supplier')->update($senddata);
          $reg1 =  Db::name('supplier')->update($todata);
          $reg2 = Db::name('supplier_record')->insertGetId($todata1);
          $reg3 = Db::name('supplier_record')->insertGetId($todata2);
          if ($reg &&$reg1&&$reg2&&$reg3) {
              Db::commit(); //提交事务
              return ['status'=>0,'msg'=>'发放成功！'];
          }else{
              Db::rollback(); //回滚事务
              return ['status'=>1,'msg'=>'发放失败！'];
          }

      } catch (\PDOException $e) {

          Db::rollback(); //回滚事务
          return ['status'=>2,'msg'=>'当前操作人数过多请稍后尝试！'];
      }


  }


    /**
     * 转账给会员
     * 请求方式：http://www.XXX.com/app.php/supplier/coupontouser
     * @param   token
     * @param   type  2转账
     * @param   mobile  会员id
     * @param   amount  转账金额
     * 请求方式：http://www.XXX.com/app.php/supplier/coupontosupplier
     * @param   token
     * @param   type  1展示页面
     * @return \think\response\Json
     */
    public function coupontouser(){
        $info = $this->supplierislogin();
//        $info['status'] = 0;
//        $info['data']['ID']=306;
        if ($info['status'] == 0) {
            if($this->request->param('type')){
                $type=$this->request->param('type');
                $where['ID']=$info['data']['ID'];
                $coupon=Db::name('supplier')->field('coupon,Name,ID,usermasg_id')->where($where)->find();
                if($type==1){
                    $returnData['data']=$coupon;
                    $returnData['status']=0;
                    $returnData['msg']='获取零购券成功';
                }elseif($type==2){
                    if($this->request->param('mobile') && $this->request->param('amount')){
                        $mobile=$this->request->param('mobile');
                        $amount=$this->request->param('amount');
                        if(empty($mobile)){
                            $returnData['status']=3;
                            $returnData['msg']='接收会员手机号不能为空';
                        }else{
                            $user_list =  Db::name('usermsg')->where("UserId={$mobile}")->field('ID,UserId,Mobile,Umoney,supplier_idd')->find(); //查出需要发卷的会员
                            Db::startTrans();
                            try{
                            if($user_list){
                                if($user_list['ID']!=$coupon['usermasg_id']){
                                    if(empty($amount)){
                                        $returnData['status']=5;
                                        $returnData['msg']='转账金额不能为空';
                                    }else{
                                        if(is_numeric($amount)&&$amount>0){
                                            $where['ID']=$info['data']['ID'];
                                            $coupons=Db::name('supplier')->field('ID,Name,coupon')->where($where)->find();
                                            $retudata=$this->couponviery($amount,$coupons['coupon']);
                                            if($retudata['status']==0){
                                                $resdata=$this->couponactiontouser($amount,$coupons,$user_list,$info['data']['ID']);
                                                if($resdata['status']==0){
                                                    Db::commit();
                                                }else{
                                                    Db::rollback();
                                                }
                                                $returnData=$resdata;
                                            }else{
                                                Db::rollback();
                                                $returnData=$retudata;
                                            }
                                        }else{
                                            $returnData['status']=6;
                                            $returnData['msg']='转账金额只能为大于0的数字';
                                        }
                                    }
                                }else{
                                    $returnData['status']=6;
                                    $returnData['msg']='不能给自己充值';
                                }
                            }else{
                                    if(empty($amount)){
                                        $returnData['status']=5;
                                        $returnData['msg']='转账金额不能为空';
                                    }else{
                                        if(is_numeric($amount)&&$amount>0){
                                            $where['ID']=$info['data']['ID'];
                                            $coupons=Db::name('supplier')->field('ID,Name,coupon')->where($where)->find();
                                            $retudata=$this->couponviery($amount,$coupons['coupon']);
                                            if($retudata['status']==0){
                                                $rule = '/^((13[0-9])|147|(15[0-35-9])|180|182|(18[0-9]))[0-9]{8}$/A';
                                                if (preg_match($rule, $mobile)) {
                                                    //查出最大的ID然后插入
                                                    $list = Db::name('usermsg')->field('ID')->where('1=1')->order('id desc')->find();
                                                    $password=$this->password();
                                                    $re=$this->userreg($mobile,$password,$coupons,$list);
                                                    if($re['status']==0){
                                                        $users =  Db::name('usermsg')->where("ID=".$re['id'])->field('ID,UserId,Mobile,Umoney,supplier_idd')->find(); //查出需要发卷的会员
                                                        $resdatas=$this->couponactiontouser($amount,$coupons,$users,$info['data']['ID']);
                                                        if($resdatas['status']==0){
                                                            $resz=$this->user_code($mobile,$password,$amount);
                                                            if($resz['status']==0){
                                                                Db::commit();
                                                                $returnData=$resz;
                                                            }else{
                                                                Db::rollback();
                                                                $returnData=$resz;
                                                            }

                                                        }else{
                                                            Db::rollback();
                                                            $returnData=$resdatas;
                                                        }

                                                    }else{
                                                        Db::rollback();
                                                        $returnData=$re;
                                                    }

                                                }else{
                                                    $returnData['status']=5;
                                                    $returnData['msg']='手机号码格式错误';
                                                }
                                            }else{
                                                Db::rollback();
                                                $returnData=$retudata;
                                            }
                                        }else{
                                            $returnData['status']=6;
                                            $returnData['msg']='转账金额只能为大于0的数字';
                                        }
                                    }
                            }
                            }catch (\Exception  $e){
                                Db::rollback();
                                $returnData['status']=6;
                                $returnData['msg']='转账失败';
                            }
                        }

                    }else{
                        $returnData['status']=2;
                        $returnData['msg']='传递参数有误';
                    }
                }
            }else{
                $returnData['status']=1;
                $returnData['msg']='传递参数有误';
            }
        }else{
            $returnData=$info;
        }
        return json($returnData);
    }

    private function couponactiontouser($amount,$coupon,$list,$fromsupplier){
              //usermsg会员表数据
        $todata['ID']     = $list['ID']; //接收劵的ID
        $todata['Mobile'] = $list['UserId'];//接收劵的会员
        $todata['Umoney'] = $amount+$list['Umoney'];//发放的金额加上原来的金额

        //supplier商家表数据
        $senddata['ID']    = $coupon['ID'];  //商家ID
        $senddata['coupon']= $coupon['coupon']-$amount; //商家减去需要发放的金额

        //记录商家发卷记录表 supplier_record

        $time =   date('Y-m-d H:i:s', time()); //发放时间
        $time1 = time();
        $rand = rand();  //生成随机订单
        //记录发卷情况

        $senddata1['supplier_id']   = $coupon['ID']; //商家id
        $senddata1['user_mobile']   = $list['Mobile']; //会员名称
        $senddata1['time']         = $time;        //转账时间
        $senddata1['supplier_id_etid'] = $coupon['ID']; //发放商家ID
        $senddata1['user_id']      =  $list['ID']; //接收劵的ID
        $senddata1['state']        = '已完成';        //1状态成功
        $senddata1['coupon']       = '-'.$amount;//转出去的零购劵
        $senddata1['personnel_id'] = $coupon['Name']; //发放人员
        $senddata1['coupon_total'] = $senddata['coupon']; //发放后的金额
        $senddata1['type']         = '3';             //3为商家发卷给会员
        $senddata1['type1']        = '2';             //1为充值 2为转账
        $senddata1['transaction']  = $rand.$time1;    //生成订单随机数

        //记录商家发卷记录表 supplier_record
        $todata1['supplier_id']   = $coupon['ID']; //商家id
        $todata1['user_mobile']   = $list['Mobile']; //会员名称
        $todata1['time']         = $time;        //转账时间
        $todata1['supplier_id_etid'] = $coupon['ID']; //发放商家ID
        $todata1['user_id']      =  $list['ID']; //接收劵的ID
        $todata1['state']        = '已完成';        //1状态成功
        $todata1['coupon']       = $amount;        //接收的零购劵
        $todata1['personnel_id'] = $coupon['Name']; //发放人员
        $todata1['coupon_total'] = $todata['Umoney']; //接收后的金额
        $todata1['type']         = '3';             //3为商家发卷给会员
        $todata1['type1']        = '1';             //1为充值 2为转账
        $todata1['transaction']  = $rand.$time1;    //生成订单随机数
        Db::startTrans(); //启动事务
        try {
            $reg =  Db::name('supplier')->update($senddata);
            $reg1 =  Db::name('usermsg')->update($todata);
            $reg2 = Db::name('supplier_record')->insertGetId($senddata1);
            $reg3 = Db::name('supplier_record')->insertGetId($todata1);
            if ($reg &&$reg1&&$reg2&&$reg3) {
                Db::commit(); //提交事务
                return ['status'=>0,'msg'=>'发放成功！'];
            }else{
                Db::rollback(); //回滚事务
                return ['status'=>1,'msg'=>'发放失败！'];
            }
        } catch (\PDOException $e) {

            Db::rollback(); //回滚事务
            return ['status'=>2,'msg'=>'当前操作人数过多请稍后尝试！'];
        }


    }
    /**
     *
     *不存在用户注册
     *
     */
    private function userreg($mobile,$password,$coupon,$list){
        $password = $password;//用户随机密码
        $user_id = $list['ID']+1;
        $innerdata['user_id'] = $user_id; //会员跟商家识别码
        $innerdata['Mobile'] = $mobile; //会员手机
        $innerdata['UserId'] = $mobile; //会员识别
        $innerdata['Password'] = md5($password);
        $innerdata['IsAudit'] = '1';//是否审核 0待审  1是
        $innerdata['business_type'] = '1';//会员类型 1会员 2.商家
        $innerdata['supplier_user_id'] = $coupon['ID']; //记录对这个会员发卷的商家ID
        $id=Db::name('usermsg')->insertGetId($innerdata);
        if($id){
            $returndata['id']=$id;
            $returndata['status']=0;
            $returndata['msg']='注册成功';
        }else{
            $returndata['status']=1;
            $returndata['msg']='注册失败';
        }
        return $returndata;
    }

    /**
     * 随机密码
     */

    public function password($length = 6){

        $min = pow(10 , ($length - 1));
        $max = pow(10, $length) - 1;
        return rand($min, $max);
    }

/**
 * 发送短信验证码
*/
    public function user_code($mobile,$password,$umoney){

        //$key=jh_key(); //key
        $key='695173a1d7230214879942a22b7b96a1';
        $tpl_id= '67580';
        $tpl_value ="#PASS#=$password&#LGJ#=$umoney";  //验证码
        $black_mobiles="13879503111";

        $value = substr("$tpl_value",7,13); //截取验证编码

        $dtype='json';
        $msgs= array();
        if (strpos($black_mobiles,$mobile) !==false)
        {
            return;
        }
        $is_success=0;
        $sendUrl = 'http://v.juhe.cn/sms/send'; //短信接口的URL
        $smsConf = array(
            'key'   => $key, //您申请的APPKEY
            'mobile'    => $mobile, //接受短信的用户手机号码
            'tpl_id'    => $tpl_id, //您申请的短信模板ID，根据实际情况修改
            'tpl_value' =>$tpl_value, //您设置的模板变量，根据实际情况修改
            'dtype'     => $dtype,
            'value'     =>$value,   //截取验证码存到session
        );
        $content = juhecurl($sendUrl,$smsConf,1); //请求发送短信

        if($content){
            $result = json_decode($content,true);
            $error_code = $result['error_code'];
            if($error_code == 0){
                //状态为0，说明短信发送成功
                //echo "短信发送成功,短信ID：".$result['result']['sid'];

                $is_success=1;
                $returnData['status'] = 0;
                $returnData['msg'] = '短信已发送';
                return $returnData;
            }else{
                //状态非0，说明失败
                $msgs = $result['reason'];
                $returnData['status'] = 3;
                $returnData['msg'] = '请输入正确的号码，或联系管理员';

                return $returnData;
            }
        }else{
            //返回内容异常，以下可根据业务逻辑自行修改
            $returnData['status'] = 3;
            $returnData['msg'] = '出现异常请联系管理员';
            return $returnData;
        }
        $data = array(
            'sms_mobile'    => $mobile,
            'sms_content'   => $content,
            // 'sms_ip'=>$verify_ip,
            'is_success'=>$is_success,
            // 'add_time'=>gmtime(),
            'result_info'=>$msgs,
        );
        return $is_success;


    }

    /**
     * 商家提现申请
     * 请求方式：http://www.XXX.com/app.php/supplier/supplier_cash
     * @param   token
     * @param   cash  金额        post提交
     * @return \think\response\Json
     */
    public function supplier_cash(){
//        $info = $this->supplierislogin();
        $info['status'] = 0;
        $info['data']['ID']=306;
        if ($info['status'] == 0) {
            $where['ID']= $info['data']['ID'];
        $suppliers = Db::name('supplier')->where($where)->field('ID,Name,cash')->find(); //查出这个会员对应的商家ID
        if ($this->request->isPost()) {
            $post = $this->request->post();
            $cash = trim($post['cash']);//需要提现的金额

            $data['ID'] = $suppliers['ID']; //商家ID
            $data['cash'] = $suppliers['cash']-$cash; //会员总现金=会员总现金-提现现金

            $time =   date('Y-m-d H:i:s', time());
            $insertdata1['cash']         = $cash; //申请金额
            $insertdata1['time']         = $time; //申请时间
            $insertdata1['supplier_id']  = $suppliers['ID']; //申请的商家ID
            $insertdata1['supplier_name']= $suppliers['Name'];//申请商家的名字
            $insertdata1['state']        = '0'; //状态 0待审核
            $insertdata1['yes_cash']     = '0'; //0未发放

            if ($cash!='0') {

                if ($suppliers['cash']>=$cash) {

                    Db::startTrans(); //启动事务
                    try {
                        $rsg = Db::name('supplier')->update($data);
                        $rsg1= Db::name('supplier_cash')->insertGetId($insertdata1);
                        if ($rsg&&$rsg) {
                            Db::commit(); //提交事务
                            $returnData['status']=0;
                            $returnData['msg']='申请提现成功';
                        }else{
                            Db::rollback(); //回滚事务
                            $returnData['status']=1;
                            $returnData['msg']='申请提现失败';
                        }

                    } catch (\PDOException $e) {
                        Db::rollback(); //回滚事务
                        $returnData['status']=1;
                        $returnData['msg']='当前操作人数过多请稍后尝试！';
                    }
                }else{
                    $returnData['status']=1;
                    $returnData['msg']='提现金额不足';
                }

            }else{
                $returnData['status']=1;
                $returnData['msg']='请输入大于0的数字';
            }

        }else{
            $summoney=Db::name('supplier_cash')->where('state<>2 and supplier_id='.$suppliers['ID'])->sum('cash');
            $suppliers['totlemoney']=$suppliers['cash']+$summoney;//总额
            $suppliers['warchmoney']=$summoney;
            $returnData['data']=$suppliers;
            $returnData['status']=0;
            $returnData['msg']='成功';

        }
        }else{
            $returnData=$info;
        }
        return json($returnData);
    }
}
