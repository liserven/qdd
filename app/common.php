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
function contentReplace($content){
    $content=str_replace('src="','src="'.Config::get('DOMAIN').'',$content);
    return $content;
}
/**
 * 根据商家id获取商家名称
 *
 */
function getsuppliername($id)
{
    $data=Db::name('supplier')->where('ID='.$id)->field('Name')->find();
    return $data['Name'];
}
/**
 * 根据商家名称判断商家是否存在
 *
 */
function getsuppliernamebyname($name)
{
    $data=Db::name('supplier')->where('Name='.$name)->field('Name')->find();
    if($data){
        return true;
    }else{
        return false;
    }
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

/**
 * @param $phonenumber
 * @return false|int
 */
function is_mobile($phonenumber){
    $pattern = '/1[34578]{1}\d{9}$/';
    return preg_match($pattern,$phonenumber);
}

/**
 * @param $userId
 * @return string
 */
function checkUserIdExists($userId){
    $where['userId']=$userId;
    $count = Db::name('usermsg')->where($where)->count();//获取满足条件的总记录数
    if($count==0){
        return 'false';
    }else{
        return 'true';
    }
}

/**
 * 产品浏览量接口
 * 请求方式：http://www.XXX.com/goods/hit?proid=7671
 * 请求方式：get
 * 请求参数：
 * @param proid 产品id
 * @return \think\response\Json 浏览量
 */
function product_hit($proid){
    $where['ProId']=$proid;
    $sql='update product set hit=hit+1 where ProId='.$proid;
    Db::execute($sql);
}


function jh_key(){

    $data = Db::name('jh_code')->where('id=1')->find();
    return $data['key'];

}

/*
验证码随机数
 */
function generate_code() {

    $arr=range(1,9);
    shuffle($arr);
    $ar=range(2,7);
    shuffle($ar);
    $array=range(3,6);
    shuffle($array);
    $arra=range(5,8);
    shuffle($arra);
    foreach ($arr as $key => $value) {
        foreach ($ar as $k => $v) {
            foreach ($array as $ke => $va) {
                foreach ($arra as $kt => $val) {
                    $tpl_value ="#code#={$value}{$va}{$v}{$val}";
                    return $tpl_value;

                }
            }


        }
    }

}

/*
   *模拟提交短信
    */

function juhecurl($url,$params=false,$ispost=0){
    $httpInfo = array();
    $ch = curl_init();
    curl_setopt( $ch, CURLOPT_HTTP_VERSION , CURL_HTTP_VERSION_1_1 );
    curl_setopt( $ch, CURLOPT_USERAGENT , 'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.22 (KHTML, like Gecko) Chrome/25.0.1364.172 Safari/537.22' );
    curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT , 30 );
    curl_setopt( $ch, CURLOPT_TIMEOUT , 30);
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER , true );
    if( $ispost )
    {
        curl_setopt( $ch , CURLOPT_POST , true );
        curl_setopt( $ch , CURLOPT_POSTFIELDS , $params );
        curl_setopt( $ch , CURLOPT_URL , $url );
    }
    else
    {
        if($params){
            curl_setopt( $ch , CURLOPT_URL , $url.'?'.$params );
        }else{
            curl_setopt( $ch , CURLOPT_URL , $url);
        }
    }
    $response = curl_exec( $ch );
    if ($response === FALSE) {
        //echo "cURL Error: " . curl_error($ch);
        return false;
    }
    $httpCode = curl_getinfo( $ch , CURLINFO_HTTP_CODE );
    $httpInfo = array_merge( $httpInfo , curl_getinfo( $ch ) );
    curl_close( $ch );
    return $response;
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