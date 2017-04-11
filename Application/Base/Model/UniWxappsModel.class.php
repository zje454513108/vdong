<?php 
    namespace Base\Model;
    use Think\Model;
    class UniWxappsModel extends Model{
    /*
    查看商户表微信支付信息
    Input:
        $where 封装的搜索条件
        
    Output:
        
        data 微信支付的信息
   */
        public function getDateByUniacid($where){
          $data=$this->where($where)->find();
          return $data; 
        }

    }