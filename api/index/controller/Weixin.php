<?php
/**
 * Created by 赵晓凡
 * User: zhaoxiaofan
 * Date: 2017/3/27
 * Time: 10:10
 */

namespace api\index\controller;
define("TOKEN", "feiducom");

use think\Db;
use think\Request;
use think\Session;

class Weixin
{
    public function index(){
        if (isset($_GET['echostr'])) {
            $this->valid();
        }else{
            $this->responseMsg();
        }
    }

    public function valid()
    {
        $echoStr = $_GET["echostr"];
        if($this->checkSignature()){
            echo $echoStr;
            exit;
        }
    }

    /**
     * 验证签名
     * @return bool
     */
    private function checkSignature()
    {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];

        $token = TOKEN;
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );

        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }

    public function responseMsg()
    {
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        $accountid=Request::instance()->param('accountid');
        if (!empty($postStr)){
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $RX_TYPE = trim($postObj->MsgType);
            switch ($RX_TYPE)
            {
                case "text":
                    $result = $this->receiveText($postObj,$accountid);
                    break;
                case "event":
                    $result = $this->receiveEvent($postObj,$accountid);
                    break;
                default:
                    $result = "未知消息类型: ".$RX_TYPE;
                    break;
            }
            echo $result;
        }else {
            echo "";
            exit;
        }
    }

    private function receiveText($object,$accountid)
    {
        $keyword = trim($object->Content);
        $data=$this->reply_content($object,$accountid,$keyword);
        if(empty($data['content'])&&empty($data['result'])){
            $data=$this->reply_content($object,1,'默认');
        }
        $content='感谢您的关注！';
        if(!empty($data['content'])){
            $content=$data['content'];
        }
        if(!empty($data['result'])){
            $result=$data['result'];
        }

        if(empty($result)){
            $result=$this->transmitText($object,$content);
        }

        return $result;
    }

    private function receiveEvent($object,$accountid)
    {
        $content = "";
        switch ($object->Event)
        {
            case "subscribe":   //关注事件
//                $data=$this->reply_content($object,$accountid,'关注');
//                if(!empty($data['content'])){
//                    $content=$data['content'];
//                }
//                if(!empty($data['result'])){
//                    $result=$data['result'];
//                }
//                if(empty($content)&&empty($result)){
//                    $content='感谢您的关注！';
//                }
                $openid = $object->FromUserName;
                $userData=$this->getUserData($accountid,$openid);
                $userData['avatarurl']=$userData['headimgurl'];
                $userData['wxopenid']=$userData['openid'];
                $returnData=Factory::instance()->getObjectInstance('user')->user_add($userData);
                Session::set('membername',$returnData['data']['userid']);
                if($returnData['status']==1){
                    $content = "您好，欢迎关注\n".
                        "用户名：".$returnData['data']['userid']."\n".
                        "密码：".$returnData['data']['password'];
                }else{
                    $content = "您好，欢迎关注，已注册\n".
                        "用户名：".$returnData['data']['userid'];
                }

                break;
            case "unsubscribe": //取消关注事件
                $content = "";
                break;
            case "CLICK":
                switch ($object->EventKey)
                {
                    case "久零商城":
                        $content[] = array("Title" =>"久零网欢迎您",
                            "Description" =>"点击图片进入网站",
                            "PicUrl" =>"https://mmbiz.qlogo.cn/mmbiz/PEp1VBjpw7SYfD6LCyf8polO0s87Ck0v8vetMGKdLIP7iawZlq3rUd8iaytzx6MeE4WpUiaEvbxHibvfAiap1WVb7HQ/0?wx_fmt=jpeg",
                            "Url" =>"http://mp.weixin.qq.com/s/bOIUKboLdwQgxMXFmmU_tQ");
                        $content[] = array("Title" =>"会员注册流程",
                            "Description" =>"点击图片进入网站",
                            "PicUrl" =>"https://mmbiz.qlogo.cn/mmbiz/PEp1VBjpw7Tp7WFAbT152L9dpV8TdkJpkPGxicLHOkBlqweicfs1dMxZkfOxicY5mARPflKtTIw7K16Z8QfnZc2BQ/0?wx_fmt=jpeg",
                            "Url" =>"http://mp.weixin.qq.com/s/UEa27XyJYBJ7lDLsCCcwdw");
                        $result = $this->transmitNews($object, $content);
                        break;
                    default:
                        $content[] = array("Title" =>"久零网欢迎您",
                            "Description" =>"点击图片进入网站",
                            "PicUrl" =>"https://mmbiz.qlogo.cn/mmbiz/PEp1VBjpw7SYfD6LCyf8polO0s87Ck0v8vetMGKdLIP7iawZlq3rUd8iaytzx6MeE4WpUiaEvbxHibvfAiap1WVb7HQ/0?wx_fmt=jpeg",
                            "Url" =>"http://mp.weixin.qq.com/s/bOIUKboLdwQgxMXFmmU_tQ");
                        $content[] = array("Title" =>"会员注册流程",
                            "Description" =>"点击图片进入网站",
                            "PicUrl" =>"https://mmbiz.qlogo.cn/mmbiz/PEp1VBjpw7Tp7WFAbT152L9dpV8TdkJpkPGxicLHOkBlqweicfs1dMxZkfOxicY5mARPflKtTIw7K16Z8QfnZc2BQ/0?wx_fmt=jpeg",
                            "Url" =>"http://mp.weixin.qq.com/s/UEa27XyJYBJ7lDLsCCcwdw");
                        $result = $this->transmitNews($object, $content);
                        break;
                }
                break;
        }
        $textTpl = "<xml>
                    <ToUserName><![CDATA[%s]]></ToUserName>
                    <FromUserName><![CDATA[%s]]></FromUserName>
                    <CreateTime>%s</CreateTime>
                    <MsgType><![CDATA[text]]></MsgType>
                    <Content><![CDATA[%s]]></Content>
                    </xml>";
        if(empty($result))
        {
            $result = sprintf($textTpl, $object->FromUserName, $object->ToUserName, time(), $content);
        }
        return $result;
    }

    private function reply_content($object,$accountid,$keyword){
        $returnData=array('content'=>'','result'=>'');

        $replyData=Db::query("select * from weixin_textreply where accountid={$accountid} and enable=1");
        if(!empty($replyData)) {
            foreach ($replyData as $key => $val) {
                if($val['matching']==1){
                    if ($val['keyword']== $keyword) {
                        $returnData['content'] = $val['content'];
                        break;
                    }
                }elseif($val['matching']==2){
                    if (strpos($keyword, $val['keyword']) !== false) {
                        $returnData['content'] = $val['content'];
                        break;
                    }
                }
            }
        }

        $replyTextData=Db::query("select * from weixin_newsreply where accountid={$accountid} and enable=1 order by usort");
        if(!empty($replyTextData)){
            foreach ($replyTextData as $key=>$val){
                if($val['matching']==1){
                    if ($val['keyword']== $keyword) {
                        $content[] = array("Title" => $val['title'],
                            "Description" => $val['text'],
                            "PicUrl" => $val['pic'],
                            "Url" => $val['url']
                        );
                    }
                }elseif($val['matching']==2){
                    if(strpos($keyword,$val['keyword'])!==false) {
                        $content[] = array("Title" => $val['title'],
                            "Description" => $val['text'],
                            "PicUrl" => $val['pic'],
                            "Url" => $val['url']
                        );
                    }
                }
            }
            if(!empty($content)){
                $returnData['result'] = $this->transmitNews($object, $content);
            }
        }
        return $returnData;
    }

    /*
  * 回复文本消息
  */
    private function transmitText($object, $content)
    {
        $textTpl = "<xml>
                    <ToUserName><![CDATA[%s]]></ToUserName>
                    <FromUserName><![CDATA[%s]]></FromUserName>
                    <CreateTime>%s</CreateTime>
                    <MsgType><![CDATA[text]]></MsgType>
                    <Content><![CDATA[%s]]></Content>
                    </xml>";
        $result = sprintf($textTpl, $object->FromUserName, $object->ToUserName, time(), $content);
        return $result;
    }

    /*
     * 回复图片消息
     */
    private function transmitImage($object, $imageArray)
    {
        $itemTpl = "<Image>
                    <MediaId><![CDATA[%s]]></MediaId>
                    </Image>";
        $item_str = sprintf($itemTpl, $imageArray['MediaId']);

        $textTpl = "<xml>
                    <ToUserName><![CDATA[%s]]></ToUserName>
                    <FromUserName><![CDATA[%s]]></FromUserName>
                    <CreateTime>%s</CreateTime>
                    <MsgType><![CDATA[image]]></MsgType>
                    $item_str
                    </xml>";

        $result = sprintf($textTpl, $object->FromUserName, $object->ToUserName, time());
        return $result;
    }

    /*
     * 回复图文消息
     */
    private function transmitNews($object, $arr_item)
    {
        if(!is_array($arr_item))
            return;
        $itemTpl = "<item>
                    <Title><![CDATA[%s]]></Title>
                    <Description><![CDATA[%s]]></Description>
                    <PicUrl><![CDATA[%s]]></PicUrl>
                    <Url><![CDATA[%s]]></Url>
                    </item>
                    ";
        $item_str = "";
        foreach ($arr_item as $item){
            $item_str .= sprintf($itemTpl, $item['Title'], $item['Description'], $item['PicUrl'], $item['Url']);
        }
        $newsTpl = "<xml>
                    <ToUserName><![CDATA[%s]]></ToUserName>
                    <FromUserName><![CDATA[%s]]></FromUserName>
                    <CreateTime>%s</CreateTime>
                    <MsgType><![CDATA[news]]></MsgType>
                    <Content><![CDATA[]]></Content>
                    <ArticleCount>%s</ArticleCount>
                    <Articles>{$item_str}</Articles>
                    </xml>";
        $result = sprintf($newsTpl, $object->FromUserName, $object->ToUserName, time(), count($arr_item));
        return $result;
    }


    private function createOauthUrlForCode($redirectUrl,$wxpay_config)
    {
        $urlObj["appid"] = $wxpay_config['appid'];
        $urlObj["redirect_uri"] = "$redirectUrl";
        $urlObj["response_type"] = "code";
        $urlObj["scope"] = "snsapi_userinfo";
        $urlObj["state"] = "STATE"."#wechat_redirect";
        $bizString = $this->formatBizQueryParaMap($urlObj, false);
        return "https://open.weixin.qq.com/connect/oauth2/authorize?".$bizString;
    }

    private function formatBizQueryParaMap($paraMap, $urlencode)
    {
        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v)
        {
            if($urlencode)
            {
                $v = urlencode($v);
            }
            $buff .= $k . "=" . $v . "&";
        }
        $reqPar='';
        if (strlen($buff) > 0)
        {
            $reqPar = substr($buff, 0, strlen($buff)-1);
        }
        return $reqPar;
    }

    /**
     * 获取微信用户的信息
     */
    public function getUserInfo(){
        $accountid=Request::instance()->param('accountid');
        if(empty($accountid)){
            $accountid=1;
        }
        if(empty($_GET['code'])){
            /**
             * 第一步：用户同意授权，获取code
             */
            $url=$this->createOauthUrlForCode('http://'.$_SERVER['HTTP_HOST'].'/api.php/index/Weixin/getUserInfo/accountid/1',$this->getConfig($accountid));
            header("Location: {$url}");
            exit();
        }else{
            /**
             *第二步：通过code换取网页授权access_token
             */
            $wxconfig=$this->getConfig($accountid);
            $appid=$wxconfig['appid'];
            $appsecret=$wxconfig['appsecret'];
            $codeData=$_GET;
            $url='https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$appid.'&secret='.$appsecret.'&code='.$codeData['code'].'&grant_type=authorization_code';
            $data=$this->https_request($url);
            $accessData=json_decode($data,true);
            /**
             * 第三步：刷新access_token（如果需要）
             */
            $url='https://api.weixin.qq.com/sns/oauth2/refresh_token?appid='.$appid.'&grant_type=refresh_token&refresh_token='.$accessData['refresh_token'];
            $data=$this->https_request($url);
            $refreshData=json_decode($data,true);
            /**
             * 第四步：拉取用户信息(需scope为 snsapi_userinfo)
             */
            $url='https://api.weixin.qq.com/sns/userinfo?access_token='.$refreshData['access_token'].'&openid='.$refreshData['openid'].'&lang=zh_CN';
            $data=$this->https_request($url);
            $userData=json_decode($data,true);
            $userData['avatarurl']=$userData['headimgurl'];
            $userData['wxopenid']=$userData['openid'];
            $returnData=Factory::instance()->getObjectInstance('user')->user_add($userData);
            Session::set('membername',$returnData['data']['userid']);
//            header("Location: mobile.php");
            echo '<pre>';
            print_r($returnData);
            echo '</pre>';
        }
    }

    public function getUserData($accountid,$openid){
        $url='https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$this->getToken($accountid).'&openid='.$openid.'&lang=zh_CN';
        $data=$this->https_request($url);
        $userData=json_decode($data,true);
        return $userData;
    }

    //获取token
    private function getToken($accountid=1)
    {
        $wxpay_config = $this->getConfig($accountid);
        $appid = $wxpay_config['appid'];
        $appsecret = $wxpay_config['appsecret'];

        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$appid."&secret=".$appsecret;
        $res = $this->https_request($url);
        $result = json_decode($res, true);
        return $result["access_token"];
    }

    private function getConfig($accountid=1){
        $wxpay_config = Db::name('weixinconfig')->where('id='.$accountid)->find();
        return $wxpay_config;
    }

    //https请求（支持GET和POST）
    private function https_request($url, $data = null)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        if (!empty($data)){
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }
}