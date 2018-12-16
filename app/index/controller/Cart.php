<?php
/**
 * Created by
 * User:
 * Date: 2018/3/9
 * Time: 9:30
 */

namespace app\index\controller;

use think\Db;
use think\Session;

/**
 * Class Cart
 * @package home\index\controller
 * @return 首页页面
 */
class Cart extends Auth
{
    /**
     * 购物车列表
     * 请求实例：http://www.XX.com/app.php/member/cart_list
     * 请求方式 get
     * @return returnData['data'] data 已添加商品信息数据
     * @return returnData['status'] status 0:添加失败  1:添加成功
     * @return returnData['msg'] msg  信息提示
     */
    public function goodsList()
    {
        $info = $this->islogin();//判断会员是否登录
        if ($info['status'] == 0) {
            $userid = $info['data']['UserId'];
            $condition['Agentd'] = $userid;
            $goodslist = Db::name('shoppingcart')->where($condition)->field('supid')->group('SupId')->select();
            $cartList = [];
            if ($goodslist) {
                $sum_money = 0;
                $sum_integral = 0;
                $sum_giveintegral = 0;
                $goodNum = 0;
                foreach ($goodslist as $k => $v) {
                    $cond['ID'] = $v['supid'];
                    $supdata = Db::name('supplier')->where($cond)->field('name')->find();
                    $cartList[$k]['supid'] = $v['supid'];
                    $cartList[$k]['supname'] = $supdata['name'];
                    $where1['Agentd'] = $userid;
                    $where1['SupId'] = $v['supid'];
                    $subList = $cartList[$k]['sub'] = indexToLower(Db::name('shoppingcart')->where($where1)->field('proid,shopprice,consumeintegral,giveintegral,id,status,proname,stylename,pronum')->select());
                    foreach ($subList as $key => $val) {
                        $where['ProId'] = $val['proid'];
                        $prodata = Db::name('product')->where($where)->field('proimg,qiqiuproimgpath')->find();
                        if(is_onqiniu()==true){
                            $cartList[$k]['sub'][$key]['proimg']= $prodata['qiqiuproimgpath'];
                        }else{
                            $cartList[$k]['sub'][$key]['proimg']=config('IMAGE_DOMAIN_NAME').'/public/Upload/cpimg/'. $prodata['proimg'];
                              }
                        if ($val['status'] == 1) {
                            $sum_money += $val['shopprice'];
                            $sum_integral += $val['consumeintegral'];
                            $sum_giveintegral += $val['giveintegral'];
                        }
                        $cartList[$k]['sub'][$key]['shopprice'] = $val['shopprice'] / $val['pronum'];
                        $cartList[$k]['sub'][$key]['consumeintegral'] = intval($val['consumeintegral'] / $val['pronum']);
                        $cartList[$k]['sub'][$key]['giveintegral'] = intval($val['giveintegral'] / $val['pronum']);
                        $goodNum += $val['pronum'];
                    }
                }
                $returnData['data']["data_list"] = $cartList;
                $returnData["data"]['sum_money'] = $sum_money;
                $returnData["data"]['sum_integral'] = $sum_integral;
                $returnData["data"]['sum_giveintegral'] = $sum_giveintegral;
                $returnData["data"]['goods_num'] = $goodNum;

                $returnData['status'] = 0;
                $returnData['msg'] = '获取数据成功';
            } else {
                $returnData['status'] = 2;
                $returnData['msg'] = '购物车为空';
            }
        } else {
            $returnData['status'] = 99;
            $returnData['msg'] = '未登录';
        }
        return json($returnData);
    }

