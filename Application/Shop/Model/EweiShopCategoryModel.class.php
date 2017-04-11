<?php

namespace Shop\Model;

use Shop\Controller\CommonController;
use Think\Model;

/**
 * litianyou @ 2017.2.15
 * 商品分类
 * */
class EweiShopCategoryModel extends Model {

    /**
     * 商品分类接口
     */
    public function getClassify($data) { //所有的商品分类 api
        if (empty($data['uniacid']) || empty($data['openid'])) {
            $aa['meta'] = array('code' => '0', 'message' => '非法操作');
            return $aa;
        }
        $users = A('Common');
        //return $users;
        $token = $data['uniacid'];
        $openid = $data['openid'];
        $users->getMember($token, $openid); //验证用户是够是该商户下的 只有这一个接口我验证了
        $category = M('ewei_shop_category');
        $where = array(
            'parentid' => 0,
            'enabled' => 1,
            'uniacid' => $data['uniacid']
        );
        $fl = $category->field('id,name,thumb,parentid,description')->where($where)->select(); //查询启用的顶级分类 （商城的分类就3级）
        foreach ($fl as &$v) {
            if ($v['thumb'] == '') {
                $v['thumb'] = '' . $v['thumb'];
            } else {
                $v['thumb'] = C('IMAGE_RESOURCE') . '/' . $v['thumb'];
            }
        }
        if ($data['classsid']) {
            $wheretj = array(
                'enabled' => 1,
                'uniacid' => $data['uniacid'],
                'parentid' => $data['classsid']
            );
        } else {
            $wheretj = array(
//            'ishome' => 1,
                'enabled' => 1,
                'uniacid' => $data['uniacid'],
                // 'isrecommand' => 1,
                'parentid'=>0
            );
        }

        $tuijian = $category->where($wheretj)->field('id,name,thumb,parentid,description')->select(); //推荐的分类
        foreach ($tuijian as &$v) {
            if ($v['thumb'] != '') {
                $v['thumb'] = C('IMAGE_RESOURCE') . '/' . $v['thumb'];
            }
        }
        $aa['fl'] = $fl;
        $aa['tuijian'] = $tuijian;
        if ($aa) {
            $aa['meta'] = array('code' => '1', 'message' => '调用成功!');
            return $aa;
        }
        $aa['meta'] = array('code' => '0', 'message' => '参数不对,调用失败!');
        return $aa;
    }

    public function classgoodlists($classid, $uniacid) { //获取分类商品列表 api
        $User = M('ewei_shop_category');
        $where = array(
            'parentid' => $classid,
            'enabled' => 1,
            'uniacid' => $uniacid,
        );
        if (empty($classid)) {
            $aa['meta'] = array('code' => '0', 'message' => '参数不对,调用失败!');
            return $aa;
        }
        $data = $User->where($where)->field('id,name,thumb,parentid,description')->select(); //分类下的商品
        if (!empty($data)) {
            foreach ($data as &$v) {
                if ($v['thumb'] != '') {
                    $v['thumb'] = C('IMAGE_RESOURCE') . '/' . $v['thumb'];
                }
            }
            $aa['fl'] = $data;
            $aa['meta'] = array('code' => '1', 'message' => '调用成功!');
            return $aa;
        } else {
            $aa['meta'] = array('code' => '0', 'message' => '无数据!');
            return $aa;
        }
    }

