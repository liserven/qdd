<?php
header('Content-Type:application/json; charset=utf-8');
// $file_name="cookie.jpg";
// $file_name="hc2688App.APK";
// //用以解决中文不能显示出来的问题
// //$file_name=iconv("utf-8","gb2312",$file_name);

// $file_path=__DIR__."/hc2688App.APK";

// //首先要判断给定的文件存在与否
// if(!file_exists($file_path)){
// 	echo "没有该文件文件";
// 	return ;
// }
// $fp=fopen($file_path,"r");
// $file_size=filesize($file_path);

// //下载文件需要用到的头
// Header("Content-type: application/octet-stream");
// Header("Accept-Ranges: bytes");
// Header("Accept-Length:".$file_size);
// Header("Content-Disposition: attachment; filename=".$file_name);
// $buffer=1024;
// $file_count=0;
// //向浏览器返回数据
// while(!feof($fp) && $file_count<$file_size){
// 	$file_con=fread($fp,$buffer);
// 	$file_count+=$buffer;
// 	echo $file_con;
// }
// fclose($fp);
$data=array();
$data['versionCode']='2.8';
$data['versionName']='19';
$data['status']='0';
$data['auto']='0';
$data['msg']="成功";
die(json_encode($data));
?>