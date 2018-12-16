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

class ArticleCategory extends Controller
{
    use \traits\controller\Jump;

    public function index(){
        return $this->articleCategoryList();
    }

    //资讯分类的显示
    public  function articleCategoryList(){
        $count=Db::name('articlecategory')->order('ID ASC')->count();//获取满足条件的总记录数
        $list=Db::name('articlecategory')->order('ID ASC')->paginate();//根据条件分页输出
        $this->view->assign('list',$list);
        $this->view->assign('count',$count);
        return $this->view->fetch('articleCategoryList');
    }

    //资讯分类删除
    public function articleCategoryDel(){
        $ID=$this->request->param();
        if($ID['ID']) {
            if (is_array($ID['ID'])) {//批量删除
                foreach ($ID['ID'] as $id) {
                    Db::name('articlecategory')->where('id='.$id)->delete();
                }
            } else {//针对单个的删除
                Db::name('articlecategory')->where('id='.$ID['ID'])->delete();
            }
            $returnData['status']=1;
            $returnData['msg']='删除成功！';
        }else{
            $returnData['status']=0;
            $returnData['msg']='删除失败！';
        }
        return json($returnData);
    }

    //资讯分类添加
    public function articleCategoryAdd(){
        $act=$this->request->param('act');
        if($act=='add'){
            $memberData=$this->request->param();
            $data["categoryname"]=$memberData["categoryname"];
            $data["isshow"]=$memberData["isshow"];
            $data["remark"]=$memberData["remark"];
            $memberId=Db::name('articlecategory')->insertGetId($data);//添加数据并获得此数据在数据库中的id
            if($memberId){
                return json(['status'=>1,'msg'=>'添加成功']);
            }
            else{
                return json(['status'=>0,'msg'=>'添加失败']);
            }
        }elseif($act=='show'){
            return $this->view->fetch('articleCategoryAdd');
        }
    }

    //资讯分类的修改
    public function articleCategoryEdit(){
        $id=$this->request->param('id');
        $act=$this->request->param('act');
        if($act=='show'){//资讯分类信息编辑页面的显示
            $articleCategoryData=array_change_key_case(Db::name('articlecategory')->where("id=".$id)->find());
            $this->view->assign("list",$articleCategoryData);
            return $this->view->fetch('articleCategoryEdit');
        }elseif($act=='edit') {//资讯分类信息的编辑
            $articleCategory=$this->request->param();
            $data["categoryname"]=$articleCategory["categoryname"];
            $data["isshow"]=$articleCategory["isshow"];
            $data["remark"]=$articleCategory["remark"];
            Db::name('articlecategory')->where("id=".$articleCategory["id"])->update($data);//修改数据库
            return json(['status'=>1,'msg'=>'资讯分类修改成功']);
        }
    }

    /**
     * 资讯分类的显示与隐藏
     */
    public function articleCategoryIsShow(){
        $status=$this->request->param("status");
        $id=$this->request->param("id");
        if($status=='yes'){
            $data["isshow"]=1;
            Db::name("articlecategory")->where("id=".$id)->update($data);
            return json(['status'=>1,'msg'=>'已显示!']);
        }elseif($status=='stop'){
            $data["isshow"]=0;
            Db::name("articlecategory")->where("id=".$id)->update($data);
            return json(['status'=>1,'msg'=>'已隐藏!']);
        }else{
            return json(['status'=>0,'msg'=>'请求参数错误!']);
        }
    }
}