<?php
/**
 * Created by 赵晓凡
 * User: zhaoxiaofan
 * Date: 2017/2/17
 * Time: 9:30
 */
namespace api\index\controller;

use think\Db;
use think\Request;
use think\session;
use think\Config;
/**
 * Class Index
 * @package home\index\controller
 * @return 首页页面
 */
class Index
{
    protected $request;
    public function __construct()
    {
        if (null === $this->request) {
            $this->request = Request::instance();
        }
    }
    public function poster(){
        $adlist=indexToLower(Db::name('adlist')->where('CategoryId=3')->order('ID')->select());
        $returnData['data']=$adlist;
        $returnData['status']=1;
        $returnData['msg']='成功';
        return json($returnData);
    }

    // 首页快速入口
    public function indexnav()
    {
        $returnArr['data'] = $this->indexnav_list();
        $returnArr['status'] = 1;
        $returnArr['msg'] = '数据记录';
        return json($returnArr);
    }

    public function news(){
        /*新闻*/
        $list =Db::name('articlelist')->field('id,articletitle,addtime')->order("id desc")->limit(0,4)->select();
        foreach ($list as $k=>$v){
            $list[$k]['title']= mb_substr($v['articletitle'],0,18,'utf-8');

        }
        $returnArr['data'] = $list;
        $returnArr['status'] = 1;
        $returnArr['msg'] = '数据记录';
        return json($returnArr);
    }
//    获取资讯列表
    public function article_list(){
        /*新闻*/
        $pagecount = 10;
        $pageTotal=0;
        $list =Db::name('articlelist')->field('id,articletitle,addtime,categoryid')->order("id desc")->paginate($pagecount);
        $data=[];
        if($list) {
            $pageTotal = ceil($list->total()/$pagecount);//总页数
            foreach ($list as $k => $v) {
                $data[$k]=array_change_key_case($v);
                $data[$k]['title'] = mb_substr($v['articletitle'], 0, 18, 'utf-8');
                $condtion['id'] = $v['categoryid'];
                $data[$k]['name'] = Db::name('articlecategory')->where($condtion)->field('categoryname')->find();
            }
        }
//        缺少else判断，后期可以补足

//        $returnArr['data'] = $data;
//        $returnArr['total']=$pageTotal;

        $returnArr['data']["data_list"] = $data;
        $returnArr["data"]['total']=$pageTotal;

        $returnArr['status'] = 1;
        $returnArr['msg'] = '数据记录';
        return json($returnArr);
    }
//    资讯详情
    public function article_show()
    {
        /*新闻详情*/
        $articlefind=Db::name("articlelist")->where("id=".$this->request->param("id"))->find();

        $returnArr['data'] = $articlefind;
        $returnArr['status'] = 1;
        $returnArr['msg'] = '数据记录';
        return json($returnArr);
    }

