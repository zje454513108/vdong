<?php
namespace Commission\Model;
use Think\Model;
/**
 *配送  litianyou @ 2/22
 * 设置表
 */
class EweiShopSyssetModel extends Model{
    
    /**
     * 获取商户配置信息
     * @param type $token
     * @param type $field
     * @return boolean
     */
    public function getShopSys($token='',$field=''){
        if(empty($token)){
            return false;
        }
        $shop = $this->where('uniacid=%d', array($token))->getField('sets');
        if($shop){
            $data = unserialize($shop);
            if($field){
                return $data[$field];
            }
            return $data;
        }else{
            return false;
        }
    }
    public function getmj($where){
        // dump($where);exit;
        $mj = $this->where($where)->field('plugins')->find();
        $mj = unserialize($mj['plugins']);
        return $mj['sale'];
    }
}