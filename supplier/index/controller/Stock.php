<?php
/**
 * Created by 赵晓凡
 * User: zhaoxiaofan
 * Date: 2017/3/3
 * Time: 9:08
 */

namespace supplier\index\controller;

use think\Db;
use think\Session;

class Stock extends Auth
{
    /**
     * 库存列表
     * @return mixed
     */
    public function stock_list(){
        if($this->request->param()){
             if($this->request->param('productcate')){
                $productcate=$this->request->param('productcate');
                $map['productcate']=$productcate;
                $where['categoryId']=$productcate;
            }
            if($this->request->param("keyproid")){
                $keyproid=$this->request->param("keyproid");
                $where["ProId"]=$keyproid;
                $map['keyproid']=$keyproid;
            }
            if($this->request->param("keyproname")){
                $keyproname=$this->request->param("keyproname");
                $where["ProName"]=array('like','%'.$keyproname.'%');
                $map['keyproname']=$keyproname;
            }
            if($this->request->param("productHit")){
                $productHit=$this->request->param("productHit");
                $where["IsHit"]=$productHit;
                $map['productHit']=$productHit;
            }
        }
        $where['SupplierId']=Session::get('supplierid');
        $list = Db::name('product')->where($where)->order("proid desc")->paginate(15);
        $data=[];
        if($list){
            foreach($list as $n=>$val){
                $data[$n]=$val=array_change_key_case($val);
                if(is_onqiniu()==true){
                    $data[$n]['img']=$val['qiqiuproimgpath'];
                    $data[$n]['maximg']=$val['qiqiupromaximgpath'];
                }else{
                    if(strpos($val['proimg'],'http://')!==false){
                        $data[$n]['img']=$val['proimg'];
                        $data[$n]['maximg']=$val['maxproimg'];

                    }else{
                        $data[$n]['img']='/public/Upload/cpimg/'.$val['proimg'];
                        $data[$n]['maximg']='/public/Upload/cpimg/'.$val['maxproimg'];
                    }
//                    $data[$n]['img']='/public/Upload/cpimg/'.$val['proimg'];
//                    $data[$n]['maximg']='/public/Upload/cpimg/'.$val['maxproimg'];
                }
                $data[$n]["voo"]=indexToLower(Db::name('productstock')->where("proid=".$val["proid"])->select());
            }
        }

        if(!empty($map)) {
            foreach ($map as $key => $value) {
                $list->appends($key, $value);//分页链接中添加请求的参数
            }
        }
        $cate=Db::name('productcategory')->where('Lay=1 and disable=1 and pid=0')->select();
        $this->view->assign('cate',$cate);
        $this->view->assign("prolist",$data);
        $this->view->assign('page',$list->render());// 赋值分页输出
        $this->view->assign("count",$list->total());

        return $this->view->fetch('stock_list');
    }

    /**
     * 库存变更记录
     * @return mixed
     */
    public function stock_list_record(){
        $where['SupplierId']=Session::get('supplierid');
        if($this->request->param()){
            if($this->request->param("keyproid")){
                $keyproid=$this->request->param("keyproid");
                $where["ProId"]=$keyproid;
                $map['keyproid']=$keyproid;
            }
            if($this->request->param("keyproname")){
                $keyproname=$this->request->param("keyproname");
                $condtion['SupplierId']=Session::get('supplierid');
                $condtion["ProName"]=array('like','%'.$keyproname.'%');
                $proidArr=Db::name('product')->where($condtion)->field('proid')->select();
                $temp=[];
                foreach ($proidArr as $key=>$val){
                    $temp[]=$val['proid'];
                }
                if(!empty($temp)){
                    if(isset($keyproid)){
                        $where["ProId"]=[['eq',$keyproid],['in',$temp]];
                    }else{
                        $where["ProId"]=['in',$temp];
                    }
                    $map['keyproname']=$keyproname;
                }else{
                    $where['SupplierId']=0;
                }

            }

            if($this->request->param("productHit")){
                $productHit=$this->request->param("productHit");
                $condtion['SupplierId']=Session::get('supplierid');
                $condtion["IsHit"]=$productHit;
                $proidArr=Db::name('product')->where($condtion)->field('proid')->select();
                $temp=[];
                foreach ($proidArr as $key=>$val){
                    $temp[]=$val['proid'];
                }
                if(!empty($temp)){
                    $where["ProId"]=['in',$temp];
                    $map['productHit']=$productHit;
                }else{
                    $where['SupplierId']=0;
                }
            }
            //根据日期进行查找
            if($this->request->param("datemin") and $this->request->param("datemax")){
                $requestdate=$this->request->param("datemax");
                $redate=date("Y-m-d",strtotime("$requestdate +1 day"));
                $where["AddDate"]=array(array('egt',$this->request->param("datemin")),array('elt',$redate),'and');
                $map["datemin"]=$this->request->param("datemin");
                $map["datemax"]=$this->request->param("datemax");
            }elseif($this->request->param("datemin")){
                $where["AddDate"]=array('egt',$this->request->param("datemin"));
                $map["datemin"]=$this->request->param("datemin");
            }elseif($this->request->param("datemax")){
                $requestdate=$this->request->param("datemax");
                $redate=date("Y-m-d",strtotime("$requestdate +1 day"));
                $where["AddDate"]=array('elt',$redate);
                $map["datemax"]=$this->request->param("datemax");
            }
        }

        $list = Db::name('productstockrecord')->where($where)->order("id desc")->paginate(15);

        $data=[];
        if($list){
            foreach($list as $n=>$val){
                $data[$n]=array_change_key_case($val);
            }
        }
//        var_dump($data);exit;
        if(!empty($map)) {
            foreach ($map as $key => $value) {
                $list->appends($key, $value);//分页链接中添加请求的参数
            }
        }

        $this->view->assign("stocklist",$data);
        $this->view->assign('page',$list->render());// 赋值分页输出
        $this->view->assign("count",$list->total());

        return $this->view->fetch('stock_list_record');
    }

