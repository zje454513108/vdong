<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Shop\Model;
use Think\Model;

//点击次数
class EweiShopCommissionClickcountModel extends Model{
    
    /**
     * 查询点击次数
     * @param type $where
     * @return boolean
     */
    public function getClickCount($where){
        if(empty($where)){
            return false;
        }
        $count = $this->where($where)->count();
        return $count;
    }
    
    /**
     * 添加点击记录
     * @param type $data
     * @return boolean
     */
    public function addClick($data){
        if(empty($data)){
            return false;
        }
        $result = $this->add($data);
        return $result;
    }
}
