<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Shop\Model;

use Think\Model;

class EweiShopMemberAddressModel extends Model{
    
    /**
     * 查询单条数据（where)
     * @param type $where
     * @param string $field
     * @return boolean
     */
    public function getByWhereFind($where,$field=''){
        if(empty($where)){
            return false;
        }
        if(empty($field)){
            $field = '*';
        }
        
        $data = $this->field($field)->where($where)->find();
        
        return $data;
    }
    
    /**
     * 查询地址条数
     * @param type $where
     * @return boolean
     */
    public function getAddrCount($where){
        if(empty($where)){
            return false;
        }
        
        $count = $this->where($where)->count();
        return $count;
    }
    
    /**
     * 添加收货地址
     * @param type $data
     * @return type
     */
    public function addAddress($data){
        if(empty($data)){
            return false;
        }
        return $this->add($data);
    }
    
    /**
     * 更新用户地址
     * @param type $where
     * @param type $data
     * @return boolean
     */
    public function updateAddress($where,$data){
        if(empty($where) || empty($data)){
            return false;
        }
        
        $result = $this->where($where)->save($data);
        return $result;
    }
}
