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
use traits\controller\Jump;

class Cache extends Controller
{
    use \traits\controller\Jump;

    public function index(Request $request){
        if($request->isPost()&&$request->param('act')=='reset'){
            $data=$request->post();
            $this->success('提交成功',url('Cache/index'));
        }
        return view('index');
    }
}
