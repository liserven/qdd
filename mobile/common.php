<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件

/**
 * 模拟tab产生空格
 * @param int $step
 * @param string $string
 * @param int $size
 * @return string
 */
use think\Config;
use think\Db;

function tab($step = 1, $string = ' ', $size = 4)
{
    return str_repeat($string, $size * $step);
}


//https请求（支持GET和POST）
function http_request($url, $data = null)
{
    $curl = curl_init();
    curl_setopt ($curl, CURLOPT_HTTPHEADER, array (
        'outid: 0000000000000000'
    ) );
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
    if (!empty($data)){
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    }
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($curl);
    curl_close($curl);
    return $output;
}



function echowebprice($proid){
    $ckinfo=Db::table('product')->where("proid=".$proid)->find();
    $ckinfo=array_change_key_case($ckinfo,CASE_LOWER);
    $psizelist=Db::table('productstock')->where("proid=".$proid)->select();
    $psizelist=indexToLower($psizelist,CASE_LOWER);
    foreach($psizelist as $n=>$val){
        $pkucun=Db::table('productstock')->where("styleid=".$val["styleid"])->field('Kucun')->find();
        if($pkucun){
            $ppkucun=$pkucun["Kucun"];
        }else{
            $ppkucun=0;
        }
        echo "\"".$val["styleid"]."\":{\"pstock\":\"".$ppkucun."\",\"fenxice\":\"".$ckinfo["rebates"]."\",\"adice\":\"".$ckinfo["repeatadvertising"]."\",\"nprice\":\"".$ckinfo["vipprice"]."\",\"fenice\":\"".$ckinfo["deducepv"]."\",\"price\":\"".$ckinfo["memberprice"]."\",\"mktprice\":\"".$ckinfo["marketprice"]."\"},";
    }
}

/**
 * 根据产区域D获取区域名称
 * @param  $ishit
 * @return  区域名称
 */

function echoishitbyid($ishit,$supid=''){ //通过区域id获得区域  1 创客空间 2 劲暴抢购 3 进口商品 4 飞度优品 5 重复消费区
    if($ishit==1){
        return "创客空间";
    }elseif($ishit==2){
        return "劲爆抢购";
    }elseif($ishit==3){
        return "进口商品";
    }elseif($ishit==4){
        return "飞度优品";
    }elseif($ishit==5){
        return "重复消费区";
    }elseif($ishit==6){
        return "积分兑购区";
    }elseif($ishit==8){
        return "产品搜索";
    }elseif($ishit==9){
        $supdata=Db::table('supplier')->where('ID='.$supid)->field('Name as name')->find();
        return $supdata['name'];
    }
}

/**
 * 根据商家id获取商家名称
 * @param $supid    商家id
 * @return mixed    商家名称
 */

function getsupnamebysupid($supid){ //根据供应商ID获得产品供应商name
    $cpname=Db::table('supplier')->where("id=".$supid)->field('Name as name')->find();
    return $cpname["name"];
}

/**
 * 把数组集合中的字符索引都转换为小写
 * @param $data 数组
 * @return mixed  字符索引为小写的数组
 */
function indexToLower($data){
    foreach ($data as $key=>$value){
        $data[$key]=array_change_key_case($value,CASE_LOWER);
    }
    return $data;
}

function getsupidbycpid($proid){ //根据产品ID获得产品供应商id
    $cpname=Db::table('product')->where("proid=".$proid)->field('SupplierId as supplierid')->find();
    return $cpname["supplierid"];
}

/**
 * 数字前缀加零
 * @param $data 数字
 * @return string   小于零的数字前缀加零
 */
function addzero($data){ //数字小于1时 在小数点前补0
    if($data<1){
        $data="0".$data;
    }else{
        $data=$data;
    }
    return $data;
}

/**
 * 内容替换
 * @param $content  包含图片链接地址的内容
 * @return mixed    图片链接地址替换为附加图片服务器地址的内容
 */

function contentReplace($content){
    $content=str_replace('src="','src="'.Config::get('DOMAIN').'',$content);
//	$content=str_replace('<img alt="" src="http://hc2688.com','<img src="'.Config::get('IMAGE_DOMAIN_NAME'),$content);
//   var_dump(Config::get('IMAGE_DOMAIN_NAME'));exit;
    return $content;
}


