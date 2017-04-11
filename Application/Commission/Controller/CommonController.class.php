<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Commission\Controller;

use Think\Controller\RestController;

/**
 * 分销公共控制器
 */
class CommonController extends RestController {
asdf
    protected $uniacid;
    protected $token;

    public function __construct() {
        parent::__construct();
//        所有get访问获取商户id
        $this->uniacid = I('get.token', 0, 'intval');
//        所有post访问获取商户id
        $this->token = I('post.token', 0, 'intval');
    }

    /**
     * 获取用户基本数据，检测是否存在
     * @param type $token   商户id
     * @param type $openid  用户openid
     *  * @param type $field  用户字段
     * @return boolean  
     */
    public function getMemberInfo($token, $openid, $field) {
        if (empty($openid) || empty($token)) {
            $this->outFormat('', '参数错误', 21001);
        }
        $user = M('ewei_shop_member')
                ->field($field)
                ->where("uniacid=%d and openid='%s'", array($token, $openid))
                ->find();
        if ($user) {
            return $user;
        } else {
            $this->outFormat('', '用户不存在', 21002);
        }
    }

    /**
     * 格式化输出
     * @param type $data 数据信息
     * @param type $msg 提示信息
     * @param type $code 状态码
     * @param type $type 输出数据类型
     * @return type
     */
    public function outFormat($data, $msg = 'ok', $code = 0, $type = 'json') {
        $result = array('Response' => $data, 'result' => $msg, 'code' => $code);
        $this->response($result, $type);
    }

    /**
     * 格式化输出添加分页数据
     * @param type $data
     * @param type $msg
     * @param type $page
     * @param type $count
     * @param type $code
     * @param type $type
     */
    public function outFormats($data, $msg = 'ok', $page = 1, $count = 0, $code = 0, $type = 'json') {
        $result = array('Response' => array('Datalist' => $data, 'Pagecount' => $count, 'page' => $page), 'result' => $msg, 'code' => $code);
        $this->response($result, $type);
    }

    /**
     * 获取商城栏目信息
     * @param type $field 要显示的字段
     * @param type $token 商户id
     * @return type
     */
    public function getCategory($token, $field = '') {
        $shopset = $this->getSysset('shop', $token);

        $allcategory = array();
        if (empty($field)) {
            $field = 'id,name,thumb,parentid,isrecommand,description,displayorder,ishome,advimg,advurl,level';
        } else {
//            这三个字段必须显示
            $field .= ',parentid,thumb,advimg';
        }
        $category = M('ewei_shop_category')
                ->field($field)
                ->where('uniacid=%d and enabled=1', array($token))
                ->order('parentid ASC, displayorder DESC')
                ->select();
        foreach ($category as &$value) {
            $value['thumb'] = getImgUrl($value['thumb']);
            $value['advimg'] = getImgUrl($value['advimg']);
        }
//        对图片进行操作
        foreach ($category as $c) {
            if (empty($c['parentid'])) {
                $children = array();
                foreach ($category as $c1) {
                    if ($c1['parentid'] == $c['id']) {
                        if (intval($shopset['catlevel']) == 3) {
                            $children2 = array();
                            foreach ($category as $c2) {
                                if ($c2['parentid'] == $c1['id']) {
                                    $children2[] = $c2;
                                }
                            }
                            $c1['children'] = $children2;
                        }
                        $children[] = $c1;
                    }
                }
                $c['children'] = $children;
                $allcategory[] = $c;
            }
        }
        return $allcategory;
    }

    /**
     * 获取设置数据
     * @param type $token
     * @return array
     */
    public function getSetData($token) {

        $set = M('ewei_shop_sysset')
                ->where('uniacid=%d', $token)
                ->find();
        if (empty($set)) {
            $set = array();
        }
        return $set;
    }

    /**
     * 获取设置
     * @param type $token
     * @param type $pluginname 插件名称
     * @return type
     */
    public function getSet($token, $pluginname = '') {
        $set = $this->getSetData($token);
        $allset = unserialize($set['plugins']);
        if (is_array($allset) && isset($allset[$pluginname])) {
            return $allset[$pluginname];
        }
        return array();
    }

    /**
     * 获取对应的后台设置
     * @param type $key 显示字段
     * @param type $uniacid
     * @return type
     */
    public function getSysset($key = '', $token) {
        $set = $this->getSetData($token);
        $allset = unserialize($set['sets']);
        $retsets = array();
        if (!empty($key)) {
            if (is_array($key)) {
                foreach ($key as $k) {
                    $retsets[$k] = isset($allset[$k]) ? $allset[$k] : array();
                }
            } else {
                $retsets = isset($allset[$key]) ? $allset[$key] : array();
            }
            return $retsets;
        } else {
            return $allset;
        }
    }

    /**
     * 判断用户是否存在
     * @param type $openid
     * @return boolean
     */
    protected function checkMember($token, $openid) {
        if (empty($openid) || empty($token)) {
            $this->outFormat('', '参数错误', 21003);
        }
        $user = M('ewei_shop_member')
                ->field('id')
                ->where("uniacid=%d and openid='%s'", array($token, $openid))
                ->find();
        if ($user) {
            return true;
        } else {
            $this->outFormat('', '用户不存在', 21004);
        }
    }

    /**
     * 小店信息
     * @param type $token
     * @param type $mid
     * @return type
     */
    protected function shopinfo($token, $mid) {
        $info = $this->myshop
                ->field('id,name,logo,img,desc,selectgoods,selectcategory,goodsids')
                ->where("uniacid=%d and mid=%d", array($token, $mid))
                ->find();
        return $info;
    }

