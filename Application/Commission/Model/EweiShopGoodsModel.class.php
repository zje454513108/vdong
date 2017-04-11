<?php 
namespace Commission\Model;
use Think\Model\RelationModel;

class EweiShopGoodsModel extends RelationModel{

   /*
    根据传入的where条件进行查找 
    Input:
        uniacid 商户ID
        openid 用户ID
      
        
    Output:
        
        data 商品信息
   */
    public function findGoods($where){
      $data=$this->field('id as goodsid,title,type,weight,total,issendfree,isnodiscount,thumb,marketprice,cash,isverify,goodssn,productsn,sales,istime,timestart,timeend,usermaxbuy,maxbuy,unit,buylevels,buygroups,deleted,status,deduct,virtual,discounts')->where($where)->find();
      return $data;
     }
     
    
     /**
      * 查询所有商品数量
      * @param type $token
      * @return boolean
      */
     public function goodsCount($token){
         if(empty($token)){
             return false;
         }
         $where = array(
             'status'   =>1,
             'deleted'  =>0,
             'uniacid'  =>$token
         );
         $count = $this->where($where)->count();
         return $count;
     } 
}