    public function all(){

        //获取请求头
        $headers=$this->request->header();
        $returnArr=array();
//        if(isset($headers['outid']) && checkOutId($headers['outid'])==='true') { //判断是否拥有访问接口的权限
            //首页轮播图
            $adlist= \think\Db::view('adcategory','ID')
                ->view(' adlist', '*', 'adcategory.ID=adlist.CategoryId')
                ->where('adcategory.CategoryName="Web端首页轮播广告"')
                ->select();
            $adlist=indexToLower($adlist);
            //$adlist = indexToLower(Db::name('adlist')->where('CategoryId=3')->field('adlinkurl,adpicture')->order('ID')->select());
            if ($adlist) {
                foreach ($adlist as $key => $val) {
                    $adlist[$key]['adpicture'] = config('IMAGE_DOMAIN_NAME'). $val['adpicture'];//图片地址
                }
            }
            $adshishang= \think\Db::view('adcategory','ID')
            ->view(' adlist', '*', 'adcategory.ID=adlist.CategoryId')
            ->where('adcategory.ID=13')
            ->select();

            $adshishang=indexToLower($adshishang);
//            var_dump(111);exit;
        if ($adshishang) {
            foreach ($adshishang as $key => $val) {
                $adshishang[$key]['adpicture'] = config('IMAGE_DOMAIN_NAME'). $val['adpicture'];//图片地址
            }
        }
        $adshangxin= \think\Db::view('adcategory','ID')
            ->view(' adlist', '*', 'adcategory.ID=adlist.CategoryId')
            ->where('adcategory.CategoryName="Web端首页每日上新"')
            ->select();

        $adshangxin=indexToLower($adshangxin);
        if ($adshangxin) {
            foreach ($adshangxin as $key => $val) {
                $adshangxin[$key]['adpicture'] = config('IMAGE_DOMAIN_NAME'). $val['adpicture'];//图片地址
            }
        }
        $addashang= \think\Db::view('adcategory','ID')
            ->view(' adlist', '*', 'adcategory.ID=adlist.CategoryId')
            ->where('adcategory.CategoryName="Web端首页新选大赏"')
            ->select();

        $addashang=indexToLower($addashang);
        if ($addashang) {
            foreach ($addashang as $key => $val) {
                $addashang[$key]['adpicture'] = config('IMAGE_DOMAIN_NAME'). $val['adpicture'];//图片地址
            }
        }
        $adquanqiu= \think\Db::view('adcategory','ID')
            ->view(' adlist', '*', 'adcategory.ID=adlist.CategoryId')
            ->where('adcategory.CategoryName="Web端首页全球时尚"')
            ->select();

        $adquanqiu=indexToLower($adquanqiu);
        if ($adquanqiu) {
            foreach ($adquanqiu as $key => $val) {
                $adquanqiu[$key]['adpicture'] = config('IMAGE_DOMAIN_NAME'). $val['adpicture'];//图片地址
            }
        }
        $addapai= \think\Db::view('adcategory','ID')
            ->view(' adlist', '*', 'adcategory.ID=adlist.CategoryId')
            ->where('adcategory.CategoryName="Web端首页非常大牌"')
            ->select();

        $addapai=indexToLower($addapai);
        if ($addapai) {
            foreach ($addapai as $key => $val) {
                $addapai[$key]['adpicture'] = config('IMAGE_DOMAIN_NAME'). $val['adpicture'];//图片地址
            }
        }
        $adyouxuan= \think\Db::view('adcategory','ID')
            ->view(' adlist', '*', 'adcategory.ID=adlist.CategoryId')
            ->where('adcategory.CategoryName="Web端首页每日优选"')
            ->select();

        $adyouxuan=indexToLower($adyouxuan);
        if ($adyouxuan) {
            foreach ($adyouxuan as $key => $val) {
                $adyouxuan[$key]['adpicture'] = config('IMAGE_DOMAIN_NAME'). $val['adpicture'];//图片地址
            }
        }
        $addongtai= \think\Db::view('adcategory','ID')
            ->view(' adlist', '*', 'adcategory.ID=adlist.CategoryId')
            ->where('adcategory.CategoryName="Web端首页全球品牌动态"')
            ->select();

        $addongtai=indexToLower($addongtai);
        if ($addongtai) {
            foreach ($addongtai as $key => $val) {
                $addongtai[$key]['adpicture'] = config('IMAGE_DOMAIN_NAME'). $val['adpicture'];//图片地址
            }
        }
        $adxianchang= \think\Db::view('adcategory','ID')
            ->view(' adlist', '*', 'adcategory.ID=adlist.CategoryId')
            ->where('adcategory.CategoryName="Web端首页海外买手现场"')
            ->select();

        $adxianchang=indexToLower($adxianchang);
        if ($adxianchang) {
            foreach ($adxianchang as $key => $val) {
                $adxianchang[$key]['adpicture'] = config('IMAGE_DOMAIN_NAME'). $val['adpicture'];//图片地址
            }
        }
        $adxinde= \think\Db::view('adcategory','ID')
            ->view(' adlist', '*', 'adcategory.ID=adlist.CategoryId')
            ->where('adcategory.CategoryName="Web端首页淘淘心得"')
            ->select();

        $adxinde=indexToLower($adxinde);
        if ($adxinde) {
            foreach ($adxinde as $key => $val) {
                $adxinde[$key]['adpicture'] = config('IMAGE_DOMAIN_NAME'). $val['adpicture'];//图片地址
            }
        }
        $adrexiao= \think\Db::view('adcategory','ID')
            ->view(' adlist', '*', 'adcategory.ID=adlist.CategoryId')
            ->where('adcategory.CategoryName="Web端首页全球热销"')
            ->select();

        $adrexiao=indexToLower($adrexiao);
        if ($adrexiao) {
            foreach ($adrexiao as $key => $val) {
                $adrexiao[$key]['adpicture'] = config('IMAGE_DOMAIN_NAME'). $val['adpicture'];//图片地址
            }
        }
         $lianjie= \think\Db::name('adlist')
           // ->view(' adlist', '*', 'adcategory.ID=adlist.CategoryId')
            ->where('CategoryId=26')
            ->find();


        if ($lianjie) {
            $lianjie=array_change_key_case($lianjie);
          $lianjie['adpicture']=config('IMAGE_DOMAIN_NAME'). $lianjie['adpicture'];//图片地址
        }else{
            $lianjie=[];
        }

        $guohe= \think\Db::name('adlist')
           // ->view(' adlist', '*', 'adcategory.ID=adlist.CategoryId')
            ->where('CategoryId=27')
            ->find();

        if ($guohe) {
            $guohe=array_change_key_case($guohe);
          $guohe['adpicture']=config('IMAGE_DOMAIN_NAME'). $guohe['adpicture'];//图片地址
        }else{
            $guohe=[];
        }

       $returnArr['data']['guohe'] = $guohe;//首页时尚搭配广告
      $returnArr['data']['lianjie'] = $lianjie;//首页时尚搭配广告
        $returnArr['data']['rexiao'] = $adrexiao;//首页时尚搭配广告
        $returnArr['data']['youxuan'] = $adyouxuan;//首页时尚搭配广告
        $returnArr['data']['dongtai'] = $addongtai;//首页时尚搭配广告
        $returnArr['data']['xianchang'] = $adxianchang;//首页时尚搭配广告
        $returnArr['data']['xinde'] = $adxinde;//首页时尚搭配广告
        $returnArr['data']['dapai'] = $addapai;//首页时尚搭配广告
        $returnArr['data']['quanqiu'] = $adquanqiu;//首页时尚搭配广告
        $returnArr['data']['dashang'] = $addashang;//首页时尚搭配广告
        $returnArr['data']['shangxin'] = $adshangxin;//首页时尚搭配广告
        $returnArr['data']['shishang'] = $adshishang;//首页时尚搭配广告
        $returnArr['data']['banner'] = $adlist;//详情页图片合集轮播
        $returnArr['data']['indexnav'] = $this->indexnav_list();//快速入口
        $returnArr['data']['recommend'] = $this->recommend_list();//首页展示商品
        $returnArr['status'] = 1;
        $returnArr['msg'] = '数据记录';
//        }else{
//            $returnArr['data']=$returnArr;
//            $returnArr['status'] = 0;
//            $returnArr['msg'] = '禁止访问!';
//        }
        return json($returnArr);
    }


