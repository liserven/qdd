<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/19
 * Time: 11:07
 */

namespace app\admin\controller;
\think\Loader::import('controller/Jump', TRAIT_PATH, EXT);

use app\admin\Controller;

class RecommendTree extends Controller
{
    public function index(){
        return $this->recommend_tree();
    }
    public function recommend_tree(){
        if($this->request->param('userid') != ''){
            $this->view->assign('param','?userid='.urldecode($this->request->param('userid')));
        }else{
            $this->view->assign('param','?userid=00001');
        }

        return $this->view->fetch('user_network/recommend_tree');
    }

}