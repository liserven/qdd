<?php
/**
 * Created by 赵晓凡
 * User: zhaoxiaofan
 * Date: 2017/3/15
 * Time: 10:30
 */
namespace app\index\controller;

use think\Db;
use think\Session;


/**
 * Class Member
 * @package api\index\controller
 * @return 会员
 */
class Member extends Auth
{
    /**
     * 会员中心
     * 请求方式：http://www.XXX.com/app.php/member/userinfo
     * @param   token
     * @return \think\response\Json
     */
    public function user_info(){
        $info = $this->islogin();
        if ($info['status'] == 0) {
            $userid = $info['data']['UserId'];
            $wherenow['UserId'] = $userid;
            $userinfo = Db::name('usermsg')->field('ID,UserId,TrueName,Nickname,Umoney,AvatarUrl')->where($wherenow)->find();
            $supplier = Db::name('supplier')->field('ID,IsAudit')->where('usermasg_id=' . $userinfo['ID'])->find();
            if ($supplier) {
                if($supplier['IsAudit']==1){
                    $userinfo['supplier_is_show'] = 1;//合作中
                }elseif($supplier['IsAudit']==0){
                    $userinfo['supplier_is_show'] = 2; //未审核
                }elseif($supplier['IsAudit']==2){
                    $userinfo['supplier_is_show'] = 3; //停用
                }elseif($supplier['IsAudit']==3){
                    $userinfo['supplier_is_show'] = 4; //拒绝
                }

            } else {
                $userinfo['supplier_is_show'] = 0;//未开店
            }

            if (!empty($userinfo['AvatarUrl'])) {
                $userinfo['AvatarUrl'] = 'http://' . $_SERVER['HTTP_HOST'] . '/public/Upload/userimg/' . $userinfo['AvatarUrl'];
            }
            $whereorder['status'] = 1;
            $whereorder['UserId'] = $userinfo['UserId'];
            $userinfo['daifu'] = Db::name('ordermain')->where($whereorder)->count();
            $whereorder2['status'] = 2;
            $whereorder2['UserId'] = $userinfo['UserId'];
            $userinfo['daifa'] = Db::name('ordermain')->where($whereorder2)->count();
            $whereorder3['status'] = 4;
            $whereorder3['UserId'] = $userinfo['UserId'];
            $userinfo['daishou'] = Db::name('ordermain')->where($whereorder3)->count();
            $whereorder4['UserId'] = $userinfo['UserId'];
            $userinfo['orderall'] = Db::name('ordermain')->where($whereorder4)->count();
            $userinfo['cartnum'] = Db::name('shoppingcart')->where('Agentd=' . $userinfo['UserId'])->sum('ProNum');
            $arc= Db::name('articlelist')->where('categoryid=1 or categoryid=10')->order('id desc')->limit(10)->select();
            foreach ($arc as $k=>$v){
                $arc[$k]['ArticleContent']=str_replace(' src="',' style="width: 100%;height: auto;"  src="'.config('IMAGE_DOMAIN_NAME'),$v['ArticleContent']);
            }
            $userinfo['article'] =$arc;
            $returnData['data'] = $userinfo;
            $returnData['status'] = 0;
            $returnData['msg'] = '成功';
        } else {
            $userinfo['ID'] = '';
            $userinfo['UserId'] = '';
            $userinfo['TrueName'] = '';
            $userinfo['Nickname'] = '';
            $userinfo['Umoney'] = '';
            $userinfo['AvatarUrl'] = '';
            $userinfo['supplier_is_show'] = 0;
            $userinfo['daifu'] = 0;
            $userinfo['daifa'] = 0;
            $userinfo['daishou'] = 0;
            $userinfo['orderall'] = 0;
            $userinfo['cartnum'] = 0;
            $arc= Db::name('articlelist')->where('categoryid=1 or categoryid=10')->order('id desc')->limit(10)->select();
            foreach ($arc as $k=>$v){
                $arc[$k]['ArticleContent']=str_replace('<img src="','<img style="width: 100%;height: auto;"  src="'.config('IMAGE_DOMAIN_NAME'),$v['ArticleContent']);
            }
            $userinfo['article'] =$arc;
            $returnData['data'] = $userinfo;
            $returnData['status'] = 0;
            $returnData['msg'] = '成功';
        }
        return json($returnData);
    }

