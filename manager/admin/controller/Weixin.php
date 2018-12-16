<?php
/**
 * Created by 赵晓凡
 * User: zhaoxiaofan
 * Date: 2017/3/27
 * Time: 14:42
 */

namespace app\admin\controller;

use app\admin\Controller;
use think\Db;

class Weixin extends Controller
{
    //公众号列表
    public function index(){
        $list=Db::name('weixinconfig')->select();
        $this->view->assign('list',$list);
        return $this->view->fetch('list');
    }

    //微信公众号的添加
    public function account_add(){
        $type=$this->request->param('type');
        if(!empty($type)&&$type=='add'){
            $postdata=$this->request->post();
            $data['name']=$postdata['name'];
            $data['appid']=$postdata['appid'];
            $data['appsecret']=$postdata['appsecret'];
            $id=Db::name('weixinconfig')->insertGetId($data);
            if(!empty($id)){
                $returnData['status']=1;
                $returnData['msg']='添加成功！';
            }else{
                $returnData['status']=0;
                $returnData['msg']='添加失败！';
            }
            return $returnData;
        }

        return $this->view->fetch('account_add');
    }

    //公众号账号信息的修改
    public function account_edit(){
        $type=$this->request->param('type');
        $id=$this->request->param('id');
        if(!empty($type)&&$type=='edit'){
            $postdata=$this->request->post();
            $data['name']=$postdata['name'];
            $data['appid']=$postdata['appid'];
            $data['appsecret']=$postdata['appsecret'];
            Db::name('weixinconfig')->where('id='.$id)->update($data);
            $returnData['status']=1;
            $returnData['msg']='修改成功！';

            return $returnData;
        }
        $config=Db::name('weixinconfig')->where('id='.$id)->find();
        $this->view->assign('config',$config);
        return $this->view->fetch('account_edit');
    }


    //公众号账号信息的修改
    public function account_del(){
        $id=$this->request->param('id');
        Db::name('weixinconfig')->where('id='.$id)->delete();
        $returnData['status']=1;
        $returnData['msg']='删除成功！';
        return $returnData;
    }

    //菜单列表
    public function menu(){
        $accountid=$this->request->param('accountid');
        $menulist=Db::name('weixinmenu')->where('pid=0 and accountid='.$accountid)->select();
        foreach ($menulist as $key=>$val){
            $menulist[$key]['sub']=Db::name('weixinmenu')->where('pid='.$val['id'])->select();
        }
        $this->view->assign('menulist',$menulist);
        $this->view->assign('accountid',$accountid);
        return $this->view->fetch('menu');
    }

    //菜单的添加
    public function menu_add(){
        $accountid=$this->request->param('accountid');
        $type=$this->request->param('type');
        if(!empty($type)&&$type=='add'){
            $requestData=$_POST;
            $this->menu_field_validate($requestData);
            $data['name']=$requestData['menuname'];
            $data['type']=empty($requestData['menutype'])?0:$requestData['menutype'];
            $data['value']=empty($requestData['menukey'])?'':$requestData['menukey'];
            $data['sort']=$requestData['menusort'];
            $data['pid']=$requestData['pid'];
            $data['accountid']=$accountid;
            $id=Db::name('weixinmenu')->insertGetId($data);
            if($id){
                $returnData['status']=1;
                $returnData['msg']='添加成功！';
            }else{
                $returnData['status']=0;
                $returnData['msg']='添加失败！';
            }
            return json($returnData);
        }else{
            $list=Db::name('weixinmenu')->where('pid=0 and accountid='.$accountid)->select();
            $this->view->assign('accountid',$accountid);
            $this->view->assign('list',$list);
            return $this->view->fetch('menu_add');
        }
    }

    //菜单的修改
    public function menu_edit(){
        $accountid=$this->request->param('accountid');
        $type=$this->request->param('type');
        $id=$this->request->param('id');
        if(!empty($type)&&$type=='edit'){
            $requestData=$_POST;
            $this->menu_field_validate($requestData);
            $data['name']=$requestData['menuname'];
            $data['type']=empty($requestData['menutype'])?0:$requestData['menutype'];
            $data['value']=empty($requestData['menukey'])?'':$requestData['menukey'];
            $data['sort']=$requestData['menusort'];
            $data['pid']=$requestData['pid'];
            Db::name('weixinmenu')->where('id='.$id)->update($data);

            $returnData['status']=1;
            $returnData['msg']='修改成功！';
            return json($returnData);
        }else{
            $menuinfo=Db::name('weixinmenu')->where('id='.$id)->find();
            $list=Db::name('weixinmenu')->where('pid=0 and accountid='.$accountid)->select();
            $this->view->assign('menuinfo',$menuinfo);
            $this->view->assign('id',$id);
            $this->view->assign('list',$list);
            $this->view->assign('accountid',$accountid);
            return $this->view->fetch('menu_edit');
        }
    }