    /**
     * 添加商品到购物车
     * 请求实例：http://www.XX.com/app.php/member/add?proid=7671&styleid=9100&pronum=6
     * 请求方式 get
     * @param proid 产品id
     * @param styleid  规格id
     * @param pronum  商品数量
     * @return returnData['data'] data 已添加商品信息数据
     * @return returnData['status'] status 0:添加失败  1:添加成功
     * @return returnData['msg'] msg  信息提示
     */
    public function addGoods()
    {
        $info = $this->islogin();//判断会员是否登录
        if ($info['status'] == 0) {
            $userid = $info['data']['UserId'];
            $proid = $this->request->param('proid');
            $styleid = $this->request->param('styleid');
            //$styleid = $this->request->param('styleid2');
            $pronum = $this->request->param('pronum');
            $agentd = $userid;
            $where['ProId'] = $proid;
            $where['IsOnSell'] = 1;
            $procount = Db::name('product')->where($where)->count();//判断该商品是否存在或者已上架
            if ($procount > 0) {
                $condition['ProId'] = $proid;
                $condition['StyleId'] = $styleid;
                $condition['Agentd'] = $agentd;
                $cartcheck = Db::name('shoppingcart')->where($condition)->count();//判断购物车中是否已添加相同规格的商品
                if ($cartcheck == 0) {
                    $styledata = Db::name('productstock')->where('StyleId=' . $styleid)->field('StyleName,Kucun')->find();
                    if ($pronum <= $styledata['Kucun']) {
                        $fields = 'ProName,SupplierId,VipPrice,ConsumeIntegral,Unit,BalancePrice,MinPurchase,GiveIntegral';
                        $prodata = Db::name('product')->where('ProId=' . $proid)->field($fields)->find();
                        if ($pronum >= $prodata['MinPurchase'] && (($pronum % $prodata['MinPurchase']) == 0)) {//判断所购的商品数量是否小于最小起订量且为最小起订量的倍数
                            //构建添加购物车的数据--start--
                            $data["SupId"] = $prodata['SupplierId'];
//                            $data["shipping_id"] = $prodata['shipping_id'];
                            $supInfo = Db::name('supplier')->where('ID=' . $prodata['SupplierId'])->field('Name')->find();
                            $data["SupName"] = $supInfo['Name'];
                            $data["ProId"] = $proid;
                            $data["ProName"] = $prodata['ProName'];
                            $data["StyleId"] = $styleid;
                            $data["StyleName"] = $styledata['StyleName'];
                            $data["ProNum"] = $pronum;
                            $data["ShopPrice"] = $prodata['VipPrice'] * $pronum;
                            $data["ConsumeIntegral"] = $prodata['ConsumeIntegral'] * $pronum;
                            $data["GiveIntegral"] = $prodata['GiveIntegral'] * $pronum;
                            $data["Unit"] = $prodata['Unit'];
                            $data["CartType"] = 2;
                            $data["Agentd"] = $agentd;
                            $data["BalancePrice"] = $prodata['BalancePrice'];
                            $data["AddTime"] = date('Y-m-d H:i:s', time());
                            $data["RegionOfBuy"] = 'APP端';
                            //构建添加购物车的数据--end--
                            Db::name('shoppingcart')->insert($data);//添加数据到购物车
                            $cartno = Db::name('shoppingcart')->where("agentd='" . $agentd . "'")->sum('ProNum');//获取该会员购物车中的商品数量
                            $cartdata['cartno'] = $cartno;
                            $returnData['data'] = $cartdata;
                            $returnData['status'] = 0;
                            $returnData['msg'] = '添加成功';
                        } else {
                            $returnData['status'] = 5;
                            $returnData['msg'] = '商品数量必须大于等于最小起订量且为最小起订量的倍数';
                        }
                    } else {

                        $returnData['status'] = 4;
                        $returnData['msg'] = '该商品库存不足';
                    }
                } else {
                    $styledata = Db::name('productstock')->where('StyleId=' . $styleid)->field('StyleName,Kucun')->find();
                    if ($pronum <= $styledata['Kucun']) {
                        $fields = 'ProName,SupplierId,VipPrice,ConsumeIntegral,Unit,BalancePrice,MinPurchase,GiveIntegral';
                        $prodata = Db::name('product')->where('ProId=' . $proid)->field($fields)->find();
                        $shopcartdata = Db::name('shoppingcart')->field('ProNum,ShopPrice,ConsumeIntegral,GiveIntegral')->where($condition)->find();
                        //构建添加购物车的数据--start--
                        $supInfo = Db::name('supplier')->where('ID=' . $prodata['SupplierId'])->field('Name')->find();

                        $updata["ProNum"] = $pronum + $shopcartdata['ProNum'];
                        $updata["ShopPrice"] = $prodata['VipPrice'] * $pronum + $shopcartdata['ShopPrice'];
                        $updata["ConsumeIntegral"] = $prodata['ConsumeIntegral'] * $pronum + $shopcartdata['ConsumeIntegral'];
                        $updata["GiveIntegral"] = $prodata['GiveIntegral'] * $pronum + $shopcartdata['GiveIntegral'];
                        $updata["BalancePrice"] = $prodata['BalancePrice'];
                        //构建添加购物车的数据--end--
                        Db::name('shoppingcart')->where($condition)->update($updata);//添加数据到购物车
                        $cartno = Db::name('shoppingcart')->where("agentd='" . $agentd . "'")->sum('ProNum');//获取该会员购物车中的商品数量
                        $cartdata['cartno'] = $cartno;
                        $returnData['data'] = $cartdata;
                        $returnData['status'] = 0;
                        $returnData['msg'] = '添加成功';
                    } else {

                        $returnData['status'] = 4;
                        $returnData['msg'] = '该商品库存不足';
                    }
                }
            } else {

                $returnData['status'] = 2;
                $returnData['msg'] = '产品不存在或者已下架';
            }
        } else {

            $returnData['status'] = 99;
            $returnData['msg'] = '未登录';
        }
        return json($returnData);
    }

