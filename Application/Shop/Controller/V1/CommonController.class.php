<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Shop\Controller;

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
        $result = array('data' => $data, 'msg' => $msg, 'code' => $code);
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
        $result = array('data' => $data, 'msg' => $msg, 'page' => $page, 'count' => $count, 'code' => $code);
        $this->response($result, $type);
    }

    /**
     * 判断用户是否存在
     * @param type $openid
     * @return boolean
     */
    protected function getMember($token, $openid, $field = '') {
        if (empty($openid) || empty($token)) {
            $this->outFormat('null', '参数错误', 7001, 'json');
        }
        if (empty($field)) {
            $field = 'id';
        }
        $user = M('ewei_shop_member')
                ->field($field)
                ->where("uniacid=%d and openid='%s'", array($token, $openid))
                ->find();
        if ($user) {
            return $user;
        } else {
            $this->outFormat('null', '用户不存在', 7002, 'json');
        }
    }

    /**
     * 商家设置
     * @param type $key
     * @param type $data 传：sets plugins
     */
//    public function getSysset($key = '', $uniacid = 0) {
//        $set = M('ewei_shop_sysset')
//                ->field('sets')
//                ->where("uniacid=%d", array($uniacid))
//                ->find();
//        $allset = unserialize($set['sets']);
//        $retsets = array();
//        if (!empty($key)) {
//            if (is_array($key)) {
//                foreach ($key as $k) {
//                    $retsets[$k] = isset($allset[$k]) ? $allset[$k] : array();
//                }
//            } else {
//                $retsets = isset($allset[$key]) ? $allset[$key] : array();
//            }
//            return $retsets;
//        } else {
//            return $allset;
//        }
//    }

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
//        Vendor('Wxpay.WxPayPubHelper');
//        $wxPay = new \WxPayDataBase();
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

}
