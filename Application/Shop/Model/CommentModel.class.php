<?php

namespace Shop\Model;

use Think\Model;

class CommentModel{
     //查询商品id
    public function selectGoods($uniacid,$orderid){
       $field= 'og.id,og.goodsid,og.price,g.title,g.thumb,og.total,g.credit,og.optionid,o.title as optiontitle';
       $where['_string'] = "og.orderid = $orderid and og.uniacid = $uniacid ";  
         $result =  M('ewei_shop_order_goods')
            ->field($field)
            ->alias('og')
            ->join('ims_ewei_shop_goods g ON g.id=og.goodsid','LEFT')
            ->join('ims_ewei_shop_goods_option o ON o.id=og.optionid','LEFT')
            ->where($where)
            ->select();
        return $result;
    }
    

}
