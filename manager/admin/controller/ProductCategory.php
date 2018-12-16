<?php
/**
 * @author 赵晓凡
 * @copyright 2017  all rights reserved.
 */

namespace app\admin\controller;

\think\Loader::import('controller/Jump', TRAIT_PATH, EXT);

use app\admin\Controller;
use think\Db;


class ProductCategory extends Controller
{
    use \traits\controller\Jump;

    //分类列表
    /**
     * 用于分类的展示、搜索
     * @return mixed
     */

    public function index()
    {
        return $this->categoryList();
    }

    public function categoryList()
    {
        $prolist=Db::name("product");
        $pcateslist=Db::name("productcategory");
        if($this->request->param("action")=="delete"){
            if(getpclevelbyid($this->request->param("pcateid"))==3){
                $pcateslist->where("id=".$this->request->param("pcateid"))->delete();
                $prom=$prolist->where("categorythird=".$this->request->param("pcateid"))->select();
                if($prom){
                    foreach($prom as $n=>$val){
                        $data["categorythird"]=0;
                        $prolist->where("proid=".$val["proid"])->update($data);
                    }
                }

            }
            if(getpclevelbyid($this->request->param("pcateid"))==2){
                $pcatem=$pcateslist->where("pid=".$this->request->param("pcateid"))->select();
                if($pcatem){
                    echo "thirderror"; //二级下面存在三级 不能删除
                    return;
                }else{
                    $pcateslist->where("id=".$this->request->param("pcateid"))->delete();
                    $prom=$prolist->where("categorycode=".$this->request->param("pcateid"))->select();
                    if($prom){
                        foreach($prom as $n=>$val){
                            $data["categorycode"]=0;
                            $data["categorythird"]=0;
                            $prolist->where("proid=".$val["proid"])->update($data);
                        }
                    }
                }
            }
            if(getpclevelbyid($this->request->param("pcateid"))==1){
                $pcatem=$pcateslist->where("pid=".$this->request->param("pcateid"))->select();
                if($pcatem){
                    echo "twoerror"; //一级下面存在二级 不能删除
                    return;
                }else{
                    $pcateslist->where("id=".$this->request->param("pcateid"))->delete();
                    $prom=$prolist->where("categoryid=".$this->request->param("pcateid"))->select();
                    if($prom){
                        foreach($prom as $n=>$val){
                            $data["categoryid"]=0;
                            $data["categorycode"]=0;
                            $data["categorythird"]=0;
                            $prolist->where("proid=".$val["proid"])->update($data);
                        }
                    }
                }
            }
        }elseif($this->request->param("action")=="enable"){
            if($this->request->param("status")=="start"){
                $data["disable"]=1;
                $pcateslist->where('id='.$this->request->param("id"))->update($data);

            }elseif($this->request->param("status")=="stop"){
                $data["disable"]=0;
                $pcateslist->where('id='.$this->request->param("id"))->update($data);

            }
        }elseif($this->request->param("action")=="sort"){
            $sorts=$_POST["sort"];
            $ids=$_POST["id"];
            if($ids!=""){
                if(is_array($ids)){
                    $sort_num=count($ids);
                    for($i=0;$i<$sort_num;$i++){
                        $data["sort"]=$sorts[$i];
                        $pcateslist->where('id='.$ids[$i])->update($data);
                    }
                }else{
                    $data["sort"]=$sorts;
                    $pcateslist->where('id='.$ids)->update($data);
                }
                $this->success("修改成功！",url("ProductCategory/categoryList"));
            }
        }else {
            $pcatelist = $pcateslist->where('pid', 0)->order("sort asc")->select();
            $count = $pcateslist->count();  //获取满足条件的总记录数
            foreach ($pcatelist as $n => $val) {
                $pcatelist[$n]['voo'] = $pcateslist->where('pid', $val['Id'])->order("sort asc")->select();
                foreach ($pcatelist[$n]['voo'] as $m => $valm) {
                    $pcatelist[$n]['voo'][$m]['boo'] = $pcateslist->where('pid', $valm['Id'])->order("sort asc")->select();
                }
            }
            $this->view->assign("count", $count);
            $this->view->assign("listone", $pcatelist);
            return $this->view->fetch('categoryList');
        }

    }

