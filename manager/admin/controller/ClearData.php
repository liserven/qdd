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

class ClearData extends Controller
{
    use \traits\controller\Jump;

    public function index()
    {
        return $this->cleardataList();
    }
    //级别列表的显示
    public  function cleardataList(){
        return $this->view->fetch('cleardata');
    }


    public function Clearuserandprize()
    {
        $sql="delete from usermsg where userid<>'00001' ;";
        Db::execute($sql);
        $sql1="update usermsg set one_level_places=0,two_level_places=0,three_level_places=0,four_level_places=0;";
        Db::execute($sql1);

        $sql1 = "truncate table web_log_001";
        Db::query($sql1);
        $sql2 = "truncate table web_log_all";
        Db::query($sql2);
        $sql3 = "truncate table pointsflow";
        Db::query($sql3);
        $sql4 = "truncate table accountrecord";
        Db::query($sql4);
        $sql5 = "truncate table login_log";
        Db::query($sql5);
        $sql6 = "truncate table orderdetail";
        Db::query($sql6);
        $sql7 = "truncate table ordermain";
        Db::query($sql7);
        $sql8 = "truncate table orderpayrecord";
        Db::query($sql8);
        $sql9 = "truncate table userloginlog";
        Db::query($sql9);
        $sql10 = "truncate table withdrawcash";
        Db::query($sql10);
        $sql11 = "truncate table userrecommenddiagram";
        Db::query($sql11);
        $sql12 = "truncate table comreceiveinfo";
        Db::query($sql12);
        $sql13 = "truncate table order_places";
        Db::query($sql13);
        $sql14 = "truncate table accountplaces";
        Db::query($sql14);
//        $sql13 = "delete from usermsg where userid <>'00001'";
//        Db::query($sql13);
//        $sql14 = "update usermsg set Umoney=0,Pv=0,UserPrize=0";
//        Db::query($sql14);
        $returnData['status'] = 1;
        $returnData['msg'] = '删除成功！';

        return json($returnData);
    }
}
