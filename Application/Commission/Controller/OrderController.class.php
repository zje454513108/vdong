<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Commission\Controller;

use Commission\Controller\CommissionBaseController;

class OrderController extends CommissionBaseController {

    public function index() {
        $openid = I('post.openid');
        $member = $this->getInfo($this->token, $openid, array(
            'ordercount0'
        ));
        $agentLevel = $this->getLevel($this->token, $openid);
        $set = $this->getSet($this->token, 'commission');
        $level = intval($set['level']);

        $commissioncount = 0;
        $status = I('post.status', 5, 'intval');
        $condition = ' and o.status>=0';
        if ($status != 5) {
            $condition = ' and o.status=' . $status;
        }
        $orders = array();
        $level1 = $member['level1'];
        $level2 = $member['level2'];
        $level3 = $member['level3'];
        $ordercount = $member['ordercount0'];
        
        if ($level >= 1) {
            $level1_memberids = D('EweiShopMember')->getAgentinfo($this->token, $member['id']);
            $level1_orders = M();
            $sql_o = 'select commission1,o.id,o.createtime,o.price,og.commissions from ims_ewei_shop_order_goods og left join ims_ewei_shop_order o on og.orderid=o.id where o.uniacid=' . $this->token . ' and o.agentid=' . $member['id'] . " {$condition} and og.status1>=0 and og.nocommission=0";
            $level1_orders = $level1_orders->query($sql_o);

            foreach ($level1_orders as $o) {
                if (empty($o['id'])) {
                    continue;
                }
                $commissions = unserialize($c['commissions']);
                $commission = unserialize($o['commission1']);
                if (empty($commissions)) {
                    $commission_ok = isset($commission['level' . $agentLevel['id']]) ? $commission['level' . $agentLevel['id']] : $commission['default'];
                } else {
                    $commission_ok = isset($commissions['level1']) ? floatval($commissions['level1']) : 0;
                }
                $hasorder = false;
                foreach ($orders as &$or) {
                    if ($or['id'] == $o['id'] && $or['level'] == 1) {
                        $or['commission'] += $commission_ok;
                        $hasorder = true;
                        break;
                    }
                }
                unset($or);
                if (!$hasorder) {
                    $orders[] = array(
                        'id' => $o['id'],
                        'commission' => $commission_ok,
                        'createtime' => $o['createtime'],
                        'level' => 1
                    );
                }
                $commissioncount += $commission_ok;
            }
        }
        if ($level >= 2) {
            if ($level1 > 0) {
                $level2_orders = M();
                $sql = 'select commission2 ,o.id,o.createtime,o.price,og.commissions from ims_ewei_shop_order_goods og left join ims_ewei_shop_order o on og.orderid=o.id where o.uniacid=' . $this->token . ' and o.agentid in(' . implode(',', array_values($member['level1_agentids'])) . ") {$condition} and og.status2>=0 and og.nocommission=0";
                $level2_orders = $level2_orders->query($sql);

                foreach ($level2_orders as $o) {
                    if (empty($o['id'])) {
                        continue;
                    }
                    $commissions = unserialize($c['commissions']);
                    $commission = unserialize($o['commission2']);
                    if (empty($commissions)) {
                        $commission_ok = isset($commission['level' . $agentLevel['id']]) ? $commission['level' . $agentLevel['id']] : $commission['default'];
                    } else {
                        $commission_ok = isset($commissions['level2']) ? floatval($commissions['level2']) : 0;
                    }
                    $hasorder = false;
                    foreach ($orders as &$or) {
                        if ($or['id'] == $o['id'] && $or['level'] == 2) {
                            $or['commission'] += $commission_ok;
                            $hasorder = true;
                            break;
                        }
                    }
                    unset($or);
                    if (!$hasorder) {
                        $orders[] = array(
                            'id' => $o['id'],
                            'commission' => $commission_ok,
                            'createtime' => $o['createtime'],
                            'level' => 2
                        );
                    }
                    $commissioncount += $commission_ok;
                }
            }
        }
        if ($level >= 3) {
            if ($level2 > 0) {
                $level3_orders = M();
                $sql = 'select commission3 ,o.id,o.createtime,o.price,og.commissions  from ims_ewei_shop_order_goods og left join ims_ewei_shop_order o on og.orderid=o.id where o.uniacid=' . $this->token . ' and o.agentid in(' . implode(',', array_values($member['level2_agentids'])) . ") {$condition} and og.status3>=0 and og.nocommission=0";
                $level3_orders = $level3_orders->query($sql);

                foreach ($level3_orders as $o) {
                    if (empty($o['id'])) {
                        continue;
                    }
                    $commissions = unserialize($c['commissions']);
                    $commission = unserialize($o['commission3']);
                    if (empty($commissions)) {
                        $commission_ok = isset($commission['level' . $agentLevel['id']]) ? $commission['level' . $agentLevel['id']] : $commission['default'];
                    } else {
                        $commission_ok = isset($commissions['level3']) ? floatval($commissions['level3']) : 0;
                    }
                    $hasorder = false;
                    foreach ($orders as &$or) {
                        if ($or['id'] == $o['id'] && $or['level'] == 3) {
                            $or['commission'] += $commission_ok;
                            $hasorder = true;
                            break;
                        }
                    }
                    unset($or);
                    if (!$hasorder) {
                        $orders[] = array(
                            'id' => $o['id'],
                            'commission' => $commission_ok,
                            'createtime' => $o['createtime'],
                            'level' => 3
                        );
                    }
                    $commissioncount += $commission_ok;
                }
            }
        }
        usort($orders, 'sortByCreateTime');
        $commissioncount = number_format($commissioncount, 2);

        $pindex = I('post.p',1,'intval');
        $psize = I('post.limit',10,'intval');
        $pageorders = array();
        $orders1 = array_slice($orders, ($pindex - 1) * $psize, $psize);
        $PageCount = ceil(count($orders)/$psize);
        $orderids = array();
        foreach ($orders1 as $o) {
            $orderids[$o['id']] = $o;
        }
        $list = array();
        if (!empty($orderids)) {
            $list = M();
            $sql = 'select id,ordersn,openid,createtime,status from ims_ewei_shop_order where uniacid ='.$this->token." and id in ( " . implode(',', array_keys($orderids)) . ') order by id desc';
            $list = $list->query($sql);
            
            foreach ($list as &$row) {
                $row['commission'] = number_format($orderids[$row['id']]['commission'], 2);
                $row['createtime'] = date('Y-m-d H:i', $row['createtime']);
                if ($row['status'] == 0) {
                    $row['status'] = '待付款';
                } else if ($row['status'] == 1) {
                    $row['status'] = '已付款';
                } else if ($row['status'] == 2) {
                    $row['status'] = '待收货';
                } else if ($row['status'] == 3) {
                    $row['status'] = '已完成';
                }
                if ($orderids[$row['id']]['level'] == 1) {
                    $row['level'] = '一';
                } else if ($orderids[$row['id']]['level'] == 2) {
                    $row['level'] = '二';
                } else if ($orderids[$row['id']]['level'] == 3) {
                    $row['level'] = '三';
                }
                if (!empty($set['openorderdetail'])) {

                    $goods = M();
                    $sql = 'SELECT og.id,og.goodsid,g.thumb,og.price,og.total,g.title,og.optionname,og.commission1,og.commission2,og.commission3,og.status1,og.status2,og.status3,og.content1,og.content2,og.content3 from ims_ewei_shop_order_goods og left join ims_ewei_shop_goods g on g.id=og.goodsid where og.orderid='.$row['id'].' and og.nocommission=0 and og.uniacid ='.$this->token.' order by og.createtime  desc';
                    $goods = $goods->query($sql);
                    foreach ($goods as &$g) {
                        if ($orderids[$row['id']]['level'] == 1) {
                            $commission = unserialize($g['commission1']);
                            $g['commission'] = isset($commission['level' . $agentLevel['id']]) ? $commission['level' . $agentLevel['id']] : $commission['default'];
                        } else if ($orderids[$row['id']]['level'] == 2) {
                            $commission = unserialize($g['commission2']);
                            $g['commission'] = isset($commission['level' . $agentLevel['id']]) ? $commission['level' . $agentLevel['id']] : $commission['default'];
                        } else if ($orderids[$row['id']]['level'] == 3) {
                            $commission = unserialize($g['commission3']);
                            $g['commission'] = isset($commission['level' . $agentLevel['id']]) ? $commission['level' . $agentLevel['id']] : $commission['default'];
                        }
                        $g['commission'] = number_format($g['commission'], 2);
                        $g['thumb'] = getImgUrl($g['thumb']);
                        unset($g['commission1'],$g['commission2'],$g['commission3'],$g['status1'],$g['status2'],$g['status3'],$g['content1'],$g['content2'],$g['content3']);
                    }
                    
                    unset($g);
                    $row['order_goods'] = $goods;
                }
//                购买者信息
                if (!empty($set['openorderbuyer'])) {
                    $row['buyer'] = D('EweiShopMember')->getItemByWhere(array('openid'=>$row['openid']),'nickname,avatar');
                }
            }
            unset($row);
            $alldata['list'] = $list;
            $alldata['commissioncount'] = $commissioncount;
            $this->outFormats($alldata, 'ok', $pindex, $PageCount, 0);
        }else{
            $alldata['list'] = array();
            $alldata['commissioncount'] = $commissioncount;
            $this->outFormats($alldata, '没有订单了！', $pindex, $PageCount, 0);
        }
    }

}
