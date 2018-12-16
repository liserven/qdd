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
use think\Config;
use think\Db;

/**
 * @param $content  替换内容
 * @return mixed    返回使用设定的域名替换后的内容
 */
//function contentReplace($content)
//{
//    //$content=str_replace('<img src="/ueditor/php/upload/','<img src="'.C('IMAGE_DOMAIN_NAME').'/ueditor/php/upload/',$content);
//    $content = str_replace('src="/ueditor/php/upload/', 'src="' . Config::get('IMAGE_DOMAIN_NAME') . '/ueditor/php/upload/', $content);
//    $content = str_replace('<img alt="" src="http://hc2688.com', '<img src="' . Config::get('IMAGE_DOMAIN_NAME'), $content);
//    return $content;
//}
function getcityname($code){
    $where['Code']=$code;
    $name=Db::name('provincecitycounty')->where($where)->field('AreaName')->find();
    return $name['AreaName'];
}
function contentReplace($content){
    $content=str_replace('src="','src="'.Config::get('DOMAIN').'',$content);

    return $content;
}

/**
 * 根据产区域D获取区域名称
 * @param  $ishit
 * @return  区域名称
 */
function echoishitbyid($ishit)
{ //通过区域id获得区域  1 创客空间 2 劲暴抢购 3 进口商品 4 {:otherconfig()['companyName']}优品 5 重复消费区
    if ($ishit == 1) {
        return "创客空间";
    }
    if ($ishit == 2) {
        return "劲爆抢购";
    }
    if ($ishit == 3) {
        return "进口商品";
    }
    if ($ishit == 4) {
        $a=otherconfig();
        return $a['companyName']."优品";
    }
    if ($ishit == 5) {
        return "重复消费区";
    }
    if ($ishit == 6) {
        return "积分兑购区";
    }
}

/**
 * 验证是否有访问接口的权限
*/
function checkOutId($outId){
    $where['outId']=$outId;
    $count = Db::name('apiusers')->where($where)->count();//获取满足条件的总记录数
    if($count==0){
        return 'false';
    }else{
        return 'true';
    }
}
function getTypeNameByTypeId($typeId){ //根据商家分类ID获得分类名称
    $cateT=Db::name('suppliertype')->where("ID=".$typeId)->find();
    return $cateT["SupplierTypeName"];
}

/**
 * 把数组集合中的字符索引都转换为小写
 * @param $data 数组
 * @return mixed  字符索引为小写的数组
 */
function indexToLower($data)
{
    foreach ($data as $key => $value) {
        $data[$key] = array_change_key_case($value, CASE_LOWER);
    }
    return $data;
}

function ismobile()
{
    // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
    if (isset ($_SERVER['HTTP_X_WAP_PROFILE']))
        return true;

    //此条摘自TPM智能切换模板引擎，适合TPM开发
    if (isset ($_SERVER['HTTP_CLIENT']) && 'PhoneClient' == $_SERVER['HTTP_CLIENT'])
        return true;
    //如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
    if (isset ($_SERVER['HTTP_VIA']))
        //找不到为flase,否则为true
        return stristr($_SERVER['HTTP_VIA'], 'wap') ? true : false;
    //判断手机发送的客户端标志,兼容性有待提高
    if (isset ($_SERVER['HTTP_USER_AGENT'])) {
        $clientkeywords = array(
            'nokia', 'sony', 'ericsson', 'mot', 'samsung', 'htc', 'sgh', 'lg', 'sharp', 'sie-', 'philips', 'panasonic', 'alcatel', 'lenovo', 'iphone', 'ipod', 'blackberry', 'meizu', 'android', 'netfront', 'symbian', 'ucweb', 'windowsce', 'palm', 'operamini', 'operamobi', 'openwave', 'nexusone', 'cldc', 'midp', 'wap', 'mobile'
        );
        //从HTTP_USER_AGENT中查找手机浏览器的关键字
        if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
            return true;
        }
    }
    //协议法，因为有可能不准确，放到最后判断
    if (isset ($_SERVER['HTTP_ACCEPT'])) {
        // 如果只支持wml并且不支持html那一定是移动设备
        // 如果支持wml和html但是wml在html之前则是移动设备
        if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
            return true;
        }
    }
    return false;
}


//判断手机号是否存在
function checkMobileExists($mobile){
    $where['Mobile']=$mobile;
    //$where['isAudit']=1;
    $count = Db::name('usermsg')->where($where)->count();//获取满足条件的总记录数
    if($count==0){
        return 'false';
    }else{
        return 'true';
    }
}
//判断会员是否存在
function checkUserIdExists($userId)
{
    $where['userId'] = $userId;
    $where['isAudit'] = 1;
    $count = Db::name('usermsg')->where($where)->count();//获取满足条件的总记录数
    if ($count == 0) {
        return 'false';
    } else {
        return 'true';
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
function getcurl($url,$data){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $datas = curl_exec($ch);
    curl_close($ch);
    $datas=json_decode($datas,true);
    return $datas;
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
