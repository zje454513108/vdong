<?php
namespace Shop\Model;
use Think\Model;
/**
 * 添加购物车
 * */
class EweiShopMemberCartModel extends Model{ 
    /**
     * 获取购物车数量
     * @param type $where
     * @return boolean
     */
    public function getCartCount($where){
        if(empty($where)){
            return false;
        }
        $count = $this->where("uniacid = %d and openid = '%s' and deleted = 0",$where)->count();
        return $count;
    }
    
    /**
     * 删除购物车
     * @param type $where
     * @param type $ids
     * @return boolean
     */
    public function deleteCart($where){
        if(empty($where)){
            return false;
        }
        $result = $this->where($where)->save(array('deleted'=>1));
        return $result;
    }
    
    /**
     * 查询单条购物车
     * @param type $where
     * @param type $field
     * @return boolean
     */
    public function findCart($where,$field=''){
        if(empty($where)){
            return false;
        }
        $field = $field ? $field : '*';
        $cart = $this->field($field)->where($where)->find();
        if($cart){
            return $cart;
        }else{
            return false;
        }
        
    }
    
    /**
     * 购物车更新
     * @param type $where
     * @param type $data
     * @return boolean
     */
    public function saveCart($where,$data){
        if(empty($where) || empty($data)){
            return false;
        }
        $result = $this->where($where)->save($data);
        return $result;
    }
    
    /**
     * 添加购物车
     * @param type $data
     * @return boolean
     */
    public function addCart($data){
        if(empty($data)){
            return false;
        }
        $result = $this->add($data);
        return $result;
    }
        
}