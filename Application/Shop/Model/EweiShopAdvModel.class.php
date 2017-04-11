<?php 
    namespace Shop\Model;
    use Think\Model;
    class EweiShopAdvModel extends Model{
    /*
    查询轮播图
    Input:
        
        liumosong @ 2.16
    Output:
        list
        轮播图列表
   */
        public function getBannerList($where){
           $list= $this->where($where)->select();
           return $list;
        }
         
    }