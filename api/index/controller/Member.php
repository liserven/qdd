<?php
/**
 * Created by 赵晓凡
 * User: zhaoxiaofan
 * Date: 2017/3/15
 * Time: 10:30
 */
namespace api\index\controller;

use think\Db;
use think\Session;

/**
 * Class Member
 * @package api\index\controller
 * @return 会员
 */
class Member extends Auth
{
    /**
     * 会员地址添加接口
     * @return \think\response\Json
     */
    public function address_add(){

        if($this->loginStatus){
            $member=$this->username;
            $addressData=$this->request->param();
            //var_dump($addressData);exit;
            if(empty($addressData["receivename"])){
                $returnData['data'] = array();
                $returnData['status']=0;
                $returnData['msg']='收货人不能为空！';
                return json($returnData);
            }

            if($addressData["province"]==1||empty($addressData["city"])||empty($addressData["county"])){
                $returnData['data'] = array();
                $returnData['status']=0;
                $returnData['msg']='请选择所在地区！';
                return json($returnData);
            }
            if(empty($addressData["address"])){
                $returnData['data'] = array();
                $returnData['status']=0;
                $returnData['msg']='详细地址不能为空！';
                return json($returnData);
            }
            /**
             *匹配手机号码
             *规则：
             *手机号码基本格式：
             *前面三位为：
             *移动：134-139 147 150-152 157-159 182 187 188
             *联通：130-132 155-156 185 186
             *电信：133 153 180 189
             *后面八位为：
             *0-9位的数字
             */
            if(!empty($addressData["mobile"])){
                $rule  = "/^((13[0-9])|147|(15[0-35-9])|180|182|(18[5-9]))[0-9]{8}$/A";
                if(!preg_match($rule,$addressData["mobile"])){
                    $returnData['data'] = array();
                    $returnData['status']=0;
                    $returnData['msg']='电话号码格式错误！';
                    return json($returnData);
                }
            }else{
                $returnData['data'] = array();
                $returnData['status']=0;
                $returnData['msg']='电话号码不能为空！';
                return json($returnData);
            }
            /**
             *匹配身份证号码
             *规则：
             *身份证号码基本格式：
             *前面十七位：
             *0-9位的数字
             * 后面一位：
             * 0-9的数字或者X字母
             */
//              if(!empty($addressData["Pcode"])){
//                  $rule  = "/^[0-9]{17}([0-9]|X)$/A";
//                  if(!preg_match($rule,$addressData["Pcode"])){
//                      $returnData['data'] = array();
//                      $returnData['status']=0;
//                      $returnData['msg']='身份证号码格式错误！';
//                      return json($returnData);
//                  }
//              }else{
//                  $returnData['data'] = array();
//                  $returnData['status']=0;
//                  $returnData['msg']='身份证号码不能为空！';
//                  return json($returnData);
//              }
            $data["UserId"]=$member;
            $data["ReceiveName"]=$addressData["receivename"];
            //$data["Pcode"]=$addressData["Pcode"];
            $data["Province"]=$addressData["province"];
            $data["City"]=$addressData["city"];
            $data["County"]=$addressData["county"];
            $data["Address"]=$addressData["address"];
//            $data["PostCode"]=$addressData["postcode"];
            $data["Mobile"]=$addressData["mobile"];
            $data["AddDate"]=date('Y-m-d H:i:s',time());
//var_dump($addressData);exit;
            if(!empty($addressData["isdefault"])){
                $datadefault["IsDefault"]=0;
                Db::name('comreceiveinfo')->where("UserId='".$member."'")->update($datadefault);
                $data["IsDefault"]=1;
            }else{
                $data["IsDefault"]=0;
            }
            $id=Db::name('comreceiveinfo')->insertGetId($data);
            if(!empty($id)){
                $returnData['data'] = array();
                $returnData['status']=1;
                $returnData['msg']='地址添加成功';
            }else{
                $returnData['data'] = array();
                $returnData['status']=0;
                $returnData['msg']='地址添加失败';
            }
        }else{
            $returnData['data'] = array();
            $returnData['status']=0;
            $returnData['msg']='未登录';
        }
        return json($returnData);
    }

    /**
     * 会员地址修改接口
     * @return \think\response\Json
     */

