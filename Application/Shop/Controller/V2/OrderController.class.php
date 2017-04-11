<?php

/*
 * lxj
 * 20170217
 * 18046521228
 */

namespace Shop\Controller\V2;
use Shop\Controller\V2\CommonController;
//5000

class OrderController extends CommonController {

    protected $order;
    
    protected $orders;

    public function __construct() {
        parent::__construct();
        $this->order = D('Order');
        $this->orders = D('EweiShopOrder');
    }

//    订单数据
    protected function orderData($openid, $status, $limit) {
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

        $count = $this->orders->getOrderCount($where);
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
        $openid = I('get.openid', '', 'string');
        $this->getMember($this->uniacid, $openid);
        $status = I('get.status'); //订单状态
        switch ($status) {
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
            $this->outFormat('', '参数格式错误', 5001);
        }
        $data = $this->orderData($openid, $status, $limit);
        if ($data['data']) {
            $this->outFormats($data['data'], 'ok', $p, $data['totalPage'], 0);
        } else {
            $this->outFormats(array(), '没有订单了！', $p, $data['totalPage'], 0);
        }
    }

    /**
     * 订单状态---已完善
     */
    public function orderStatus() {
        $openid = I('get.openid', '', 'string');
        $this->getMember($this->uniacid, $openid);
        $where = array(
            'uniacid'   =>$this->uniacid,
            'openid'    =>$openid,
            'status'    =>array('egt',0),
            'userdeleted'   =>0
        );
        $count = $this->orders->getOrderCount($where);
        $order = $this->orders->getByWhereSelect($where,'id,status,refundid');
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

        $this->outFormat($data, 'ok', 0);
    }

    /**
     * 订单详情
     */
    public function orderinfo() {
        $openid = I('get.openid', '', 'string');
        $id = I('get.id', '', 'intval');
        if (empty($id)) {
            $this->outFormat('', '参数错误', 5002);
        }
        $this->getMember($this->uniacid, $openid);
        $where = array(
            'uniacid' => $this->uniacid,
            'openid' => $openid,
            'id' => $id,
            'userdeleted' => 0,
        );
        $field = 'id,ordersn,price,goodsprice,status,refundid,discountprice,dispatchprice,address,isverify,virtual,finishtime,iscomment';
        $order = $this->order
                ->field($field)
                ->relation(true)
                ->where($where)
                ->find();

        if (empty($order)) {
            $this->outFormat('', '非法操作', 5003);
        }
        foreach ($order['OrderGoods'] as &$val) {
            $val['Goods']['thumb'] = getImgUrl($val['Goods']['thumb']);
        }
//        $sys = $this->getSysset('shop',$this->uniacid);
//        $order['name'] = $sys['name'];
        $order['finishtime'] = $order['finishtime'] ? date('Y-m-d H:i:s', $order['finishtime']) : '';
        $order['address'] = unserialize($order['address']);
        unset($order['address']['province'], $order['address']['city'], $order['address']['area']);
        $this->outFormat($order, 'ok', 0);
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
            $this->outFormat('', '参数错误', 5004);
        }
        $order = $this->order
                ->field('id,ordersn,openid,status,deductcredit,deductprice')
                ->relation(true)
                ->where("uniacid=%d and openid='%s' and id=%d", array($this->token, $openid, $id))
                ->find();
        if (empty($order) || $order['status'] != 0) {
            $this->outFormat('', '非法操作', 5005);
        }
        $model = M();
        $model->startTrans();
        $re1 = $model
                ->table(C('DB_PREFIX') . 'ewei_shop_order')
                ->where("uniacid=%d and openid='%s' and id=%d", array($this->token, $openid, $id))
                ->save(array('status' => -1, 'canceltime' => time()));
//        将商品数量的库存恢复
        $goods = M('ewei_shop_goods');
        foreach ($order['OrderGoods'] as $value) {
//            加库存
            $re2 = $model->table(C('DB_PREFIX') . 'ewei_shop_goods')
                    ->where('id=%d', array($value['goodsid']))
                    ->setInc('total',$value['total']);
        }
        if ($re1 && $re2) {
            $model->commit();
            $this->outFormat(array('num' => 1), 'ok', 0);
        } else {
            $model->rollback();
            $this->outFormat('', '操作失败', 5006);
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
            $this->outFormat('', '参数错误', 5007);
        }
        $order = $this->orders->getByidFind($this->token,$openid,$id,'id,status,userdeleted');

