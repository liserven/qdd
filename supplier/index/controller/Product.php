<?php
/**
 * @author 赵晓凡
 * @copyright 2017  all rights reserved.
 */

namespace supplier\index\controller;

use think\Config;
use think\Db;
use think\Session;
use think\Image;
use Qiniu\Auth as Aath;
use Qiniu\Storage\BucketManager;
use Qiniu\Storage\UploadManager;

 class Product extends Auth
{

    //商品列表
    /**
     * 用于商品的展示、搜索
     * @return mixed
     */

    public function index(){
        return $this->productList();
    }
    public function productList(){
        $where["SupplierId"]=Session::get("supplierid");
        $map=[];
        if($this->request->param()){
            if($this->request->param('keyproid')){//获取搜索栏提交的商品ID
                $keyproid=$this->request->param('keyproid');
                $map=['keyproid'=>$keyproid];
                $where['ProId']=$keyproid;
            }
            if($this->request->param('keyproname')){
                $keyproname=$this->request->param('keyproname');
                $map=['keyproname'=>$keyproname];
                $where['ProName']=['like','%'.$keyproname.'%'];
            }
            if($this->request->param('productHit')){
                $productHit=$this->request->param('productHit');
                $map=['productHit'=>$productHit];
                $where['IsHit']=$productHit;
            }
            if($this->request->param('productState')){
                $productState=$this->request->param('productState');
                $map=['productState'=>$productState];
                $where['IsOnSell']=$productState;
            }
            if($this->request->param('isindex')){
                $isindex=$this->request->param('isindex');
                $map=['isindex'=>$isindex];
                $where['IsIndex']=$isindex;
            }
            if($this->request->param('keysupid')){
                $keysupid=$this->request->param('keysupid');
                $map=['keysupid'=>$keysupid];
                $where['SupplierId']=$keysupid;
            }
             if($this->request->param('productcate')){
                $productcate=$this->request->param('productcate');
                $map['productcate']=$productcate;
                $where['categoryId']=$productcate;
            }
            if($this->request->param('keysupname')){
                $keysupname=$this->request->param('keysupname');
                $condition['Name']=['like','%'.$keysupname.'%'];
                $supData=Db::name('supplier')->where($condition)->field('ID')->select();
                $supIds=[];
                foreach ($supData as $key=>$value){
                    $supIds[]=$value['ID'];
                }
                $map=['keysupname'=>$keysupname];
                if(!empty($supIds)) {
                    $where['SupplierId'] = ['in', $supIds];
                }
            }
        }
        $list=Db::name('product')->where($where)->order('ProId desc')->paginate();//根据条件分页输出
        if(!empty($map)) {
            foreach ($map as $key => $value) {
                $list->appends($key, $value);//分页链接中添加请求的参数
            }
        }
        $data=[];
        foreach ($list as $k=>$v){
            $data[$k]=$list[$k];
            if(is_onqiniu()==true){

                $data[$k]['img']=$v['qiqiuproimgpath'];
                $data[$k]['maximg']=$v['qiqiuproMaximgpath'];
            }else{
                if(strpos($v['ProImg'],'http://')!==false){
                    $data[$k]['img']=$v['ProImg'];
                    $data[$k]['maximg']=$v['MaxProImg'];

                }else{
                    $data[$k]['img']=config('IMAGE_DOMAIN_NAME').'/public/Upload/cpimg/'.$v['ProImg'];
                    $data[$k]['maximg']=config('IMAGE_DOMAIN_NAME').'/public/Upload/cpimg/'.$v['MaxProImg'];

                }
//                $data[$k]['img']='/public/Upload/cpimg/'.$v['ProImg'];
//                $data[$k]['img']=$v['ProImg'];
//                $data[$k]['maximg']='/public/Upload/cpimg/'.$v['MaxProImg'];
//                $data[$k]['maximg']=$v['MaxProImg'];
            }
        }
//        var_dump($data);exit;
        $cate=Db::name('productcategory')->where('Lay=1 and disable=1 and pid=0')->select();
        $this->view->assign('cate',$cate);
        $this->view->assign('prolist',$data);
        $this->view->assign('page',$list->render());//输出分页的样式
        $this->view->assign('count',$list->total()?$list->total():0);
        return $this->view->fetch('productList');
    }