function getproimgbycpid($proid){ //根据产品ID获得产品主图
	$cpname=Db::table('product')->where("proid=".$proid)->field('ProImg as proimg')->find();
	return $cpname["proimg"];
}

function getcpnamebycpid($proid){//根据产品ID获得产品名称
    $cpname=Db::table('product')->where("proid=".$proid)->field('ProName as proname')->find();
    return $cpname["proname"];
}

function getproimgbysid($psid){ //根据产品规格获得产品主图
    $psizetxm=Db::name("productstock")->where("styleid=".$psid)->field('Proid as proid')->find();
    if($psizetxm) {
        $proid = $psizetxm["proid"];
        $cpname = Db::table('product')->where("proid=" . $proid)->field('ProImg as proimg,qiqiuproimgpath')->find();
//        var_dump(is_onqiniu());exit;
        if(is_onqiniu()==true){

            $img= $cpname["qiqiuproimgpath"];
        }else{
            $img= config('IMAGE_DOMAIN_NAME').'/public/Upload/cpimg/'.$cpname["proimg"];
        }
        return $img;
    }
}

function getexpbyid($id){ //通过ID获得物流信息
	$expxx=Db::table('expresscompany')->where("yong=".$id)->field('expname')->find();
	if($expxx){
		return $expxx["expname"];
	}	
}

function getorderstep($proid){ // 获取产品起订步长
    $prodata=Db::name('product')->where("proid=".$proid)->field('OrderStep as orderstep')->find();
    return $prodata["orderstep"];
}

function kucunaccount($proid,$psizetxm,$kcstyle){ //根据产品ID和规格参数ID获得对应的库存或者未发货订单
    $kccount=Db::name("productstock")->where("proid=".$proid." and styleid=".$psizetxm)->field('kucun,kucunweifahuo')->find();
    if($kccount){
        if($kcstyle="kkcc"){
            return $kccount["kucun"];
        }elseif($kcstyle="kkccww"){
            return $kccount["kucunweifahuo"];
        }
    }else{
        return 0;
    }
}

function getzxqdlbysid($psid){ //通过产品规格获得对应的最小起订量
    $psizetxm=Db::name("productstock")->where("styleid=".$psid)->field('Proid as proid')->find();
    if($psizetxm){
        $proid=$psizetxm["proid"];
        $zxqdl=Db::name("product")->where("proid=".$proid)->field('minpurchase')->find();
        return $zxqdl["minpurchase"];
    }
}


function getcppriceobysid($psid){ //通过规格参数获得对应的产品价格
    $psizetxm=Db::name("productstock")->where("styleid=".$psid)->field('Proid as proid')->find();
    if($psizetxm){
        $proid=$psizetxm["proid"];
        $zxqdl=Db::name("product")->where("proid=".$proid)->field('VipPrice as vipprice')->find();
        return $zxqdl["vipprice"];
    }
}

function getcppvobysid($psid){ //通过规格参数获得对应的产品扣除积分
    $psizetxm=Db::name("productstock")->where("styleid=".$psid)->field('Proid as proid')->find();
    if($psizetxm){
        $proid=$psizetxm["proid"];
        $zxqdl=Db::name("product")->where("proid=".$proid)->field('DeducePv as deducepv')->find();
        return $zxqdl["deducepv"];
    }
}
//判断是否开启七牛云图片
function is_onqiniu()
{
    $where['id'] = 1;
    $data = Db::name('otherconfig')->where($where)->find();//获取满足条件的总记录数
    if ($data['img_on']==0) {
        return false;
    } else {
        return true;
    }
}
//查询系统参数
function otherconfig(){
    $where['id'] = 1;
    $data = Db::name('otherconfig')->where($where)->find();//获取满足条件的总记录数
    return $data;
}

function getTypenameByUserId($type){ // 根据类型取出类型名称
    $wheret['ID'] = $type;
    $typeinfo = Db::name('usertype')->where($wheret)->find();
    return $typeinfo["Name"];
}

