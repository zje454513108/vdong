<?php

namespace Commission\Model;

use Think\Model;

class CommissionModel extends Model {
    Protected $autoCheckFields = false;
     //新增
    public function selectGoods($uniacid,$orderid){
        $Model = D();
        $sql = "select og.id,og.realprice,og.total,g.hascommission,g.nocommission, g.commission1_rate,
        g.commission1_pay,g.commission2_rate,g.commission2_pay,g.commission3_rate,g.commission3_pay,
        og.commissions from  ims_ewei_shop_order_goods og left join ims_ewei_shop_goods g on g.id = og.goodsid  where og.orderid=$orderid and og.uniacid=$uniacid";
        $result = $Model->query($sql);
       return $result;
    }
    

}
