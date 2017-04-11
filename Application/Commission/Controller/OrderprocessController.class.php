<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Commission\Controller;

use Commission\Controller\CommonController;

/**
 * 我的团队
 */
class OrderProcessController extends CommonController {

    public $order;

    public function __construct() {
        parent::__construct();
        $this->order = D('Commission/ewei_shop_order');
    }

    public function complete($orderid = '', $token = '') {
        if (empty($orderid) || empty($token)) {
            return;
        }
//            查询订单
        $order = $this->order->getItemByWhere(array('id' => $orderid, 'uniacid' => $token, 'status' => array('egt', 3)), 'id,openid,ordersn,goodsprice,agentid,finishtime');
//            不存在返回
        if (empty($order)) {
            return;
        }
//            查询设置
        $set = $this->getSet($token, 'commission');
        if (empty($set['level'])) {
            return;
        }
//            分销未开启直接返回

        $openid = $order['openid'];
        $member = D('Commission/ewei_shop_member')->getInfo($token, $openid);
        if (empty($member)) {
            return;
        }
        $time = time();
//      判断是否是分销商
        $isagent = $member['isagent'] == 1 && $member['status'] == 1;

//      不是分销商、成为分销商的前提(订单完成后）
        if (!$isagent && $set['become_order'] == 1) {
//            2消费达到次数 3消费达到金额
            if ($set['become'] == 2 || $set['become'] == 3) {
                $parentisagent = true;
                if (!empty($member['agentid'])) {
                    $parent = D('Commission/ewei_shop_member')->getItemByWhere(array('id' => $member['agentid'], 'uniacid' => $token));
                    if (empty($parent) || $parent['isagent'] != 1 || $parent['status'] != 1) {
                        $parentisagent = false;
                    }
                }
                if ($parentisagent) {
                    $can = false;
                    if ($set['become'] == '2') {

                        $ordercount = $this->order->countOrder(array('uniacid' => $token, 'openid' => $openid, 'status' => array('egt', 3)));
                        $can = $ordercount >= intval($set['become_ordercount']);
                    } else if ($set['become'] == '3') {

                        $moneycount = $this->order->countOrderMoney(array('uniacid' => $token, 'openid' => $openid, 'status' => array('egt', 3)));
                        $can = $moneycount >= floatval($set['become_moneycount']);
                    }
                    if ($can) {
//                        是否是黑名单
                        if (empty($member['agentblack'])) {
//                            是否需要审核
                            $become_check = intval($set['become_check']);
                            D('Commission/ewei_shop_member')
                                    ->update(array(
                                        'uniacid' => $token,
                                        'id' => $member['id']), array('status' => $become_check,
                                        'isagent' => 1,
                                        'agenttime' => $time
                            ));
                        }
                    }
                }
            }
        }
//            是分销商---发送消息
        /* if (!empty($member['agentid'])) {
          $parent = D('Commission/ewei_shop_member')->getItemByWhere(array('id'=>$member['agentid'],'uniacid'=>$token));
          //                上级是分销商
          if (!empty($parent) && $parent['isagent'] == 1 && $parent['status'] == 1) {
          //                    订单分销商id是分销商id
          if ($order['agentid'] == $parent['id']) {
          $order_goods = D('Commission/ewei_shop_order_goods')
          ->getOrderGoods($token,$order['id']);
          //                        查询所有订单商品
          $goods = '';
          $level = $parent['agentlevel'];
          p($parent['agentlevel']);
          die;
          $commission_total = 0;
          $pricetotal = 0;
          foreach ($order_goods as $og) {
          $goods .= "" . $og['title'] . '( ';
          if (!empty($og['optiontitle'])) {
          $goods .= " 规格: " . $og['optiontitle'];
          }
          $goods .= ' 单价: ' . ($og['realprice'] / $og['total']) . ' 数量: ' . $og['total'] . ' 总价: ' . $og['realprice'] . "); ";
          $commission = unserialize($og['commission1']);
          $commission_total += isset($commission['level' . $level]) ? $commission['level' . $level] : $commission['default'];
          $pricetotal += $og['realprice'];
          }
          $this->sendMessage($parent['openid'], array(
          'nickname' => $member['nickname'],
          'ordersn' => $order['ordersn'],
          'price' => $pricetotal,
          'goods' => $goods,
          'commission' => $commission_total,
          'finishtime' => $order['finishtime']
          ), TM_COMMISSION_ORDER_FINISH);
          }
          }
          } */
        $this->upgradeLevel($token, $openid);
    }

