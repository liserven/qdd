<?php
$dataconfig=require_once $_SERVER['DOCUMENT_ROOT']."/manager/database.php";   //导入数据库配置文件
$conn=mysqli_connect($dataconfig['hostname'],$dataconfig['username'],$dataconfig['password'],$dataconfig['database']);
$sql="select * from payconfig where id=1 limit 1";
$aaa=mysqli_query($conn,$sql);
$data=$aaa->fetch_assoc();
$appid=$data['aliapp_id'];
$merchant_private_key=$data['merchant_private_key'];
$alipay_public_key=$data['alipay_public_key'];
$url='http://'.$_SERVER['HTTP_HOST'];
$config = array (
	//应用ID,您的APPID。
		'app_id' => $appid,
	//沙箱环境测试APPID
//		'app_id' => "2016082100302049",
	//商户私钥，您的原始格式RSA私钥
		'merchant_private_key' => $merchant_private_key,
		'notify_url' => $url."/api.php/order/alipay_notify",
	//同步跳转
		'return_url' => $url."/mobile.php/order/alipay_return",
	//编码格式
		'charset' => "UTF-8",
	//签名方式
		'sign_type'=>"RSA2",
	//支付宝网关
		'gatewayUrl' => "https://openapi.alipay.com/gateway.do",
//沙箱环境测试
//		'gatewayUrl' => "https://openapi.alipaydev.com/gateway.do",
	//支付宝公钥,查看地址：https://openhome.alipay.com/platform/keyManage.htm 对应APPID下的支付宝公钥。
		'alipay_public_key' => $alipay_public_key
	//沙箱环境公匙
//		'alipay_public_key' => "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAohyU06Hkp+zX6bCgWJebjcp1nN3+M0B1GmmxQGzphHJdVoSFJbMLjFPcYLxkBxaNQQMV3IcARrq3e3u9Da5A2tKoUMZlQHmiuvC4cQG/ZHRswj1T55KCt89np81pdTMj2oxDnkKwQJJbQHWzxYohYPjxpzqvvyiqoqAkVG7U0ACsVUbe6BgREv3cwAwuFhIUFLUTZEhtB2uoPuxBEhRHhFa54lkrEmcw1FRe9JTQ5yf3TR6ExyCFINskfhMAxXVIZPsQLeccpyB3stB0UTWVHlxBoWZ7PuoiXhKaH+O2qkYYy8Oth6rsPtDZh0KDgP+1pr4os2Ozg9PhcEzG8BEVrQIDAQAB"
);