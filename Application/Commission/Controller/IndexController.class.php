<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Commission\Controller;

//use Commission\Controller\CommonController;
use Commission\Controller\CommissionBaseController;

/**
 * 我的下线
 */
class IndexController extends CommissionBaseController {

    public function index() {
        $openid = I('get.openid', '', 'string');
//        获取用户信息
        $member = $this->getInfo($this->uniacid, $openid, array(
            'total',
            'ordercount0',
            'ok'
        ));
//        设置信息
        $set = $this->getSet($this->uniacid, 'commission');

        $cansettle = $member['commission_ok'] > 0 && $member['commission_ok'] >= floatval($set['withdraw']);
        $cansettle = $cansettle ? 1 : 0;
        $commission_ok = $member['commission_ok'];
        $member['agentcount'] = number_format($member['agentcount'], 0);
        $member['ordercount0'] = number_format($member['ordercount0'], 0);
        $member['commission_ok'] = number_format($member['commission_ok'], 2);
        $member['commission_pay'] = number_format($member['commission_pay'], 2);
        $member['commission_total'] = number_format($member['commission_total'], 2);
//        获取下线总数

        $member['customercount'] = $this->shopMember->getCustomerCount($this->uniacid, $member['id']);
        if (mb_strlen($member['nickname'], 'utf-8') > 6) {
            $member['nickname'] = mb_substr($member['nickname'], 0, 6, 'utf-8');
        }
//        是否开启自选商品
        $openselect = 0;//不开启自选商品
        $closemyshop = 1;//不开启小店
        if ($set['closemyshop'] == 0) {
            $closemyshop = 0;
            if ($set['select_goods'] == '1') {
                if (empty($member['agentselectgoods']) || $member['agentselectgoods'] == 2) {
                    $openselect = 1;
                }
            } else {
                if ($member['agentselectgoods'] == 2) {
                    $openselect = 1;
                }
            }
        }

//        分销商等级有问题
//        $member['agentlevel'] = 32;
        if($member['agentlevel'] == 0){
            $level = $set['levelname'];
        }else{
            $where = array(
                'uniacid'   =>$this->uniacid,
                'id'        =>$member['agentlevel']
            );
            $re = D('ewei_shop_commission_level')->getItemByWhere($where,'levelname');
            $level = $re['levelname'];
        }

        $data = array(
            'commission_ok' =>$commission_ok,//可提现佣金
            'level'         => $level,
            'cansettle'     => $cansettle,//可否点击提现---点击后要判断
            'openselect'    =>$openselect,
            'closemyshop'   =>$closemyshop,
            'avatar'        =>$member['avatar'],
            'nickname'      =>$member['nickname'],
            'agenttime'     =>$member['agenttime'],
            'commission_total'  =>$member['commission_total'],
            'ordercount0'       =>$member['ordercount0'],//分销订单
            'agentcount'        =>$member['agentcount'],//我的团队人数
            'customercount'     =>$member['customercount'],//我的下线
            'settlemoney'   => number_format(floatval($set['withdraw']), 2),//提现额度
            
        );
        $this->outFormat($data, 'ok', 0);
    }
    
}
