<?php
namespace api\index\controller;


use think\Db;
use think\Log;
use think\Session;
use think\Cookie;
class User extends Auth
{
    //扫描微信公众号注册会员
    /*public function user_add($userdata){
        if(!empty($userdata)){
            $openidData=Db::name('usermsg')->where("WxOpenid='".$userdata['wxopenid']."'")->find();
            if(empty($openidData)){
                $password=rand(100000, 999999);
                $levllpwd='123456';
                $userid='wx_'.time();
                $addData['UserId']=$userid;
                $addData['Nickname']=$userdata['nickname'];
                $addData['Sex']=$userdata['sex'];
                $addData['Province']=$userdata['province'];
                $addData['City']=$userdata['city'];
                $addData['AvatarUrl']=$userdata['avatarurl'];
                $addData['WxOpenid']=$userdata['wxopenid'];
                $addData['Password']=md5($password);
                $addData['LevIIPwd']=md5($levllpwd);
                $addData['SupplierType']=0;
                $id=Db::name('usermsg')->insertGetId($addData);
                if(!empty($id)){
                    $returnData['data']=['userid'=>$addData['UserId'],'password'=>$password];
                    $returnData['status']=1;
                    $returnData['msg']='添加成功';
                }else{
                    $returnData['status']=0;
                    $returnData['msg']='添加失败';
                    $returnData['data']=['userid'=>''];
                }
            }else{
                $returnData['data']=['userid'=>$openidData['UserId']];
                $returnData['status']=0;
                $returnData['msg']='已注册';
            }
        }else{
            $returnData['status']=0;
            $returnData['msg']='参数不能为空';
        }
        return $returnData;
    }*/

     /**
     * 会员推荐关系
     * @return \think\response\Json
     */
    public function get_recommend(){
        //        if($this->loginStatus){
        $userid = $this->request->param('userid');
        $id = $this->request->param('id');
        $client = $this->request->param('client');
        $sid = $this->request->param('sid');
        if ( !empty($id)) {
            $userid = $id;
        }
        $where['ReferrerID'] = $userid;
        //            $where['isAudit']=1;
        $list = Db::name('usermsg')->where($where)->select();
        if ( !empty($list)) {
            if (empty($id)) {
                $userData = $this->getUserData($userid);
                $returnData[] = ['id' => $userid, 'pId' => $userid, 'name' => '0------[' . $userid . ']. ' . $userData['username'] . $this->request->param('userid') . ',<cite class=c-blue>' . $userData['typename'] . '</cite>' . $userData['AddDate'], 'isParent' => $userData['isParent'], 'sid' => 0];
            }
            foreach ($list as $val) {
                if ( !empty($client) && $client == 'users' && Session::get('userId') == $val['UserId']) {
                    $returnData = [];
                    $returnData['status'] = 0;
                    $returnData['msg'] = '无权查看上级';

                    return json($returnData);
                }
                $userData = $this->getUserData($val['UserId']);
                $returnData[] = ['id' => $val['UserId'], 'pId' => $userid, 'name' => $sid + '1' . '------[' . $val['UserId'] . '] ' . $userData['username']  . ',<cite class=c-blue>' . $userData['typename'] . '</cite>' . $userData['AddDate'], 'isParent' => $userData['isParent'], 'sid' => $sid + 1];
            }
        } else {
            $returnData = [];
        }
        //        }else{
        //            $returnData['status']=0;
        //            $returnData['msg']='未登录';
        //        }
        return json($returnData);
    }

    private function getUserData($userid)
    {
        $condition['userId'] = $userid;
        //        $condition['isAudit']=1;
        $userData = Db::name('usermsg')->where($condition)->field('trueName,userType,AddDate,position')->find();
        $where1['id'] = $userData['userType'];
        $userType = Db::name('usertype')->where($where1)->field('Name')->find();
        //        $userType=Db::name('usertype')->where('id='.$userData['userType'])->field('Name')->find();
        $flag = false;
        $cond['ReferrerID'] = $userid;
        //        $cond['isAudit']=1;
        $sublist = Db::name('usermsg')->where($cond)->select();
        if ( !empty($sublist)) {
            $flag = true;
        }
        if ($userData['position']==1){
            $userData['position']='左区';
        }else{
            $userData['position']='右区';
        }
        $data = ['username' => $userData['trueName'], 'AddDate' => $userData['AddDate'], 'typename' => $userType['Name'], 'isParent' => $flag,'position'=>$userData['position']];

        return $data;
    }

