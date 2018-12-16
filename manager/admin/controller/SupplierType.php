<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/7/21
 * Time: 9:47
 */

namespace app\admin\controller;


\think\Loader::import('controller/Jump', TRAIT_PATH, EXT);

use app\admin\Controller;
use think\Db;
use think\Image;

class SupplierType extends Controller
{
    use \traits\controller\Jump;

    public function index(){
        return  $this->supplierTypeList();
    }

    //级别列表的显示
    public  function supplierTypeList(){
        $count=Db::name('suppliertype')->order('ID ASC')->count();//获取满足条件的总记录数
        $list=Db::name('suppliertype')->order('ID ASC')->paginate();//根据条件分页输出

        $this->view->assign('supplierType',$list);
        $this->view->assign('count',$count);
        return $this->view->fetch('supplierTypeList');
    }

    //删除分类
    public function supplierTypeDel(){
        $ID=$this->request->param();
        if($ID['ID']) {
            if (is_array($ID['ID'])) {//批量删除

                foreach ($ID['ID'] as $id) {
                    //查询是否属于该分类的留言，若有，则不允许删除
                    $count=Db::name('supplier')->where('SupplierType='.$id)->count();//获取满足条件的总记录数
                    if ($count>0){//属于该分类的商家条数
                        $returnData['status']=0;
                        $returnData['msg']='请先将属于['.getTypeNameByTypeId($id).']分类的商家删除，再来删除分类哦！';
                    }else{
                        Db::name('suppliertype')->where('ID='.$id)->delete();
                        $returnData['status']=1;
                        $returnData['msg']='删除成功！';
                    }
                }
            } else {//针对单个分类的删除
                $count=Db::name('supplier')->where('SupplierType='.$ID['ID'])->count();//获取满足条件的总记录数
                if ($count>0){
                    $returnData['status']=0;
                    $returnData['msg']='请先将属于['.getTypeNameByTypeId($ID['ID']).']分类的商家删除，再来删除分类哦！';
                }else{
                    Db::name('suppliertype')->where('id='.$ID['ID'])->delete();
                    $returnData['status']=1;
                    $returnData['msg']='删除成功！';
                }
            }
        }else{
            $returnData['status']=0;
            $returnData['msg']='删除失败！';
        }
        return json($returnData);
    }

    //级别添加
    public function supplierTypeAdd(){
        $act=$this->request->param('act');
        if($act=='add'){
            $supplierTypeData=$this->request->param();
            $data["SupplierTypeName"]=$supplierTypeData["name"];
            $data["AddDate"]=date('Y-m-d H:i:s');
            $data["ModifyDate"]=date('Y-m-d H:i:s');

            $proimg = $this->request->file('proimg');
            if(!empty($proimg)) {
                //打开图片
                $image = Image::open($proimg);
                if($image->width()!=60 or $image->height()!=60 ){
                    $this->error("分类图片尺寸必须为60*60！",url("SupplierType/supplierTypeAdd",['act'=>'show','name'=>$supplierTypeData["name"]]));
                }

                //获取新添加的分类的id（因为在商家分类表中Id为自增列a）
                $sql="SHOW TABLE STATUS LIKE 'suppliertype'";
                $ProidData=Db::query($sql);
                $id=$ProidData[0]['Auto_increment'];

                //商家分类图片保存地址
                $uploaddir = ROOT_PATH . 'public' . DS . 'Upload' . DS . 'supplier' . DS . 'supplierType'.DS.$id;
                $imgpath= '/public/Upload/supplier/supplierType/'.$id.'/';//保存到数据库的图片地址路径
                $info = $proimg->validate(['size' => 1024000, 'ext' => 'jpg,png,gif,jpeg'])->move($uploaddir);
                if ($info) {
                    $filename = $info->getPathInfo()->getFilename() . '/' . $info->getFilename();
                    $data["SupplierTypeImg"] = $imgpath.$filename;
                } else {
                    $this->error("分类图片上传失败！",url("SupplierType/supplierTypeAdd",['act'=>'show']));
                }
            }

            $typeId=Db::name('suppliertype')->insertGetId($data);//添加数据并获得此数据在数据库中的id
            if($typeId){
                $this->success("分类添加成功！",url("SupplierType/supplierTypeList"));
            }
        }elseif($act=='show'){
            $name=$this->request->param('name');
            if(!empty($name)){
                $this->view->assign('name',$this->request->param('name'));
            }else{
                $this->view->assign('name','');
            }
            return $this->view->fetch('supplierTypeAdd');
        }
    }

    //级别修改
    public function supplierTypeEdit()
    {
        $id = $this->request->param('id');
        $act = $this->request->param('act');
        if ($act == 'show') {//分类信息编辑页面的显示
            $supplierTypeList = array_change_key_case(Db::name('suppliertype')->where("id=" . $id)->find());
            $this->view->assign("list", $supplierTypeList);
            return $this->view->fetch('supplierTypeEdit');
        } elseif ($act == 'edit') {//分类信息的编辑
            $supplierTypeData = $this->request->param();
            $id=$supplierTypeData["id"];
            $data["SupplierTypeName"] = $supplierTypeData["name"];
            $proimg = $this->request->file('proimg');
            if(!empty($proimg)) {
                //打开图片
                $image = Image::open($proimg);
                if($image->width()>60 or $image->height()>60 ){
                    $this->error("分类图片尺寸必须为60*60！",url("SupplierType/supplierTypeEdit",['id'=>$id,'act'=>'show']));
                }
                //商家分类图片保存地址
                $uploaddir = ROOT_PATH . 'public' . DS . 'Upload' . DS . 'supplier' . DS . 'supplierType'.DS.$id;
                $imgpath= '/public/Upload/supplier/supplierType/'.$id.'/';//保存到数据库的图片地址路径
                $info = $proimg->validate(['size' => 1024000, 'ext' => 'jpg,png,gif,jpeg'])->move($uploaddir);
                if ($info) {
                    $filename = $info->getPathInfo()->getFilename() . '/' . $info->getFilename();
                    $data["SupplierTypeImg"] = $imgpath.$filename;
                } else {
                    $this->error("分类图片上传失败！",url("SupplierType/supplierTypeEdit",['id'=>$id,'act'=>'show']));
                }
            }
            $data["ModifyDate"]=date('Y-m-d H:i:s');
            Db::name('suppliertype')->where("id=" .$id )->update($data);//修改数据库
            $this->success("分类修改成功！",url("SupplierType/supplierTypeList"));
        }
    }

}