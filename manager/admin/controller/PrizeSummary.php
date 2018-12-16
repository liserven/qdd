<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/6/15
 * Time: 9:23
 */

namespace app\admin\controller;

use app\admin\Controller;
use think\Db;

class PrizeSummary extends Controller
{
    use \traits\controller\Jump;

    public  function index(){

        return $this->prizeSummaryList();
    }

    public function prizeSummaryList()
    {
        $where['query_condition'] = '1=1';
        $map = [];
        if ($this->request->param()) {
            //根据会员ID查询
            if ($this->request->param('keyuserid')) {
                $keyuserid = $this->request->param('keyuserid');
                $map['keyuserid'] = $keyuserid;
                $where['UserId'] = $keyuserid;
            }
            //根据日期进行查找 start
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
            //根据日期进行查找 end
        }

        $where['TypeName'] = '返积分';

        if (count($where) == 1) {
            unset($where['query_condition']);
            $where = '1=1';
        } else {
            unset($where['query_condition']);
        }

//        echo '<pre>';
//        print_r($where);
//        echo '</pre>';
//        exit();
        $sumData = Db::name('pointsflow')->where($where)->field('UserId,SUM(Amount) Amount,UserId,Is_Grant,MAX(BalancePv) BalancePv ,SUM(Amount)-MAX(BalancePv)  notgrant,TypeName')->group('UserId')->select();
//        $count= count($sumData);
        $count=Db::name('pointsflow')->where($where)->group('UserId')->countgroupby();
        $list = Db::name('pointsflow')->where($where)->field('UserId,SUM(Amount) Amount,MAX(BalancePv) BalancePv ,SUM(Amount)-MAX(BalancePv)  notgrant,TypeName')->group('UserId')->paginategroupby(20);//根据条件分页输出


//        $list = Db::name('vw_test')->paginate(20);//根据条件分页输出

       $pagestaticArr= $staticArr = [ ['TypeName' => '总金额', 'Amount' => 0], ['TypeName' => '未发放金额', 'Amount' => 0], ['TypeName' => '已发放金额', 'Amount' => 0]];
        if(!empty($sumData)){
            foreach ($sumData as $k=>$v){
                    $staticArr[0]['Amount']+=$v['Amount'];
                    $staticArr[1]['Amount']+=$v['notgrant'];
                    $staticArr[2]['Amount']+=$v['BalancePv'];
            }
        }
        if(!empty($list)){
            foreach ($list as $k=>$v){
                $pagestaticArr[0]['Amount']+=$v['Amount'];
                $pagestaticArr[1]['Amount']+=$v['notgrant'];
                $pagestaticArr[2]['Amount']+=$v['BalancePv'];
            }
        }


        if(!empty($map)) {
            foreach ($map as $key => $value) {
                $list->appends($key, $value);//分页链接中添加请求的参数
            }
        }

        $this->view->assign('integralList', $list);
        $this->view->assign('page', $list->render());//输出分页的样式
        $this->view->assign('count', $count);
        $this->view->assign('staticArr', $staticArr);
        $this->view->assign('pagestaticArr', $pagestaticArr);
        return $this->view->fetch('prizeSummary');
    }

    //数据导出奖金
    public  function  prize_outexcel()
    {
        if ($this->request->isPost()) {

            $map = [];
            if ($this->request->param()) {
                //根据会员ID查询
                if ($this->request->param('keyuserids')) {
                    $keyuserid = $this->request->param('keyuserids');
                    $map['keyuserid'] = $keyuserid;
                    $where['UserId'] = $keyuserid;
                }
                //根据日期进行查找 start
                if ($this->request->param("datemins") and $this->request->param("datemaxs")) {
                    $requestdate = $this->request->param("datemaxs");
                    $redate = date("Y-m-d", strtotime("$requestdate +1 day"));
                    $where["AddDate"] = array(array('egt', $this->request->param("datemins")), array('elt', $redate), 'and');
                    $map["datemin"] = $this->request->param("datemins");
                    $map["datemax"] = $this->request->param("datemaxs");
                } elseif ($this->request->param("datemins")) {
                    $where["AddDate"] = array('egt', $this->request->param("datemins"));
                    $map["datemin"] = $this->request->param("datemins");
                } elseif ($this->request->param("datemaxs")) {
                    $requestdate = $this->request->param("datemaxs");
                    $redate = date("Y-m-d", strtotime("$requestdate +1 day"));
                    $where["AddDate"] = array('elt', $redate);
                    $map["datemax"] = $this->request->param("datemaxs");
                }
                //根据日期进行查找 end
            }
            $where['TypeName'] = '返积分';
//            $p=$this->request->param('p')?$this->request->param('p'):1;
            $header = ['会员编号', '奖金总额', '已发放金额', '未发放金额'];
//            $data = Db::name("pointsflow")->where($where)->field("UserId,SUM(Amount) Amount,MAX(BalancePv) BalancePv ,SUM(Amount)-MAX(BalancePv)  notgrant")->group('UserId')->limit(($p-1)*20,20)->select();
            $data = Db::name("pointsflow")->where($where)->field("UserId,SUM(Amount) Amount,MAX(BalancePv) BalancePv ,SUM(Amount)-MAX(BalancePv)  notgrant")->group('UserId')->select();
            if ($error = \Excel::export($header, $data, "奖金汇总", '2007')) {
                throw new Exception($error);
            }
        } else {
            return $this->view->fetch('prizeSummary');
        }
    }

}
