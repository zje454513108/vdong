<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Commission\Controller;

use Commission\Controller\CommissionBaseController;

/**
 * 我的小店======31001------
 */
class MyshopController extends CommissionBaseController {

    protected $myshop;

    public function __construct() {
        parent::__construct();
        $this->myshop = M('ewei_shop_commission_shop');
    }

    /**
     * 自选商品
     */
    public function index() {
        $openid = I('get.openid', '', 'string'); //用户openid

        $uid = $this->getMemberInfo($this->uniacid, $openid, 'id');

//        小店是否开启，开启才有自选商品
        if ($this->isCloseShop($this->uniacid)) {
            $this->outFormat('', '非法操作1', 31001);
        }
        $re = $this->isOpenSelectGoods($this->uniacid, $openid);
        if (empty($re)) {
            $this->outFormat('', '非法操作2', 31002);
        }
        $data = $this->shopinfo($this->uniacid, $uid['id']);
        if (empty($data)) {
            $this->outFormat('', '首次设置小店', 0);
        }
        unset($data['name'], $data['logo'], $data['img'], $data['desc']);
        $data['goods'] = $data['selectgoods'] ? $this->getSelectGoods($data['goodsids']) : '';
        if (empty($data['selectgoods'])) {
            $data['goodsids'] = '';
        }
        $this->outFormat($data, 'ok', 0);
    }

    /**
     * 自选商品写入操作
     */
    public function setSelect() {
        $openid = I('post.openid', '', 'string'); //用户openid
        $shopdata['selectgoods'] = I('post.selectgoods', 0, 'intval'); //是否开启自选商品
        $shopdata['selectcategory'] = I('post.selectcategory', 0, 'intval'); //自选栏目
        $shopdata['uniacid'] = $this->token;
        $shopdata['goodsids'] = I('post.goodsids', '', 'string'); //商品ids
        //1,2,3,4,
        if ($shopdata['goodsids'] && substr(trim($shopdata['goodsids']), -1) != ',') {
            $this->outFormat('', '参数格式错误1', 31003);
        }
        $shopdata['goodsids'] = $shopdata['goodsids'] ? substr(trim($shopdata['goodsids']), 0, -1) : '';

        $uid = $this->getMemberInfo($this->token, $openid, 'id');
        $shopdata['mid'] = $uid['id'];
        $shop = $this->shopinfo($this->token, $uid['id']);

        if (!empty($shopdata['selectgoods']) && empty($shopdata['goodsids'])) {
            $this->outFormat('', '请选择商品', 31004);
        }
        $shopdata['selectgoods'] = $shopdata['selectgoods'] ? 1 : 0;
        $shopdata['selectcategory'] = $shopdata['selectcategory'] ? 1 : 0;

        if (empty($shop['id'])) {
            $result = $this->myshop
                    ->add($shopdata);
        } else {
            $result = $this->myshop
                    ->where(array('id' => $shop['id']))
                    ->save($shopdata);
        }
        if ($result !== false) {
            $this->outFormat(array('num' => 1), 'ok', 0);
        } else {
            $this->outFormat('', '操作失败', 31005);
        }
    }

    /**
     * 获取已选择的商品信息
     * @param type $ids
     * @return type
     */
    protected function getSelectGoods($ids) {
        $goods = M('ewei_shop_goods')
                ->field('id,title,thumb,marketprice')
                ->where(array('id' => array('in', $ids)))
                ->select();
        foreach ($goods as &$v) {
            $v['thumb'] = getImgUrl($v['thumb']);
        }
        return $goods;
    }