    //提交数据的非空验证
    public function menu_field_validate($requestData){
        if(empty($requestData['menuname'])){
            return json(['status'=>0,'msg'=>'菜单名称不能为空']);
        }
    }


    /**
     * 删除微信菜单
     * @return \think\response\Json
     */
    public function del_menu(){
        $id=$this->request->param('id');
        $menu=Db::name('weixinmenu')->where('id='.$id)->find();
        if($menu['pid']==0){
            $submenu=Db::name('weixinmenu')->where('pid='.$menu['id'])->count();
            if($submenu){
                $returnData['status']=0;
                $returnData['msg']='请先删除子菜单！';
                return json($returnData);
            }
        }
        $flag=Db::name('weixinmenu')->where('id='.$id)->delete();
        if($flag){
            $returnData['status']=1;
            $returnData['msg']='菜单删除成功！';
        }else{
            $returnData['status']=0;
            $returnData['msg']='菜单删除失败！';
        }
        return json($returnData);
    }


    //文字回复列表
    public function text_reply(){
        $accountid=$this->request->param('accountid');
        $text_reply_list=Db::name('weixin_textreply')->where('accountid='.$accountid)->select();
        $this->view->assign('accountid',$accountid);
        $this->view->assign('list',$text_reply_list);
        return $this->view->fetch('text_reply');
    }

    //文字回复的添加
    public function text_reply_add(){
        $accountid=$this->request->param('accountid');
        $type=$this->request->param('type');
        if(!empty($type)&&$type=='add'){
            $requestData=$_POST;
            if(empty($requestData['keyword'])){
                return json(['status'=>0,'msg'=>'关键字不能为空']);
            }
            if(empty($requestData['content'])){
                return json(['status'=>0,'msg'=>'回复内容不能为空']);
            }
            $keywordinfo = Db::name('weixin_textreply')->where("accountid=".$accountid." and keyword='".$requestData['keyword']."'")->find();
            if(empty($keywordinfo)){
                $data['keyword']=$requestData['keyword'];
                $data['content']=$requestData['content'];
                $data['matching']=$requestData['matching'];
                $data['createtime']=date('Y-m-d H:i:s',time());
                $data['enable']=0;
                $data['accountid']=$accountid;

                $insertid=Db::name('weixin_textreply')->insertGetId($data);
                if($insertid){
                    return json(['status'=>1,'msg'=>'添加成功']);
                }else{
                    return json(['status'=>0,'msg'=>'添加失败']);
                }
            }else{
                return json(['status'=>0,'msg'=>'关键字：'.$requestData['keyword'].'已存在！']);
            }
        }
        $this->view->assign('accountid',$accountid);
        return $this->view->fetch('text_reply_add');
    }

    //文字回复的修改
    public function text_reply_edit(){
        $accountid=$this->request->param('accountid');
        $id=$this->request->param('id');
        $type=$this->request->param('type');
        if(!empty($type)&&$type=='edit'){
            $requestData=$_POST;
            if(empty($requestData['keyword'])){
                return json(['status'=>0,'msg'=>'关键字不能为空']);
            }
            if(empty($requestData['content'])){
                return json(['status'=>0,'msg'=>'回复内容不能为空']);
            }

            $data['keyword']=$requestData['keyword'];
            $data['content']=$requestData['content'];
            $data['matching']=$requestData['matching'];
            $data['updatetime']=date('Y-m-d H:i:s',time());

            Db::name('weixin_textreply')->where('id='.$id)->update($data);
            return json(['status'=>1,'msg'=>'修改成功']);
        }

        $replyinfo=Db::name('weixin_textreply')->where('id='.$id)->find();
        $this->view->assign('replyinfo',$replyinfo);
        $this->view->assign('accountid',$accountid);
        return $this->view->fetch('text_reply_edit');
    }

    //文字回复的删除
    public function text_reply_del(){
        $id=$this->request->param('id');
        Db::name('weixin_textreply')->where('id='.$id)->delete();
        return json(['status'=>1,'msg'=>'删除成功']);
    }

    //关键字回复的启用与禁用
    public function text_reply_action(){
        $accountid=$this->request->param('accountid');
        $action=$this->request->param('action');
        $id=$this->request->param('id');
        if(!empty($action)){
            if($action=='enable'){//启用
                $data=Db::name('weixin_textreply')->where('id='.$id)->find();
                $machData=Db::query("select * from weixin_textreply where accountid=".$accountid." and keyword='".$data['keyword']."'");
                foreach ($machData as $key=>$val){
                    if($val['id']==$id){
                        $editdata['enable']=1;
                    }else{
                        $editdata['enable']=0;
                    }
                    Db::name('weixin_textreply')->where('id='.$val['id'])->update($editdata);
                }
                $machNewsData=Db::query("select * from weixin_newsreply where accountid=".$accountid." and keyword='".$data['keyword']."'");
                foreach ($machNewsData as $k=>$v){
                    $editdata['enable']=0;
                    Db::name('weixin_newsreply')->where('id='.$v['id'])->update($editdata);
                }
                return json(['status'=>1,'msg'=>'启用成功']);
            }elseif($action=='disable'){//禁用
                $editdata['enable']=0;
                Db::name('weixin_textreply')->where('id='.$id)->update($editdata);
                return json(['status'=>1,'msg'=>'禁用成功']);
            }
        }else{
            return json(['status'=>0,'msg'=>'操作参数不能为空']);
        }
    }


