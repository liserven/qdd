<?php
namespace api\index\controller;


use think\Image;

class Thumbnail{
    
    public function thumb($dir_file,$uploadpath='/Upload/cpimg/',$thumbpath='/Resource/WapThumb/cpimg/',$width='200',$height='200'){
        $filename = $uploadpath . $dir_file;
        $thumbimg = $thumbpath . $dir_file;
        $dirname = $_SERVER['DOCUMENT_ROOT'];
        $file = $dirname . $filename;
        if (file_exists($file)) {
            $thumbfile = $dirname . $thumbimg;
            if (! file_exists($thumbfile)) {
                //
                $info = pathinfo($thumbfile);
                $savedir = $info['dirname'];
                
                if (in_array(strtolower($info['extension']), array('jpg','gif','png','jpeg'))) {
                    $image=image::open($file);
                    if (! file_exists($savedir)) {
                        $this->mkDirs1($savedir);
                    }
                    $image->thumb($width, $height)->save($savedir . '/' . $info['basename']);
                }
                //
            }
            return $thumbimg;
        } else {
            return $filename;
        }    
    }
    
    public function mkDirs1($path){
        if(is_dir($path)){//已经是目录了就不用创建
            return true;
        }
        if(is_dir(dirname($path))){//父目录已经存在，直接创建
            return mkdir($path);
        }
        $this->mkDirs1(dirname($path));//从子目录往上创建
        return mkdir($path);//因为有父目录，所以可以创建路径
    }
}
?>