     /**
      * 商品的删除
      */
    public function productDelete(){
        $proid=$this->request->param();
        if($proid['proid']) {
            if (is_array($proid['proid'])) {//批量删除
                foreach ($proid['proid'] as $pid) {
                    Db::name('product')->where('proid='.$pid)->delete();
                    // 产品删除的同时，购物车里面的产品也相应删除
                    Db::name('shoppingcart')->where('proid='.$pid)->delete();
                    //添加产品删除记录
//                    $deldata["proid"]=$ids[$i];
//                    $deldata["admin"]=Session::get("adminname");
//                    Db::name('delproductrecords')->insert($deldata);
                }
            } else {//针对单个商品的删除
                Db::name('product')->where('proid='.$proid['proid'])->delete();
                // 产品删除的同时，购物车里面的产品也相应删除
                Db::name('shoppingcart')->where('proid='.$proid['proid'])->delete();
            }
            $returnData['status']=1;
            $returnData['msg']='删除成功！';
        }else{
            $returnData['status']=0;
            $returnData['msg']='删除失败！';
        }
        return json($returnData);
    }

    /**
     * 商品的修改
     */

    public function productEdit(){
        $proid=$this->request->param('proid');
        $act=$this->request->param('act');
        if(!empty($act)&&$act=='edit'){
            $productData=$this->request->post();
            if(!empty($_POST['content'])){
                $content=$_POST['content'];
                $productData['content']=str_replace('src="'.Config::get('DOMAIN').'','src="',$content);
            }else{
                $productData['content']='';
            }

            if(empty($productData["ishit"])){
            	$this->error("显示位置不能为空！",url("product/productEdit",['proid'=>$proid]));
            }
            //判断规格参数是否为空（规格和条形码）
            //for($i=0;$i<count($productData['psize']);$i++){
                //if(empty($productData['psize'][$i]) or empty($productData['txm'][$i])){
                //    $this->error("规格和条形码如果填都必须不能为空！",url("product/productEdit",['proid'=>$proid]));
              //  }
            //}
            //产品图片的上传
            $proimg = $this->request->file('proimg');
            if(!empty($proimg)){
                $picsite=getimagesize($_FILES['proimg']['tmp_name']);
                if($picsite[0]!=$picsite[1]){
                    $this->error("请上传1:1的图片！",url("product/productAdd"));
                }
                if($_FILES['proimg']['size']>1024*100*5){
                    $this->error("请上传500k以内的图片！",url("product/productEdit",['proid'=>$proid]));

                }
//                var_dump($_FILES['proimg']['type']);exit;
                $object111=Factory::instance()->getObjectInstance('productphoto');
                $returnDate=$object111->product_photo_action($proimg,'edit',$proid);
                if($returnDate) {
                    $data["ProImg"] = $returnDate['proimgpath'];
                    $data["MaxProImg"] = $returnDate['proMaximgpath'];
                    $data['qiqiuproimgpath'] = $returnDate['qiqiuproimgpath'];
                    $data['qiqiuproMaximgpath'] = $returnDate['qiqiuproMaximgpath'];
                }else {
                    $this->error('产品图片上传失败！', url("product/productAdd"));
                }
            }
            //视频上传
            $video = $this->request->file('video');
            if($video){
                $type1 = explode(".", $_FILES['video']['name']);
                $type = strtolower(end($type1));
                $uploaddirs=ROOT_PATH . 'public' . DS . 'Upload'. DS .'prophoto';
                if ($_FILES['video']['size'] < 100*1024*1024) {
                    if ($type == 'mp4') {
                        $info = $video->move($uploaddirs);
                        if ($info) {

                            $insertdata['videopath'] = '/public/Upload/prophoto/' . $info->getSaveName();
                        } else {
                            // 上传失败获取错误信息
                            echo $video->getError();
                        }
                    } else {
                        $this->error('格式不支持，应为mp4格式！');
                    }
                } else {
                    $this->error('视频过大，应为100M以内！');
                }
            }
            //产品相册的上传
            $photo = $this->request->file('photo');
//            var_dump($photo);exit;
            if(!empty($photo)){
            	$photoImgArr=[];
            	foreach ($photo as $k=> $file){
//            	    var_dump($_FILES['photo']['tmp_name'][$k]);exit;
//            	    var_dump($file['type']);exit;
            	    $picsite=getimagesize($_FILES['photo']['tmp_name'][$k]);
                    if($picsite[0]!=$picsite[1]){
                        $this->error("请上传1:1的图片！",url("product/productAdd"));
                    }
                    if($_FILES['photo']['size'][$k]>102400*5){
                        $this->error("请上传500k以内的图片！",url("product/productEdit",['proid'=>$proid]));

                    }
            		$uploaddir=ROOT_PATH . 'public' . DS . 'Upload'. DS .'prophoto';
            		$info = $file->validate(['size'=>102400*5,'ext'=>'jpg,png,gif,jpeg'])->move($uploaddir);
            		if($info){
            			$photoImgArr[$k]['img']=$info->getPathInfo()->getFilename().'/'.$info->getFilename();
//                        $dasss=$this->uploadfile($info);
//                        if($dasss['status']==0){
//                            $photoImgArr[$k]['imgs']=$dasss['img'];
//                        }else{
                            $photoImgArr[$k]['imgs']='';
//                        }
            		}else{
            			$this->error('产品相册图片上传失败！',url("product/productEdit",['proid'=>$proid]));
            		}
            	}
            }
            //将数据写入数据库
            $data["ProName"]=$productData["proname"];
            $data["Description"]=$productData["description"];
            $data["Keywords"]=$productData["keywords"];
//            $data["goodstype"]=$productData["goodstype"];
            $data["ProductSizeDetail"]=$productData["productsizedetail"];
            $data["Unit"]=$productData["unit"];
            $data["IsOnSell"]=2;
            $data["Weight"]=$productData["weight"];
            $data["Length"]=$productData["length"];
            $data["MinPurchase"]=$productData["minpurchase"];
            $data["OrderStep"]=$productData["orderstep"];
            $data["IsHit"]=$productData["ishit"];
            $data["MarketPrice"]=$productData["marketprice"];
            $data["BalancePrice"]=$productData["balanceprice"];
            $data["EnjoyPrice"]=$productData["EnjoyPrice"];
            $data["VipPrice"]=$productData["vipprice"];
//            $data["Pv"]=$productData["Pv"];
//            $data["ConsumeIntegral"]=$productData["consumeintegral"];
            $data["AfterService"]=$productData["afterservice"];
            $data["peisong"]=$productData["peisong"];
            $data["ProContent"]=$productData["content"];
            //$data["issenddaijinquan"]=$productData['issenddaijinquan'];
//            $data["GiveIntegral"]=$productData['GiveIntegral'];
            $data["moneyOfFirst"]=$productData['moneyOfFirst'];
            $data["moneyOfSecond"]=$productData["moneyOfSecond"];
            $data["moneyOfThird"]=$productData["moneyOfThird"];
            $data["moneyOfEveryFloor"]=$productData["moneyOfEveryFloor"];
            $data["moneyOfFirstLeader"]=$productData["moneyOfFirstLeader"];

            Db::name('product')->where("proid=".$proid)->update($data);
            if(!empty($insertdata)){
                $provideo=Db::name('productvideo')->where("proid=".$proid)->find();
                if($provideo){
                    $whereinsert['proid']=$proid;
                    Db::name('productvideo')->where($whereinsert)->update($insertdata);
                }else{
                    $insertdata['proid']=$proid;
                    Db::name('productvideo')->insert($insertdata);
                }
            }
            //添加商品相册的图片路径到数据库
            if(!empty($photoImgArr)){
            	foreach ($photoImgArr as $photoPath){
            		$photoData["proid"]=$proid;
            		$photoData["imgpath"]=$photoPath['img'];
                    $photoData["qiniuimgpath"]=$photoPath['imgs'];
            		Db::name('productimg')->insert($photoData);
            	}
            }
            //添加商品规格
            if(!empty($productData['psize'][0]) and !empty($productData['txm'][0]) and !empty($productData['pcoler'][0])){
            	$sn=count($productData['psize'])-1;
            	for($i=0;$i<=$sn;$i++){
            		$datast["StyleName"]=$productData['psize'][$i];
            		$datast["StyleName1"]=$productData['pcoler'][$i];
            		$datast["Txm"]=$productData['txm'][$i];
            		$datast["ProId"]=$proid;
            		Db::name('productstock')->insert($datast);
            	}
            }
            
            $this->success("产品修改成功！",url("Product/productList"));
        }else{
            $proinfo=array_change_key_case(Db::name('product')->where('proid='.$proid)->find());
//            print_r($proinfo['procontent']);exit;
            if(is_onqiniu()==true){
                $proinfo['img']=$proinfo['qiqiuproimgpath'];
                $proinfo['maximg']=$proinfo['qiqiupromaximgpath'];
                $proinfo['procontent']= contentReplace($proinfo['procontent']);
//                var_dump($proinfo['procontent']);exit;
            }else{
                if(strpos($proinfo['proimg'],'http://')!==false){
                    $proinfo['img']=$proinfo['proimg'];
                    $proinfo['maximg']=$proinfo['maxproimg'];

                }else{
                    $proinfo['img']=config('IMAGE_DOMAIN_NAME').'/public/Upload/cpimg/'.$proinfo['proimg'];
                    $proinfo['maximg']=config('IMAGE_DOMAIN_NAME').'/public/Upload/cpimg/'.$proinfo['maxproimg'];

                }
            }
            $count=Db::name('productimg')->where("proid=".$proid)->count();
            $provideo=Db::name('productvideo')->where("proid=".$proid)->find();
            if($provideo){
                $show=1;
            }else{
                $show=2;
            }
            $this->view->assign('show',$show);
            $this->view->assign('provideo',$provideo);
            $this->view->assign('count',$count);
            $this->view->assign('list',$proinfo);
            return $this->view->fetch();
        }
    }

