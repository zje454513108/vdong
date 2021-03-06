<?php 
namespace Commission\Model;
use Think\Model;

class EweiShopMemberAddressModel extends Model{

   /*
    根据传入的where条件进行查找 
    Input:
        uniacid 商户ID
        openid 用户ID
      
        
    Output:
        
        购物人的地址ID 姓名 手机 地址 省 市 区
   */
    public function findData($where){
      $data=$this->field('id,realname,mobile,address,province,city,area')->where($where)->find();
      return $data;
     }

}