<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/17
 * Time: 10:28
 */

namespace api\index\controller;
use think\Db;


class Record   extends Auth
{
  public function accountrecord()
  {
      if($this->loginStatus) {
          /*购物币记录*/
          $pagecount = 8;
          $pageTotal = 0;
          $where['UserId'] = $this->username;

          //根据订单id进行搜索
          if($this->request->param('flowtype')){
              $where['FlowType']=$this->request->param('flowtype');
          }
          if($this->request->param('UserFrom')){
              $where['FromWho']=$this->request->param('UserFrom');
          }
          //根据日期进行查找
          if($this->request->param("datemin") and $this->request->param("datemax")){
              $requestdate=$this->request->param("datemax");
              $redate=date("Y-m-d",strtotime("$requestdate +1 day"));
              $where["AddDate"]=array(array('egt',$this->request->param("datemin")),array('elt',$redate),'and');

          }elseif($this->request->param("datemin")){
              $where["AddDate"]=array('egt',$this->request->param("datemin"));
          }elseif($this->request->param("datemax")){
              $requestdate=$this->request->param("datemax");
              $redate=date("Y-m-d",strtotime("$requestdate +1 day"));
              $where["AddDate"]=array('elt',$redate);
          }


          $list = Db::name('accountrecord')->where($where)->order("id desc")->paginate($pagecount);
          $data = [];
          if ($list) {
              $pageTotal = ceil($list->total() / $pagecount);//总页数
              foreach ($list as $k => $v) {
                  $data[$k] = $v = array_change_key_case($v);
                  $condtion['UserID'] = $v['fromwho'];
                  $data[$k]['fromname'] = Db::name('usermsg')->where($condtion)->field('TrueName')->find();
              }


          }
          $returnArr['data'] = $data;
          $returnArr['total'] = $pageTotal;

          $returnArr['data']["data_list"] = $data;
          $returnArr["data"]['total'] = $pageTotal;

          $returnArr['status'] = 1;
          $returnArr['msg'] = '数据记录';
      }else{
          $returnArr['data']=array();
          $returnArr['status']=0;
          $returnArr['msg']='未登录';
      }
    return json($returnArr);

  }
  public  function  account_show()
  {
      if($this->loginStatus) {
          /*购物币记录详情*/
          $accountfind = array_change_key_case(Db::name("accountrecord")->where("id=" . $this->request->param("id"))->find());
          if ($accountfind)
          {
              $memberinfo=Db::name('usermsg')->where("UserId='".$accountfind['fromwho']."'")->field('TrueName')->find();

          }
          $accountfind['fromwhoname']=$memberinfo['TrueName'];
          $returnArr['data'] = $accountfind;
          $returnArr['status'] = 1;
          $returnArr['msg'] = '数据记录';
      }else{
          $returnArr['data']=array();
          $returnArr['status']=0;
          $returnArr['msg']='未登录';
      }
      return json($returnArr);
  }

    /**
     * 奖金记录接口
     * @return \think\response\Json
     */
  public function  prize_list(){
      if($this->loginStatus){
          $pagecount = 5;
          $where['UserId']=$this->username;
//          $where['TypeName']='返积分';
          //根据日期进行查找
          if($this->request->param("datemin") and $this->request->param("datemax")){
              $requestdate=$this->request->param("datemax");
              $redate=date("Y-m-d",strtotime("$requestdate +1 day"));
              $where["AddDate"]=array(array('egt',$this->request->param("datemin")),array('elt',$redate),'and');
          }elseif($this->request->param("datemin")){
              $where["AddDate"]=array('egt',$this->request->param("datemin"));
          }elseif($this->request->param("datemax")){
              $requestdate=$this->request->param("datemax");
              $redate=date("Y-m-d",strtotime("$requestdate +1 day"));
              $where["AddDate"]=array('elt',$redate);
          }
//var_dump($where);exit;
          $list=Db::name('pointsflow')->where($where)->order("id desc")->paginate($pagecount);
//          var_dump($list);exit;
          $data=[];
          if ($list) {
              $pageTotal = ceil($list->total() / $pagecount);//总页数
              foreach ($list as $k => $v) {
                  $data[$k] = $v=array_change_key_case($v);
                  $userinfo=Db::name('usermsg')->field('truename')->find();
                  $data[$k]['fromwhoname']=$userinfo['truename'];
              }
              $returnData['data'] = $data;
              $returnData['total']=$pageTotal;
              $returnData['status']=1;
              $returnData['msg']='成功';
          }else{
              $returnData['status']=0;
              $returnData['msg']='奖金记录为空';
          }
      }else{
          $returnData['status']=0;
          $returnData['msg']='未登录';
      }
      return json($returnData);
  }

