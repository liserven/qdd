<?php
use think\Config;
use think\Db;
use think\Session;

function echoishitbyid($ishit,$supid=''){ //通过区域id获得区域  1 创客空间 2 劲暴抢购 3 进口商品 4 和创优品 5 重复消费区

    if($ishit==1){
        return "标准区";
    }elseif($ishit==2){
        return "尊享区";
    }elseif($ishit==3){
        return "复消区";
    }elseif($ishit==4){
        return "商城";
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
function echoishitbyid1($proid){ //通过区域id获得区域  1 创客空间 2 劲暴抢购 3 进口商品 4 和创优品 5 重复消费区
//    var_dump($ishit);exit;
    $hit=Db::name('product')->where('proid='.$proid)->field('ishit')->find();
    $ishit=$hit['ishit'];
    if($ishit==1){
        return "标准区";
    }elseif($ishit==2){
        return "尊享区";
    }elseif($ishit==4){
        return "商城";
    }
}

function supnamebysupid($supid){ //查看商家名称通过商家id
	$where['ID']=$supid;
	$sup=\think\Db::table('supplier')->where($where)->field('Name')->find();
	return $sup["Name"];
}

function getpcnamebyid($id){ //根据产品栏目ID获得对应栏目名称
	if($id!=""){
		$where['Id']=$id;
		$cate=\think\Db::table('productcategory')->where($where)->field('name')->find();
		return $cate["name"];
	}
}


function getcpnamebycpid($proid){//根据产品ID获得产品名称
	$cpname=Db::table('product')->where("proid=".$proid)->field('ProName as proname')->find();
	return $cpname["proname"];
}

function getsalenumproid($proid){ //统计产品的总共销售量 以确定收货后为准
	//$pronum=Db::name('salepronum')->where("ProId=".$proid)->field('pronumz')->find();
	//return $pronum['pronumz'];
	return '12';
}

function contentReplace($content){
	$content=str_replace('src="','src="'.Config::get('DOMAIN').'',$content);
//	$content=str_replace('<img alt="" src="http://hc2688.com','<img src="'.Config::get('IMAGE_DOMAIN_NAME'),$content);
	return $content;
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
		echo "\"".$val["styleid"]."\":{\"pstock\":\"".$ppkucun."\",\"nprice\":\"".$ckinfo["vipprice"]."\",\"fenice\":\"".$ckinfo["consumeintegral"]."\",\"mktprice\":\"".$ckinfo["marketprice"]."\"},";
	}
}

function addzero($data){ //数字小于1时 在小数点前补0
	if($data<1){
		$data="0".$data;
	}else{
		$data=$data;
	}
	return $data;
}

function indexToLower($data){
    foreach ($data as $key=>$value){
        $data[$key]=array_change_key_case($value,CASE_LOWER);
    }
    return $data;
}


function cpcount($isonsell){ //获得不同在线状态产品的数量
	$condition["SupplierId"]=Session::get("supplierid");
	if($isonsell!=""){
		$condition["IsOnSell"]=$isonsell;
	}
	$cpcount=Db::name("product")->where($condition)->count();
	return $cpcount;
}
function ordercount($status){ //获得不同状态订单的数量
	$condition["SupplierId"]=Session::get("supplierid");
	$condition["Status"]=$status;
	$ordercount=Db::name("ordermain")->where($condition)->count();
	return $ordercount;
}

function echotubiaocate($sort,$pid){ //根据产品栏目ID和pid输出对应图标
	$cateT=Db::name("productcategory")->where("sort>".$sort." and pid=".$pid)->order("sort asc")->find();
	if($cateT){
		return "├─";
	}else{
		return "└─";
	}
}

function getsortbypcateid($pcateid){ //根据栏目ID得到栏目sort
	$cateT=Db::name("productcategory")->where("Id=".$pcateid)->find();
	if($cateT){
		return $cateT["sort"];
	}
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
            if(strpos($cpname['proimg'],'http://')!==false){
                $img=$cpname['proimg'];//相册合集
            }else{
                $img=config('IMAGE_DOMAIN_NAME').'/public/Upload/cpimg/'.$cpname['proimg'];//相册合集
            }
        }
        return $img;
    }
}