    /**
     * 根据购物车id删除购物车中的商品
     * 请求实例：http://www.XX.com/api.php/member/del_goods?cartid=11211
     * 请求方式 get
     * @param cartid 购物车id
     * @return $returnData['status']  status 删除状态
     * @return $returnData['msg']  msg 删除提示
     */

    public function delGoods()
    {
        $info = $this->islogin();//判断会员是否登录

        if ($info['status'] == 0) {
            $userid = $info['data']['UserId'];
            $agentd = $userid;
            $cartid = $this->request->param('cartid');
            Db::name('shoppingcart')->where('Id=' . $cartid . " and Agentd='" . $agentd . "'")->delete();

            $returnData['status'] = 0;
            $returnData['msg'] = '已删除';
        } else {

            $returnData['status'] = 99;
            $returnData['msg'] = '未登录';
        }
        return json($returnData);
    }

    /**
     *购物车中商品数量的更改
     * 请求实例：http://www.XX.com/app.php/member/update_catr_goodsnum?act=increase&cartid=198702  数量增加
     * 请求方式 get
     * 请求实例：http://www.XX.com/app.php/member/update_catr_goodsnum?act=reduce&cartid=198702   数量的减少
     * 请求方式 get
     * @param act   increase 数量增加  |   reduce  数量的减少
     * @param cartid
     * @return returnData['data']
     *
     *     data[
     *       pronum：更新数量后该商品的数量
     *       shopprice：更新数量后该商品的应付款(商品单价与数量的乘积)
     *       pv：更新数量后该商品的扣除积分(单个商品应扣积分与数量的乘积)
     *       shopcartsum：会员购物车中商品价格总额
     *       shoppvsum：会员购买的商品需扣除积分的总额
     *     ]
     * @return returnData['status'] status 0:添加失败  1:添加成功
     * @return returnData['msg'] msg  信息提示
     */

