<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Commission\Controller;

use Commission\Controller\CommissionBaseController;

/**
 * 我的团队===41001---
 */
class MyteamController extends CommissionBaseController {

//    protected $team;

    public function __construct() {
        parent::__construct();
//        $this->team = M('ewei_shop_member');
    }

    /**
     * 团队列表----暂未添加分页
     */
    public function index() {
        $openid = I('get.openid', '', 'string');
//        团队级别
        $level = I('get.level', 0, 'intval');
        $member = $this->getInfo($this->uniacid, $openid);
//        获取系统分销等级
        $set = $this->getSet($this->uniacid,'commission');
//        分销层级
        $sys_level = intval($set['level']);
        $total  = $member['agentcount'];
        ($level > 3 || $level <= 0) && $level = 1;
        $level1 = $member['level1'];//一级人数
        $level2 = $member['level2'];//二级人数
        $level3 = $member['level3'];//三级人数
//        p($member);die;
        $where = array(
            'uniacid'   =>$this->uniacid,
            'isagent'   =>1,
            'status'    =>1
        );
        $hasangent = false;
        if ($level == 1) {
            if ($level1 > 0) {
                $where['agentid'] = $member['id'];
                $hasangent = true;
            }
        } else if ($level == 2) {
            if ($level2 > 0) {
                $where['agentid']   = array('in',implode(',', array_values($member['level1_agentids'])));
                $hasangent = true;
            }
        } else if ($level == 3) {
            if ($level3 > 0) {
                $where['agentid']   = array('in',implode(',', array_values($member['level2_agentids'])));
                $hasangent = true;
            }
        }
        $list = array();
        if ($hasangent) {
            $list = $this->shopMember
                    ->field('id,openid,agentid,nickname,avatar')
                    ->where($where)
                    ->order('agenttime desc')
                    ->select();
            foreach ($list as &$row) {
                $info = $this->getInfo($this->uniacid,$row['openid'], array(
                    'total'
                ));
                $row['commission_total'] = $info['commission_total'];
                $row['agentcount'] = $info['agentcount'];
                $row['agenttime'] = date('Y-m-d H:i', $row['agenttime']);
            }
            $data = array(
                'sysLevel'  =>$sys_level,
                'total' =>$total,
                'level1'    =>$level1,
                'level2'    =>$level2,
                'level3'    =>$level3,
                'data'  =>$list
            );
            $this->outFormat($data, 'ok', 0);
        }else{
            $this->outFormat(array(), '没有团队', 0);
        }
    }

}