    public function productStyle(){
        $proid=$this->request->param('proid');
        $action=$this->request->param('action');
        if($action=="edit"){
            $styleid=$this->request->param("styleid");
            $psizecanshu="psize".$styleid;
            $txmcanshu="txm".$styleid;
            $pcolercanshu="pcoler".$styleid;
            $data["StyleName1"]=$this->request->param($pcolercanshu);
            $data["StyleName"]=$this->request->param($psizecanshu);
            $data["Txm"]=$this->request->param($txmcanshu);
            $data["SupplierId"]=$this->request->param("supid");
            $psizetxmxx=Db::name('productstock')->where("styleid=".$styleid)->update($data);
            $this->redirect(url('Product/productStyle',['proid'=>$proid,'supid'=>$this->request->param("supid")]));
        }elseif($action=="del"){
            $count=Db::name('productstock')->where("proid=".$proid)->count();
            if ($count>1)
            {
                Db::name('productstock')->where("styleid=".$this->request->param("styleid"))->delete();
                $this->redirect(url('Product/productStyle',['proid'=>$proid,'supid'=>$this->request->param("supid")]));

            }

            else{
                $string='<script type="text/javascript">';
                $string.='alert("最后一条数据不能删除！");';
                $string.='location.href="'.url('Product/productStyle',['proid'=>$proid,'supid'=>$this->request->param("supid")]).'";';
                $string.='</script>';
                return $string;
                //$this->error("最后一条数据不能删除！",url('Product/productStyle',['proid'=>$proid,'supid'=>$this->request->param("supid")]));
            }
        }else{
            $psizetxm=indexToLower(Db::name('productstock')->where("proid=".$proid)->select());
            $this->view->assign("plist",$psizetxm);
            return $this->view->fetch();
        }
    }

