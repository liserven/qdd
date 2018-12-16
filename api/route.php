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
    Route::any('poster','Index/poster');
    Route::any('indexnav','Index/indexnav');
    Route::any('news','Index/news');
    Route::any('all','Index/all');
    Route::any('article','Index/article_show');
    Route::any('article_list','Index/article_list');
    Route::any('getaddress','Index/getaddress');
    Route::any('address_add','Index/address_add');
    Route::any('getcity','Index/getcity');
    Route::any('recommend_goods','Index/recommend_goods');
});
Route::group('login', function(){//定义登陆验证的路由分组
	Route::any('check','Login/check');
	Route::any('auth','Login/auth');
    Route::post('mobile_check','Login/mobile_check');
    Route::any('mobile_auth','Login/mobile_auth');
    Route::any('exit','Login/login_exit');
});
Route::group('order', function(){//定义用户订单的路由分组
	Route::any('list','Order/orderList');
	Route::any('pay','Order/orderPay');
	Route::any('action','Order/orderAction');
    Route::post('generate','Order/orderGenerate');
    Route::post('generatenow','Order/orderGeneratenow');
    Route::any('affirm','Order/orderAffirm');
    Route::any('affirmnow','Order/orderAffirmnow');
    Route::any('show','Order/orderShow');
    Route::any('detail','Order/orderDetail');
    Route::any('alipay','Alipay/orderAlipay');
    Route::any('alipaywap','Alipaywap/orderAlipay');
    Route::any('wxpay','Wxpay/jsApiCall');
    Route::any('jswxpay','Wxpay/apiCall');
    Route::any('wxpaypc','Wxpay/nativeCall');
    Route::any('bdpay','Bdpay/jsApiCall');
    Route::any('alipay_notify','Notify/alipay_notify_url');
    Route::any('wxpay_notify','Notify/wxpay_notify_url');
    Route::any('orderstatus','Notify/orderstatuschange');
    Route::any('bdpay_notify','Notify/bdpay');
    Route::any('delete','Order/delete');
    Route::any('thirdpay','Order/thirdpay');
    Route::any('orderGenerate','Order/orderhitGenerate');
});
Route::group('cart', function() {//定义购物车的路由分组
    Route::any('add', 'Cart/addGoods');//定义添加商品到购物车的路由规则
    Route::any('delete', 'Cart/delGoods');//定义删除购物车中商品 的路由规则
    Route::any('update', 'Cart/updateGoodsNum');//定义更改购物车中商品数量的路由规则
    Route::any('list', 'Cart/goodsList');//定义获取购物车中商品列表的路由规则
    Route::any('empty', 'Cart/emptyCart');//定义清空购物车中商品的路由规则
    Route::any('status', 'Cart/shopStatus');
    Route::any('newupdate', 'Cart/newupdateGoodsNum');
});

Route::group('goods', function(){//定义产品的路由分组
    Route::any('detail','Product/product_detail');
    Route::any('list','Product/product_list');
    Route::any('stock','Product/product_stock');
    Route::get('hit','Product/product_hit');
    Route::get('category','Product/product_category');
    Route::get('categoryson','Product/product_categoryson');//定义产品列表的路由规则
    Route::get('search','Product/product_search');
    Route::any('collect','Product/collect');
    Route::any('collect_list','Product/collect_list');
    Route::any('collect_del','Product/delCollect');
    Route::any('history','Product/getHistory');
    Route::any('enjoylist','Product/enjoylist');
});

Route::group('supplier', function(){//定义产品的路由分组
    Route::any('detail','Supplier/supplier_detail');
    Route::any('list','Supplier/supplier_list');
});

Route::group('member', function(){//定义会员的路由分组
    Route::any('address_add','Member/address_add');
    Route::any('address_edit','Member/address_edit');
    Route::any('address_action','Member/address_action');
    Route::any('address_detail','Member/address_detail');
    Route::any('address_list','Member/address_list');
    Route::any('userinfo','Member/user_info');
    Route::any('useredit','Member/user_edit');
    Route::any('password_reset','Member/user_password_reset');
    Route::any('reg','UserReg/user_Regs');
    Route::any('subordinate','Member/user_subordinate');
    Route::any('security_code','UserReg/security_code');
    Route::any('reset_password','UserReg/reset_password');
});

Route::group('record', function(){//定义账户记录的路由分组
    Route::any('accountrecord','Record/accountrecord');
    Route::any('integral_record','Record/integral_record');
    Route::any('integral_detail','Record/integral_detail');
    Route::any('prize_list','Record/prize_list');
    Route::any('prize_detail','Record/prize_detail');
    Route::any('account','Record/account_show');
});

Route::group('weixin', function(){//定义账户记录的路由分组
    Route::any('set_menu','Weixin/set_menu');
    Route::any('get_menu','Weixin/get_menu');
    Route::any('del_menu','Weixin/del_menu');
    Route::any('index','Weixin/index');
});
Route::group('qrcode', function(){
    Route::any('wxpay','Qrcode/getWxpayQrcode');
    Route::any('user_qrcode','Qrcode/getUserQrcode');
});


Route::group('settlement', function(){
    Route::any('settlement_test','UserSettlement/test_user_settlement');
});


Route::group('user', function(){
    Route::any('get_sub','User/get_subordinate');
    Route::any('get_sup','User/get_superior');
    Route::any('get_recommend','User/get_recommend');
    Route::any('get_network','User/get_network');
    Route::any('wealth_net','UserSettlement/wealth_net');
    Route::any('three_level_rebate','UserSettlement/three_level_rebate');
    Route::any('product_or_bonus','UserSettlement/product_or_bonus');
    Route::any('two_shopping','UserSettlement/two_shopping');
    Route::any('product_or_bonus','UserSettlement/product_or_bonus');
    Route::any('add_team_achievement','UserSettlement/add_team_achievement');
    Route::any('three_level_rebate_new','UserSettlement/three_level_rebate_new');//直接添加到账户记录
    Route::any('test','UserSettlement/test_user_settlement');//直接添加到账户记录
});