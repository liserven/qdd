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

namespace app\admin\controller;

\think\Loader::import('controller/Jump', TRAIT_PATH, EXT);

use app\admin\Controller;
use think\Session;
use think\Db;
use think\Config;
use think\View;
use think\Request;

class Payconfig extends Controller
{
    use \traits\controller\Jump;

    public function index(){
        return $this->payconfig();
    }

    /**
     * 订单列表
     * @return string
     */
    public function payconfig(){
        $where['id']=1;
        if($_POST){
            $wxappid=$this->request->param('wxappid');
            if(!empty($wxappid)){
                $updata['wxappid']=$this->request->param('wxappid');
            }else{
                $updata['wxappid']=NULL;
            }
            $wxmchid=$this->request->param('wxmchid');
            if(!empty($wxmchid)){
                $updata['wxmchid']=$this->request->param('wxmchid');
            }else{
                $updata['wxmchid']=NULL;
            }
            $wxkey=$this->request->param('wxkey');
            if(!empty($wxkey)){
                $updata['wxkey']=$this->request->param('wxkey');
            }else{
                $updata['wxkey']=NULL;
            }
            $wxappsecret=$this->request->param('wxappsecret');
            if(!empty($wxappsecret)){
                $updata['wxappsecret']=$this->request->param('wxappsecret');
            }else{
                $updata['wxappsecret']=NULL;
            }
            $aliapp_id=$this->request->param('aliapp_id');
            if(!empty($aliapp_id)){
                $updata['aliapp_id']=$this->request->param('aliapp_id');
            }else{
                $updata['aliapp_id']=NULL;
            }
            $merchant_private_key=$this->request->param('merchant_private_key');
            if(!empty($merchant_private_key)){
                $updata['merchant_private_key']=$this->request->param('merchant_private_key');
            }else{
                $updata['merchant_private_key']=NULL;
            }
            $merchant_private_key=$this->request->param('merchant_private_key');
            if(!empty($merchant_private_key)){
                $updata['merchant_private_key']=$this->request->param('merchant_private_key');
            }else{
                $updata['merchant_private_key']=NULL;
            }
            Db::name('payconfig')->where($where)->update($updata);
            return json(['code'=>200,'msg'=>'配置成功']);
        }
        $data=Db::name('payconfig')->where($where)->find();
        $this->view->assign('list',$data);
        return $this->view->fetch('payconfig');
    }
}


