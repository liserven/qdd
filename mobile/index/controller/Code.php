<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/19
 * Time: 9:57
 */

namespace mobile\index\controller;


class Code extends Common
{
    /**
     * 二维码页面
     * @return mixed
     */
    public function qr_code(){
        return $this->view->fetch('qr_code');
    }
}
