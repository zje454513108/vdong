<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Shop\Controller\V2;

use Think\Controller\RestController;

//7000
class CommonController extends RestController {

    protected $uniacid;
    protected $token;

    public function __construct() {
        parent::__construct();
//        所有get访问获取商户id
        $this->uniacid = I('get.token', 0, 'intval');
//        所有post访问获取商户id
        $this->token = I('post.token', 0, 'intval');
        $token = I('token',0,'intval');
        $openid = I('openid','','string');
        $mid = I('mid',0,'intval');
        $this->checkAgent($token, $openid, $mid);
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
     * @param type $page 当前页码
     * @param type $count 总页数
     * @param type $code 状态码
     * @param type $type
     */
    public function outFormats($data, $msg = 'ok', $page = 1, $count = 0, $code = 0, $type = 'json') {
        $result = array('Response' => array('Datalist' => $data, 'Pagecount' => $count, 'page' => $page), 'result' => $msg, 'code' => $code);
        $this->response($result, $type);
    }

    /**
     * 判断用户是否存在
     * @param type $openid
     * @return boolean
     */
    protected function getMember($token, $openid, $field = '') {
        if (empty($openid) || empty($token)) {
            $this->outFormat('', '参数错误', 7001);
        }
        if (empty($field)) {
            $field = 'id';
        }
        $user = D('EweiShopMember')->getByidFind($token, $openid, $field);

        if ($user) {
            return $user;
        } else {
            $this->outFormat('', '用户不存在', 7002);
        }
    }

    /**
     * 商家设置
     * @param type $key
     * @param type $data 传：sets plugins
     */
    public function getSysset($key = '', $uniacid = 0) {
        $set = M('ewei_shop_sysset')
                ->field('sets')
                ->where("uniacid=%d", array($uniacid))
                ->find();
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
     * 插件设置
     * @param type $pluginname 插件名称
     * @param type $uniacid 传：商家id
     */
    public function getSet($pluginname = '', $uniacid = 0) {
        $set = M('ewei_shop_sysset')
                ->field('plugins')
                ->where("uniacid=%d", array($uniacid))
                ->find();
        $allset = unserialize($set['plugins']);
        if (is_array($allset) && isset($allset[$pluginname])) {
            return $allset[$pluginname];
        }
        return array();
    }

    /**
     * 生成签名
     * @param type $params
     * @param type $key
     * @return type
     */
    public function makeSign($params, $key) {
        $sign = $this->iMakeSign($params, $key);
        return $sign;
    }

    /**
     * 生成签名
     * @return 签名，本函数不覆盖sign成员变量，如要设置签名需要调用SetSign方法赋值
     */
    public function iMakeSign($params = '', $key = '') {
        //签名步骤一：按字典序排序参数
//		ksort($this->values);
        ksort($params);
        $string = $this->iToUrlParams($params);
        //签名步骤二：在string后加入KEY
        $string = $string . "&key=" . $key;
        //签名步骤三：MD5加密
        $string = md5($string);
        //签名步骤四：所有字符转为大写
        $result = strtoupper($string);
        return $result;
    }

    /**
     * 格式化参数格式化成url参数
     */
    public function iToUrlParams($params) {
        $buff = "";
        foreach ($params as $k => $v) {
            if ($k != "sign" && $v != "" && !is_array($v)) {
                $buff .= $k . "=" . $v . "&";
            }
        }

        $buff = trim($buff, "&");
        return $buff;
    }

//    检测是否是分销
    protected function checkAgent($token,$openid='',$mid='') {
//        $token = I('token');
//        $openid = I('openid');
        $set = $this->getSet('commission', $token);
        if (empty($set['level'])) {
            return;
        }
        if (empty($openid)) {
            return;
        }
        $member_model = D('EweiShopMember');
        $member = $member_model->getByidFind($token, $openid);
        if (empty($member)) {
            return;
        }
        $parent = false;
//        $mid = I('mid'); //上级id
        if (!empty($mid)) {
            $parent = $member_model->getByWhereInfo($mid);
        }
//        判断上级是否是分销商
        $parent_is_agent = !empty($parent) && $parent['isagent'] == 1 && $parent['status'] == 1;
//        是
        if ($parent_is_agent) {
            if ($parent['openid'] != $openid) {
//                修改添加次数
                $where_c = array(
                    'uniacid'   =>$token,
                    'openid'    =>$openid,
                    'from_openid'   =>$parent['openid']
                );
                $click_model = D('EweiShopCommissionClickcount');
                $clickcount = $click_model->getClickCount($where_c);
//                没有保存时
                if ($clickcount <= 0) {
                    $click = array(
                        'uniacid' => $token,
                        'openid' => $openid,
                        'from_openid' => $parent['openid'],
                        'clicktime' => time()
                    );
                    $click_model->addClick($click);
                    $member_model->updateMember(array('uniacid' => $token,'id' => $parent['id']),array('clickcount' => $parent['clickcount'] + 1));
                }
            }
        }
        if ($member['isagent'] == 1) {
            return;
        }

        if ($type == 0) {
            $where = array(
                'uniacid' => $token,
                'id' => array('lt', $member['id'])
            );
            $first = $member_model->getMemberNum($where);

            if ($first <= 0) {
                $data = array(
                    'isagent' => 1,
                    'status' => 1,
                    'agenttime' => time(),
                    'agentblack' => 0
                );
                $member_model->updateMember(array('uniacid' => $token, 'id' => $member['id']), $data);
                return;
            }
        }
        $time = time();
        $become_child = intval($set['become_child']);
//        上级是分销商，当前用户非分销商
        if ($parent_is_agent && empty($member['agentid'])) {
            if ($member['id'] != $parent['id']) {
//                成为下线=-----无条件
                if (empty($become_child)) {
                    $where_a = array(
                        'agentid' => $parent['id'],
                        'childtime' => $time
                    );
                } else {
                    $where_a = array(
                        'inviter' => $parent['id']
                    );
                }
                $member_model->updateMember(array('uniacid' => $token, 'id' => $member['id']), $where_a);
            }
        }
//        是否需要审核
        $become_check = intval($set['become_check']);
//        成为分销商---无条件
        if (empty($set['become'])) {
            if (empty($member['agentblack'])) {
                $where_agent = array(
                    'isagent' => 1,
                    'status' => $become_check,
                    'agenttime' => $become_check == 1 ? $time : 0
                );
                $member_model->updateMember(array('uniacid' => $token, 'id' => $member['id']), $where_agent);
            }
        }
    }

}
