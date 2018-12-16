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

class News extends Controller
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

    public function index()
    {
        return $this->newsList();
    }

    public function newsList()
    {
        $where['id'] = ['>', '0'];
        $newlist = Db::name('columnlist')->where($where)->order('addtime desc')->paginate();//根据条件分页输出
        $newsum = Db::name('columnlist')->where($where)->count();
        if (!empty($map)) {
            foreach ($map as $key => $value) {
                $newlist->appends($key, $value);//分页链接中添加请求的参数
            }
        }
        $this->view->assign("list", $newlist);
        $this->view->assign("newsum", $newsum);
        $this->view->assign('page', $newlist->render());//输出分页的样式
        $this->view->assign('count', $newlist->total() ? $newlist->total() : 0);

        $Newscategory = Db::name('columncategory')->select();
        $this->view->assign("Newscatelist", $Newscategory);

        return $this->view->fetch('newsList');
    }
    //会员删除
    /**
     * @return array
     */
    public function news_Delete()
    {
        $newsid = $this->request->param();
        if ($newsid['id']) {
            if (is_array($newsid['id'])) {//批量删除
                foreach ($newsid['id'] as $id) {
                    Db::name('columnlist')->where('id='.$id)->delete();
                }
            } else {//针对单个咨询的删除
                Db::name('columnlist')->where('id='.$newsid['id'])->delete();
            }
            $returnData['status'] = 1;
            $returnData['msg'] = '删除成功！';
        } else {
            $returnData['status'] = 0;
            $returnData['msg'] = '删除失败！';
        }
        return json($returnData);
    }

    /**
     * 咨询启用或者停用
     */
    public  function news_start_or_stop()
    {
           $newsid=$this->request->param('id');
           $act=$this->request->param('action');
           $where['id']=$newsid;
           if (!empty($newsid)&&$act=='stop'){
               $data["status"]=0;
               Db::name('columnlist')->where($where)->update($data);
               $returnData['status'] = 1;
               $returnData['msg'] = '修改成功！';
           }
        else  if (!empty($newsid)&&$act=='start'){
            $data["status"]=1;
            Db::name('columnlist')->where($where)->update($data);
            $returnData['status'] = 1;
            $returnData['msg'] = '修改成功！';
          }
          else{
              $returnData['status'] = 0;
              $returnData['msg'] = '修改失败！';

          }
        return json($returnData);
    }

    /**
     * 查看咨询
     */
    public  function news_show()
    {
        $id=$this->request->param('id');
        $where['id']=$id;
        $newInfor=Db::name('columnlist')->where($where)->find() ;
        $this->view->assign('list',$newInfor);
       return $this->view->fetch('news_show');
    }

    /**
     * 修改信息
     */
    public  function news_edit()
    {
        $id=$this->request->param('id');
        $where['id']=$id;
        $act=$this->request->param('action');
        $data=$this->request->post();
        $newInfor=Db::name('columnlist')->where($where)->find() ;
        $where1['id'] = ['<>', $newInfor['categoryid']];
        $categoryInfor=Db::name('columncategory')->where($where1)->select() ;



        if(!empty($act)&&$act=='edit')
        {
            if(isset($_POST['content'])){
                $newData['content']=$_POST['content'];
            }else{
                $newData['content']='';
            }
            $newData['title']=$data['title'];
            $newData['remark']=$data['remark'];
            $newData['categoryid']=$data['categoryid'];
            $newData['editor']=Session::get('user_name');
            Db::name('columnlist') ->where("id='".$id."'")->update($newData);
        }
         else{
             $this->view->assign('list',$newInfor);
             $this->view->assign('clist',$categoryInfor);
             return $this->view->fetch('news_edit');
         }
    }


    //文章添加
    public function news_add(){
        $act=$this->request->param('action');
        $categoryInfor=Db::name('columncategory')->select() ;

        if($act=='add'){
            $data=$this->request->param();

            if(empty($data['title'])){
                return json(['status'=>0,'msg'=>'标题不能为空']);
            }
            elseif (empty($_POST['content'])){
                return json(['status'=>0,'msg'=>'内容不能为空']);
            }

            $newData['title']=$data['title'];
            $newData['remark']=$data['remark'];
            $newData['categoryid']=$data['categoryid'];
            $newData['content']=$_POST['content'];
            $newData['editor']=Session::get('user_name');
            $newData['creater']=Session::get('user_name');
            $newData['addtime']=date('Y-m-d H:i:s');
            $newData['status']=1;

            $memberId= Db::name('columnlist')->insertGetId($newData);//添加数据并获得此数据在数据库中的id
            if($memberId){
                return json(['status'=>1,'msg'=>'添加成功']);
            }
            else{
                return json(['status'=>0,'msg'=>'添加失败']);
            }

        }else{
            $this->view->assign('clist',$categoryInfor);
            return $this->view->fetch('news_add');

        }
    }
}