    /**
     * 用于分类的添加，编辑
     * @return mixed
     */
     public function productCategoryAdd()
     {
         $pcateadd=Db::name("productcategory");
         $pid = $this->request->param("pid");
         $categoryname = $this->request->param("categoryname");
         if($this->request->param('action')=="add"){
             //echo getpclevelbyid($this->request->param["pid"]);
             //return;
             if(empty($categoryname)){
                 echo "catenameerror";
                 return;
             }
             if(getpclevelbyid($pid)==3){
                 echo "levelerror";
                 return;
             }else{
                 $where['pid']=$pid;
                 $where['name']=$this->request->param('categoryname');
                 $where['lay']=getpclevelbyid($pid+1);
                 $pcatechk=$pcateadd->where($where)->find();
                 if($pcatechk){
                     echo "catenamere";
                     return;
                 }else{
                     if($pid==0){
                         $data["pid"]=0;
                         $data["oid"]=0;
                         $data["Lay"]=1;
                     }else{
                         if(getpclevelbyid($pid)==1){
                             $data["pid"]=$pid;
                             $data["oid"]=$pid;
                             $data["Lay"]=2;
                         }else{
                             $data["pid"]=$pid;
                             $data["oid"]=getproductoidbyid($pid);
                             $data["Lay"]=3;
                         }
                     }
                     $file = $this->request->file('photo');
                     if(!empty($file)){
                         $picsite=getimagesize($_FILES['photo']['tmp_name']);
                         if($picsite[0]!=$picsite[1]){
                             echo 'limitphoto';
                             return;
//                              $this->error("请上传1:1的图片！",url("product/productCategoryAdd"));
                         }
                         if($_FILES['photo']['size']>1024*100*2){
                             echo 'sizephoto';
                             return;
//                             $this->error("请上传200k以内的图片！",url("product/productCategoryAdd"));

                         }
                         $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads');
                         if ($info) {
                             $data['photo'] = '/public/uploads/' . $info->getSaveName();
                         } else {
                             // 上传失败获取错误信息
                             echo $file->getError();
                         }
                     }
                     $data["name"]=$categoryname;
                     $pcateadd->insert($data);

                 }
             }

         }else{
             //$this->assign("list",$pcateadd);
             $pcatelist=$pcateadd->where("pid",0)->order("sort asc")->select();
             foreach($pcatelist as $n=> $val){
                 $pcatelist[$n]['voo']=$pcateadd->where('pid',$val['Id'])->order("sort asc")->select();
                 foreach($pcatelist[$n]['voo'] as $m=> $valm){
                     $pcatelist[$n]['voo'][$m]['boo']=$pcateadd->where('pid='.$valm['Id'])->order("sort asc")->select();
                 }
             }
             $this->view->assign("listone",$pcatelist);
             return $this->view->fetch('productCategoryAdd');
         }

     }

