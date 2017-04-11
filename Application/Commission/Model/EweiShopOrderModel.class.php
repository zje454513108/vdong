<?php

namespace Commission\Model;

use Think\Model;

class EweiShopOrderModel extends Model {
    /*
      根据传入的where条件进行查找
      Input:
      uniacid 商户ID
      openid 用户ID


      Output:

      data 商品信息
     */

    public function findGoods($where) {
        $data = $this->field('id as goodsid,title,type,weight,total,issendfree,isnodiscount,thumb,marketprice,cash,isverify,goodssn,productsn,sales,istime,timestart,timeend,usermaxbuy,maxbuy,unit,buylevels,buygroups,deleted,status,deduct,virtual,discounts')->where($where)->find();
        return $data;
    }

    /**
     * 获取用户的佣金记录
     * @param type $token
     * @param type $openid
     * @param type $type
     * @param type $val
     * @param type $status
     * @return type
     */
    public function getOrdergoods($token, $openid, $type = '', $val = '', $status = '') {
        $where = array(
            'o.status' => array('egt', 1),
            'og.nocommission' => 0,
            'o.uniacid' => $token
        );
        if ($type == 'in') {
            $where['o.agentid'] = array('in', $val);
            if ($status == 2) {
                $field = 'og.commission2,og.commissions';
            } else if ($status == 3) {
                $field = 'og.commission3,og.commissions';
            }
        } else {
            $where['o.agentid'] = $openid;
            $field = 'og.commission1,og.commissions';
        }
        $data = $this
                ->alias('o')
                ->field($field)
                ->join('RIGHT JOIN ims_ewei_shop_order_goods og on o.id = og.orderid')
                ->where($where)
                ->select();
        return $data;
    }
    /**
     * 获取单条信息 
     * @param type $where
     * @param type $field
     */
    public function getItemByWhere($where,$field){
       $field = $field ? :'*';
       $query= $this->field($field)->where($where)->find();
       return $query;
    }
    // 更新
    public function update($where,$data){
       $result= $this->where($where)->save($data);
       return $result;
    }
    /*
       data 商品属性
   */
    public function countOrder($where){
      $data=$this->where($where)->count('id');
      return $data;
     }
   /*
      传入数据 进行插入操作
    Input:
    $data 数据
     *    
     *id 插入后 返回的ID
   */
    public function addOrder($data){
      $id=$this->add($data);
      return $id;
    }
    
    /**
     * 获取订单价格
     * @param type $where
     * @return type
     */
    public function countOrderMoney($where){
      $data=$this->where($where)->sum('price');
      return $data;
     }

}
