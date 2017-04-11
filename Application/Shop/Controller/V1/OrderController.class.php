<?php

/*
 * lxj
 * 20170217
 * 18046521228
 */

namespace Shop\Controller;

use Shop\Controller\CommonController;

//5000

/**
 * Description of OrderController
 *
 * @author Administrator
 */
class OrderController extends CommonController {

    protected $order;

//    oJ17Ujh-mufiue8axfUQFuGSvt9U
//    66

    public function __construct() {
        parent::__construct();
        $this->order = D('Order');
    }

//    订单数据
    protected function orderData($openid, $status, $limit) {
//        查询订单数据
//        订单表
//        订单商品表
//        商品表
//        每页默认显示一条数据
        $where = array(
            'uniacid' => $this->uniacid,
            'openid' => $openid,
        );
        switch ($status) {
            case "0":
                $where['status'] = 0;
                break;
            case "1":
                $where['status'] = 1;
                break;
            case "2":
                $where['status'] = 2;
                break;
            case "3":
                $where['status'] = 3;
//                显示没有删除的订单
                $where['userdeleted'] = 0;
                break;
            case "4":
//                待退款订单列表
//                状态必须为1或者3，并且userdeleted=0,refundid>0
                $where['status'] = array('in', '1,3');
                $where['userdeleted'] = 0;
                $where['refundid'] = array('gt', 0);
                break;
            default:
                $where['status'] = array('egt', 0);
                $where['userdeleted'] = 0;
                break;
        }
        $count = $this->order
                ->field('id')
                ->where($where)
                ->count();
        $Page = new \Think\Page($count, $limit);
        $totalPage = ceil($count / $limit);
        $order = $this->order
                ->field('id,ordersn,price,status,refundid,isverify,virtual,iscomment')
                ->relation(true)
                ->where($where)
                ->order('createtime DESC')
                ->limit($Page->firstRow . ',' . $Page->listRows)
                ->select();
        foreach ($order as &$value) {

            foreach ($value['OrderGoods'] as &$val) {
                $value['totalGoods'] += 1;
                $val['Goods']['thumb'] = getImgUrl($val['Goods']['thumb']);
            }
        }
//        总页数
        $data['totalPage'] = $totalPage ? $totalPage : 0;
        $data['data'] = $order;
        return $data;
    }

    /**
     * 订单====通过status状态来区分订单状态
     */
    public function listOrder() {
//    先获取用户的openid
//    再查询order表获取该用户的订单信息
        $openid = I('get.openid', '', 'string');
        $this->getMember($this->uniacid, $openid);
        $status = I('get.status'); //订单状态
        switch ($status) {
//            case "-1":
//                $status = -1;//已取消----不用显示
//                break;
            case "0":
                $status = 0; //待支付
                break;
            case "1":
                $status = 1; //待发货
                break;
            case "2":
                $status = 2; //待收货
                break;
            case "3":
                $status = 3; //完成
                break;
            case "4":
                $status = 4; //待退款
                break;
            default:
                $status = 'all';
                break;
        }


        $p = I('get.p', 1, 'intval'); //当前页码
        $limit = I('get.limit', 2, 'intval'); //每页条数
        if ($limit > 20) {
            $this->outFormat('null', '参数格式错误', 5001, 'json');
        }
        $data = $this->orderData($openid, $status, $limit);
        if ($data['data']) {
            $this->outFormats($data['data'], 'ok', $p, $data['totalPage'], 0, 'json');
        } else {
            $this->outFormats('null', '没有订单', $p, $data['totalPage'], 0, 'json');
        }
    }

    /**
     * 订单状态---已完善
     */
    public function orderStatus() {
        $openid = I('get.openid', '', 'string');
        $this->getMember($this->uniacid, $openid);
        $order = M('ewei_shop_order')
                ->field('id,status,refundid')
                ->where("uniacid=%d and openid='%s'", array($this->uniacid, $openid))
                ->select();
        $count  = M('ewei_shop_order')
                ->field('id,status,refundid')
                ->where("uniacid=%d and openid='%s' and status>=0 and userdeleted = 0", array($this->uniacid, $openid))
                ->count();
        $data = array();
        foreach ($order as $value) {
            if ($value['status'] == 0) {
                $data['notPay'] += 1; //待付款
            } else if ($value['status'] == 1 && $value['refundid'] == 0) {
                $data['notDeliver'] += 1; //待发货
            } else if ($value['status'] == 2) {
                $data['notReceipt'] += 1; //待收货
            } else if ($value['status'] == 1 && $value['refundid'] != 0 || $value['status'] == 3 && $value['refundid'] != 0 && userdeleted == 0) {
                $data['waitRefund'] += 1; //待退款
            }
        }
        $data['notPay'] = $data['notPay'] ? $data['notPay'] : 0;
        $data['notDeliver'] = $data['notDeliver'] ? $data['notDeliver'] : 0;
        $data['notReceipt'] = $data['notReceipt'] ? $data['notReceipt'] : 0;
        $data['waitRefund'] = $data['waitRefund'] ? $data['waitRefund'] : 0;
        $data['allCount'] = (int)$count;

        $this->outFormat($data, 'ok', 0, 'json');
    }

    /**
     * 订单详情
     */
    public function orderinfo() {
        $openid = I('get.openid', '', 'string');
        $id = I('get.id', '', 'intval');
        if (empty($id)) {
            $this->outFormat('null', '参数错误', 5002, 'json');
        }
        $this->getMember($this->uniacid, $openid);
        $where = array(
            'uniacid' => $this->uniacid,
            'openid' => $openid,
            'id' => $id,
            'userdeleted' => 0,
        );
        $order = $this->order
                ->field('id,ordersn,price,goodsprice,status,refundid,discountprice,dispatchprice,address,isverify,virtual,finishtime,iscomment')
                ->relation(true)
                ->where($where)
                ->find();

        if (empty($order)) {
            $this->outFormat('null', '非法操作', 5003, 'json');
        }
        foreach ($order['OrderGoods'] as &$val) {
            $val['Goods']['thumb'] = getImgUrl($val['Goods']['thumb']);
        }
        $sys = $this->getsysset($this->uniacid, 'shop');
        $order['name'] = $sys['name'];
        $order['finishtime'] = $order['finishtime'] ? date('Y-m-d H:i:s', $order['finishtime']) : '';
        $order['address'] = unserialize($order['address']);
        unset($order['address']['province'], $order['address']['city'], $order['address']['area']);
        $this->outFormat($order, 'ok', 0, 'json');
    }