    /**
     * @param type $token 商户
     * @param type $openid 用户openid
     * @param array $options 获取条件
     * @return type
     */
    public function getInfo($token, $openid, $options = null) {
        if (empty($options) || !is_array($options)) {
            $options = array();
        }
//        获取用户设置
        $set = $this->getSet($token, 'commission');
//        分销层级
        $level = intval($set['level']);
//        获得用户信息
        $member = D('Commission/ewei_shop_member')->getInfo($token, $openid);
//        获得分销等级

        if ($member['agentlevel']) {
            $agentLevel = D('Commission/ewei_shop_member_level')->findList(array('uniacid' => $token, 'id' => $member['agentlevel']));
            $agentLevel = $agentLevel[0];
        } else {
            $agentLevel = false;
        }

        $time = time();
//        结算天数，当订单完成后的n天后，佣金才能申请提现
        $day_times = intval($set['settledays']) * 3600 * 24;

        $agentcount = 0;
        $ordercount0 = 0;
        $ordermoney0 = 0;
        $ordercount = 0;
        $ordermoney = 0;
        $ordercount3 = 0;
        $ordermoney3 = 0;
        $commission_total = 0;
        $commission_ok = 0;
        $commission_apply = 0;
        $commission_check = 0;
        $commission_lock = 0;
        $commission_pay = 0;
        $level1 = 0;
        $level2 = 0;
        $level3 = 0;
        $order10 = 0;
        $order20 = 0;
        $order30 = 0;
        $order1 = 0;
        $order2 = 0;
        $order3 = 0;
        $order13 = 0;
        $order23 = 0;
        $order33 = 0;

//        分销等级操作
        if ($level >= 1) {
            if (in_array('ordercount0', $options)) {
//                $level1_ordercount = pdo_fetch('select sum(og.realprice) as ordermoney,count(distinct o.id) as ordercount from ' . tablename('ewei_shop_order') . ' o ' . ' left join  ' . tablename('ewei_shop_order_goods') . ' og on og.orderid=o.id ' . ' where o.agentid=:agentid and o.status>=0 and og.status1>=0 and og.nocommission=0 and o.uniacid=:uniacid  limit 1', array(
//                    ':uniacid' => $_W['uniacid'],
//                    ':agentid' => $member['id']
//                ));
//                一级订单金额与数量
                $sql = "select sum(og.realprice) as ordermoney,count(distinct o.id) as ordercount from ims_ewei_shop_order o left join  ims_ewei_shop_order_goods og on og.orderid=o.id  where o.agentid= $member[id] and o.status>=0 and og.status1>=0 and og.nocommission=0 and o.uniacid= $token  limit 1";
                $level1_ordercount = M();
                $level1_ordercount = $level1_ordercount->query($sql);
                if ($level1_ordercount) {
                    $level1_ordercount = $level1_ordercount[0];
                }

                $order10 += $level1_ordercount['ordercount'];
                $ordercount0 += $level1_ordercount['ordercount'];
                $ordermoney0 += $level1_ordercount['ordermoney'];
            }
//            不清楚是查询什么的
            if (in_array('ordercount', $options)) {
//                $level1_ordercount = pdo_fetch('select sum(og.realprice) as ordermoney,count(distinct o.id) as ordercount from ' . tablename('ewei_shop_order') . ' o ' . ' left join  ' . tablename('ewei_shop_order_goods') . ' og on og.orderid=o.id ' . ' where o.agentid=:agentid and o.status>=1 and og.status1>=0 and og.nocommission=0 and o.uniacid=:uniacid  limit 1', array(
//                    ':uniacid' => $_W['uniacid'],
//                    ':agentid' => $member['id']
//                ));
                $level1_ordercount = M();
                $sql = 'select sum(og.realprice) as ordermoney,count(distinct o.id) as ordercount from ims_ewei_shop_order o left join ims_ewei_shop_order_goods og on og.orderid=o.id ' . ' where o.agentid=' . $member['id'] . ' and o.status>=1 and og.status1>=0 and og.nocommission=0 and o.uniacid=' . $token . '  limit 1';
                $level1_ordercount = $level1_ordercount->query($sql);
                if ($level1_ordercount) {
                    $level1_ordercount = $level1_ordercount[0];
                }
                $order1 += $level1_ordercount['ordercount'];
                $ordercount += $level1_ordercount['ordercount'];
                $ordermoney += $level1_ordercount['ordermoney'];
            }
            if (in_array('ordercount3', $options)) {

//                $level1_ordercount3 = pdo_fetch('select sum(og.realprice) as ordermoney,count(distinct o.id) as ordercount from ' . tablename('ewei_shop_order') . ' o ' . ' left join  ' . tablename('ewei_shop_order_goods') . ' og on og.orderid=o.id ' . ' where o.agentid=:agentid and o.status>=3 and og.status1>=0 and og.nocommission=0 and o.uniacid=:uniacid  limit 1', array(
//                    ':uniacid' => $_W['uniacid'],
//                    ':agentid' => $member['id']
//                ));
                $level1_ordercount3 = M();
                $sql = 'select sum(og.realprice) as ordermoney,count(distinct o.id) as ordercount from ims_ewei_shop_order o ' . ' left join ims_ewei_shop_order_goods og on og.orderid=o.id ' . ' where o.agentid=' . $member['id'] . ' and o.status>=3 and og.status1>=0 and og.nocommission=0 and o.uniacid=' . $token . '  limit 1';
                $level1_ordercount3 = $level1_ordercount3->query($sql);
                if ($level1_ordercount3) {
                    $level1_ordercount3 = $level1_ordercount3[0];
                }

                $order13 += $level1_ordercount3['ordercount'];
                $ordercount3 += $level1_ordercount3['ordercount'];
                $ordermoney3 += $level1_ordercount3['ordermoney'];
            }
//            计算一级的金额
            if (in_array('total', $options)) {
//                $level1_commissions = pdo_fetchall('select og.commission1,og.commissions  from ' . tablename('ewei_shop_order_goods') . ' og ' . ' left join  ' . tablename('ewei_shop_order') . ' o on o.id = og.orderid' . " where o.agentid=:agentid and o.status>=1 and og.nocommission=0 and o.uniacid=:uniacid", array(
//                    ':uniacid' => $_W['uniacid'],
//                    ':agentid' => $member['id']
//                ));
                $level1_commissions = D('Commission/ewei_shop_order')->getOrdergoods($token, $member['id']);

                foreach ($level1_commissions as $c) {
                    $commissions = unserialize($c['commissions']); //佣金
                    $commission = unserialize($c['commission1']); //一级
//                    存在分销就取commissions,不存在就取一级
                    if (empty($commissions)) {
                        $commission_total += isset($commission['level' . $agentLevel['id']]) ? $commission['level' . $agentLevel['id']] : $commission['default'];
                        ;
                    } else {
                        $commission_total += isset($commissions['level1']) ? floatval($commissions['level1']) : 0;
                    }
                }
            }
            if (in_array('ok', $options)) {
//                查出已完成的订单佣金
//                $level1_commissions = pdo_fetchall('select og.commission1,og.commissions  from ' . tablename('ewei_shop_order_goods') . ' og ' . ' left join  ' . tablename('ewei_shop_order') . ' o on o.id = og.orderid' . " where o.agentid=:agentid and o.status>=3 and og.nocommission=0 and ({$time} - o.createtime > {$day_times}) and og.status1=0  and o.uniacid=:uniacid", array(
//                    ':uniacid' => $_W['uniacid'],
//                    ':agentid' => $member['id']
//                ));
                $level1_commissions = M();
                $sql = "select og.commission1,og.commissions  from ims_ewei_shop_order_goods og left join ims_ewei_shop_order o on o.id = og.orderid where o.agentid= $member[id] and o.status>=3 and og.nocommission=0 and ($time - o.createtime > $day_times) and og.status1=0  and o.uniacid= $token";
                $level1_commissions = $level1_commissions->query($sql);
//                p($sql);
//                p($level1_commissions);die;
                foreach ($level1_commissions as $c) {
                    $commissions = unserialize($c['commissions']);
                    $commission = unserialize($c['commission1']);
                    if (empty($commissions)) {
                        $commission_ok += isset($commission['level' . $agentLevel['id']]) ? $commission['level' . $agentLevel['id']] : $commission['default'];
                    } else {
                        $commission_ok += isset($commissions['level1']) ? $commissions['level1'] : 0;
                    }
                }
            }
            if (in_array('lock', $options)) {
//                未结算佣金
//                $level1_commissions1 = pdo_fetchall('select og.commission1,og.commissions  from ' . tablename('ewei_shop_order_goods') . ' og ' . ' left join  ' . tablename('ewei_shop_order') . ' o on o.id = og.orderid' . " where o.agentid=:agentid and o.status>=3 and og.nocommission=0 and ({$time} - o.createtime <= {$day_times})  and og.status1=0  and o.uniacid=:uniacid", array(
//                    ':uniacid' => $_W['uniacid'],
//                    ':agentid' => $member['id']
//                ));
                $level1_commissions1 = M();
                $sql = 'select og.commission1,og.commissions  from ims_ewei_shop_order_goods og  left join ims_ewei_shop_order o on o.id = og.orderid where o.agentid=' . $member['id'] . ' and o.status>=3 and og.nocommission=0 and (' . $time . ' - o.createtime <= ' . $day_times . ')  and og.status1=0  and o.uniacid=' . $token;
                $level1_commissions1 = $level1_commissions1->query($sql);
//                p($level1_commissions1);die;
                foreach ($level1_commissions1 as $c) {
                    $commissions = unserialize($c['commissions']);
                    $commission = unserialize($c['commission1']);
                    if (empty($commissions)) {
                        $commission_lock += isset($commission['level' . $agentLevel['id']]) ? $commission['level' . $agentLevel['id']] : $commission['default'];
                    } else {
                        $commission_lock += isset($commissions['level1']) ? $commissions['level1'] : 0;
                    }
                }
            }
            if (in_array('apply', $options)) {
//                已申请佣金
//                $level1_commissions2 = pdo_fetchall('select og.commission1,og.commissions  from ' . tablename('ewei_shop_order_goods') . ' og ' . ' left join  ' . tablename('ewei_shop_order') . ' o on o.id = og.orderid' . " where o.agentid=:agentid and o.status>=3 and og.status1=1 and og.nocommission=0 and o.uniacid=:uniacid", array(
//                    ':uniacid' => $_W['uniacid'],
//                    ':agentid' => $member['id']
//                ));
                $level1_commissions2 = M();
                $sql = 'select og.commission1,og.commissions  from ims_ewei_shop_order_goods og left join  ims_ewei_shop_order o on o.id = og.orderid where o.agentid=' . $member['id'] . ' and o.status>=3 and og.status1=1 and og.nocommission=0 and o.uniacid=' . $token;
                $level1_commissions2 = $level1_commissions2->query($sql);

                foreach ($level1_commissions2 as $c) {
                    $commissions = unserialize($c['commissions']);
                    $commission = unserialize($c['commission1']);
                    if (empty($commissions)) {
                        $commission_apply += isset($commission['level' . $agentLevel['id']]) ? $commission['level' . $agentLevel['id']] : $commission['default'];
                    } else {
                        $commission_apply += isset($commissions['level1']) ? $commissions['level1'] : 0;
                    }
                }
            }
            if (in_array('check', $options)) {
//                二级待审核佣金
//                $level1_commissions2 = pdo_fetchall('select og.commission1,og.commissions  from ' . tablename('ewei_shop_order_goods') . ' og ' . ' left join  ' . tablename('ewei_shop_order') . ' o on o.id = og.orderid' . " where o.agentid=:agentid and o.status>=3 and og.status1=2 and og.nocommission=0 and o.uniacid=:uniacid ", array(
//                    ':uniacid' => $_W['uniacid'],
//                    ':agentid' => $member['id']
//                ));
                $level1_commissions2 = M();
                $sql = 'select og.commission1,og.commissions  from ims_ewei_shop_order_goods og left join ims_ewei_shop_order o on o.id = og.orderid where o.agentid=' . $member['id'] . ' and o.status>=3 and og.status1=2 and og.nocommission=0 and o.uniacid=' . $token;
                $level1_commissions2 = $level1_commissions2->query($sql);
//                p($level1_commissions2);die;
                foreach ($level1_commissions2 as $c) {
                    $commissions = unserialize($c['commissions']);
                    $commission = unserialize($c['commission1']);
                    if (empty($commissions)) {
                        $commission_check += isset($commission['level' . $agentLevel['id']]) ? $commission['level' . $agentLevel['id']] : $commission['default'];
                    } else {
                        $commission_check += isset($commissions['level1']) ? $commissions['level1'] : 0;
                    }
                }
            }
            if (in_array('pay', $options)) {
//                $level1_commissions2 = pdo_fetchall('select og.commission1,og.commissions  from ' . tablename('ewei_shop_order_goods') . ' og ' . ' left join  ' . tablename('ewei_shop_order') . ' o on o.id = og.orderid' . " where o.agentid=:agentid and o.status>=3 and og.status1=3 and og.nocommission=0 and o.uniacid=:uniacid ", array(
//                    ':uniacid' => $_W['uniacid'],
//                    ':agentid' => $member['id']
//                ));
                $level1_commissions2 = M();
                $sql = 'select og.commission1,og.commissions  from ims_ewei_shop_order_goods og left join ims_ewei_shop_order o on o.id = og.orderid where o.agentid=' . $member['id'] . ' and o.status>=3 and og.status1=3 and og.nocommission=0 and o.uniacid=' . $token;
                $level1_commissions2 = $level1_commissions2->query($sql);

                foreach ($level1_commissions2 as $c) {
                    $commissions = unserialize($c['commissions']);
                    $commission = unserialize($c['commission1']);
                    if (empty($commissions)) {
                        $commission_pay += isset($commission['level' . $agentLevel['id']]) ? $commission['level' . $agentLevel['id']] : $commission['default'];
                    } else {
                        $commission_pay += isset($commissions['level1']) ? $commissions['level1'] : 0;
                    }
                }
            }
//            查出一级人数
//            $level1_agentids = pdo_fetchall('select id from ' . tablename('ewei_shop_member') . ' where agentid=:agentid and isagent=1 and status=1 and uniacid=:uniacid ', array(
//                ':uniacid' => $_W['uniacid'],
//                ':agentid' => $member['id']
//                    ), 'id');
            $level1_agentids = D('Commission/ewei_shop_member')->getAgentArr($token, $member['id']);
            $level1 = count($level1_agentids);
            $agentcount += $level1;
        }
//        二级
        if ($level >= 2) {
            if ($level1 > 0) {
                if (in_array('ordercount0', $options)) {
//                    $level2_ordercount = pdo_fetch('select sum(og.realprice) as ordermoney,count(distinct o.id) as ordercount from ' . tablename('ewei_shop_order') . ' o ' . ' left join  ' . tablename('ewei_shop_order_goods') . ' og on og.orderid=o.id ' . ' where o.agentid in( ' . implode(',', array_keys($level1_agentids)) . ')  and o.status>=0 and og.status2>=0 and og.nocommission=0 and o.uniacid=:uniacid limit 1', array(
//                        ':uniacid' => $_W['uniacid']
//                    ));
//                    二级订单
                    $sql = 'select sum(og.realprice) as ordermoney,count(distinct o.id) as ordercount from ims_ewei_shop_order o left join  ims_ewei_shop_order_goods  og on og.orderid=o.id  where o.agentid in(' . implode(',', array_values($level1_agentids)) . ') and o.status>=0 and og.status2>=0 and og.nocommission=0 and o.uniacid= ' . $token . ' limit 1';
                    $level2_ordercount = M();
                    $level2_ordercount = $level2_ordercount->query($sql);
                    if ($level2_ordercount) {
                        $level2_ordercount = $level2_ordercount[0];
                    }
                    $order20 += $level2_ordercount['ordercount'];
                    $ordercount0 += $level2_ordercount['ordercount'];
                    $ordermoney0 += $level1_ordercount['ordermoney'];
                }
                if (in_array('ordercount', $options)) {
                    $level2_ordercount = pdo_fetch('select sum(og.realprice) as ordermoney,count(distinct o.id) as ordercount from ' . tablename('ewei_shop_order') . ' o ' . ' left join  ' . tablename('ewei_shop_order_goods') . ' og on og.orderid=o.id ' . ' where o.agentid in( ' . implode(',', array_keys($level1_agentids)) . ')  and o.status>=1 and og.status2>=0 and og.nocommission=0 and o.uniacid=:uniacid limit 1', array(
                        ':uniacid' => $_W['uniacid']
                    ));
                    $level2_ordercount = M();
                    $sql = 'select sum(og.realprice) as ordermoney,count(distinct o.id) as ordercount from ims_ewei_shop_order o ' . ' left join ims_ewei_shop_order_goods og on og.orderid=o.id ' . ' where o.agentid in( ' . implode(',', array_values($level1_agentids)) . ')  and o.status>=1 and og.status2>=0 and og.nocommission=0 and o.uniacid=' . $token . ' limit 1';
                    $level2_ordercount = $level2_ordercount->query($sql);
                    if ($level2_ordercount) {
                        $level2_ordercount = $level2_ordercount[0];
                    }
                    $order2 += $level2_ordercount['ordercount'];
                    $ordercount += $level2_ordercount['ordercount'];
                    $ordermoney += $level1_ordercount['ordermoney'];
                }
                if (in_array('ordercount3', $options)) {
//                    $level2_ordercount3 = pdo_fetch('select sum(og.realprice) as ordermoney,count(distinct o.id) as ordercount from ' . tablename('ewei_shop_order') . ' o ' . ' left join  ' . tablename('ewei_shop_order_goods') . ' og on og.orderid=o.id ' . ' where o.agentid in( ' . implode(',', array_keys($level1_agentids)) . ')  and o.status>=3 and og.status2>=0 and og.nocommission=0 and o.uniacid=:uniacid limit 1', array(
//                        ':uniacid' => $_W['uniacid']
//                    ));
                    $level2_ordercount3 = M();
                    $sql = 'select sum(og.realprice) as ordermoney,count(distinct o.id) as ordercount from ims_ewei_shop_order o left join ims_ewei_shop_order_goods og on og.orderid=o.id ' . ' where o.agentid in( ' . implode(',', array_values($level1_agentids)) . ')  and o.status>=3 and og.status2>=0 and og.nocommission=0 and o.uniacid=' . $token . ' limit 1';
                    $level2_ordercount3 = $level2_ordercount3->query($sql);
                    if ($level2_ordercount3) {
                        $level2_ordercount3 = $level2_ordercount3[0];
                    }
                    $order23 += $level2_ordercount3['ordercount'];
                    $ordercount3 += $level2_ordercount3['ordercount'];
                    $ordermoney3 += $level1_ordercount3['ordermoney'];
                }
//                取二级的佣金
                if (in_array('total', $options)) {
//                    $level2_commissions = pdo_fetchall('select og.commission2,og.commissions from ' . tablename('ewei_shop_order_goods') . ' og ' . ' left join  ' . tablename('ewei_shop_order') . ' o on o.id = og.orderid ' . ' where o.agentid in( ' . implode(',', array_keys($level1_agentids)) . ")  and o.status>=1 and og.nocommission=0 and o.uniacid=:uniacid", array(
//                        ':uniacid' => $_W['uniacid']
//                    ));
                    $level2_commissions = D('Commission/ewei_shop_order')->getOrdergoods($token, $member['id'], 'in', implode(',', array_values($level1_agentids)), 2);

                    foreach ($level2_commissions as $c) {
                        $commissions = unserialize($c['commissions']);
                        $commission = unserialize($c['commission2']);
                        if (empty($commissions)) {
                            $commission_total += isset($commission['level' . $agentLevel['id']]) ? $commission['level' . $agentLevel['id']] : $commission['default'];
                        } else {
                            $commission_total += isset($commissions['level2']) ? $commissions['level2'] : 0;
                        }
                    }
                }
                if (in_array('ok', $options)) {
//                    已完成订单
//                    $level2_commissions = pdo_fetchall('select og.commission2,og.commissions  from ' . tablename('ewei_shop_order_goods') . ' og ' . ' left join  ' . tablename('ewei_shop_order') . ' o on o.id = og.orderid ' . ' where o.agentid in( ' . implode(',', array_keys($level1_agentids)) . ")  and ({$time} - o.createtime > {$day_times}) and o.status>=3 and og.status2=0 and og.nocommission=0  and o.uniacid=:uniacid", array(
//                        ':uniacid' => $_W['uniacid']
//                    ));
//                  
                    $sql = 'select og.commission2,og.commissions  from ims_ewei_shop_order_goods og left join ims_ewei_shop_order o on o.id = og.orderid where o.agentid in(' . implode(',', array_values($level1_agentids)) . ') and (' . $time . ' - o.createtime > ' . $day_times . ') and o.status>=3 and og.status2=0 and og.nocommission=0  and o.uniacid= ' . $token;
                    $level2_commissions = M();
                    $level2_commissions = $level2_commissions->query($sql);

                    foreach ($level2_commissions as $c) {
                        $commissions = unserialize($c['commissions']);
                        $commission = unserialize($c['commission2']);
                        if (empty($commissions)) {
                            $commission_ok += isset($commission['level' . $agentLevel['id']]) ? $commission['level' . $agentLevel['id']] : $commission['default'];
                        } else {
                            $commission_ok += isset($commissions['level2']) ? $commissions['level2'] : 0;
                        }
                    }
                }
                if (in_array('lock', $options)) {
//                    二级未结算佣金
//                    $level2_commissions1 = pdo_fetchall('select og.commission2,og.commissions  from ' . tablename('ewei_shop_order_goods') . ' og ' . ' left join  ' . tablename('ewei_shop_order') . ' o on o.id = og.orderid ' . ' where o.agentid in( ' . implode(',', array_keys($level1_agentids)) . ")  and ({$time} - o.createtime <= {$day_times}) and og.status2=0 and o.status>=3 and og.nocommission=0 and o.uniacid=:uniacid", array(
//                        ':uniacid' => $_W['uniacid']
//                    ));
                    $level2_commissions1 = M();
                    $sql = 'select og.commission2,og.commissions  from ims_ewei_shop_order_goods og left join ims_ewei_shop_order o on o.id = og.orderid where o.agentid in( ' . implode(',', array_values($level1_agentids)) . ') and (' . $time . ' - o.createtime <= ' . $day_times . ') and og.status2=0 and o.status>=3 and og.nocommission=0 and o.uniacid=' . $token;
                    $level2_commissions1 = $level2_commissions1->query($sql);
//                    p($level2_commissions1);die;
                    foreach ($level2_commissions1 as $c) {
                        $commissions = unserialize($c['commissions']);
                        $commission = unserialize($c['commission2']);
                        if (empty($commissions)) {
                            $commission_lock += isset($commission['level' . $agentLevel['id']]) ? $commission['level' . $agentLevel['id']] : $commission['default'];
                        } else {
                            $commission_lock += isset($commissions['level2']) ? $commissions['level2'] : 0;
                        }
                    }
                }
                if (in_array('apply', $options)) {
//                    二级已申请佣金
//                    $level2_commissions2 = pdo_fetchall('select og.commission2,og.commissions  from ' . tablename('ewei_shop_order_goods') . ' og ' . ' left join  ' . tablename('ewei_shop_order') . ' o on o.id = og.orderid ' . ' where o.agentid in( ' . implode(',', array_keys($level1_agentids)) . ")  and o.status>=3 and og.status2=1 and og.nocommission=0 and o.uniacid=:uniacid", array(
//                        ':uniacid' => $_W['uniacid']
//                    ));
                    $level2_commissions2 = M();
                    $sql = 'select og.commission2,og.commissions  from ims_ewei_shop_order_goods og left join  ims_ewei_shop_order o on o.id = og.orderid where o.agentid in( ' . implode(',', array_values($level1_agentids)) . ')  and o.status>=3 and og.status2=1 and og.nocommission=0 and o.uniacid=' . $token;
                    $level2_commissions2 = $level2_commissions2->query($sql);

                    foreach ($level2_commissions2 as $c) {
                        $commissions = unserialize($c['commissions']);
                        $commission = unserialize($c['commission2']);
                        if (empty($commissions)) {
                            $commission_apply += isset($commission['level' . $agentLevel['id']]) ? $commission['level' . $agentLevel['id']] : $commission['default'];
                        } else {
                            $commission_apply += isset($commissions['level2']) ? $commissions['level2'] : 0;
                        }
                    }
                }
                if (in_array('check', $options)) {
//                    二级待审核佣金
//                    $level2_commissions3 = pdo_fetchall('select og.commission2,og.commissions  from ' . tablename('ewei_shop_order_goods') . ' og ' . ' left join  ' . tablename('ewei_shop_order') . ' o on o.id = og.orderid ' . ' where o.agentid in( ' . implode(',', array_keys($level1_agentids)) . ")  and o.status>=3 and og.status2=2 and og.nocommission=0 and o.uniacid=:uniacid", array(
//                        ':uniacid' => $_W['uniacid']
//                    ));
                    $level2_commissions3 = M();
                    $sql = 'select og.commission2,og.commissions  from ims_ewei_shop_order_goods og left join ims_ewei_shop_order o on o.id = og.orderid where o.agentid in( ' . implode(',', array_values($level1_agentids)) . ')  and o.status>=3 and og.status2=2 and og.nocommission=0 and o.uniacid=' . $token;
                    $level2_commissions3 = $level2_commissions3->query($sql);
//                    p($level2_commissions3);die;
                    foreach ($level2_commissions3 as $c) {
                        $commissions = unserialize($c['commissions']);
                        $commission = unserialize($c['commission2']);
                        if (empty($commissions)) {
                            $commission_check += isset($commission['level' . $agentLevel['id']]) ? $commission['level' . $agentLevel['id']] : $commission['default'];
                        } else {
                            $commission_check += isset($commissions['level2']) ? $commissions['level2'] : 0;
                        }
                    }
                }
                if (in_array('pay', $options)) {
//                    二级成功提现佣金
//                    $level2_commissions3 = pdo_fetchall('select og.commission2,og.commissions  from ' . tablename('ewei_shop_order_goods') . ' og ' . ' left join  ' . tablename('ewei_shop_order') . ' o on o.id = og.orderid ' . ' where o.agentid in( ' . implode(',', array_keys($level1_agentids)) . ")  and o.status>=3 and og.status2=3 and og.nocommission=0 and o.uniacid=:uniacid", array(
//                        ':uniacid' => $_W['uniacid']
//                    ));
                    $level2_commissions3 = M();
                    $sql = 'select og.commission2,og.commissions  from ims_ewei_shop_order_goods og left join ims_ewei_shop_order o on o.id = og.orderid where o.agentid in(' . implode(',', array_values($level1_agentids)) . ')  and o.status>=3 and og.status2=3 and og.nocommission=0 and o.uniacid=' . $token;
                    $level2_commissions3 = $level2_commissions3->query($sql);
                    foreach ($level2_commissions3 as $c) {
                        $commissions = unserialize($c['commissions']);
                        $commission = unserialize($c['commission2']);
                        if (empty($commissions)) {
                            $commission_pay += isset($commission['level' . $agentLevel['id']]) ? $commission['level' . $agentLevel['id']] : $commission['default'];
                        } else {
                            $commission_pay += isset($commissions['level2']) ? $commissions['level2'] : 0;
                        }
                    }
                }
//                查出二级的分销商
//                 将一级的id拼成字符串
//                $xl = implode(',', array_values($level1_agentids));
//                $level2_agentids = pdo_fetchall('select id from ' . tablename('ewei_shop_member') . ' where agentid in( ' . implode(',', array_keys($level1_agentids)) . ') and isagent=1 and status=1 and uniacid=:uniacid', array(
//                    ':uniacid' => $_W['uniacid']
//                        ), 'id');
                $level2_agentids = D('Commission/ewei_shop_member')->getAgentArr($token, $openid, 'in', implode(',', array_values($level1_agentids)));

                $level2 = count($level2_agentids);
                $agentcount += $level2;
            }
        }
//        三级
        if ($level >= 3) {
            if ($level2 > 0) {
                if (in_array('ordercount0', $options)) {
//                    三级订单
//                    $level3_ordercount = pdo_fetch('select sum(og.realprice) as ordermoney,count(distinct og.orderid) as ordercount from ' . tablename('ewei_shop_order') . ' o ' . ' left join  ' . tablename('ewei_shop_order_goods') . ' og on og.orderid=o.id ' . ' where o.agentid in( ' . implode(',', array_keys($level2_agentids)) . ')  and o.status>=0 and og.status3>=0 and og.nocommission=0 and o.uniacid=:uniacid limit 1', array(
//                        ':uniacid' => $_W['uniacid']
//                    ));
                    $sql = 'select sum(og.realprice) as ordermoney,count(distinct og.orderid) as ordercount from ims_ewei_shop_order o left join ims_ewei_shop_order_goods og on og.orderid=o.id where o.agentid in (' . implode(',', array_values($level2_agentids)) . ') and o.status>=0 and og.status3>=0 and og.nocommission=0 and o.uniacid= ' . $token . ' limit 1';
                    $level3_ordercount = M();
                    $level3_ordercount = $level3_ordercount->query($sql);
                    if ($level3_ordercount) {
                        $level3_ordercount = $level3_ordercount[0];
                    }

                    $order30 += $level3_ordercount['ordercount'];
                    $ordercount0 += $level3_ordercount['ordercount'];
                    $ordermoney0 += $level3_ordercount['ordermoney'];
                }
                if (in_array('ordercount', $options)) {
//                    $level3_ordercount = pdo_fetch('select sum(og.realprice) as ordermoney,count(distinct og.orderid) as ordercount from ' . tablename('ewei_shop_order') . ' o ' . ' left join  ' . tablename('ewei_shop_order_goods') . ' og on og.orderid=o.id ' . ' where o.agentid in( ' . implode(',', array_keys($level2_agentids)) . ')  and o.status>=1 and og.status3>=0 and og.nocommission=0 and o.uniacid=:uniacid limit 1', array(
//                        ':uniacid' => $_W['uniacid']
//                    ));
                    $level3_ordercount = M();
                    $sql = 'select sum(og.realprice) as ordermoney,count(distinct og.orderid) as ordercount from ims_ewei_shop_order o  left join ims_ewei_shop_order_goods og on og.orderid=o.id ' . ' where o.agentid in( ' . implode(',', array_values($level2_agentids)) . ')  and o.status>=1 and og.status3>=0 and og.nocommission=0 and o.uniacid=' . $token . ' limit 1';
                    $level3_ordercount = $level3_ordercount->query($sql);
                    if ($level3_ordercount) {
                        $level3_ordercount = $level3_ordercount[0];
                    }
                    $order3 += $level3_ordercount['ordercount'];
                    $ordercount += $level3_ordercount['ordercount'];
                    $ordermoney += $level3_ordercount['ordermoney'];
                }
                if (in_array('ordercount3', $options)) {
                    $level3_ordercount3 = pdo_fetch('select sum(og.realprice) as ordermoney,count(distinct og.orderid) as ordercount from ' . tablename('ewei_shop_order') . ' o ' . ' left join  ' . tablename('ewei_shop_order_goods') . ' og on og.orderid=o.id ' . ' where o.agentid in( ' . implode(',', array_keys($level2_agentids)) . ')  and o.status>=3 and og.status3>=0 and og.nocommission=0 and o.uniacid=:uniacid limit 1', array(
                        ':uniacid' => $_W['uniacid']
                    ));
                    $level3_ordercount3 = M();
                    $sql = 'select sum(og.realprice) as ordermoney,count(distinct og.orderid) as ordercount from ims_ewei_shop_order o left join ims_ewei_shop_order_goods og on og.orderid=o.id ' . ' where o.agentid in( ' . implode(',', array_values($level2_agentids)) . ')  and o.status>=3 and og.status3>=0 and og.nocommission=0 and o.uniacid=' . $token . ' limit 1';
                    $level3_ordercount3 = $level3_ordercount3->query($sql);
                    if ($level3_ordercount3) {
                        $level3_ordercount3 = $level3_ordercount3[0];
                    }
                    $order33 += $level3_ordercount3['ordercount'];
                    $ordercount3 += $level3_ordercount3['ordercount'];
                    $ordermoney3 += $level3_ordercount3['ordermoney'];
                }
//                三级分销
                if (in_array('total', $options)) {
//                    $level3_commissions = pdo_fetchall('select og.commission3,og.commissions  from ' . tablename('ewei_shop_order_goods') . ' og ' . ' left join  ' . tablename('ewei_shop_order') . ' o on o.id = og.orderid' . ' where o.agentid in( ' . implode(',', array_keys($level2_agentids)) . ")  and o.status>=1 and og.nocommission=0 and o.uniacid=:uniacid", array(
//                        ':uniacid' => $_W['uniacid']
//                    ));
                    $level3_commissions = D('Commission/ewei_shop_order')->getOrdergoods($token, $member['id'], 'in', implode(',', array_values($level1_agentids)), 3);
                    foreach ($level3_commissions as $c) {
                        $commissions = unserialize($c['commissions']);
                        $commission = unserialize($c['commission3']);
                        if (empty($commissions)) {
                            $commission_total += isset($commission['level' . $agentLevel['id']]) ? $commission['level' . $agentLevel['id']] : $commission['default'];
                        } else {
                            $commission_total += isset($commissions['level3']) ? $commissions['level3'] : 0;
                        }
                    }
                }
                if (in_array('ok', $options)) {
//                    三级已完成订单
//                    $level3_commissions = pdo_fetchall('select og.commission3,og.commissions  from ' . tablename('ewei_shop_order_goods') . ' og ' . ' left join  ' . tablename('ewei_shop_order') . ' o on o.id = og.orderid' . ' where o.agentid in( ' . implode(',', array_keys($level2_agentids)) . ")  and ({$time} - o.createtime > {$day_times}) and o.status>=3 and og.status3=0  and og.nocommission=0 and o.uniacid=:uniacid", array(
//                        ':uniacid' => $_W['uniacid']
//                    ));
                    $sql = 'select og.commission3,og.commissions  from ims_ewei_shop_order_goods og left join ims_ewei_shop_order o on o.id = og.orderid where o.agentid in(' . implode(',', array_values($level2_agentids)) . ') and (' . $time . ' - o.createtime > ' . $day_times . ') and o.status>=3 and og.status3=0  and og.nocommission=0 and o.uniacid=' . $token;
                    $level3_commissions = M();
                    $level3_commissions = $level3_commissions->query($sql);

                    foreach ($level3_commissions as $c) {
                        $commissions = unserialize($c['commissions']);
                        $commission = unserialize($c['commission3']);
                        if (empty($commissions)) {
                            $commission_ok += isset($commission['level' . $agentLevel['id']]) ? $commission['level' . $agentLevel['id']] : $commission['default'];
                        } else {
                            $commission_ok += isset($commissions['level3']) ? $commissions['level3'] : 0;
                        }
                    }
                }
                if (in_array('lock', $options)) {
//                    三级未结算佣金
//                    $level3_commissions1 = pdo_fetchall('select og.commission3,og.commissions  from ' . tablename('ewei_shop_order_goods') . ' og ' . ' left join  ' . tablename('ewei_shop_order') . ' o on o.id = og.orderid' . ' where o.agentid in( ' . implode(',', array_keys($level2_agentids)) . ")  and o.status>=3 and ({$time} - o.createtime > {$day_times}) and og.status3=0  and og.nocommission=0 and o.uniacid=:uniacid", array(
//                        ':uniacid' => $_W['uniacid']
//                    ));
                    $level3_commissions1 = M();
                    $sql = 'select og.commission3,og.commissions  from ims_ewei_shop_order_goods og left join ims_ewei_shop_order o on o.id = og.orderid where o.agentid in( ' . implode(',', array_values($level2_agentids)) . ')  and o.status>=3 and (' . $time . ' - o.createtime > ' . $day_times . ') and og.status3=0  and og.nocommission=0 and o.uniacid=' . $token;
                    $level3_commissions1 = $level3_commissions1->query($sql);
//                    p($level3_commissions1);die;
                    foreach ($level3_commissions1 as $c) {
                        $commissions = unserialize($c['commissions']);
                        $commission = unserialize($c['commission3']);
                        if (empty($commissions)) {
                            $commission_lock += isset($commission['level' . $agentLevel['id']]) ? $commission['level' . $agentLevel['id']] : $commission['default'];
                        } else {
                            $commission_lock += isset($commissions['level3']) ? $commissions['level3'] : 0;
                        }
                    }
                }
                if (in_array('apply', $options)) {
//                    三级已申请佣金
//                    $level3_commissions2 = pdo_fetchall('select og.commission3,og.commissions  from ' . tablename('ewei_shop_order_goods') . ' og ' . ' left join  ' . tablename('ewei_shop_order') . ' o on o.id = og.orderid' . ' where o.agentid in( ' . implode(',', array_keys($level2_agentids)) . ")  and o.status>=3 and og.status3=1 and og.nocommission=0 and o.uniacid=:uniacid", array(
//                        ':uniacid' => $_W['uniacid']
//                    ));
                    $level3_commissions2 = M();
                    $sql = 'select og.commission3,og.commissions  from ims_ewei_shop_order_goods og left join ims_ewei_shop_order o on o.id = og.orderid where o.agentid in( ' . implode(',', array_values($level2_agentids)) . ')  and o.status>=3 and og.status3=1 and og.nocommission=0 and o.uniacid=' . $token;
                    $level3_commissions2 = $level3_commissions2->query($sql);
                    foreach ($level3_commissions2 as $c) {
                        $commissions = unserialize($c['commissions']);
                        $commission = unserialize($c['commission3']);
                        if (empty($commissions)) {
                            $commission_apply += isset($commission['level' . $agentLevel['id']]) ? $commission['level' . $agentLevel['id']] : $commission['default'];
                        } else {
                            $commission_apply += isset($commissions['level3']) ? $commissions['level3'] : 0;
                        }
                    }
                }
                if (in_array('check', $options)) {
//                    三级待审核佣金
//                    $level3_commissions3 = pdo_fetchall('select og.commission3,og.commissions  from ' . tablename('ewei_shop_order_goods') . ' og ' . ' left join  ' . tablename('ewei_shop_order') . ' o on o.id = og.orderid' . ' where o.agentid in( ' . implode(',', array_keys($level2_agentids)) . ")  and o.status>=3 and og.status3=2 and og.nocommission=0 and o.uniacid=:uniacid", array(
//                        ':uniacid' => $_W['uniacid']
//                    ));
                    $level3_commissions3 = M();
                    $sql = 'select og.commission3,og.commissions  from ims_ewei_shop_order_goods og left join ims_ewei_shop_order o on o.id = og.orderid where o.agentid in( ' . implode(',', array_values($level2_agentids)) . ')  and o.status>=3 and og.status3=2 and og.nocommission=0 and o.uniacid=' . $token;
                    $level3_commissions3 = $level3_commissions3->query($sql);
//                    p($level3_commissions3);die;
                    foreach ($level3_commissions3 as $c) {
                        $commissions = unserialize($c['commissions']);
                        $commission = unserialize($c['commission3']);
                        if (empty($commissions)) {
                            $commission_check += isset($commission['level' . $agentLevel['id']]) ? $commission['level' . $agentLevel['id']] : $commission['default'];
                        } else {
                            $commission_check += isset($commissions['level3']) ? $commissions['level3'] : 0;
                        }
                    }
                }
                if (in_array('pay', $options)) {
//                    三级成功提现佣金
//                    $level3_commissions3 = pdo_fetchall('select og.commission3,og.commissions  from ' . tablename('ewei_shop_order_goods') . ' og ' . ' left join  ' . tablename('ewei_shop_order') . ' o on o.id = og.orderid' . ' where o.agentid in( ' . implode(',', array_keys($level2_agentids)) . ")  and o.status>=3 and og.status3=3 and og.nocommission=0 and o.uniacid=:uniacid", array(
//                        ':uniacid' => $_W['uniacid']
//                    ));
                    $level3_commissions3 = M();
                    $sql = 'select og.commission3,og.commissions  from ims_ewei_shop_order_goods og left join ims_ewei_shop_order o on o.id = og.orderid where o.agentid in( ' . implode(',', array_values($level2_agentids)) . ')  and o.status>=3 and og.status3=3 and og.nocommission=0 and o.uniacid=' . $token;
                    $level3_commissions3 = $level3_commissions3->query($sql);
//                    p($level3_commissions3);die;
                    foreach ($level3_commissions3 as $c) {
                        $commissions = unserialize($c['commissions']);
                        $commission = unserialize($c['commission3']);
                        if (empty($commissions)) {
                            $commission_pay += isset($commission['level' . $agentLevel['id']]) ? $commission['level' . $agentLevel['id']] : $commission['default'];
                        } else {
                            $commission_pay += isset($commissions['level3']) ? $commissions['level3'] : 0;
                        }
                    }
                }
//                三级
//                $level3_agentids = pdo_fetchall('select id from ' . tablename('ewei_shop_member') . ' where uniacid=:uniacid and agentid in( ' . implode(',', array_keys($level2_agentids)) . ') and isagent=1 and status=1', array(
//                    ':uniacid' => $_W['uniacid']
//                        ), 'id');
                $level3_agentids = D('Commission/ewei_shop_member')->getAgentArr($token, $openid, 'in', implode(',', array_values($level2_agentids)));
                $level3 = count($level3_agentids);
                $agentcount += $level3;
            }
        }
        $member['agentcount'] = $agentcount;
        $member['ordercount'] = $ordercount;
        $member['ordermoney'] = $ordermoney;
        $member['order1'] = $order1;
        $member['order2'] = $order2;
        $member['order3'] = $order3;
        $member['ordercount3'] = $ordercount3;
        $member['ordermoney3'] = $ordermoney3;
        $member['order13'] = $order13;
        $member['order23'] = $order23;
        $member['order33'] = $order33;
        $member['ordercount0'] = $ordercount0;
        $member['ordermoney0'] = $ordermoney0;
        $member['order10'] = $order10;
        $member['order20'] = $order20;
        $member['order30'] = $order30;
        $member['commission_total'] = round($commission_total, 2);
        $member['commission_ok'] = round($commission_ok, 2);
        $member['commission_lock'] = round($commission_lock, 2);
        $member['commission_apply'] = round($commission_apply, 2);
        $member['commission_check'] = round($commission_check, 2);
        $member['commission_pay'] = round($commission_pay, 2);
        $member['level1'] = $level1;
        $member['level1_agentids'] = $level1_agentids;
        $member['level2'] = $level2;
        $member['level2_agentids'] = $level2_agentids;
        $member['level3'] = $level3;
        $member['level3_agentids'] = $level3_agentids;
        $member['agenttime'] = date('Y-m-d H:i', $member['agenttime']);
        return $member;
    }
    
    /**
     * 获取分销商等级
     * @param type $token
     * @param type $openid
     * @return boolean
     */
    public function getLevel($token,$openid) {
        if (empty($openid) || empty($token)) {
            return false;
        }
        $member = D('Commission/ewei_shop_member')->getinfo($token,$openid);

        if (empty($member['agentlevel'])) {
            return false;
        }
        $member['agentlevel'] = 32;
        $level = D('Commission/ewei_shop_commission_level')->getItemByWhere(array('uniacid'=>$token,'id'=>$member['agentlevel']));
        return $level;
    }

}