    /**
     * 获取商品列表
     */
    public function getGoods() {
        $openid = I('get.openid', '', 'string');
        $this->getMemberInfo($this->uniacid, $openid);
        $isnew = I('get.isnew', 0, 'intval'); //新品
        $isrecommand = I('get.isrecommand', 0, 'intval'); //推荐
        $ishot = I('get.ishot', 0, 'intval'); //热销
        $istime = I('get.istime', 0, 'intval'); //限时秒杀
        $isdiscount = I('get.isdiscount', 0, 'intval'); //促销
        $keywords = I('get.keywords', '', 'string'); //商品名称
        $pcate = I('get.pcate', 0, 'intval'); //一级
        $ccate = I('get.ccate', 0, 'intval'); //二级
        $tcate = I('get.tcate', 0, 'intval'); //三级
        $limit = I('get.limit', 2, 'intval'); //分页显示的条数
        $p = I('get.p', 1, 'intval'); //当前页码

        $condition = 'uniacid = %d and deleted = 0 and status=1';
        $params = array(
            $this->uniacid
        );
        if (!empty($isnew)) {
            $condition .= " and isnew=1";
        }

        if (!empty($ishot)) {
            $condition .= " and ishot=1";
        }

        if (!empty($isrecommand)) {
            $condition .= " and isrecommand=1";
        }

        if (!empty($isdiscount)) {
            $condition .= " and isdiscount=1";
        }

        if (!empty($istime)) {
            $condition .= " and istime=1 and " . time() . ">=timestart and " . time() . "<=timeend";
        }

        if (!empty($keywords)) {
            $condition .= " and title like '%s'";
            $params[] = '%' . trim($keywords) . '%';
        }

//        栏目id
        if (!empty($tcate)) {
            $condition .= " AND ( tcate =%d or  FIND_IN_SET({$tcate},tcates)<>0 )";
            $params[] = $tcate;
        } else {
            if (!empty($ccate)) {
                $condition .= " AND ( ccate = %d or  FIND_IN_SET({$ccate},ccates)<>0 )";
                $params[] = $ccate;
            } else {
                if (!empty($pcate)) {
                    $condition .= " AND ( pcate = %d or  FIND_IN_SET({$pcate},pcates)<>0 )";
                    $params[] = $pcate;
                }
            }
        }
        $data = $this->goodsinfo($condition, $params, $limit, $p);
        if ($data['data']) {
            $this->outFormats($data['data'], 'ok', $p, $data['totalPage'], 0);
        } else {
            $this->outFormats(array(), '没有商品', $p, $data['totalPage'], 0);
        }
    }

    /**
     * 获得商品数据
     * @param type $condition
     * @param type $params
     * @param type $limit
     * @param type $p
     * @return type
     */
    public function goodsinfo($condition, $params, $limit, $p) {
        $order = M('ewei_shop_goods');
        $count = $order
                ->field('id')
                ->where($condition, $params)
                ->count();
        $Page = new \Think\Page($count, $limit);
        $totalPage = ceil($count / $limit);
        $info = $order
                ->field('id,title,thumb,marketprice')
                ->where($condition, $params)
                ->order('displayorder desc,createtime desc')
                ->limit($Page->firstRow . ',' . $Page->listRows)
                ->select();
        foreach ($info as &$v) {
            $v['thumb'] = getImgUrl($v['thumb']);
        }
        $data['totalPage'] = $totalPage ? $totalPage : 0;
        $data['data'] = $info;
        return $data;
    }

    /**
     * 获取栏目列表
     */
    public function categoryList() {
        $openid = I('get.openid', '', 'string');
        $this->getMemberInfo($this->uniacid, $openid);
        $category = $this->getCategory($this->uniacid, 'id,name');
        $this->outFormat($category, 'ok', 0);
    }

