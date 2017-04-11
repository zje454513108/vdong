<?php
namespace Commission\Controller;

use Commission\Controller\CommissionBaseController;

class LogController extends CommissionBaseController
{
    /**
     * $openid
     * $token 公众号标识
     * $status 状态 1=待审核 2=待打款 3=已打款 -1=无效  查询全部不传值
     */
    //获取分销明细列表
/*array(3) {
//分销列表
["list"]=>
array(1) {
[0]=>
array(16) {
["id"]=>
string(2) "19"
["uniacid"]=>
string(2) "66"
["applyno"]=>明细编号
string(22) "CA20170323164323856621"
["mid"]=>
string(5) "19513"
["type"]=>
string(1) "1"
["orderids"]=>
string(517) "a:10:{i:0;a:2:{s:7:"orderid";s:4:"1026";s:5:"level";i:2;}i:1;a:2:{s:7:"orderid";s:4:"1216";s:5:"level";i:2;}i:2;a:2:{s:7:"orderid";s:4:"1217";s:5:"level";i:2;}i:3;a:2:{s:7:"orderid";s:4:"1279";s:5:"level";i:2;}i:4;a:2:{s:7:"orderid";s:4:"1280";s:5:"level";i:2;}i:5;a:2:{s:7:"orderid";s:4:"1026";s:5:"level";i:3;}i:6;a:2:{s:7:"orderid";s:4:"1216";s:5:"level";i:3;}i:7;a:2:{s:7:"orderid";s:4:"1217";s:5:"level";i:3;}i:8;a:2:{s:7:"orderid";s:4:"1279";s:5:"level";i:3;}i:9;a:2:{s:7:"orderid";s:4:"1280";s:5:"level";i:3;}}"
["commission"]=>申请佣金
string(4) "1.50"
["commission_pay"]=>已支付佣金
string(4) "0.00"
["content"]=>
NULL
["status"]=>
string(1) "1"
["applytime"]=>
string(10) "1490258603"
["checktime"]=>
string(1) "0"
["paytime"]=>
string(1) "0"
["invalidtime"]=>
string(1) "0"
["statusstr"]=>审核状态状态
string(9) "待审核"
["dealtime"]=>申请时间
string(16) "2017-03-23 16:43"
}
}
条数
["total"]=>明细条数
  string(1) "1"
//预计佣金
  ["commissioncount"]=>
  float(1.5)
}*/
    public function getCommissionMoney()
    {
        $openid =$_POST['openid'];
        //每页显示条数
        $page_size=$_POST['size']?$_POST['size']:10;
        //页数
        $page=$_POST['page']?$_POST['page']:1;
        $uniacid =$this->token;
        //member表id
        $info = $this->getInfo($openid,$uniacid);
        $mid=$info['id'];
        $where['mid'] = $mid;
        $where['unicid'] = $mid;
        if(!empty($_POST['status'])){
            $where['status']=$_POST['status'];
        }
        $list = M('ewei_shop_commission_apply')->where($where)->order('id desc')->limit(($page-1)*$page_size,$page*$page_size)->select();
        $total = M('ewei_shop_commission_apply')->where($where)->count();

        $commissioncount = 0;
        foreach ($list as &$row) {
            $commissioncount += $row['commission'];
            if ($row['status'] == 1) {
                $row['statusstr'] = '待审核';
                $row['dealtime'] = date('Y-m-d H:i', $row['applytime']);
            } else if ($row['status'] == 2) {
                $row['statusstr'] = '待打款';
                $row['dealtime'] = date('Y-m-d H:i', $row['checktime']);
            } else if ($row['status'] == 3) {
                $row['statusstr'] = '已打款';
                $row['dealtime'] = date('Y-m-d H:i', $row['checktime']);
            } else if ($row['status'] == -1) {
                $row['dealtime'] = date('Y-m-d H:i', $row['invalidtime']);
                $row['statusstr'] = '无效';
            }
        }
        unset($row);
        $data['list'] = $list;
        $data['total'] = $total;
        $data['page_max']=ceil($total/$page_size);
        $data['commissioncount'] = $commissioncount;
        exit(json_encode($data, JSON_UNESCAPED_UNICODE));
    }
    /**
     *    [  [1026]=>
    array(5) {
    ["goods"]=>
    array(1) {
    [0]=>
    array(23) {
    ["orderid"]=>订单id
    string(4) "1026"
    ["id"]=>
    string(4) "1100"
    ["goodsid"]=>商品id
    string(3) "981"
    ["thumb"]=>
    string(52) "images/66/2016/12/P21212inNq6C426I161c170Y74CCGV.jpg"
    ["price"]=>商品
    string(5) "28.00"
    ["total"]=>
    string(1) "1"
    ["title"]=>商品名
    string(24) "30元话费-虚拟商品"
    ["optionname"]=>
    string(0) ""
    ["commission1"]=>
    string(97) "a:4:{s:7:"default";s:4:"0.28";s:7:"level32";s:1:"0";s:7:"level33";s:1:"0";s:7:"level34";s:1:"0";}"
    ["commission2"]=>
    string(97) "a:4:{s:7:"default";s:4:"0.17";s:7:"level32";s:1:"0";s:7:"level33";s:1:"0";s:7:"level34";s:1:"0";}"
    ["commission3"]=>
    string(78) "a:4:{s:7:"default";i:0;s:7:"level32";i:0;s:7:"level33";i:0;s:7:"level34";i:0;}"
    ["status1"]=>
    string(1) "2"
    ["status2"]=>
    string(1) "1"
    ["status3"]=>
    string(1) "1"
    ["content1"]=>
    string(0) ""
    ["content2"]=>
    NULL
    ["content3"]=>
    NULL
    ["commission"]=>佣金
    int(0)
    ["statusstr"]=>状态
    string(9) "待审核"
    ["dealtime"]=>
    string(16) "1970-01-01 08:00"
    ["status"]=>商品状态 1=待审核 2=待打款 3=已打款 -1=失效
    string(1) "1"
    ["content"]=>失效理由
    NULL
    ["level"]=>级别
    string(3) "三"
    }
    }
    ["ordercommission"]=>申请金额
    int(0)
    ["orderpay"]=>审核通过金额
    int(0)
    ["applyno"]=>明细号
    string(22) "CA20170323164323856621"
    ["ordersn"]=>订单号
    string(20) "SH201702160944356842"
    }
     */
    //获取分销明细 详情
    public function commissionMoneyDetails()
    {
        $openid = $this->openid;
        $uniacid =$this->token;
        $agentLevel = $this->getLevel($openid, $uniacid);
        //佣金订单id
        $id = $_POST['id'];
        $where['id'] = $id;
        $details = M('ewei_shop_commission_apply')->where($where)->find();
        if (empty($details)) {
            $data['code'] = 500;
            $data['msg'] = '未找到提现申请';
            $data['data'] = '';
            exit(json_encode($data, JSON_UNESCAPED_UNICODE));
        }
        $orderids = unserialize($details['orderids']);
        if (!is_array($orderids) || count($orderids) <= 0) {
            $data['code'] = 500;
            $data['msg'] = '未找到订单信息!';
            $data['data'] = '';
            exit(json_encode($data, JSON_UNESCAPED_UNICODE));
        }
        $ids = array();
        //订单id数组
        foreach ($orderids as $k => $o) {
            $ids[] = $o['orderid'];
            //$guess[$o['orderid']] = $orderids[$k];
        }
        $sql = 'select id,agentid,ordersn,price,goodsprice,dispatchprice,createtime, paytype from ims_ewei_shop_order   where id in(' . implode(",", $ids) . ')';
        //订单信息
        $list = M()->query($sql);
        foreach($list as $v){
            $orderlist[$v['id']]=$v;
        }
        $field = 'og.id,og.goodsid,g.thumb,og.price,og.total,g.title,og.optionname,og.commission1,og.commission2,og.commission3,og.status1,og.status2,og.status3,og.content1,og.content2,og.content3';
        //订单详情
        $result = $this->getOrderDetails($ids, $field);
        $totalcommission = 0;
        $totalpay = 0;
        $ordercommission = 0;
        $orderpay = 0;

        //遍历订单
        foreach ($result as $k=>$v) {
            //$row = $guess[$k];
            foreach($orderids as $value){
                if($value['orderid']=$k) {
                    $row['level'] = $value['level'];
                }
            }
            $row['level']=2;
            //遍历订单下所有商品
            foreach ($v as &$g) {
                if ($row['level'] == 1) {
                    $commission = unserialize($g['commission1']);

                    $g['commission'] = isset($commission['level' . $agentLevel['id']]) ? $commission['level' . $agentLevel['id']] : $commission['default'];
                    $totalcommission += $g['commission'];
                    $ordercommission += $g['commission'];
                    if ($g['status1'] >= 2) {
                        $totalpay += $g['commission'];
                        $orderpay += $g['commission'];
                    }
                }
                if ($row['level'] == 2) {
                    $commission = unserialize($g['commission2']);
                    $g['commission_pay'] = 0;
                    $g['commission'] = isset($commission['level' . $agentLevel['id']]) ? $commission['level' . $agentLevel['id']] : $commission['default'];
                    $totalcommission += $g['commission'];
                    $ordercommission += $g['commission'];
                    if ($g['status2'] >= 2) {
                        $g['commission_pay'] = $g['commission'];
                        $totalpay += $g['commission'];
                        $orderpay += $g['commission'];
                    }
                }
                if ($row['level'] == 3) {
                    $commission = unserialize($g['commission3']);
                    //判断代理分佣 不存在为默认
                    $g['commission'] = isset($commission['level' . $agentLevel['id']]) ? $commission['level' . $agentLevel['id']] : $commission['default'];
                    $totalcommission += $g['commission'];
                    $ordercommission += $g['commission'];
                    if ($g['status3'] >= 2) {
                        $totalpay += $g['commission'];
                        $orderpay += $g['commission'];
                    }
                }
                $status = $g['status' . $row['level']];
                if ($status == 1) {
                    //状态名
                    $g['statusstr'] = '待审核';
                    //申请时间
                    $g['dealtime'] = date('Y-m-d H:i', $row['applytime' . $row['level']]);
                } else if ($status == 2) {
                    //审核时间
                    $g['statusstr'] = '待打款';
                    $g['dealtime'] = date('Y-m-d H:i', $row['checktime' . $row['level']]);
                } else if ($status == 3) {
                    //打款时间
                    $g['statusstr'] = '已打款';
                    $g['dealtime'] = date('Y-m-d H:i', $row['checktime' . $row['level']]);
                } else if ($status == -1) {
                    //失效时间
                    $g['dealtime'] = date('Y-m-d H:i', $row['invalidtime' . $row['level']]);
                    $g['statusstr'] = '无效';
                }
                //状态
                $g['status'] = $status;
                $g['content'] = $g['content' . $row['level']];
                //级别
                $g['level'] = $row['level'];
                if ($row['level'] == 1) {
                    $g['level'] = '一';
                } else if ($row['level'] == 2) {
                    $g['level'] = '二';
                } else if ($row['level'] == 3) {
                    $g['level'] = '三';
                }
            }
            unset($g);
            //商品
            $array[$k]['goods']=$v;
            $array[$k]['ordercommission']=$ordercommission;
            $array[$k]['orderpay']=$orderpay;
            $array[$k]['applyno']=$details['applyno'];
            $array[$k]['ordersn']=$orderlist[$k]['ordersn'];

        }
        $data['list']=$array;
        $data['applyno']=$details['applyno'];
        $this->outFormats($data, 'ok', 0);

    }
    //获取订单详情
    /**
     * @param $orderid int,array,str
     * @param $where array
     * @return bool
     */
    public function getOrderDetails($orderid, $field, $where = array())
    {
        $where = ' og.nocommission=0 ';
        if (empty($orderid)) {
            return false;
        }
        if (is_array($orderid)) {
            $str = trim(implode($orderid, ','), ',');
            $where .= 'and og.orderid in (' . $str . ')';
        } elseif (is_numeric($orderid)) {
            $where .= 'and og.orderid=' . $orderid;
        } else {
            $where .= 'and og.orderid in (' . trim($orderid) . ')';
        }
        //$field='og.orderid,og.goodsid,og.optionname,og.commission1,og.commission2,og.commission3,og.status1,og.status2,og.status3,og.content1,og.content2,og.content3,g.title';
        $sql = 'select og.orderid,' . $field . ' from ims_ewei_shop_order_goods as og LEFT JOIN ims_ewei_shop_goods as g on og.goodsid=g.id where ' . $where;
        $result = M()->query($sql);
        foreach ($result as $k => $v) {

            $order_goods_arr[$v['orderid']][] = $v;
        }
        return $order_goods_arr;

    }

