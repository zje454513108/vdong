<?php

/* 
 * lixiaojun
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Shop\Model;

use Think\Model\RelationModel;

/**
 * 订单商品模型
 */
class OrderGoodsModel extends RelationModel {
    
    protected $tableName = 'ewei_shop_order_goods';

    protected $_link = array(
        'Goods' => array(
                'mapping_type'  => self::BELONGS_TO,
                'class_name'    => 'Goods',
                'foreign_key'   => 'goodsid',
                'mapping_fields'  =>'title,thumb',
            ),
    );
    

}
