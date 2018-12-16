<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/6/20
 * Time: 11:15
 */

namespace api\index\controller;

use think\Db;
use think\Session;
use think\Image;
use Qiniu\Auth as Aath;
use Qiniu\Storage\BucketManager;
use Qiniu\Storage\UploadManager;
class ProductPhoto extends Auth
{
    /***
     *   $proimg 图片信息  上传原图  主图 缩略图
     *
     */
    public function product_photo_action($proimg=null,$action=null,$proId=null){

        if($action=='add'){
            $SupplierId=Session::get("supplierid");

            //获取新添加的商品的id（因为在商品表中ProId为自增列a）
            $sql="SHOW TABLE STATUS LIKE 'product'";
            $ProidData=Db::query($sql);
            $ProId=$ProidData[0]['Auto_increment'];
        }else{
            $SupplierInfo=Db::name("product")->where("ProId=".$proId)->field('SupplierId')->find();
            $SupplierId=$SupplierInfo['SupplierId'];
            $ProId=$proId;
        }

        //原始图路径 public\Upload\cpimg\product_photo\商家id\商品编号\original\年月\图片名称
        $productPhotoOriginalPath=ROOT_PATH . 'public' . DS . 'Upload'. DS .'cpimg'.DS.'product_photo'.DS.$SupplierId.DS.$ProId.DS.'original';
        //主图路径 public\Upload\cpimg\product_photo\商家id\商品编号\main\图片名称
        $productPhotoMainPath=ROOT_PATH . 'public' . DS . 'Upload'. DS .'cpimg'.DS.'product_photo'.DS.$SupplierId.DS.$ProId.DS.'main';
        //缩略图路径 public\Upload\cpimg\product_photo\商家id\商品编号\thumb\图片名称
        $productPhotoThumbPath=ROOT_PATH . 'public' . DS . 'Upload'. DS .'cpimg'.DS.'product_photo'.DS.$SupplierId.DS.$ProId.DS.'thumb';

        //保存原始图
        $info = $proimg->validate(['size'=>1024000*5,'ext'=>'jpg,png,gif,jpeg'])->move($productPhotoOriginalPath);

        $date='';
        if($info){
            /***
             * 生成缩略图并上传
             */
            //获取原始图保存图片名称
            $filename=$info->getFilename();//38d206ad4c68d18e6aed9201c449acb6.jpg
            //打开图片
            $image = Image::open($info);
            //生成缩略图并保存 保存路径 public\Upload\cpimg\Product_main_800\商家id\商品编号
            $MainPath=$productPhotoMainPath.DS.$filename;//缩略图保存的路径（例如E:\workcode\feidukehu\php\sanjifenxiao\public\Upload\cpimg\Product_main_800\1\111\577d755c8a36bc6a6066ca5751f21fe0.jpg）
            $MainInfo=$image->thumb(800, 800)->save($MainPath,$productPhotoMainPath);

            $thumbPath=$productPhotoThumbPath.DS.$filename;//缩略图保存的路径（例如E:\workcode\feidukehu\php\sanjifenxiao\public\Upload\cpimg\Product_main_800\1\111\577d755c8a36bc6a6066ca5751f21fe0.jpg）
            $thumbInfo=$image->thumb(240, 240)->save($thumbPath,$productPhotoThumbPath);


            if($thumbInfo && $MainInfo){
                //存入数据库的主图的访问地址
                $DataMainPath='product_photo/'.$SupplierId.'/'.$ProId.'/main/'.$filename;
                //存入数据库的缩略图的访问地址
                $DataThumbPath='product_photo/'.$SupplierId.'/'.$ProId.'/thumb/'.$filename;

                $date['proimgpath']=$DataThumbPath;
                $date['proMaximgpath']=$DataMainPath;
//                $returndata=$this->uploadfile($info);
//                if($returndata['status']==0){
//                    $date['qiqiuproimgpath']=$returndata['img'].'?imageView2/0/w/240/h/240';
//                    $date['qiqiuproMaximgpath']=$returndata['img'].'?imageView2/0/w/800/h/800';
//                }else{
                    $date['qiqiuproimgpath']='photo.png';
                    $date['qiqiuproMaximgpath']='photo.png';
//                }
            }
        }
        return $date;
    }
    private function uploadfile($file)
    {
        vendor('qiuqiu.php-sdk.autoload');
        $accessKey = config('ACCESSKEY');
        $secretKey = config('SECRETKEY');
        $bucket = config('BUCKET');
        $domain = config('DOMAIN');
//        print_r($accessKey);
//        print_r($secretKey);
//        print_r($bucket);
//        print_r($domain);exit;
        $filePath = $file->getRealPath();
        $ext = pathinfo($file->getInfo('name'), PATHINFO_EXTENSION);  //后缀
        $key =substr(md5($file->getRealPath()) , 0, 5). date('YmdHis') . rand(0, 9999) . '.' . $ext;
        $auth = new Aath($accessKey, $secretKey);
        $token = $auth->uploadToken($bucket);
        $uploadMgr = new UploadManager();

        list($ret, $err) = $uploadMgr->putFile($token, $key, $filePath);

        if ($err !== null) {
            $return['status']=1;
            $return['msg']='上传错误';
        } else {
            $return['img']=$domain.$ret['key'];
            $return['status']=0;
            $return['msg']='上传成功';
        }
        return $return;
    }
}