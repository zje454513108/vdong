<?php
namespace Shop\Model;
use Think\Model;
/**
 *产品参数  litianyou @ 2/21
 */
class EweiShopGoodsParamModel extends Model{
    public function param($data){ //产品参数  litianyou @ 2/21
        $User = M('ewei_shop_goods_param');
        $where = array(
            'uniacid' => $data['uniacid'],
            'goodsid' => $data['goodsid']
        );
        $data = $User->where($where)->field('title,value')->select();
        if($data){
            $aa['data'] = $data;
            $aa['meta'] = array('code'=>'1','message'=>'调用成功!');
            return $aa;
        }else{
            $aa['meta'] = array('code'=>'0','message'=>'无数据!');
            return $aa;
        }
    }
}