    /**
     * 会员资料
     * 请求方式：http://www.XXX.com/app.php/member/userself
     * @param   token
     * @param   type  show(个人信息显示)、edit(个人信息的修改)
     * @return \think\response\Json
     */
    public function user_self(){
        $info = $this->islogin();
//        $info['data']['UserId']='00001';
//        $info['status']=0;
        if ($info['status'] == 0) {
            $userid=$info['data']['UserId'];
            $type=$this->request->param('type');
            $where['UserId']=$userid;
            if($type=='show'){                                  //会员信息的显示
                $userinfof=Db::name('usermsg')->where($where)->find();
                if(!empty($userinfof['AvatarUrl'])){
                    $userinfof['AvatarUrl']=config('IMAGE_DOMAIN_NAME').'/public/Upload/userimg/'.$userinfof['AvatarUrl'];
                }else{
                    $userinfof['AvatarUrl']='';
                }
                $returnData['data']=$userinfof;
                $returnData['status']=0;
                $returnData['msg']='成功';
            }elseif($type=='edit'){                             //会员信息的修改
                $submitdata=$this->request->param();
                $file = $this->request->file('memberpic');

                if(!empty($file)){
                    // 移动到框架应用根目录/public/uploads/ 目录下
                    $uploaddir=ROOT_PATH . 'public' . DS . 'Upload'. DS .'userimg';
                    $info = $file->validate(['size'=>1024000,'ext'=>'jpg,png,gif,jpeg'])->move($uploaddir);
                    if($info){
//                    $mempicture=Db::name('usermsg')->where($where)->field('AvatarUrl')->find();
//                    if(file_exists($uploaddir.'/'.$mempicture['AvatarUrl'])){
//                        unlink($uploaddir.'/'.$mempicture['AvatarUrl']);//删除原有图片
//                    }
                        $filename=$info->getPathInfo()->getFilename().'/'.$info->getFilename();
                    }else{
                        $filename='default.jpg';
                    }
                    $data["AvatarUrl"]=$filename;
                }

//                if(empty($submitdata["mobile"])){
//                    $returnData['status']=1;
//                    $returnData['msg']='手机号码不能为空';
//                    return json($returnData);
//                }else{
//                    $data["Mobile"]=$submitdata["mobile"];
//                }
                if(empty($submitdata["truename"])){
                    $returnData['status']=1;
                    $returnData['msg']='真实姓名不能为空';
                    return json($returnData);
                }else{
                    $data["TrueName"]=$submitdata["truename"];
                }

                $data["Sex"]=$submitdata["sex"];

                if(empty($submitdata["idcardno"])){
                    $returnData['status']=1;
                    $returnData['msg']='身份证号码不能为空';
                    return json($returnData);
                }else{
                    $data["IdCardNo"]=$submitdata["idcardno"];
                }
                $data["Nickname"]=$submitdata["nickname"];
                $data["Province"]=$submitdata["province"];
                $dNicknameata["City"]=$submitdata["city"];
                $data["County"]=$submitdata["county"];
                $data["Address"]=$submitdata["address"];
                Db::name('usermsg')->where($where)->update($data);
                $returnData['status']=0;
                $returnData['msg']='编辑成功';
            }
        }else{
            $returnData['status']=1;
            $returnData['msg']='未登录';
        }
        return json($returnData);
    }
    /**用户注册接口
     * 请求方式：http://www.XXX.com/app.php/member/userreg
     * @param   username 手机号
     * @param   password  密码
     * @param   mobilecode  验证码
     * @return \think\response\Json
     */
    public function user_regs()
    {
        $memberData = $this->request->param();
        if(!empty($memberData["username"])){
            if(checkUserIdExists($memberData["username"])=='true'){
                return json(['status' => 1, 'msg' => '用户已存在！']);
            }
            if(is_mobile($memberData["username"])<1){
                $returnData['status'] = 1;
                $returnData['msg'] = '请输入正确的手机号';
                return json($returnData);
            }
            $wheremobile['mobile']=$data["UserId"] = $memberData["username"];
            $data["Mobile"] = $memberData["username"];
            $mobilecode=Db::name('mobilecode')->where($wheremobile)->find();
            if( $memberData["username"]!=$mobilecode['mobile']){
                $returnData['status'] = 1;
                $returnData['msg'] = '和发送短信验证码的手机号码不一致';
                $returnData['data'] = array();
                return json($returnData);
            }
            if(!empty($memberData["mobilecode"])) {
                if (time() - $mobilecode['addtime'] > 120) {
                    $returnData['status'] = 1;
                    $returnData['msg'] = '验证码过期';

                    return json($returnData);
                } else {
                    if ($memberData["mobilecode"] != $mobilecode['code']) {
                        $returnData['status'] = 1;
                        $returnData['msg'] = '验证码错误';

                        return json($returnData);
                    }
                }
                if (!empty($memberData["password"])) {
                    if (strlen($memberData["password"]) < 6) {
                        $returnData['status'] = 1;
                        $returnData['msg'] = '密码不能小于六位字符';

                        return json($returnData);
                    }
                    if (strlen($memberData["password"]) > 16) {
                        $returnData['status'] = 1;
                        $returnData['msg'] = '密码不能大于十六位字符';

                        return json($returnData);
                    }
                    $data["Password"] = md5($memberData["password"]);
                    $data["LevIIPwd"] = md5($memberData["password"]);
                    $data["IsAudit"] = 1;//是否审核（2否 1是）
                    $data["AddDate"] = date('Y-m-d H:i:s', time());//注册时间
                    //插入会员表的信息
                    $Id = Db::name('usermsg')->insertGetId($data);//添加数据并获得此数据在数据库中的id
                    if ($Id) {
                        return json(['status' => 0, 'msg' => '注册成功']);
                    }
                } else {
                    $returnData['status'] = 1;
                    $returnData['msg'] = '密码不能为空！';

                    return json($returnData);
                }
            }else{
                $returnData['status'] = 1;
                $returnData['msg'] = '验证码不能为空';

                return json($returnData);
            }
        }else{
            $returnData['status'] = 1;
            $returnData['msg'] = '用户名不能为空！';

            return json($returnData);
        }
    }