        if (empty($order) || $order['status'] != 3 && $order['userdeleted'] != 0) {
            $this->outFormat('', '非法操作', 5008, 'json');
        }
        $result = $this->orders->updateByWhere(array('id'=>$id),array('userdeleted' => 1));
        if ($result) {
            $this->outFormat(array('num' => 1), 'ok', 0);
        } else {
            $this->outFormat('', '操作失败', 5009);
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
            $this->outFormat('', '参数错误', 5010);
        }
        $order = $this->order
                ->field('id,status,openid')
                ->relation(true)
                ->where("uniacid=%d and openid='%s' and id=%d", array($this->token, $openid, $id))
                ->find();
        if (empty($order) || $order['status'] != 2) {
            $this->outFormat('', '非法操作', 5011);
        }
        $result = $this->orders->updateByWhere(array('id'=>$id),array('status' => 3, 'finishtime' => time()));
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
            $this->outFormat(array('num' => 1), 'ok', 0);
        } else {
            $this->outFormat('', '操作失败', 5012);
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
            $this->outFormat('', '参数错误', 5013);
        }
        $order = $this->order
                ->field('id,status,expresscom,expresssn,sendtime')
                ->relation(true)
                ->where("uniacid=%d and openid='%s' and id=%d", array($this->token, $openid, $id))
                ->find();
        if (empty($order) || $order['status'] != 2) {
            $this->outFormat('', '非法操作', 5014);
        }
        $order['sendtime'] = date('Y-m-d H:i:s', $order['sendtime']);

        foreach ($order['OrderGoods'] as &$val) {
            $val['Goods']['thumb'] = getImgUrl($val['Goods']['thumb']);
        }
        $this->outFormat($order, 'ok', 0);
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
            $this->outFormat('', '参数错误', 5015);
        }
        $order = $this->orders->getByidFind($this->token,$openid,$id,'id,status,price,refundid,virtual,isverify,finishtime,dispatchprice,deductprice,deductcredit2');

        if (empty($order) || $order['status'] != 1 && $order['refundid'] != 0 || $order['status'] != 3 && $order['refundid'] != 0) {
            $this->outFormat('', '非法操作', 5016);
        }
