<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/6/7
 * Time: 15:55
 */


namespace app\admin\controller;

\think\Loader::import('controller/Jump', TRAIT_PATH, EXT);

use app\admin\Controller;
use think\Db;

class Databack extends Controller
{
    use \traits\controller\Jump;

    public function index()
    {
        return $this->DataBackList();
    }
    //级别列表的显示
    public  function DataBackList(){
        return $this->view->fetch('databack');
    }
    public function Data_Back_List()
    {

        $cfg_dbname = 'sanjifenxiao_xiaofan';
        $filename=date("Y-m-d_H-i-s")."-".$cfg_dbname.".sql";
// 所保存的文件名
        header("Content-disposition:filename=".$filename);
        header("Content-type:application/octetstream");
        header("Pragma:no-cache");
        header("Expires:0");
// 获取当前页面文件路径，SQL文件就导出到此文件夹内
        $tmpFile = (dirname(__FILE__))."\\"."db\\".$filename;
// 用MySQLDump命令导出数据库
        exec("mysqldump -uroot -proot -h192.168.0.180 --default-character-set=utf8 $cfg_dbname > ".$tmpFile);
        $file = fopen($tmpFile, "r"); // 打开文件
        if(fread($file,filesize($tmpFile))){
            fclose($file);
            $returnData['status'] = 1;
            $returnData['msg'] = '备份成功！';
            return json($returnData);
        }else{
            $returnData['status'] = 2;
            $returnData['msg'] = '备份失败！';
            return json($returnData);
        }
    }
}
