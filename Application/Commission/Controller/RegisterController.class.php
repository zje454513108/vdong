<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Commission\Controller;

use Commission\Controller\CommonController;

/**
 * 分销商注册---51001
 */
class RegisterController extends CommonController {

    /**
     * 分销商显示
     */
    public function index() {
        $openid = I('get.openid', '', 'string');
        $member = $this->getMemberInfo($this->uniacid, $openid);
        $set = $this->getSet($this->uniacid, 'commission');
        $shop_set = $this->getSysset('shop', $this->uniacid);
        $regbg = getImgUrl($set['regbg']); //背景图
        if ($member['isagent'] == 1 && $member['status'] == 1) {
            $this->outFormat('', '您已经是分销商', 51001);
        }
        $status = $set['become_order'] == 0 ? 1 : 3;
        $order = D('ewei_shop_order');
        $where = array(
            'uniacid' => $this->uniacid,
            'openid' => $openid,
            'status' => $status
        );
        $user_ordercount = $order->countOrder($where);
        $user_moneycount = $order->countOrderMoney($where);
        $data = array(
            'isagent' => $member['isagent'], //是否是分销商
            'mem_status' => $member['status'],
            'status' => $set['become'], //成为分销商的条件
            'become_ordercount' => $set['become_ordercount'], //订单数量
            'become_moneycount' => $set['become_moneycount'], //订单金额
            'agentblack' => $member['agentblack'], //黑名单
            'regbg' => $regbg, //背景图
            'user_ordercount' => $user_ordercount, //用户订单数量
            'user_moneycount' => $user_moneycount, //用户订单金额
            'agent' => $set['texts']['agent'] ? $set['texts']['agent'] : '分销商',
            'commission' => $set['texts']['commission'] ? $set['texts']['commission'] : '佣金',
            'name' => $shop_set['name']//名称
        );
        $this->outFormat($data, 'ok', 0);
    }

//    注册---申请（未考虑无条件等）
    public function reg() {
//        上级id
        $mid = I('post.mid', 0, 'intval');
        $openid = I('post.openid', '', 'string');
        $member = $this->getMemberInfo($this->token, $openid);
        $set = $this->getSet($this->token, 'commission');
        $shop_set = $this->getSysset('shop', $this->token);
        $agent = false;
        $model = D('ewei_shop_member');
        $realname = I('post.realname', '', 'string'); //姓名
        $mobile = I('post.mobile', '', 'string'); //电话号码
        $rep = "/^(13[0-9]|14[0-9]|15[0-9]|17[0-9]|18[0-9])\d{8}$/i";
        
        $weixin = I('post.weixin', '', 'string');
        if(empty($realname) || empty($mobile) || empty($weixin)){
            $this->outFormat('', '参数错误', 51002);
        }
        if (empty($mobile) || !preg_match($rep, $mobile)) {
            $this->outFormat('', '请输入正确的联系电话', 51003);
        }
        if ($member['isagent'] == 1 && $member['status'] == 1) {
            $this->outFormat('', '已经是分销商了', 51004);
        }
//        判断是否有上级
        if (!empty($member['agentid'])) {
            $mid = $member['agentid'];
            $agent = $model->getItemByWhere(array('id' => $member['agentid'], 'uniacid' => $this->token));
        } else if (!empty($mid)) {
            $agent = $model->getItemByWhere(array('id' => $mid, 'uniacid' => $this->token));
        }

        $status = intval($set['become_order']) == 0 ? 1 : 3;
//      申请成为分销商
        $become_reg = intval($set['become_reg']);
//      是否需要审核0 需要
        $become_check = intval($set['become_check']);
        $data = array(
            'isagent' => 1,
            'agentid' => $mid,
            'status' => $become_check,
            'realname' => $realname,
            'mobile' => $mobile,
            'weixin' => $weixin,
            'agenttime' => $become_check == 1 ? time() : 0
        );
        $re = $model->update(array('uniacid'=>$this->token,'openid'=>$openid,'id'=>$member['id']),$data);
        if($re){
            $this->outFormat(array('status'=>1), 'ok', 0);
        }else{
            $this->outFormat('', '操作失败', 51005);
        }
    }
    
    /**
     * 分销商身份
     * type :shop小店  commission 分销
     */
    public function sys(){

        $openid = I('get.openid','','string');
        $member = $this->getMemberInfo($this->uniacid,$openid,'isagent,status,agentblack');
        $set = $this->getSet($this->uniacid, 'commission');
        if($member['status'] == 1 && $member['isagent'] == 1 && $member['agentblack']== 0){
            $isagent = 1;
        }else{
            $isagent = 0;
        }
        $data = array(
            'is_commission'    =>$set['level'] >0 ? 1 : 0,
            'is_agent'           =>$isagent
        );
        $this->outFormat($data, 'ok', 0);
    }   

}