    /**会员收货地址列表
     * 请求方式：http://www.XXX.com/app.php/member/address_list
     * @param   token
     * @return \think\response\Json
     */
    public function address_list(){
//        var_dump($_COOKIE);exit;
        $info=$this->islogin();
//        $info['data']['UserId']='00001';
//        $info['status']=0;
        if($info['status']==0){
            $userid=$info['data']['UserId'];
            $pagecount = 8;
            $pageTotal=0;
            $where['UserId']=$userid;
            $data=[];
            $addresslist=Db::name('comreceiveinfo')->where($where)->order('id desc')->paginate($pagecount);
            if($addresslist){
                $pageTotal = ceil($addresslist->total()/$pagecount);//总页数
                foreach ($addresslist as $val){
                    $data[]=array_change_key_case($val);
                }
            }
            $returnData["data"]['addresslist']=$data;
            $returnData["data"]['total']=$pageTotal;
            $returnData['status']=0;
            $returnData['msg']='获取数据成功';
        }else{
            $returnData['status']=1;
            $returnData['msg']='未登录';
        }
        return json($returnData);
    }

    /**会员地址添加接口
     * 请求方式：http://www.XXX.com/app.php/member/address_add
     * @param   token
     * @param   receivename  收货人姓名
     * @param   city  城市
     * @param   province  省份
     * @param   county  县城
     * @param   address  详细地址
     * @param   mobile  电话
     * @param   IsDefault   是否默认
     * @return \think\response\Json
     */
    public function address_add(){
        $info=$this->islogin();
        if($info['status']==0){
            $userid=$info['data']['UserId'];
            $addressData=$this->request->param();
            if(empty($addressData["receivename"])){

                $returnData['status']=1;
                $returnData['msg']='收货人不能为空！';
                return json($returnData);
            }
            if($addressData["province"]=='请选择'||$addressData["city"]=='请选择'||$addressData["county"]=='请选择'){

                $returnData['status']=2;
                $returnData['msg']='请选择所在地区！';
                return json($returnData);
            }
            if(empty($addressData["address"])){

                $returnData['status']=3;
                $returnData['msg']='详细地址不能为空！';
                return json($returnData);
            }
            /**
             *匹配手机号码
             *规则：
             *手机号码基本格式：
             *前面三位为：
             *移动：134-139 147 150-152 157-159 182 187 188
             *联通：130-132 155-156 185 186
             *电信：133 153 180 189
             *后面八位为：
             *0-9位的数字
             */
            if(!empty($addressData["mobile"])){
                $rule  = "/^((13[0-9])|147|(15[0-35-9])|180|182|(18[5-9]))[0-9]{8}$/A";
                if(!preg_match($rule,$addressData["mobile"])){

                    $returnData['status']=3;
                    $returnData['msg']='电话号码格式错误！';
                    return json($returnData);
                }
            }else{

                $returnData['status']=4;
                $returnData['msg']='电话号码不能为空！';
                return json($returnData);
            }
            $data["UserId"]=$userid;
            $data["ReceiveName"]=$addressData["receivename"];
            $data["Province"]=$addressData["province"];
            $data["City"]=$addressData["city"];
            $data["County"]=$addressData["county"];
            $data["Address"]=$addressData["address"];
            $data["Mobile"]=$addressData["mobile"];
            $data["AddDate"]=date('Y-m-d H:i:s',time());

            if(!empty($addressData["IsDefault"]) && $addressData["IsDefault"]==1){
                $datadefault["IsDefault"]=0;
                Db::name('comreceiveinfo')->where("UserId='".$userid."'")->update($datadefault);
                $data["IsDefault"]=1;
            }else{
                $data["IsDefault"]=0;
            }
            $id=Db::name('comreceiveinfo')->insertGetId($data);
            if(!empty($id)){

                $returnData['status']=0;
                $returnData['msg']='地址添加成功';
            }else{

                $returnData['status']=5;
                $returnData['msg']='地址添加失败';
            }
        }else{

            $returnData['status']=6;
            $returnData['msg']='未登录';
        }
        return json($returnData);
    }

