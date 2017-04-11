<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Commission\Controller;

use Commission\Controller\CommissionBaseController;

/**
 * 分销公共控制器
 */
class CommissionBaseController extends CommonController {

    protected $shopOrder;
    protected $shopMember;

    /**
     * 初始化
     */
    public function __construct() {
        parent::__construct();
        $this->shopOrder = D('ewei_shop_order');
        $this->shopMember = D('ewei_shop_member');
        $token = I('token', '', 'string');
        $openid = I('openid', '', 'string');
        $this->checkMember($token, $openid);
        $set = $this->getSet($token, 'commission');
        if ($set['level'] > 0) {
            $isAgent = $this->isAgent($openid);
            if (empty($isAgent)) {
                $this->outFormat('', '你没有权限进入，您不是分销商', 21005);
            }
        } else {
            $this->outFormat('', '你没有权限进入，未开启分销', 21006);
        }
    }

    // 判断用户是否为代理商
    public function isAgent($openid) {
        if (empty($openid)) {
            return false;
        }
        $field = 'isagent,status';
        $where['openid'] = $openid;
        $member = $this->shopMember->getItemByWhere($where, $field);
        if ($member['isagent'] == 1 && $member['status'] == 1) {
            return true;
        }
        return false;
    }

    public function index() {
        $orderid = 45;
        $this->checkOrderConfirm($orderid);
    }

    /**
     * 是否关闭我的小店
     * @param type $token
     * @return boolean true表示已关闭
     */
    public function isCloseShop($token) {
        $set = $this->getSet($token, 'commission');
        if ($set['closemyshop'] == 1) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 是否开启自选商品
     * @param type $token
     * @return boolean true 开启
     */
    public function isOpenSelectGoods($token,$openid) {
        $member = $this->shopMember->getItemByWhere(array('uniacid'=>$token,'openid'=>$openid),'agentselectgoods');
        $openselect = false;
        $set = $this->getSet($token, 'commission');
        if ($set['select_goods'] == '1') {
            if (empty($member['agentselectgoods']) || $member['agentselectgoods'] == 2) {
                $openselect = true;
            }
        } else {
            if ($member['agentselectgoods'] == 2) {
                $openselect = true;
            }
        }
        return $openselect;
    }

}