    public function address_edit(){
        if($this->loginStatus){
            $member=$this->username;
            $addressData=$this->request->param();
            if(empty($addressData["receivename"])){
                $returnData['data'] = array();
                $returnData['status']=0;
                $returnData['msg']='收货人不能为空！';
                return json($returnData);
            }
            if($addressData["province"]==1||empty($addressData["city"])||empty($addressData["county"])){
                $returnData['data'] = array();
                $returnData['status']=0;
                $returnData['msg']='请选择所在地区！';
                return json($returnData);
            }
            if(empty($addressData["address"])){
                $returnData['data'] = array();
                $returnData['status']=0;
                $returnData['msg']='详细地址不能为空！';
                return json($returnData);
            }
            if(!empty($addressData["mobile"])){
                $rule  = "/^((13[0-9])|147|(15[0-35-9])|180|182|(18[5-9]))[0-9]{8}$/A";
                if(!preg_match($rule,$addressData["mobile"])){
                    $returnData['data'] = array();
                    $returnData['status']=0;
                    $returnData['msg']='电话号码格式错误！';
                    return json($returnData);
                }
            }else{
                $returnData['data'] = array();
                $returnData['status']=0;
                $returnData['msg']='电话号码不能为空！';
                return json($returnData);
            }
            /**
             *匹配身份证号码
             *规则：
             *身份证号码基本格式：
             *前面十七位：
             *0-9位的数字
             * 后面一位：
             * 0-9的数字或者X字母
             */
//            if(!empty($addressData["Pcode"])){
//                $rule  = "/^[0-9]{17}([0-9]|X)$/A";
//                if(!preg_match($rule,$addressData["Pcode"])){
//                    $returnData['data'] = array();
//                    $returnData['status']=0;
//                    $returnData['msg']='身份证号码格式错误！';
//                    return json($returnData);
//                }
//            }else{
//                $returnData['data'] = array();
//                $returnData['status']=0;
//                $returnData['msg']='身份证号码不能为空！';
//                return json($returnData);
//            }

            $data["UserId"]=$member;
            $data["ReceiveName"]=$addressData["receivename"];
            $data["Province"]=$addressData["province"];
            $data["City"]=$addressData["city"];
            $data["County"]=$addressData["county"];
            $data["Address"]=$addressData["address"];
//            $data["PostCode"]=$addressData["postcode"];
            $data["Mobile"]=$addressData["mobile"];
            $data["AddDate"]=date('Y-m-d H:i:s',time());

            if(!empty($addressData["isdefault"])){
                $datadefault["IsDefault"]=0;
                Db::name('comreceiveinfo')->where("UserId='".$member."'")->update($datadefault);
                $data["IsDefault"]=1;
            }else{
                $data["IsDefault"]=0;
            }

            $id=$this->request->param('id');
            $where['UserId']=$this->username;
            $where['Id']=$id;

            Db::name('comreceiveinfo')->where($where)->update($data);
            $returnData['data'] = array();
            $returnData['status']=1;
            $returnData['msg']='地址修改成功';
        }else{
            $returnData['data'] = array();
            $returnData['status']=0;
            $returnData['msg']='未登录';
        }
        return json($returnData);
    }

