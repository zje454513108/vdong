<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Shop\Model;

use Think\Model\RelationModel;

/**
 * 订单模型
 */

class OrderModel extends RelationModel {
    
    protected $tableName = 'ewei_shop_order';
    
//    订单和订单商品为一对多
    protected $_link = array(
        'OrderGoods' => array(
            'mapping_type' => self::HAS_MANY,
            'class_name' => 'OrderGoods',
            'foreign_key'   =>'orderid',
            'mapping_fields'  =>'price,total,optionname,optionid,goodsid',
            'relation_deep'=>true,
        ),
    );

}

