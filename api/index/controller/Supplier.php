<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/7/24
 * Time: 16:01
 */

namespace api\index\controller;
use think\Db;
use think\Session;
use think\Request;

class Supplier
{
    protected $request;

    public function __construct()
    {
        if (null === $this->request) {
            $this->request = Request::instance();
        }
    }
    /**
     * 商家列表接口
     * 请求方式：http://www.XXX.com/supplier/list
     * 请求方式：get
     * @return \think\response\Json
     */

    public function supplier_list()
    {
        //获取请求头
//        $headers=$this->request->header();
//        if(isset($headers['from']) && ($headers['from']=='android' || $headers['from']=='ios')) { //判断是否拥有访问接口的权限
//
//        }
        $pagesize = $this->request->param('pagesize');
        $where['ID']=['>','0'];

        //根据商家id进行查找
        if($this->request->param('sid')){
            $supid=$this->request->param('sid');
            $where['ID']=$supid;
            $map['sid']=$supid;
        }

        //根据商家名称进行查找
        if($this->request->param('sname')){
            $supname=$this->request->param('sname');
            $where['Name']=['like','%'.$supname.'%'];
            $map['sname']=$supname;
        }

        //根据商家分类id进行查找
        if($this->request->param('stype')){
            $stype=$this->request->param('stype');
            $where['SupplierType']=$stype;
            $map['stype']=$stype;
        }

        $pagecount = !empty($pagesize) ?: 20;

        $fields = 'ID,SupplierId,SupplierLogoForApp,SupplierBannerForApp,Name,Mobile,Remark,Province,City,Area,Address';
        $data = Db::name('supplier')->where($where)->field($fields)->order('id asc')->paginate($pagecount);

        $dataArr = [];
        foreach ($data as $key => $val) {
            if(isset($val['SupplierLogoForApp']) && !empty($val['SupplierLogo'])){
                $val['SupplierLogoForApp'] = config('IMAGE_DOMAIN_NAME') . $val['SupplierLogoForApp'];
            }else{
                $val['SupplierLogoForApp'] = config('IMAGE_DOMAIN_NAME') .'/public/Upload/supplier/supplierBanner/60.jpg';
            }
            if(isset($val['SupplierBannerForApp']) && !empty($val['SupplierBannerForApp'])){
                $val['SupplierBannerForApp'] = config('IMAGE_DOMAIN_NAME') . $val['SupplierBannerForApp'];
            }else{
                $val['SupplierBannerForApp'] = config('IMAGE_DOMAIN_NAME') .'/public/Upload/supplier/supplierBanner/640.jpg';
            }

            $dataArr[] = $val;

        }
//        print_r($dataArr);
//        exit();
        if (!empty($dataArr)) {

            $returnData["data"]['total'] = ceil($data->total() / $pagecount);//总页数
            $returnData['data']["data_list"] = $dataArr;

            $returnData['status'] = 1;
            $returnData['msg'] = '获取列表数据成功';
        } else {
            $returnData['data'] = '';
            $returnData['status'] = 0;
            $returnData['msg'] = '暂无数据';
        }
        return json($returnData);
    }

    /**
     * 商家详情接口
     * 请求方式：http://www.XXX.com/supplier/detail?sid=1
     * 请求方式：get
     * 请求参数：
     * @param   sid 商家id
     * @return \think\response\Json
     * $returnData['status'] :0=success;-1=fail
     */
    public function supplier_detail()
    {
        $sid = $this->request->param('sid');
        if (!empty($sid) && is_numeric($sid)) {
            $where['ID'] = $sid;
            $fields = '*';
            $data = Db::name('supplier')->where($where)->field($fields)->find();
            if ($data) {
                if (isset($data['SupplierLogoForApp']) && !empty($data['SupplierLogo'])) {
                    $data['SupplierLogoForApp'] = config('IMAGE_DOMAIN_NAME') . $data['SupplierLogoForApp'];
                } else {
                    $data['SupplierLogoForApp'] = config('IMAGE_DOMAIN_NAME') . '/public/Upload/supplier/supplierBanner/60.jpg';
                }
                if (isset($data['SupplierBannerForApp']) && !empty($data['SupplierBannerForApp'])) {
                    $data['SupplierBannerForApp'] = config('IMAGE_DOMAIN_NAME') . $data['SupplierBannerForApp'];
                } else {
                    $data['SupplierBannerForApp'] = config('IMAGE_DOMAIN_NAME') . '/public/Upload/supplier/supplierBanner/640.jpg';
                }

                if (isset($data['SupplierLogo']) && !empty($data['SupplierLogo'])) {
                    $data['SupplierLogo'] = config('IMAGE_DOMAIN_NAME') . $data['SupplierLogo'];
                } else {
                    $data['SupplierLogo'] = config('IMAGE_DOMAIN_NAME') . '/public/Upload/supplier/supplierBanner/80.jpg';
                }
                if (isset($data['SupplierBannerForPc']) && !empty($data['SupplierBannerForPc'])) {
                    $data['SupplierBannerForPc'] = config('IMAGE_DOMAIN_NAME') . $data['SupplierBannerForPc'];
                } else {
                    $data['SupplierBannerForPc'] = config('IMAGE_DOMAIN_NAME') . '/public/Upload/supplier/supplierBanner/1000.jpg';
                }

                $data['SupplierType']=getTypeNameByTypeId($data['SupplierType']);

                $returnData['data'] = $data;//数据详情
                $returnData['status'] = 1;
                $returnData['msg'] = '获取数据成功!';
            } else {
                $returnData['data'] = '';
                $returnData['status'] = 0;
                $returnData['msg'] = '商家不存在!';
            }
        } else {
            $returnData = ['data' => '', 'status' => 0, 'mes' => '参数格式错误，必须为非空数字!'];
        }
        return json($returnData);
    }

}