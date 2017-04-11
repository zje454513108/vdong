<?php

namespace Shop\Model;

use Think\Model;

class EweiShopOrderModel extends Model {

    /**
     * 添加信息
     * @param  $data 添加的数据
     * @return 
     */
    public function insert($data){
        $result = $this->data($data)->add();
        return $result;
    }
    /**
     * 根据id查询
     * @param  $uniacid 商家id
     * @param  $openid  用户id
     * @param  $id   主键id     
     * @param  $field   查询字段
     * @return 
     */
    public function getByidFind($uniacid,$openid,$id,$field=''){
        $where = array(
            'uniacid' => $uniacid,
            'openid' => $openid,
            'id' => $id
        );
        $result = $this->where($where)->field($field)->find();
        return $result;
    }
    /**
     * 查询条件查询多条
     * @param  $uniacid 商家id
     * @param  $openid  用户id
     * @param  $where   查询条件     
     * @param  $field   查询字段
     * @param  $order   排序 
     * @return 
     */
    public function getByWhereSelect($where,$field='',$order){
        $result = $this->where($where)->field($field)->order($order)->select();
        return $result;
    }
    /**
     * 根据条件删除
     * @param  $uniacid 商家id
     * @param  $openid  用户id
     * @param  $where   查询条件     
     * @param  $field   查询字段
     * @param  $order   排序 
     * @return 
     */
    public function delBywhere($where){
        $result = $this->where($where)->delete();
        return $result;
    }
    /**
     * 根据条件更新数据
     * @param  $where 更新条件
     * @param  $data  更新数据
     * @return 
     */
    public function updateByWhere($where,$data){
        $result = $this->where($where)->save($data); 
        return $result;
    }
    
    /**
     * 获取订单数量
     * @param type $where
     * @return boolean
     */
    public function getOrderCount($where){
        if(empty($where)){
            return false;
        }
        $count = $this->where($where)->count();
        return $count;
    }
    
    /**
     * 根据where查询单条数据
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
        $result = $this->field($field)->where($where)->find();
        return $result;
    }

}
