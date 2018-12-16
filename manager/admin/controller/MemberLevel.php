<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/2/28
 * Time: 15:40
 */

namespace app\admin\controller;

\think\Loader::import('controller/Jump', TRAIT_PATH, EXT);

use app\admin\Controller;
use think\Db;

class MemberLevel extends Controller
{
    use \traits\controller\Jump;

    public function index(){
        return $this->memberLevelList();
    }

    //级别列表的显示
    public  function memberLevelList(){

        $count=Db::name('usertype')->order('ID ASC')->count();//获取满足条件的总记录数
        $list=Db::name('usertype')->order('ID ASC')->paginate();//根据条件分页输出

        $this->view->assign('memberLevelList',$list);
        $this->view->assign('count',$count);
        return $this->view->fetch('memberLevelList');
    }

    //级别删除
    public function memberLevelDel(){
        $ID=$this->request->param();
        if($ID['ID']) {
            if (is_array($ID['ID'])) {//批量删除
                foreach ($ID['ID'] as $id) {
                    Db::name('usertype')->where('id='.$id)->delete();
                }
            } else {//针对单个商品的删除
                Db::name('usertype')->where('id='.$ID['ID'])->delete();
            }
            $returnData['status']=1;
            $returnData['msg']='删除成功！';
        }else{
            $returnData['status']=0;
            $returnData['msg']='删除失败！';
        }
        return json($returnData);
    }

    //级别添加
    public function memberLevelAdd(){
        $act=$this->request->param('act');
        if($act=='add'){
            $memberData=$this->request->param();
            $data["Name"]=$memberData["name"];
            $data["LessMoney"]=$memberData["lessmoney"];
            $data["AddDate"]=date('Y-m-d H:i:s');
            $memberId=Db::name('usertype')->insertGetId($data);//添加数据并获得此数据在数据库中的id
            if($memberId){
                return json(['status'=>1,'msg'=>'会员级别添加成功']);
            }
        }elseif($act=='show'){
            return $this->view->fetch('memberLevelAdd');
        }
    }

    //级别修改
    public function memberLevelEdit(){
        $id=$this->request->param('id');
        $act=$this->request->param('act');
        if($act=='show'){//会员级别信息编辑页面的显示
            $memberLevelList=array_change_key_case(Db::name('usertype')->where("id=".$id)->find());
            $this->view->assign("list",$memberLevelList);
            return $this->view->fetch('memberLevelEdit');
        }elseif($act=='edit') {//会员级别信息的编辑
            $memberLevelData=$this->request->param();
            $data["Name"]=$memberLevelData["Name"];
            $data["buy_price"]=$memberLevelData["buy_price"];
            $data["annualfee_price"]=$memberLevelData["annualfee_price"];
            $data["discount"]=$memberLevelData["discount"];
            $data["one_level_places"]=$memberLevelData["one_level_places"];
            $data["two_level_places"]=$memberLevelData["two_level_places"];
            $data["three_level_places"]=$memberLevelData["three_level_places"];
            $data["four_level_places"]=$memberLevelData["four_level_places"];
            Db::name('usertype')->where("id=".$memberLevelData["id"])->update($data);//修改数据库
            return json(['status'=>1,'msg'=>'会员级别修改成功']);
        }
    }
}