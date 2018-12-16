<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/9
 * Time: 10:05
 */

namespace app\admin\controller;
\think\Loader::import('controller/Jump', TRAIT_PATH, EXT);

use app\admin\Controller;
use think\Db;

class AdGroup extends Controller
{
    use \traits\controller\Jump;
    public function index(){
      return  $this->adGroupList();
    }
    public function adGroupList(){
        $adgroup=Db::name('adcategory');
        if($this->request->param("action")=="delete") {
            $id=$this->request->param("id");
            $adgroup->where("ID=" .$id )->delete();
        }else {
            $adlist = $adgroup->select();
            $count = count($adlist);  //获取总记录数
            $this->view->assign("count", $count);
            $this->view->assign("listone", $adlist);
            return $this->view->fetch('adGroupList');
        }
    }
    public function adGroupAdd()
    {
        $adgroup = Db::name("adcategory");
        $categoryname=$this->request->param('categoryname');
        if($this->request->param('action')=="add"){
            if(empty($categoryname)){
                echo "catenameerror";
                return;
            }else{
                $where['CategoryName']=$this->request->param('categoryname');
                $name=$adgroup->where($where)->find();
                if($name){
                    echo "catenamere";
                    return;
                }else{
                    $data["CategoryName"]=$categoryname;
                    $data["AddDate"]=date('Y-m-d H:i:s');
                    $adgroup->insert($data);
                }
            }
        } else {
            return $this->view->fetch('adGroupAdd');
        }
    }
    public function adGroupEdit()
    {
        $adgroup = Db::name("adcategory");
        if($this->request->param('action')=='edit'){
            $categoryname = $this->request->param("categoryname");
            if(empty($categoryname)){
                echo "catenameerror";
                return;
            }else{
                $where['CategoryName']=$this->request->param('categoryname');
                $where['ID']=array('neq',$this->request->param('id'));
                $adlist=$adgroup->where($where)->find();
                if($adlist){
                    echo "catenamere";
                    return;
                }else{
                    $data['CategoryName']=$this->request->param("categoryname");
                    $data['ModifyDate']=date('Y-m-d H:i:s');
                    $adgroup->where('ID='.$this->request->param("id"))->update($data);
                }
            }

        }else{
        $where['ID']=$this->request->param('id');
        $data=$adgroup->where($where)->find();
        $this->view->assign('data',$data);
        return $this->view->fetch('adGroupEdit');
        }
    }
}