<?php
/**
 * tpAdmin [a web admin based ThinkPHP5]
 *
 * @author yuan1994 <tianpian0805@gmail.com>
 * @link http://tpadmin.yuan1994.com/
 * @copyright 2016 yuan1994 all rights reserved.
 * @license http://www.apache.org/licenses/LICENSE-2.0
 */

//------------------------
// 公开不授权控制器
//-------------------------

namespace supplier\index\controller;
use think\Exception;
use think\Request;
use think\Session;
use think\Db;
class Freight extends Auth
{
    /**快递列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function index(){

        $where['supplierid']=Session::get('supplierid');
        $list = Db::name('deliverytype')->where($where)->order("id desc")->paginate(15);
        $data=[];
        if($list){
            foreach($list as $n=>$val){
                $data[$n]=$val=array_change_key_case($val);
            }
        }

        if(!empty($map)) {
            foreach ($map as $key => $value) {
                $list->appends($key, $value);//分页链接中添加请求的参数
            }
        }

        $this->view->assign("list",$data);
        $this->view->assign('page',$list->render());// 赋值分页输出
        return $this->view->fetch('index');
    }

    /**添加快递信息
     * @return \think\response\Json
     */
    public function deliverytypeadd(){
        if(Request()->post()){
            $data=Request()->post();
            if(empty($data['name'])){
                return json(['status'=>0,'msg'=>'快递名称不能为空']);
            }
            if(empty($data['sort'])){
                $newdata['sort']=255;
            }else{
                $newdata['sort']=$data['sort'];
            }
            $newdata['name']=$data['name'];
            $newdata['description']=$data['description'];
            $newdata['is_on']=$data['is_on'];
            $newdata['supplierid']=Session::get('supplierid');
            $where['supplierid']=Session::get('supplierid');
            $where['name']=$data['name'];
            $count=Db::name('deliverytype')->where($where)->count();
            if($count>0){
                return json(['status'=>0,'msg'=>'该配送快递已存在']);
            }else{
                $res=Db::name('deliverytype')->insert($newdata);
                if($res){
                    return json(['status'=>1,'msg'=>'添加成功']);
                }else{
                    return json(['status'=>0,'msg'=>'添加失败']);
                }
            }

        }
        return $this->view->fetch('deliverytypeadd');
    }
    /**编辑快递信息
     * @return \think\response\Json
     */
    public function deliverytypeedit(){
        $id=$this->request->param('id');
        if(Request()->post()){
            $data=Request()->post();
            $where['id']=$id;
            if(empty($data['name'])){
                return json(['status'=>0,'msg'=>'快递名称不能为空']);
            }
            if(empty($data['sort'])){
                $data['sort']=255;
            }
            $datanew=$data;
            $where['supplierid']=Session::get('supplierid');
            Db::name('deliverytype')->where($where)->update($datanew);
            return json(['status'=>1,'msg'=>'修改成功']);
        }
        $where['id']=$id;
        $where['supplierid']=Session::get('supplierid');
        $list = Db::name('deliverytype')->where($where)->find();
        if($list){
            $this->view->assign('list',$list);
        }else{
            $this->error('传递参数有误','Freight/index');
        }
        return  $this->view->fetch('deliverytypeedit');
    }

