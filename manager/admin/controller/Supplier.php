<?php
namespace app\admin\controller;

\think\Loader::import('controller/Jump', TRAIT_PATH, EXT);

use app\admin\Controller;
use think\Db;
use think\Image;

class Supplier extends Controller
{
    use \traits\controller\Jump;

    public function index(){
        return  $this->supplierList();
    }

    /**
     * 商家列表
     * @return string
     */
    public function supplierList(){
        $where['ID']=['>','0'];

        //根据商家id进行查找
        if($this->request->param('supid')){
            $supid=$this->request->param('supid');
            $where['ID']=$supid;
            $map['supid']=$supid;
        }

        //根据商家名称进行查找
        if($this->request->param('supname')){
            $supname=$this->request->param('supname');
            $where['Name']=['like','%'.$supname.'%'];
            $map['supname']=$supname;
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

        $suplist=Db::name('supplier')->where($where)->order('ID desc')->paginate();//根据条件分页输出

        $data=[];
        foreach($suplist as $n=>$val){
            $data[$n]=$val=array_change_key_case($val);
            $data[$n]["voo"]=indexToLower(Db::name('supplierpcat')->where("supid=".$val["id"])->select());
        }
        if(!empty($map)) {
            foreach ($map as $key => $value) {
                $suplist->appends($key, $value);//分页链接中添加请求的参数
            }
        }

        $this->view->assign("list",$data);
        $this->view->assign('page',$suplist->render());// 赋值分页输出
        $this->view->assign("count",$suplist->total());
        return $this->view->fetch('supplierList');
    }

    public function supplierEdit(){
        $id=$this->request->param('id');
        $act=$this->request->param('act');
        if($act=='show'){//商家编辑页面的显示
            $suplist=array_change_key_case(Db::name('supplier')->where("id=".$id)->find());
            $this->view->assign("list",$suplist);
            $supcates=indexToLower(Db::name('supplierpcat')->where("supid=".$id." and pcatelevel=1")->select());
            if($supcates){
                foreach($supcates as $n=>$val){
                    $supcates[$n]['voo']=indexToLower(Db::name('supplierpcat')->where('pid='.$val['pcateid'].' and supid='.$id)->select());
                    foreach($supcates[$n]['voo'] as $m=> $valm){
                        $supcates[$n]['voo'][$m]['boo']=indexToLower(Db::name('supplierpcat')->where('pid='.$valm['pcateid'].' and supid='.$id)->select());
                    }
                }
            }
            $this->view->assign("listcate",$supcates);
            $suphornors=Db::name('supplierhornor')->where("sid=".$id)->select();
            $this->view->assign("supplierId",$id);
            if($suphornors){
                $this->view->assign("listhornor",$suphornors);
            }else{
                $this->view->assign("listhornor",[]);
            }

            //商家分类
            $where1['ID'] = ['<>', $suplist['suppliertype']];
            $suppliertype=Db::name('suppliertype')->where($where1)->select();
            if($suppliertype){
                $this->view->assign('suppliertype',$suppliertype);
            }else{
                $this->view->assign("suppliertype",[]);
            }

            return $this->view->fetch('supplierEdit');
        }elseif($act=='edit') {//商家信息的编辑
            $supData=$this->request->param();
            $photo = $this->request->file('photo');
            if(!empty($photo)){
                $photoImgArr=[];
                foreach ($photo as $file){
                    $uploaddir=ROOT_PATH . 'public' . DS . 'Upload'. DS .'supplier'. DS .'licence';
                    $info = $file->validate(['size'=>3145728,'ext'=>'jpg,png,gif,jpeg'])->move($uploaddir);
                    if($info){
                        $photoImgArr[]=$info->getPathInfo()->getFilename().'/'.$info->getFilename();
                    }else{
                        $this->error($info->getError(),url('Supplier/supplierEdit',['act'=>'edit','id'=>$id]));
                    }
                }
            }

            //商家logo start
            $proimg = $this->request->file('proimg');//pc端logo
            if(!empty($proimg)) {
                //打开图片
                $image = Image::open($proimg);
                if($image->width()!=80 or $image->height()!=80 ){
                    $this->error("PC端logo尺寸必须为80*80！");
                }

                //商家logo图片保存地址
                $uploaddir = ROOT_PATH . 'public' . DS . 'Upload' . DS . 'supplier' . DS . 'supplierLogo'.DS.$supData["id"].DS.'PC';
                $imgpath= '/public/Upload/supplier/supplierLogo/'.$supData["id"].'/'.'PC/';//保存到数据库的图片地址路径
                $info = $proimg->validate(['size' => 1024000, 'ext' => 'jpg,png,gif,jpeg'])->move($uploaddir);
                if ($info) {
                    $filename = $info->getPathInfo()->getFilename() . '/' . $info->getFilename();
                    $data["SupplierLogo"] = $imgpath.$filename;
                } else {
                    $this->error("商家logo上传失败[PC]！");
                }
            }
            $proimgwap = $this->request->file('proimgwap');//wap端logo
            if(!empty($proimgwap)) {
                //打开图片
                $imagewap = Image::open($proimgwap);
                if($imagewap->width()!=60 or $imagewap->height()!=60 ){
                    $this->error("App端logo尺寸必须为60*60！");
                }

                //商家logo图片保存地址
                $uploaddirwap = ROOT_PATH . 'public' . DS . 'Upload' . DS . 'supplier' . DS . 'supplierLogo'.DS.$supData["id"].DS.'APP';
                $imgpathwap= '/public/Upload/supplier/supplierLogo/'.$supData["id"].'/'.'APP/';//保存到数据库的图片地址路径
                $infowap = $proimgwap->validate(['size' => 1024000, 'ext' => 'jpg,png,gif,jpeg'])->move($uploaddirwap);
                if ($infowap) {
                    $filenamewap = $infowap->getPathInfo()->getFilename() . '/' . $infowap->getFilename();
                    $data["SupplierLogoForApp"] = $imgpathwap.$filenamewap;
                } else {
                    $this->error("商家logo上传失败[WAP]！");
                }
            }
            //商家logo end

            //商家banner start
            $proimgBannerPc = $this->request->file('bannerPc');//pc端banner
            if(!empty($proimgBannerPc)) {
                //打开图片
                $imageBannerPc = Image::open($proimgBannerPc);
                if($imageBannerPc->width()!=1000 or $imageBannerPc->height()!=200 ){
                    $this->error("PC端banner尺寸必须为1000*200！");
                }

                //商家banner图片保存地址
                $uploaddirBannerPc = ROOT_PATH . 'public' . DS . 'Upload' . DS . 'supplier' . DS . 'supplierBanner'.DS.$supData["id"].DS.'PC';
                $imgpathBannerPc= '/public/Upload/supplier/supplierBanner/'.$supData["id"].'/'.'PC/';//保存到数据库的图片地址路径
                $infoBannerPc = $proimgBannerPc->validate(['size' => 1024000, 'ext' => 'jpg,png,gif,jpeg'])->move($uploaddirBannerPc);
                if ($infoBannerPc) {
                    $filenameBannerPc = $infoBannerPc->getPathInfo()->getFilename() . '/' . $infoBannerPc->getFilename();
                    $data["SupplierBannerForPc"] = $imgpathBannerPc.$filenameBannerPc;
                } else {
                    $this->error("商家banner上传失败[PC]！");
                }
            }
            $proimgBannerApp = $this->request->file('bannerApp');//wap端banner
            if(!empty($proimgBannerApp)) {
                //打开图片
                $imageBannerApp = Image::open($proimgBannerApp);
                $rate=($imageBannerApp->width()/$imageBannerApp->height());
                if(floor($rate*10)/10!=1.6 ){
                    $this->error("APP端banner尺寸必须为640*400或者宽高比例为1.6！");
                }

                //商家banner图片保存地址
                $uploaddirBannerApp = ROOT_PATH . 'public' . DS . 'Upload' . DS . 'supplier' . DS . 'supplierBanner'.DS.$supData["id"].DS.'APP';
                $imgpathBannerApp= '/public/Upload/supplier/supplierBanner/'.$supData["id"].'/'.'APP/';//保存到数据库的图片地址路径
                $infoBannerApp = $proimgBannerApp->validate(['size' => 1024000, 'ext' => 'jpg,png,gif,jpeg'])->move($uploaddirBannerApp);
                if ($infoBannerApp) {
                    $filenameBannerApp = $infoBannerApp->getPathInfo()->getFilename() . '/' . $infoBannerApp->getFilename();
                    $data["SupplierBannerForApp"] = $imgpathBannerApp.$filenameBannerApp;
                } else {
                    $this->error("商家banner上传失败[App]！");
                }
            }
            //商家banner end

            $data["Name"]=$supData["name"];
            $data["Mobile"]=$supData["mobile"];
            $data["WeChatName"]=$supData["wechatname"];
            $data["TelPhone"]=$supData["telphone"];
            $data["BankAccount"]=$supData["bankaccount"];
            $data["BankName"]=$supData["bankname"];
            $data["BankInfo"]=$supData["bankinfo"];
            $data["BankSupName"]=$supData["banksupname"];
            $data["SupNetwork"]=$supData["supnetwork"];
            $data["Province"]=$supData["province"];
            $data["City"]=$supData["city"];
            $data["Area"]=$supData["area"];
            $data["Address"]=$supData["address"];
            $data["Remark"]=$_POST['content'];;
            $data["EditDate"]=date('Y-m-d H:i:s',time());
            $data['SupplierType']=$supData['suppliertypeid'];//商家分类

            $supplierInfo=Db::name('supplier')->where("id=".$supData["id"])->find();
            $data["WithdrawalType"]=$supData["WithdrawalType"];
            if($supData["WithdrawalType"]==1){
                if($supData["WithdrawalAmountRate"]>1){
                    $this->error("可提现比率不能大于1！");
                }
                $data["WithdrawalAmountRate"]=$supData["WithdrawalAmountRate"];
                $data["WithdrawalAmount"]=$supData["WithdrawalAmountRate"]*$supplierInfo['Account'];
            }elseif($supData["WithdrawalType"]==2){
                if($supData["WithdrawalAmount"]>$supplierInfo['Account']){
                    $this->error("可提现金额不能大于账户余额！");
                }
                $data["WithdrawalAmountRate"]='1';
                $data["WithdrawalAmount"]=$supData["WithdrawalAmount"];
            }
            $data["WithdrawalAmountLimit"]=$supData["WithdrawalAmountLimit"];


            Db::name('supplier')->where("id=".$supData["id"])->update($data);//修改数据库
            if(!empty($photoImgArr)){
                foreach ($photoImgArr as $photoPath){
                    $datah["sid"]=$id;
                    $datah["imgpath"]=$photoPath;
                    Db::name('supplierhornor')->insert($datah);
                }
            }
            $this->success("商家修改成功",url('Supplier/supplierList'));
        }
    }

    public function supplier_map(){

        $act=$this->request->param('act');
        $supid=$this->request->param('supid');
        $this->view->assign("supplierId",$supid);
        if($act=='show') {
            return $this->view->fetch('supplier_map');
        }else{
            $sid=$this->request->param('sid');
            $lng=$this->request->param('lng');
            $lat=$this->request->param('lat');
            $data['MapLng']=$lng;
            $data['MapLat']=$lat;
            Db::name('supplier')->where("id=".$sid)->update($data);//修改数据库
        }
    }

    /**
     * 添加商家
     * @return string
     */
    public function supplierAdd(){
        $act=$this->request->param('act');
        if($act=='add'){

            $supData=$this->request->param();
            if($supData["supproduct"][0]==""){
                $this->error("请选择主营产品");
            }
            $photo = $this->request->file('photo');
            if(!empty($photo)){
                $photoImgArr=[];
                foreach ($photo as $file){
                    $uploaddir=ROOT_PATH . 'public' . DS . 'Upload'. DS .'supplier'. DS .'licence';
                    $info = $file->validate(['size'=>3145728,'ext'=>'jpg,png,gif,jpeg'])->move($uploaddir);
                    if($info){
                        $photoImgArr[]=$info->getPathInfo()->getFilename().'/'.$info->getFilename();
                    }else{
                        $this->error($info->getError(),url('Supplier/supplierEdit',['act'=>'edit','id'=>$id]));
                    }
                }
            }

            $data["Name"]=$supData["name"];
            $data["LoginPasswd"]=md5($supData["loginpasswd"]);
            if(!empty($supData["ReferrerID"])){
                $data["ReferrerID"]=$supData["ReferrerID"];
            }
            $data["Mobile"]=$supData["mobile"];
            $data["WeChatName"]=$supData["wechatname"];
            $data["TelPhone"]=$supData["telphone"];
            $data["BankAccount"]=$supData["bankaccount"];
            $data["BankName"]=$supData["bankname"];
            $data["BankInfo"]=$supData["bankinfo"];
            $data["BankSupName"]=$supData["banksupname"];
            $data["SupNetwork"]=$supData["supnetwork"];
            $data["Province"]=$supData["province"];
            $data["City"]=$supData["city"];
            $data["Area"]=$supData["area"];
            $data["Address"]=$supData["address"];
            $data["Remark"]=$_POST['content'];
            $data['SupplierType']=$supData['suppliertypeid'];//商家分类

            $data["WithdrawalType"]=$supData["WithdrawalType"];
            if($supData["WithdrawalType"]==1){
                if($supData["WithdrawalAmountRate"]>1){
                    $this->error("可提现比率不能大于1！");
                }
                $data["WithdrawalAmountRate"]=$supData["WithdrawalAmountRate"];
                $data["WithdrawalAmount"]=0;
            }elseif($supData["WithdrawalType"]==2){
                $data["WithdrawalAmountRate"]='1';
                $data["WithdrawalAmount"]=$supData["WithdrawalAmount"];
            }
            $data["WithdrawalAmountLimit"]=$supData["WithdrawalAmountLimit"];

            $data["AddDate"]=date('Y-m-d H:i:s');
            $supsid=Db::name('supplier')->insertGetId($data);//添加数据并获得此数据在数据库中的id


            if($supsid){
                if(!empty($photoImgArr)){
                    foreach ($photoImgArr as $photoPath){
                        $datah["sid"]=$supsid;
                        $datah["imgpath"]=$photoPath;
                        $picid=Db::name('supplierhornor')->insert($datah);
                    }
                }
                $supproduct=$supData["supproduct"];
                foreach($supproduct as $key=>$value){
                    $dataf["pcateid"]=$supproduct[$key]; //产品栏目id
                    $dataf["supid"]=$supsid; //商家ID
                    $dataf["pcatelevel"]=getpclevelbyid($supproduct[$key]); //产品栏目id级别
                    $dataf["pid"]=getpcpidbyid($supproduct[$key]); //产品栏目id的父id 也就是上一级栏目id
                    $catid=Db::name('supplierpcat')->insert($dataf);
                }

                $this->success("商家添加成功",url('Supplier/supplierList'));
            }
        }elseif($act=='show'){
            $pcatelistonly=indexToLower(Db::name("productcategory")->where("pid=0")->order("sort asc")->select());
            $pcatelist=indexToLower(Db::name("productcategory")->where("pid=0")->order("sort asc")->select());
            foreach($pcatelist as $n=> $val){
                $pcatelist[$n]['voo']=indexToLower(Db::name("productcategory")->where('pid='.$val['id'])->order("sort asc")->select());
                foreach($pcatelist[$n]['voo'] as $m=> $valm){
                    $pcatelist[$n]['voo'][$m]['boo']=indexToLower(Db::name("productcategory")->where('pid='.$valm['id'])->order("sort asc")->select());
                }
            }

            //商家分类
            $suppliertype=Db::name('suppliertype')->select();
            if($suppliertype){
                $this->view->assign('suppliertype',$suppliertype);
            }else{
                $this->view->assign("suppliertype",[]);
            }

            $this->view->assign("listonly",$pcatelistonly);
            $this->view->assign("listone",$pcatelist);
            return $this->view->fetch('supplierAdd');
        }

    }

    /**
     * 删除指定的商家
     * @return \think\response\Json
     */

    public function supplierDelete(){
        //删除商家
        $supid=$this->request->param('supid');
        if($supid){
            Db::name("supplier")->where('id='.$supid)->delete();
            Db::name("product")->where("supplierid=".$supid)->delete();
            return json(['status'=>1,'msg'=>'删除成功!']);
        }else{
            return json(['status'=>0,'msg'=>'请求参数错误!']);
        }
    }

    /**
     * 商家密码的修改
     * @return string
     */
    public function supplierChangePassword(){
        if($this->request->param("action")=="edit"){
            $supid=$this->request->param("supid");
            $newpassword=$this->request->param("newpassword");
            $data["LoginPasswd"]=md5($newpassword);
            Db::name("supplier")->where("id=".$supid)->update($data);
        }else{
            $supid=$this->request->param("supid");
            $userinfo=array_change_key_case(Db::name("supplier")->where("id=".$supid)->find());
            $this->view->assign("userinfo",$userinfo);
            return $this->view->fetch('supplierChangePassword');
        }
    }

    /**
     * 商家合作的启用与停止
     */
    public function supplierCooperate(){
        $status=$this->request->param("status");
        $supid=$this->request->param("supid");
        if($status=='yes'){
            $data["IsAudit"]=0;
            Db::name("supplier")->where("id=".$supid)->update($data);
            return json(['status'=>1,'msg'=>'已启用!']);
        }elseif($status=='stop'){
            $data["IsAudit"]=1;
            Db::name("supplier")->where("id=".$supid)->update($data);
            $datap["IsOnSell"]=3;
            Db::name("product")->where("supplierid=".$supid)->update($datap);
            return json(['status'=>1,'msg'=>'已停止合作!']);
        }else{
            return json(['status'=>0,'msg'=>'请求参数错误!']);
        }
    }

    /**
     * 商家详情
     */

    public function supplierShow(){
        $supid=$this->request->param('supid');
        $suplist=array_change_key_case(Db::name("supplier")->where("id=".$supid)->find());
        $this->view->assign("list",$suplist);
        $supcates=indexToLower(Db::name("supplierpcat")->where("supid=".$supid." and pcatelevel=1")->select());
        if($supcates){
            foreach($supcates as $n=>$val){
                $supcates[$n]['voo']=indexToLower(Db::name("supplierpcat")->where('pid='.$val['pcateid'].' and supid='.$supid)->select());
                foreach($supcates[$n]['voo'] as $m=> $valm){
                    $supcates[$n]['voo'][$m]['boo']=indexToLower(Db::name("supplierpcat")->where('pid='.$valm['pcateid'].' and supid='.$supid)->select());
                }
            }
        }
        $this->view->assign("listcate",$supcates);
        $suphornors=indexToLower(Db::name("supplierhornor")->where("sid=".$supid)->select());
        if($suphornors){
            $this->view->assign("listhornor",$suphornors);
        }else{
            $this->view->assign("listhornor",[]);
        }
        return $this->view->fetch('supplierShow');
    }

    /**
     * 商家经营范围的设置
     */

    public function supplierStyle(){
        if($this->request->param("action")=="add"){
            $styleData=$this->request->param();
            $supsid=$styleData['supsid'];
            $supproduct=$styleData['supproduct'];
            $pcondition["supid"]=$supsid;
            Db::name("supplierpcat")->where($pcondition)->delete();
            //print_r($styleData);exit;
            foreach($supproduct as $key=>$value){
                $dataf["pcateid"]=$supproduct[$key]; //产品栏目id
                $dataf["supid"]=$supsid; //商家ID
                $dataf["pcatelevel"]=getpclevelbyid($supproduct[$key]); //产品栏目id级别
                $dataf["pid"]=getpcpidbyid($supproduct[$key]); //产品栏目id的父id 也就是上一级栏目id
                Db::name("supplierpcat")->insert($dataf);
            }
            $list=Db::name('supplierpcat')->where('pcatelevel=2 and supid="'.$supsid.'"')->group('pid')->select();
            $pcateid=Db::name('supplierpcat')->field('pcateid')->where('pcatelevel=1 and pid=0')->find();
            if($list){
                foreach ($list as $k=>$v){
                    if($v['pid']==$pcateid['pcateid']){
                        continue;
                    }else{
                        $dataf1["pcateid"]=$list[$k]['pid']; //产品栏目id
                        $dataf1["supid"]=$supsid; //商家ID
                        $dataf1["pcatelevel"]=getpclevelbyid($v['pid']); //产品栏目id级别
                        $dataf1["pid"]=getpcpidbyid($v['pid']); //产品栏目id的父id 也就是上一级栏目id
                        Db::name("supplierpcat")->insert($dataf1);
                    }
                }
            }

        }else{
            $pcatelistonly=indexToLower(Db::name("productcategory")->where("pid=0")->order("sort asc")->select());
            $pcatelist=indexToLower(Db::name("productcategory")->where("pid=0")->order("sort asc")->select());
            foreach($pcatelist as $n=> $val){
                $pcatelist[$n]['voo']=indexToLower(Db::name("productcategory")->where('pid='.$val['id'])->order("sort asc")->select());
                foreach($pcatelist[$n]['voo'] as $m=> $valm){
                    $pcatelist[$n]['voo'][$m]['boo']=indexToLower(Db::name("productcategory")->where('pid='.$valm['id'])->order("sort asc")->select());
                }
            }
            $this->view->assign("listonly",$pcatelistonly);
            $this->view->assign("listone",$pcatelist);
            return $this->view->fetch('supplierStyle');
        }
    }

    /**
     * 商家资质图片的删除
     * @return mixed
     */
    public function supplier_hornor_del(){
        if($this->request->param("action")=="delimg"){//商家资质图片的删除
            $id=$this->request->param("id");
            $sid=$this->request->param("sid");
            $suphordel=Db::name('supplierhornor')->where("id=".$id)->find();
            $horpath= "./Upload/supplier/licence/".$suphordel["imgpath"];
            Db::name('supplierhornor')->where("id=".$id)->delete();
            if(file_exists($horpath)&&is_file($horpath)){
                unlink($horpath);
            }
            $this->redirect(url("Supplier/supplier_hornor_del",['sid'=>$sid]));
        }else{
            $suphornors=Db::name('supplierhornor')->where("sid=".$this->request->param("sid"))->select();
            if($suphornors){
                $this->view->assign("listhornor",$suphornors);
            }
            return $this->view->fetch('supplier_hornor_del');
        }
    }
//echo '<pre>';
//print_r($data);
//echo '</pre>';
//exit();
}
