<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Commission\Controller;

use Commission\Controller\CommonController;

/**
 * 分销订单下单操作等
 */
class CommissionController extends CommonController {

    protected $shopOrder;
    protected $shopMember;
    protected $ShopCommissionLevel;
    protected $ShopOrderGoods;

    public function __construct() {
        parent::__construct();
        $this->shopOrder = D('Commission/ewei_shop_order');
        $this->shopMember= D('Commission/ewei_shop_member');
        $this->ShopCommissionLevel= D('Commission/ewei_shop_commission_level');
        $this->ShopOrderGoods= D('Commission/ewei_shop_order_goods');
    }
     public function index(){
        $orderid = 1003;
        $this->checkOrderConfirm($orderid);  
     }
    /**
     * check分销订单
     */
    public function checkOrderConfirm($orderid = '0'){

            if (empty($orderid)) {
                return;
            }
            $set = $this->getSet($this->uniacid,'commission');

            if (empty($set['level'])) {
                return;
            }
            $field = 'id,openid,ordersn,goodsprice,agentid,paytime';
            $where['id'] = $orderid;
            $where['uniacid'] = $this->uniacid;
            $where['status']  = array('EGT',0);
            $order =  $this->shopOrder->getItemByWhere($where,$field);

            if (empty($order)) {
                return;
            }
            $openid = $order['openid'];
            $member = $this->shopMember->getMember($this->uniacid,$openid,'id,agentid,inviter,isagent,status'); 

            if (empty($member)) {
                return;
            }
            $become_child = intval($set['become_child']);
            $parent  = false;
            if (empty($become_child)) {
                $parent = $this->shopMember->getMember($this->uniacid,$member['agentid'],'id,isagent,status');
            } else {
                $parent = $this->shopMember->getMember($this->uniacid,$member['inviter'],'id,isagent,status');
            }
            $parent_is_agent = !empty($parent) && $parent['isagent'] == 1 && $parent['status'] == 1;
            $time = time();
            $become_child = intval($set['become_child']);
            if ($parent_is_agent) {
                if ($become_child == 1) {
                    if (empty($member['agentid']) && $member['id'] != $parent['id']) {
                        $member['agentid'] = $parent['id'];
                        $where['uniacid'] = $this->uniacid;
                        $where['id'] = $member['id'];
                        $data['agentid'] = $parent['id'];
                        $data['childtime'] = $time;
                        $updateRes =  $this->shopMember->update($where,$data);
                    }
                }
            }
            $agentid = $member['agentid'];
            if ($member['isagent'] == 1 && $member['status'] == 1) {
                if (!empty($set['selfbuy'])) {
                    $agentid = $member['id'];
                }
            }
            if (!empty($agentid)) {
                $where['id'] = $orderid;
                $data['agentid'] = $agentid;
                $this->shopOrder->update($where,$data);
            }
            $this->calculate($orderid);
    }
    public function calculate($orderid = 0, $update = true){
        $set = $this->getSet($this->uniacid,'commission');
        $levels  = $this->getLevels();
        $agentid_where['id'] = $orderid;
        $agentid_field = 'agentid';
        $agentid = $this->shopOrder->getItemByWhere($agentid_where,$agentid_field);
         
        $CommissionModel = D('Commission');
        $goods = $CommissionModel->selectGoods($this->uniacid,$orderid);
    
        if ($set['level'] > 0) {
            foreach ($goods as &$cinfo) {
                $price = $cinfo['realprice'];

                if (empty($cinfo['nocommission'])) {
                    if ($cinfo['hascommission'] == 1) {
                        $cinfo['commission1'] = array(
                            'default' => $set['level'] >= 1 ? ($cinfo['commission1_rate'] > 0 ? round($cinfo['commission1_rate'] * $price / 100, 2) . "" : round($cinfo['commission1_pay'] * $cinfo['total'], 2)) : 0
                        );
                        $cinfo['commission2'] = array(
                            'default' => $set['level'] >= 2 ? ($cinfo['commission2_rate'] > 0 ? round($cinfo['commission2_rate'] * $price / 100, 2) . "" : round($cinfo['commission2_pay'] * $cinfo['total'], 2)) : 0
                        );
                        $cinfo['commission3'] = array(
                            'default' => $set['level'] >= 3 ? ($cinfo['commission3_rate'] > 0 ? round($cinfo['commission3_rate'] * $price / 100, 2) . "" : round($cinfo['commission3_pay'] * $cinfo['total'], 2)) : 0
                        );
                    
                    } else {
                        $cinfo['commission1'] = array(
                            'default' => $set['level'] >= 1 ? round($set['commission1'] * $price / 100, 2) . "" : 0
                        );
                        $cinfo['commission2'] = array(
                            'default' => $set['level'] >= 2 ? round($set['commission2'] * $price / 100, 2) . "" : 0
                        );
                        $cinfo['commission3'] = array(
                            'default' => $set['level'] >= 3 ? round($set['commission3'] * $price / 100, 2) . "" : 0
                        );
                        foreach ($levels as $level) {
                            $cinfo['commission1']['level' . $level['id']] = $set['level'] >= 1 ? round($level['commission1'] * $price / 100, 2) . "" : 0;
                            $cinfo['commission2']['level' . $level['id']] = $set['level'] >= 2 ? round($level['commission2'] * $price / 100, 2) . "" : 0;
                            $cinfo['commission3']['level' . $level['id']] = $set['level'] >= 3 ? round($level['commission3'] * $price / 100, 2) . "" : 0;
                        }
                    }
                 
                } else {
                    $cinfo['commission1'] = array(
                        'default' => 0
                    );
                    $cinfo['commission2'] = array(
                        'default' => 0
                    );
                    $cinfo['commission3'] = array(
                        'default' => 0
                    );
                    foreach ($levels as $level) {
                        $cinfo['commission1']['level' . $level['id']] = 0;
                        $cinfo['commission2']['level' . $level['id']] = 0;
                        $cinfo['commission3']['level' . $level['id']] = 0;
                    }
                }
                if ($update) {
                    $commissions = array(
                        'level1' => 0,
                        'level2' => 0,
                        'level3' => 0
                    );
                    /*写到这了*/
                    if (!empty($agentid)) {
                        
                        $m1 = $this->shopMember->getMember($this->uniacid,$agentid['agentid'],'id,openid,isagent,status,agentid');
                       
                        if ($m1['isagent'] == 1 && $m1['status'] == 1) {
                            $l1 = $this->getLevel($m1['openid']);
                            
                            $commissions['level1'] = empty($l1) ? round($cinfo['commission1']['default'], 2) : round($cinfo['commission1']['level' . $l1['id']], 2);
                            if (!empty($m1['agentid'])) {
                                $m2                    = m('member')->getMember($m1['agentid']);
                                $l2                    = $this->getLevel($m2['openid']);
                                $commissions['level2'] = empty($l2) ? round($cinfo['commission2']['default'], 2) : round($cinfo['commission2']['level' . $l2['id']], 2);
                                if (!empty($m2['agentid'])) {
                                    $m3                    = m('member')->getMember($m2['agentid']);
                                    $l3                    = $this->getLevel($m3['openid']);
                                    $commissions['level3'] = empty($l1) ? round($cinfo['commission3']['default'], 2) : round($cinfo['commission3']['level' . $l3['id']], 2);
                                }
                            }
                        }
                    }
                    $orderGood_where['id'] = $cinfo['id'];
                    $orderGood_data['commission1'] = iserializer($cinfo['commission1']);
                    $orderGood_data['commission2'] = iserializer($cinfo['commission2']);
                    $orderGood_data['commission3'] = iserializer($cinfo['commission3']);
                    $orderGood_data['commissions'] = iserializer($commissions);
                    $orderGood_data['nocommission'] = $cinfo['nocommission'];
                    $orderGood = $this->ShopOrderGoods->update($orderGood_where,$orderGood_data);
                }
            }
            unset($cinfo);
        }
        return $goods;
    }
    public function getLevels($all = true)
    {
        if ($all) {
            $where['uniacid'] = $this->uniacid;
            $order = 'commission1 asc';
            return $this->ShopCommissionLevel->getListByWhere($where,'',$order);
        } else {
            $where = "uniacid=".$this->uniacid." and (ordermoney>0 or commissionmoney>0)";
            $order = 'commission1 asc';
            return $this->ShopCommissionLevel->getListByWhere($where,'',$order);
        }
    }
    public function getLevel($openid){
        if (empty($openid)) {
            return false;
        }

        $member = $this->shopMember->getMember($this->uniacid,$openid,'level');
        if (empty($member['level'])) {
            return array(
                'discount' => 10
            );
        }
        $where['uniacid'] = $this->uniacid;
        $where['id'] = $member['level'];
        $order = 'level asc';
        $level = $this->ShopCommissionLevel->getItemByWhere($where,'',$order);
        if (empty($level)) {
            return array(
                'discount' => 10
            );
        }
        return $level;
    }
    
    /**
     * 确认收货用户等级提升
     * @param type $openid
     * @return type
     */
    public function upgradeLevel($token,$openid)
    {
        if (empty($openid) || empty($token)) {
            return;
        }
        $member = $this->shopMember->getInfo($token,$openid);

        if (!empty($member)) {
            $ordercount = $this->shopOrder->countOrder(array('uniacid' => $token,'openid' => $member['openid'],'status'=>3));

            $ordermoney = $this->shopOrder->countOrderMoney(array('openid'=>$member['openid'],'status'=>3,'uniacid'=>$token));

            $level = M();
            $sql = 'select * from ims_ewei_shop_member_level where uniacid='.$token . ' and  ( ('.$ordercount.' >= ordercount and ordercount>0) or  ('.$ordermoney.' >= ordermoney and ordermoney>0) ) order by level desc limit 1';
            $level = $level->query($sql);
            if($level){
                $level = $level[0];
            }      
            if (!empty($level) && $level['id'] != $member['level']) {
//                更新用户等级
                $this->shopMember->update(array('id' => $member['id']),array('level' => $level['id']));
            }
        }
    }
}
