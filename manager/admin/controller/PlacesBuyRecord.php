<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/2/27
 * Time: 10:47
 */

namespace app\admin\controller;

\think\Loader::import('controller/Jump', TRAIT_PATH, EXT);

use app\admin\Controller;
use think\Db;

class PlacesBuyRecord  extends Controller
{
    use \traits\controller\Jump;

    public  function index(){
        return $this->recordList();
    }

    public function recordList(){
        $where['query_condition']='1=1';
        $map=[];
        if($this->request->param()){
            //根据会员ID查询
            if($this->request->param('keyuserid')){
                $keyuserid=$this->request->param('keyuserid');
                $map['keyuserid']=$keyuserid;
                $where['UserId']=$keyuserid;
            }
            // 根据订单编号
            if($this->request->param('orderid')){
                $orderid=$this->request->param('orderid');
                $map['orderid']=$orderid;
                $where['orderid']=$orderid;
            }
            //根据交易类型查询
            if($this->request->param('recordType')){
                $recordType=$this->request->param('recordType');
                $map['recordType']=$recordType;
                $where['FlowType']=['like','%'.$recordType.'%'];
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

        if(count($where)==1){
            unset($where['query_condition']);
            $where='1=1';
        }else{
            unset($where['query_condition']);
        }
        $daochu=$this->request->param('daochu');
        if(isset($daochu)&&$daochu=='daochu'){
            set_time_limit(0);
            ini_set('memory_limit', '128M');
            $filename='购物记录';
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="'.$filename.'.csv"');
            header('Cache-Control: max-age=0');
            // 打开PHP文件句柄，php://output 表示直接输出到浏览器
            $fp = fopen('php://output', 'a');
            //我们试着用fputcsv从数据库中导出1百万的数据
            //我们每次取1万条数据，分100步来执行
            //如果线上环境无法支持一次性读取1万条数据，可把$nums调小，$step相应增大。
            $step=Db::name('accountrecord')->where($where)->count();
            if ($step > 10000)
            {
                $nums = 1000;

                $totalcount=ceil($step/1000);
            }else
            {
                $nums = 100;

                $totalcount=ceil($step/100);
            }
            $head = array('会员编号', '奖金类型', '交易金额','账户余额', '金额来源','备注信息','时间');
            foreach ($head as $i => $v) {
                // CSV的Excel支持GBK编码，一定要转换，否则乱码
                $head[$i] = iconv('utf-8', 'gb2312', $v);
            }
            //将标题写到标准输出中
            fputcsv($fp, $head);
            for($s=1;$s<=$totalcount;$s++)
            {

                $start = ($s - 1) * $nums;
                $modellist=Db::name('accountrecord')
                    ->field('UserId,FlowType,Amount,Balance,FromWho,Memo,AddDate')
                    ->where($where)->order('ID desc')->limit($start,$nums)->select();
                if (!empty($modellist))
                {
                    foreach($modellist as $k=>$v){
                        $modellist[$k]['UserId']='‘'.$v['UserId'].'‘';
                        $modellist[$k]['FromWho']='‘'.$v['FromWho'].'‘';
                    }
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
        }


        $count=Db::name('order_places')->where($where)->order('ID desc')->count();//获取满足条件的总记录数
        $list=Db::name('order_places')->where($where)->order('ID desc')->paginate(15);//根据条件分页输出

        
        $this->view->assign('recordlist',$list);
        $this->view->assign('page',$list->render());//输出分页的样式
        $this->view->assign('count',$count);
       

//        print_r($staticArr);
//        exit();
        return $this->view->fetch('recordlist');
    }


}
