<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Shop\Model;
use Think\Model;
class EweiShopOrderRefundModel extends Model{
    
    /**
     * 查询退款申请单个字段值
     * @param type $where
     * @param type $field
     * @return boolean
     */
    public function getRefundField($where,$field){
        if(empty($where) || empty($field)){
            return false;
        }
        $info = $this->where($where)->getField($field);
        return $info;
    }
    
    /**
     * 通过where查询一条数据
     * @param type $where
     * @param string $field
     * @return boolean
     */
    public function getByWhere($where,$field){
        if(empty($where)){
            return false;
        }
        if(empty($field)){
            $field = '*';
        }
        $info = $this->field($field)->where($where)->find();
        return $info;
    }
    
    /**
     * 更新退款订单信息
     * @param type $where
     * @param type $data
     * @return boolean
     */
    public function updateRefund($where,$data){
        if(empty($where) || empty($data)){
            return false;
        }
        $result = $this->where($where)->save($data);
        return $result;
    }
}