function getunitbysid($psid){ //通过规格参数获得对应的产品单位
    $psizetxm=Db::name("productstock")->where("styleid=".$psid)->field('proid')->find();
    if($psizetxm){
        $proid=$psizetxm["proid"];
        $zxqdl=Db::name("product")->where("proid=".$proid)->field('unit')->find();
        return $zxqdl["unit"];
    }
}


function getsupnamebysupid($supid){ //根据供应商ID获得产品供应商name
    $cpname=Db::table('supplier')->where("id=".$supid)->field('Name as name')->find();
    return $cpname["name"];
}

function getMobilebysupid($supid){ //根据供应商ID获得产品供应商name
    $cpname=Db::table('supplier')->where("id=".$supid)->field('Mobile as Mobile')->find();
    return $cpname["Mobile"];
}

function getunitbycpid($proid){ //根据产品id获得产品unit
    $cpname = Db::table('product')->where("proid=" . $proid)->field('Unit as unit')->find();
    return $cpname["unit"];
}


function ordertypeby($allianceinfo,$isrep){ //根据订单类型id获得订单类型名称
    if($allianceinfo==1){
        return "注册区";
    }
    if($allianceinfo==2){
        return "复消区";
    }
    if($allianceinfo==3){
        return "商城订单";
    }
    if($allianceinfo==2 and $isrep==1){
        return "复消订单";
    }

}


function gettruenamebyuserid($userid){ //通过userid获得truename
    $truename=Db::name('usermsg')->where("userid='".$userid."'")->field('TrueName as truename')->find();
    if($truename){
        return $truename["truename"];
    }else{
        return "平台推荐";
    }
}

function getexpbyid($id){ //通过ID获得物流信息
    $expxx=Db::name('expresscompany')->where("id=".$id)->field('expname')->find();
    if($expxx){
        return $expxx["expname"];
    }
}

function GetSSRecordsZuiHouEndDate($supid){ //根据商家id获取某个商家结算记录表SupplierSettlementRecords中最后的一次结算记录的结束时间
    $supsetrecordxx=Db::name('suppliersettlementrecords')->field("enddate,adddate")->where("supplierid=".$supid)->order("adddate desc")->find();
    if(!$supsetrecordxx){
        return 0;
    }else{
        return $supsetrecordxx["enddate"];
    }
}

function supplierisdepot($supid){ //查看商家是否被托管
    $sup=Db::name('supplier')->where("id=".$supid)->field('isdepot')->find();
    return $sup["isdepot"];
}



function getmobilebyuserid($userid){ //通过userid获得mobile
    $truename=Db::name("usermsg")->where("userid='".$userid."'")->field("mobile")->find();
    return $truename["mobile"];
}

function getsupmobilebysupid($supid){ //根据供应商ID获得产品供应商mobile
    $cpname=Db::name("supplier")->where("id=".$supid)->field("mobile")->find();
    return $cpname["mobile"];
}

function getexpnamebyid($expid){ //根据快递公司ID获得公司名称
    $exp=Db::name("expresscompany")->where("id=".$expid)->field('expname')->find();
    return $exp["expname"];
}

function getpsizetxmbypsid($psid,$getstyle='ppsize'){ //通过规格参数id获得规格名称或者条形码
    $psizetxm=Db::name("productstock")->where("styleid=".$psid)->field('stylename,txm')->find();
    if($psizetxm){
        if($getstyle=="pptxm"){
            return $psizetxm["txm"];
        }
        if($getstyle=="ppsize"){
            return $psizetxm["stylename"];
        }
    }
}

function getpclevelbyid($id){ //根据产品栏目ID获得对应栏目级别
    if($id==0){
        return 1;
    }else{
        $getpclevelbyid=Db::name("productcategory")->where("id=".$id)->field('lay')->find();
        return $getpclevelbyid["lay"];
    }
}

function getpcpidbyid($id){ //根据产品栏目ID获得父级栏目id  也就是pid
    $cateT=Db::name("productcategory")->where("id=".$id)->field('pid')->find();
    return $cateT["pid"];
}