    public function indexnav_list(){
        defined('IMAGE_DOMAIN_NAME') or define('IMAGE_DOMAIN_NAME',    config('IMAGE_DOMAIN_NAME')); // 定义图片服务器域名常量;
//        $list[] = array(
//            'name' => '全部商品',
//            'picclass' => 'px1',//应用的样式
//            'picurl'=>'/mobile.php/list?ishit=1',
//            'picpath'=>config('IMAGE_DOMAIN_NAME').'/public/Public/mobile/images/px1.png',
//        );
        $list[] = array(
            'name' => '分类',
            'picclass' => 'px2',
            'picurl'=>'/mobile.php/category',
            'picpath'=>config('IMAGE_DOMAIN_NAME').'/public/Public/mobile/images/px2.png',
        );
//        $list[] = array(
//            'name' => '注册区',
//            'picclass' => 'px3',
//            'picurl'=>'/mobile.php/list?ishit=2',
//            'picpath'=>config('IMAGE_DOMAIN_NAME').'/public/Public/mobile/images/px3.png',
//        );
//        $list[] = array(
//            'name' => '重消区',
//            'picclass' => 'px4',
//            'picurl'=>'/mobile.php/list?ishit=3',
//            'picpath'=>config('IMAGE_DOMAIN_NAME').'/public/Public/mobile/images/px4.png',
//        );
        $list[] = array(
            'name' => '商城',
            'picclass' => 'px1',
            'picurl'=>'/mobile.php/list?ishit=4',
            'picpath'=>config('IMAGE_DOMAIN_NAME').'/public/Public/mobile/images/px1.png',
        );
        return $list;
    }