     //商品分类的修改
    public function productCategoryEdit(){
        $pcateadd=Db::name("productcategory");
        $pro=Db::name("product");
        $suppcate=Db::name("supplierpcat");
        $categoryname=$this->request->param("categoryname");
        if($this->request->param("action")=="edit"){
            if(empty($categoryname)){
                echo "catenameerror";
                return;
            }
            if(getpclevelbyid($this->request->param("pid"))==3){
                echo "levelerror";
                return;
            }else{
                $pcatechk=Db::name("productcategory")->where("pid=".$this->request->param("pid")." and Id<>".$this->request->param("pcateid")." and name='".$this->request->param("categoryname")."' and lay=".(getpclevelbyid($this->request->param("pid"))+1))->find();
                if($pcatechk){
                    echo "catenamere";
                    return;
                }else{
                    if($this->request->param("oldpid")!=$this->request->param("pid")){ //如果pid也修改了
                        if($this->request->param("pid")==$this->request->param("pcateid")){
                            echo "selferror";
                            return;
                        }
                        //获取老pid的级别
                        $oldcateidlev=getpclevelbyid($this->request->param("oldpid"));
                        if($this->request->param("pid")==0){ //变一级
                            //一级不能变一级 所以直接跳过去
                            if($oldcateidlev==1){ //如果这里是1 说明即将修改的$this->request->param("pcateid")的级别是2  所以要看下$this->request->param("pcateid")的下面是否有第三级别 有的话升级为二级
                                $pcateidlist=Db::name("productcategory")->where("pid=".$this->request->param("pcateid"))->select();
                                if($pcateidlist){ //为$this->request->param("pcateid")下的分类升级 这里是由三级变二级
                                    foreach($pcateidlist as $on=>$oval){
                                        $datao["oid"]=$this->request->param("pcateid");
                                        $datao["Lay"]=2;
                                        Db::name("productcategory")->where("id=".$oval["id"])->update($datao);
                                    }
                                }
                                //无论二级下面是不是有三级 都需要执行以下程序
                                $ccprolist=$pro->where("categorycode=".$this->request->param("pcateid"))->select(); //修改产品里面的分类
                                if($ccprolist){
                                    foreach($ccprolist as $ccn=>$ccval){
                                        $datacpone["categoryId"]=$this->request->param("pcateid"); //一级
                                        $datacpone["CategoryCode"]=$ccval["categorythird"];//二级
                                        $datacpone["CategoryThird"]=0;//三级
                                        $pro->where("proid=".$ccval["proid"])->update($datacpone);
                                    }
                                }

                                //商家经营范围内删除级别为2的request("pcateid")
                                //$suppcate->where("pcateid=".$this->request->param("pcateid"))->delete();
                                //$suppcate->where("pid=".$this->request->param("pcateid"))->delete();

                            }elseif(getpclevelbyid($this->request->param("pid"))==2){ //如果这里是2 说明即将修改的$this->request->param("pcateid")的级别是3
                                $ccprolist=$pro->where("categorythird=".$this->request->param("pcateid"))->select(); //修改产品里面的分类
                                if($ccprolist){
                                    foreach($ccprolist as $ccn=>$ccval){
                                        $datacpone["categoryId"]=$this->request->param("pcateid"); //一级
                                        $datacpone["CategoryCode"]=0;//二级
                                        $datacpone["CategoryThird"]=0;//三级
                                        $pro->where("proid=".$ccval["proid"])->update($datacpone);
                                    }
                                }
                                //商家经营范围内删除级别为3的request("pcateid")
                                //$suppcate->where("pcateid=".$this->request->param("pcateid"))->delete();
                            }
                            $data["pid"]=0;
                            $data["oid"]=0;
                            $data["Lay"]=1;
                            $data["name"]=$this->request->param["categoryname"];
                            $file = $this->request->file('photo');
                            if(!empty($file)){
                                $picsite=getimagesize($_FILES['photo']['tmp_name']);
                                if($picsite[0]!=$picsite[1]){
                                    echo 'limitphoto';
                                    return;
                                }
                                if($_FILES['photo']['size']>1024*100*2){
                                    echo 'sizephoto';
                                    return;
                                }
                                $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads');
                                if ($info) {
                                    $data['photo'] = '/public/uploads/' . $info->getSaveName();
                                } else {
                                    // 上传失败获取错误信息
                                    echo $file->getError();
                                }
                            }
                            Db::name("productcategory")->where("id=".$this->request->param("pcateid"))->update($data);
                        }else{
                            if(getpclevelbyid($this->request->param["pid"])==1){
                                if($this->request->param("oldpid")==0){ //如果这里是0 说明即将修改的$this->request->param("pcateid")的级别是1  所以要看下$this->request->param("pcateid")的下面是否有第三级别 有的话不可以变为二级
                                    $pcateidlist=Db::name("productcategory")->where("oid=".$this->request->param("pcateid")." and lay=3")->select();
                                    if($pcateidlist){
                                        echo "onenottotwohasthree"; //因为一级的下面有三级 所以一级不能变为二级
                                        return;
                                    }
                                    //一级变二级 二级变三级
                                    $data["pid"]=$this->request->param("pid");
                                    $data["oid"]=$this->request->param("pid");
                                    $data["Lay"]=2;
                                    $data["name"]=$this->request->param("categoryname");
                                    $file = $this->request->file('photo');
                                    if(!empty($file)){
                                        $picsite=getimagesize($_FILES['photo']['tmp_name']);
                                        if($picsite[0]!=$picsite[1]){
                                            echo 'limitphoto';
                                            return;
                                        }
                                        if($_FILES['photo']['size']>1024*100*2){
                                            echo 'sizephoto';
                                            return;

                                        }
                                        $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads');
                                        if ($info) {
                                            $datao['photo'] = '/public/uploads/' . $info->getSaveName();
                                        } else {
                                            // 上传失败获取错误信息
                                            echo $file->getError();
                                        }
                                    }
                                    Db::name("productcategory")->where("id=".$this->request->param("pcateid"))->update($data);
                                    $pcateidlist=Db::name("productcategory")->where("oid=".$this->request->param("pcateid")." and lay=2")->select();
                                    if($pcateidlist){
                                        foreach($pcateidlist as $on=>$oval){
                                            $datao["oid"]=$this->request->param("pid");
                                            $datao["Lay"]=3;
                                            Db::name("productcategory")->where("id=".$oval["id"])->update($datao);
                                        }
                                    }
                                    $ccprolist=$pro->where("categoryid=".$this->request->param("pcateid"))->select(); //修改产品里面的分类
                                    if($ccprolist){
                                        foreach($ccprolist as $ccn=>$ccval){
                                            $datacpone["categoryId"]=$this->request->param("pid"); //一级
                                            $datacpone["CategoryCode"]=$this->request->param("pcateid");//二级
                                            $datacpone["CategoryThird"]=$ccval["categorycode"];//三级
                                            $pro->where("proid=".$ccval["proid"])->update($datacpone);
                                        }
                                    }
                                    //商家经营范围内删除级别为1的request("pcateid")
                                    $suppcate->where("pcateid=".$this->request->param("pcateid"))->delete(); //删除经营范围内的一级
                                    $twodelete=$suppcate->where("pid=".$this->request->param("pcateid"))->select();//查询二级
                                    foreach($twodelete as $dn=>$dval){
                                        $suppcate->where("pid=".$twodelete("pcateid"))->delete();//删除经营范围内的三级
                                    }
                                    $suppcate->where("pid=".$this->request->param("pcateid"))->delete();//删除经营范围内的二级

                                }elseif($oldcateidlev==1){ //二级变成其他一级下的二级
                                    $datao["oid"]=$this->request->param("pid");
                                    $datao["pid"]=$this->request->param("pid");
                                    $datao["name"]=$this->request->param("categoryname");
                                    $file = $this->request->file('photo');
                                    if(!empty($file)){
                                        $picsite=getimagesize($_FILES['photo']['tmp_name']);
                                        if($picsite[0]!=$picsite[1]){
                                            echo 'limitphoto';
                                            return;
                                        }
                                        if($_FILES['photo']['size']>1024*100*2){
                                            echo 'sizephoto';
                                            return;
                                        }
                                        $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads');
                                        if ($info) {
                                            $datao['photo'] = '/public/uploads/' . $info->getSaveName();
                                        } else {
                                            // 上传失败获取错误信息
                                            echo $file->getError();
                                        }
                                    }
                                    Db::name("productcategory")->where("id=".$this->request->param("pcateid"))->update($datao);
                                    $ccprolist=$pro->where("categorycode=".$this->request->param("pcateid"))->select(); //修改产品里面的分类
                                    if($ccprolist){
                                        foreach($ccprolist as $ccn=>$ccval){
                                            $datacpone["categoryId"]=$this->request->param("pid"); //一级
                                            //$datacpone["CategoryCode"]=$ccval["categorythird"];//二级
                                            //$datacpone["CategoryThird"]=0;//三级
                                            $pro->where("proid=".$ccval["proid"])->update($datacpone);
                                        }
                                    }
                                    //商家经营范围内删除级别为2的request("pcateid")
                                    $suppcate->where("pcateid=".$this->request->param("pcateid"))->delete();
                                    $suppcate->where("pid=".$this->request->param("pcateid"))->delete();

                                }elseif($oldcateidlev==2){ //三级变成二级
                                    $datao["oid"]=$this->request->param("pid");
                                    $datao["pid"]=$this->request->param("pid");
                                    $datao["Lay"]=2;
                                    $datao["name"]=$this->request->param("categoryname");
                                    $file = $this->request->file('photo');
                                    if(!empty($file)){
                                        $picsite=getimagesize($_FILES['photo']['tmp_name']);
                                        if($picsite[0]!=$picsite[1]){
                                            echo 'limitphoto';
                                            return;
                                        }
                                        if($_FILES['photo']['size']>1024*100*2){
                                            echo 'sizephoto';
                                            return;
                                        }
                                        $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads');
                                        if ($info) {
                                            $datao['photo'] = '/public/uploads/' . $info->getSaveName();
                                        } else {
                                            // 上传失败获取错误信息
                                            echo $file->getError();
                                        }
                                    }
                                    Db::name("productcategory")->where("id=".$this->request->param("pcateid"))->update($datao);
                                    $ccprolist=$pro->where("categorythird=".$this->request->param("pcateid"))->select(); //修改产品里面的分类
                                    if($ccprolist){
                                        foreach($ccprolist as $ccn=>$ccval){
                                            $datacpone["categoryId"]=$this->request->param("pid"); //一级
                                            $datacpone["CategoryCode"]=$this->request->param("pcateid");//二级
                                            $datacpone["CategoryThird"]=0;//三级
                                            $pro->where("proid=".$ccval["proid"])->update($datacpone);
                                        }
                                    }
                                    //商家经营范围内删除级别为3的request("pcateid")
                                    $suppcate->where("pcateid=".$this->request->param("pcateid"))->delete();
                                }

                            }else{
                                if(getpclevelbyid($this->request->param("pid"))==2){
                                    if($this->request->param("oldpid")==0){ //一级变三级
                                        echo "onenottothree"; //一级不能变三级
                                        return;
                                    }
                                    if($oldcateidlev==1){ //二级变三级
                                        $pcateidlist=Db::name("productcategory")->where("pid=".$this->request->param("pcateid")." and lay=3")->select();
                                        if($pcateidlist){
                                            echo "twonottothree"; //因为二级的下面有三级 所以二级不能变三级
                                            return;
                                        }else{
                                            $data["pid"]=$this->request->param("pid");
                                            $data["oid"]=getproductoidbyid($this->request->param("pid"));
                                            $data["Lay"]=3;
                                            $file = $this->request->file('photo');
                                            if(!empty($file)){
                                                $picsite=getimagesize($_FILES['photo']['tmp_name']);
                                                if($picsite[0]!=$picsite[1]){
                                                    echo 'limitphoto';
                                                    return;
                                                }
                                                if($_FILES['photo']['size']>1024*100*2){
                                                    echo 'sizephoto';
                                                    return;
                                                }
                                                $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads');
                                                if ($info) {
                                                    $data['photo'] = '/public/uploads/' . $info->getSaveName();
                                                } else {
                                                    // 上传失败获取错误信息
                                                    echo $file->getError();
                                                }
                                            }
                                            $data["name"]=$this->request->param("categoryname");
                                            Db::name("productcategory")->where("id=".$this->request->param("pcateid"))->update($data);
                                            $ccprolist=$pro->where("categorycode=".$this->request->param("pcateid"))->select(); //修改产品里面的分类
                                            if($ccprolist){
                                                foreach($ccprolist as $ccn=>$ccval){
                                                    $datacpone["categoryId"]=getproductoidbyid($this->request->param("pid")); //一级
                                                    $datacpone["CategoryCode"]=$this->request->param("pid");//二级
                                                    $datacpone["CategoryThird"]=$this->request->param("pcateid");//三级
                                                    $pro->where("proid=".$ccval["proid"])->update($datacpone);
                                                }
                                            }
                                        }
                                        //商家经营范围内删除级别为2的request("pcateid")
                                        $suppcate->where("pcateid=".$this->request->param("pcateid"))->delete();
                                        $suppcate->where("pid=".$this->request->param("pcateid"))->delete();
                                    }
                                    if($oldcateidlev==2){ //三级变三级
                                        $datao["oid"]=getproductoidbyid($this->request->param("pid"));
                                        $datao["pid"]=$this->request->param("pid");
                                        $datao["name"]=$this->request->param("categoryname");
                                        $file = $this->request->file('photo');
                                        if(!empty($file)){
                                            $picsite=getimagesize($_FILES['photo']['tmp_name']);
                                            if($picsite[0]!=$picsite[1]){
                                                echo 'limitphoto';
                                                return;
                                            }
                                            if($_FILES['photo']['size']>1024*100*2){
                                                echo 'sizephoto';
                                                return;
                                            }
                                            $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads');
                                            if ($info) {
                                                $datao['photo'] = '/public/uploads/' . $info->getSaveName();
                                            } else {
                                                // 上传失败获取错误信息
                                                echo $file->getError();
                                            }
                                        }
                                        Db::name("productcategory")->where("id=".$this->request->param("pcateid"))->update($datao);
                                        $ccprolist=$pro->where("categorythird=".$this->request->param("pcateid"))->select(); //修改产品里面的分类
                                        if($ccprolist){
                                            foreach($ccprolist as $ccn=>$ccval){
                                                $datacpone["categoryId"]=getproductoidbyid($this->request->param("pid")); //一级
                                                $datacpone["CategoryCode"]=$this->request->param("pid");//二级
                                                $pro->where("proid=".$ccval["proid"])->update($datacpone);
                                            }
                                        }
                                        //商家经营范围内删除级别为3的request("pcateid")
                                        $suppcate->where("pcateid=".$this->request->param("pcateid"))->delete();
                                    }
                                }
                            }
                        }
                    }else{

                        $data["name"]=$this->request->param("categoryname");
                        $file = $this->request->file('photo');
                        if(!empty($file)){
                            $picsite=getimagesize($_FILES['photo']['tmp_name']);
                            if($picsite[0]!=$picsite[1]){
                                echo 'limitphoto';
                                return;
                            }
                            if($_FILES['photo']['size']>1024*100*2){
                                echo 'sizephoto';
                                return;
                            }
                            $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads');
                            if ($info) {
                                $data['photo'] = '/public/uploads/' . $info->getSaveName();
                            } else {
                                // 上传失败获取错误信息
                                echo $file->getError();
                            }
                        }
                        Db::name("productcategory")->where("id=".$this->request->param("pcateid"))->update($data);
                    }

                }
            }

        }else{
            $pcateadd=Db::name("productcategory");
            $defaultpcate=$pcateadd->where("id=".$this->request->param("pcateid"))->find();
            $pcatelist=$pcateadd->where("pid",0)->select();
            foreach($pcatelist as $n=> $val){
                $pcatelist[$n]['voo']=$pcateadd->where('pid='.$val['Id'])->select();
                foreach($pcatelist[$n]['voo'] as $m=> $valm){
                    $pcatelist[$n]['voo'][$m]['boo']=$pcateadd->where('pid='.$valm['Id'])->select();
                }
            }
            $this->view->assign("defaultpcate",$defaultpcate);
            $this->view->assign("listone",$pcatelist);

            return $this->view->fetch("productCategoryEdit");
        }
    }
}