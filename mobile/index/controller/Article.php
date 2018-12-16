<?php
/**
 * Created by 李娜.
 * User: lina
 * Date: 2017/3/16
 * Time: 13:58
 */

namespace mobile\index\controller;


class Article extends Common
{
    /**
     * 资讯列表
     * @return mixed
     */
    public function article_list(){
        return $this->view->fetch('article_list');
    }

    /**
     * 新闻详情
     * @return mixed
     */
    public function article_show(){

        return $this->view->fetch('article_show');
    }
}
