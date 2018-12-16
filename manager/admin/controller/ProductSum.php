<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/7/7
 * Time: 16:06
 */

namespace app\admin\controller;

use app\admin\Controller;
use think\Db;

class ProductSum  extends Controller
{
    use \traits\controller\Jump;

    public  function index(){

        return $this->ProductSumList();
    }
    public function ProductSumList()
    {

        $where['query_condition'] = '1=1';
        $map = [];


        if ($this->request->param()) {
            //根据商家ID查询
            if ($this->request->param('SupplierId')) {
                $keySupplierId = $this->request->param('SupplierId');
                $map['SupplierId'] = $keySupplierId;
                $where['orderdetail.SupplierId'] = $keySupplierId;
            }
            //根据产品名称查询
            if ($this->request->param('ProName')) {
                $keyProName = $this->request->param('ProName');
                $map['ProName'] = $keyProName;
                $where['orderdetail.ProName'] = ['like','%'.$keyProName.'%'];
            }
            //根据日期进行查找 start
            if ($this->request->param("datemin") and $this->request->param("datemax")) {
                $requestdate = $this->request->param("datemax");
                $redate = date("Y-m-d", strtotime("$requestdate +1 day"));
                $where["orderdetail.AddDate"] = array(array('egt', $this->request->param("datemin")), array('elt', $redate), 'and');
                $map["datemin"] = $this->request->param("datemin");
                $map["datemax"] = $this->request->param("datemax");
            } elseif ($this->request->param("datemin")) {
                $where["orderdetail.AddDate"] = array('egt', $this->request->param("datemin"));
                $map["datemin"] = $this->request->param("datemin");
            } elseif ($this->request->param("datemax")) {
                $requestdate = $this->request->param("datemax");
                $redate = date("Y-m-d", strtotime("$requestdate +1 day"));
                $where["orderdetail.AddDate"] = array('elt', $redate);
                $map["datemax"] = $this->request->param("datemax");
            }
            //根据日期进行查找 end
        }

        if (count($where) == 1) {
            unset($where['query_condition']);
            $where = '1=1';
        } else {
            unset($where['query_condition']);
        }

//        $sumData = Db::name('orderdetail')->where($where)->field('UserId,ProName,sum(proNum) proNum,BalancePrice,Price,ConsumeIntegral,sum(proNum)*BalancePrice  BalancePriceSum, sum(proNum)*ConsumeIntegral  IntegralSum,
// sum(proNum)*Price  PriceSum,sum(proNum)*Price-sum(proNum)*BalancePrice  productprofit')->group('ProId')->select();
//        $count=Db::name('orderdetail')->where($where)->group('ProId')->countgroupby();
//        $list = Db::name('orderdetail')->where($where)->field('UserId,ProName,sum(proNum) proNum,BalancePrice,Price,ConsumeIntegral,sum(proNum)*BalancePrice  BalancePriceSum, sum(proNum)*ConsumeIntegral  IntegralSum,
// sum(proNum)*Price  PriceSum,sum(proNum)*Price-sum(proNum)*BalancePrice  productprofit')->group('ProId')->paginategroupby(20);//根据条件分页输出

        $sumData = Db::table('orderdetail')
            ->join('ordermain','ordermain.InnerOrderId=orderdetail.InnerOrderId')
            ->field('orderdetail.UserId,orderdetail.ProName,sum(orderdetail.proNum) proNum,orderdetail.BalancePrice,orderdetail.Price,orderdetail.ConsumeIntegral')
            ->where($where)
            ->where('ordermain.status >1')
            ->group('orderdetail.ProId')
            ->select();
        $count=Db::table('orderdetail')
            ->join('ordermain','ordermain.InnerOrderId=orderdetail.InnerOrderId')
            ->where($where)
            ->where('ordermain.status >1')
            ->group('orderdetail.ProId')->countgroupby();
        $list = Db::table('orderdetail')
            ->join('ordermain','ordermain.InnerOrderId=orderdetail.InnerOrderId')
            ->field('orderdetail.UserId,orderdetail.ProName,sum(orderdetail.proNum) proNum,orderdetail.BalancePrice,orderdetail.Price,orderdetail.ConsumeIntegral')
            ->where($where)
            ->where('ordermain.status >1')
            ->group('orderdetail.ProId')->paginategroupby(20);//根据条件分页输出

        $staticArr = [ ['TypeName' => '', 'proNum' => 0], ['TypeName' => '', 'BalancePrice' => 0], ['TypeName' => '', 'BalancePriceSum' => 0],
             ['TypeName' => '', 'PriceSum' => 0], ['TypeName' => '', 'IntegralSum' => 0], ['TypeName' => '', 'productprofit' => 0]];
//        var_dump($sumData);exit;
        if(!empty($sumData)){
            foreach ($sumData as $k=>$v){
                $staticArr[0]['proNum']+=$v['proNum'];
                $staticArr[1]['BalancePrice']+=$v['BalancePrice'];
                $staticArr[2]['BalancePriceSum']+=$v['BalancePrice']*$v['proNum'];
                $staticArr[3]['PriceSum']+=$v['Price']*$v['proNum'];
//                $staticArr[4]['IntegralSum']+=$v['IntegralSum'];
                $staticArr[5]['productprofit']+=($v['Price']-$v['BalancePrice'])*$v['proNum'];
            }
        }


        if(!empty($map)) {
            foreach ($map as $key => $value) {
                $list->appends($key, $value);//分页链接中添加请求的参数
            }
        }
        $this->view->assign('ProductSumList', $list);
        $this->view->assign('page', $list->render());//输出分页的样式
        $this->view->assign('count', $count);
        $this->view->assign('staticArr', $staticArr);
        return $this->view->fetch('productsum');
    }
    //数据导出奖金
    public  function  ProductSum_outexcel()
    {
        $where['query_condition'] = '1=1';
        if ($this->request->isPost()) {

            $map = [];
            if ($this->request->param()) {

                //根据商家ID查询
                if ($this->request->param('SupplierId')) {
                    $keyProName = $this->request->param('SupplierId');
                    $map['SupplierId'] = $keyProName;
                    $where['SupplierId'] = $keyProName;
                }
                //根据产品名称查询
                if ($this->request->param('ProNames')) {
                    $keyProNames = $this->request->param('ProNames');
                    $map['ProNames'] = $keyProNames;
                    $where['ProName'] = ['like','%'.$keyProNames.'%'];;
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
            if (count($where) == 1) {
                unset($where['query_condition']);
                $where = '1=1';
            } else {
                unset($where['query_condition']);
            }
            $header = ['产品名称', '销售数量', '成本单价', '销售单价','产品成本总价','销售总金额','销售总积分','产品毛利'];
            $data = Db::name("orderdetail")->where($where)->field("ProName,sum(proNum) proNum,BalancePrice,Price,sum(proNum)*BalancePrice BalancePriceSum,sum(proNum)*Price PriceSum,sum(proNum)*ConsumeIntegral IntegralSum,sum(proNum)*Price-sum(proNum)*BalancePrice  productprofit")->group('ProId')->select();
            if ($error = \Excel::export($header, $data, "统计产品利润", '2007')) {
                throw new Exception($error);
            }
        } else {
            return $this->view->fetch('ProductSum');
        }
    }
}
