<?php

/*
 * lxj
 * 20170217
 * 18046521228
 */

namespace Shop\Controller;

use Shop\Controller\CommonController;

/**
 * 会员控制器4000
 *
 * @author Administrator
 */
class UserController extends CommonController {

    protected $user;

    public function __construct() {
        parent::__construct();
        $this->user = M('ewei_shop_member');

    }

    /**
     * 用户基本信息
     */
    public function info() {
        $openid = I('get.openid', '', 'string');
        if (empty($openid) || empty($this->uniacid)) {
            $this->outFormat('null', '参数错误', 4001, 'json');
        }
        $user = $this->user
                ->field('id,realname,mobile,weixin,gender,province,city,birthyear,birthmonth,birthday')
                ->where("uniacid=%d and openid='%s'", array($this->uniacid, $openid))
                ->find();
        if($user['birthyear'] && $user['birthmonth'] && $user['birthday']){
            $user['birth'] = $user['birthyear'].'-'.$user['birthmonth'] .'-'.$user['birthday'];
        }else{
            $user['birth'] = '';
        }
        
        if ($user) {
            $this->outFormat($user, 'ok', 0, 'json');
        } else {
            $this->outFormat('null', '该用户不存在', 4002, 'json');
        }
    }

    /**
     * 更新用户基本信息
     */
    public function updateInfo() {
//        姓名、手机号码、微信号必填    
        $openid = I('post.openid', '', 'string');

        $realname = I('post.realname', '', 'string');
        if (empty($realname)) {
            $this->outFormat('null', '请输入姓名', 40003, 'json');
        }
        $mobile = I('post.mobile', '', 'string');
        $rep = "/^(13[0-9]|14[0-9]|15[0-9]|17[0-9]|18[0-9])\d{8}$/i";
        if (empty($mobile) || !preg_match($rep, $mobile)) {
            $this->outFormat('null', '请输入正确的手机号码', 4004, 'json');
        }
        $weixin = I('post.weixin', '', 'string');
        if (empty($weixin)) {
            $this->outFormat('null', '请输入微信号', 4005, 'json');
        }
        $sex = I('post.sex');
        if ($sex) {
            if ($sex != 1 && $sex != 2) {
                $this->outFormat('null', '参数错误', 4006, 'json');
            }
        }
        $province = I('post.province', '', 'string');
        $city = I('post.city', '', 'string');
        $birth = I('post.birth','','string');
        $birth = explode('-', $birth);
        $birthyear = $birth[0];
        $birthmonth = $birth[1];
        $birthday = $birth[2];
        $this->getMember($this->token, $openid);
        $data = array(
            'realname' => $realname,
            'mobile' => $mobile,
            'weixin' => $weixin,
            'gender' => $sex,
            'province' => $province,
            'city' => $city,
            'birthyear' => $birthyear,
            'birthmonth' => $birthmonth,
            'birthday' => $birthday,
        );
        $result = $this->user
                ->where("uniacid=%d and openid='%s'", array($this->token, $openid))
                ->save($data);
        if ($result !== false) {
            $this->outFormat(array('status' => 1), 'ok', 0, 'json');
        } else {
            $this->outFormat('null', '操作失败', 4007, 'json');
        }
    }

    /**
     * 获取用户openid
     */
    public function getOpenid() {
        $code = I('post.code', '', 'string');
        $ptype = I('post.ptype',0,'intval');
        if (empty($this->token) || empty($code) || empty($ptype)) {
            $this->outFormat('null', '参数错误', 4008, 'json');
        }
        $wxapps = M('uni_wxapps')
                ->field('appId,appSecret')
                ->where('uniacid=%d and ptype=%d', array($this->token,$ptype))
                ->find();
        if(empty($wxapps)){
            $this->outFormat('null', '商户不存在', 4009, 'json');
        }
//        $appid = 'wx17bdd0797c3b43a7';
//        $appSecret = '7c1ce42e079bcab3bc9e1d1a3e6b01cd';
//        $url = 'https://api.weixin.qq.com/sns/jscode2session?appid=' . $appid . '&secret=' . $appSecret . '&js_code=' . $code . '&grant_type=authorization_code';
        $url = 'https://api.weixin.qq.com/sns/jscode2session?appid=' . $wxapps['appid'] . '&secret=' . $wxapps['appsecret'] . '&js_code=' . $code . '&grant_type=authorization_code';
        $result = http($url);
        $wx = json_decode($result,true);
        $openid = $wx['openid'];
        if (empty($openid)) {
            $this->outFormat('null', 'openid获取失败', 4010, 'json');
        }
        $this->addUser($openid);
        $info = array(
            'openid'    =>$openid,
            'status'    =>1,
        );
        $this->outFormat($info, 'ok', 0, 'json');

    }
        
    /**
     * 注册用户
     */
    protected function addUser($openid){
        $data['uniacid'] = $this->token;
        $data['openid']= $openid; //openid
        $result = $this->user
                    ->where($data)
                    ->count();
        if(empty($result)){
            $data['createtime']=time();
            $result = $this->user
                    ->add($data); 
        }
         return $result;
    }
    
    /**
     * 更新用户微信昵称和头像
     */
    public function upwxinfo(){
        $nickname = I('post.nickname', '', 'string');
        $gender = I('post.gender',0,'intval');
        $openid = I('post.openid','','string');
        $avatar = I('post.avatar','','string');
        $this->getMember($this->token, $openid);
        
//        $data = json_decode($content);
        $info = array(
            'nickname'  =>$nickname,
            'gender'    =>$gender,
            'avatar'    =>$avatar
        );
        $where = array(
            'openid'    =>$openid,
            'uniacid'   =>$this->token
        );
        $result = $this->user
                ->where($where)
                ->save($info);
        if($result !== false){
            $this->outFormat(array('status'=>1), 'ok', 0, 'json');
        }else{
            $this->outFormat('null', '更新失败', 4011, 'json');
        }
        
    }
    
    /**
     * 用户等级信息
     */
    public function userLevel(){
        $openid = I('get.openid','','string');
        $user = $this->getMember($this->uniacid, $openid,'id,level,nickname,avatar');
        $level = M('ewei_shop_member_level')->where(array('uniacid'=>$this->uniacid,'level'=>$user['level']))->getField('levelname');
        $set = $this->getSysset('shop', $this->uniacid);
        $lname = $set['levelname'] ? $set['levelname'] :'普通会员';
        $user['level'] = $level ? $level:$lname;
        $this->outFormat($user, 'ok', 0, 'json');
    }
    

}