    /**会员地址修改接口
     * 请求方式：http://www.XXX.com/app.php/member/address_edit
     * @param   token
     * @param   receivename  收货人姓名
     * @param   city  城市
     * @param   province  省份
     * @param   county  县城
     * @param   address  详细地址
     * @param   mobile  电话
     * @param   IsDefault   是否默认
     * @param  type  show展示 edit 编辑
     * @return \think\response\Json
     */
    public function address_edit(){
         $info=$this->islogin();
        if($info['status']==0){
            $userid=$info['data']['UserId'];
            $addressData=$this->request->param();
            $id=$this->request->param('id');
                    if (empty($addressData["receivename"])) {
                        $returnData['status'] = 1;
                        $returnData['msg'] = '收货人不能为空！';
                        return json($returnData);
                    }
                    if ($addressData["province"] == '请选择' || $addressData["city"] == '请选择' || $addressData["county"] == '请选择') {
                        $returnData['status'] = 2;
                        $returnData['msg'] = '请选择所在地区！';
                        return json($returnData);
                    }
                    if (empty($addressData["address"])) {

                        $returnData['status'] = 3;
                        $returnData['msg'] = '详细地址不能为空！';
                        return json($returnData);
                    }
                    if (!empty($addressData["mobile"])) {
                        $rule = "/^((13[0-9])|147|(15[0-35-9])|180|182|(18[5-9]))[0-9]{8}$/A";
                        if (!preg_match($rule, $addressData["mobile"])) {

                            $returnData['status'] = 4;
                            $returnData['msg'] = '电话号码格式错误！';
                            return json($returnData);
                        }
                    } else {

                        $returnData['status'] = 5;
                        $returnData['msg'] = '电话号码不能为空！';
                        return json($returnData);
                    }
                    $data["UserId"] = $userid;
                    $data["ReceiveName"] = $addressData["receivename"];
                    $data["Province"] = $addressData["province"];
                    $data["City"] = $addressData["city"];
                    $data["County"] = $addressData["county"];
                    $data["Address"] = $addressData["address"];
                    $data["Mobile"] = $addressData["mobile"];
                    $data["AddDate"] = date('Y-m-d H:i:s', time());

                    if (!empty($addressData["IsDefault"]) && $addressData["IsDefault"]==1) {
                        $datadefault["IsDefault"] = 0;
                        Db::name('comreceiveinfo')->where("UserId='" . $userid . "'")->update($datadefault);
                        $data["IsDefault"] = 1;
                    } else {
                        $data["IsDefault"] = 0;
                    }
                    $where['UserId'] = $userid;
                    $where['Id'] = $id;
                    Db::name('comreceiveinfo')->where($where)->update($data);

                    $returnData['status'] = 0;
                    $returnData['msg'] = '地址修改成功';
        }else{

            $returnData['status']=6;
            $returnData['msg']='未登录';
        }
        return json($returnData);
    }