    /**
     * 会员信息
     * @return \think\response\Json
     */
    public function user_info(){
        if($this->loginStatus){
            $where['UserId']=$this->username;
            $userid=$this->username;
            $userinfo['nickname']=$userid;
            $show=1;
            $orderstaymoney=Db::name('ordermain')->where("UserId='".$userid."'  and  Status=1")->count();
            $orderstayDeliver=Db::name('ordermain')->where("UserId='".$userid."'  and  (Status=2  or Status=3) ")->count();
            $orderstayreceipt=Db::name('ordermain')->where("UserId='".$userid."'  and  Status=4 ")->count();
            $orderstayrefund=Db::name('ordermain')->where("UserId='".$userid."'  and  Status=15")->count();
            $userinfo['staymoney']=$orderstaymoney;
            $userinfo['stayDelive']=$orderstayDeliver;
            $userinfo['receipt']=$orderstayreceipt;
            $userinfo['refund']=$orderstayrefund;
            $userinfo['show']=$show;
            $returnData['data']=$userinfo;
            $returnData['status']=1;
            $returnData['msg']='成功';
        }else{
            $returnData["data"] = array();
            $returnData['status']=0;
            $returnData['msg']='未登录';
        }
        return json($returnData);
    }
    /**
     * 会员信息的修改
     * @return \think\response\Json
     */
    public function user_edit(){
        if($this->loginStatus){
            if($this->request->post()){
                $userData=$this->request->post();
                $file = $this->request->file('memberpic');
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
                    $data["AvatarUrl"]=$filename;
                }

                $data["Mobile"]=$userData["mobile"];
                $data["TrueName"]=$userData["truename"];
                //$data["Sex"]=$userData["sex"];
                $data["Sex"]=1;
                $data["IdCardNo"]=$userData["idcardno"];
                $data["QQ"]=$userData["qq"];
                $data["Province"]=$userData["province"];
                $data["City"]=$userData["city"];
                $data["County"]=$userData["county"];
                $data["Address"]=$userData["address"];

                $where['UserId']=$this->username;
                Db::name('usermsg')->where($where)->update($data);

                if(ismobile()){
                    $url='/mobile.php/member/useredit';
                }
                $returnData["data"] = array();
                $returnData['status']=1;
                $returnData['msg']='修改成功';

//                $string='<script type="text/javascript">';
//                $string.='location.href="'.$url.'";';
//                $string.='</script>';
//                return $string;
            }else{
                $returnData["data"] = array();
                $returnData['status']=0;
                $returnData['msg']='数据提交方式有误';
            }
        }else{
            $returnData["data"] = array();
            $returnData['status']=0;
            $returnData['msg']='未登录';
        }
        return json($returnData);
    }

    /**
     * 会员密码的重置
     * @return \think\response\Json
     */
    public function user_password_reset(){
        if($this->loginStatus){
            $where['UserId']=$this->username;
          /*  if($this->request->param("dlpwd")==0){
                $where['Password']=md5($this->request->param("password"));
                $oldpasschk=Db::name('usermsg')->where($where)->find();
            }else{
                $where['LevIIPwd']=md5($this->request->param("password"));
                $oldpasschk=Db::name('usermsg')->where($where)->find();
            }
            if(!$oldpasschk){
                $returnData["data"] = array();
                $returnData['status']=0;
                $returnData['msg']='原始密码输入错误！';
                return json($returnData);
            }*/
            if($this->request->param("newpassword")!=$this->request->param("newpassword2")){
                $returnData["data"] = array();
                $returnData['status']=0;
                $returnData['msg']='密码不一致！';
                return json($returnData);
            }
            if($this->request->param("dlpwd")==0){
                $data["Password"]=md5($this->request->param("newpassword"));
                Db::name('usermsg')->where($where)->update($data);
                Session::clear();
                $returnData["data"] = array();
                $returnData['status']=1;
                $returnData['msg']='密码修改成功，请重新登录！';
            }else{
                $data["LevIIPwd"]=md5($this->request->param("newpassword"));
                Db::name('usermsg')->where($where)->update($data);
                $returnData["data"] = array();
                $returnData['status']=1;
                $returnData['msg']='支付密码修改成功！';
            }

        }else{
            $returnData["data"] = array();
            $returnData['status']=0;
            $returnData['msg']='未登录';
        }
        return json($returnData);
    }

    /**
     * 会员收货地址列表
     * @return \think\response\Json
     */

    public function address_list(){
        if($this->loginStatus){
            $pagecount = 8;
            $pageTotal=0;
            $where['UserId']=$this->username;
            $data=[];
            $addresslist=Db::name('comreceiveinfo')->where($where)->order('id desc')->paginate($pagecount);
            if($addresslist){
                $pageTotal = ceil($addresslist->total()/$pagecount);//总页数
                foreach ($addresslist as $val){
                    $val['Province']=getcityname($val['Province']);
                    $val['City']=getcityname($val['City']);
                    $val['County']=getcityname($val['County']);
                    $data[]=array_change_key_case($val);
                }
            }
            $returnData["data"]['addresslist']=$data;
            $returnData["data"]['total']=$pageTotal;

            $returnData['status']=1;
            $returnData['msg']='获取数据成功';
        }else{
            $returnData['data'] = array();
            $returnData['status']=0;
            $returnData['msg']='未登录';
        }
        return json($returnData);
    }

    /**
     * 会员收货地址的删除以及默认收货地址的设置
     * @return \think\response\Json
     */
    public function address_action(){
        if($this->loginStatus){
            $type=$this->request->param('type');
            $id=$this->request->param('id');
            if($type=='set_default'){//默认收货地址的设置
                $where['UserId']=$this->username;
                $updata['IsDefault']=0;
                Db::name('comreceiveinfo')->where($where)->update($updata);
                $where['Id']=$id;
                $updata['IsDefault']=1;
                Db::name('comreceiveinfo')->where($where)->update($updata);
                $returnData['data'] = array();
                $returnData['status']=1;
                $returnData['msg']='设置默认收货地址成功';
            }elseif($type=='del_default'){//收货地址的删除
                $where['Id']=$id;
                Db::name('comreceiveinfo')->where($where)->delete();
                $returnData['data'] = array();
                $returnData['status']=1;
                $returnData['msg']='地址删除成功';
            }
        }else{
            $returnData['data'] = array();
            $returnData['status']=0;
            $returnData['msg']='未登录';
        }
        return json($returnData);
    }

    /**
     *会员地址详情
     * @return \think\response\Json
     */
    public function address_detail(){
        if($this->loginStatus){
            $id=$this->request->param('id');
            $where['UserId']=$this->username;
            $where['Id']=$id;
            $address_detail=array_change_key_case(Db::name('comreceiveinfo')->where($where)->find());
            if($address_detail){
                $returnData['detail']=$address_detail;
                $returnData['status']=1;
                $returnData['msg']='数据记录';
            }else{
                $returnData['status']=0;
                $returnData['msg']='地址不存在';
            }
        }else{
            $returnData['status']=0;
            $returnData['msg']='未登录';
        }
        return json($returnData);
    }

