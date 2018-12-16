<?php
/**
 * Created by
 * User:
 * Date: 2018/3/8
 * Time: 15:30
 */

namespace app\index\controller;

use think\Db;
use think\Request;
use think\Session;
use think\Image;

/**
 * Class Index
 * @package home\index\controller
 * @return 首页页面
 */
class Product extends Auth
{
    /**
     * 收藏接口
     * @param userId  收藏人
     * @param collectType  收藏类型 1 商品  2商家
     * @param collectId  收藏商品的id 或 商家的id
     * @param addDate  添加时间
     * @return mixed    添加信息
     */
    public function addcollect()
    {
        $info = $this->islogin();
//        $info['data']['User_Id']='15907720288';
//        $info['status']=0;
        if ($info['status'] == 0) {
            $userId = $info['data']['UserId'];
            $addDate = date('Y-m-d H:i:s', time());
            $collectId = $this->request->param('collectId');
            $collectType = $this->request->param('collectType');
            $where['collectId'] = $collectId;
            $where['userId'] = $userId;
            $result = Db::name('collectrecords')->where($where)->find();
            if (!empty($result)) {
                Db::name('collectrecords')->where($where)->delete();
                $returnData['status'] = 0;
                $returnData['msg'] = '已取消收藏';
                return json($returnData);
            } else {
                $data['userId'] = $userId;
                $data['addDate'] = $addDate;
                $data['collectId'] = $collectId;
                $data['collectType'] = $collectType;
                $insertid = Db::name('collectrecords')->insertGetId($data);
                if ($insertid) {
                    $returnData['status'] = 0;
                    $returnData['msg'] = "已收藏";
                } else {
                    $returnData['status'] = 2;
                    $returnData['msg'] = "收藏失败";
                }
            }
        } else {
            $returnData['status'] = 99;
            $returnData['msg'] = '未登录';
        }
        return json($returnData);
    }

    /**
     * 收藏列表接口
     * 请求方式：http://www.XXX.com/app.php/goods/collectlist？collectType=1
     * @param token
     * $param page  页码
     * @param collectType  收藏类型 1 商品  2商家
     */
    public function collectlist()
    {
//        $_COOKIE['token']='36931388ae666cfc2e41699eb22041bc';
        $info = $this->islogin();
        if ($info['status'] == 0) {
            $userId = $info['data']['UserId'];
            $collectType = $this->request->param('collectType');
            $page = $this->request->param('page');
            $page = empty($page) ? 1 : $page;
            $where['collectType'] = $collectType;
            $where['userId'] = $userId;
            if ($collectType == 1) {
                $result = Db::view('collectrecords')
                    ->view('product', 'proname,qiqiuproimgpath,proimg,vipprice', 'product.proid=collectrecords.collectId')
                    ->where($where)
                    ->limit(($page - 1) * 10, 10)
                    ->select();
                if (!empty($result)) {
                    foreach ($result as $k => $v) {
                        if(is_onqiniu()==true){
                            $result[$k]['img']= $result[$k]['qiqiuproimgpath'];
                        }else{
                            $result[$k]['img']=config('IMAGE_DOMAIN_NAME').'/public/Upload/cpimg/'. $result[$k]['proimg'];
                        }
                        unset($result[$k]['proimg']);
                    }
                    $returnData['data'] = $result;
                    $returnData['status'] = 0;
                    $returnData['msg'] = '查询成功';
                } else {
                    $returnData['status'] = 1;
                    $returnData['msg'] = '暂无数据';
                }
            } else {
                $result = Db::name('collectrecords')->where($where)->limit(($page - 1) * 10, 10)->select();
                if (!empty($result)) {
                    $returnData['data'] = $result;
                    $returnData['status'] = 0;
                    $returnData['msg'] = '查询成功';
                } else {
                    $returnData['status'] = 1;
                    $returnData['msg'] = '暂无数据';
                }
            }

        } else {
            $returnData['status'] = 99;
            $returnData['msg'] = '未登录';
        }
        return json($returnData);
    }

