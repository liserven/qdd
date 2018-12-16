<?php
/**
 * Created by 赵晓凡
 * User: zhaoxiaofan
 * Date: 2017/5/3
 * Time: 10:46
 */

namespace api\index\controller;

interface ActionInterface
{
    public function accountAction();//定义账户操作的抽象方法
    public function orderAction();//定义订单操作的抽象方法
    public function stockAction();//定义库存操作的抽象方法
}
