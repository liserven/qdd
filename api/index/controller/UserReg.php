<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/19
 * Time: 11:07
 */
namespace api\index\controller;
use think\Db;
use think\Request;
use think\Session;
class UserReg{
    protected $request;
    public function __construct(){
        if (null === $this->request) {

            $this->request = Request::instance();

        }

    }

//用户注册接口

    public function user_Regs(){
        $memberData = $this->request->post();
        $rule = '/^((13[0-9])|147|(15[0-35-9])|180|182|(18[5-9]))[0-9]{8}$/A';

        // 判断推荐人情况
        $puserinfo = db::name('usermsg')->where('invitecode = "' . $memberData["introduceId"] . '"')->find();
        if(empty($puserinfo)){
            return json(['status' => 2, 'msg' => '邀请码不存在！']);
        }
        $data["ReferrerID"] = $puserinfo['UserId'];
        $memberData["mobile"] = $memberData["UserId"];
        if (!empty($memberData["mobile"])) {

            if (checkMobileExists($memberData["mobile"]) == 'true') {
                return json(['status' => 0, 'msg' => '手机号已存在！']);
            }else if(!preg_match($rule, $memberData["mobile"])) {
                $returnData['status'] = 0;
                $returnData['msg'] = '电话号码格式错误！';
                $returnData['data'] = array();
                return json($returnData);
            } else {
                $data["Mobile"] = $memberData["mobile"];
                $data["UserId"] = $memberData["UserId"];
                if (!empty($memberData["password"])) {
                    $data["Password"] = md5($memberData["password"]);
                    /*if ($memberData["password"] != $memberData["password2"]) {
                        $returnData['status'] = 0;
                        $returnData['msg'] = '密码不一致！';
                        $returnData['data'] = array();
                        return json($returnData);
                    }*/
                    $memberData["paypassword"] = $memberData["password"];
                    if (!empty($memberData["paypassword"])) {
                        /*if ($memberData["paypassword"] != $memberData["paypassword2"]) {
                            $returnData['status'] = 0;
                            $returnData['msg'] = '支付密码不一致！';
                            $returnData['data'] = array();
                            return json($returnData);
                        }*/
                        $data["LevIIPwd"] = md5($memberData["password"]);
                        //$data["ReferrerID"] = $memberData["introduceId"];//会员推荐人
                        $data["AddDate"] = date('Y-m-d H:i:s', time());//注册时间
                        //注册完成之后进入待付款状态
                        $data["IsAudit"] = 0;//是否审核（0：待付款 1：账号已激活 2：待上传资料 3：待审核）

                        // 生成邀请码
                        $count = 1;
                        while ($count) {
                            $code = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                            $rand = $code[rand(0, 25)]
                                . strtoupper(dechex(date('m')))
                                . date('d') . substr(time(), -5)
                                . substr(microtime(), 2, 5)
                                . sprintf('%02d', rand(0, 99));
                            for (
                                $a = md5($rand, true),
                                $s = '0123456789ABCDEFGHIJKLMNOPQRSTUV',
                                $d = '',
                                $f = 0;
                                $f < 6;
                                $g = ord($a[$f]),
                                $d .= $s[($g ^ ord($a[$f + 8])) - $g & 0x1F],
                                $f++
                            ) ;
                            $count = db::name('usermsg')->where('invitecode = "' . $d . '"')->count();
                        }
                        $data['invitecode'] = $d;
                        //插入会员表的信息
                        $Id = Db::name('usermsg')->insertGetId($data);//添加数据并获得此数据在数据库中的id
                        if ($Id) {
                            Session::set('membername', $data["UserId"]);
                            //添加会员位置关系
                            $returnData = $this->user_recommend_diagram($data['UserId']);
                            if ($returnData['status'] == 0) {
                                return json(['status' => 0, 'msg' => $returnData['msg']]);
                            }
                            return json(['data' => array(), 'status' => 1, 'msg' => '注册成功']);
                        }
                    } else {
                        $returnData['status'] = 0;
                        $returnData['msg'] = '支付密码不能为空！';
                        $returnData['data'] = array();
                        return json($returnData);
                    }
                } else {
                    $returnData['status'] = 0;
                    $returnData['msg'] = '密码不能为空！';
                    $returnData['data'] = array();
                    return json($returnData);
                }
            }
        }else {
            $returnData['status'] = 0;
            $returnData['msg'] = '电话号码不能为空！';
            $returnData['data'] = array();
            return json($returnData);
        }

    }


