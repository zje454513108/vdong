<?php 
namespace Commission\Model;
use Think\Model;

class EweiShopGoodsOptionModel extends Model{

   /*
    根据传入的where条件进行查找 
    Input:
        uniacid 商户ID
        openid 用户ID
      
        
    Output:
        
        data 商品属性
   */
    public function findOption($where){
      $data=$this->field('id,title,marketprice,goodssn,productsn,stock,virtual,weight')->where($where)->find();
      return $data;
     }

}
 
               