    /**
     * 商品详情接口
     * 请求方式：http://www.XXX.com/app.php/goods/detail?proid=7671
     * 请求方式：get
     * 请求参数：
     * @param   proid 产品id
     * @return \think\response\Json
     * $returnData['status'] :0=success;-1=fail
     */
    public function product_detail()
    {
        $proid = $this->request->param('proid');
        if (!empty($proid) && is_numeric($proid)) {
            $where['ProId'] = $proid;
            $where['IsOnSell'] = 1;
            $fields = 'supplierid,proid,maxproimg,proimg,proname,qiqiuproimgpath,marketprice,vipprice,consumeintegral,giveintegral,minpurchase,peisong,title,keywords,unit,productsizedetail,description,procontent,hit,orderstep,goodstype';
            $data = Db::name('product')->where($where)->field($fields)->find();
            if ($data) {
                $isCollection = "0";
                if (isset($_COOKIE['token'])) {
                    $token = $_COOKIE['token'];
                    $wheretoken['Token'] = $token;
                    $userid = Db::name('token')->field('User_Id')->where($wheretoken)->find();
                    $condition['Agentd'] = $userid['User_Id'];

                    $whereCollect['collectId'] = $proid;
                    $whereCollect['userId'] = $userid['User_Id'];
                    $isCollectionData = Db::name('collectrecords')->where($whereCollect)->find();
                    if ($isCollectionData) {
                        $isCollection = "1";
                    } else {
                        $isCollection = "0";
                    }

                    $data['cartnum'] = Db::name('shoppingcart')->where($condition)->sum('ProNum');

                    if ($data['cartnum'] > 0) {
                        $data['cartnum'] = $data['cartnum'];
                    } else {
                        $data['cartnum'] = 0;
                    }
                } else {
                    $data['cartnum'] = 0;
                }
                $data['procontent'] = '<meta name="viewport" content="width=device-width" >' . $data['procontent'];
                $data['procontent'] = str_replace('<img ', '<img style="width: 100%;height: auto;" ', $data['procontent']);
                $data['consumeintegral'] = intval($data['consumeintegral']);
                $data['giveintegral'] = intval($data['giveintegral']);

                if(is_onqiniu()==true){
                    $data['proimg']= $data['qiqiuproimgpath'];
                    $data['procontent']=contentReplace($data['procontent']);
                }else{
                    $data['proimg']=config('IMAGE_DOMAIN_NAME').'/public/Upload/cpimg/'. $data['proimg'];
                    $data['procontent']=str_replace('src="','src="'.Config::get('IMAGE_DOMAIN_NAME').'',$data['procontent']);
                }

                $styleArr = Db::name('productstock')->where('ProId=' . $data['proid'])->field('styleid,stylename,sum(kucun) as kucun')->group('stylename')->select();
                //$styleArr1 = Db::name('productstock')->where('ProId=' . $data['proid'])->field('styleid,stylename1,stylename,kucun')->group('stylename1')->select();

                $numkucun = Db::name('productstock')->where('ProId=' . $data['proid'])->field('sum(kucun) as kucun')->select();
                product_hit($proid);//累加点击数
                setcookie("BrowserHistory", "", time() - 3600);
                $historyData = "{$data['proid']}|{$data['proname']}|{$data['marketprice']}|{$data['vipprice']}|{$data['consumeintegral']}|{$data['proimg']}";

                $this->product_history($historyData);//添加浏览记录到COOKIE

                //详情页图片轮播
                $albums = Db::name('productimg')->where('proid=' . $data['proid'])->field('imgpath,qiniuimgpath')->select();
//
                if (empty($albums)) {
                    $albums[0] = $data['proimg'];//图片主图
                } else {
                    foreach ($albums as $key => $val) {
                        $albums[0] = $data['proimg'];//图片主图
                        if(is_onqiniu()==true){
                            $albums[$key + 1] = $val['qiniuimgpath'];
                        }else{
                            $albums[$key + 1] = config('IMAGE_DOMAIN_NAME') . '/public/Upload/prophoto/' . $val['imgpath'];//相册合集
                        }
                    }
                }

                $data['isCollection'] = $isCollection;
                $share = ['url' => 'http://tyshop1.8521446.com/mobilenew.php/goods/' . $proid, 'title' => $data['proname'], "detail" => "快来抢购啊！",
                    "img" => $data['proimg']];
                $returnData['data']['imgalbum'] = $albums;//详情页图片合集轮播
                $returnData['data']['goodsstyle'] = $styleArr;//规格
                //$returnData['data']['goodsstyle1'] = $styleArr1;//颜色
                $returnData['data']['kucun'] = $numkucun;//库存
                $returnData['data']['datails'] = $data;//数据详情
                $returnData['data']['share'] = $share;//分享数据
                $returnData['status'] = 0;
                $returnData['msg'] = '获取数据成功';
            } else {
                $returnData['status'] = 1;
                $returnData['msg'] = '产品不存在或者已下架';
            }
        } else {
            $returnData = ['status' => 1, 'mes' => '参数格式错误，必须为非空数字'];
        }
        return json($returnData);
    }

