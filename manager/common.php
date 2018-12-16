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

use think\Db;

// 应用公共文件

/**
 * 模拟tab产生空格
 * @param int $step
 * @param string $string
 * @param int $size
 * @return string
 */
function tab($step = 1, $string = ' ', $size = 4)
{
    return str_repeat($string, $size * $step);
}

function getTypeNameByTypeId($typeId){ //根据商家分类ID获得分类名称
    $cateT=Db::name('suppliertype')->where("ID=".$typeId)->find();
    return $cateT["SupplierTypeName"];
}

function getMobileByTypeId($typeId){ //根据商家分类ID获得分类名称
    $cateT=Db::name('supplier')->where("ID=".$typeId)->find();
    return $cateT["Mobile"];
}

function indexToLower($data){
    foreach ($data as $key=>$value){
        $data[$key]=array_change_key_case($value,CASE_LOWER);
    }
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