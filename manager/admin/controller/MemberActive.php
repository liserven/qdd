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
use think\Loader;
use think\Session;
use think\Db;
use think\Config;
use think\Exception;
use think\View;
use think\Request;

class MemberActive extends Controller
{
    use \traits\controller\Jump;

    // 视图类实例
    protected $view;
    // Request实例
    protected $request;

    public function __construct()
    {
        if (null === $this->view) {
            $this->view = View::instance(Config::get('template'), Config::get('view_replace_str'));
        }
        if (null === $this->request) {
            $this->request = Request::instance();
        }

        // 用户ID
        defined('UID') or define('UID', Session::get(Config::get('rbac.user_auth_key')));
    }

    public function index(){
        return $this->wait_active();
    }

    
    //待激活会员列表
    public function wait_active(){
        $where['IsAudit'] = ['in',[0,2]];
        $where['ID']=['>','0'];
        $map=[];
        if($this->request->param()){
            if($this->request->param('UserId')){
                $UserId=$this->request->param('UserId');
                $map['UserId']=$UserId;
                $where['UserId']=$UserId;
            }
            if($this->request->param('Mobile')){
                $UserId=$this->request->param('Mobile');
                $map['Mobile']=$UserId;
                $where['Mobile']=$UserId;
            }
            if($this->request->param('TrueName')){
                $TrueName=$this->request->param('TrueName');
                $map['TrueName']=$TrueName  ;
                $where['TrueName']=['like','%'.$TrueName.'%'];
            }
            if($this->request->param('IsAudit') || $this->request->param('IsAudit')=='0'){
                $userState=$this->request->param('IsAudit');
                $map['IsAudit']=$userState;
                $where['IsAudit']=$userState;

            }
            if($this->request->param('userType')){
                $userType=$this->request->param('userType');
                $map['userType']=$userType;
                $where['userType']=$userType;
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
        $daochu=$this->request->param('daochu');
        if(isset($daochu)&&$daochu=='daochu'){
            set_time_limit(0);
            $header = ['会员ID', '姓名', '积分','购物','加入时间'];
            $data = Db::name("usermsg")->field("UserId,TrueName,Umoney,Pv,AddDate")->where($where)->order('ID desc')->select();
            if ($error = \Excel::export($header, $data, "会员信息", '2007')) {
                throw new Exception($error);
            }
        }
        $count=Db::name('usermsg')->where($where)->count();//获取满足条件的总记录数
        $list=Db::name('usermsg')->where($where)->order('id desc')->paginate();//根据条件分页输出
        if(!empty($map)) {
            foreach ($map as $key => $value) {
                $list->appends($key, $value);//分页链接中添加请求的参数
            }
        }
        //购物币总和
        $umoneysum=Db::name('usermsg')->where($where)->sum("Umoney");
        //消费积分总和
        $pvsum=Db::name('usermsg')->where($where)->sum("Pv");
        $this->view->assign("umoneysum",$umoneysum);
        $this->view->assign("pvsum",$pvsum);
        $this->view->assign('Member_list',$list);
        $this->view->assign('page',$list->render());//输出分页的样式
        $this->view->assign('count',$count);
        return $this->view->fetch('wait_active');
    }


}