    /**会员收货地址的删除以及默认收货地址的设置
     * 请求方式：http://www.XXX.com/app.php/member/address_action
     * @param   token
     * @param   type  类型set_default设置  del_default删除
     * @param   id
     * @return \think\response\Json
     */
    public function address_action(){
        $info=$this->islogin();
//        $info['data']['UserId']='15907720288';
//        $info['status']=0;
        if($info['status']==0){
            $userid=$info['data']['UserId'];
            $type=$this->request->param('type');
            $id=$this->request->param('id');
            if($type=='set_default'){//默认收货地址的设置
                $where['UserId']=$userid;
                $updata['IsDefault']=0;
                Db::name('comreceiveinfo')->where($where)->update($updata);
                $where['Id']=$id;
                $updata['IsDefault']=1;
                Db::name('comreceiveinfo')->where($where)->update($updata);
                $returnData['status']=0;
                $returnData['msg']='设置默认收货地址成功';
            }elseif($type=='del_default'){//收货地址的删除
                $where['Id']=$id;
                Db::name('comreceiveinfo')->where($where)->delete();

                $returnData['status']=0;
                $returnData['msg']='地址删除成功';
            }
        }else{

            $returnData['status']=2;
            $returnData['msg']='未登录';
        }
        return json($returnData);
    }

