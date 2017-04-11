<?php 
namespace Commission\Model;
use Think\Model;

class EweiShopOrderGoodsModel extends Model{

   /*
      传入数据 进行插入操作
    Input:
       $data 数据
      
        
    Output:
        
        id 插入后 返回的ID
   */
    public function addOrderGoods($data){
      $id=$this->add($data);
      return $id;
    }
    // 更新
    public function update($where,$data){
       $result= $this->where($where)->save($data);
       return $result;
    }
    
    /**
     * 获取订单商品信息
     * @param type $token
     * @param type $orderid
     * @return type
     */
    public function getOrderGoods($token,$orderid){
        $goods = $this->alias('og')
                ->field('g.id,g.title,og.total,og.realprice,og.price,og.optionname as optiontitle,g.noticeopenid,g.noticetype,og.commission1')
                ->join('__EWEI_SHOP_GOODS__ g on og.goodsid = g.id')
                ->where(array('og.uniacid'=>$token,'og.orderid'=>$orderid))
                ->select();
        return $goods;
    }
}
 
               