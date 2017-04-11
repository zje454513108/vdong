<?php 
namespace Commission\Model;
use Think\Model;
class EweiShopCommissionShopModel extends Model{
    //新增
    public function insert($data){
       $result= $this->add($data);
       return $result;
    }
    // 更新
    public function update($where,$data){
       $result= $this->where($where)->save($data);
       return $result;
    }
    // 查询多条信息
    public function getListByWhere($where,$field){
       $field = $field ? :'*';
       $list= $this->field($field)->where($where)->select();
       return $list;
    }
    // 查询单条信息
    public function getItemByWhere($where,$field){
       $field = $field ? :'*';
       $query= $this->field($field)->where($where)->find();
       return $query;
    }
     
}