    /**
     * 获取会员的下级会员(4层)
     */
    public function get_subordinate()
    {
        //        if($this->loginStatus){
        $userid = $this->request->param('userid');
        if (empty($userid)) {
            $userid = 'H8888';
        }
        $client = $this->request->param('client');
        $where['pid'] = $userid;
        $where['pidLay'] = ['<=', '3'];
        $list = Db::name('userpositiondiagram')->where($where)->order('pidPosition asc,id asc')->limit(0, 15)->select();


        $colorData = Db::name('usertype')->where('id<>1')->field('color,id')->order('id')->select();

        $typeNameData = Db::name('usertype')->where('id<>1')->field('Name')->order('id')->select();

        //            $levelName=['一星会员','二星会员','三星会员','四星会员','五星会员'];
        $levelName = $typeNameData;
        foreach ($colorData as $key => $val) {
            $colorData[$key]['name'] = $levelName[$key]['Name'];
        }

        $datascource = [];
        if ( !empty($list)) {
            $userData['addDate'] = date('Y-m-d H:i:s', time());
            //构建第一层
            $htmlData = $this->getUserHtml($userid, $client);
            $data = ['userid' => $userid, 'name' => $htmlData['userid'], 'userdata' => $htmlData['userdata'], 'sub' => []];
            $datascource = ['name' => $htmlData['userid'], 'title' => $htmlData['userdata'], 'className' => 'frontend1'];
            //构建第二层
            $temp = $list;
            foreach ($list as $key => $val) {
                if ($val['pidLay'] == 1 && $val['pid'] == $userid) {
                    $index = $val['upPidPosition'];
                    $htmlData = $this->getUserHtml($val['userId'], $client);
                    $data['sub'][$index] = ['userid' => $val['userId'], 'name' => $htmlData['userid'], 'userdata' => $htmlData['userdata'], 'sub' => []];
                    $datascource['children'][$index - 1] = ['name' => $htmlData['userid'], 'title' => $htmlData['userdata'], 'className' => 'middle-level'];
                    unset($temp[$key]);//清除满足条件的数据
                }
            }
            //构建第三层
            $list = $temp;
            foreach ($list as $key => $val) {
                if ($val['pidLay'] == 2) {
                    foreach ($data['sub'] as $k => $v) {
                        if ($val['upPid'] == $v['userid']) {
                            $index = $val['upPidPosition'];
                            $htmlData = $this->getUserHtml($val['userId'], $client);
                            $data['sub'][$k]['sub'][$index] = ['userid' => $val['userId'], 'name' => $htmlData['userid'], 'userdata' => $htmlData['userdata'], 'sub' => []];
                            $datascource['children'][$k - 1]['children'][$index - 1] = ['name' => $htmlData['userid'], 'title' => $htmlData['userdata'], 'className' => 'product-dept'];
                            unset($temp[$key]);//清除满足条件的数据
                        }
                    }
                }
            }
            //构建第四层
            $list = $temp;
            //                foreach ($list as $key=>$val){
            //                    if($val['pidLay']==3){
            //                        foreach ($data['sub'] as $k=>$v){
            //                            foreach ($v['sub'] as $n=>$m){
            //                                if($val['upPid']==$m['userid']){
            //                                    $index=$val['upPidPosition'];
            //                                    $htmlData=$this->getUserHtml($val['userId'],$client);
            //                                    $data['sub'][$k]['sub'][$n]['sub'][$index]=['userid'=>$val['userId'],'name'=>$htmlData['userid'],'userdata'=>$htmlData['userdata'],'sub'=>[]];
            //                                    $datascource['children'][$k-1]['children'][$n-1]['children'][$index-1]=['name'=>$htmlData['userid'],'title'=>$htmlData['userdata'],'className'=>'pipeline1'];
            //                                }
            //                            }
            //                        }
            //                    }
            //                }

            //构建会员注册数据--start
            if (empty($data['sub'][1])) {
                $datascource['children'][0] = ['name' => $this->getRegisterHtml($data['userid'], 1, $client), 'title' => '', 'className' => 'frontend2'];
            }

            if (empty($data['sub'][2])) {
                $datascource['children'][1] = ['name' => $this->getRegisterHtml($data['userid'], 2, $client), 'title' => '', 'className' => 'frontend2'];
            }

            //                foreach ($data['sub'] as $key=>$val){
            //                    if(empty($val['sub'])){
            //                        $datascource['children'][$key-1]['children'][]=['name'=>$this->getRegisterHtml($val['userid'],1,$client),'title'=>'','className'=>'frontend2'];
            //                        $datascource['children'][$key-1]['children'][]=['name'=>$this->getRegisterHtml($val['userid'],2,$client),'title'=>'','className'=>'frontend2'];
            //                    }else{
            //                        if(empty($val['sub'][$key])){
            //                            $datascource['children'][$key-1]['children'][]=['name'=>$this->getRegisterHtml($val['userid'],$key,$client),'title'=>'','className'=>'frontend2'];
            //                        }
            //                        foreach ($val['sub'] as $k=>$v){
            //                            if(empty($v['sub'])){
            //                                $datascource['children'][$key-1]['children'][$k-1]['children'][]=['name'=>$this->getRegisterHtml($v['userid'],1,$client),'title'=>'','className'=>'frontend2'];
            //                                $datascource['children'][$key-1]['children'][$k-1]['children'][]=['name'=>$this->getRegisterHtml($v['userid'],2,$client),'title'=>'','className'=>'frontend2'];
            //                            }else {
            //                                if (empty($v['sub'][$k])) {
            //                                    $datascource['children'][$key - 1]['children'][$k - 1]['children'][] = ['name' => $this->getRegisterHtml($v['userid'], $k,$client), 'title' => '', 'className' => 'frontend2'];
            //                                }
            //                            }
            //                        }
            //                    }
            //                }
            //构建会员注册数据--end

            $returnData['data'] = $datascource;
            $returnData['levelcolor'] = $colorData;
            $returnData['status'] = 1;
            $returnData['msg'] = '会员数据！';
        } else {
            $htmlData = $this->getUserHtml($userid, $client);

            $data = ['userid' => $userid, 'name' => $htmlData['userid'], 'userdata' => $htmlData['userdata'], 'sub' => []];
            $datascource = ['name' => $htmlData['userid'], 'title' => $htmlData['userdata'], 'className' => 'frontend1'];
            $datascource['children'][0] = ['name' => $this->getRegisterHtml($data['userid'], 1, $client), 'title' => '', 'className' => 'frontend2'];
            $datascource['children'][1] = ['name' => $this->getRegisterHtml($data['userid'], 2, $client), 'title' => '', 'className' => 'frontend2'];

            $returnData['data'] = $datascource;
            $returnData['levelcolor'] = $colorData;
            $returnData['status'] = 1;
            $returnData['msg'] = '会员数据！';
        }
        //        }else{
        //            $returnData['status']=0;
        //            $returnData['msg']='未登录';
        //        }
        return json($returnData);
    }

