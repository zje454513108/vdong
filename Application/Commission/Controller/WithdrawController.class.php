<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Commission\Controller;

use Commission\Controller\CommissionBaseController;

/**
 * 分销佣金
 */
class WithdrawController extends CommissionBaseController {
    
    /**
     * 分销佣金列表
     */
    public function index() {
        $openid = I('get.openid','','string');
//        lock未结算佣金
//        check待打款佣金
//        apply已申请佣金
//        pay成功提现佣金
        $member = $this->getInfo($this->uniacid,$openid, array(
            'total',
            'ok',
            'apply',
            'check',
            'lock',
            'pay'
        ));
        $set = $this->getSet($this->uniacid, 'commission');
        $cansettle = $member['commission_ok'] > 0 && $member['commission_ok'] >= floatval($set['withdraw']);
        $member['commission_ok'] = number_format($member['commission_ok'], 2);
        $member['commission_total'] = number_format($member['commission_total'], 2);
        $member['commission_check'] = number_format($member['commission_check'], 2);
        $member['commission_apply'] = number_format($member['commission_apply'], 2);
        $member['commission_lock'] = number_format($member['commission_lock'], 2);
        $member['commission_pay'] = number_format($member['commission_pay'], 2);
        
        $data = array(
            'commission_ok'     =>$member['commission_ok'],//可提现
            'commission_pay'    =>$member['commission_pay'],//成功提现
            'commission_total'  =>$member['commission_total'],//累计佣金
            'commission_apply'  =>$member['commission_apply'],//已申请佣金
            'commission_check'  =>$member['commission_check'],//待打款佣金
            'settledays'        =>$set['settledays'],//结算提现天数
            'settlemoney'       =>number_format(floatval($set['withdraw']), 2),//提现条件，满多少金额
            'cansettle'        =>$cansettle?1:0,//是否可点击提现按钮
        );  
        $this->outFormat($data, 'ok', 0);
    }

}