    /**
     * 商品详情接口
     */
    public function getDetail($d) {
        //return $d;
        $goods = M('ewei_shop_goods');
        $favorite = M('ewei_shop_member_favorite');
        $history = M('ewei_shop_member_history');
        $sku = M('ewei_shop_goods_option');
        $spec = M('ewei_shop_goods_spec');
        $spec_item = M('ewei_shop_goods_spec_item');
        $goodid = $d['goodsid'];
        $uniacid = $d['uniacid'];
        if (empty($goodid)) {
            $aa['meta'] = array('code' => '0', 'message' => '参数不对,调用失败!');
            return $aa;
        }
        $arr['openid'] = $d['openid'];
        $arr['uniacid'] = $d['uniacid'];
        $arr['goodsid'] = $goodid;
        $arr['deleted'] = 0;
        $arr['createtime'] = time();
        $wherel = array(
            'goodsid' => $goodid,
            'uniacid' => $uniacid,
            'openid' => $d['openid'],
            'deleted' => 0,
        );
        $cha = $favorite->where($wherel)->field('id')->select(); //这件商品是否收藏
        if (empty($cha)) {
            $aa['favorite'] = '0'; //未收藏
        } else {
            $aa['favorite'] = '1'; //已收藏
        }
        $chaf = $history->where($wherel)->field('id')->select(); //判断历史表
        if (empty($chaf)) {
            $history->add($arr);
        }
        $where = array(
            'id' => $goodid,
            'uniacid' => $uniacid
        );
        $data = $goods->where($where)->field('id,title,total,sales,thumb,marketprice,productprice,description,thumb_url')->find(); //商品的基本信息

        if ($data) {
            $data['thumb_url'] = unserialize($data['thumb_url']);
            array_unshift($data['thumb_url'], $data['thumb']);
            if($data['thumb_url']){
                foreach ($data['thumb_url'] as &$value) {
                    $value = getImgUrl($value);
                }
            }
            $data['thumb'] = getImgUrl($data['thumb']);
            $whereapec = array(
                'goodsid' => $goodid,
                'uniacid' => $uniacid
            );
            $shuxing = $spec->where($whereapec)->select(); //是否为单品（没有属性）
            if (empty($shuxing)) {
                $firstGoods = array(
                    'id' => '',
                    'specs' => '',
                    'marketprice' => $data['marketprice'],
                    'stock' => $data['total']
                );
                $sks = null;
                $aa['sku'] = $sks;
                $aa['firstGoods'] = $firstGoods;
                $aa['data'] = array($data);
                $aa['meta'] = array('code' => '1', 'message' => '调用成功!');
                return $aa;
            }

            $sks = array();
            foreach ($shuxing as $k => $v) {
                $sks[$k]['title'] = $v['title'];
                $sks[$k]['data'] = $spec_item->field('id as optionid,title,thumb,valueid')->where("specid = $v[id]")->select();
            }
            $firstGoods = array();
            foreach ($sks as $q => &$w) {
                foreach ($w['data'] as $z => &$x) {
                    $x['thumb'] = getImgUrl($x['thumb']);
                    if ($z == 0) {
                        $firstGoods[] = $w['data'][0]['optionid'];
                    }
                }
            }
            if ($firstGoods) {
                //            获取默认属性的数据
                $optionsid = M('ewei_shop_goods_option')
                        ->where(array('uniacid' => $d['uniacid'], 'goodsid' => $d['goodsid']))
                        ->field('id,specs,marketprice,stock')
                        ->select();
                foreach ($optionsid as $k => $v) {
                    $specsid = explode('_', $v['specs']);
                    $fanid = array_diff($firstGoods, $specsid);
                    if (empty($fanid)) {  //比较2个数组,如果为空的话就是直接 返回出所对应的optionid
//                $optionid = $v['id'];
                        $oldoption = $v;
                    }
                }
            }

            $sku = $sku->where($whereapec)->field('marketprice')->select(); //option表中所有属性的价钱
            //p($sku);die;
            $min = $sku[0]['marketprice']; //拟定最大和最小的价格，然后处理最高和最低价格
            $max = $sku[0]['marketprice'];
            foreach ($sku as $k => $v) {
                if ($v['marketprice'] < $min) {
                    $min = $v['marketprice'];
                }
                if ($v['marketprice'] > $max) {
                    $max = $v['marketprice'];
                }
            }
            if ($min != $max) {
                $min = $min < $data['marketprice'] ? $min : $data['marketprice'];
                $max = $max > $data['marketprice'] ? $max : $data['marketprice'];
                $data['marketprice'] = "$min-$max"; //得到售价 是一个波动值 比如 1-10 元
            }
            $data = array($data);
            $aa['sku'] = $sks;
            $aa['firstGoods'] = isset($oldoption) ? $oldoption : '';
            $aa['data'] = $data;
            $aa['meta'] = array('code' => '1', 'message' => '调用成功!');
            //p($data);die;
            return $aa;
        } else {
            $aa['meta'] = array('code' => '0', 'message' => '无数据!');
            return $aa;
        }
    }

    public function getClassification($where) {
        // return $where;exit;
        $data = $this->field('id,name,thumb,parentid,description,advimg')->where($where)->select();
        return $data;
    }

}

?>