    /**
     * 取消订单--
     */
    public function cancel() {
//        取消订单的条件
        $openid = I('post.openid', '', 'string');
        $id = I('post.id', '', 'intval'); //订单主键id

        $this->getMember($this->token, $openid);

        if (empty($id)) {
            $this->outFormat('null', '参数错误', 5004, 'json');
        }
        $order = $this->order
                ->field('id,ordersn,openid,status,deductcredit,deductprice')
                ->relation(true)
                ->where("uniacid=%d and openid='%s' and id=%d", array($this->token, $openid, $id))
                ->find();
        if (empty($order) || $order['status'] != 0) {
            $this->outFormat('null', '非法操作', 5005, 'json');
        }
//        将当前订单状态改为-1,记录取消时间
        $model = M();
        $model->startTrans();
        $re1 = $model
                ->table(C('DB_PREFIX') . 'ewei_shop_order')
                ->where("uniacid=%d and openid='%s' and id=%d", array($this->token, $openid, $id))
                ->save(array('status' => -1, 'canceltime' => time()));
//        将商品数量的库存恢复
        $goods = M('ewei_shop_goods');
        foreach ($order['OrderGoods'] as $value) {
//            查库存
            $oldGoods = $goods
                    ->field('total')
                    ->where('id=%d', array($value['goodsid']))
                    ->find();
//            加库存
            $re2 = $model->table(C('DB_PREFIX') . 'ewei_shop_goods')
                    ->where('id=%d', array($value['goodsid']))
                    ->save(array('total' => $oldGoods['total'] + $value['total']));
        }

//        未加入积分
        if ($re1 && $re2) {
            $model->commit();
            $this->outFormat(array('status' => 1), 'ok', 0, 'json');
        } else {
            $model->rollback();
            $this->outFormat('null', '操作失败', 5006, 'json');
        }
    }

    /**
     * 订单删除----删除后不在订单列表显示
     */
    public function delOrder() {
        $openid = I('post.openid', '', 'string');
        $this->getMember($this->token, $openid);
        $id = I('post.id', '', 'intval'); //订单主键id
        if (empty($id)) {
            $this->outFormat('null', '参数错误', 5007, 'json');
        }
        $order = $this->order
                ->field('id,status,userdeleted')
                ->where("uniacid=%d and openid='%s' and id=%d", array($this->token, $openid, $id))
                ->find();
        if (empty($order) || $order['status'] != 3 && $order['userdeleted'] != 0) {
            $this->outFormat('null', '非法操作', 5008, 'json');
        }
//        改变订单状态，变为userdeleted=1
        $result = $this->order
                ->where('id=%d', array($id))
                ->save(array('userdeleted' => 1));
        if ($result) {
            $this->outFormat(array('status' => 1), 'ok', 0, 'json');
        } else {
            $this->outFormat('null', '操作失败', 5009, 'json');
        }
    }

    /**
     * 确认收货-----添加分销
     */
    public function complete() {
        $openid = I('post.openid', '', 'string');
        $this->getMember($this->token, $openid);
        $id = I('post.id', '', 'intval'); //订单主键id
        if (empty($id)) {
            $this->outFormat('null', '参数错误', 5010, 'json');
        }
        $order = $this->order
                ->field('id,status,openid')
                ->relation(true)
                ->where("uniacid=%d and openid='%s' and id=%d", array($this->token, $openid, $id))
                ->find();
        if (empty($order) || $order['status'] != 2) {
            $this->outFormat('null', '非法操作', 5011, 'json');
        }
        $result = $this->order
                ->where('id=%d', array($id))
                ->save(array('status' => 3, 'finishtime' => time()));
//        修改状态为3
//        添加订单完成时间finishtime
//        修改用户等级
        $member = A('Commission/Commission');

        $a = $member->upgradeLevel($this->token, $openid);

//        判断是否开启分销
        $commission = A('Commission/Common');
        $set = $commission->getSet($this->token, 'commission');
        if ($set['level'] > 0) {
            $orderprocess = A('Commission/Orderprocess');
            $orderprocess->complete($order['id'], $this->token);
        }

        if ($result) {
            $this->outFormat(array('status' => 1), 'ok', 0, 'json');
        } else {
            $this->outFormat('null', '操作失败', 5012, 'json');
        }
    }

    /**
     * 查看物流信息
     */
    public function express() {
//        状态等于2时可查看物流信息
        $openid = I('post.openid', '', 'string');
        $this->getMember($this->token, $openid);
        $id = I('post.id', '', 'intval'); //订单主键id
        if (empty($id)) {
            $this->outFormat('null', '参数错误', 5013, 'json');
        }
        $order = $this->order
                ->field('id,status,expresscom,expresssn,sendtime')
                ->relation(true)
                ->where("uniacid=%d and openid='%s' and id=%d", array($this->token, $openid, $id))
                ->find();
        if (empty($order) || $order['status'] != 2) {
            $this->outFormat('null', '非法操作', 5014, 'json');
        }
        $order['sendtime'] = date('Y-m-d H:i:s', $order['sendtime']);

        foreach ($order['OrderGoods'] as &$val) {
            $val['Goods']['thumb'] = getImgUrl($val['Goods']['thumb']);
        }
        $this->outFormat($order, 'ok', 0, 'json');
    }

