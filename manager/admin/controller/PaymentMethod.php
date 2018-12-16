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
use think\Exception;
use think\View;
use think\Request;

class PaymentMethod extends Controller
{
    use \traits\controller\Jump;

    public function index(){
        return $this->paymentList();
    }

    public function paymentList(){

        $where['PaymentId']=['>','0'];
        $count=Db::name('paymentmethod')->where($where)->count();//获取满足条件的总记录数
        $list=Db::name('paymentmethod')->where($where)->paginate();//根据条件分页输出

        $this->view->assign('paymentmethod',$list);
        $this->view->assign('page',$list->render());//输出分页的样式
        $this->view->assign('count',$count);
        return $this->view->fetch('paymentList');


    }


    /**
     * 会员的删除
     */
    public function memberDelete(){
        $UserId=$this->request->param();
        $where['UserId']=$UserId['UserId'];
        $where['IsAudit']=0;
        if($UserId['UserId']) {
                Db::name('usermsg')->where($where)->delete();
            $returnData['status']=1;
            $returnData['msg']='删除成功！';
        }else{
            $returnData['status']=0;
            $returnData['msg']='删除失败！';
        }
        return json($returnData);
    }
    /**
     * 会员的启用与锁定
     */
    public function payment_start(){
        $PaymentId=$this->request->param();
        $where['PaymentId']=$PaymentId['PaymentId'];
        $act=$this->request->param('action');
        if(!empty($act)&&$act=='start'){
            $data["Enabled"]=1;
            Db::name('paymentmethod')->where($where)->update($data);
            $returnData['status'] = 1;
            $returnData['msg'] = '修改成功！';
        }

       else if(!empty($act)&&$act=='stop'){
            $data["Enabled"]=0;
            Db::name('paymentmethod')->where($where)->update($data);
           $returnData['status'] = 1;
           $returnData['msg'] = '修改成功！';
        }
        else{

            $returnData['status'] = 0;
            $returnData['msg'] = '修改失败！';
        }
        return json($returnData);
    }

    //修改个人信息
    public function  payment_edit(){
        $PaymentId=$this->request->param('PaymentId');
        $act=$this->request->param('action');
        $paymentmethod=Db::name('paymentmethod')->where("PaymentId='".$PaymentId."'") ->find() ;
        if(!empty($act)&$act=='edit'){
            $memberData=$this->request->post();
            $data["PaymentName"]=$memberData["PaymentName"];
            $data["Enabled"]=$memberData["Enabled"];
            $data["SortOrder"]=$memberData["SortOrder"];
            Db::name('paymentmethod')->where("PaymentId='".$memberData["PaymentId"]."'")->update($data);

        }
        else{
            $this->view->assign('paymentmethod',$paymentmethod);
            return  $this->view->fetch('payment_edit');
        }
    }

}