     public function productPhotoDel(){
         $id=$this->request->param('id');
         $action=$this->request->param('action');
         if($action=="delimg"){
             $sid=$this->request->param('sid');
             $suphordel=Db::name('productimg')->where("id=".$id)->find();
             $horpath= "./Upload/prophoto/".$suphordel["imgpath"];
             Db::name('productimg')->where("id=".$id)->delete();
//             unlink($horpath);
             $this->redirect(url('Product/productPhotoDel',['id'=>$sid]));
         }else{
             $suphornors=indexToLower(Db::name('productimg')->where("proid=".$id)->select());
             if($suphornors){
                 foreach ($suphornors as $k=>$v){
                     if(is_onqiniu()==true){
                         $suphornors[$k]['img']=$v['qiniuimgpath'];
                     }else{
                         if(strpos($v['imgpath'],'http://')!==false){
                             $suphornors[$k]['img']=$v['imgpath'];

                         }else{
                             $suphornors[$k]['img']=config('IMAGE_DOMAIN_NAME').'/public/Upload/prophoto/'.$v['imgpath'];
                         }
                     }
                 }
                 $this->view->assign("listhornor",$suphornors);
             }else{
                 $this->view->assign("listhornor",[]);
             }
             return $this->view->fetch();
         }
     }

     public function productShow(){
         $proid=$this->request->param('proid');
         if($proid){
             $where['ProId']=$proid;
             $ckinfo=Db::name('product')->where($where)->find();
             if(!empty($ckinfo)){
                 $ckinfo=array_change_key_case($ckinfo);

                 if(is_onqiniu()==true){
                     $ckinfo['img']=$ckinfo['qiqiuproimgpath'];
                     $ckinfo['maximg']=$ckinfo['qiqiupromaximgpath'];
                     $ckinfo['procontent']= contentReplace($ckinfo['procontent']);
                 }else{
                     if(strpos($ckinfo['ProImg'],'http://')!==false){
                         $ckinfo['img']=$ckinfo['proimg'];
                         $ckinfo['maximg']=$ckinfo['maxproimg'];

                     }else{
                         $ckinfo['img']=config('IMAGE_DOMAIN_NAME').'/public/Upload/cpimg/'.$ckinfo['ProImg'];
                         $ckinfo['maximg']=config('IMAGE_DOMAIN_NAME').'/public/Upload/cpimg/'.$ckinfo['MaxProImg'];

                     }
//                     $ckinfo['img']=config('IMAGE_DOMAIN_NAME').'/public/Upload/cpimg/'.$ckinfo['proimg'];
//                     $ckinfo['img']=$ckinfo['proimg'];
//                     $ckinfo['maximg']=$ckinfo['maxproimg'];
//                     $ckinfo['maximg']=config('IMAGE_DOMAIN_NAME').'/public/Upload/cpimg/'.$ckinfo['maxproimg'];
                 }
                 $this->view->assign("ckinfo",$ckinfo);
                 $proimglist=indexToLower(Db::name('productimg')->where($where)->select());
                 foreach ($proimglist as $k=>$v){
                     if(is_onqiniu()==true){
                         $proimglist[$k]['img']=$v['qiniuimgpath'];
                     }else{
                         if(strpos($v['imgpath'],'http://')!==false){
                             $proimglist[$k]['img']=$v['proimg'];
                         }else{
                             $proimglist[$k]['img']=config('IMAGE_DOMAIN_NAME').'/public/Upload/prophoto/'.$v['ProImg'];
                         }
//                         $proimglist[$k]['img']=config('IMAGE_DOMAIN_NAME').'/public/Upload/prophoto/'.$v['imgpath'];
//                         $proimglist[$k]['img']=$v['imgpath'];
                     }
                 }
                 $this->view->assign("proimglist",$proimglist);
                 $psizelist=indexToLower(Db::name('productstock')->where($where)->select());
                 $this->view->assign("psizelist",$psizelist);

                 return $this->view->fetch();
             }else{
                 $this->error('该商品不存在');
             }
         }
     }
     
