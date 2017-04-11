<?php 
namespace Commission\Model;
use Think\Model;

class EweiShopMemberLevelModel extends Model{

   /*
    根据传入的where条件进行查找 
    Input:
        uniacid 商户ID
        openid 用户ID
      
        
    Output:
        
        等级对应的折扣信息 取最大折扣
   */
    public function findList($where){
      $list=$this->where($where)->order('level asc')->select();
      return $list;
     }
     
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
    public function getListByWhere($where='',$field='',$order=''){
       $field = $field ? :'*';
       $order = $order ? :'';
       $list= $this->field($field)->where($where)->order($order)->select();
       return $list;
    }
    // 查询单条信息
    public function getItemByWhere($where='',$field=''){
       $field = $field ? :'*';
       $query= $this->field($field)->where($where)->find();
       return $query;
    }

}