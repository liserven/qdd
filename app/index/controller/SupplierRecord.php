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
class SupplierRecord extends Auth
{
    /**
     * 商家收券记录
     * 请求方式：http://www.XXX.com/app.php/supplier/supplierrecord
     * @param type 1 收券 3.转给商家 2.转给会员
     * @param page 页码
     */
    public function supplierrecord()
    {
        $info = $this->supplierislogin();
//      $info['data']['ID']=306;
//      $info['status'] = 0;
        if ($info['status'] == 0) {
            $type = $this->request->param('type');
            $page = $this->request->param('page');
            if ($page) {
                $page = $page;
            } else {
                $page = 1;
            }
            if ($type == 1) {
                $where['supplier_id'] = $info['data']['ID'];
                $where['type1'] = 1;
                $where['type'] = 2;
                $list = Db::view('supplier_record', 'coupon,time')
                    ->view('supplier', 'Name', 'supplier_record.supplier_id=supplier.ID')//查出中商家表的名称
                    ->where($where)//查出指定的字段
                    ->order('supplier_record.id desc')
                    ->limit(($page - 1) * 10, 10)
                    ->select();
                foreach ($list as $k=>$v){
                    $list[$k]['type']='充值';
                }

            } elseif ($type == 2) {
                $where['supplier_id_etid'] = $info['data']['ID'];
                $where['type'] = 3;
                $where['type1'] = 2;
                $list = Db::name('supplier_record')
                    ->field('coupon,time,user_mobile')//查出中商家表的名称
                    ->where($where)//查出指定的字段
                    ->order('id desc')
                    ->limit(($page - 1) * 10, 10)
                    ->select();
                foreach ($list as $k=>$v){
                    $list[$k]['type']='转账';
                }
            } elseif ($type == 3) {
                $where['supplier_id_etid'] = $info['data']['ID'];
                $where['type1'] = 2;
                $where['type'] = 2;
                $list = Db::view('supplier_record', 'coupon,time')
                    ->view('supplier', 'Name', 'supplier_record.supplier_id_add=supplier.ID')//查出中商家表的名称
                    ->where($where)//查出指定的字段
                    ->order('supplier_record.id desc')
                    ->limit(($page - 1) * 10, 10)
                    ->select();
                foreach ($list as $k=>$v){
                    $list[$k]['type']='转账';
                }

            }
            if (!empty($list)) {
                $returnData['data'] = $list;
                $returnData['status'] = 0;
                $returnData['msg'] = '获取数据成功';
            } else {
                $returnData['status'] = 0;
                $returnData['msg'] = '暂无数据';
            }
        } else {
            $returnData = $info;
        }
        return json($returnData);
    }
    /**
     * 商家转券记录详情
     * 请求方式：http://www.XXX.com/app.php/supplier/supplierrecorddeail
     * @param id  转券记录id
     */
    public function supplierrecorddeail(){
        $info = $this->supplierislogin();
//      $info['data']['ID']=306;
//      $info['status'] = 0;
        if ($info['status'] == 0) {
            $where['supplier_id'] = $info['data']['ID'];
            $where['ID']=$id=$this->request->param('id');
            $where['type']=2;
            $where['type1']=2;
            $list=Db::name('supplier_record')->field('ID,supplier_id_edit,coupon,coupon_total,time')->where($where)->find();
            if($list){
                $list['type']='转账';
                $returnData['data']=$list;
                $returnData['status']=0;
                $returnData['msg']='成功';
            }else{
                $returnData['status']=1;
                $returnData['msg']='非法操作';
            }

        }else{
            $returnData=$info;
        }
        return json($returnData);
    }


    /**
     * 商家申请记录
     * 请求方式：http://www.XXX.com/app.php/supplier/supplier_cash_list
     * @param   token
     * @param   page
     * @return \think\response\Json
     */

    public function supplier_cash_list(){
        $info = $this->supplierislogin();
//      $info['data']['ID']=306;
//      $info['status'] = 0;
        if ($info['status'] == 0) {
            $where['supplier_id'] = $info['data']['ID'];
            $page=$this->request->param('page');
            if($page){
                $page=$page;
            }else{
                $page=1;
            }
            $list = Db::name('supplier_cash')->where($where)->order('id desc')->field('id,cash,time,yes_cash,state')->limit(($page-1)*10,10)->select();
            $returnData['status']=0;
            $returnData['msg']='成功';
            $returnData['data']=$list;
        }else{
            $returnData=$info;
        }
        return json($returnData);
    }

    /**
     * 业务员销售记录
     * 请求方式：http://www.XXX.com/app.php/supplier/supplier_order
     * @param   token
     * @param   page
     * @return \think\response\Json
     */

    public function supplier_order(){
        $info = $this->supplierislogin();
//      $info['data']['ID']=306;
//      $info['status'] = 0;
        if ($info['status'] == 0) {
            $where['supplier_id'] = $info['data']['ID'];
            $where['state']=0;
            $page=$this->request->param('page');
            if($page){
                $page=$page;
            }else{
                $page=1;
            }
        $list = Db::name('supplier_cash_list')->where($where)->limit(($page-1)*10,10)->select();
        }else{
        $returnData=$info;
        }
        return json($returnData);

    }
}