    /**
     * 根据会员id获取会员结算数据
     * @param $userid
     * @return mixed
     */
    private function getUserHtml($userid, $client)
    {
        $where['userId'] = $userid;
        $data = Db::name('usermsg')->where($where)->field('leftAccumulate,leftBalance,leftNew,rightAccumulate,rightBalance,rightNew,addDate,userType')->find();
        if ($data) {
            $userData['leftAccumulate'] = $data['leftAccumulate'];
            $userData['leftBalance'] = $data['leftBalance'];
            $userData['leftNew'] = $data['leftNew'];
            $userData['rightAccumulate'] = $data['rightAccumulate'];
            $userData['rightBalance'] = $data['rightBalance'];
            $userData['rightNew'] = $data['rightNew'];
            $userData['addDate'] = $data['addDate'];
            $userData['userType'] = $data['userType'];
        } else {
            $userData['leftAccumulate'] = 0;
            $userData['leftBalance'] = 0;
            $userData['leftNew'] = 0;
            $userData['rightAccumulate'] = 0;
            $userData['rightBalance'] = 0;
            $userData['rightNew'] = 0;
            $userData['addDate'] = date('Y-m-d H:i:s', time());
            $userData['userType'] = 1;
        }
        $userData['userId'] = $userid;

        return $this->buildHtml($userData, $client);
    }