    /**
     * 小店设置
     */
    public function setMyShop() {
        //如果有值就新增或修改
        $shopdata['img'] = I('img');
        $shopdata['logo'] = I('logo');
        $shopdata['name'] = I('name');
        $shopdata['desc'] = I('desc');

        $isSetMyShop = false;
        if (!empty($shopdata['img']) || !empty($shopdata['logo']) || !empty($shopdata['name']) || !empty($shopdata['desc'])) {
            $isSetMyShop = true;
        }
        $commissionShopModel = D('ewei_shop_commission_shop');
        $openid = I('get.openid', '', 'string');
        $member = $this->getMemberInfo($this->uniacid, $openid, 'id,agentselectgoods,status,isagent');
        if ($member['isagent'] == 1 && $member['status'] == 1) {
            $mid = $member['id'];
        }
        $shop_where['uniacid'] = $this->uniacid;
        $shop_where['mid'] = $member['id'];
        $shop = $commissionShopModel->getItemByWhere($shop_where, 'id,img,logo,name,desc');
        if ($isSetMyShop) {
            $shopdata['uniacid'] = $this->uniacid;
            $shopdata['mid'] = $member['id'];
            if (empty($shop['id'])) {
                $result = $commissionShopModel->insert($shopdata);
            } else {
                $where['id'] = $shop['id'];
                $result = $commissionShopModel->update($where, $shopdata);
            }
            $this->outFormat($result, 'ok', 0);
        }
        //  select_goods 小店设置的自选商品开启
        //  agentselectgoods  0:跟随系统设置，1:强制禁止,2:强制开启
        $openselect = false;
        //插件设置
        $set = $this->getSet('commission', $this->uniacid);
        if ($set['select_goods'] == '1') {
            if (empty($member['agentselectgoods']) || $member['agentselectgoods'] == 2) {
                $openselect = true;
            }
        } else {
            if ($member['agentselectgoods'] == 2) {
                $openselect = true;
            }
        }
        //openselect 根据这个判断显示不显示自选商品按钮
        $shop['img']  = !empty($shop['img'])?$shop['img']:0;
        $shop['logo'] = !empty($shop['logo'])?$shop['logo']:0;
        $shop['name'] = !empty($shop['name'])?$shop['name']:0;
        $shop['desc'] = !empty($shop['desc'])?$shop['desc']:0;
        $shop['openselect'] = $openselect;
        $this->outFormat($shop, 'ok', 0);
    }

    /**
     * 我的小店商品列表
     */
    public function goodsList() {
        $openid = I('get.openid', '', 'string');
        $p = I('get.p', 1, 'intval') ? I('get.p', 1, 'intval') : 1;
        $limit = I('get.limit', 6, 'intval') ? I('get.limit', 6, 'intval') : 6;
        $member = $this->getMemberInfo($this->uniacid, $openid, 'id');
        $where_m = array(
            'mid' => $member['id'],
            'uniacid' => $this->uniacid
        );
        $shop = D('ewei_shop_commission_shop')
                ->getItemByWhere($where_m, 'selectgoods,goodsids');
        $model = D('EweiShopGoods');
        $field = 'id,title,thumb,productprice,marketprice';
        $where = array(
                'status' => 1,
                'deleted' => 0,
                'uniacid' => $this->uniacid
            );
        if ($shop['selectgoods']) {
            $where['id'] = array('in',$shop['goodsids']);
            $count = count(explode(",", $shop['goodsids']));
        } else {
            $count = $model->goodsCount($this->uniacid);
        }
        $Page = new \Think\Page($count, $limit);
        $data = $model
                    ->field($field)
                    ->where($where)
                    ->order('displayorder desc,createtime desc')
                    ->limit($Page->firstRow . ',' . $Page->listRows)
                    ->select();
        $totalPage = ceil($count / $limit);
        if ($data) {
            foreach ($data as &$v) {
                $v['thumb'] = getImgUrl($v['thumb']);
            }
            $list['DataList'] = $data;
            $list['goodsnum'] = $count;
            $this->outFormats($list, 'ok', $p, $totalPage, 0);
        } else {
            $list['DataList'] = array();
            $list['goodsnum'] = $count;
            $this->outFormats($list, '没有更多数据了！', $p, $totalPage, 0);
        }
    }
    
    /**
     * 获取我的小店用户信息
     */
    public function userShop(){
        $openid = I('get.openid','','string');
        $member = $this->getMemberInfo($this->uniacid, $openid, 'id,nickname,avatar');
        $info = D('EweiShopCommissionShop')->getItemByWhere(array('mid'=>$member['id'],'uniacid'=>$this->uniacid),'name,logo,img');
        $info['name'] = $info['name'] ? $info['name'] : $member['nickname'];
        $info['logo'] = $info['logo'] ? $info['logo'] : $member['avatar'];
        $set = $this->getSysset('shop', $this->uniacid);
        $info['img'] = $info['img'] ? $info['img'] : getImgUrl($set['img']);
        $this->outFormat($info, 'ok', 0);
        
    }

}