    /**快递的删除开通和停用
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function deliverytype_action(){
        $act=$this->request->param('act');
        $id=$this->request->param('id');
        if($act=='start'){
            $where['id']=$id;
            $where['supplierid']=Session::get('supplierid');
            $count=Db::name('deliverytype')->where($where)->count();
            if($count>0){
                $data['is_on']=1;
                $res=Db::name('deliverytype')->where($where)->update($data);
                if($res){
                    return json(['status'=>1,'已启用']);
                }else{
                    return json(['status'=>0,'启用失败']);
                }
            }else{
                return json(['status'=>0,'传递参数有误']);
            }
        }elseif($act=='del'){
            $where['ID']=$id;
            $where['SupplierId']=Session::get('supplierid');
            $count=Db::name('deliverytype')->where($where)->count();
            if($count>0){
                if($this->freight_del(Session::get('supplierid'),$id)){
                    $action=Db::name('deliverytype')->where($where)->delete();
                    if($action){
                        $this->success('删除成功','Freight/index');
                    }else{
                        $this->error('传递参数有误');
                    }
                }else{
                    $this->error('删除失败');
                }
            }else{
                $this->error('传递参数有误');
            }
        }elseif($act=='stop'){
            $where['id']=$id;
            $where['supplierid']=Session::get('supplierid');
            $count=Db::name('deliverytype')->where($where)->count();
            if($count>0){
                $data['is_on']=2;
                $res=Db::name('deliverytype')->where($where)->update($data);
                if($res){
                    return json(['status'=>1,'已停用']);
                }else{
                    return json(['status'=>0,'停用失败']);
                }
            }else{
                return json(['status'=>0,'传递参数有误']);
            }
        }else{
            $this->error('传递参数有误');
        }
    }

    /**快递列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function freight(){
        $where['supplierid']=Session::get('supplierid');
        $list = Db::name('deliverytype')->where($where)->order("id desc")->paginate(15);
        $data=[];
        if($list){
            foreach($list as $n=>$val){
                $data[$n]=$val=array_change_key_case($val);
            }
        }
        if(!empty($map)) {
            foreach ($map as $key => $value) {
                $list->appends($key, $value);//分页链接中添加请求的参数
            }
        }

        $this->view->assign("list",$data);
        $this->view->assign('page',$list->render());// 赋值分页输出
        return $this->view->fetch('freight');
    }

    /**查看当前快递已经设置的快递运费
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function freightlist(){
        $id=$this->request->param('id');
        $where['SupplierId']=Session::get('supplierid');
        $where['freighttype']=$id;

        $list = Db::name('freightnew')->where($where)->paginate();
//        var_dump(Db::name('freightnew')->getLastSql());exit;
        $this->view->assign('page',$list->render());
        $this->view->assign('list',$list);
        $this->view->assign('id',$id);
         return  $this->view->fetch('freightlist');
    }

    /**添加配送区域
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function freightadd(){
//        var_dump(Request()->post());exit;
        $id=$this->request->param('id');
        $this->view->assign('id',$id);

        if(Request()->post()){
            $data=Request()->post();
            $frieght['Name']=$data['name'];
            if($this->checkname($data['name'])==false){
                return json(['status'=>0,'msg'=>'已经存在一个同名的配送区域!']);
            }
            if(empty($data['arer'])){
                return json(['status'=>0,'msg'=>'配送区域不能为空!']);
            }
            $frieght['SupplierId']=Session::get('supplierid');
            $frieght['type']=$data['type'];
            $frieght['freighttype']=$id;
            if($data['type']==1){
                $frieght['Heavy']=$data['Heavy'];
                $frieght['HeavyMoney']=$data['HeavyMoney'];
                $frieght['ContinuedHeavy']=$data['ContinuedHeavy'];
                $frieght['ContinuedHeavyMoney']=$data['ContinuedHeavyMoney'];
                $frieght['MoneyFreightFree']=$data['MoneyFreightFree'];
            }elseif ($data['type']==2){
                $frieght['CountMoney']=$data['CountMoney'];
                $frieght['ContinuedCountMoney']=$data['ContinuedCountMoney'];
                $frieght['CountFreightFree']=$data['CountFreightFree'];
            }
            Db::startTrans();
            try{
           $ids=Db::name('freightnew')->insertGetId($frieght);

            if($ids){
                $arer=$data['arer'];
                foreach ($arer as $v){
                    if($this->checkfriegth($v,$id)==true){
                    $freigh_to_province['provincecitycounty_id']=$v;
                    $freigh_to_province['freighrnew_Id']=$ids;
                    $re=Db::name('freigh_to_province')->insert($freigh_to_province);
                    if(!$re){
                        Db::rollback();
                        return json(['status'=>0,'msg'=>'添加失败']);
                    }
                    }else{
                        Db::rollback();
                        return json(['status'=>0,'msg'=>'存在已经设置过的地区']);
                    }
                }
                Db::commit();
                return json(['status'=>1,'msg'=>'添加成功','id'=>$id]);
            }else{
                Db::rollback();
                return json(['status'=>0,'msg'=>'添加失败']);
            }
            }catch (\Exception $e){
                return json(['status'=>0,'msg'=>'添加失败']);
            }
        }
        $province=Db::name('provincecitycounty')->field('Code,AreaName')->where('ParentCode=0')->select();
        $this->view->assign('province',$province);
        return $this->view->fetch('freightadd');
    }

    /**编辑配送区域
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function freightedit(){
        $id=$this->request->param('id');
        $typeid=$this->request->param('typeid');
        if(Request()->post()){
            $data=Request()->post();
            $frieght['Name']=$data['name'];
            if($this->checkname($data['name'],$id)==false){
                return json(['status'=>0,'msg'=>'已经存在一个同名的配送区域!']);
            }
            if(empty($data['arer'])){
                return json(['status'=>0,'msg'=>'配送区域不能为空!']);
            }
            $frieght['SupplierId']=Session::get('supplierid');
            $frieght['type']=$data['type'];
            $frieght['freighttype']=$typeid;
            if($data['type']==1){
                $frieght['Heavy']=$data['Heavy'];
                $frieght['HeavyMoney']=$data['HeavyMoney'];
                $frieght['ContinuedHeavy']=$data['ContinuedHeavy'];
                $frieght['ContinuedHeavyMoney']=$data['ContinuedHeavyMoney'];
                $frieght['MoneyFreightFree']=$data['MoneyFreightFree'];
            }elseif ($data['type']==2){
                $frieght['CountMoney']=$data['CountMoney'];
                $frieght['ContinuedCountMoney']=$data['ContinuedCountMoney'];
                $frieght['CountFreightFree']=$data['CountFreightFree'];
            }
            $where['supplierid']=Session::get('supplierid');
            $where['freighttype']=$typeid;
            $where['Id']=$id;
            Db::startTrans();
            try{
                Db::name('freightnew')->where($where)->update($frieght);
                    $wherea['freighrnew_Id']=$id;
                    Db::name('freigh_to_province')->where($wherea)->delete();
                    $arer=$data['arer'];
                    foreach ($arer as $v){
                        if($this->checkfriegth($v,$id)==true){
                            $freigh_to_province['provincecitycounty_id']=$v;
                            $freigh_to_province['freighrnew_Id']=$id;
                            $re=Db::name('freigh_to_province')->insert($freigh_to_province);
                            if(!$re){
                                Db::rollback();
                                return json(['status'=>0,'msg'=>'编辑失败']);
                            }
                        }else{
                            Db::rollback();
                            return json(['status'=>0,'msg'=>'存在已经设置过的地区']);
                        }
                    }
                    Db::commit();
                    return json(['status'=>1,'msg'=>'编辑成功','id'=>$typeid]);
            }catch (\Exception $e){
                return json(['status'=>0,'msg'=>'编辑失败']);
            }
        }
        $where['Id']=$id;
        $where['supplierid']=Session::get('supplierid');
        $list=Db::name('freightnew')->where($where)->find();
        if($list){
            $names=Db::view('freigh_to_province')
                ->view('provincecitycounty','AreaName','freigh_to_province.provincecitycounty_id=provincecitycounty.Code')
                ->where('freigh_to_province.freighrnew_Id='.$id)
                ->select();
            $this->view->assign('name',$names);
        }else{
            $this->error('传递参数有误');
        }
        $province=Db::name('provincecitycounty')->field('Code,AreaName')->where('ParentCode=0')->select();
        $this->view->assign('province',$province);
        $this->view->assign('id',$typeid);
        $this->view->assign('list',$list);
        return $this->view->fetch('freightedit');
    }
    /**获取城市县区
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function getcity(){
        $code=$this->request->post('code');
        if($code){
            $where['ParentCode']=$code;
            $res=Db::name('provincecitycounty')->field('Code,AreaName')->where($where)->select();
            if($res){
                return json(['status'=>1,'msg'=>'成功','data'=>$res]);
            }
        }else{
            return json(['status'=>1,'msg'=>'传递方式有误']);
        }

    }
    /**删除配送区域
     * @return int
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function freight_action(){
        $id=$this->request->param('id');
        $typeid=$this->request->param('typeid');
            $where['Id']=$id;
            $where['SupplierId']=Session::get('supplierid');
            $res=Db::name('freightnew')->where($where)->find();
            if($res){
                    $count=Db::name('freigh_to_province')->where('freighrnew_Id='.$id)->count();
                    if($count>0){
                        $resu=Db::name('freigh_to_province')->where('freighrnew_Id='.$id)->delete();
                       if(!$resu){
                           $this->error('删除失败');
                       }
                    }
                $resul=Db::name('freightnew')->where($where)->delete();
                    if($resul){
                        $this->success('删除成功',url('Freight/freightlist',['id'=>$typeid]));
                    }else{
                        $this->error('删除失败');
                    }
            }else{
                $this->error('传递参数有误');
            }
    }
    /** 删除当前快递下的快递运费信息
     * @param $supplierid
     * @param $id
     * @return bool
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    protected  function freight_del($supplierid,$id){
        $where['supplierid']=$supplierid;
        $where['type']=$id;
        $res=Db::name('freightnew')->where($where)->select();
        if($res){
            foreach ($res as $k=>$v){
                $resu=Db::name('freigh_to_province')->where('freighrnew_Id='.$v['Id'])->select();
                if($resu){
                    $del=Db::name('freigh_to_province')->where('freighrnew_Id='.$v['Id'])->delete();
                    if($del){
                        continue;
                    }else{
                        return false;
                    }
                }else{
                    continue;
                }
            }
            $re=Db::name('freightnew')->where($where)->delete();
            if($re){
                return true;
            }else{
                return false;
            }
        }else{
            return true;
        }

    }

    /**查看当前商家当前快递该地区是否设置过
     * @param $id 快递id
     * @param $code 地区code
     * @return bool
     */
    protected function checkfriegth($code,$id){
        $where['SupplierId']=Session::get('supplierid');
        $where['freighttype']=$id;
        $where['provincecitycounty_id']=$code;
        $count=Db::view('freightnew')
            ->view('freigh_to_province','provincecitycounty_id','freigh_to_province.freighrnew_Id=freightnew.Id')
            ->where($where)
            ->count();
//        var_dump($count);exit;
        if($count>0){
            return false;
        }else{
            return true;
        }
    }

    /**查询当前商家设置的配送区域名称是否存在
     * @param $name
     * @return bool
     */
    protected function checkname($name,$id=null){
        $where['SupplierId']=Session::get('supplierid');
        $where['Name']=$name;
        if(!empty($id)){
            $where['Id']=['neq',$id];
        }
        $re=Db::name('freightnew')->where($where)->count();
        if($re>0){
            return false;
        }else{
            return true;
        }
    }

}
