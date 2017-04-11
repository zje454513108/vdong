<?php
namespace Shop\Model;
use Think\Model;
class EweiShopGoodsSpecModel extends Model{
   
    /*
    根据条件查询商品表首页信息
    Input:
        查询条件
        
    Output:
        
        查询商品是否存在规格
    */
    public function specSelectByWhere($where){
        $spec=$this->where($where)->select();
        return  $spec;
    }
}