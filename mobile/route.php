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
Route::get('goods/:proid','Product/product_details');//定义商品详情的路由规则
Route::get('list','Product/product_list');//定义产品列表的路由规则
Route::get('collect','Product/collect_list');//定义产品收藏列表的路由规则
Route::get('category','Product/product_category');//定义产品列表的路由规则
Route::get('categoryson','Product/product_categoryson');//定义产品列表的路由规则
Route::any('search','Product/product_search');//定义搜索页的路由规则
Route::any('search_list','Product/product_search_list');//定义搜索列表的路由规则
Route::get('login_reg','Index/login_reg');//定义会员登录注册页面的路由规则
Route::get('login','Index/login');//定义会员登录的路由规则
Route::get('history','Product/history_list');//定义产品收藏列表的路由规则
Route::get('product','Product/productnewdetail');//注册复消商品

Route::group('cart', function(){//定义购物车的路由分组
    Route::any('list','Cart/cart_list');//定义获取购物车中商品列表的路由规则
    Route::any('empty','Cart/cart_empty_list');//定义获取购物车中商品列表的路由规则
});

Route::group('order', function(){//定义订单的路由分组
    Route::any('affirm','Order/order_affirm');
    Route::any('affirmnew','Order/order_affirmnow');
    Route::any('pay','Order/order_pay');
    Route::any('alipay_return','Order/alipay_return');
    Route::any('list','Order/order_list');
    Route::any('show','Order/order_show');
    Route::any('return','Order/order_return');
});

Route::group('member', function(){//定义会员的路由分组
    Route::any('address','Member/member_address');
    Route::any('address_list','Member/address_list');
    Route::any('userinfo','Member/user_info');
    Route::any('infos','Member/user_infos');
    Route::any('useredit','Member/user_edit');
    Route::any('password_reset','Member/user_password_reset');
    Route::any('reg','UserReg/user_reg');
    Route::any('raccount','Member/receive_account');

    Route::any('account','Member/user_account');

    Route::any('user_subordinate','Member/user_subordinate');
    Route::any('message','Member/user_message');

    Route::any('security_code','UserReg/security_code');

    Route::any('reset_password','UserReg/reset_password');
    Route::any('pay_annual_free','Member/pay_annual_free'); //会员充值年费
    Route::any('buy_places','Member/buy_places'); //会员购买推荐名额
    Route::any('add_userinfo_one','Member/add_userinfo_one'); //填写会员资料步骤1
    Route::any('add_userinfo_two','Member/add_userinfo_two'); //填写会员资料步骤2
    Route::any('active_member','Member/active_member'); //填写会员资料步骤2

});

Route::group('article', function(){//定义咨询的路由分组
    Route::any('list','Article/article_list');
    Route::get('show','Article/article_show');//定义会员登录的路由规则
});
Route::group('code', function(){//定义二维码的路由分组
    Route::any('qr','code/qr_code');
    Route::any('user_qrcode','Member/user_qrcode');
});

Route::group('record', function(){//定义交易记录的路由分组
    Route::any('list','Record/accountrecord');
    Route::any('show','Record/account_show');
    Route::any('menu','Record/record_menu');
    Route::any('integral_list','Record/integral_record');
    Route::any('integral_detail','Record/integral_detail');

    Route::any('prize_list','Record/prize_list');
    Route::any('prize_detail','Record/prize_detail');
});

// 微信支付路由组
Route::group('weixin_pay', function(){//定义二维码的路由分组
    Route::any('active_pay_successful','weixin_pay/active_pay_successful');
    Route::any('buy_places_pay_successful','weixin_pay/buy_places_pay_successful');
});