    /**
     * 商品列表接口
     * 请求方式：http://www.XXX.com/goods/list?ishit=2
     * 请求方式：get
     * 请求参数：
     * @param   ishit 产品展示区域id
     * @return \think\response\Json
     */

    public function product_list()
    {
        $ishit = $this->request->param('ishit');
        $pagesize = $this->request->param('pagesize');
        if (!empty($ishit) && is_numeric($ishit)) {
            $pagecount = !empty($pagesize) ?: 12;
            $where['IsHit'] = $ishit;
            $where['IsOnSell'] = 1;
            $fields = 'proid,proname,ishit,supplierid,marketprice,vipprice,consumeintegral,proimg,qiqiuproimgpath';
            $data = Db::name('product')->where($where)->field($fields)->order('proid asc')->paginate($pagecount);
            $dataArr = [];
            foreach ($data as $key => $val) {
                $supinfo = Db::table('supplier')->where("id=" . $val['supplierid'])->field('name')->find();
                $val['suppliername'] = $supinfo['name'];
                if(is_onqiniu()==true){
                    $val['proimg']= $data['qiqiuproimgpath'];
                }else{
                    $val['proimg'] = config('IMAGE_DOMAIN_NAME') . '/Upload/cpimg/' . $val['proimg'];
                }
                $val['consumeintegral'] = intval($val['consumeintegral']);
                $dataArr[] = $val;
            }
            if (!empty($dataArr)) {
//                $returnData['total']=ceil($data->total()/$pagecount);//总页数
//                $returnData['data'] = $dataArr;

                $returnData["data"]['total'] = ceil($data->total() / $pagecount);//总页数
                $returnData['data']["data_list"] = $dataArr;

                $returnData['status'] = 0;
                $returnData['msg'] = '获取列表数据成功';
            } else {

                $returnData['status'] = 2;
                $returnData['msg'] = '暂无数据';
            }
        } else {
            $returnData = ['status' => 1, 'msg' => '参数格式错误，必须为非空数字'];
        }
        return json($returnData);
    }

    /**
     * 产品规格接口
     * 请求方式：http://www.XXX.com/goods/stock?proid=7671&stylename=白色
     * 请求方式：get
     * 请求参数：
     * @param proid 商品id
     * @param stylename   颜色名称
     * @return \think\response\Json 规格参数
     */
    public function product_stock()
    {
        $where['ProId'] = $this->request->param('proid');
        $where['StyleName1'] = $this->request->param('stylename');
        $result = Db::name('productstock')->where($where)->select();
        if ($result) {
            $returnData = $result;
        } else {
            $returnData = [];
        }
        return json($returnData);
    }

    /**
     * 产品库存接口
     * 请求方式：http://www.XXX.com/goods/stock?proid=7671&&stylename=白色&stylename1=59
     * 请求方式：get
     * 请求参数：
     * @param proid 商品id
     * @param stylename1   颜色名称
     * @param stylename   颜色名称
     * @return \think\response\Json 库存数量
     */
    public function product_stockAll()
    {
        $where['ProId'] = $this->request->param('proid');
        $where['StyleName1'] = $this->request->param('stylename');
        $where['StyleName'] = $this->request->param('stylename1');

        $result = Db::name('productstock')->where($where)->select();
        if ($result) {
            $returnData = $result;
        } else {
            $returnData = [];
        }
        return json($returnData);
    }

