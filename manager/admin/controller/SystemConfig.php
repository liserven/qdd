<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/23
 * Time: 15:47
 */

namespace app\admin\controller;
\think\Loader::import('controller/Jump', TRAIT_PATH, EXT);

use app\admin\Controller;
use think\Session;
use think\Db;
use think\Exception;


class SystemConfig  extends Controller
{
    use \traits\controller\Jump;

    public function index(){
        return $this->systemConfig();
    }
    //系统基本配置
    public function systemConfig(){
        $act=$this->request->param('act');
        $id=1;
        $systemConfig=Db::name('otherconfig')->where("id=".$id)->find();
        if(empty($act)){//系统信息编辑页面的显示
            $this->view->assign("list",$systemConfig);
            return $this->view->fetch('systemConfig');
        }elseif($act=='edit') {//系统信息的编辑
            $systemConfigData=$this->request->param();
            unset($systemConfigData['act']);

            //图片的上传
            $file = $this->request->file('logo');
            if(empty($file)){
                $systemConfigData["logo"]=$systemConfig['logo'];
            }else{
                // 移动到框架应用根目录/Uploads/webAd目录下
                $uploaddir=ROOT_PATH . 'public' . DS . 'Public'. DS .'static'.DS.'images'.DS .'web';
                $info = $file->validate(['size'=>1024000,'ext'=>'jpg,png,gif,jpeg'])->move($uploaddir);
                if($info){
                    $filename='/public/Public/static/images/web/'.$info->getPathInfo()->getFilename().'/'.$info->getFilename();
                }else{
                    $this->error("图片上传失败！",url("SystemConfig/systemConfig"));
                }
                $systemConfigData["logo"]=$filename;
            }

            Db::name('otherconfig')->where("id=".$id)->update($systemConfigData);//修改数据库
//            return json(['status'=>1,'msg'=>'修改成功']);
            $this->success("修改成功！",url("SystemConfig/systemConfig"));
        }
    }
}