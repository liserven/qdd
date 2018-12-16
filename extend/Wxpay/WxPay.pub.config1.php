<?php
/**
* 	配置账号信息
*/
class WxPayConf_pub
{
    //=======【基本信息设置】=====================================
    //微信公众号身份的唯一标识。审核通过后，在微信发送的邮件中查看
    //const APPID = 'wx481fc3af822e90f0';
    const APPID = 'wxab943c1c3318c99d';
    //受理商ID，身份标识
    //const MCHID = '1331519101';
    const MCHID = '1434442002';
    //商户支付密钥Key。审核通过后，在微信发送的邮件中查看
    //const KEY = 'Kg2Eg2Rdfvh8BR2n3T3sAuL4qMJ6XMsp';
    const KEY = '1jvoww6qjg0bwgft0lwek9t7po8ec4fj';
    //JSAPI接口中获取openid，审核后在公众平台开启开发模式后可查看
    //const APPSECRET = '1c4fa6da6aca3aacc36b8a6c29c47b17';
    const APPSECRET = '5fff0238e55403596d0f46a2e749d814';

    //=======【JSAPI路径设置】===================================
    //获取access_token过程中的跳转uri，通过跳转将code传入jsapi支付页面
    const JS_API_CALL_URL = 'http://pay.999000.cn/api.php/order/wxpay';

    //=======【证书路径设置】=====================================
    //证书路径,注意应该填写绝对路径
    const SSLCERT_PATH = './cacert/apiclient_cert.pem';
    const SSLKEY_PATH = './WxPayPubHelper/cacert/apiclient_key.pem';

    //=======【异步通知url设置】===================================
    //异步通知url，商户根据实际开发过程设定
    const NOTIFY_URL = 'http://pay.999000.cn/api.php/order/wxpay_notify';

    //=======【curl超时设置】===================================
    //本例程通过curl使用HTTP POST方法，此处可修改其超时时间，默认为30秒
    const CURL_TIMEOUT = 30;
}

?>