function checksupplierfwok($pcateid,$supid){ //检测商家经营范围是否选中
    $condition["pcateid"]=$pcateid;
    $condition["supid"]=$supid;
    $checksupcat=Db::name("supplierpcat")->where($condition)->find();
    if($checksupcat){
        echo "checked";
    }
}

function getsupbankbysupid($supid,$bankinfo){ //根据供应商ID获得产品供应商银行信息
    $cpname=array_change_key_case(Db::name("supplier")->where("id=".$supid)->find());
    return $cpname[$bankinfo];
}

function getzuihouconfirmdate($supid,$startdate){ //根据供应商ID获得最后结算日期
    $supsetrecordxx=indexToLower(Db::name("suppliersettlementrecords")->where("supplierid=".$supid." and isconfirm=1 and enddate<'".$startdate."'")->order("id desc")->limit(0,1)->select());
    if(empty($supsetrecordxx["confirmdate"])){
        return "查询不到该商家的上次结算日期";
    }else{
        return "该商家上次结算日期".$supsetrecordxx["confirmdate"];
    }
}


function kucunaccount($styleid,$kcstyle){ //根据规格参数ID获得对应的库存或者未发货订单、未付款订单
    $kccount=array_change_key_case(Db::name("productstock")->where("styleid=".$styleid)->find());
    if($kccount){
        if($kcstyle=="kkcc"){
            return $kccount["kucun"];
        }elseif($kcstyle=="kkccww"){
            return $kccount["kucunweifahuo"];
        }elseif($kcstyle=="wfkkccww"){
            return $kccount["kucunweifukuan"];
        }
    }else{
        return 0;
    }

}

function getsupidbycpid($proid){ //根据产品ID获得产品供应商id
    $cpname=Db::name('product')->where("proid=".$proid)->field("supplierid")->find();
    return $cpname["supplierid"];
}

function getcppvobysid($psid){ //通过规格参数获得对应的产品扣除积分
    $psizetxm=Db::name("productstock")->where("styleid=".$psid)->field('Proid as proid')->find();
    if($psizetxm){
        $proid=$psizetxm["proid"];
        $zxqdl=Db::name("product")->where("proid=".$proid)->field('ConsumeIntegral as consumeintegral')->find();
        return $zxqdl["consumeintegral"];
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



/*************
*生成表格函数
*$pram  array[][]   传入参数为一个二维数组
*
*
****************/

function mkexcel($content){

    set_time_limit(0);
    ini_set('memory_limit', '128M');
    $fileName = date('YmdHis', time());
    
    header('Content-Type: application/vnd.ms-execl');
    header('Content-Disposition: attachment;filename="' . $fileName . '.csv"');
   
            //打开php标准输出流
            //以写入追加的方式打开
    $fp = fopen('php://output', 'a');

    // 数据转码
    /*foreach($content as $key => $item) {
        foreach($content[$key] as $key2 => $item2) {
           
             $content[$key][$key2] = iconv('UTF-8', 'GBK', $item2);  
        }
        fputcsv($fp, $content[$key]);
    }*/
    $num = count($content);
    for($i=0;$i<$num;$i++){
        foreach($content[$i] as $key => $item) {
            $content[$i][$key] = iconv('UTF-8', 'GBK', $item);
        }
         //将数据写到标准输出中
        fputcsv($fp, $content[$i]);
    }
    ob_flush();
    flush();
               
    
}

//通过配送区域id查看配送区域
function getbycityname($id){
    $names=Db::view('freigh_to_province')
        ->view('provincecitycounty','AreaName','freigh_to_province.provincecitycounty_id=provincecitycounty.Code')
        ->where('freigh_to_province.freighrnew_Id='.$id)
        ->select();
    $str='';
    foreach ($names as $k=>$v){
        $str.=$v['AreaName'].',';
    }
    $str=rtrim($str,',');
    return $str;
}
//获取区域名称
function getcityname($code){
    $where['Code']=$code;
    $name=Db::name('provincecitycounty')->where($where)->field('AreaName')->find();
    return $name['AreaName'];
}