    private function buildHtml($userData, $client)
    {
        if ($client == 'qiantai') {
            $string['userid'] = "<div style='background-color: " . $this->getColor($userData['userType']) . "'><a href='/index.php/index/network_tree/network_tree?userid=" . $userData['userId'] . "' style='text-decoration:none;color:#fff'>" . $userData['userId'] . "</a></div>";
        } else {
            $string['userid'] = "<div style='background-color: " . $this->getColor($userData['userType']) . "'><a href='/admin.php/admin/network_tree/index.html?userid=" . $userData['userId'] . "' style='text-decoration:none;color:#fff'>" . $userData['userId'] . "</a></div>";
        }
        $string['userdata'] = "<div style='width: 30px;float: left;'><span style='color:#fff;'>累计：</span></div><div style='width:60px;float: left;'>分销区</div><div style='width:60px;float: left;'>帮扶区</div>";
        $string['userdata'] .= "<div style='clear:both;'></div>";
        $string['userdata'] .= "<div style='width: 30px;float: left;'>累计：</div><div style='width:60px;float: left;'>" . $userData['leftAccumulate'] . "</div><div style='width:60px;float: left;'>" . $userData['rightAccumulate'] . "</div>";
        $string['userdata'] .= "<div style='clear:both;'></div>";
        $string['userdata'] .= "<div style='width: 30px;float: left;'>结余：</div><div style='width:60px;float: left;'>" . $userData['leftBalance'] . "</div><div style='width:60px;float: left;'>" . $userData['rightBalance'] . "</div>";
        $string['userdata'] .= "<div style='clear:both;'></div>";
        $string['userdata'] .= "<div style='width: 30px;float: left;'>新增：</div><div style='width:60px;float: left;'>" . $userData['leftNew'] . "</div><div style='width:60px;float: left;'>" . $userData['rightNew'] . "</div>";
        $string['userdata'] .= "<div style='clear:both;'></div>";
        $string['userdata'] .= "<span style='margin-left:0px'>" . $userData['addDate'] . "</span><br/>";
        if ( !empty($userData['className'])) {
            $string['className'] = $userData['className'];
        }

        return $string;
    }

    private function getColor($userType)
    {
        $where['id'] = $userType;
        $data = Db::name('usertype')->where($where)->field('color')->find();

        return $data['color'];
    }

    private function getRegisterHtml($userid, $position, $client)
    {
        if ($client == 'qiantai') {
            //$string="<div style='background: #0f743c;width: 50px;margin-left: 39px;'><a href='/index.php/index/user_registered/index?contactid=".$userid."&position=".$position."' style='text-decoration:none;color:#fff'>注册</a></div>";
            $string = "<div style='background: #0f743c;width: 50px;margin-left: 39px;'><a  style='text-decoration:none;color:#fff'>可注册</a></div>";
        } else {
            //$string="<div style='background: #0f743c;width: 50px;margin-left: 39px;'><a href='/admin.php/admin/user_registered/index?contactid=".$userid."&position=".$position."' style='text-decoration:none;color:#fff'>注册</a></div>";
            $string = "<div style='background: #0f743c;width: 50px;margin-left: 39px;'><a  style='text-decoration:none;color:#fff'>可注册</a></div>";
        }

        return $string;
    }