    public function recommend_list(){
        $where['isonsell']=1;
        $where['isindex']=1;
        $goods=indexToLower(Db::name('product')->where($where)->field('qiqiupromaximgpath,qiqiuproimgpath,proid,proimg,proname,vipprice,maxproimg,prosum')->order("proid desc")->limit(0,6)->select());
        if($goods){
            foreach ($goods as $key=>$val){

                if(is_onqiniu()==true){

                    $goods[$key]['img']=$val['qiqiuproimgpath'];
                    $goods[$key]['maximg']=$val['qiqiupromaximgpath'];
                }else{
                    if(strpos($val['proimg'],'http://')!==false){
                        $goods[$key]['img']=$val['proimg'];
                        $goods[$key]['maximg']=$val['maxproimg'];
                    }else{
                        $goods[$key]['img']=config('IMAGE_DOMAIN_NAME').'/public/Upload/cpimg/'.$val['proimg'];
                        $goods[$key]['maximg']=config('IMAGE_DOMAIN_NAME').'/public/Upload/cpimg/'.$val['maxproimg'];
                    }
//                    $goods[$key]['img']=$val['proimg'];//图片地址
//                    $goods[$key]['img']=config('IMAGE_DOMAIN_NAME').'/public'.'/Upload/cpimg/'.$val['proimg'];//图片地址
//                    $goods[$key]['maximg']=config('IMAGE_DOMAIN_NAME').'/public'.'/Upload/cpimg/'.$val['maxproimg'];//图片地址
//                    $goods[$key]['maximg']=$val['maxproimg'];//图片地址
                }
                $goods[$key]['vipprice'] ="￥".$goods[$key]['vipprice'];
            }
        }
        return $goods;
    }

    public function recommend_goods(){
        $pagecount = 6;
        $pageTotal=0;
        $data=[];
        $hitgoods=Db::name('product')->field('ProId,MarketPrice,EnjoyPrice,VipPrice,ProName,ProImg')->where('IsOnSell=1 and IsHit=1')->order('sort asc,prosum desc,ProId desc')->paginate($pagecount);
        if($hitgoods){
            $pageTotal = ceil($hitgoods->total()/$pagecount);//总页数
            foreach ($hitgoods as $key=>$val){
                if(strpos($val['ProImg'],'http://')!==false){
                    $val['ProImg']=$val['ProImg'];
                }else{
                    $val['ProImg']=config('IMAGE_DOMAIN_NAME').'/public/Upload/cpimg/'.$val['ProImg'];
                }
                $data[]=array_change_key_case($val);
            }
        }
        $returnData["data"]['indexlist']=$data;
        $returnData["data"]['total']=$pageTotal;
        $returnData['status']=1;
        $returnData['msg']='获取数据成功';
        return json($returnData);
    }