     public function productAction(){
     	$action=$this->request->param('action');
     	if($action=='enable'){
     		$status=$this->request->param('status');
     		$proid=$this->request->param('proid');
			if($status=='nosale'){
				$data["IsOnSell"]=3;
				Db::name('product')->where('proid='.$proid)->update($data);
				Db::name('shoppingcart')->where('proid='.$proid)->delete();
				$returnData['status']=1;
				$returnData['msg']='商品下架成功！';
			}elseif($status=='issale'){
				$data["IsOnSell"]=1;
				Db::name('product')->where('proid='.$proid)->update($data);
				$returnData['status']=1;
				$returnData['msg']='商品上架成功！';
			}elseif($status=='notaudit'){
				$data["IsOnSell"]=4;
				//$streason=urldecode($this->request->param("reason"));
				$data["reason"]=$this->request->param("reason");
				Db::name('product')->where('proid='.$proid)->update($data);
				$returnData['status']=1;
				$returnData['msg']='审核不通过！';
			}
     	}elseif($action=="isindex"){
			$status=$this->request->param('status');
			$proid=$this->request->param('proid');
			if($status=="yes"){
				$data["IsIndex"]=1;
				Db::name('product')->where('proid='.$proid)->update($data);
			}elseif($status=="no"){
				$data["IsIndex"]=0;
				Db::name('product')->where('proid='.$proid)->update($data);
			}
		}
	    return json($returnData);
     }
	 
	public function reasonShow(){
		$proid=$this->request->param('proid');
		$proinfo=Db::name('product')->where("proid=".$proid)->field('reason')->find();
		$this->view->assign("proinfo",$proinfo);
		return $this->view->fetch();
	}