    /**
     * 商品搜索接口
     * 请求方式：http://www.XXX.com/goods/product_search
     * 请求方式：get
     * 请求参数：
     * @param keyword   产品关键字
     * @return \think\response\Json
     */
    public function product_search()
    {
        $pagesize = $this->request->param('page');
        $pagesize = empty($pagesize) ? 1 : $pagesize;
        $where['IsOnSell'] = 1;
        $where["MinPurchase"] = ['>', 0];
        //产品搜索的查询条件
        if ($this->request->param('keyword')) {
            $where['ProName'] = ['like', '%' . $this->request->param("keyword") . '%'];
        }
        if ($this->request->param('sort')) {

            $sort = $this->request->param('sort');
            if ($sort == 1) {
//                    $sortid=Session::get('sortid');
//                    if($sortid==0){
//                        $order='proid asc';
//                        Session::set('sortid',1);
//                    }elseif ($sortid==1){
                $order = 'proid desc';
//                        Session::set('sortid',0);
//                    }else{
//                    $order='proid desc';
//                    Session::set('sortid',0);
//                }
            } elseif ($sort == 2) {
//                    $sortnum=Session::get('sortnum');
//                    if($sortnum==0){
//                        $order='prosum asc';
//                        Session::set('sortnum',1);
//                    }elseif ($sortnum==1){
                $order = 'prosum desc';
//                        Session::set('sortnum',0);
//                    }else{
//                    $order='prosum desc';
//                    Session::set('sortnum',0);
//                }

            } elseif ($sort == 3) {
//                    $pricesort=Session::get('pricesort');
//                    if($pricesort==0){
                $order = 'vipprice asc';
//                        Session::set('pricesort',1);
//                    }elseif ($pricesort==1){
//                        $order='marketprice desc';
//                        Session::set('pricesort',0);
//                    } else{
//                    $order='marketprice desc';
//                    Session::set('pricesort',0);
//                }
            } elseif ($sort == 4) {
                $order = 'vipprice desc';
            }
        } else {
            $order = 'proid desc';
//            Session::set('sortid',0);
        }
        $fields = 'proid,proname,ishit,supplierid,marketprice,vipprice,consumeintegral,proimg,prosum as pronum,qiqiuproimgpath';
        $data = Db::name('product')->where($where)->field($fields)->order($order)->limit(($pagesize - 1) * 10, 10)->select();
        $dataArr = [];
        foreach ($data as $key => $val) {
            $supinfo = Db::table('supplier')->where("id=" . $val['supplierid'])->field('name')->find();
            $val['suppliername'] = $supinfo['name'];
            if(is_onqiniu()==true){
                $val['proimg']= $data['qiqiuproimgpath'];
            }else{
                $val['proimg'] = config('IMAGE_DOMAIN_NAME') . '/public/Upload/cpimg/' . $val['proimg'];
            }
            unset($val['MaxProImg']);
            $val['consumeintegral'] = intval($val['consumeintegral']);
            $dataArr[] = $val;
        }
        if (!empty($dataArr)) {
            $returnData['data']['data_list'] = $dataArr;
            $returnData['status'] = 0;
            $returnData['msg'] = '获取列表数据成功';
        } else {
            $returnData['status'] = 1;
            $returnData['msg'] = '暂无数据';
        }
        return json($returnData);
    }