    /**
     * 更新分销商等级(确认收货后）
     * @param type $token
     * @param type $openid
     * @return type
     */
    public function upgradeLevel($token, $openid) {
        if (empty($openid) || empty($token)) {
            return;
        }
        $set = $this->getSet($token, 'commission');

        if (empty($set['level'])) {
            return;
        }
        $m = D('Commission/ewei_shop_member')->getInfo($token, $openid);

        $agents = array();
        if (!empty($set['selfbuy'])) {
            $agents[] = $m;
        }
        if (!empty($m)) {
            if (!empty($m['agentid'])) {
                $m1 = D('Commission/ewei_shop_member')->getItemByWhere(array('id' => $m['agentid'], 'uniacid' => $token));
                if (!empty($m1)) {
                    $agents[] = $m1;
//                    获取二级
                    if (!empty($m1['agentid']) && $m1['isagent'] == 1 && $m1['status'] == 1) {
                        $m2 = D('Commission/ewei_shop_member')->getItemByWhere(array('id' => $m1['agentid'], 'uniacid' => $token));
                        if (!empty($m2) && $m2['isagent'] == 1 && $m2['status'] == 1) {
                            $agents[] = $m2;
//                            获取三级
                            if (empty($set['selfbuy'])) {
                                if (!empty($m2['agentid']) && $m2['isagent'] == 1 && $m2['status'] == 1) {
                                    $m3 = D('Commission/ewei_shop_member')->getItemByWhere(array('id' => $m1['agentid'], 'uniacid' => $token));
                                    if (!empty($m3) && $m3['isagent'] == 1 && $m3['status'] == 1) {
                                        $agents[] = $m3;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        foreach ($agents as $agent) {
            $info = $this->getInfo($token, $agent['openid'], array(
                'ordercount3'
            ));
            if (!empty($info['agentnotupgrade'])) {
                continue;
            }
            $ordermoney = $info['ordermoney3'];
            $newlevel = M();
            $sql = 'select * from ims_ewei_shop_commission_level where uniacid=' . $token . ' and ' . $ordermoney . ' >= ordermoney and ordermoney>0  order by ordermoney desc limit 1';
            $newlevel = $newlevel->query($sql);

            if ($newlevel) {
                $newlevel = $newlevel[0];
            }

            if (!empty($newlevel) && $newlevel['id'] != $agent['agentlevel']) {
//                $oldlevel = $this->getLevel($token,$agent['openid']);
//                if (empty($oldlevel['id'])) {
//                    $oldlevel = array(
//                        'levelname' => empty($set['levelname']) ? '普通等级' : $set['levelname'],
//                        'commission1' => $set['commission1'],
//                        'commission2' => $set['commission2'],
//                        'commission3' => $set['commission3']
//                    );
//                }
                D('Commission/ewei_shop_member')->update(array('id' => $agent['id']), array('agentlevel' => $newlevel['id']));
//                发送消息暂未添加
//                $this->sendMessage($agent['openid'], array(
//                    'nickname' => $agent['nickname'],
//                    'oldlevel' => $oldlevel,
//                    'newlevel' => $newlevel
//                        ), TM_COMMISSION_UPGRADE);
            }
        }
    }

    /**
     * 支付完成---对分销商状态的操作
     * @param type $orderid
     * @return type
     */
    public function checkOrderPay($orderid = '0', $token = '') {
        if (empty($orderid) || empty($token)) {
            return;
        }
        $set = $this->getSet($token, 'commission');
        if (empty($set['level'])) {
            return;
        }

        $order = $this->order->getItemByWhere(array('id' => $orderid, 'status' => array('egt', 1), 'uniacid' => $token), 'id,openid,ordersn,goodsprice,agentid,paytime');
        if (empty($order)) {
            return;
        }

        $openid = $order['openid'];
        $member = D('Commission/ewei_shop_member')->getInfo($token, $openid);

        if (empty($member)) {
            return;
        }

//            成为下线0成为下线条件 	首次点击分享连接0 首次下单1 首次付款2
        $become_child = intval($set['become_child']);

        $parent = false;
        if (empty($become_child)) {
            $parent = D('Commission/ewei_shop_member')->getItemByWhere(array('id' => $member['agentid'], 'uniacid' => $token));
        } else {
//                邀请人
            $parent = D('Commission/ewei_shop_member')->getItemByWhere(array('id' => $member['inviter'], 'uniacid' => $token));
        }
        $parent_is_agent = !empty($parent) && $parent['isagent'] == 1 && $parent['status'] == 1;
        $time = time();

        if ($parent_is_agent) {
//                首次付款成为下线
            if ($become_child == 2) {
                if (empty($member['agentid']) && $member['id'] != $parent['id']) {
                    $member['agentid'] = $parent['id'];
//                        保存上线id
                    D('Commission/ewei_shop_member')->update(array('uniacid' => $token, 'id' => $member['id']), array('agentid' => $parent['id'], 'childtime' => $time));

                    if (empty($order['agentid'])) {
                        $order['agentid'] = $parent['id'];
//                            将订单的agentid更新
                        $this->order->update(array('id' => $orderid), array('agentid' => $parent['id']));
                    }
                }
            }
        }
        $isagent = $member['isagent'] == 1 && $member['status'] == 1;
//            不是分销商&&订单付款后
        if (!$isagent && empty($set['become_order'])) {
//                2消费达到次数 3消费达到金额
            if ($set['become'] == 2 || $set['become'] == 3) {
                $parentisagent = true;
                if (!empty($member['agentid'])) {

                    $parent = D('Commission/ewei_shop_member')->getItemByWhere(array('id' => $member['agentid'], 'uniacid' => $token));

                    if (empty($parent) || $parent['isagent'] != 1 || $parent['status'] != 1) {
                        $parentisagent = false;
                    }
                }
//                    上级是分销商
                $set['become'] = 3;
                if ($parentisagent) {
                    $can = false;
//                        判断次数
                    $orderWhere = array(
                        'uniacid' => $token,
                        'openid' => $openid,
                        'status' => array('egt', 1)
                    );
                    if ($set['become'] == '2') {

                        $ordercount = $this->order->countOrder($orderWhere);
                        $can = $ordercount >= intval($set['become_ordercount']);
                    } else if ($set['become'] == '3') {
//                            判断金额----前后不统一                            
                        $moneycount = $this->order->countOrderMoney($orderWhere);
                        $can = $moneycount >= floatval($set['become_moneycount']);
                    }
                    if ($can) {
//                            黑名单
                        if (empty($member['agentblack'])) {
                            $become_check = intval($set['become_check']);
//                                提交分销商申请/成为分销商
                            D('Commission/ewei_shop_member')->update(array('uniacid' => $token, 'id' => $member['id']), array('status' => $become_check, 'isagent' => 1, 'agenttime' => $time));
                        }
                    }
                }
            }
        }
//            发送消息
//            if (!empty($member['agentid'])) {
//                $parent = m('member')->getMember($member['agentid']);
//                if (!empty($parent) && $parent['isagent'] == 1 && $parent['status'] == 1) {
//                    if ($order['agentid'] == $parent['id']) {
//                        $order_goods      = pdo_fetchall('select g.id,g.title,og.total,og.price,og.realprice, og.optionname as optiontitle,g.noticeopenid,g.noticetype,og.commission1 from ' . tablename('ewei_shop_order_goods') . ' og ' . ' left join ' . tablename('ewei_shop_goods') . ' g on g.id=og.goodsid ' . ' where og.uniacid=:uniacid and og.orderid=:orderid ', array(
//                            ':uniacid' => $_W['uniacid'],
//                            ':orderid' => $order['id']
//                        ));
//                        $goods            = '';
//                        $level            = $parent['agentlevel'];
//                        $commission_total = 0;
//                        $pricetotal       = 0;
//                        foreach ($order_goods as $og) {
//                            $goods .= "" . $og['title'] . '( ';
//                            if (!empty($og['optiontitle'])) {
//                                $goods .= " 规格: " . $og['optiontitle'];
//                            }
//                            $goods .= ' 单价: ' . ($og['realprice'] / $og['total']) . ' 数量: ' . $og['total'] . ' 总价: ' . $og['realprice'] . "); ";
//                            $commission = iunserializer($og['commission1']);
//                            $commission_total += isset($commission['level' . $level]) ? $commission['level' . $level] : $commission['default'];
//                            $pricetotal += $og['realprice'];
//                        }
//                        $this->sendMessage($parent['openid'], array(
//                            'nickname' => $member['nickname'],
//                            'ordersn' => $order['ordersn'],
//                            'price' => $pricetotal,
//                            'goods' => $goods,
//                            'commission' => $commission_total,
//                            'paytime' => $order['paytime']
//                        ), TM_COMMISSION_ORDER_PAY);
//                    }
//                }
//            }
    }

}
