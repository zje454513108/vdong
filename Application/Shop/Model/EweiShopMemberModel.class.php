<?php
namespace Shop\Model;
use Think\Model;

class EweiShopMemberModel extends Model{

    /**
     * 根据id查询
     * @param  $uniacid 商家id
     * @param  $openid  用户id
     * @param  $id   主键id     
     * @param  $field   查询字段
     * @return 
     */
    public function getByidFind($uniacid,$openid,$field=''){
        $where = array(
            'uniacid' => $uniacid,
            'openid' => $openid,
        );
        $result = $this->where($where)->field($field)->find();
        return $result;
    }
    
    /**
     * 更新用户基本信息
     * @param type $where
     * @param type $data
     * @return boolean
     */
    public function updateMember($where,$data){
        if(empty($where) || empty($data)){
            return false;
        }
        $result = $this->where($where)->save($data);
        return $result;
    }
    
    /**
     * 添加用户
     * @param type $data
     * @return boolean
     */
    public function addMember($data){
        if(empty($data)){
            return false;
        }
        $result = $this->add($data);
        
        return $result;
    } 
    
    /**
     * 通过主键id获取用户信息
     * @param type $id
     * @return boolean
     */
    public function getByWhereInfo($id){
        if(empty($id)){
            return false;
        }
        $result = $this->where(array('id'=>$id))->find();
        return $result;
    }
    
    /**
     * 获取用户条数
     * @param type $where
     * @return boolean
     */
    public function getMemberNum($where){
        if(empty($where)){
            return false;
        }
        
        $count = $this->where($where)->count();
        return $count;
    }
}