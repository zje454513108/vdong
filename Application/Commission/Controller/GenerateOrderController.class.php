<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Commission\Controller;

use Commission\Controller\CommonController;

/**
 * 生成订单 lms
 */
class GenerateOrderController extends CommonController {
    public function createOrder(){
          $token = I('get.token'); //商户id 
        $openid = I('get.openid'); //openid 
        $dispatchtype=I('get.dispatchtype');//配送类型 //0为邮寄 1为自提
        $addressid=I('get.addressid');//地址ID
        $dispatchid=I('get.dispatchid');//配送方式ID
         $dispatch['id'] = $dispatchid;
        $dispatch['uniacid'] = $token;
        $goodsID=I('get.goodsid');
        $goodsOptionID=I('get.optionid');
        $remark=I('get.remark');
        $total=I('get.total');
        $shoppingCart=I('get.shoppingCart');//购物车ID
         if (!empty($shoppingCart)) {
             $shopMemberCartDb=D('ewei_shop_member_cart');
           $cartId=explode(',',$shoppingCart);
           $field=array('goodsid','total','marketprice','optionid');
                 $allgoods      = array();
                // 总价
                $totalprice    = 0;
                // 商品价格
                $goodsprice    = 0;
                //重量
                $weight        = 0;
                //折扣价格
                $discountprice = 0;
                //拆分商品信息
                //现金
                $cash          = 1;
                $level         = $this->getLevel($member,$uniacid);
                //扣除价格
                $deductprice   = 0;
                //虚拟销售
                $virtualsales  = 0;
            foreach ($cartId as $key => $val) {
                $cartWhere['id']=$val;
                $cartWhere['uniacid']=$token;
                $cartWhere['openid']=$openid;
                $goodsMsg=$shopMemberCartDb->findMemberCart($cartWhere,$field);
                $goodsid    = $goodsMsg['goodsid'];
                $optionid   = $goodsMsg['optionid'];
                $goodstotal = $goodsMsg['total'];
                //实例化商品表
                    $shopGoodsDb=D('ewei_shop_goods');
                /*结束*/
                /*商品库存 开始*/
                $goodsWhere['uniacid']=array('eq',$token);
                $goodsWhere['id']=array('eq',$goodsid);
                $goodsData=$shopGoodsDb->findGoods($goodsWhere);
                 //虚拟商品模板ID 0 多规格虚拟商品
            $virtualid=$goodsData['virtual'];
            //把商品的库存换个下标
            $goodsData['stock']=$goodsData['total'];
            //把商品库存改为传过来的库存
            $goodsData['total']=$goodstotal;
                 // dump($goodsData);exit;
             if ($goodsData['cash'] !=2 ) {
                 $cash = 0;
             }//$goodsData['cash'] 结束
              /*三元判断是否设置单位 没有为件开始*/
            $unit = empty($goodsData['unit']) ? '件' : $goodsData['unit'];
            /*结束*/
            /*判断是否有限购 开始*/
            //   if ($goodsData['maxbuy'] > 0) {
            //     if ($goodstotal > $goodsData['maxbuy']) {
            //          $this->outFormat('null',$goodsData['title']."<br/>一次限购".$goodsData['maxbuy'].$unit.'!',404,'json');
                   
            //     }
            // }//限购判断结束
            /*判断一个用户最多购买数量 开始*/
            // if ($goodsData['usermaxbuy']>0) {
            //    $sql="select ifnull(sum(og.total),0)  from ims_ewei_shop_order_goods og left join ims_ewei_shop_order o on og.orderid=o.id where og.goodsid=".$goodsData['goodsid']." and o.status>=1 and o.openid='".$openid."' and og.uniacid=".$token;
            //    $order_goodscount=M('ewei_shop_order_goods')->query($sql);
               // dump($goodsData);exit;
            //    if ($order_goodscount >=$goodsData['usermaxbuy']) {
            //     $this->outFormat('null',$goodsData['title']."<br/>最多限购".$goodsData['maxbuy'].$unit.'!',404,'json');
            //    }
            // }//判断用户购买数量是否大于限购数结束
             /*判断限购时间是否开启 如果开启的话 在不在限购时间内 开始*/
            if ($goodsData['istime'] == 1) {
                if (time() < $goodsData['timestart']) {
                    $this->outFormat('null',$goodsData['title']."<br/> 限购时间未到",404,'json');
                }
                if (time() > $goodsData['timeend']) {
                     $this->outFormat('null',$goodsData['title']."<br/> 限购时间已过!",404,'json');
                }
            }//限购时间判断结束
             /*结束*/
            //会员等级
            $levelid = intval($member['level']);
            //会员组
            $groupid = intval($member['groupid']);
            /*判断是否有购买等级设置 如果有 判断是否有购买的等级 开始*/
            if ($goodsData['buylevels'] != '') {
                $buylevels = explode(',', $goodsData['buylevels']);
                if (!in_array($levelid, $buylevels)) {
                    $this->outFormat('null',"您的会员等级无法购买<br/>".$goodsData['title']."!",404,'json');
                }
            }
            /*购买等级设置结束*/
            /*判断商品是否有会员组 如果有 是否在购买组内*/
            if ($goodsData['buygroups'] != '') {
                $buygroups = explode(',', $goodsData['buygroups']);
                if (!in_array($groupid, $buygroups)) {
                    $this->outFormat('null',"您所在会员组无法购买<br/>".$goodsData['title']."!",404,'json');
                }
            }
            /*会员组结束*/
             //实例化 商品属性表
            $goodsOptionDb=D('ewei_shop_goods_option');
            //判断商品是否有属性
            if (!empty($optionid)) {
                  $whereOption['uniacid']=array('eq',$token);
                  $whereOption['goodsid']=array('eq',$goodsid);
                  $whereOption['id']=array('eq',$optionid);
                  //查找属性库存
                  $option=$goodsOptionDb->findOption($whereOption);
                  //如果属性库存不为空
                  if (!empty($option)) {
                     /*如果库存不等于 -1*/
                           if ($option['stock'] != -1) {
                                if (empty($option['stock'])) {
                                    $this->outFormat('null',$goodsData['title']."<br/>".$option['title']." 库存不足!",404,'json');
                                }
                            }//库存判断结束
                            //商品里的属性ID 赋值
                            $goodsData['optionid'] =$optionid;
                            //属性标题赋值
                            $goodsData['optiontitle'] =$option['title'];
                            //市场价格
                            $goodsData['marketprice'] =$option['marketprice'];
                            $virtualid = $option['virtual'];
                            if (!empty($option['goodssn'])) {
                                  $goodsData['goodssn'] = $option['goodssn'];
                            }
                            //产品
                            if (!empty($option['productsn'])) {
                                $goodsData['productsn'] = $option['productsn'];
                            }
                            //重量
                            if (!empty($option['weight'])) {
                                $goodsData['weight'] = $option['weight'];
                            } 
                            /**********三个判断结束*/
                  }//属性库存不为空结束
                  
                }else{
                    //不存在属性ID 进入这里
                    if ($goodsData['stock'] != -1) {
                       if (empty($goodsData['stock'])) {
                           $this->outFormat('null',$goodsData['title']."<br/>"." <br/>库存不足!",404,'json');
                       }
                    }//库存判断结束
                }//属性判断结束
                   $gprice = $goodsData['marketprice'] * $goodstotal;
                    $goodsprice += $gprice;
                    $discounts = json_decode($goodsData['discounts'], true);
            //判断是否参与会员折扣
            if (is_array($discounts)) {
                  if (!empty($level['id'])) {
                      if ($discounts['level' . $level['id']] > 0 && $discounts['level' . $level['id']] < 10) {
                          $level['discount'] = $discounts['level' . $level['id']];
                      }
                  }else {
                      if ($discounts['default'] > 0 && $discounts['default'] < 10) {
                          $level['discount'] = $discounts['default'];
                      }
                  }
            }//会员判断结束
            /*会员折扣总价格*/
            if (empty($goodsData['isnodiscount']) && $level['discount'] > 0 && $level['discount'] < 10) {
              $dprice = round($gprice * $level['discount'] / 10, 2);
              $discountprice += $gprice - $dprice;
              $totalprice += $dprice;
            } else {
              $totalprice += $gprice;
            }
            /*会员折扣总价格结束*/
            /*是否包邮，计算重量*/
            if (empty($goodsData['issendfree'])) {
                  $weight += $goodsData['weight'] * $goodstotal;
            }
            /*包邮结束*/
            /*是否虚拟物品*/
            if ($goodsData['isverify'] == 2) {
                  $isverify = true;
            }
            /*虚拟物品结束*/
            /*商品类型*/
            if ($goodsData['type'] == 2) {
                  $isvirtual = true;
            }
            /*商品类型结束*/
             $deductprice += $goodsData['deduct'];
            $virtualsales += $goodsData['sales'];
            $allgoods[] = $goodsData;
            //开始删除购物车
             $shopMemberCartWhere['uniacid']=array('eq',$token);
                $shopMemberCartWhere['openid']=array('eq',$openid);
                $shopMemberCartWhere['id']=array('eq',$val);
                // dump($shopMemberCartWhere);exit;
                // $shopMemberCartDb->deleteMemberCart($shopMemberCartWhere);
            }//循环遍历购物车结束
                     /*----开始---*/
        $deductenough = 0;
        /*----结束---*/
         /*----计算邮费开始---*/
        $dispatchprice = 0;
        /*----结束---*/
         /*----开始---*/
        $totalprice -= $deductenough;
        $totalprice += $dispatchprice;
        $deductcredit  = 0;
        $deductmoney   = 0;
        $deductcredit2 = 0;
        /*----结束---*/
        // 生成订单号
        $ordersn    = $this->createNo('ewei_shop_order','ordersn','SH');
        $verifycode = "";
        //如果是虚拟商品进入下面的订单号生成
        if ($isverify) {
            $verifycode = random(8, true);
             $shopOrderDb=D('ewei_shop_order');
             $shopOrderWhere['verifycode']=$verifycode;
             $shopOrderWhere['uniacid']=$uniacid;
            while (1) {
                $count = $shopOrderDb->countOrder($shopOrderWhere);
                if ($count <= 0) {
                    break;
                }
                $verifycode = random(8, true);
            }
        }//订单号生成结束
        // dump($ordersn);exit;
        /*----结束---*/
        /*----开始---*/
        // 自提人
        // $carrier  = $_GPC['carrier'];
        // $carriers = is_array($carrier) ? iserializer($carrier) : iserializer(array());
        $order    = array(
            'uniacid' => $uniacid,
            'openid' => $openid,
            'ordersn' => $ordersn,
            'price' => $totalprice,
            'cash' => $cash,
            'discountprice' => $discountprice,
            'deductprice' => $deductmoney,
            'deductcredit' => $deductcredit,
            'deductcredit2' => $deductcredit2,
            'deductenough' => $deductenough,
            'status' => 0,
            'paytype' => 0,
            'transid' => '',
            'remark' => $remark,
            'addressid' => empty($dispatchtype) ? $addressid : 0,
            'goodsprice' => $goodsprice,
            'dispatchprice' => $dispatchprice,
            'dispatchtype' => $dispatchtype,
            'dispatchid' => $dispatchid,
            'createtime' => time(),
            'isverify' => $isverify ? 1 : 0,
            'verifycode' => $verifycode,
            'virtual' => $virtualid,
            'isvirtual' => $isvirtual ? 1 : 0,
            'oldprice' => $totalprice,
            'olddispatchprice' => $dispatchprice
        );
        if (!empty($address)) {
            $order['address'] = serialize($address);
        }
        $shopOrderDb=D('ewei_shop_order');
        $orderid=$shopOrderDb->addOrder($order);
        /*----开始---*/
        foreach ($allgoods as $goods) {
            $order_goods = array(
                'uniacid' => $uniacid,
                'orderid' => $orderid,
                'goodsid' => $goods['goodsid'],
                'price' => $goods['marketprice'] * $goods['total'],
                'total' => $goods['total'],
                'optionid' => $goods['optionid'],
                'createtime' => time(),
                'optionname' => $goods['optiontitle'],
                'goodssn' => $goods['goodssn'],
                'productsn' => $goods['productsn']
            );
            if (empty($goods['isnodiscount']) && $level['discount'] > 0 && $level['discount'] < 10) {
                $order_goods['realprice'] = $order_goods['price'] * $level['discount'] / 10;
            } else {
                $order_goods['realprice'] = $order_goods['price'];
            }
            $order_goods['oldprice'] = $order_goods['realprice'];
            $shopOrderGoodsDb=D('ewei_shop_order_goods');
            $shopOrderGoodsDb->addOrderGoods($order_goods);
        }
        $Article = A('Commission/Commission');
        $commission=$Article->checkOrderConfirm($ordersn);
        $this->outFormat($ordersn,'下单成功!',200,'json');
                
           //有购物车ID 结束      
         }else{
        $addressDb = D('ewei_shop_member_address');
         $addressWhere['openid']=$openid;
         $addressWhere['id']=$addressid;
         $addressWhere['uniacid']=$token;
         $addressWhere['isdefault']=1;
         $addressWhere['deleted']=0;
         //查找
        $addressMSG=$addressDb->findData($addressWhere);
        if(!empty($addressMSG)){
            $address = serialize($addressMSG);
        }else{
             $this->outFormat('','买家地址信息为空!',107,'json');
        }//地址判断结束
        if (empty($goodsOptionID)) {
            $goodsDb = D('EweiShopGoods');
            $goodsWhere['id']=$goodsID;
            $goodsWhere['uniacid']=$token;
            $goodsMsg= $goodsDb->findGoods($goodsWhere); //查询商品的售价，商品名
            if(!empty($goodsMsg)){
                $goodsOrder['marketprice'] = $goodsMsg['marketprice'];
                $goodsOrder['title'] = '';
                $goodsOrder['goodsname'] = $goodsMsg['title'];
                }
         }else{
            //有属性ID
            $optionDb = D('EweiShopGoodsOption');
            $goodsOptionMsg = $optionDb->getGoodskuprice($goodsOptionID,$token);//查有optionid的商品信息
            $goodDb = D('EweiShopGoods');
            $goodsWhere['id']=$goodsID;
            $goodsWhere['uniacid']=$token;
            $goodsMsg = $goodDb->findGoods($goodsWhere); //查商品名
            if(!empty($goodsMsg)){
                $goodsOrder['marketprice'] = $goodsMsg['marketprice'];
                $goodsOrder['title'] = $goodsMsg['title'];
                $goodsOrder['goodsname'] = $goodsMsg['title'];
            }
         }//属性判断结束
         $dispatchDb = D('EweiShopDispatch'); //运费
         $dispatchdata = $dispatchDb->getdispatch($dispatch);
         $prices = $goodsOrder['marketprice'] * $total; //单价*数量
         $pricesy = $prices + $dispatchdata;
         $mjDb = D('EweiShopSysset'); //满减
         $mjWhere['uniacid']=$token;
        $mj = $mjDb->getmj($mjWhere);
        if ($prices >= $mj['enoughmoney']) {
            $goodsOrder['prices'] = $pricesy - $mj['enoughdeduct'];
            $goodsOrder['m'] = $mj['enoughmoney'];
            $goodsOrder['j'] = $mj['enoughdeduct'];
        } else {
            $goodsOrder['prices'] = $pricesy;
            $goodsOrder['j'] = 0;
        }
        $ordersn = $this->createNo('ewei_shop_order','ordersn','SH');
            dump($ordersn);exit;
        $arr['ordersn'] =  $ordersn;
        $arr['openid'] = $openid;  //openid
        $arr['uniacid'] = $token; //商户id
        $arr['goodsprice'] = $prices; // 原始订单金额
        $arr['oldprice'] = $pricesy;//商品价格加上运费减去满减
        $arr['dispatchprice'] = $dispatchdata;//运费
        $arr['olddispatchprice'] = $dispatchdata;  //原运费
        $arr['price'] =$goodsOrder['prices']; //支付订单金额
        $arr['discountprice'] = $goodsOrder['j']; //优惠金额
        $arr['remark'] = $remark; //留言
        $arr['addressid'] = $addressid; //收货地址id
        $arr['createtime'] = time(); //下单时间
        $arr['address'] = $address; //收货地址
        $model = M();
        $model->startTrans(); //开启事务
        $orderDb = D('ewei_shop_order');
        $orderadd = $order->addOrder($arr);  // 添加到订单表
        if ($orderadd) {
            $orderg = D('ewei_shop_order_goods');  //
            //return $data1;
            $ordergoods['uniacid'] = $token; //商户id
            $ordergoods['orderid'] = $orderadd; //订单ID
            $ordergoods['goodsid'] = $goodsID; //商品id
            $ordergoods['price'] = $goodsOrder['marketprice']; //商品价格
            $ordergoods['total'] = $total; //数量
            $ordergoods['optionid'] = $goodsOptionID ? $goodsOptionID : 0; //属性id
            $ordergoods['createtime'] = time(); //创建时间
            $ordergoods['optionname'] = $goodsOrder['title']; //属性名称
            $ordergoods['realprice'] = $goodsOrder['marketprice'];
            $ordergoods['oldprice'] = $goodsOptionMsg['productprice'];
            $ordergoods['goodsn'] =$ordersn;
            $ordergoods['productsn'] =$goodsMsg['productsn'] ;
            $order_good = $orderg->add($ordergoods); //加入订单商品表
            if($orderadd && $order_good){
                $model->commit(); //事务提交
            }else{
                $model->rollback(); //回滚
            }
                $this->outFormat('','下单成功!',0,'json');
           
        }else{
            $this->outFormat('','下单失败!',109,'json');
        }

    }//立即购买结束


    }
    //获得折扣信息
    public function getLevel($level,$uniacid){
        //判断传入的会员信息中的等级 如果没有 没有折扣
         if (empty($level['level'])) {
            return array(
                'discount' => 10
            );
        }
        $levelDb=D('ewei_shop_member_level');
        $levelwhere['id']=array('eq',$level['level']);
        $levelwhere['uniacid']=array('eq',$uniacid);
        $levelList=$levelDb->findList($levelwhere);
        if (empty($level)) {
            return array(
                'discount' => 10
            );
        }
        return $level;
    }
    