    public function updateGoodsNum()
    {
        $info = $this->islogin();//判断会员是否登录
        if ($info['status'] == 0) {
            $userid = $info['data']['UserId'];
            $act = $this->request->param('act');
            $cartid = $this->request->param('cartid');
            $membername = $userid;
            if ($act == 'increase') {//按照最低起订量增加
                $cartRecord = Db::name('shoppingcart')->where('Id=' . $cartid . " and Agentd='" . $membername . "'")->field('ProId,ProNum,ShopPrice,ConsumeIntegral,StyleId,GiveIntegral,SupId')->find();
                if ($cartRecord) {
                    $kucunData = Db::name('productstock')->where('StyleId=' . $cartRecord['StyleId'])->field('Kucun')->find();
                    $proinfo = Db::name('product')->where('ProId=' . $cartRecord['ProId'])->field('ProName,MinPurchase,VipPrice,ConsumeIntegral,GiveIntegral')->find();
                    $updateData['ProNum'] = $cartRecord['ProNum'] + $proinfo['MinPurchase'];
                    if ($kucunData['Kucun'] >= $updateData['ProNum']) {//判断购物数量是否大于库存
                        $updateData['ShopPrice'] = $cartRecord['ShopPrice'] + $proinfo['MinPurchase'] * $proinfo['VipPrice'];
                        $updateData["ConsumeIntegral"] = $cartRecord['ConsumeIntegral'] + $proinfo['MinPurchase'] * $proinfo['ConsumeIntegral'];
                        $updateData["GiveIntegral"] = $cartRecord['GiveIntegral'] + $proinfo['MinPurchase'] * $proinfo['GiveIntegral'];
                        Db::name('shoppingcart')->where('Id=' . $cartid . " and Agentd='" . $membername . "'")->update($updateData);
                        $cartNewRecord['pronum'] = $updateData['ProNum'];
                        $cartNewRecord['shopprice'] = $updateData['ShopPrice'];
                        $cartNewRecord['consumeintegral'] = $updateData["ConsumeIntegral"];
                        $cartNewRecord['giveintegral'] = $updateData["GiveIntegral"];
                        $cartNewRecord['shopcartsum'] = Db::name('shoppingcart')->where("status=1 and agentd='" . $membername . "'")->sum('shopprice');
                        $cartNewRecord['shoppvsum'] = Db::name('shoppingcart')->where("status=1 and agentd='" . $membername . "'")->sum('ConsumeIntegral');
                        $cartNewRecord['shopgivepvsum'] = Db::name('shoppingcart')->where("status=1 and agentd='" . $membername . "'")->sum('GiveIntegral');
                        $returnData['data'] = $cartNewRecord;
                        $returnData['status'] = 0;
                        $returnData['msg'] = '数量增加成功';
                    } else {

                        $returnData['status'] = 3;
                        $returnData['msg'] = '该商品库存不足';
                    }
                } else {

                    $returnData['status'] = 2;
                    $returnData['msg'] = '购物车中无此商品';
                }
            } elseif ($act == 'reduce') {//按照最低起订量减少
                $cartRecord = Db::name('shoppingcart')->where('Id=' . $cartid . " and Agentd='" . $membername . "'")->field('ProId,ProNum,ShopPrice,ConsumeIntegral,GiveIntegral,SupId')->find();
                if ($cartRecord) {
                    $proinfo = Db::name('product')->where('ProId=' . $cartRecord['ProId'])->field('ProName,MinPurchase,VipPrice,ConsumeIntegral,GiveIntegral')->find();
                    if ($cartRecord['ProNum'] <= $proinfo['MinPurchase']) {//如果购物车中的商品数量小于或者等于该商品的最小起订量，则无法更改
                        $returnData['status'] = 6;
                        $returnData['msg'] = '商品 : ' . $proinfo['ProName'] . '最小起订数量必须为' . $proinfo['MinPurchase'];
                    } else {
                        $updateData['ProNum'] = $cartRecord['ProNum'] - $proinfo['MinPurchase'];
                        $updateData['ShopPrice'] = $cartRecord['ShopPrice'] - $proinfo['MinPurchase'] * $proinfo['VipPrice'];
                        $updateData["ConsumeIntegral"] = $cartRecord['ConsumeIntegral'] - $proinfo['MinPurchase'] * $proinfo['ConsumeIntegral'];
                        $updateData["GiveIntegral"] = $cartRecord['GiveIntegral'] - $proinfo['MinPurchase'] * $proinfo['GiveIntegral'];
                        Db::name('shoppingcart')->where('Id=' . $cartid . " and Agentd='" . $membername . "'")->update($updateData);

                        $cartNewRecord['pronum'] = $updateData['ProNum'];
                        $cartNewRecord['shopprice'] = $updateData['ShopPrice'];
                        $cartNewRecord['consumeintegral'] = $updateData["ConsumeIntegral"];
                        $cartNewRecord['giveintegral'] = $updateData["GiveIntegral"];
                        $cartNewRecord['shopcartsum'] = Db::name('shoppingcart')->where("status=1 and agentd='" . $membername . "'")->sum('shopprice');
                        $cartNewRecord['shoppvsum'] = Db::name('shoppingcart')->where("status=1 and agentd='" . $membername . "'")->sum('ConsumeIntegral');
                        $cartNewRecord['shopgivepvsum'] = Db::name('shoppingcart')->where("status=1 and agentd='" . $membername . "'")->sum('GiveIntegral');
                        $returnData['data'] = $cartNewRecord;
                        $returnData['status'] = 0;
                        $returnData['msg'] = '数量减少成功';
                    }
                } else {

                    $returnData['status'] = 5;
                    $returnData['msg'] = '购物车中无此商品';
                }
            } else {

                $returnData['status'] = 4;
                $returnData['msg'] = '非法操作';
            }
        } else {

            $returnData['status'] = 99;
            $returnData['msg'] = '未登录';
        }
        return json($returnData);
    }

    /**
     * 清空已登录会员购物车中的所有商品
     * 请求实例：http://www.XX.com/app.php/member/empty_cart
     * @return $returnData['status']  status 删除状态
     * @return $returnData['msg']  msg 删除提示
     */

    public function emptyCart()
    {
        $info = $this->islogin();//判断会员是否登录
        if ($info['status'] == 0) {
            $userid = $info['data']['UserId'];
            $agentd = $userid;
            Db::name('shoppingcart')->where("Agentd='" . $agentd . "'")->delete();
            $returnData['status'] = 0;
            $returnData['msg'] = '已删除';
        } else {
            $returnData['status'] = 99;
            $returnData['msg'] = '未登录';
        }
        return json($returnData);
    }


}
