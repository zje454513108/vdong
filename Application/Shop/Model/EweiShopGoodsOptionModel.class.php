<?php
namespace Shop\Model;
use Think\Model;
/**
 * litianyou @ 2017.2.16
 * 商品SKU
 * */
class EweiShopGoodsOptionModel extends Model{
    public function getGoodsku($goodsid,$uniacid){
        $sku = M('ewei_shop_goods_option');
        $where = "goodsid = $goodsid ";
        $where .= " and uniacid = $uniacid ";
        if(empty($goodsid)){
            $aa['meta'] = array('code'=>'0','message'=>'参数不对,调用失败!');
            return $aa;
        }
        $data = $sku->where("$where")->field('id,title,productprice,marketprice,thumb,stock,weight,goodssn')->select();
        if($data){
            foreach($data as &$v){
                if($v['thumb'] != ''){
                    $v['thumb'][]=C('IMAGE_RESOURCE').'/'.$v['thumb'];
                }
            }
            $aa['sku'] = $data;
            $aa['meta'] = array('code'=>'1','message'=>'调用成功!');
            return $aa;
        }else{
            $aa['meta'] = array('code'=>'0','message'=>'参数不对,调用失败!');
            return $aa;
        }
    }
    public function getGoodskuprice($optionid,$uniacid){
        if(empty($optionid)){
            $aa['meta'] = array('code'=>'0','message'=>'参数不对,调用失败!');
            return $aa;
        }
        $User = M('ewei_shop_goods_option');
        $where = array(
            'id' => $optionid,
            'uniacid' => $uniacid
        );
        $data = $User->where($where)->field('title,marketprice')->find();
        return $data;
    }
    /*
    根据条件查询商品属性信息
    Input:
        查询条件
        
    Output:
        
        商品属性信息
    */
    public function optionSelectByWhere($where,$field=''){
        $field = $field ? $field : '*';
        $spec=$this->field($field)->where($where)->select();
        return  $spec;
    }
}