    /**
     * 商品分类搜索接口
     * 请求方式：http://www.XXX.com/goods/product_cid
     * 请求方式：get
     * 请求参数：
     * @param cid   分类id
     * @return \think\response\Json
     */
    public function product_cid()
    {
        $pagesize = $this->request->param('page');
        $pagesize = empty($pagesize) ? 1 : $pagesize;
        $where['IsOnSell'] = 1;
        $where["MinPurchase"] = ['>', 0];
        //产品一级分类查询条件
        if ($this->request->param('cid')) {
            $cid = $this->request->param('cid');
            $str = 'CategoryCode=' . $cid . ' or CategoryThird=' . $cid;
        } else {
            $str = '';
        }

        if ($this->request->param('sort')) {

            $sort = $this->request->param('sort');
            if ($sort == 1) {
//                    $sortid=Session::get('sortid');
//                    if($sortid==0){
//                        $order='proid asc';
//                        Session::set('sortid',1);
//                    }elseif ($sortid==1){
                $order = 'proid desc';
//                        Session::set('sortid',0);
//                    }else{
//                    $order='proid desc';
//                    Session::set('sortid',0);
//                }
            } elseif ($sort == 2) {
//                    $sortnum=Session::get('sortnum');
//                    if($sortnum==0){
//                        $order='prosum asc';
//                        Session::set('sortnum',1);
//                    }elseif ($sortnum==1){
                $order = 'prosum desc';
//                        Session::set('sortnum',0);
//                    }else{
//                    $order='prosum desc';
//                    Session::set('sortnum',0);
//                }

            } elseif ($sort == 3) {
//                    $pricesort=Session::get('pricesort');
//                    if($pricesort==0){
                $order = 'vipprice asc';
//                        Session::set('pricesort',1);
//                    }elseif ($pricesort==1){
//                        $order='marketprice desc';
//                        Session::set('pricesort',0);
//                    } else{
//                    $order='marketprice desc';
//                    Session::set('pricesort',0);
//                }
            } elseif ($sort == 4) {
                $order = 'vipprice desc';
            }
        } else {
            $order = 'proid desc';
//            Session::set('sortid',0);
        }
        $fields = 'proid,proname,ishit,supplierid,marketprice,vipprice,consumeintegral,proimg,prosum as pronum,qiqiuproimgpath';
        $data = Db::name('product')->where($where)->where($str)->field($fields)->order($order)->limit(($pagesize - 1) * 10, 10)->select();
        $dataArr = [];
        foreach ($data as $key => $val) {
            $supinfo = Db::table('supplier')->where("id=" . $val['supplierid'])->field('name')->find();
            $val['suppliername'] = $supinfo['name'];
            if(is_onqiniu()==true){
                $val['proimg']= $data['qiqiuproimgpath'];
            }else{
                $val['proimg'] = config('IMAGE_DOMAIN_NAME') . '/public/Upload/cpimg/' . $val['proimg'];
            }
            unset($val['MaxProImg']);
            $val['consumeintegral'] = intval($val['consumeintegral']);
            $dataArr[] = $val;
        }
        if (!empty($dataArr)) {
            $returnData['data']['data_list'] = $dataArr;
            $returnData['status'] = 0;
            $returnData['msg'] = '获取列表数据成功';
        } else {
            $returnData['status'] = 1;
            $returnData['msg'] = '暂无数据';
        }
        return json($returnData);
    }

