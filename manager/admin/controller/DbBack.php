<?php
namespace app\admin\controller;
\think\Loader::import('controller/Jump', TRAIT_PATH, EXT);

use app\admin\Controller;
use think\Session;
use think\Db;
use think\Exception;
use think\DbManage;

class DbBack extends Controller
{
    use \traits\controller\Jump;
    public function index(){
        return $this->dbBackup();
    }
    public function dbBackup(){
        $act=$this->request->param('act');

        if($act=='up'){
            $host=config('database.hostname');
            $username=config('database.username');
            $pass=config('database.password');
            $name=config('database.database');
            $str=$this->request->param('name');
            $db = new DbManage($host,$username,$pass,$name);
            $aa=$db->backup($str,"","","");
            if($aa['code']=='200'){
                if(!empty($str)){
                    $data['name']=$str;
                }else{
                    $data['name']=$aa['truename'];
                }
                $data['path']='/db_backup/'.iconv( "gb2312", "utf-8" , $aa['name']);
                $data['addtime']=$aa['addtime'];
                Db::name('db_back')->insert($data);
//                var_dump(Db::name('db_back')->getLastSql());exit;
                return json(['msg'=>'备份成功','code'=>200]);
            }

        }
        $list=Db::name('db_back')->order('addtime desc,id desc')->paginate();
        $this->view->assign('list',$list);
        $this->view->assign('page',$list->render());

        return $this->view->fetch('dbBackup');

    }
    public function db_download(){
        $id=$this->request->param('id');
        $path=Db::name('db_back')->field('path')->where('id='.$id)->find();
        $file_path=$_SERVER['DOCUMENT_ROOT'].$path['path'];
        $ss=explode('/',$path['path']);
        $file_name = $ss[2]; //得到文件名
        $file_name=iconv("utf-8","gb2312",$file_name);
        $file_dir =$_SERVER['DOCUMENT_ROOT']."/db_backup/";
        if (!file_exists($file_dir . $file_name)) { //检查文件是否存在
            echo "文件找不到";
            exit;
        }else {
//            $zip=new \ZipArchive();
//            $filename=date('YmdHis').'.zip';
//            if(!file_exists($filename)) {
//                $zip->open($filename,  ZIPARCHIVE::CREATE);
////                $zip->addFile($path,basename($path));
//
//            }


            $file = fopen($file_dir . $file_name,"r"); // 打开文件
            Header("Content-type: application/octet-stream");
            Header("Accept-Ranges: bytes");
            Header("Accept-Length: ".filesize($file_dir . $file_name));
            Header("Content-Disposition: attachment; filename=" . $file_name);
            echo fread($file,filesize($file_dir . $file_name));
            fclose($file);
            exit;
        }






    }
}