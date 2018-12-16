<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/6/5
 * Time: 14:52
 */

namespace app\admin\controller;

\think\Loader::import('controller/Jump', TRAIT_PATH, EXT);


use app\admin\Controller;
use think\Db;

class UserPrize  extends Controller
{
    use \traits\controller\Jump;

    public  function index(){

        return $this->prizeList();
    }

    public function prizeList(){
        $where['query_condition']='1=1';
        $map=[];
        if($this->request->param()){
            //根据会员ID查询
            if($this->request->param('keyuserid')){
                $keyuserid=$this->request->param('keyuserid');
                $map['keyuserid']=$keyuserid;
                $where['UserId']=$keyuserid;
            }
            //根据交易类型查询
            if($this->request->param('typeName')){
                $recordType=$this->request->param('typeName');
                $map['typeName']=$recordType;
                $where['TypeName']=['like','%'.$recordType.'%'];
            }
            //根据日期进行查找 start
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
            //根据日期进行查找 end

            //按金额查询 start
            if($this->request->param("moneystart") and $this->request->param("moneyend")){
                $where["Amount"]=array(array('egt',$this->request->param("moneystart")),array('elt',$this->request->param("moneyend")),'and');
                $map["moneystart"]=$this->request->param("moneystart");
                $map["moneyend"]=$this->request->param("moneyend");
            }elseif($this->request->param("moneystart")){
                $where["Amount"]=array('egt',$this->request->param("moneystart"));
                $map["moneystart"]=$this->request->param("moneystart");
            }elseif($this->request->param("moneyend")){
                $where["Amount"]=array('elt',$this->request->param("moneyend"));
                $map["moneyend"]=$this->request->param("moneyend");
            }
            //金额查询end
        }

        $where['TypeName']='返积分';
        if(count($where)==1){
            unset($where['query_condition']);
            $where='1=1';
        }else{
            unset($where['query_condition']);
        }

        $sumData=Db::name('pointsflow')->where($where)->field('TypeName,sum(Amount) as Amount')->group('TypeName')->select();
        $count=Db::name('pointsflow')->where($where)->order('ID asc')->count();//获取满足条件的总记录数
        $list=Db::name('pointsflow')->where($where)->order('ID asc')->paginate(20);//根据条件分页输出

        $staticPageArr=$staticArr=[['TypeName'=>'充值','Amount'=>0],['TypeName'=>'扣积分','Amount'=>0],['TypeName'=>'赠积分','Amount'=>0],['TypeName'=>'返积分','Amount'=>0]];
        if(!empty($sumData)){
            foreach ($sumData as $k=>$v){
                if($v['TypeName']=='充值'){
                    $staticArr[0]=$v;
                }
                if($v['TypeName']=='扣积分'){
                    $staticArr[1]=$v;
                }
                if($v['TypeName']=='赠积分'){
                    $staticArr[2]=$v;
                }
                if($v['TypeName']=='返积分'){
                    $staticArr[3]=$v;
                }
            }
        }

        foreach ($list as $k=>$v){
            if($v['TypeName']=='充值'){
                $staticPageArr[0]['Amount']+=$v['Amount'];
            }
            if($v['TypeName']=='扣积分'){
                $staticPageArr[1]['Amount']+=$v['Amount'];
            }
            if($v['TypeName']=='赠积分'){
                $staticPageArr[2]['Amount']+=$v['Amount'];
            }
            if($v['TypeName']=='返积分'){
                $staticPageArr[3]['Amount']+=$v['Amount'];
            }
        }

        if(!empty($map)) {
            foreach ($map as $key => $value) {
                $list->appends($key, $value);//分页链接中添加请求的参数
            }
        }

        $this->view->assign('integralList',$list);
        $this->view->assign('page',$list->render());//输出分页的样式
        $this->view->assign('count',$count);
        $this->view->assign('staticPageArr',$staticPageArr);
        $this->view->assign('staticArr',$staticArr);
        return $this->view->fetch('prizeList');
    }

    /**
     * 批量发放奖金
     */
    public function prize_Grant(){

        $where = $this->request->param();
        if ($where['id']) {
            if (is_array($where['id'])) {//批量发放
                foreach ($where['id'] as $id) {
                    $returnData=$this->prize_Grant_two($id)->getData();//奖金发放
                }
            }else{
                $id=$where['id'];
                $returnData=$this->prize_Grant_two($id)->getData();//单个发放
            }
        } else {
            $returnData['status'] = 0;
            $returnData['msg'] = '参数错误！';
        }
        return json($returnData);
    }

    public function prize_Grant_two($id){
        Db::startTrans();
        try {
            //要激活的会员的信息
            $userPrizeInfo = Db::name('pointsflow')->where("ID=" . $id)->find();


            if($userPrizeInfo['Is_Grant']==1) {

                $where['UserId']=$userPrizeInfo['UserId'];
                //会员等级信息
                $userInfo = Db::name('usermsg')->where($where)->find();
                if(!empty($userInfo))
                {
                   $datauser["UserPrize"]= $userInfo["UserPrize"]+$userPrizeInfo["Amount"];
                    //修改该奖金的状态为已发放
                    $data["Is_Grant"] = 2;//是否发放（1否 2是）
                    $data["Grant_Date"] = date('Y-m-d H:i:s', time());//发放时间
                    $data["BalancePv"] =  $datauser["UserPrize"];//剩余奖金
                    //修改会员表的信息
                    Db::name('pointsflow')->where("ID=" . $id)->update($data);
                    Db::name('usermsg')->where($where)->update($datauser);
                    $returnData['status'] = 1;
                    $returnData['msg'] = '发放成功！';
                }

            }else{
                $returnData['status'] = 0;
                $returnData['msg'] = '该条记录不存在或已发放！';
            }
            if( $returnData['status'] = 1)
            {
                Db::commit();
                return json($returnData);
            }


        }catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return ['status' => 0, 'msg' => '操作失败'.$e->getMessage()];
        }
    }

}