    /**
     * 商品分类接口
     * 请求方式：http://www.XXX.com/app.php/index/category
     * 请求方式：get
     * @param type  category商品分类 brand 商品品牌
     * @return \think\response\Json
     */
    public function category()
    {
        $typea = $this->request->param('type');
        $type = !empty($typea) ? $typea : 'category';
        if ($type == 'category') {
            $cate = Db::table('productcategory')->where("pid=0 and disable=1")->limit(8)->select();
            foreach ($cate as $k => $v) {
                $cate[$k]['twocate'] = $twocate = Db::table('productcategory')->where("pid=" . $v['Id'] . " and disable=1")->select();
                 if ($twocate) {
                    foreach ($twocate as $kk => $vv) {
                        $cate[$k]['twocate'][$kk]['threecate'] = $threecate = Db::table('productcategory')->where("pid=" . $vv['Id'] . " and disable=1")->select();
                        if (empty($threecate)) {
                            $cate[$k]['twocate'][$kk]['photo']=config('IMAGE_DOMAIN_NAME').$cate[$k]['twocate'][$kk]['photo'];
                            $cate[$k]['twocate'][$kk]['threecate'][0] = $twocate[$kk];
                        }else{
                            foreach ($threecate as $kkk=>$vvv){
                                $cate[$k]['twocate'][$kk]['threecate'][$kkk]['photo']=config('IMAGE_DOMAIN_NAME').$cate[$k]['twocate'][$kk]['threecate'][$kkk]['photo'];
                            }
                        }
                    }
                }
            }
            $returndata['data'] = $cate;
            $returndata['status'] = 0;
            $returndata['msg'] = '成功';
            return json($returndata);
        } else {
            $list = Db::name('images')->field('photo as img,img_name as name,propaganda as detail,url as link,type')->where('images_id=15')->order('id asc')->select();
            if (!empty($list)) {
                foreach ($list as $k => $v) {
                    $list[$k]['img'] = config('IMAGE_DOMAIN_NAME') . '/uploads/' . $list[$k]['img'];
                }
                $returndata['data'] = $list;
                $returndata['status'] = 0;
                $returndata['msg'] = '成功';
            } else {
                $returndata['status'] = 1;
                $returndata['msg'] = '暂无数据';
            }


            return json($returndata);
//            $data='{"data":[{"img":"","name":"WQ-ii雅致女装 品牌男装","detail":"源自法国的经典","link":"","type":"0"},{"img":"","name":"WQ-ii皮具 鳄鱼纹/鸵鸟纹/编织纹","detail":"源自法国的经典","link":"","type":"0"},{"img":"","name":"优资莱护肤品久零名品专供","detail":"植物的 更亲肤","link":"","type":"0"},{"img":"","name":"魔法世家护肤品","detail":"天然膜护理专家","link":"","type":"0"},{"img":"","name":"六世缘珠宝","detail":"精雕细琢 件件精品","link":"","type":"0"},{"img":"","name":"东洋之花","detail":"专为东方女性研制","link":"","type":"0"}],"status":"0","msg":"获取成功"';
//            return $data;
        }
    }

    /**
     * 设置cookie
     */
    private function product_history($data)
    {
        if (!empty($data)) {
            if (!empty($_COOKIE['BrowserHistory'])) {
                $history = explode(',', $_COOKIE['BrowserHistory']);
                array_unshift($history, $data);//在这个数组的开头插入当前正在浏览的商品ID

                $history = array_unique($history);//去除数组里重复的值
                while (count($history) > 6) {
                    //将数组最后一个单元弹出，直到它的长度小于等于5为止
                    array_pop($history);
                }
                //把这个数组用逗号连成一个字符串写入COOKIE，并设置其过期时间为30天
                setcookie('BrowserHistory', implode(',', $history), time() + 3600 * 24 * 30);

            } else {
                //如果COOKIE里面为空，则把当前浏览的商品ID写入COOKIE ，这个只在第一次浏览该网站时发生
                setcookie('BrowserHistory', $data, time() + 3600 * 24 * 30);
            }
        }
    }

    /**
     * 获取cookie中存储的商品信息
     * @return \think\response\Json
     */

    public function getHistory()
    {
        $history = isset ($_COOKIE['BrowserHistory']) ? $_COOKIE['BrowserHistory'] : 0;
        $arr = explode(',', $history);
        $dataArr = [];
        if (!empty($arr)) {
            foreach ($arr as $k => $v) {
                $tempArr = explode('|', $v);
                $datarr['proid'] = $tempArr[0];
                $datarr['proname'] = $tempArr[1];
                $datarr['marketprice'] = $tempArr[2];
                $datarr['vipprice'] = $tempArr[3];
                $datarr['consumeintegral'] = $tempArr[4];
                $datarr['proimg'] = $tempArr[5];
                $dataArr[] = $datarr;
            }
            $returnData['data'] = $dataArr;
            $returnData['status'] = 1;
            $returnData['msg'] = '获取列表数据成功';
        } else {

            $returnData['status'] = 0;
            $returnData['msg'] = '暂无数据';
        }
        return json($returnData);
    }

}