	public function productAdd(){
		if($this->request->param('action')=='add'){
		 	$productData=$this->request->post();
            if(!empty($_POST['content'])){
                $productData['content']=$_POST['content'];
            }else{
                $productData['content']='';
            }

            if(empty($productData["ishit"])){
            	$this->error("显示位置不能为空！",url("product/productAdd"));
            }
            //判断规格参数是否为空（规格和条形码）
            for($i=0;$i<count($productData['psize']);$i++){
            	if(empty($productData['psize'][$i]) or empty($productData['txm'][$i]) or empty($productData['pcoler'][$i])){

                    $this->error("颜色，规格和条形码如果填都必须不能为空！");
                    break;
            	}
            }
            //产品图片的上传
            $proimg = $this->request->file('proimg');
            if(!empty($proimg)){
                $picsite=getimagesize($_FILES['proimg']['tmp_name']);
                if($picsite[0]!=$picsite[1]){
                    $this->error("请上传1:1的图片！",url("product/productAdd"));
                }
                if($_FILES['proimg']['size']>1024*100*5){
                    $this->error("请上传500k以内的图片！",url("product/productAdd"));

                }
                $object=Factory::instance()->getObjectInstance('productphoto');
                $returnDate=$object->product_photo_action($proimg,'add','');
                if($returnDate) {
                    $proimgpath = $returnDate['proimgpath'];
                    $proMaximgpath = $returnDate['proMaximgpath'];
                    $qiqiuproimgpath = $returnDate['qiqiuproimgpath'];
                    $qiqiuproMaximgpath = $returnDate['qiqiuproMaximgpath'];
                }else {
                    $this->error('产品图片上传失败！', url("product/productAdd"));
                }


            }else{
            	$proimgpath="photo.png";
                $proMaximgpath="photo.png";
                $qiqiuproimgpath = "photo.png";
                $qiqiuproMaximgpath = "photo.png";
            }
            $video = $this->request->file('video');
            if($video){
                $type1 = explode(".", $_FILES['video']['name']);
                $type = strtolower(end($type1));
                $uploaddirs=ROOT_PATH . 'public' . DS . 'Upload'. DS .'prophoto';
                if ($_FILES['video']['size'] < 100*1024*1024) {
                    if ($type == 'mp4') {
                        $info = $video->move($uploaddirs);
                        if ($info) {

                            $insertdata['videopath'] = '/public/Upload/prophoto/' . $info->getSaveName();
                        } else {
                            // 上传失败获取错误信息
                            echo $video->getError();
                        }
                    } else {
                        $this->error('格式不支持，应为mp4格式！');
                    }
                } else {
                    $this->error('视频过大，应为100M以内！');
                }
            }
            //产品相册的上传
            $photo = $this->request->file('photo');

            if(!empty($photo)){
            	$photoImgArr=[];
            	foreach ($photo as $k=>$file){
                       $picsite=getimagesize($_FILES['photo']['tmp_name'][$k]);
                    if($picsite[0]!=$picsite[1]){
                        $this->error("请上传1:1的图片！",url("product/productAdd"));
                    }
                    if($_FILES['photo']['size'][$k]>102400*5){
                        $this->error("请上传500k以内的图片！",url("product/productAdd"));

                    }
            		$uploaddir=ROOT_PATH . 'public' . DS . 'Upload'. DS .'prophoto';
            		$info = $file->validate(['size'=>502400,'ext'=>'jpg,png,gif,jpeg'])->move($uploaddir);
//            		var_dump($info);exit;
            		if($info){
            			$photoImgArr[$k]['img']=$info->getPathInfo()->getFilename().'/'.$info->getFilename();
                        $dasss=$this->uploadfile($info);
                        if($dasss['status']==0){
                            $photoImgArr[$k]['imgs']=$dasss['img'];
                        }else{
                            $photoImgArr[$k]['imgs']='';
                        }
            		}else{
            			$this->error('产品相册图片上传失败！',url("product/productAdd"));
            		}
            	}
            }
            
            $pcateinfo=Db::name("productcategory")->where("id=".$productData["pcateid"])->find();
            if($pcateinfo["Lay"]==1){
            	$data["categoryId"]=$productData["pcateid"]; //一级
            	$data["CategoryCode"]=0;//二级
            	$data["CategoryThird"]=0;//三级
            }elseif($pcateinfo["Lay"]==2){
            	$data["categoryId"]=$pcateinfo["oid"]; //一级
            	$data["CategoryCode"]=$productData["pcateid"];//二级
            	$data["CategoryThird"]=0;//三级
            }elseif($pcateinfo["Lay"]==3){
            	$data["categoryId"]=$pcateinfo["oid"]; //一级
            	$data["CategoryCode"]=$pcateinfo["pid"];//二级
            	$data["CategoryThird"]=$_REQUEST["pcateid"];//三级
            }
            
            $data["SupplierId"]=Session::get("supplierid");
            $data["ProName"]=$productData["proname"];
            $data["Description"]=$productData["description"];
            $data["Keywords"]=$productData["keywords"];
            //$data["goodstype"]=$productData["goodstype"];
            $data["ProductSizeDetail"]=$productData["productsizedetail"];
            $data["Unit"]=$productData["unit"];
            $data["IsOnSell"]=2;
            $data["Weight"]=$productData["weight"];
            $data["Length"]=$productData["length"];
            $data["MinPurchase"]=$productData["minpurchase"];
            $data["OrderStep"]=$productData["orderstep"];
            $data["IsHit"]=$productData["ishit"];
            //$data["issenddaijinquan"]=$productData['issenddaijinquan'];
//            $data["GiveIntegral"]=$productData['GiveIntegral'];
            //$data["moneyOfFirst"]=$productData['moneyOfFirst'];
            //$data["moneyOfSecond"]=$productData["moneyOfSecond"];
            //$data["moneyOfThird"]=$productData["moneyOfThird"];
            //$data["moneyOfEveryFloor"]=$productData["moneyOfEveryFloor"];
            //$data["moneyOfFirstLeader"]=$productData["moneyOfFirstLeader"];

            if(empty($productData["marketprice"])){
                $data["MarketPrice"]=0;
            }else{
                $data["MarketPrice"]=$productData["marketprice"];
            }
            if(empty($productData["balanceprice"])){
                $data["BalancePrice"]=0;
            }else{
                $data["BalancePrice"]=$productData["balanceprice"];
            }
            if(empty($productData["vipprice"])){
                $data["VipPrice"]=0;
            }else{
                $data["VipPrice"]=$productData["vipprice"];
            }
            if(empty($productData["EnjoyPrice"])){
                $data["EnjoyPrice"]=0;
            }else{
                $data["EnjoyPrice"]=$productData["EnjoyPrice"];
            }
            if(empty($productData["Pv"])){
                $data["Pv"]=0;
            }else{
                $data["Pv"]=$productData["Pv"];
            }
            if(empty($productData["consumeintegral"])){
                $data["ConsumeIntegral"]=0;
            }else{
                $data["ConsumeIntegral"]=$productData["consumeintegral"];
            }
            if(empty($productData["GiveIntegral"])){
                $data["GiveIntegral"]=0;
            }else{
                $data["GiveIntegral"]=$productData["GiveIntegral"];
            }
            $data["AfterService"]=$productData["afterservice"];
            $data["peisong"]=$productData["peisong"];
            $data["ProImg"]=$proimgpath;//缩略图
            $data["MaxProImg"]=$proMaximgpath;//主图
            $data["qiqiuproimgpath"]=$qiqiuproimgpath;//七牛云缩略图
            $data["qiqiuproMaximgpath"]=$qiqiuproMaximgpath;//七牛云主图
            $data["ProContent"]=$productData["content"];

            $proid=Db::name('product')->insertGetId($data);
            if(!empty($insertdata)){
                $insertdata['proid']=$proid;
                Db::name('productvideo')->insert($insertdata);
            }

            //添加商品相册的图片路径到数据库
            if(!empty($photoImgArr)){
            	$cover=$productData['cover'];
            	foreach ($photoImgArr as $key=>$photoPath){
            		if ($cover == ($key + 1)) {
            			$photoData["cover"] = 1;
            		} else {
            			$photoData["cover"] = 0;
            		}
            		$photoData["proid"]=$proid;
            		$photoData["imgpath"]=$photoPath['img'];
                    $photoData["qiniuimgpath"]=$photoPath['imgs'];
            		Db::name('productimg')->insert($photoData);
            	}
            }
            //添加商品规格
            $sn=count($productData['psize'])-1;
            for($i=0;$i<=$sn;$i++){
            	$datast["StyleName"]=$productData['psize'][$i];
            	$datast["StyleName1"]=$productData['pcoler'][$i];
            	$datast["Txm"]=$productData['txm'][$i];
            	$datast["ProId"]=$proid;
            	Db::name('productstock')->insert($datast);
            }           
            
            $this->success("产品添加成功！",url("Product/productList"));
		}

		$supplierid=Session::get('supplierid');
        $supcatelist=Db::name('supplierpcat')->where("supid=".$supplierid." and pcatelevel=1")->select();
        if($supcatelist){
            foreach($supcatelist as $n=> $val){
                $supcatelist[$n]['voo']=Db::name('supplierpcat')->where("pid=".$val['pcateid']." and supid=".$supplierid)->select();
                if($supcatelist[$n]['voo']){
                    foreach($supcatelist[$n]['voo'] as $m=> $valm){
                        $supcatelist[$n]['voo'][$m]['boo']=Db::name('supplierpcat')->where("pid=".$valm['pcateid']." and supid=".$supplierid)->select();
                    }
                }

            }
        }
//        $supcatelist=Db::name('productcategory')->where(" Lay=1")->select();
//        if($supcatelist){
//            foreach($supcatelist as $n=> $val){
//                $supcatelist[$n]['voo']=Db::name('productcategory')->where("pid=".$val['Id'])->select();
//                if($supcatelist[$n]['voo']){
//                    foreach($supcatelist[$n]['voo'] as $m=> $valm){
//                        $supcatelist[$n]['voo'][$m]['boo']=Db::name('productcategory')->where("pid=".$valm['Id'])->select();
//                    }
//                }
//
//            }
//        }
		$this->view->assign("listone",$supcatelist);
		return $this->view->fetch();
	}