    public function getaddress(){
        $userid=$this->request->param('name');
        $where['UserId']=$userid;
        $where['OrderType']=1;
        $where['Status']=['>',1];
        $list=Db::name('ordermain')->field('Province,City,County,Address,UserTel,ReceiveName')->where($where)->find();
        if($list){
            $returndata['data']=$list;
            $returndata['status']=1;
            $returndata['msg']='成功';
        }else{
            $returndata['status']=0;
            $returndata['msg']='失败';
        }
        return json($returndata);
    }
//同步商品
    public function getproduct(){
        $page=Db::name('page')->field('page')->where('id=1')->find();
        if($page['page']>=10000){
            die();
        }else{
            $url="http://api.999000.cn/api/third/goods/?act=zhonghonggoods&page=".$page['page'];

            $data=file_get_contents($url);
            $data=json_decode($data,true);
            //return json($data);

            if($data['data']['count']>0){
                foreach ($data['data']['list'] as $k=>$v){
//                    return json($v['description']);
                    $product=[];
                    $product['goods_id']=$v['goods_id'];
                    $product['ProName']=$v['goods_name'];
                    $product['ProImg']=$v['default_image'];
                    $product['MaxProImg']=$v['default_image'];
                    $product['VipPrice']=$v['voucher_price'];
                    $product['MarketPrice']=$v['voucher_price'];
                    $product['EnjoyPrice']=$v['voucher_price'];
                    $product['categoryId']=$v['cate_id_1'];
                    $product['CategoryCode']=$v['cate_id_2'];
                    $product['CategoryThird']=$v['cate_id_3'];
                    $product['IsOnSell']=1;
                    $str='';
                    foreach ($v['description'] as $kkkk=>$vvvv){
                        $str.='<img src="'.$vvvv.'"/>';
                    }
                    $product['ProContent']=$str;
                    $product['SupplierId']=1;
                    $proid=Db::name('product')->insertGetId($product);

                    foreach ($v['goods_banner'] as $kkk=>$vvv){
                        $productimg=[];
                        $productimg['imgpath']=$vvv['goods_banner'];
                        $productimg['proid']=$proid;
                        $productimg['goods_id']=$v['goods_id'];
                        Db::name('productimg')->insert($productimg);
                    }
                    foreach ($v['spec'] as $kk=>$vv){
                        $productstock=[];
                        $productstock['goods_id']=$vv['goods_id'];
                        $productstock['spec_id']=$vv['spec_id'];
                        $productstock['StyleName1']=$vv['spec_1'];
                        $productstock['StyleName']=$vv['spec_2'];
                        $productstock['voucher_price']=$vv['voucher_price'];
                        $productstock['SupplierId']=1;
                        $productstock['Kucun']=$vv['stock'];
                        $productstock['Txm']=$vv['sku'];
                        $productstock['ProId']=$proid;
                        Db::name('productstock')->insert($productstock);
                    }
                }
                Db::name('page')->where('id=1')->setInc('page');
            }else{
                Db::name('page')->where('id=1')->update(['page'=>10000]);
            }

        }

    }
//同步分类
    public function getcateg(){
        $url="http://api.999000.cn/api/third/goodstype/?act=allcat";
        $data=file_get_contents($url);
        $data=json_decode($data,true);
//        return json($data);
        foreach ($data['data']['list'] as $k=>$v){
            $insertdata['Id']=$v['cate_id'];
            $insertdata['name']=$v['cate_name'];
            $insertdata['disable']=$v['if_show'];
            $insertdata['oid']=$v['parent_id'];
            $insertdata['pid']=$v['parent_id'];
            $insertdata['Lay']=1;
            $insertdata['photo']='http://www.999000.cn/'.$v['poster_picpath'];
            $insertdata['images']='http://www.999000.cn/'.$v['poster_picpath'];
            $insertdata['sort']=$v['sort_order'];
            Db::name('productcategory')->insert($insertdata);
            if(!empty($v['two'])){
                foreach ($v['two'] as $kk=>$vv){
                    $insertdata1['Id']=$vv['cate_id'];
                    $insertdata1['name']=$vv['cate_name'];
                    $insertdata1['disable']=$vv['if_show'];
                    $insertdata1['oid']=$vv['parent_id'];
                    $insertdata1['pid']=$vv['parent_id'];
                    $insertdata1['Lay']=2;
                    $insertdata1['photo']='http://www.999000.cn/'.$vv['poster_picpath'];
                    $insertdata1['images']='http://www.999000.cn/'.$vv['poster_picpath'];
                    $insertdata1['sort']=$vv['sort_order'];
                    Db::name('productcategory')->insert($insertdata1);

                }
            }
            if(!empty($v['three'])){
                foreach ($v['three'] as $kkk=>$vvv){
                    $insertdata2['Id']=$vvv['cate_id'];
                    $insertdata2['name']=$vvv['cate_name'];
                    $insertdata2['disable']=$vvv['if_show'];
                    $insertdata2['oid']=$vvv['parent_id'];
                    $insertdata2['pid']=$vvv['parent_id'];
                    $insertdata2['Lay']=3;
                    $insertdata2['photo']='http://www.999000.cn/'.$vvv['poster_picpath'];
                    $insertdata2['images']='http://www.999000.cn/'.$vvv['poster_picpath'];
                    $insertdata2['sort']=$vvv['sort_order'];
                    Db::name('productcategory')->insert($insertdata2);

                }
            }

        }
    }
    //同步库存
    public function getstock(){
        $page=Db::name('page')->field('page')->where('id=2')->find();
        $url = "http://api.999000.cn/api/third/goods/?act=zh_stock&id=" . $page['page'];
        $data=file_get_contents($url);
        $data=json_decode($data,true);
        if($data['status']==0){
            foreach ($data['data']['data'] as $k=>$v){
                    $updatepage['page']=$v['id'];
                    $stockdata['ModifyDate']=$v['add_time'];
                    $stockdata['Kucun']=$v['stock'];
                    $where['Txm']=$v['sku'];
                    Db::name('productstock')->where($where)->update($stockdata);
                    Db::name('page')->where('id=2')->update($updatepage);
                    $proid=Db::name('productstock')->where($where)->field('ProId')->find();
                    if($proid['ProId']!=1625){
                        $nums=Db::name('productstock')->where('ProId="'.$proid['ProId'].'"')->sum('Kucun');
                        $status=Db::name('product')->where('ProId="'.$proid['ProId'].'"')->field('IsOnSell')->find();
                        if($nums>0&&($status['IsOnSell']==3)){
                            Db::name('product')->where('ProId="'.$proid['ProId'].'"')->update(['IsOnSell'=>1]);
                        }elseif($nums<1&&($status['IsOnSell']==1)){
                            Db::name('product')->where('ProId="'.$proid['ProId'].'"')->update(['IsOnSell'=>3]);
                        }
                    }

            }
        }
    }
    /**
     * 添加收货地址
     * @param code  验证码
     * @param name  userID
     * @param receivename   收货人
     * @param province  省
     * @param city  市
     * @param county    区
     * @param address   地址
     * @param mobile    手机号
     * @param isdefault 默认收货地址
     */
    public function address_add()
    {
        $params = $this->request->param();
        if (empty($params['code'])) {
            $returndata['status'] = -1;
            $returndata['msg'] = '数据有误';
            return json($returndata);
        }
        if (empty($params['name'])) {
            $returndata['status'] = -1;
            $returndata['msg'] = '数据有误';
            return json($returndata);
        }
        if ($params['code'] != '56000029') {
            $returndata['status'] = -2;
            $returndata['msg'] = '数据有误';
            return json($returndata);
        }
        if (empty($params["receivename"])) {
            $returnData['data'] = array();
            $returnData['status'] = -3;
            $returnData['msg'] = '收货人不能为空！';
            return json($returnData);
        }
        if ($params["province"] == '请选择' || $params["city"] == '请选择' || $params["county"] == '请选择') {
            $returnData['data'] = array();
            $returnData['status'] = -4;
            $returnData['msg'] = '请选择所在地区！';
            return json($returnData);
        }
        if (empty($params["address"])) {
            $returnData['data'] = array();
            $returnData['status'] = -5;
            $returnData['msg'] = '详细地址不能为空！';
            return json($returnData);
        }
        if (!empty($params["mobile"])) {
            $rule = "/^[0-9]{11}$/A";
            if (!preg_match($rule, $params["mobile"])) {
                $returnData['data'] = array();
                $returnData['status'] = -6;
                $returnData['msg'] = '电话号码格式错误！';
                return json($returnData);
            }
        } else {
            $returnData['data'] = array();
            $returnData['status'] = -7;
            $returnData['msg'] = '电话号码不能为空！';
            return json($returnData);
        }

        $data["UserId"] = $params["name"];
        $data["ReceiveName"] = $params["receivename"];
        $data["Province"] = $params["province"];
        $data["City"] = $params["city"];
        $data["County"] = $params["county"];
        $data["Address"] = $params["address"];
        $data["Mobile"] = $params["mobile"];
        $data["AddDate"] = date('Y-m-d H:i:s', time());

        if (!empty($params["isdefault"])) {
            $datadefault["IsDefault"] = 0;
            Db::name('comreceiveinfo')->where("UserId='" . $params["name"] . "'")->update($datadefault);
            $data["IsDefault"] = 1;
        } else {
            $data["IsDefault"] = 0;
        }
        $id = Db::name('comreceiveinfo')->insertGetId($data);
        if (!empty($id)) {
            $returnData['data'] = array();
            $returnData['status'] = 1;
            $returnData['msg'] = '地址添加成功';
        } else {
            $returnData['data'] = array();
            $returnData['status'] = 0;
            $returnData['msg'] = '地址添加失败';
        }
        return json($returnData);
    }

