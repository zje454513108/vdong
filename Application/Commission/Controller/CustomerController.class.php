<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Commission\Controller;

use Commission\Controller\CommissionBaseController;

/**
 * 我的下线
 */
class CustomerController extends CommissionBaseController {


    public function __construct() {
        parent::__construct();
    }

    public function index() {
        $openid = I('get.openid', '', 'string');
        $p = I('get.p', 1 , 'intval'); //当前页码
        $p = $p == 0 ? 1:$p;
        $limit = I('get.limit', 10, 'intval'); //
        //每页条数
        $user = $this->getMemberInfo($this->uniacid, $openid, 'id');
        $uid = $user['id'];
//        p($uid);
//        下线总数
        $cus_num = $this->getCustomerInfo($this->uniacid, $uid);
        if (empty($cus_num)) {
            $data['customer_num'] = 0;
            $data['order'] = '';
            $this->outFormats($data, 'ok', $p, 0, 0);
        }
//        获取下线信息
        $list = $this->getCustomerInfo($this->uniacid, $uid, 'select', $limit);
        $order = M('ewei_shop_order');
        foreach ($list as &$row) {
            $where = array(
                'uniacid' => $this->uniacid,
                'openid' => $row['openid']
            );
            $row['createtime'] = date('Y-m-d H:i', $row['createtime']);
//            订单状态未考虑----公众号bug
            $ordercount = $order
                    ->where($where)
                    ->count();
            $row['ordercount'] = number_format(intval($ordercount), 0);
//            总金额
            $where1 = array(
                'o.uniacid' => $this->uniacid,
                'o.openid' => $row['openid'],
                'o.status' => array('egt', 1)
            );
            $moneycount = $order
                    ->alias('o')
                    ->join('ims_ewei_shop_order_goods og on o.id = og.orderid')
                    ->where($where1)
                    ->sum('og.realprice * og.total');
            $row['moneycount'] = number_format(floatval($moneycount), 2);
            unset($row['openid']);
        }
        unset($row);
//        所有页数
        $totalPage = ceil($cus_num / $limit);
        $data['data'] = $list;
        $data['customer_num'] = $cus_num;
        if ($data['data']) {
            $this->outFormats($data, 'ok', $p, $totalPage, 0);
        } else {
            $data['data'] = array();
            $this->outFormats($data, '没有更多了', $p, $totalPage, 0);
        }
    }

    /**
     * 获取下线数据
     * @param type $token 商户id
     * @param type $uid 用户主键id
     * @param type $type 查询类型
     * @return type 数量
     */
    public function getCustomerInfo($token, $uid, $type = '', $limit = 10) {
        $where1 = array(
            'isagent' => 1,
            'status' => 0,
            '_logic' => 'and'
        );
        $where2 = array(
            'isagent' => 0,
            '_complex' => $where1,
            '_logic' => 'or'
        );
        $where = array(
            'uniacid' => $token,
            'agentid' => $uid,
            '_complex' => $where2
        );
        if ($type == 'select') {
            $count = $this->shopMember
                    ->where($where)
                    ->count();
            $Page = new \Think\Page($count, $limit);

            $data = $this->shopMember
                    ->field('id,agentid,openid,nickname,createtime,avatar')
                    ->where($where)
                    ->limit($Page->firstRow . ',' . $Page->listRows)
                    ->order('createtime DESC')
                    ->select();
        } else {
            $data = $this->shopMember
                    ->where($where)
                    ->count();
        }
        return $data;
    }

}