    /**
     * 申请退款--操作
     */
    public function refund() {
        $openid = I('post.openid', '', 'string');
        $reason = I('post.reason'); //退款原因
        $content = I('post.content'); //备注
        $this->getMember($this->token, $openid); //判断用户是否存在
        $id = I('post.id', 0, 'intval'); //订单主键id
        if (empty($id) || empty($reason)) {
            $this->outFormat('null', '参数错误', 5015, 'json');
        }
        $order = $this->order
                ->field('id,status,price,refundid,virtual,isverify,finishtime,dispatchprice,deductprice,deductcredit2')
                ->where("uniacid=%d and openid='%s' and id=%d", array($this->token, $openid, $id))
                ->find();
        if (empty($order) || $order['status'] != 1 && $order['refundid'] != 0 || $order['status'] != 3 && $order['refundid'] != 0) {
            $this->outFormat('null', '非法操作', 5016, 'json');
        }
//        如果已确认收货要判断是否可以退货
        if ($order['status'] == 3) {
            if (!empty($order['virtual']) || $order['isverify'] == 1) {
                $this->outFormat('null', '此订单不允许退款', 5017, 'json');
            }
//            获取配置信息
            $sys = $this->getsysset($this->token, 'trade');
//            退款天数
            $refunddays = intval($sys['refunddays']);
            if ($refunddays > 0) {
                $days = intval((time() - $order['finishtime']) / 3600 / 24);
                if ($days > $refunddays) {
                    $this->outFormat('null', '订单完成已超过 ' . $refunddays . ' 天, 无法发起退款申请!', 5018, 'json');
                }
            } else {
                $this->outFormat('null', '订单完成, 无法申请退款!', 5019, 'json');
            }
        }

        $order['refundprice'] = $order['price'] + $order['deductcredit2'];
        if ($order['status'] >= 3) {
//            减掉运费
            $order['refundprice'] -= $order['dispatchprice'];
        }
//        开始写数据库操作

        $refundno = $this->createNO('order_refund', 'refundno', 'SR');
        $refund_info = array(
            'uniacid' => $this->token,
            'orderid' => $id,
            'refundno' => $refundno,
            'price' => $order['refundprice'],
            'reason' => $reason,
            'content' => $content,
            'createtime' => time(),
        );
//        开启事物
        // 添加事务
        $model = M();
        $model->startTrans();
        $re1 = $model
                ->table(C('DB_PREFIX') . 'ewei_shop_order_refund')
                ->add($refund_info);
        $re2 = $model
                ->table(C('DB_PREFIX') . 'ewei_shop_order')
                ->where("uniacid=%d and openid='%s' and id=%d", array($this->token, $openid, $id))
                ->save(array('refundid' => $re1));
        if ($re1 && $re2) {
            $model->commit();
            $this->outFormat(array('status' => 1), 'ok', 0, 'json');
        } else {
            $model->rollback();
            $this->outFormat('null', '操作失败', 5020, 'json');
        }
    }

    /**
     * 获取订单编号
     * @param type $table
     * @param type $field
     * @param type $prefix
     * @return type
     */
    protected function createNO($table, $field, $prefix) {
        $billno = $prefix . date('YmdHis') . rand(100001, 999999);
        while (1) {
//            查询订单编号的数量
            $count = M('ewei_shop_' . $table)
                    ->where(array("$field" => $billno))
                    ->count();
            if ($count <= 0) {
                break;
            }
            $billno = $prefix . date('YmdHis') . rand(100001, 999999);
        }
        return $billno;
    }

    /**
     * 获取配置信息
     * @param type $token
     */
    protected function getsysset($token, $field) {
        $data = M('ewei_shop_sysset')->where('uniacid=%d', array($token))->getField('sets');
        $info = unserialize($data);
//        return $info['trade'];
        return $info[$field];
    }

//    退款页面--显示
    public function reflist() {
        $openid = I('get.openid', '', 'string');
        $this->getMember($this->uniacid, $openid);
        $id = I('get.id', '', 'intval'); //订单主键id
        if (empty($id)) {
            $this->outFormat('null', '参数错误', 5021, 'json');
        }
        $order = $this->order
                ->field('id,status,price,deductcredit2,dispatchprice,refundid')
                ->where("uniacid=%d and openid='%s' and id=%d", array($this->uniacid, $openid, $id))
                ->find();
        if (empty($order) || $order['status'] != 1 && $order['status'] != 3) {
            $this->outFormat('null', '非法操作', 5022, 'json');
        }

        $info = $this->getsysset($this->uniacid, 'trade');
        $explain = $info['refundcontent'];
        $data['id'] = $order['id'];
        $data['status'] = $order['status'];
        $data['price'] = $order['price'] + $order['deductcredit2'];
        if ($order['status'] >= 3) {
//            减掉运费
            $data['price'] -= $order['dispatchprice'];
        }
        $data['explain'] = $explain;
        if ($order['refundid']) {
            $data['content'] = M('ewei_shop_order_refund')->where('id=%d', array($order['refundid']))->getField('content');
        }
        $this->outFormat($data, 'ok', 0, 'json');
    }

//    正在退款中--页面
    public function refuning() {
        $openid = I('get.openid', '', 'string');
        $this->getMember($this->uniacid, $openid);
        $id = I('get.id', '', 'intval'); //订单主键id
        if (empty($id)) {
            $this->outFormat('null', '参数错误', 5023, 'json');
        }
        $order = $this->order
                ->field('id,status,price,refundid')
                ->where("uniacid=%d and openid='%s' and id=%d", array($this->uniacid, $openid, $id))
                ->find();
        if (empty($order) || $order['status'] != 1 && $order['status'] != 3 || $order['refundid'] == 0) {
            $this->outFormat('null', '非法操作', 5024, 'json');
        }
        $data = M('ewei_shop_order_refund')
                        ->field('reason,content,createtime')
                        ->where('id=%d', array($order['refundid']))->find();
        $data['createtime'] = date('Y-m-d H:i');
        $data['id'] = $id;
        $this->outFormat($data, 'ok', 0, 'json');
    }