     //商品内容里面的所属分类修改
     public function product_category_edit(){
         if($this->request->param("action")=="edit"){
             $pcateid=$this->request->param("pcateid");
             $pcateinfo=Db::name("productcategory")->where("id=".$pcateid)->field('lay,oid,pid')->find();
             if($pcateinfo["lay"]==1){
                 $datacpcate["categoryId"]=$pcateid; //一级
                 $datacpcate["CategoryCode"]=0;//二级
                 $datacpcate["CategoryThird"]=0;//三级
             }elseif($pcateinfo["lay"]==2){
                 $datacpcate["categoryId"]=$pcateinfo["oid"]; //一级
                 $datacpcate["CategoryCode"]=$pcateid;//二级
                 $datacpcate["CategoryThird"]=0;//三级
             }elseif($pcateinfo["lay"]==3){
                 $datacpcate["categoryId"]=$pcateinfo["oid"]; //一级
                 $datacpcate["CategoryCode"]=$pcateinfo["pid"];//二级
                 $datacpcate["CategoryThird"]=$pcateid;//三级
             }
             Db::name("product")->where("proid=".$this->request->param("proid"))->update($datacpcate);
         }else{
             $proid=$this->request->param("proid");
             $supplierid=Session::get("supplierid");
             $supcatelist=indexToLower(Db::name("supplierpcat")->where("supid=".$supplierid." and pcatelevel=1")->select());
             if($supcatelist){
                 foreach($supcatelist as $n=> $val){
                     $supcatelist[$n]['voo']=indexToLower(Db::name("supplierpcat")->where("pid=".$val['pcateid']." and supid=".$supplierid)->select());
                     if($supcatelist[$n]['voo']){
                         foreach($supcatelist[$n]['voo'] as $m=> $valm){
                             $supcatelist[$n]['voo'][$m]['boo']=indexToLower(Db::name("supplierpcat")->where("pid=".$valm['pcateid']." and supid=".$supplierid)->select());
                         }
                     }
                 }
             }
             $prolist=array_change_key_case(Db::name("product")->where("proid=".$proid)->find());
             $this->view->assign("listone",$supcatelist);
             $this->view->assign("list",$prolist);
             return $this->view->fetch('product_category_edit');
         }
     }

     private function uploadfile($file)
     {
         vendor('qiuqiu.php-sdk.autoload');
         $accessKey = config('ACCESSKEY');
         $secretKey = config('SECRETKEY');
         $bucket = config('BUCKET');
         $domain = config('DOMAIN');
//        print_r($accessKey);
//        print_r($secretKey);
//        print_r($bucket);
//        print_r($domain);exit;
         $filePath = $file->getRealPath();
         $ext = pathinfo($file->getInfo('name'), PATHINFO_EXTENSION);  //后缀
         $key =substr(md5($file->getRealPath()) , 0, 5). date('YmdHis') . rand(0, 9999) . '.' . $ext;
         $auth = new Aath($accessKey, $secretKey);
         $token = $auth->uploadToken($bucket);
         $uploadMgr = new UploadManager();
         list($ret, $err) = $uploadMgr->putFile($token, $key, $filePath);
//var_dump($uploadMgr->putFile($token, $key, $filePath));exit;
         if ($err !== null) {
             $return['status']=1;
             $return['msg']='上传错误';
         } else {
             $return['img']=$domain.$ret['key'];
             $return['status']=0;
             $return['msg']='上传成功';
         }
         return $return;
     }
}