    public function stock_action(){
        $act=$this->request->param('act');
        $proid=$this->request->param('proid');
        $method='stock_'.$act;
        return $this->$method($proid);
    }

    /**
     * 入库操作
     * @param $proid
     * @return mixed
     */
    public function stock_add($proid){
        if($this->request->param('type')&&$this->request->param('type')=='submit'){
            $submitData=$this->request->param('');
            if(empty($submitData["styleid"])){
                return json(['status'=>0,'msg'=>'规格参数不能为空！']);
            }elseif($submitData["kucun"]<0){
                return json(['status'=>0,'msg'=>'入库数量不能小于零！']);
            }else {
                $data["ProId"] = $submitData["proid"];
                $data["remark"] = $submitData["remark"];
                $data["Txm"] = getpsizetxmbypsid($submitData["styleid"], 'pptxm');
                $data["StyleName"] = getpsizetxmbypsid($submitData["styleid"], 'ppsize');
                $data["SupplierId"] = getsupidbycpid($submitData["proid"]);
                $pstockcz = Db::name('productstock')->where("styleid=" . $submitData["styleid"])->field('kucun,styleid')->find();
                if (!$pstockcz) {
                    $yukucun = $data["Kucun"] = $submitData["kucun"];
                    $styleid = Db::name('productstock')->insertGetId($data);
                } else {
                    $yukucun = $data["Kucun"] = $pstockcz["kucun"] + $submitData["kucun"];
                    Db::name('productstock')->where("styleid=" . $pstockcz['styleid'])->update($data);
                    $styleid = $pstockcz["styleid"];
                }
                $data["StyleId"] = $styleid;
                $data["Kucun"] = $submitData["kucun"];
                $data["AddDate"]=date('Y-m-d H:i:s',time());
                $data["operator"] = Session::get('supplierid');
                $data["yukucun"] = $yukucun;
                $data["recordstyle"] = "入库";
                Db::name('productstockrecord')->insert($data);
                return json(['status' => 1, 'msg' => '恭喜您添加成功！']);
            }
        }else {
            $pstocklist = indexToLower(Db::name('productstock')->where("proid=" . $proid)->order("styleid desc")->select());
            $this->view->assign("pstocklist", $pstocklist);
            return $this->view->fetch('stock_add');
        }
    }

    /**
     * 出库操作
     * @param $proid
     * @return \think\response\Json
     */
    public function stock_out($proid){
        if($this->request->param('type')&&$this->request->param('type')=='submit'){
            $submitData=$this->request->param('');
            if(empty($submitData["styleid"])){
                return json(['status'=>0,'msg'=>'规格参数不能为空！']);
            }elseif($submitData["kucun"]<0){
                return json(['status'=>0,'msg'=>'出库数量不能小于零！']);
            }else{
                $data["ProId"]=$submitData["proid"];
                $data["remark"]=$submitData["remark"];
                $data["Txm"]=getpsizetxmbypsid($submitData["styleid"],'pptxm');
                $data["StyleName"]=getpsizetxmbypsid($submitData["styleid"],'ppsize');
                $data["SupplierId"]=getsupidbycpid($submitData["proid"]);
                $pstockcz=Db::name('productstock')->where("styleid=".$submitData["styleid"])->field('kucun,styleid')->find();
                if(!$pstockcz){
                    return json(['status'=>0,'msg'=>'此规格的产品还未添加库存！']);
                }else{

                    if($pstockcz["kucun"]<$submitData["kucun"]){
                        return json(['status'=>0,'msg'=>'此规格的产品库存不够！']);;
                    }else{
                        $yukucun=$data["Kucun"]=$pstockcz["kucun"]-$submitData["kucun"];
                        Db::name('productstock')->where("styleid=".$pstockcz['styleid'])->update($data);
                        $styleid=$pstockcz["styleid"];
                    }
                }
                $data["StyleId"]=$styleid;
                $data["Kucun"]=$submitData["kucun"];
                $data["AddDate"]=date('Y-m-d H:i:s',time());
                $data["yukucun"]=$yukucun;
                $data["operator"]=Session::get('supplierid');
                $data["recordstyle"]="出库";
                Db::name('productstockrecord')->insert($data);
                return json(['status'=>1,'msg'=>'恭喜您添加成功！']);
            }
        }else {
            $pstocklist = indexToLower(Db::name('productstock')->where("proid=" . $proid)->order("styleid desc")->select());
            $this->view->assign("pstocklist", $pstocklist);
            return $this->view->fetch('stock_out');
        }
    }
}