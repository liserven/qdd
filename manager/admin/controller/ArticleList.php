<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/9
 * Time: 9:47
 */

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


class ArticleList extends Controller
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
        return $this->articleList();
    }

    public function articleList()
    {
        $where['id'] = ['>', '0'];
        $map = [];
        if ($this->request->param()) {
            if ($this->request->param('keywords')) {
                $keywords = $this->request->param('keywords');
                $map['keywords'] = $keywords;
                $where["articletitle"] = array('like', '%' . $keywords . '%');
            }
            if ($this->request->param('articleCate') || $this->request->param('articleCate') == '0') {
                $articleCate = $this->request->param('articleCate');
                $map['articleCate'] = $articleCate;
                $where['categoryid'] = $articleCate;

            }
            if ($this->request->param('articlestate') || $this->request->param('articlestate') == '0') {
                $articlestate = $this->request->param('articlestate');
                $map['articlestate'] = $articlestate;
                $where['status'] = $articlestate;

            }
            //根据日期进行查找
            if ($this->request->param("datemin") and $this->request->param("datemax")) {
                $requestdate = $this->request->param("datemax");
                $redate = date("Y-m-d", strtotime("$requestdate +1 day"));
                $where["addtime"] = array(array('egt', $this->request->param("datemin")), array('elt', $redate), 'and');
                $map["datemin"] = $this->request->param("datemin");
                $map["datemax"] = $this->request->param("datemax");
            } elseif ($this->request->param("datemin")) {
                $where["addtime"] = array('egt', $this->request->param("datemin"));
                $map["datemin"] = $this->request->param("datemin");
            } elseif ($this->request->param("datemax")) {
                $requestdate = $this->request->param("datemax");
                $redate = date("Y-m-d", strtotime("$requestdate +1 day"));
                $where["addtime"] = array('elt', $redate);
                $map["datemax"] = $this->request->param("datemax");
            }
        }


        $newlist = Db::name('articlelist')->where($where)->order('addtime desc')->paginate();//根据条件分页输出
        $newsum = Db::name('articlelist')->where($where)->count();
        if (!empty($map)) {
            foreach ($map as $key => $value) {
                $newlist->appends($key, $value);//分页链接中添加请求的参数
            }
        }
        $this->view->assign("list", $newlist);
        $this->view->assign("newsum", $newsum);
        $this->view->assign('page', $newlist->render());//输出分页的样式
        $this->view->assign('count', $newlist->total() ? $newlist->total() : 0);

        $articlecategory = Db::name('articlecategory')->select();
        $this->view->assign("articlecategory", $articlecategory);
        return $this->view->fetch('articleList');
    }
    //删除
    /**
     * @return array
     */
    public function article_Delete()
    {
        $newsid = $this->request->param();
        if ($newsid['id']) {
            if (is_array($newsid['id'])) {//批量删除
                foreach ($newsid['id'] as $id) {
                    Db::name('articlelist')->where('id='.$id)->delete();
                }
            } else {//针对单个咨询的删除
                Db::name('articlelist')->where('id='.$newsid['id'])->delete();
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
    public  function article_start_or_stop()
    {
        $newsid=$this->request->param('id');
        $act=$this->request->param('action');
        $where['id']=$newsid;
        if (!empty($newsid)&&$act=='stop'){
            $data["status"]=0;
            Db::name('articlelist')->where($where)->update($data);
            $returnData['status'] = 1;
            $returnData['msg'] = '修改成功！';
        }
        else  if (!empty($newsid)&&$act=='start'){
            $data["status"]=1;
            Db::name('articlelist')->where($where)->update($data);
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
    public  function article_show()
    {
        $id=$this->request->param('id');
        $where['id']=$id;
        $newInfor=Db::name('articlelist')->where($where)->find() ;
        $this->view->assign('list',$newInfor);
        return $this->view->fetch('article_show');
    }

    /**
     * 修改信息
     */
    public  function article_edit()
    {
        $id=$this->request->param('id');
        $where['id']=$id;
        $act=$this->request->param('action');
        $data=$this->request->post();
        $newInfor=Db::name('articlelist')->where($where)->find() ;

        $where1['id'] = ['<>', $newInfor['categoryid']];
        $categoryInfor=Db::name('articlecategory')->where($where1)->select() ;

        if(!empty($act)&&$act=='edit')
        {
            $newData['ArticleTitle']=$data['title'];
            $newData['remark']=$data['remark'];
            $newData['categoryid']=$data['categoryid'];
            $newData['ArticleContent']=$_POST['content'];
            $newData['editor']=Session::get('user_name');
            Db::name('articlelist') ->where("id='".$id."'")->update($newData);
        }
        else{
            $this->view->assign('list',$newInfor);
            $this->view->assign('clist',$categoryInfor);
            return $this->view->fetch('article_edit');
        }
    }

    //文章添加
    public function article_add(){
        $act=$this->request->param('action');
        $categoryInfor=Db::name('articlecategory')->select() ;

        if($act=='add'){
            $data=$this->request->param();

            if(empty($data['title'])){
                return json(['status'=>0,'msg'=>'标题不能为空']);
            }
            elseif (empty($_POST['content'])){
                return json(['status'=>0,'msg'=>'内容不能为空']);
            }

            $newData['ArticleTitle']=$data['title'];
            $newData['remark']=$data['remark'];
            $newData['categoryid']=$data['categoryid'];
            $newData['ArticleContent']=$_POST['content'];
            $newData['editor']=Session::get('user_name');
            $newData['creater']=Session::get('user_name');
            $newData['addtime']=date('Y-m-d H:i:s');
            $newData['status']=1;

            $memberId= Db::name('articlelist')->insertGetId($newData);//添加数据并获得此数据在数据库中的id
            if($memberId){
                return json(['status'=>1,'msg'=>'添加成功']);
            }
            else{
                return json(['status'=>0,'msg'=>'添加失败']);
            }

        }elseif($act=='show'){
            $this->view->assign('clist',$categoryInfor);
            return $this->view->fetch('article_add');

        }
    }
}