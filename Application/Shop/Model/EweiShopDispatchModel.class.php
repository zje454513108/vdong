<?php
namespace Shop\Model;
use Think\Model;
/**
 *配送  litianyou @ 2/22
 */
class EweiShopDispatchModel extends Model{
    public function getdispatch($data){
        // $dispatch = D('EweiShopDispatch');
        $where['id']=$data['dispatch'];
        $where['uniacid']=$data['uniacid'];
        $dispatch = $this->where($where)->field('firstprice')->find();
        return $dispatch['firstprice'];
    }
    public function findData($where){
      $data=$this->field('id,dispatchname,dispatchtype,firstprice,firstweight,secondprice,secondweight,areas,carriers')->where($where)->select();
      return $data;
     }

}