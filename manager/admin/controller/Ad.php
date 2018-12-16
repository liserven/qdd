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

class Ad extends Controller
{
    public function index(){
        return $this->adList();
    }
    public function adList(){
        $adlist=Db::name('adlist');
        if($this->request->param('action')=='delete'){
            $id=$this->request->param("id");
            $adlist->where("ID=" .$id )->delete();
        }else{
            $list = \think\Db::view('adlist', '*')
                ->view('adcategory', 'CategoryName', 'adcategory.ID=adlist.CategoryId')
                ->order('adlist.ID desc')
                ->paginate(10);
            $counts=$adlist->select();
            $count=count($counts);
        $page = $list->render();
        $this->view->assign('listone',$list);
        $this->view->assign('page', $page);
            $this->view->assign('count', $count);
        return $this->view->fetch('adList');
        }
    }
    public function adAdd(){
        if($this->request->param('act')=='add'){
            $data=$this->request->post();
            if(empty($data['CategoryId'])){
                $this->error('请选择广告分组！', url("Ad/adAdd"));
            }
            $data['AddDate']=date('Y-m-d H:i:s');
            if($_FILES){
                $photofile = request()->file('AdPicture');
                if(!empty($photofile)){
                    $info = $photofile->move(ROOT_PATH . 'public' . DS . 'uploads');
                    if ($info) {
                        $data['AdPicture'] = '/public/uploads/' . $info->getSaveName();
                    } else {
                        // 上传失败获取错误信息
                        echo $photofile->getError();
                    }
                }else{
                    $this->error('广告图片不能为空！', url("Ad/adAdd"));
                }
            }
            $res = Db::name('adlist')->insert($data);
            if ($res) {
                $this->success("添加成功！", url("Ad/adList"));

            } else {
                $this->success("添加失败！", url("Ad/adAdd"));
            }
        }else{
        $adgroup=Db::name('adcategory');
        $adlist = $adgroup->field('CategoryName,ID')->select();
        $this->view->assign("listone", $adlist);
        return $this->view->fetch('adAdd');
        }
    }
    public function adEdit(){

        if($this->request->param('act')=='edit'){
            $where['ID']=$this->request->param('id');
            $data=$this->request->post();
            if(empty($data['CategoryId'])){
                $this->error('请选择广告分组！');
            }
            if($_FILES){
                $photofile = request()->file('AdPicture');
                if(!empty($photofile)){
                    $info = $photofile->move(ROOT_PATH . 'public' . DS . 'uploads');
                    if ($info) {
                        $data['AdPicture'] = '/public/uploads/' . $info->getSaveName();
                    } else {
                        // 上传失败获取错误信息
                        echo $photofile->getError();
                    }
                }
            }
            $res = Db::name('adlist')->where($where)->update($data);
            if ($res) {
                $this->success("修改成功！", url("Ad/adList"));

            } else {
                $this->success("修改失败！");
            }
        }else{
        $where['ID']=$this->request->param('id');
        $adlist=Db::name('adlist');
        $list=$adlist->where($where)->find();
        $adgroup=Db::name('adcategory');
        $res=$adgroup->select();
        //var_dump($list);exit;
        $this->view->assign('listone',$list);
        $this->view->assign('adcate',$res);
        return $this->view->fetch('adEdit');
        }
    }
}