    /**会员密码的重置
     * 请求方式：http://www.XXX.com/app.php/member/user_password_reset
     * @param   token
     * @param   mobile      手机号
     * @param   mobilecode  验证码
     * @param   password  新密码
     ** @param   dlpwd      1登录密码  2支付密码
     * @param   password2   确认新密码
     * @return \think\response\Json
     */
    public function user_password_reset(){
//        Session::set('code_mobile', '11111');
        $info=$this->islogin();
        if($info['status']==0){
            $userid=$info['data']['UserId'];
            $wheremobile['mobile']=$where['UserId']=$userid;
            $memberdata=$this->request->param();
            if($memberdata['mobile']!=$userid){

                $returnData['status']=1;
                $returnData['msg']='输入手机号和登录号码不一致！';
                return json($returnData);
            }
            if(empty($memberdata["mobilecode"])){
                $returnData['status']=1;
                $returnData['msg']='验证码不能为空！';
                return json($returnData);
            }
            $mobilecode=Db::name('mobilecode')->where($wheremobile)->find();
//            var_dump($memberdata);exit;
            if (time() - $mobilecode['addtime'] > 120) {
                $returnData['status'] = 1;
                $returnData['msg'] = '验证码过期';
                return json($returnData);
            } else {
                if ($memberdata["mobilecode"] != $mobilecode['code']) {
                    $returnData['status'] = 1;
                    $returnData['msg'] = '验证码错误';

                    return json($returnData);
                }
            }
            if (!empty($memberdata["password"])) {
                if (strlen($memberdata["password"]) < 6) {
                    $returnData['status'] = 1;
                    $returnData['msg'] = '密码不能小于六位字符';

                    return json($returnData);
                }
                if (strlen($memberdata["password"]) > 16) {
                    $returnData['status'] = 1;
                    $returnData['msg'] = '密码不能大于十六位字符';

                    return json($returnData);
                }
            }else{
                $returnData['status'] = 1;
                $returnData['msg'] = '密码不能为空';
                return json($returnData);
            }
            if($memberdata["password"]!=$memberdata["password2"]){
                $returnData['status']=1;
                $returnData['msg']='密码不一致！';
                return json($returnData);
            }
            if($this->request->param("dlpwd")==2){
                $data["LevIIPwd"]=md5($this->request->param("password"));
                Db::name('usermsg')->where($where)->update($data);
                $returnData['status']=0;
                $returnData['msg']='支付密码修改成功！';

            }else{
                $data["Password"]=md5($this->request->param("password"));
                Db::name('usermsg')->where($where)->update($data);
                Session::clear();
                $returnData['status']=0;
                $returnData['msg']='密码修改成功，请重新登录！';
            }

        }else{
            $returnData['status']=2;
            $returnData['msg']='未登录';
        }
        return json($returnData);
    }
    /**注册发送短信验证码
     * 请求方式：http://www.XXX.com/app.php/member/user_password_reset
     * @param   mobile 手机号码

     * @return \think\response\Json
     */
    public function sendsms(){
        if ($this->request->isPost()) {

//            $mobile= $this->request->post('mobile'); //获取手机号码
            $datass=$this->request->post();
            if(isset($datass['mobile'])){
            $mobile=$datass['mobile'];
            }else{
                $returnData['status'] = 2;
                $returnData['msg'] = '传递参数有误！';
                return json($returnData);
            }
            if(empty($mobile)){
                $returnData['status'] = 2;
                $returnData['msg'] = '手机号码不能为空！';
                return json($returnData);
            }
            if(isset($datass['type'])){
                $type=$this->request->post('type');
                if($type==2){
                    $wherewww['UserId']=$mobile;
                    $lisst=Db::name('usermsg')->where($wherewww)->find();
                    if(!$lisst){
                        $returnData['status'] = 2;
                        $returnData['msg'] = '用户不存在请注册！';
                        return json($returnData);
                    }
                }
            }
            //$key= jh_key(); //key
            $key='695173a1d7230214879942a22b7b96a1';
            $tpl_id= '67202';
            $tpl_value =generate_code();  //验证码
            $black_mobiles="13879503111";
            $value = substr("$tpl_value",7,13); //截取验证编码
            $dtype='json';
            $msgs= array();
            if (strpos($black_mobiles,$mobile) !==false)
            {
                return;
            }
            $is_success=0;
            $sendUrl = 'http://v.juhe.cn/sms/send'; //短信接口的URL
            $smsConf = array(
                'key'   => $key, //您申请的APPKEY
                'mobile'    => $mobile, //接受短信的用户手机号码
                'tpl_id'    => $tpl_id, //您申请的短信模板ID，根据实际情况修改
                'tpl_value' =>$tpl_value, //您设置的模板变量，根据实际情况修改
                'dtype'     => $dtype,
                'value'     =>$value,   //截取验证码存到session
            );
            $content = juhecurl($sendUrl,$smsConf,1); //请求发送短信

            if($content){
                $result = json_decode($content,true);
                $error_code = $result['error_code'];
                if($error_code == 0){
                    //状态为0，说明短信发送成功
                    //echo "短信发送成功,短信ID：".$result['result']['sid'];
                    $where['mobile']=$smsConf['mobile'];
                    $list=Db::name('mobilecode')->where($where)->find();
                    if($list){
                        $updata['code']=$smsConf['value'];
                        $updata['addtime']=time();
                        Db::name('mobilecode')->where($where)->update($updata);
                    }else{
                        $innerdata['mobile']=$smsConf['mobile'];
                        $innerdata['code']=$smsConf['value'];
                        $innerdata['addtime']=time();
                        Db::name('mobilecode')->insertGetId($innerdata);
                    }
                    $is_success=1;
                    $returnData['status'] = 0;
                    $returnData['msg'] = '短信已发送';
                    return json($returnData);
                }else{
                    //状态非0，说明失败
                    $msgs = $result['reason'];
                    $returnData['status'] = 3;
                    $returnData['msg'] = '请输入正确的号码，或联系管理员';
                    return json($returnData);
                    //echo "短信发送失败(".$error_code.")：".$msg;
                }
            }else{
                //返回内容异常，以下可根据业务逻辑自行修改
                $returnData['status'] = 3;
                $returnData['msg'] = '出现异常请联系管理员';
                return json($returnData);
            }
            $data = array(
                'sms_mobile'    => $mobile,
                'sms_content'   => $content,
                'is_success'=>$is_success,
                'result_info'=>$msgs,
            );
            return $is_success;
        }else{
            $returnData['status'] = 1;
            $returnData['msg'] = '传递方式出错';
            return json($returnData);


        }
    }
    /**消费记录查询
     * 请求方式：http://www.XXX.com/app.php/member/accountrecord
     * @return \think\response\Json
     */
    public function accountrecord(){
        $info=$this->islogin();
        if($info['status']==0){
            $userid=$info['data']['UserId'];
            $where['UserId']=$userid;
            $list=Db::name('accountrecord')->where($where)->select();
            if(!empty($list)){
                $returnData['data']=$list;
                $returnData['status']=0;
                $returnData['msg']='查询成功';
            }else{
                $returnData['status']=2;
                $returnData['msg']='暂无数据';
            }

        }else{
            $returnData['status']=1;
            $returnData['msg']='未登录';
        }
        return json($returnData);
    }
    /**忘记密码
     * 请求方式：http://www.XXX.com/app.php/member/user_password_forget
     * @param   mobile      手机号
     * @param   code  验证码
     * @param   password  新密码
     * @param   password2   确认新密码
     * @return \think\response\Json
     */
    public  function user_password_forget(){
        $passworddata=$this->request->param();

        if(isset($passworddata['mobile'])){
            if(!empty($passworddata['mobile'])){
                $wheremobile['mobile']=$passworddata['mobile'];
                $mobilecode=Db::name('mobilecode')->where($wheremobile)->find();
                if($mobilecode){
                    if(isset($passworddata['code'])){
                        if(!empty($passworddata['code'])&&($passworddata['code']==$mobilecode['code'])){
                            if (time() - $mobilecode['addtime'] > 120) {
                                $returnData['status'] = 1;
                                $returnData['msg'] = '验证码过期';
                            } else {
                                if(isset($passworddata['password'])){
                                    if (strlen($passworddata["password"]) < 6 && strlen($passworddata["password"]) > 16) {
                                        $returnData['status'] = 1;
                                        $returnData['msg'] = '密码不能小于六位大于16位字符';
                                    }else{
                                        if(isset($passworddata["password2"])){
                                            if($passworddata["password"]!=$passworddata["password2"]){
                                                $returnData['status']=1;
                                                $returnData['msg']='密码不一致！';
                                            }else{
                                                $data["Password"]=md5($passworddata["password"]);
                                                $where['UserId']=$passworddata['mobile'];
                                                $res=Db::name('usermsg')->where($where)->update($data);
                                                $returnData['status']=0;
                                                $returnData['msg']='修改成功！';
                                            }
                                        }else{
                                            $returnData['status']=1;
                                            $returnData['msg']='缺少传递参数password2！';
                                        }
                                    }
                                }else{
                                    $returnData['status']=1;
                                    $returnData['msg']='缺少传递参数password！';
                                }
                            }
                        }else{
                            $returnData['status']=1;
                            $returnData['msg']='验证码错误！';
                        }
                    }else{
                        $returnData['status']=1;
                        $returnData['msg']='缺少传递参数code！';
                    }
                }else{
                    $returnData['status']=1;
                    $returnData['msg']='发送短信的手机号码和提交号码不一致！';
                }
            }else{
                $returnData['status']=1;
                $returnData['msg']='手机号码不能为空！';
            }
        }else{
            $returnData['status']=1;
            $returnData['msg']='缺少传递参数mobile！';
        }
        return json($returnData);
    }
}