    //图文回复列表
    public function news_reply(){
        $accountid=$this->request->param('accountid');
        $news_reply_list=Db::name('weixin_newsreply')->where('accountid='.$accountid)->select();
        $this->view->assign('accountid',$accountid);
        $this->view->assign('list',$news_reply_list);
        return $this->view->fetch('news_reply');
    }

    //图文回复的添加
    public function news_reply_add(){
        $accountid=$this->request->param('accountid');
        $type=$this->request->param('type');
        if(!empty($type)&&$type=='add'){
            $requestData=$_POST;
            $this->field_validate($requestData);//非空验证

            $data['accountid']=$accountid;
            $data['keyword']=$requestData['keyword'];
            $data['text']=$requestData['text'];
            $data['pic']=$requestData['pic'];
            $data['url']=$requestData['url'];
            $data['title']=$requestData['title'];
            $data['createtime']=date('Y-m-d H:i:s',time());
            $data['usort']=empty($requestData['usort'])?1:$requestData['usort'];
            $data['matching']=$requestData['matching'];
            $data['enable']=0;

            $insertid=Db::name('weixin_newsreply')->insertGetId($data);
            if($insertid){
                return json(['status'=>1,'msg'=>'添加成功']);
            }else{
                return json(['status'=>0,'msg'=>'添加失败']);
            }
        }
        $this->view->assign('accountid',$accountid);
        return $this->view->fetch('news_reply_add');
    }


    //图文回复的修改
    public function news_reply_edit(){
        $accountid=$this->request->param('accountid');
        $id=$this->request->param('id');
        $type=$this->request->param('type');
        if(!empty($type)&&$type=='edit'){
            $requestData=$_POST;
            $this->field_validate($requestData);//非空验证

            $data['keyword']=$requestData['keyword'];
            $data['text']=$requestData['text'];
            $data['pic']=$requestData['pic'];
            $data['url']=$requestData['url'];
            $data['title']=$requestData['title'];
            $data['uptatetime']=date('Y-m-d H:i:s',time());
            $data['usort']=empty($requestData['usort'])?1:$requestData['usort'];
            $data['matching']=$requestData['matching'];

            Db::name('weixin_newsreply')->where('id='.$id)->update($data);
            return json(['status'=>1,'msg'=>'修改成功']);
        }
        $replyinfo=Db::name('weixin_newsreply')->where('id='.$id)->find();
        $this->view->assign('replyinfo',$replyinfo);
        $this->view->assign('accountid',$accountid);
        $this->view->assign('id',$id);
        return $this->view->fetch('news_reply_edit');
    }

    //文字回复的删除
    public function news_reply_del(){
        $id=$this->request->param('id');
        Db::name('weixin_newsreply')->where('id='.$id)->delete();
        return json(['status'=>1,'msg'=>'删除成功']);
    }


    //图文回复的启用与禁用
    public function news_reply_action(){
        $accountid=$this->request->param('accountid');
        $action=$this->request->param('action');
        $id=$this->request->param('id');
        if(!empty($action)){
            if($action=='enable'){//启用
                $data=Db::name('weixin_newsreply')->where('id='.$id)->find();
                $editdata['enable']=1;
                Db::name('weixin_newsreply')->where('id='.$id)->update($editdata);

                $machTextData=Db::query("select * from weixin_textreply where accountid=".$accountid." and keyword='".$data['keyword']."'");
                foreach ($machTextData as $k=>$v){
                    $editdata['enable']=0;
                    Db::name('weixin_textreply')->where('id='.$v['id'])->update($editdata);
                }
                return json(['status'=>1,'msg'=>'启用成功']);
            }elseif($action=='disable'){//禁用
                $editdata['enable']=0;
                Db::name('weixin_newsreply')->where('id='.$id)->update($editdata);
                return json(['status'=>1,'msg'=>'禁用成功']);
            }
        }else{
            return json(['status'=>0,'msg'=>'操作参数不能为空']);
        }
    }


