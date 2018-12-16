<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/7/7
 * Time: 20:00
 */

namespace app\admin\controller;

use app\admin\Controller;
use think\Db;

class SupplierSum extends Controller
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
            //根据供应商ID查询
            if ($this->request->param('SupplierId')) {
                $keyProName = $this->request->param('SupplierId');
                $map['SupplierId'] = $keyProName;
                $where['SupplierId'] = $keyProName;
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
        if($this->request->param("daochu") && $this->request->param("daochu")=="daochu"){
            set_time_limit(0);// 不限制脚本执行时间以确保导出完成
            ini_set('memory_limit', '128M');
            $filename='供应商统计';
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="'.$filename.'.csv"');
            header('Cache-Control: max-age=0');
            // 打开PHP文件句柄，php://output 表示直接输出到浏览器
            $fp = fopen('php://output', 'a');

            //我们试着用fputcsv从数据库中导出1百万的数据
            //我们每次取1万条数据，分100步来执行
            //如果线上环境无法支持一次性读取1万条数据，可把$nums调小，$step相应增大。
            $step=Db::name('orderdetail')->field('count(SupplierId)')->where($where)->group('SupplierId')->select();
            $step=count($step);
            if ($step > 10000)
            {
                $nums = 1000;

                $totalcount=ceil($step/1000);
            }else
            {
                $nums = 100;

                $totalcount=ceil($step/100);
            }
            //设置标题
            $head = ['供应商ID', '销售数量', '产品成本','销售总金额','产品毛利'];
            foreach ($head as $i => $v) {
                // CSV的Excel支持GBK编码，一定要转换，否则乱码
                $head[$i] = iconv('utf-8', 'gb2312', $v);
            }
            //将标题写到标准输出中
            fputcsv($fp, $head);

            for($s=1;$s<=$totalcount;$s++)
            {
                $start = ($s - 1) * $nums;
                $list = Db::name('orderdetail')->where($where)->field('SupplierId,sum(proNum) proNum')->group('SupplierId')->select();//根据条件分页输出
                foreach($list as $kk=>$vv){
                    $data[$kk]['SupplierId']=$vv['SupplierId'];
                    $data[$kk]['proNum']=$vv['proNum'];
                    $data[$kk]['BalancePriceSum']='';
                    $data[$kk]['PriceSum']='';
                    $data[$kk]['productprofit']='';
                    $sumdatas=Db::name('orderdetail')->where('SupplierId="'.$vv['SupplierId'].'"')->field('BalancePrice,Price,ConsumeIntegral,sum(proNum)*BalancePrice  BalancePriceSum, sum(proNum)*ConsumeIntegral  IntegralSum,
 sum(proNum)*Price  PriceSum,sum(proNum)*Price-sum(proNum)*BalancePrice  productprofit')->group('ProId')->select();
                    foreach($sumdatas as $key=>$vo){
                        $data[$kk]['BalancePriceSum']+=$vo['BalancePriceSum'];//产品成本
                        $data[$kk]['PriceSum']+=$vo['PriceSum'];//销售金额
                        $data[$kk]['productprofit']+=$vo['productprofit'];//产品毛利

                    }

                }
                $modellist=$data;
                if (!empty($modellist))
                {
                    //这里必须转码，不然会乱码
                    for ($i=0;$i<count($modellist);$i++)
                    {
                        $row=$modellist[$i];
                        foreach($row as $key => $item) {
                            //这里必须转码，不然会乱码
                            $row[$key] = iconv('UTF-8', 'GBK', $item);
                        }
                        fputcsv($fp, $row);
                    }
                    //每1万条数据就刷新缓冲区
                    ob_flush();
                    flush();
                }
            }
            exit();
        }else{
        $list = Db::name('orderdetail')->where($where)->field('SupplierId,sum(proNum) proNum')->group('SupplierId')->paginategroupby(20);//根据条件分页输出
        $data=$list->all();
        foreach($data as $kk=>$vv){
            $data[$kk]['Price']='';
            $data[$kk]['ConsumeIntegral']='';
            $data[$kk]['BalancePriceSum']='';
            $data[$kk]['PriceSum']='';
            $data[$kk]['productprofit']='';
            $data[$kk]['IntegralSum']='';
            $data[$kk]['BalancePrice']='';
            $sumdatas=Db::name('orderdetail')->where('SupplierId="'.$vv['SupplierId'].'"')->field('BalancePrice,Price,ConsumeIntegral,sum(proNum)*BalancePrice  BalancePriceSum, sum(proNum)*ConsumeIntegral  IntegralSum,
 sum(proNum)*Price  PriceSum,sum(proNum)*Price-sum(proNum)*BalancePrice  productprofit')->group('ProId')->select();
            foreach($sumdatas as $key=>$vo){
            $data[$kk]['Price']+=$vo['Price'];//销售单价
            $data[$kk]['BalancePrice']+=$vo['BalancePrice'];//成本单价
            $data[$kk]['ConsumeIntegral']+=$vo['ConsumeIntegral'];//销售单价积分
            $data[$kk]['BalancePriceSum']+=$vo['BalancePriceSum'];//产品成本
            $data[$kk]['PriceSum']+=$vo['PriceSum'];//销售金额
            $data[$kk]['productprofit']+=$vo['productprofit'];//产品毛利
            $data[$kk]['IntegralSum']+=$vo['IntegralSum'];//销售积分
            }

        }
        $sumData = Db::name('orderdetail')->where($where)->field('SupplierId,sum(proNum) proNum')->group('SupplierId')->select();
        foreach($sumData as $kkk=>$vvv){
            $sumData[$kkk]['BalancePriceSum']='';
            $sumData[$kkk]['PriceSum']='';
            $sumData[$kkk]['productprofit']='';
            $sumData[$kkk]['IntegralSum']='';
            $totle=Db::name('orderdetail')->where('SupplierId="'.$vvv['SupplierId'].'"')->field('sum(proNum)*BalancePrice  BalancePriceSum, sum(proNum)*ConsumeIntegral  IntegralSum,
 sum(proNum)*Price  PriceSum,sum(proNum)*Price-sum(proNum)*BalancePrice  productprofit')->group('ProId')->select();

            foreach($totle as $keyy=>$voo){
                $sumData[$kkk]['BalancePriceSum']+=$voo['BalancePriceSum'];//产品成本
                $sumData[$kkk]['PriceSum']+=$voo['PriceSum'];//销售金额
                $sumData[$kkk]['productprofit']+=$voo['productprofit'];//产品毛利
                $sumData[$kkk]['IntegralSum']+=$voo['IntegralSum'];//销售积分
            }
        }
        $count=Db::name('orderdetail')->where($where)->group('SupplierId')->countgroupby();
        $staticArr = [ ['TypeName' => '', 'proNum' => 0], ['TypeName' => '', 'BalancePrice' => 0], ['TypeName' => '', 'BalancePriceSum' => 0],
            ['TypeName' => '', 'PriceSum' => 0], ['TypeName' => '', 'IntegralSum' => 0], ['TypeName' => '', 'productprofit' => 0]];
        if(!empty($sumData)){
            foreach ($sumData as $k=>$v){
                $staticArr[0]['proNum']+=$v['proNum'];

                $staticArr[2]['BalancePriceSum']+=$v['BalancePriceSum'];
                $staticArr[3]['PriceSum']+=$v['PriceSum'];
                $staticArr[4]['IntegralSum']+=$v['IntegralSum'];
                $staticArr[5]['productprofit']+=$v['productprofit'];
            }
        }
        if(!empty($map)) {
            foreach ($map as $key => $value) {
                $list->appends($key, $value);//分页链接中添加请求的参数
            }
        }
        $this->view->assign('ProductSumList', $data);
        $this->view->assign('page', $list->render());//输出分页的样式
        $this->view->assign('count', $count);
        $this->view->assign('staticArr', $staticArr);
        }
        return $this->view->fetch('suppliersum');
    }
    //数据导出奖金
    public  function  ProductSum_outexcel()
    {
        $where['query_condition'] = '1=1';
        if ($this->request->isPost()) {

            $map = [];
            if ($this->request->param()) {
                //根据供应商ID查询
                if ($this->request->param('SupplierIds')) {
                    $keyProNames = $this->request->param('SupplierIds');
                    $map['SupplierId'] = $keyProNames;
                    $where['SupplierId'] = $keyProNames;
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
            $header = ['供应商ID', '销售数量', '成本单价', '销售单价','产品成本总价','销售总金额','销售总积分','产品毛利'];
            $data = Db::name("orderdetail")->where($where)->field("SupplierId,sum(proNum) proNum,BalancePrice,Price,sum(proNum)*BalancePrice BalancePriceSum,sum(proNum)*Price PriceSum,sum(proNum)*ConsumeIntegral IntegralSum,sum(proNum)*Price-sum(proNum)*BalancePrice  productprofit")->group('SupplierId')->select();
            if ($error = \Excel::export($header, $data, "统计产品利润", '2007')) {
                throw new Exception($error);
            }
        } else {
            return $this->view->fetch('SupplierSum');
        }
    }
}