//    推荐用户
    public function user_subordinate(){
        if($this->loginStatus){
            $pagecount=10;
            $where['pid']=$this->username;
            $data=Db::name('userrecommenddiagram')->where($where)->paginate($pagecount);
            $dataArr=[];
            foreach ($data as $key=>$val){
                $val=array_change_key_case($val);
                $userinfo=Db::table('usermsg')->where("UserId='".$val['userid']."'")->field('userid,nickname,adddate,avatarurl')->find();
                if(empty($userinfo['avatarurl'])){
                    $userinfo['avatarurl']='http://wx.qlogo.cn/mmopen/3YBUWIibibIkPlaZsgNO0x7AaOdrkzBLvv5CZaBj8QJBbh6e4BpQOreibUViahxrYFEfibicu4qoLkMCK4f7OdZldB1w/0';
                }
                if(empty($userinfo['nickname'])){
                    $userinfo['name']=$userinfo['userid'].'（匿名）';
                }else{
                    $userinfo['name']=$userinfo['userid'].'（'.$userinfo['nickname'].'）';
                }
                $order_money=Db::table('ordermain')->where("UserId='".$val['userid']."'")->sum('GoodsAmount');
                $userinfo['order_money']=empty($order_money)?0:$order_money;
                $dataArr[]=$userinfo;
            }
            if(!empty($dataArr)) {
//                $returnData['total']=ceil($data->total()/$pagecount);//总页数
//                $returnData['data'] = $dataArr;
//                $returnData['usernum'] = $data->total();

                $returnData["data"]['total']=ceil($data->total()/$pagecount);//总页数
                $returnData["data"]['data_list'] = $dataArr;
                $returnData["data"]['usernum'] = $data->total();

                $returnData['status'] = 1;
                $returnData['msg'] = '获取列表数据成功';
            }else{
//                $returnData['data'] = '';
//                $returnData['usernum'] = 0;
                $returnData['data']['usernum'] = 0;
                $returnData['status'] = 0;
                $returnData['msg'] = '暂无数据';
            }
        }else{
            $returnData['data']=array();
            $returnData['status']=0;
            $returnData['msg']='未登录';
        }
        return json($returnData);
    }



    public function user_account(){
        return $this->view->fetch('user_account');
    }
}
