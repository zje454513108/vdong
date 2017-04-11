<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Shop\Controller\V2;
use Shop\Controller\V2\CommonController;

/**
 * 购物车----8000--
 *
 * @author Administrator
 */
class CartController extends CommonController {

    protected $cart; //购物车模型

    public function __construct() {
        parent::__construct();
        $this->cart = D('EweiShopMemberCart');
    }

    /**
     * 购物车列表
     */
    public function index() {
        $openid = I('get.openid', '', 'string');
        $p = I('get.p', 1, 'intval') ? I('get.p', 1, 'intval') : 1;
        $limit = I('get.limit', 2, 'intval') ? I('get.limit', 2, 'intval') : 2;
        $this->getMember($this->uniacid, $openid);
        $where = array(
            'f.uniacid' => $this->uniacid,
            'f.openid' => $openid,
            'f.deleted' => 0
        );
        $count = $this->cart->getCartCount(array($this->uniacid, $openid));
        $Page = new \Think\Page($count, $limit);
        $totalPage = ceil($count / $limit);
        $list = $this->cart
                ->alias('f')
                ->field('f.id,f.total,f.goodsid,g.total as stock, o.stock as optionstock, g.maxbuy,g.title,g.thumb,ifnull(o.marketprice, g.marketprice) as marketprice,g.productprice,o.title as optiontitle,f.optionid,o.specs')
                ->join('left join __EWEI_SHOP_GOODS__ g ON f.goodsid = g.id')
                ->join('left join __EWEI_SHOP_GOODS_OPTION__ o ON f.optionid = o.id')
                ->where($where)
                ->order('f.id DESC')
                ->limit($Page->firstRow . ',' . $Page->listRows)
                ->select();
        foreach ($list as &$r) {
            if (!empty($r['optionid'])) {
                $r['stock'] = $r['optionstock'];
            }
            $r['thumb'] = getImgUrl($r['thumb']);
        }
        if ($list) {
            $this->outFormats($list, 'ok', $p, $totalPage, 0);
        } else {
            $this->outFormats(array(), '本页没有数据了！', $p, $totalPage, 0);
        }
    }

    /**
     * 删除购物车
     */
    public function delete() {
        $openid = I('post.openid', '', 'string');
        $this->getMember($this->token, $openid);
        $ids = trim(I('post.ids', '', 'string'));
        $check = substr($ids, -1);
        $ids = substr($ids, 0, -1);
        if (empty($ids) || $check != ',') {
            $this->outFormat('', '参数错误！', 8002);
        }
        $where = array(
            'uniacid' => $this->token,
            'openid' => $openid,
            'deleted' => 0,
            'id' => array('in', $ids)
        );
        $data = $this->cart->deleteCart($where);
        if ($data) {
            $this->outFormat(array('num' => 1), 'ok', 0);
        } else {
            $this->outFormat('', '删除失败！', 8001);
        }
    }

    /**
     * 购物车更新数量
     */
    public function update() {
        $openid = I('post.openid', '', 'string');
        $this->getMember($this->token, $openid);
        $id = I('post.id', 0, 'intval');
        $total = I('post.total', 0, 'intval');

        if (empty($id) || empty($total)) {
            $this->outFormat('', '参数错误！', 8003);
        }
        $where = array(
            'id' => $id,
            'uniacid' => $this->token,
            'openid' => $openid,
            'deleted' => 0
        );
        $info = $this->cart->findCart($where, 'id');
        if ($info == false) {
            $this->outFormat('', '购物车数据未找到！', 8004);
        }
        $data = $this->cart->saveCart($where, array('total' => $total));

        if ($data) {
            $this->outFormat(array('num' => 1), 'ok', 0);
        } else {
            $this->outFormat('', '操作失败！', 8005);
        }
    }

    /**
     * 添加购物车
     */
    public function add() {
        $openid = I('post.openid', '', 'string');
        $goodsid = I('post.goodsid', 0, 'intval');
        $total = I('post.total', 0, 'intval');
        $optionid = I('post.optionid', 0, 'intval');
        if (empty($goodsid) || empty($total)) {
            $this->outFormat('', '参数错误！', 8006);
        }
        $this->getMember($this->token, $openid);
//        查询商品
        $goods = D('EweiShopGoods')->findGoods(array('uniacid' => $this->token, 'id' => $goodsid), 'id,marketprice');
        if (empty($goods)) {
            $this->outFormat('', '商品未找到！', 8007);
        }
//        查询属性
        $option = D('EweiShopGoodsOption')->optionSelectByWhere(array('uniacid' => $this->token, 'goodsid' => $goodsid), 'id');
        if (empty($option)) {
            $optionid = 0;
        } else {
            if (empty($optionid)) {
                $this->outFormat('', '属性不存在！', 8008);
            }
            $has_op = false;
            foreach ($option as $v) {
                if ($v['id'] == $optionid) {
                    $has_op = true;
                    break;
                }
            }
        }
        if ($has_op == false) {
            $this->outFormat('', '属性不存在！', 8009);
        }
//        查询购物车是否存在
        $where = array(
            'uniacid' => $this->token,
            'openid' => $openid,
            'optionid' => $optionid,
            'goodsid' => $goodsid,
            'deleted' => 0
        );
        $o_cart = $this->cart->findCart($where, 'id');
//        存在
        if ($o_cart) {
            $result = M('ewei_shop_member_cart')->where(array('id' => $o_cart['id']))->setInc('total', $total);
            if ($result) {
                $this->outFormat(array('num' => 1), 'ok', 0);
            } else {
                $this->outFormat('', '操作失败！', 8010);
            }
        }
//        新增
        $data = array(
            'uniacid' => $this->token,
            'openid' => $openid,
            'goodsid' => $goodsid,
            'optionid' => $optionid,
            'marketprice' => $goods['marketprice'],
            'total' => $total,
            'createtime' => time()
        );
        $result = $this->cart->addCart($data);
        if ($result) {
            $this->outFormat(array('num' => 1), 'ok', 0);
        } else {
            $this->outFormat('', '操作失败！', 8011);
        }
    }

}
