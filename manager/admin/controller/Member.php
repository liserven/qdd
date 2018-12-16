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

namespace app\admin\controller;

\think\Loader::import('controller/Jump', TRAIT_PATH, EXT);

use app\admin\Controller;
use think\Loader;
use think\Session;
use think\Db;
use think\Config;
use think\Exception;
use think\View;
use think\Request;

class Member extends Controller
{
    use \traits\controller\Jump;

    // 视图类实例
    protected $view;
    // Request实例
    protected $request;

    public function __construct()
    {
        if (null === $this->view) {
            $this->view = View::instance(Config::get('template'), Config::get('view_replace_str'));
        }
        if (null === $this->request) {
            $this->request = Request::instance();
        }

        // 用户ID
        defined('UID') or define('UID', Session::get(Config::get('rbac.user_auth_key')));
    }

    public function index(){
        return $this->memberList();
    }

    public function memberList(){
        $where['ID']=['>','0'];
        $map=[];
        if($this->request->param()){
            if($this->request->param('UserId')){
                $UserId=$this->request->param('UserId');
                $map['UserId']=$UserId;
                $where['UserId']=$UserId;
            }
            if($this->request->param('Mobile')){
                $UserId=$this->request->param('Mobile');
                $map['Mobile']=$UserId;
                $where['Mobile']=$UserId;
            }
            if($this->request->param('TrueName')){
                $TrueName=$this->request->param('TrueName');
                $map['TrueName']=$TrueName  ;
                $where['TrueName']=['like','%'.$TrueName.'%'];
            }
            if($this->request->param('IsAudit') || $this->request->param('IsAudit')=='0'){
                $userState=$this->request->param('IsAudit');
                $map['IsAudit']=$userState;
                $where['IsAudit']=$userState;

            }
            if($this->request->param('userType')){
                $userType=$this->request->param('userType');
                $map['userType']=$userType;
                $where['userType']=$userType;
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
        $daochu=$this->request->param('daochu');
        if(isset($daochu)&&$daochu=='daochu'){
            set_time_limit(0);
            $header = ['会员ID', '姓名', '积分','购物','加入时间'];
            $data = Db::name("usermsg")->field("UserId,TrueName,Umoney,Pv,AddDate")->where($where)->order('ID desc')->select();
            if ($error = \Excel::export($header, $data, "会员信息", '2007')) {
                throw new Exception($error);
            }
        }
        $count=Db::name('usermsg')->where($where)->count();//获取满足条件的总记录数
        $list=Db::name('usermsg')->where($where)->order('id desc')->paginate();//根据条件分页输出
        if(!empty($map)) {
            foreach ($map as $key => $value) {
                $list->appends($key, $value);//分页链接中添加请求的参数
            }
        }
        //购物币总和
        $umoneysum=Db::name('usermsg')->where($where)->sum("Umoney");
        //消费积分总和
        $pvsum=Db::name('usermsg')->where($where)->sum("Pv");
        $this->view->assign("umoneysum",$umoneysum);
        $this->view->assign("pvsum",$pvsum);
        $this->view->assign('Member_list',$list);
        $this->view->assign('page',$list->render());//输出分页的样式
        $this->view->assign('count',$count);
        return $this->view->fetch('Memberlist');


    }


    /**
     * 会员的删除
     */
    public function memberDelete(){
        $UserId=$this->request->param();
        $where['UserId']=$UserId['UserId'];
        $where['IsAudit']=0;
        if($UserId['UserId']) {
                Db::name('usermsg')->where($where)->delete();
            $returnData['status']=1;
            $returnData['msg']='删除成功！';
        }else{
            $returnData['status']=0;
            $returnData['msg']='删除失败！';
        }
        return json($returnData);
    }
    /**
     * 会员的启用与锁定
     */
    public function member_start(){
        $UserId=$this->request->param();
        $where['UserId']=$UserId['userid'];
        $act=$this->request->param('action');
        if(!empty($act)&&$act=='start'){
            $data["IsAudit"]=3;
            Db::name('usermsg')->where($where)->update($data);
            $returnData['status'] = 1;
            $returnData['msg'] = '修改成功！';
        }

       else if(!empty($act)&&$act=='stop'){
            $data["IsAudit"]=4;
            Db::name('usermsg')->where($where)->update($data);
           $returnData['status'] = 1;
           $returnData['msg'] = '修改成功！';
        }
        else{

            $returnData['status'] = 0;
            $returnData['msg'] = '修改失败！';
        }
        return json($returnData);
    }


    //修改密码
    public function change_password(){
        $UserId=$this->request->param('UserId');
        $act=$this->request->param('act');
        if(!empty($act)&$act=='edit') {
            $memberData=$this->request->post();

            if($memberData['dlpwd']=='0'){
                $data["Password"]=md5($memberData["newpassword"]);
            }
            else{
                $data["LevIIPwd"]=md5($memberData["newpassword"]);

            }

            Db::name('usermsg')->where("UserId='".$UserId."'")->update($data);

            //$this->success("密码修改成功！",url("Member/Memberlist"));

        }
         else   {
             $userinfo=Db::name('usermsg')->where("UserId='".$UserId."'")->find();
             $this->view->assign('list',$userinfo);
             return $this->view->fetch('change_password');

         }
    }
    //修改个人信息
    public function  member_edit(){
        $userId=$this->request->param('UserId');
        $act=$this->request->param('action');
        $useredit=array_change_key_case(Db::name('usermsg')->where("UserId='".$userId."'") ->find()) ;
        if(!empty($act)&$act=='edit'){
            $oldmobile=$useredit["mobile"];
            $oldtruename=$useredit["truename"];
            $oldidcardno=$useredit["idcardno"];
            $memberData=$this->request->post();
            if($oldmobile!=$memberData["mobile"] or $oldtruename!=$memberData["truename"] or $oldidcardno!=$memberData["idcardno"]) {
                if($oldmobile!=$memberData["mobile"]){
                    $chkmobile=Db::name('usermsg')->where('UserId='.$memberData["mobile"])->find();
                    if($chkmobile){
                        die ( '<script type="text/javascript"> alert("您准备修改的手机号已经存在，请换个手机号进行修改！");window.history.back(-1);</script>' );
                        return;

                    }else{
                        $data["Mobile"]=$memberData["mobile"];
                    }
                }
            }
            //$data['userType']=$memberData['usertypeid'];

            //修改会员享受的层奖的代数，普通会员享受3层，奖金积分大于等于100的时候享受7代，领导人享受10代
            /*if($memberData['usertypeid']==2){
                $data['ShareFloor']=10;
            }else{
                $sql= "select SUM(Amount) realmoney from pointsflow where typename='返积分' and   (Memo   like '%一级%'  or  Memo  like '%二级%'  or Memo   like '%三级%') AND UserId='".$userId."'";//查找该会员总共得到的奖金积分
                $returnData= Db::query($sql);
                if($returnData[0]['realmoney']>=100){
                    $data['ShareFloor']=7;
                }else{
                    $data['ShareFloor']=0;
                }
            }*/

            $data["TrueName"]=$memberData["truename"];
            $data["Sex"]=$memberData["sex"];
            $data["IdCardNo"]=$memberData["idcardno"];
            $data["QQ"]=$memberData["qq"];
            $data["Province"]=$memberData["province"];
            $data["City"]=$memberData["city"];
            $data["County"]=$memberData["county"];
            $data["Address"]=$memberData["address"];
            $data["userType"]=$memberData["userType"];
            Db::name('usermsg')->where("UserId='".$userId."'")->update($data);

        }
        else{
            $where1['ID'] = ['<>', $useredit['usertype']];
            $usertype=Db::name('usertype')->where($where1)->select();
            $this->view->assign('usertypelist',$usertype);
            $this->view->assign('userinfo',$useredit);
            return  $this->view->fetch('member_edit');

        }
    }

    public  function    member_recharge_Umoney() //充值购物币
{

    $userId=$this->request->param('UserId');
    $memberData=$this->request->post();
    $type=$this->request->param('rechagetype');
    $userinfo=array_change_key_case(Db::name('usermsg')->where("UserId='".$userId."'")->find());
    if ($type==1){
        if($memberData['umoney']<=0){
            echo "umoneyzero";  //必须大于0
            return;
        }
        $yuumoney = $data['Umoney'] = $userinfo['umoney']+$memberData['umoney'];
        Db::name('usermsg') ->where("UserId='".$userId."'")->update($data);
        $datauf["FlowType"]="充值";
        $cremark="管理员后台充币";
       //在购物币消费记录表里添加记录
        $datauf["UserId"]=$userId;
        $datauf["Amount"]=$memberData["umoney"];
        $datauf["Balance"]=$yuumoney; //剩余购物币
        $datauf["FromWho"]=Session::get('user_name');
        $datauf["AddDate"]=date('Y-m-d H:i:s',time());
        $datauf["Memo"]=$cremark."(".$memberData['remark'].")";
        Db::name('accountrecord')->insert($datauf);
    }
   if ($type==2){
        if($memberData['umoney']<=0){
            echo "umoneyzero";  //必须大于0
            return;
        }
       if($userinfo["umoney"]<$memberData["umoney"]){
           echo "memmoneyenough";  //会员的购物币不足
           return;
       }
        $yuumoney=$data['Umoney']=$userinfo['umoney']-$memberData['umoney'];
       Db::name('usermsg') ->where("UserId='".$userId."'")->update($data);


        $datauf["FlowType"]="扣币";
        $cremark="管理员后台扣币";
        //在购物币消费记录表里添加记录
        $datauf["UserId"]=$userId;
        $datauf["Amount"]=0-$memberData["umoney"];
        $datauf["Balance"]=$yuumoney; //剩余购物币
        $datauf["FromWho"]=Session::get('user_name');
       if  (!empty($memberData['remark']) )
       {

           $datauf["Memo"]=$cremark."(".$memberData['remark'].")";
       }
       else{
           $datauf["Memo"]=$cremark;
       }
       $datauf["AddDate"]=date('Y-m-d H:i:s',time());
        Db::name('accountrecord')->insert($datauf);
    }
      else{

           $this->view->assign('list',$userinfo);
           return $this->view->fetch('member_recharge_Umoney');

       }

}
    public  function    member_recharge_Pv() //充值扣币积分
    {

        $memberData=$this->request->post();
        $UserId=$this->request->param('UserId');
        $type=$this->request->param('rechagetype');
        $userinfo=array_change_key_case(Db::name('usermsg')->where("UserId='".$UserId."'")->find());
        if($type==1){
                if($memberData['points']<=0){
                    echo "pointszero";  //必须大于0
                    return;
                }
                $yupv=$data["Pv"]=$userinfo['pv']+$memberData['points'];
                Db::name('usermsg') ->where("UserId='".$UserId."'")->update($data);
                $datauf["UserId"]=$UserId;
                $datauf["TypeName"]="充值";
                $datauf["TypeId"]="2";
                $datauf["Amount"]=$memberData['points'];
                $datauf["BalancePv"]=$yupv; //剩余消费积分
                $datauf["FromWho"]=Session::get('user_name');
                if  (!empty($memberData['remark']) )
                {

                    $datauf["Memo"]="管理员后台充购物积分"."(".$memberData['remark'].")";
                }
                 else{
                     $datauf["Memo"]="管理员后台充购物积分";
                 }
                $datauf["AddDate"]=date('Y-m-d H:i:s',time());
                Db::name('pointsflow')->insert($datauf);
            }
        if($type==2){
            if($memberData['points']<=0){
                echo "pointszero";  //必须大于0
                return;
            }
            if($userinfo['pv']<$memberData['points']){
                echo "pointsenough";  //账户月余额不足
                return;
            }
            $yupv=$data["Pv"]=$userinfo['pv']-$memberData['points'];
            Db::name('usermsg') ->where("UserId='".$UserId."'")->update($data);
            $datauf["UserId"]=$UserId;
            $datauf["TypeName"]="扣币";
            $datauf["Amount"]=$memberData['points'];
            $datauf["BalancePv"]=$yupv; //剩余消费积分
            $datauf["FromWho"]=Session::get('user_name');
            if  (!empty($memberData['remark']) )
            {

                $datauf["Memo"]="管理员后台扣购物积分"."(".$memberData['remark'].")";
            }
            else{
                $datauf["Memo"]="管理员后台扣购物积分";
            }
            $datauf["AddDate"]=date('Y-m-d H:i:s',time());
            Db::name('pointsflow')->insert($datauf);
        }
        else{
            $this->view->assign('list',$userinfo);
            return $this->view->fetch('member_recharge_Pv');
        }

    }
    //会员升级
    public   function  memberup() {
        $UserId=$this->request->param('UserId');
        $userinfo=array_change_key_case(Db::name('usermsg')->where("UserId='".$UserId."'")->find());
        $this->view->assign('list',$userinfo);
        $usertype=Db::name('usertype')->select();
        $this->view->assign('usertypelist',$usertype);
        return $this->view->fetch('memberup');

    }
    public  function  member_show(){

        $UserId=$this->request->param('UserId');
        $userinfo=array_change_key_case(Db::name('usermsg')->where("UserId='".$UserId."'")->find());
        $this->view->assign('userinfo',$userinfo);
        return $this->view->fetch('member_show');
    }
     //数据导出
    public  function  member_outexcel()
    {
        if ($this->request->isPost()) {
            $p=$this->request->param('p')?$this->request->param('p'):1;
            $header = ['会员ID', '姓名', '类别', '购物币','积分','加入时间'];
            $data = Db::name("usermsg")->field("UserId,TrueName,userType,Umoney,Pv,AddDate")->limit(($p-1)*20,20)->select();
            if ($error = \Excel::export($header, $data, "会员列表信息", '2007')) {
                throw new Exception($error);
            }
        } else {
            return $this->view->fetch();
        }
    }

}