    /**
     * 取消退款申请
     */
    public function refcancel() {
//        退款申请表变为-1
//        订单表状态变为refundid = 0
        $openid = I('post.openid', '', 'string');
        $this->getMember($this->token, $openid);
        $id = I('post.id', '', 'intval'); //订单主键id
        if (empty($id)) {
            $this->outFormat('null', '参数错误', 5025, 'json');
        }
        $order = $this->order
                ->field('id,status,price,refundid')
                ->where("uniacid=%d and openid='%s' and id=%d", array($this->token, $openid, $id))
                ->find();
        if (empty($order) || $order['status'] != 1 && $order['status'] != 3 || $order['refundid'] == 0) {
            $this->outFormat('null', '非法操作', 5026, 'json');
        }
//        开启事物
        $model = M();
        $model->startTrans();
        $re1 = $model
                ->table(C('DB_PREFIX') . 'ewei_shop_order_refund')
                ->where('id=%d', array($order['refundid']))
                ->save(array('status' => -1));
        $re2 = $model
                ->table(C('DB_PREFIX') . 'ewei_shop_order')
                ->where("uniacid=%d and openid='%s' and id=%d", array($this->token, $openid, $id))
                ->save(array('refundid' => 0));
        if ($re1 && $re2) {
            $model->commit();
            $this->outFormat(array('status' => 1), 'ok', 0, 'json');
        } else {
            $model->rollback();
            $this->outFormat('null', '操作失败', 5027, 'json');
        }
    }

    /**
     * 修改退款申请
     */
    public function edrefund() {
        $openid = I('post.openid', '', 'string');
        $reason = I('post.reason'); //退款原因
        $content = I('post.content'); //备注
        $this->getMember($this->token, $openid); //判断用户是否存在
        $id = I('post.id', 0, 'intval'); //订单主键id
        if (empty($id) || empty($reason)) {
            $this->outFormat('null', '参数错误', 5028, 'json');
        }
        $order = $this->order
                ->field('id,status,price,refundid,price,dispatchprice,deductcredit2')
                ->where("uniacid=%d and openid='%s' and id=%d", array($this->token, $openid, $id))
                ->find();
        if (empty($order) || $order['status'] != 1 && $order['status'] != 3 || $order['refundid'] == 0) {
            $this->outFormat('null', '非法操作', 5029, 'json');
        }

        $order['refundprice'] = $order['price'] + $order['deductcredit2'];
        if ($order['status'] >= 3) {
//            减掉运费
            $order['refundprice'] -= $order['dispatchprice'];
        }
//        开始写数据库操作
        $refundno = $this->createNO('order_refund', 'refundno', 'SR');
        $refund_info = array(
            'refundno' => $refundno,
            'price' => $order['refundprice'],
            'reason' => $reason,
            'content' => $content,
        );
        $result = M('ewei_shop_order_refund')
                ->where('id=%d', array($order['refundid']))
                ->save($refund_info);
        if ($result !== false) {
            $this->outFormat(array('status' => 1), 'ok', 0, 'json');
        } else {
            $this->outFormat('null', '失败操作', 5030, 'json');
        }
    }

    /**
     * 订单支付页面---过滤不能支付的商品
     */
    public function pay() {
        $openid = I('post.openid', '', 'string'); //openid
        $id = I('post.id', '', 'intval'); //订单主键id
        $this->getMember($this->token, $openid);
        if (empty($id)) {
            $this->outFormat('null', '参数错误', 5031, 'json');
        }
        $order = $this->order
                ->field('id,status,price,ordersn')
                ->relation(true)
                ->where("uniacid=%d and openid='%s' and id=%d", array($this->token, $openid, $id))
                ->find();

        if (empty($order) || $order['status'] != 0) {
            $this->outFormat('null', '非法操作', 5032, 'json');
        }
        $goods = M('ewei_shop_goods');

        $details = array();
        foreach ($order['OrderGoods'] as &$v) {

            $goodsinfo = $goods
                    ->field('title,total,status,unit,deleted,maxbuy,usermaxbuy,istime,timestart,timeend')
                    ->where('id=%d', array($v['goodsid']))
                    ->find();
//            判断商品状态
//            商品不存在
            if (empty($goodsinfo)) {
                $this->outFormat('null', '非法操作', 5033, 'json');
            }
//            商品已下架
            if (empty($goodsinfo['status']) || !empty($goodsinfo['deleted'])) {
                $this->outFormat('null', $goodsinfo['title'] . '已下架!', 5034, 'json');
            }
//            一次限购数量
//            if ($goodsinfo['maxbuy'] > 0) {
//                if ($v['total'] > $goodsinfo['maxbuy']) {
//                    $this->outFormat('null', $goodsinfo['title'] . '一次限购' . $goodsinfo['unit'], 404, 'json');
//                }
//            }
//            查看限购时间
            if ($goodsinfo['istime'] == 1) {
                if (time() < $goodsinfo['timestart']) {
                    $this->outFormat('null', $goodsinfo['title'] . '限购时间未到!', 5035, 'json');
                }
                if (time() > $goodsinfo['timeend']) {
                    $this->outFormat('null', $goodsinfo['title'] . '限购时间已过!', 5036, 'json');
                }
//            库存不用判断-----下单时直接减掉库存了
            }

//            算出商品单价
            $v['price'] = sprintf("%.2f", $v['price'] / $v['total']);

//            $details .= '名称:'.$v['Goods']['title'] . '，价格：' . $v['price'] .' 元，数量：' . $v['total'] .' ;';
            $details[] = array(
                'goods_id' => $v['goodsid'],
                'goods_name' => $v['Goods']['title'],
                'price' => $v['price'] * 100,
                'quantity' => (int) $v['total'],
            );
        }
//        获取店家名称
        $tenant = $this->getsysset($this->token, 'shop');
//        $order['name'] = $tenant['name'];
        $body = $tenant['name'] . '-微动商城，消费总计：' . $order['price'] . '元';
//        回调地址
//        $order['notify'] = 'https://'.$_SERVER['HTTP_HOST'].'/Shop/Order/notify';
        $data = array(
            'id' => $order['id'],
            'status' => $order['status'],
            'price' => $order['price'],
            'ordersn' => $order['ordersn'],
            'notify' => 'https://' . $_SERVER['HTTP_HOST'] . '/Shop/Order/notify',
            'name' => $tenant['name'],
            'body' => $body,
            'details' => json_encode(array('goods_detail' => $details)),
        );
        $this->outFormat($data, 'ok', 0, 'json');
    }

