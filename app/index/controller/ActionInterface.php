<?php
/**
 * Created by
 * User:
 * Date: 2018/3/12
 * Time: 10:46
 */

namespace app\index\controller;

interface ActionInterface
{
    public function accountAction();//定义账户操作的抽象方法
    public function orderAction();//定义订单操作的抽象方法
    public function stockAction();//定义库存操作的抽象方法
}
