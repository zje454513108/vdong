<?php 
namespace Commission\Model;
use Think\Model;

class EweiShopMemberCartModel extends Model{

   /*
    根据传入的where条件进行删除 
    Input:
        uniacid 商户ID
        openid 用户ID
      
        
    Output:
        
        data 是否成功
   */
    public function deleteMemberCart($where){
      $data=$this->where($where)->setField('deleted',1);
      return $data;
     }
     /*
    根据传入的where条件进行删除 
    Input:
        uniacid 商户ID
        openid 用户ID
      
        
    Output:
        
        data 是否成功
   */
    public function findMemberCart($where,$field){
      $data=$this->field($field)->where($where)->find();
      return $data;
     }
}