    //扫描微信公众号注册会员
    public function user_add($userdata)
    {
        if ( !empty($userdata)) {
            $where2['WxOpenid'] = $userdata['wxopenid'];
            $openidData = Db::name('usermsg')->where($where2)->find();
            //            $openidData=Db::name('usermsg')->where("WxOpenid='".$userdata['wxopenid']."'")->find();
            if (empty($openidData)) {
                $password = rand(100000, 999999);
                $levllpwd = '123456';
                $userid = 'wx_' . time();
                $addData['UserId'] = $userid;
                $addData['Nickname'] = $userdata['nickname'];
                $addData['Sex'] = $userdata['sex'];
                $addData['Province'] = $userdata['province'];
                $addData['City'] = $userdata['city'];
                $addData['AvatarUrl'] = $userdata['avatarurl'];
                $addData['WxOpenid'] = $userdata['wxopenid'];
                $addData['Password'] = md5($password);
                $addData['LevIIPwd'] = md5($levllpwd);
                $addData['SupplierType'] = 0;

                $id = Db::name('usermsg')->insertGetId($addData);
                if ( !empty($id)) {
                    $returnData['data'] = ['userid' => $addData['UserId'], 'password' => $password];
                    $returnData['status'] = 1;
                    $returnData['msg'] = '添加成功';
                } else {
                    $returnData['status'] = 0;
                    $returnData['msg'] = '添加失败';
                    $returnData['data'] = ['userid' => ''];
                }
            } else {
                $returnData['data'] = ['userid' => $openidData['UserId']];
                $returnData['status'] = 0;
                $returnData['msg'] = '已注册';
            }
        } else {
            $returnData['status'] = 0;
            $returnData['msg'] = '参数不能为空';
        }

        return $returnData;
    }

    /**
     * 接点关系
     * @return \think\response\Json
     */
    public function get_network()
    {
        //        if($this->loginStatus){
        $userid = $this->request->param('userid');
        $id = $this->request->param('id');
        $client = $this->request->param('client');
        $sid = $this->request->param('sid');
        if ( !empty($id)) {
            $userid = $id;
        }
        $where['contactId'] = $userid;
        //            $where['isAudit']=1;
        $list = Db::name('usermsg')->where($where)->select();
        if ( !empty($list)) {
            if (empty($id)) {
                $userData = $this->getUserDatas($userid);
                $returnData[] = ['id' => $userid, 'pId' => $userid, 'name' => '0------[' . $userid . ']. ' . $userData['username'] .$this->request->param('userid') .','.$userData['position']. ',<cite class=c-blue>' . $userData['typename'] . '</cite>' . $userData['AddDate'], 'isParent' => $userData['isParent'], 'sid' => 0];
            }
            foreach ($list as $val) {
                if ( !empty($client) && $client == 'users' && Session::get('userId') == $val['UserId']) {
                    $returnData = [];
                    $returnData['status'] = 0;
                    $returnData['msg'] = '无权查看上级';

                    return json($returnData);
                }
                $userData = $this->getUserDatas($val['UserId']);
                $returnData[] = ['id' => $val['UserId'], 'pId' => $userid, 'name' => $sid + '1' . '------[' . $val['UserId'] . '] ' . $userData['username'].',' .$userData['position']. ',<cite class=c-blue>'  . $userData['typename'] . '</cite>' . $userData['AddDate'], 'isParent' => $userData['isParent'], 'sid' => $sid + 1];
            }
        } else {
            $returnData = [];
        }
        //        }else{
        //            $returnData['status']=0;
        //            $returnData['msg']='未登录';
        //        }
        return json($returnData);
    }

    private function getUserDatas($userid)
    {
        $condition['userId'] = $userid;
        //        $condition['isAudit']=1;
        $userData = Db::name('usermsg')->where($condition)->field('trueName,userType,AddDate,position')->find();
        $where1['id'] = $userData['userType'];
        $userType = Db::name('usertype')->where($where1)->field('Name')->find();
        //        $userType=Db::name('usertype')->where('id='.$userData['userType'])->field('Name')->find();
        $flag = false;
        $cond['contactId'] = $userid;
        //        $cond['isAudit']=1;
        $sublist = Db::name('usermsg')->where($cond)->select();
        if ( !empty($sublist)) {
            $flag = true;
        }
        if ($userData['position']==1){
            $userData['position']='左区';
        }else{
            $userData['position']='右区';
        }
        $data = ['username' => $userData['trueName'], 'AddDate' => $userData['AddDate'], 'typename' => $userType['Name'], 'isParent' => $flag,'position'=>$userData['position']];

        return $data;
    }


}