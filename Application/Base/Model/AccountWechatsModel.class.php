<?php 
    namespace Base\Model;
    use Think\Model;
    class AccountWechatsModel extends Model{
    /*
    查看商户表微信支付信息
    Input:
        uniacid 商户ID
        
    Output:
        
        data 微信支付的信息
   */
        public function getDateByUniacid($id){
          $data=$this->field('secret,key')->where(array('uniacid'=>$id))->find();
          return $data; 
        }

    }