    /**

     * 注册会员的时候向会员推荐关系表中添加数据

     */

    public function user_recommend_diagram($userId){
        $where['userId'] = $userId;
        //要注册的会员的信息
        $userInfo = Db::name('usermsg')->where($where)->find();
        try {
            //把直接推荐人的推荐信息插入推荐信息表中
            $data['userId'] = $userId;//会员编号
            $data['pid'] = $userInfo['ReferrerID'];//会员的每一层上级推荐人
            $data['lay'] = 1;//会员在该层推荐人pid的层数
            Db::name('userrecommenddiagram')->insertGetId($data);
            //找到要激活会员的直接推荐人上面的推荐关系，层数+1存入要激活的会员的关系图中
            if ($userInfo['ReferrerID'] != '00001') {
                $insertSql = "insert into userrecommenddiagram(userId,pid,lay) select '" . $userId . "',pid,lay+1" . "  from userrecommenddiagram where userId='" . $userInfo['ReferrerID'] . "'";
                Db::execute($insertSql);//添加到表中
            }
            return ['status' => 1, 'msg' => '操作成功'];
        }catch (\Exception $e) {
            return ['status' => 0, 'msg' => '操作失败' . $e->getMessage()];
        }

    }

    public function security_code(){

        $mobile=$this->request->param('mobile');

        if(empty($mobile)){

            $returnData['status'] = 0;

            $returnData['msg'] = '手机号不能为空';

        }else{

            $where['UserId']=$mobile;

            $userinfo=Db::name('usermsg')->where($where)->count();

            if(!empty($userinfo)){

                $security_code=$this->request->param('security_code');

                if(empty($security_code)){

                    $returnData['status'] = 0;

                    $returnData['msg'] = '验证码不能为空';

                }else{

                    if($security_code=='888888'){

                        $returnData['status'] = 1;

                        $returnData['msg'] = '验证通过';

                        $returnData['mobile'] = $mobile;

                        Session::set('security_code',$security_code);

                    }else{

                        $returnData['status'] = 0;

                        $returnData['msg'] = '请输入默认验证码：888888';

                    }

                }

            }else{

                $returnData['status'] = 0;

                $returnData['msg'] = '该手机所对应的会员不存在';

            }

        }

        return json($returnData);

    }



    public function reset_password(){

        if(Session::get('security_code')){

            $password=$this->request->param('password');

            $password2=$this->request->param('password2');

            if(!empty($password)&&!empty($password2)){

                if($password!=$password2){

                    $returnData['status'] = 0;

                    $returnData['msg'] = '密码不一致';

                }else{

                    $mobile=$this->request->param('mobile');

                    $where['UserId']=$mobile;

                    $userinfo=Db::name('usermsg')->where($where)->count();

                    if(!empty($userinfo)){

                        $data['Password']=md5($password);

                        $code=Db::name('usermsg')->where($where)->update($data);

                        if($code){

                            $returnData['status'] = 1;

                            $returnData['msg'] = '密码重置成功';

                            Session::set('security_code','');

                        }else{

                            $returnData['status'] = 0;

                            $returnData['msg'] = '密码重置失败';

                        }

                    }else{

                        $returnData['status'] = 0;

                        $returnData['msg'] = '该手机所对应的会员不存在';

                    }

                }

            }else{

                $returnData['status'] = 0;

                $returnData['msg'] = '密码不能为空';

            }

        }else{

            $returnData['status'] = 0;

            $returnData['msg'] = '该手机未通过验证';

        }



        return json($returnData);

    }

}