    //提交数据的非空验证
    public function field_validate($requestData){
        if(empty($requestData['keyword'])){
            return json(['status'=>0,'msg'=>'关键字不能为空']);
        }
        if(empty($requestData['title'])){
            return json(['status'=>0,'msg'=>'标题不能为空']);
        }
        if(empty($requestData['pic'])){
            return json(['status'=>0,'msg'=>'图片地址不能为空']);
        }
        if(empty($requestData['url'])){
            return json(['status'=>0,'msg'=>'跳转地址不能为空']);
        }
        if(empty($requestData['text'])){
            return json(['status'=>0,'msg'=>'简介不能为空']);
        }
    }

    //回复图片的上传
    public function image_upload(){
        //产品图片的上传
        $proimg = $this->request->file('imageupload');
        if(!empty($proimg)){
            $uploaddir=ROOT_PATH . 'public' . DS . 'Upload'. DS .'weixinimg';
            if(!file_exists($uploaddir)){
                $this->mkDirs1($uploaddir);
            }
            $info = $proimg->validate(['size'=>1024000,'ext'=>'jpg,png,gif,jpeg'])->move($uploaddir);
            if($info){
                $filename=$info->getPathInfo()->getFilename().'/'.$info->getFilename();
                $imgpath=config('IMAGE_DOMAIN_NAME').'/Upload/weixinimg/'.$filename;
                echo json_encode(['status'=>1,'msg'=>'图片上传成功！','imgurl'=>$imgpath]);
                exit();
            }else{
                echo json_encode(['status'=>0,'msg'=>'图片上传失败！']);
                exit();
            }
        }
    }

    //递归创建目录
    public function mkDirs1($path){
        if(is_dir($path)){//已经是目录了就不用创建
            return true;
        }
        if(is_dir(dirname($path))){//父目录已经存在，直接创建
            return mkdir($path);
        }
        $this->mkDirs1(dirname($path));//从子目录往上创建
        return mkdir($path);//因为有父目录，所以可以创建路径
    }

    /**
     * 微信自定义菜单的设置
     * @return \think\response\Json
     */
    public function set_menu(){
        $accountid=$this->request->param('accountid');
        if(!empty($accountid)){
            $data=['is_menu_open'=>1];
            $temp=[];
            $menuData=Db::name('weixinmenu')->where('pid=0 and accountid='.$accountid)->order('sort')->select();
            if(count($menuData)>3){
                $returnData['status']=0;
                $returnData['msg']='最多3个一级菜单！';
                return json($returnData);
            }
            foreach ($menuData as $key=>$val){
                $temp[$key]['name']=$val['name'];
                if($val['type']==1){
                    $temp[$key]['type']='click';
                    $temp[$key]['key']=$val['value'];
                }elseif($val['type']==2){
                    $temp[$key]['type']='view';
                    $temp[$key]['url']=$val['value'];
                }
                $subMenu=Db::name('weixinmenu')->where('pid='.$val['id'])->order('sort desc')->select();
                if(count($subMenu)>5){
                    $returnData['status']=0;
                    $returnData['msg']='最多5个二级菜单！';
                    return json($returnData);
                }
                foreach ($subMenu as $k=>$v){
                    if($v['type']==1){
                        $temp[$key]['sub_button'][$k]['type']='click';
                        $temp[$key]['sub_button'][$k]['name']=$v['name'];
                        $temp[$key]['sub_button'][$k]['key']=$v['value'];
                    }elseif($v['type']==2){
                        if(strpos($v['value'],'http://') === false){
                            $returnData['status']=0;
                            $returnData['msg']='菜单"'.$v['name'].'"的跳转链接缺少"http://"前缀！';
                            return json($returnData);
                        }
                        $temp[$key]['sub_button'][$k]['type']='view';
                        $temp[$key]['sub_button'][$k]['name']=$v['name'];
                        $temp[$key]['sub_button'][$k]['url']=$v['value'];
                    }

                }
            }
            $data['button']=$temp;
            $data=json_encode($data,JSON_UNESCAPED_UNICODE);
            $url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=".$this->getToken($accountid);
            $res = $this->https_request($url, $data);
            $res=json_decode($res, true);
            if($res['errmsg']=='ok'){
                $returnData['status']=1;
                $returnData['msg']='菜单设置成功！';
            }else{
                $returnData['status']=0;
                $returnData['msg']=$res['errmsg'];
            }
        }else{
            $returnData['status']=0;
            $returnData['msg']='账户id不能为空！';
        }
        return json($returnData);
    }

    //获取token
    private function getToken($accountid)
    {
        $wxpay_config = Db::name('weixinconfig')->where('id='.$accountid)->find();
        $appid = $wxpay_config['appid'];
        $appsecret = $wxpay_config['appsecret'];

        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$appid."&secret=".$appsecret;
        $res = $this->https_request($url);
        $result = json_decode($res, true);
        return $result["access_token"];
    }

    //https请求（支持GET和POST）
    protected function https_request($url, $data = null)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        if (!empty($data)){
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }
}