    /**
     * 支付回调
     */
    public function notify() {
//        修改订单状态
//        添加支付记录
        $xml = $GLOBALS['HTTP_RAW_POST_DATA'];
//        $xml = '<xml><appid><![CDATA[wx17bdd0797c3b43a7]]></appid>
//<attach><![CDATA[12]]></attach>
//<bank_type><![CDATA[CFT]]></bank_type>
//<cash_fee><![CDATA[1]]></cash_fee>
//<fee_type><![CDATA[CNY]]></fee_type>
//<is_subscribe><![CDATA[N]]></is_subscribe>
//<mch_id><![CDATA[1444874802]]></mch_id>
//<nonce_str><![CDATA[efryrhd1pf696efb7hbm8c4vcqrjxwql]]></nonce_str>
//<openid><![CDATA[oy8II0W0u_3Ku2Jco122v5Q9sK7Q]]></openid>
//<out_trade_no><![CDATA[SH201703301100330524]]></out_trade_no>
//<result_code><![CDATA[SUCCESS]]></result_code>
//<return_code><![CDATA[SUCCESS]]></return_code>
//<sign><![CDATA[22668DDFC07D26518B4E2F376F22AF65]]></sign>
//<time_end><![CDATA[20170330114054]]></time_end>
//<total_fee>1</total_fee>
//<trade_type><![CDATA[JSAPI]]></trade_type>
//<transaction_id><![CDATA[4005292001201703305151828646]]></transaction_id>
//</xml>';
//        $commission = A('Commission/Common');
//        $set = $commission->getSet(66, 'commission');
//        if ($set['level'] > 0) {
//            $orderprocess = A('Commission/Orderprocess');
//
//            $orderprocess->checkOrderPay(1606, 66);
//        }
//        
//        die;
        $data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), TRUE);
        $result = $data['return_code'];
        $wx_sign = $data['sign'];//微信返回的
        
        if ($result == 'SUCCESS') {
            file_put_contents('lxj_log.txt', $xml);
            $ordersn = $data['out_trade_no']; //商城订单号
            $openid = $data['openid']; //用户标识
            $cash_fee = $data['cash_fee'] * 0.01; //支付金额
            $order = $this->order
                    ->field('id,uniacid,openid,ordersn,price,status')
                    ->where("openid='%s' and ordersn='%s'", array($openid, $ordersn))
                    ->find();
//            验证签名
            $sign = $this->checkSign($data, $order['uniacid']);

            if ($order['status'] == 0 && $order['price'] == $cash_fee && $wx_sign == $sign) {
                $wxorder = $data['transaction_id']; //微信订单号
                $endtime = strtotime($data['time_end']); //订单支付时间
//                删除掉公众号支付页面提交的数据
                $tag = array(
                    'transaction_id' => $wxorder,
                );
//                修改订单状态
                $log = M('core_paylog')->field('plid')->where(array('uniacid' => $order['uniacid'], 'tid' => $ordersn))->find();
                if ($log) {
                    M('core_paylog')->where(array('plid' => $log['plid']))->delete();
                }
                $data1 = array(
                    'status' => 1,
                    'paytype' => 21,
                    'paytime' => $endtime,
                );

                $uid = M('ewei_shop_member')->where("openid='%s'", array($openid))->getField('uid');
                $data2 = array(
                    'type' => 'wechat',
                    'fee' => $order['price'],
                    'status' => 1,
                    'module' => 'ewei_shop',
                    'createtime' => time(),
                    'tid' => $ordersn,
                    'uniacid' => $order['uniacid'],
                    'tag' => serialize($tag),
                    'openid' => $uid,
                );
//                开启事物
                $model = M();
                $model->startTrans();
                $re1 = $model
                        ->table(C('DB_PREFIX') . 'ewei_shop_order')
                        ->where('id=%d', array($order['id']))
                        ->save($data1);
                $re2 = $model
                        ->table(C('DB_PREFIX') . 'core_paylog')
                        ->add($data2);
                if ($re1 && $re2) {
                    $model->commit();
//                    打印机
                    $this->printOrder($order['uniacid'], $order['id']);
//                    分销商操作
                    $commission = A('Commission/Common');
                    $set = $commission->getSet($order['uniacid'], 'commission');
                    if ($set['level'] > 0) {
                        $orderprocess = A('Commission/Orderprocess');
                        $orderprocess->checkOrderPay($order['id'], $order['uniacid']);
                    }
                    echo "SUCCESS";
                } else {
                    $model->rollback();
                }
            } else {
                echo "SUCCESS";
            }
        }else{
            echo "SUCCESS";
        }
    }

    /**
     * 支付成功订单打印
     * @param int $token
     * @param int $orderid
     */
    protected function printOrder($token, $orderid) {
//        $url = 'https://'.$_SERVER['HTTP_HOST'].'/Base/Print/print_start';
        $url = 'https://wxapis.vdongchina.com/Base/print/print_start';
//        商家名称
        $corp = $this->getsysset($token, 'shop');
        $order = $this->order
                ->field('id,ordersn,price,address,dispatchprice,discountprice,deductenough')
                ->relation(true)
                ->where(array('id' => $orderid))
                ->find();
        $address = unserialize($order['address']);
        $data = array();
        $data['orderInfo'] = array();
        $data['orderInfo']['corpname'] = $corp['name']; //企业名称
        $data['orderInfo']['orderno'] = $order['ordersn']; //订单名称
        $data['orderInfo']['username'] = $address['realname'];  //购买人姓名
        $data['orderInfo']['usertel'] = $address['mobile']; //购买人电话
        $data['orderInfo']['useraddr'] = $address['province'] . $address['city'] . $address['area'] . $address['address']; //地址
        $data['orderInfo']['time'] = date('Y-m-d H:i', time()); //时间
        $data['orderDetail'] = array();
        $detail = array();
        foreach ($order['OrderGoods'] as $value) {
            $detail[] = array(
                'name' => $value['optionname'] ? $value['Goods']['title'] . '(' . $value['optionname'] . ')' : $value['Goods']['title'],
                'price' => $value['price'] / $value['total'],
                'count' => $value['total']
            );
        }
        $data['orderDetail'] = $detail;
//        $data['orderCut'] = array('type' => '1', 'num' => 8); //type=1-2,1-折扣,2-优惠
        $data['orderCut'] = array('type' => '2', 'num' => $order['discountprice'] + $order['deductenough'], 'fee' => $order['dispatchprice']);
        $content = getOrderContent($data, 1);
        $pot = array(
            'module' => 'shop',
            'niacid' => $token,
            'system' => 'shop',
            'order' => $order['ordersn'],
            'content' => urlencode($content),
        );
        http_curl($url, $pot);
    }

    /**
     * 添加订单评价  litianyou @ 2/22
     */
    public function commentadd() { //页面  litianyou @ 2/22
        $data['uniacid'] = I('uniacid'); //商户id 66
        $data['openid'] = I('openid'); //openid oJ17UjmkDWm6KWpuysl1t3WAorjw
        $data['orderid'] = I('orderid'); //订单id
        $aa = D('EweiShopOrder');
        $a = $aa->commentadd($data);
        $this->response($a, 'json');
    }

    /**
     * P ：添加评价的时候 如果有图片上传的话 需要先上传到 七牛云 上，然后返回在七牛云上的该图片的地址，如果是多个图片的话，图片地址用 ‘，’ 隔开 用thumb参数传过来
     * */
    public function commentadding() {//添加评价   litianyou @ 2/22 http://www.wd.com/?c=order&a=commentadding&thumb=img&uniacid=66&openid=oJ17UjmkDWm6KWpuysl1t3WAorjw&content=123123123&level=5&headimgurl=www.sad.com/qeq.jpg&goodsid=123&orderid=22
        $data['uniacid'] = I('uniacid'); //商户id 66
        if (empty($data['images'])) {
            $data['images'] = serialize(array());
        } else {
            $data['images'] = serialize(explode(',', I('thumb'))); //图片地址
        }
        $data['openid'] = I('openid'); //openid
        //$data['nickname'] = I('nickname'); //用户昵称
        //$data['headimgurl'] = serialize(explode(',', I('headimgurl'))); //用户头像地址
        $data['goodsid'] = I('goodsid'); //商品ID
        $data['orderid'] = I('orderid'); //订单id
        $data['level'] = I('level'); //评级等级
        $data['content'] = I('content'); //评级内容
        $data['createtime'] = time(); //创建时间
        //wprint_r($data);die;
        $aa = D('EweiShopOrder');
        $a = $aa->commentadding($data);
        $this->response($a, 'json');
    }

    public function commentaddingz() {//追加评价   litianyou @ 2/22 http://www.wd.com/?c=order&a=commentadding&thumb=img&uniacid=66&openid=oJ17UjmkDWm6KWpuysl1t3WAorjw&content=123123123&level=5&headimgurl=www.sad.com/qeq.jpg&goodsid=123&orderid=22
        $data['uniacid'] = I('uniacid'); //商户id 66
        $data['reply_images'] = serialize(explode(',', I('thumb'))); //图片地址
        $data['openid'] = I('openid'); //openid
        $data['orderid'] = I('orderid'); //订单id
        $data['reply_content'] = I('content'); //评级内容
        $data['time'] = time(); //创建时间
        $aa = D('EweiShopOrder');
        $a = $aa->commentaddingz($data);
        $this->response($a, 'json');
    }

    public function orderadd() { //下单  litianyou @ 2/23
        $data['uniacid'] = I('uniacid'); //商户id 66
        $data['openid'] = I('openid'); //openid oJ17UjmkDWm6KWpuysl1t3WAorjw
        $data['memberid'] = I('memberid'); //购物车id 438
        $dispatch['dispatch'] = I('dispatch');
        $dispatch['uniacid'] = I('uniacid');
      if (empty($data['uniacid']) || empty($data['openid']) || empty($data['memberid'])) {
            $aa['meta'] = array('code' => '0', 'message' => "参数缺少");
            return $aa;
        }
        $User = M('ewei_shop_member_cart');
        $cart = $User->field('id')->where("uniacid = $data[uniacid] and openid = '$data[openid]' and id in ( $data[memberid]) and deleted = 0")->find();
        //return $User->getLastSql();
        if(empty($cart)){
            $aa['meta'] = array('code' => '0', 'message' => "非法操作");
            return $aa;
        }
        $sql = "SELECT ims_ewei_shop_member_cart.total,ims_ewei_shop_member_cart.id as goodscartid,ims_ewei_shop_goods.marketprice as goodsmarketprice,
ims_ewei_shop_goods_option.marketprice as opmarketprice,ims_ewei_shop_goods.title as goodsname,ims_ewei_shop_goods.thumb,ims_ewei_shop_goods.id as
 goodsid,ims_ewei_shop_goods_option.title,ims_ewei_shop_goods.total as goodskc,ims_ewei_shop_goods_option.stock from ims_ewei_shop_member_cart
 LEFT JOIN ims_ewei_shop_goods on ims_ewei_shop_member_cart.goodsid = ims_ewei_shop_goods.id
LEFT JOIN ims_ewei_shop_goods_option on ims_ewei_shop_goods_option.id=ims_ewei_shop_member_cart.optionid where openid =
'$data[openid]' and ims_ewei_shop_member_cart.deleted = 0 and ims_ewei_shop_member_cart.uniacid = $data[uniacid] and ims_ewei_shop_member_cart.id in ($data[memberid])";
        $arr = $User->query($sql); //查询所有的商品
        foreach ($arr as $k => &$v) {
            if ($v['opmarketprice'] != '') { //返回售价
                $v['marketprice'] = $v['opmarketprice']; //如果有属性，取属性里的价格
            } else {
                $v['marketprice'] = $v['goodsmarketprice']; //否则取 goods 表里的价格
            }
            if ($v['thumb'] != '') {
                $arr[$k]['thumb'] = C('IMAGE_RESOURCE') . '/' . $v['thumb'];
            }
            if ($v['goodskc'] <= 0) {
                $aa['meta'] = array('code' => '0', 'message' => "该订单中商品库存不足");
                return $aa;
            }
            unset($v['opmarketprice'],$v['goodsmarketprice']);
            $prices += $v['total'] * $v['marketprice'];
        }
        $aa['da'] = $arr;//商品数据
        $aa['prices'] = $prices; //所有加起来的钱
        //return $aa['prices'];
        $address = M('ewei_shop_member_address');
        $whereaddress = array(
            'openid' => $data['openid'],
            'uniacid' => $data['uniacid'],
            'isdefault' => 1,
            'deleted' => 0,
        );
        $aa['address'] = $address->where($whereaddress)->field('id,realname,mobile,address')->select(); //收货地址（取默认收货地址）
        $dispatchDb = D('EweiShopDispatch'); //运费
        $dispatchdata = $dispatchDb->getdispatch($dispatch);
        $aa['distribution'] = $dispatchdata;
        $aa['memberid'] = $data['memberid'];

        $mj = D('EweiShopSysset'); //满减
        $mj = $mj->getmj($data['uniacid']);
        if ($prices >= $mj['enoughmoney']) {
            $aa['marketprice'] = $prices - $mj['enoughdeduct'] + $dispatchdata;
            $aa['m'] = $mj['enoughmoney'];
            $aa['j'] = $mj['enoughdeduct'];
        }
        $aa['meta'] = array('code' => '1', 'message' => "调用成功");
           //print_r($a);die;
        $this->response($aa, 'json');
    }

    public function orderadddo() { //确认订单  litianyou @ 2/24
        $data['uniacid'] = I('uniacid'); //商户id 66
        $data['openid'] = I('openid'); //openid oJ17UjmkDWm6KWpuysl1t3WAorjw  oJ17UjgJ2CYLtPOKxt5xtbgMWOtA
        $data['memberid'] = I('memberid'); //购物车id 438
        $data['addressid'] = I('addressid'); //收货地址id 438
        $data['remark'] = I('remark'); //留言内容 438
         $dispatch['dispatch'] = I('dispatch');
        $dispatch['uniacid'] = I('uniacid');
        if (empty($data['uniacid']) || empty($data['openid']) || empty($data['memberid']) || empty($data['addressid'])) {
            $aa['meta'] = array('code' => '0', 'message' => "参数缺少");
            $this->response($aa, 'json');
            exit;
        }
        $member_cart = M('ewei_shop_member_cart');
        $cart = $member_cart->field('id')->where("uniacid = $data[uniacid] and openid = '$data[openid]' and id in ($data[memberid]) and deleted = 0")->select();
        if (empty($cart)) {
            $aa['meta'] = array('code' => '0', 'message' => "非法操作");
            $this->response($aa, 'json');
            exit;
        }
        $sql = "SELECT ims_ewei_shop_member_cart.total,ims_ewei_shop_member_cart.id as goodscartid,ims_ewei_shop_goods.marketprice as goodsmarketprice,
ims_ewei_shop_goods_option.marketprice as opmarketprice,ims_ewei_shop_goods.title as goodsname,ims_ewei_shop_goods.thumb,ims_ewei_shop_goods.id as
 goodsid,ims_ewei_shop_goods_option.title,ims_ewei_shop_goods_option.id,ims_ewei_shop_goods.total as goodskc,ims_ewei_shop_goods_option.stock from ims_ewei_shop_member_cart
 LEFT JOIN ims_ewei_shop_goods on ims_ewei_shop_member_cart.goodsid = ims_ewei_shop_goods.id
LEFT JOIN ims_ewei_shop_goods_option on ims_ewei_shop_goods_option.id=ims_ewei_shop_member_cart.optionid where openid =
'$data[openid]' and ims_ewei_shop_member_cart.deleted = 0 and ims_ewei_shop_member_cart.uniacid = $data[uniacid] and ims_ewei_shop_member_cart.id in ($data[memberid])";
        $data1 = $member_cart->query($sql);
        foreach ($data1 as $k => &$v) {
            if ($v['opmarketprice'] != '') {
                $data1[$k]['marketprice'] = $v['opmarketprice'];
            } else {
                $data1[$k]['marketprice'] = $v['goodsmarketprice'];
            }
            if ($v['thumb'] == '') {
                $v['thumb'] = null;
            } else {
                $data1[$k]['thumb'] = C('IMAGE_RESOURCE') . '/' . $v['thumb'];
            }
            unset($v['opmarketprice'], $v['goodsmarketprice']);
            if ($v['goodskc'] < $v['total']) {
                $aa['meta'] = array('code' => '0', 'message' => "该订单中商品库存不足");
                return $aa;
            }
            $prices += $v['total'] * $v['marketprice'];
        }
        $aa['da'] = $data1;
        $aa['prices'] = $prices;

         $dispatchDb = D('EweiShopDispatch'); //运费
        $dispatchdata = $dispatchDb->getdispatch($dispatch);

        $dispatchprice = $dispatchdata; //运费
        $pricesy = $prices + $dispatchprice; //原价

        $mj = D('EweiShopSysset'); //满减
        $mj = $mj->getmj($data['uniacid']);
        if ($pricesy >= $mj['enoughmoney']) {
            $prices = $pricesy - $mj['enoughdeduct'];
            $aa['m'] = $mj['enoughmoney'];
            $aa['j'] = $mj['enoughdeduct'];
        } else {
            $prices = $pricesy;
            $aa['j'] = '0';
        }
        if (empty($data['addressid'])) {
            $aa['meta'] = array('code' => '2', 'message' => "请选择收货地址");
            return $aa;
        }
        /* $wheregwc = array(
          'id' => "in($data[memberid])",
          'uniacid' => $data['uniacid'],
          'openid' => $data['openid']
          ); */
        $wheregwc['id'] = array('in', ($data['memberid']));
        $wheregwc['uniacid'] = $data['uniacid'];
        $wheregwc['openid'] = $data['openid'];
        $gwcdel['deleted'] = '1';
        $model = M();
        $model->startTrans();
        $re1 = $member_cart->where($wheregwc)->setField($gwcdel);  //删除购物车
        $aa['prices'] = $prices;
        $address = M('ewei_shop_member_address');
        $whereaddress = array(//收货地址
            'openid' => $data['openid'],
            'uniacid' => $data['uniacid'],
            'id' => $data['addressid'],
            'deleted' => 0,
        );
        $address = $address->where($whereaddress)->field('id,realname,mobile,province,city,area,address')->find(); //收货地址
        $arr = array();
        if (!empty($address)) {
            $b = serialize($address);
        } else {
            $b = '';
        }
        $ar = array();
        while (count($ar) < 6) {
            $ar[] = rand(0, 9);
            $ar = array_unique($ar);
        }
        $arr['ordersn'] = 'SH' . date('YmdHis') . implode("", $ar); //订单号
        $arr['openid'] = $data['openid'];  //openid
        $arr['uniacid'] = $data['uniacid']; //商户id
        $arr['dispatchprice'] = $dispatchprice; //运费
        $arr['oldprice'] = $prices;
        $arr['olddispatchprice'] = $dispatchprice; //原始运费
        $arr['goodsprice'] = $aa['prices']; // 原始订单金额
        $arr['price'] = $prices; //支付订单金额
        $arr['discountprice'] = $aa['j']; //优惠金额
        $arr['remark'] = $data['remark']; //留言
        $arr['addressid'] = $data['addressid']; //收货地址id
        $arr['createtime'] = time(); //下单时间
        $arr['address'] = $b; //收货地址
        //dump($arr);die;
        //p($arr);die;
        $order = M('ewei_shop_order');
        $orderadd = $order->data($arr)->add();  // 添加到订单表
        if ($re1 && $orderadd) {
            $orderg = M('ewei_shop_order_goods');  //
            //return $data1;
            foreach ($data1 as $q => $w) {
                $ordergoods['uniacid'] = $data['uniacid']; //商户id
                $ordergoods['orderid'] = $orderadd; //订单id
                $ordergoods['goodsid'] = $w['goodsid']; //商品id
                $ordergoods['price'] = $ordergoods['oldprice'] = $w['marketprice']; //商品单价
                $ordergoods['total'] = $w['total']; //数量
                $ordergoods['optionid'] = $w['id']; //属性id
                $ordergoods['createtime'] = time(); //创建时间
                $ordergoods['optionname'] = $w['title']; //属性名称
                $ordergoods['realprice'] = $w['marketprice'] * $w['total']; //属性名称
                $orderg->add($ordergoods);
            }
            $model->commit();
            $orderaddo['orderid'] = $orderadd;
            $orderaddo['meta'] = array('code' => '1', 'message' => "调用成功");
            $this->response($orderaddo, 'json');
        } else {
            $model->rollback();
            $orderaddo['meta'] = array('code' => '0', 'message' => "添加订单失败");
            $this->response($orderaddo, 'json');
        }
    }
    
    /**
     * 验证签名
     * @param type $xml
     * @param type $token
     * @return type
     */
    public function checkSign($data,$token) {
        $key = M('uni_wxapps')->where(array('uniacid' =>$token, 'appId' => $data['appid']))->getField('appkey');
        $w_sign = array();           //参加验签签名的参数数组                     
        $w_sign['appid'] = $data['appid'];
        $w_sign['attach'] = $data['attach'];
        $w_sign['bank_type'] = $data['bank_type'];
        $w_sign['cash_fee'] = $data['cash_fee'];
        $w_sign['fee_type'] = $data['fee_type'];
        $w_sign['is_subscribe'] = $data['is_subscribe'];
        $w_sign['mch_id'] = $data['mch_id'];
        $w_sign['nonce_str'] = $data['nonce_str'];
        $w_sign['openid'] = $data['openid'];
        $w_sign['out_trade_no'] = $data['out_trade_no'];
        $w_sign['result_code'] = $data['result_code'];
        $w_sign['return_code'] = $data['return_code'];
        $w_sign['time_end'] = $data['time_end'];
        $w_sign['total_fee'] = $data['total_fee'];
        $w_sign['trade_type'] = $data['trade_type'];
        $w_sign['transaction_id'] = $data['transaction_id'];
        $sign = $this->makeSign($w_sign, $key);
        return $sign;
    }

}