//        如果已确认收货要判断是否可以退货
        if ($order['status'] == 3) {
            if (!empty($order['virtual']) || $order['isverify'] == 1) {
                $this->outFormat('', '此订单不允许退款', 5017);
            }
//            获取配置信息
            $sys = $this->getSysset('trade',$this->token);

//            退款天数
            $refunddays = intval($sys['refunddays']);
            if ($refunddays > 0) {
                $days = intval((time() - $order['finishtime']) / 3600 / 24);
                if ($days > $refunddays) {
                    $this->outFormat('', '订单完成已超过 ' . $refunddays . ' 天, 无法发起退款申请!', 5018);
                }
            } else {
                $this->outFormat('', '订单完成, 无法申请退款!', 5019);
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
            $this->outFormat(array('num' => 1), 'ok', 0);
        } else {
            $model->rollback();
            $this->outFormat('', '操作失败', 5020);
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


//    退款页面--显示
    public function reflist() {
        $openid = I('get.openid', '', 'string');
        $this->getMember($this->uniacid, $openid);
        $id = I('get.id', '', 'intval'); //订单主键id
        if (empty($id)) {
            $this->outFormat('', '参数错误', 5021);
        }
        $order = $this->orders->getByidFind($this->uniacid,$openid,$id,'id,status,price,deductcredit2,dispatchprice,refundid');

        if (empty($order) || $order['status'] != 1 && $order['status'] != 3) {
            $this->outFormat('', '非法操作', 5022);
        }

        $info = $this->getSysset('trade',$this->uniacid);
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
             $data['content'] = D('EweiShopOrderRefund')->getRefundField(array('id'=>$order['refundid']),'content');
        }
        $this->outFormat($data, 'ok', 0);
    }

//    正在退款中--页面
    public function refuning() {
        $openid = I('get.openid', '', 'string');
        $this->getMember($this->uniacid, $openid);
        $id = I('get.id', '', 'intval'); //订单主键id
        if (empty($id)) {
            $this->outFormat('', '参数错误', 5023);
        }
        $order = $this->orders->getByidFind($this->uniacid,$openid,$id,'id,status,price,refundid');
        if (empty($order) || $order['status'] != 1 && $order['status'] != 3 || $order['refundid'] == 0) {
            $this->outFormat('', '非法操作', 5024);
        }
        $data = D('EweiShopOrderRefund')->getByWhere(array('id'=>$order['refundid']),'reason,content,createtime');
        $data['createtime'] = date('Y-m-d H:i');
        $data['id'] = $id;
        $this->outFormat($data, 'ok', 0);
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
            $this->outFormat('', '参数错误', 5025);
        }
        $order = $this->orders->getByidFind($this->token,$openid,$id,'id,status,price,refundid');
        if (empty($order) || $order['status'] != 1 && $order['status'] != 3 || $order['refundid'] == 0) {
            $this->outFormat('', '非法操作', 5026);
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
            $this->outFormat(array('num' => 1), 'ok', 0);
        } else {
            $model->rollback();
            $this->outFormat('', '操作失败', 5027);
        }
    }

    /**
     * 修改退款申请
     */
    public function edrefund() {
        $openid = I('post.openid', '', 'string');
        $reason = I('post.reason','','string'); //退款原因
        $content = I('post.content','','string'); //备注
        $this->getMember($this->token, $openid); //判断用户是否存在
        $id = I('post.id', 0, 'intval'); //订单主键id
        if (empty($id) || empty($reason)) {
            $this->outFormat('', '参数错误', 5028);
        }

        $order = $this->orders->getByidFind($this->token,$openid,$id,'id,status,price,refundid,price,dispatchprice,deductcredit2');

        if (empty($order) || $order['status'] != 1 && $order['status'] != 3 || $order['refundid'] == 0) {
            $this->outFormat('', '非法操作', 5029);
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
        $result = D('EweiShopOrderRefund')->updateRefund(array('id'=>$order['refundid']),$refund_info);
        if ($result !== false) {
            $this->outFormat(array('num' => 1), 'ok', 0);
        } else {
            $this->outFormat('', '失败操作', 5030);
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
            $this->outFormat('', '参数错误', 5031);
        }
        $order = $this->order
                ->field('id,status,price,ordersn')
                ->relation(true)
                ->where("uniacid=%d and openid='%s' and id=%d", array($this->token, $openid, $id))
                ->find();

        if (empty($order) || $order['status'] != 0) {
            $this->outFormat('', '非法操作', 5032);
        }
        $goods = D('EweiShopGoods');

        $details = array();
        $field = 'title,total,status,unit,deleted,maxbuy,usermaxbuy,istime,timestart,timeend';
        foreach ($order['OrderGoods'] as &$v) {

            $goodsinfo = $goods->findGoods(array('id'=>$v['goodsid']),$field);
//            判断商品状态
//            商品不存在
            if (empty($goodsinfo)) {
                $this->outFormat('', '非法操作', 5033);
            }
//            商品已下架
            if (empty($goodsinfo['status']) || !empty($goodsinfo['deleted'])) {
                $this->outFormat('', $goodsinfo['title'] . '已下架!', 5034);
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
                    $this->outFormat('', $goodsinfo['title'] . '限购时间未到!', 5035);
                }
                if (time() > $goodsinfo['timeend']) {
                    $this->outFormat('', $goodsinfo['title'] . '限购时间已过!', 5036);
                }
//            库存不用判断-----下单时直接减掉库存了
            }

//            算出商品单价
            $v['price'] = sprintf("%.2f", $v['price'] / $v['total']);

            $details[] = array(
                'goods_id' => $v['goodsid'],
                'goods_name' => $v['Goods']['title'],
                'price' => $v['price'] * 100,
                'quantity' => (int) $v['total'],
            );
        }
//        获取店家名称
        $tenant = $this->getSysset('shop',$this->token);
        $body = $tenant['name'] . '-微动商城，消费总计：' . $order['price'] . '元';

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
        $this->outFormat($data, 'ok', 0);
    }

    /**
     * 支付回调
     */
    public function notify() {
//        修改订单状态
//        添加支付记录
        $xml = $GLOBALS['HTTP_RAW_POST_DATA'];
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
            
            $order = $this->orders->getByWhereFind(array('openid'=>$openid,'ordersn'=>$ordersn),'id,uniacid,openid,ordersn,price,status');
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

        $url = 'https://wxapis.vdongchina.com/Base/print/print_start';
//        商家名称
        $corp = $this->getSysset('shop',$token);
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
            'content' => urlencode($content)
        );
        http_curl($url, $pot);
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