    //生成订单号   
   public function createNo( $table,$field,$prefix){
      $billno = date('YmdHis') . $this->random(6, true);
      $shopOrderDb=M($table);
     
      $orderWhere[$field]=array('eq',$billno);

        while (1) {

            $count =$shopOrderDb->where($orderWhere)->count('id');
            if ($count <= 0) {
                break;
            }
            $billno = date('YmdHis') . random(6, true);
        }
        return $prefix . $billno;
   }

        //随机数方法
           public function random($length, $numeric = FALSE) {
            $seed = base_convert(md5(microtime() . $_SERVER['DOCUMENT_ROOT']), 16, $numeric ? 10 : 35);
            $seed = $numeric ? (str_replace('0', '', $seed) . '012340567890') : ($seed . 'zZ' . strtoupper($seed));
            if ($numeric) {
                $hash = '';
            } else {
                $hash = chr(rand(1, 26) + rand(0, 1) * 32 + 64);
                $length--;
            }
            $max = strlen($seed) - 1;
            for ($i = 0; $i < $length; $i++) {
                $hash .= $seed{mt_rand(0, $max)};
            }
            return $hash;
        }
        public function CommissionWithdrawal(){
            // dump($_GET);exit;
            $token=I('get.token');
            $openid=I('get.openid');
            $type=I('get.type');
            if (empty($token)||empty($openid)) {
                $this->outFormat('','参数错误!',106,'json');
            }
            // echo "string";exit();
           $data= $this->getSet($token,'commission');
           $member=$this->getInfo($token,$openid, array('ok'));
           // dump($member);exit;
           $level=$data['level'];
            $time= time();
             $day_times= intval($data['settledays']) * 3600 * 24;
             $commission_ok = $member['commission_ok'];
             $orderids = array();
             //等级1的
           if ($level >= 1) {
            $sql='select distinct o.id from ims_ewei_shop_order o left join ims_ewei_shop_order_goods og on og.orderid=o.id where o.agentid='.$member['id'].' and o.status>=3  and og.status1=0 and og.nocommission=0 and ('.$time.'- o.createtime >'.$day_times.') and o.uniacid='.$token.' group by o.id';
           
            $levelDb=M();
                $level1_orders=$levelDb->query($sql);
                // dump($level1_orders);exit;
            foreach ($level1_orders as $o) {
                if (empty($o['id'])) {
                    continue;
                }
                $orderids[] = array(
                    'orderid' => $o['id'],
                    'level' => 1
                );
            }
        }
        //等级2的
        if ($level >= 2) {
            if ($member['level1'] > 0) {
                 $levelDb=M();
                 $sql='select distinct o.id from ims_ewei_shop_order o left join ims_ewei_shop_order_goods og on og.orderid=o.id where o.agentid in( " '.implode(',',array_keys($member['level1_agentids'])).' ")  and o.status>=3  and og.status2=0 and og.nocommission=0 and ('.$time.'- o.createtime >'.$day_times.') and nd o.uniacid='.$token.' group by o.id';
                 $level2_orders=$levelDb->query($sql);
                foreach ($level2_orders as $o) {
                    if (empty($o['id'])) {
                        continue;
                    }
                    $orderids[] = array(
                        'orderid' => $o['id'],
                        'level' => 2
                    );
                }
            }
        }
        //等级3的
         if ($level >= 3) {
            if ($member['level2'] > 0) {
                 $levelDb=M();
                 $sql='select distinct o.id from ims_ewei_shop_order o left join ims_ewei_shop_order_goods og on og.orderid=o.id where o.agentid in("'.implode(',', array_keys($member['level2_agentids'])).'")  and o.status>=3  and  og.status3=0 and og.nocommission=0 and ('.$time.'- o.createtime >'.$day_times.') and o.uniacid='.$token.' group by o.id';
                $level3_orders=$levelDb->query($sql);
                foreach ($level3_orders as $o) {
                    if (empty($o['id'])) {
                        continue;
                    }
                    $orderids[] = array(
                        'orderid' => $o['id'],
                        'level' => 3
                    );
                }
            }
        }
        // dump($level1_orders);
        // dump($level2_orders);
        // dump($orderids);
        // exit;
         $time = time();
        foreach ($orderids as $o) {
            $data=array('status'.$o['level'] => 1,
                  'applytime' . $o['level'] => $time
                );
            $params=array('orderid'=> $o['orderid'],
                       'uniacid' =>$token,
                );
            
            $shopOrderGoodsDb=D('ewei_shop_order_goods');
            $da1=$shopOrderGoodsDb->update($params,$data);
        }

           
           $applyno =$this->createNO('ewei_shop_commission_apply', 'applyno', 'CA');
        // dump($commission_ok);
        // exit;
            $apply   = array(
            'uniacid' => $token,
            'applyno' => $applyno,
            'orderids' => serialize($orderids),
            'mid' => $member['id'],
            'commission' => $commission_ok,
            'type' => $type,
            'status' => 1,
            'applytime' => $time
        );
           // dump($apply);exit;
            $commissionApplyDb=D('ewei_shop_commission_apply');
            $commissionApplyDb->insert($apply);
             // pdo_insert('ewei_shop_commission_apply', $apply);
             $this->outFormat($applyno,'提现成功!',200,'json');
            exit;

    }
    //可提现佣金
    public function commissionWithWithdrawals(){
        $token=I('get.token');
        $openid=I('get.openid');
        $data=$this->getInfo($token,$openid,array('ok'));
        $date['commissionWithdrawals']=$data['commission_ok'];
        $this->outFormat($date,'可提现佣金!',200,'json');
    }
}