    public function getfrieght(){
        $data=Request()->post();
        //var_dump($data);exit;
        $where['Id']=$data['addresid'];
        $where['UserId']=session('membername');
        $addres=Db::name('comreceiveinfo')->where($where)->field('Province,City,County')->find();
        //var_dump($frightlist);exit;
        if(isset($data['type'])&&$data['type']==2){
            $whereuser['UserId']=Session::get('membername');
            $usertype=Db::view('usermsg','userType')
                ->view('usertype','discount','usertype.ID=usermsg.userType')
                ->where($whereuser)->find();
            if($usertype['userType']==1){
                $fields = 'VipPrice,Weight';
                $product = Db::name('product')->where('ProId=' . $data['proid'])->field($fields)->select();
                $product[0]['ProNum']=$data['prosum'];
                $product[0]['ShopPrice']=$data['prosum']*$usertype['discount']*$product[0]['VipPrice'];
            }elseif ($usertype['userType']>=2){
                $fields = 'EnjoyPrice,Weight';
                $product = Db::name('product')->where('ProId=' . $data['proid'])->field($fields)->select();
                $product[0]['ProNum']=$data['prosum'];
                $product[0]['ShopPrice']=$data['prosum']*$usertype['discount']*$product[0]['EnjoyPrice'];
            }else{
                $fields = 'MarketPrice,Weight';
                $product = Db::name('product')->where('ProId=' . $data['proid'])->field($fields)->select();
                $product[0]['ProNum']=$data['prosum'];
                $product[0]['ShopPrice']=$data['prosum']*$usertype['discount']*$product[0]['MarketPrice'];
            }
            $product[0]['Weight']=$data['prosum']*$product[0]['Weight'];
//            var_dump($product);exit;
        }else{
//            var_dump(111);exit;
            $wherep['SupId']=$data['supplierid'];
            $wherep['Agentd']=session('membername');
            $product=Db::name('shoppingcart')->field('ProNum,ShopPrice,Weight')->where($wherep)->select();
        }
//        var_dump($product);exit;
//        $wherep['SupId']=$data['supplierid'];
//        $wherep['Agentd']=session('membername');
//        $product=Db::name('shoppingcart')->field('ProNum,ShopPrice,Weight')->where($wherep)->select();
        $price=0;
        $weght=0;
        $sum=0;

        if($product){
            foreach ($product as $k=>$v){
                $price+=$v['ShopPrice'];
                $weght+=$v['Weight'];
                $sum+=$v['ProNum'];
            }
            if($addres){
                $wheref['freightnew.SupplierId']=$data['supplierid'];
                $wheref['freightnew.freighttype']=$data['id'];
                $wheref['freigh_to_province.provincecitycounty_id']=$addres['County'];
                $frightlist=Db::view('freightnew','*')
                    ->view('freigh_to_province','*','freightnew.Id=freigh_to_province.freighrnew_Id')
                    ->where($wheref)
                    ->find();
                if($frightlist){
                    if($frightlist['type']==1){
                        $fright=$this->getfright($frightlist,$weght,$price);
                    }else{
                        $fright=$this->getcount($frightlist,$sum);
                    }
                }else{

                    $wheref1['freightnew.SupplierId']=$data['supplierid'];
                    $wheref1['freightnew.freighttype']=$data['id'];
                    $wheref1['freigh_to_province.provincecitycounty_id']=$addres['City'];
                    $frightlist1=Db::view('freightnew','*')
                        ->view('freigh_to_province','*','freightnew.Id=freigh_to_province.freighrnew_Id')
                        ->where($wheref1)
                        ->find();
                    if($frightlist1){
                        if($frightlist1['type']==1){
                            $fright=$this->getfright($frightlist1,$weght,$price);
                        }else{
                            $fright=$this->getcount($frightlist1,$sum);
                        }
                    }else{
                        $wheref2['freightnew.SupplierId']=$data['supplierid'];
                        $wheref2['freightnew.freighttype']=$data['id'];
                        $wheref2['freigh_to_province.provincecitycounty_id']=$addres['Province'];
                        $frightlist2=Db::view('freightnew','*')
                            ->view('freigh_to_province','*','freightnew.Id=freigh_to_province.freighrnew_Id')
                            ->where($wheref2)
                            ->find();
                        if($frightlist2){
                            if($frightlist2['type']==1){
                                $fright=$this->getfright($frightlist2,$weght,$price);
                            }else{
                                $fright=$this->getcount($frightlist2,$sum);
                            }
                        }else{
//                                var_dump(111);exit;
                            $wheref3['freightnew.SupplierId']=$data['supplierid'];
                            $wheref3['freightnew.freighttype']=$data['id'];
                            $wheref3['freigh_to_province.provincecitycounty_id']='-1';
                            $frightlist3=Db::view('freightnew','*')
                                ->view('freigh_to_province','*','freightnew.Id=freigh_to_province.freighrnew_Id')
                                ->where($wheref3)
                                ->find();
                            if($frightlist3){
                                if($frightlist3['type']==1){
                                    $fright=$this->getfright($frightlist3,$weght,$price);
                                }else{
                                    $fright=$this->getcount($frightlist3,$sum);
                                }
                            }else{
                                $fright=0;
                            }
                        }
                    }
                }
                $returndata['fright']=$fright;
                $returndata['status']=0;
                $returndata['msg']='成功';
//                if($frightlist['type']==1){
////                    var_dump($addres['County']);exit;
//                    $County=Db::name('freigh_to_province')->where('provincecitycounty_id='.$addres['County'].' and freighrnew_Id='.$frightlist['Id'])->find();
//                    if($County){
//                        $fright=$this->getfright($frightlist,$weght,$price);
//                    }else{
//                        $City=Db::name('freigh_to_province')->where('provincecitycounty_id='.$addres['City'].' and freighrnew_Id='.$frightlist['Id'])->find();
//                        if($City){
//                            $fright=$this->getfright($frightlist,$weght,$price);
//                        }else{
//                            $Province=Db::name('freigh_to_province')->where('provincecitycounty_id='.$addres['Province'].' and freighrnew_Id='.$frightlist['Id'])->find();
//                            if($Province){
//
//                                $fright=$this->getfright($frightlist,$weght,$price);
//                            }else{
//
//                                $chin=Db::name('freigh_to_province')->where('provincecitycounty_id=-1 and freighrnew_Id='.$frightlist['Id'])->find();
//                                if($chin){
//                                    $fright=$this->getfright($frightlist,$weght,$price);
//                                }else{
//                                    $fright=0;
//                                }
//                            }
//                        }
//                    }
//                    $returndata['fright']=$fright;
//                    $returndata['status']=0;
//                    $returndata['msg']='成功';
//                }else{
//
//                    $County=Db::name('freigh_to_province')->where('provincecitycounty_id='.$addres['County'].' and freighrnew_Id='.$frightlist['Id'])->find();
//                    if($County){
//                        $fright=$this->getcount($frightlist,$sum);
//                    }else{
//                        $City=Db::name('freigh_to_province')->where('provincecitycounty_id='.$addres['City'].' and freighrnew_Id='.$frightlist['Id'])->find();
//                        if($City){
//                            $fright=$this->getcount($frightlist,$sum);
//                        }else{
//                            $Province=Db::name('freigh_to_province')->where('provincecitycounty_id='.$addres['Province'].' and freighrnew_Id='.$frightlist['Id'])->find();
//                            if($Province){
//
//                                $fright=$this->getcount($frightlist,$sum);
//                            }else{
//                                $chin=Db::name('freigh_to_province')->where('provincecitycounty_id=-1 and freighrnew_Id='.$frightlist['Id'])->find();
//                                if($chin){
//                                    $fright=$this->getcount($frightlist,$sum);
//                                }else{
//                                    $fright=0;
//                                }
//                            }
//                        }
//                    }
//                    $returndata['fright']=$fright;
//                    $returndata['status']=0;
//                    $returndata['msg']='成功';
//                }
            }else{
                $returndata['status']=1;
                $returndata['msg']='传递参数有误';
            }
        }else{
            $returndata['status']=1;
            $returndata['msg']='传递参数有误';
        }

        return json($returndata);
    }
    public function getfright($frightlist,$weght,$sum){
        //var_dump($sum);exit;
        if($frightlist['MoneyFreightFree']==-1){
            if($weght<=$frightlist['Heavy']){
                $fright=$frightlist['HeavyMoney'];
            }else{
                $limit=ceil(($weght-$frightlist['Heavy'])/$frightlist['ContinuedHeavy']);
                $fright=$frightlist['HeavyMoney']+$limit*$frightlist['ContinuedHeavyMoney'];
            }
        }else{
            if($sum>=$frightlist['MoneyFreightFree']){
                $fright=0;
            }else{
                if($weght<=$frightlist['Heavy']){
                    $fright=$frightlist['HeavyMoney'];
                }else{
                    $limit=ceil(($weght-$frightlist['Heavy'])/$frightlist['ContinuedHeavy']);
                    $fright=$frightlist['HeavyMoney']+$limit*$frightlist['ContinuedHeavyMoney'];
                }
            }
        }
        return $fright;
    }
    public function getcount($frightlist,$sum){
        if($frightlist['CountFreightFree']==-1){
            if($sum==1){
                $fright=$frightlist['CountMoney'];
            }else{
                $limit=ceil(($sum-1));
                $fright=$frightlist['CountMoney']+$limit*$frightlist['ContinuedCountMoney'];
            }
        }else{
            if($sum>=$frightlist['CountFreightFree']){
                $fright=0;
            }else{
                if($sum==1){
                    $fright=$frightlist['CountMoney'];
                }else{
                    $limit=ceil(($sum-1));
                    $fright=$frightlist['CountMoney']+$limit*$frightlist['ContinuedCountMoney'];
                }
            }
        }
        return $fright;
    }

    /**获取城市县区
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function getcity(){
        $code=$this->request->post('code');
        if($code){
            $where['ParentCode']=$code;
            $res=Db::name('provincecitycounty')->field('Code,AreaName')->where($where)->select();
            if($res){
                return json(['status'=>1,'msg'=>'成功','data'=>$res]);
            }
        }else{
            return json(['status'=>1,'msg'=>'传递方式有误']);
        }

    }
}
