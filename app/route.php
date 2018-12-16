<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\Route;

Route::group('index', function(){//定义首页的路由分组
    Route::any('index','Index/index');
    Route::any('category','Product/category');//商品分类接口
    Route::any('getaddress','Index/getaddress');//省市县接口
    Route::any('version_ios','Index/version_ios');
    Route::any('version_android','Index/version_android');
});

Route::group('login', function(){//定义登陆验证的路由分组
    Route::any('check','Login/check');//用户登录
});
Route::group('supplier', function(){//定义登陆验证的路由分组
    Route::any('coupontosupplier','Supplier/coupontosupplier');//商家转账给商家
    Route::any('coupontouser','Supplier/coupontouser');//商家转账给会员
    Route::any('supplieradd','Supplier/supplieradd');//添加商家
    Route::any('supplierrecord','SupplierRecord/supplierrecord');//商家发券收券记录
    Route::any('supplierrecorddeail','SupplierRecord/supplierrecorddeail');//商家发券收券记录详情
    Route::any('supplier_cash','Supplier/supplier_cash');//商家提现申请
    Route::any('supplier_cash_list','SupplierRecord/supplier_cash_list');//商家提现申请
    Route::any('supplier_order','SupplierRecord/supplier_order');//商家提现申请
});
Route::group('member', function(){//定义会员的路由分组
    Route::any('userinfo','Member/user_info');//个人中心
    Route::any('userself','Member/user_self');//个人资料和编辑
    Route::any('userreg','Member/user_regs');//用户注册
    Route::any('address_list','Member/address_list');//会员收货地址信息列表
    Route::any('address_add','Member/address_add');//会员收货地址信息添加
    Route::any('address_edit','Member/address_edit');//会员收货信息地址编辑和展示
    Route::any('address_action','Member/address_action');//会员收货信息地址的删除和设置默认收货地址
    Route::any('add_cart_goods','Cart/addGoods');//添加到购物车
    Route::any('update_catr_goodsnum','Cart/updateGoodsNum');//更新购物车中商品的数量
    Route::any('empty_cart','Cart/emptyCart');//清空购物车
    Route::any('cart_list','Cart/goodsList');//购物车列表
    Route::any('del_goods','Cart/delGoods');//删除购物车中某个商品
    Route::any('sendsms','Member/sendsms');//注册发送短信验证码
    Route::any('user_password_reset','Member/user_password_reset');//修改密码
    Route::any('accountrecord','Member/accountrecord');//消费记录
    Route::any('user_password_forget','Member/user_password_forget');//找回密码
});

Route::group('goods', function(){//定义会员的路由分组
    Route::any('detail','Product/product_detail');//商品详情页面
    Route::any('addcollect','Product/addcollect');//商品收藏
    Route::any('product_search','Product/product_search');//商品搜索接口
    Route::any('product_cid','Product/product_cid');//商品分类搜索接口
    Route::any('collectlist','Product/collectlist');//商品商家收藏列表

});

Route::group('order', function(){//定义会员的路由分组
    Route::any('affirm','Order/orderAffirm');//提交订单页面
    Route::any('nowBuy','Order/nowBuy');//提交订单页面
    Route::any('generate','Order/orderGenerate');//生成订单接口
    Route::any('generatenow','Order/nowBuy_orderGenerate');//生成订单接口
    Route::any('pay','Order/orderPay');//订单支付接口
    Route::any('list','Order/orderList');//订单列表
    Route::any('action','Order/orderAction');//未付款订单取消/确认收货接口
    Route::any('del','Order/orderdelete');//订单删除
    Route::any('wuliu','Order/getwuliumsg');//获取物流信息
    Route::any('checkmoney','Order/accountpayVerify');//获取物流信息
    Route::any('alipay_notify_app','Notify/aliPay_notify_app');
});