    /**获取代理的级别 级别名称，id
     * @param bool|true $all
     * @return array
     */
    public function getLevel($openid, $weid)
    {
        $field[] = 'agentlevel';
        $agent_info = $this->getInfo($openid, $weid, $field);
        if (empty($agent_info)) {
            return false;
        }
        $where['id'] = $agent_info;
        $where['uniacid'] = $weid;
        $level = M('ewei_shop_commission_level')->where($where)->select();
        return $level;
    }
    //获取公众号插件设置
    /**
     * @param $weid 公众号weid
     * $key 想要获取的插件名
     * 默认全部
     */
    private function getAccountSet($weid, $key = false)
    {

        if (empty($weid)) {
            return false;
        }
        $where['uniacid'] = $weid;
        $set = M('ewei_shop_sysset')->where($where)->find();
        if (empty($set)) {
            return array();
        }
        //插件设置
        $plugins = unserialize($set['plugins']);
        $plugin_set = array();
        if (!empty($key)) {
            $plugin_set = $plugins[$key];
        } else {
            $plugin_set = $plugins;
        }
        return $plugin_set;
    }

    /**获取member表用户详情信息
     * @param $openid
     * @param $weid
     */
    public function getInfo($openid, $weid, $filed = array())
    {
        $condition = '';
        if (empty($filed)) {
            $field = '*';
        }
        $where['openid'] = $openid;
        $where['uniacid'] = $weid;
        $data = M('ewei_shop_member')->where($where)->find();
        return $data;
    }
}