    /**
     * 奖金详情接口
     * @return \think\response\Json
     */
    public function  prize_detail(){
        if($this->loginStatus){
            $where['UserId']=$this->username;
            $where['ID']=$this->request->param('id');
            $record=array_change_key_case(Db::name('pointsflow')->where($where)->order("id desc")->find());
            if ($record) {
                $condition['UserId']=$this->username;
                $userinfo=Db::name('usermsg')->where($condition)->field('truename')->find();
                $record['fromwhoname']=$userinfo['truename'];
                $returnData['data'] = $record;
                $returnData['status']=1;
                $returnData['msg']='成功';
            }else{
                $returnData['status']=0;
                $returnData['msg']='奖金记录为空';
            }
        }else{
            $returnData['status']=0;
            $returnData['msg']='未登录';
        }
        return json($returnData);
    }

    /**
     * 积分记录接口
     * @return \think\response\Json
     */
    public function  integral_record(){
        if($this->loginStatus){
            $pagecount = 5;
            $where['UserId']=$this->username;
            $where['TypeName']=['neq','返积分'];
            //根据交易类型进行查找
            if($this->request->param('typename')){
                $where['TypeName']=$this->request->param('typename');

            }
            //根据日期进行查找
            if($this->request->param("datemin") and $this->request->param("datemax")){
                $requestdate=$this->request->param("datemax");
                $redate=date("Y-m-d",strtotime("$requestdate +1 day"));
                $where["AddDate"]=array(array('egt',$this->request->param("datemin")),array('elt',$redate),'and');
            }elseif($this->request->param("datemin")){
                $where["AddDate"]=array('egt',$this->request->param("datemin"));
            }elseif($this->request->param("datemax")){
                $requestdate=$this->request->param("datemax");
                $redate=date("Y-m-d",strtotime("$requestdate +1 day"));
                $where["AddDate"]=array('elt',$redate);
            }

            $list=Db::name('pointsflow')->where($where)->order("id desc")->paginate($pagecount);
            $data=[];
            if ($list) {
                $pageTotal = ceil($list->total() / $pagecount);//总页数
                foreach ($list as $k => $v) {
                    $data[$k] = $v=array_change_key_case($v);
                    $userinfo=Db::name('usermsg')->field('truename')->find();
                    $data[$k]['fromwhoname']=$userinfo['truename'];
                }
                $returnData['data'] = $data;
                $returnData['total']=$pageTotal;
                $returnData['status']=1;
                $returnData['msg']='成功';
            }else{
                $returnData['status']=0;
                $returnData['msg']='积分记录为空';
            }
        }else{
            $returnData['status']=0;
            $returnData['msg']='未登录';
        }
        return json($returnData);
    }

    /**
     * 积分详情接口
     * @return \think\response\Json
     */
    public function  integral_detail(){
        if($this->loginStatus){
            $where['UserId']=$this->username;
            $where['ID']=$this->request->param('id');
            $record=array_change_key_case(Db::name('pointsflow')->where($where)->order("id desc")->find());
            if ($record) {
                $condition['UserId']=$this->username;
                $userinfo=Db::name('usermsg')->where($condition)->field('truename')->find();
                $record['fromwhoname']=$userinfo['truename'];
                $returnData['data'] = $record;
                $returnData['status']=1;
                $returnData['msg']='成功';
            }else{
                $returnData['status']=0;
                $returnData['msg']='积分记录为空';
            }
        }else{
            $returnData['status']=0;
            $returnData['msg']='未登录';
        }
        return json($returnData);
    }
}
