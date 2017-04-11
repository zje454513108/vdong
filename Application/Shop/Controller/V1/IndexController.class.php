<?php
namespace Shop\Controller;
use Think\Controller\RestController;
use Shop\Model;
/**
 * litianyou @ 2017/2/15
 * 商城
 */
header("content-type:text/html;charset=utf-8");
class IndexController extends RestController {
    //所有的商品分类 api 2.15 litianyou
    /**
     * 分类列表
     */
    public function getClassify(){
        $data['uniacid'] = I('uniacid');
        $data['openid'] = I('openid');
        $data['classsid']   = I('classsid');
        $aa = D('EweiShopCategory');
        $a = $aa->getClassify($data);
        //print_r($a);die;
        $this->response($a,'json');
    }
    //获取分类商品列表 api 2.15 litianyou
    public function classgoodlists(){
        $classid = I('get.classsid');  //参数  类别id
        $uniacid = I('uniacid');
        // dump($classid);
        // echo'1';
        // exit; 
        //参数  类别id
        $aa = D('EweiShopCategory');
        $a = $aa->classgoodlists($classid,$uniacid);
        //print_r($a);die;
        $this->response($a,'json');
    }
    //商品详情接口 2.16 litianyou
    public function getDetail(){
        $d['goodsid'] = I('goodsid');
        $d['openid'] = I('openid'); //用户openid
        $d['uniacid'] = I('uniacid'); //商户id
        $aa = D('EweiShopCategory');
        $a = $aa->getDetail($d);
        $this->response($a,'json');
    }
    //商品详情内容（html）content 2.16 litianyou
    public function goodcontent(){
        $d['goodsid'] = I('goodsid');
        $d['uniacid'] = I('uniacid'); //商户id
        $aa = D('EweiShopGoods');
        $a = $aa->goodcontent($d);
        $this->response($a,'json');
    }
    //商品参数接口 litianyou @ 2/21
    public function param(){
        $data['goodsid'] = I('goodsid'); //1219
        $data['uniacid'] = I('uniacid'); //商户id
        $aa = D('EweiShopGoodsParam');
        $a = $aa->param($data);
        $this->response($a,'json');
    }
    //获取分类商品列表接口 2.15 litianyou
    public function getGoods(){
        $data['uniacid'] = I('uniacid'); //商户ID
        $data['sort'] = I('get.sort'); // 参数 排序方式  方式 post
        $data['ccates'] = I('get.ccates'); // 参数 二级分类的id  方式 post
        //print_r($ccates);die;
        $aa = D('EweiShopGoods');
        $a = $aa->getGoods($data);
        //print_r($a);die;
        $this->response($a,'json');
    }
    //获取商品sku接口 2.16 litianyou
    public function getGoodsku(){
        $goodsid = I('goodsid'); // 参数  商品id  方式 post
        $uniacid = I('uniacid'); // 参数 商户ID
        $aa = D('EweiShopGoodsOption');
        $a = $aa->getGoodsku($goodsid,$uniacid);
        //print_r($a);die;
        $this->response($a,'json');
    }
    //用户评价展示接口 2.16 litianyou  ing
    public function comment(){
        $data['goodsid'] = I('get.goodsid'); // 参数  商品id  方式 post
        $data['uniacid'] = I('uniacid'); // 参数 uniacid
        $aa = D('EweiShopOrderComment');
        $a = $aa->comment($data);
        //print_r($a);die;
        $this->response($a,'json');
    }
    /**
     * 加入购物车接口  2.15 litianyou  ing
     */
    public function gwcadd(){
        $data = array();
        $goodsid = I('goodsid'); //商品id
        $optionid = I('optionid'); //商品的属性sku id .post
        $data['uniacid'] = I('uniacid'); //商户ID
        $data['openid'] = I('openid'); //用户openid
        $data['goodsid'] = I('goodsid'); //商品id
        $data['optionid'] = I('optionid'); //商品的属性sku id .post
        $data['total'] = I('total');
        $data['createtime']=time();
        $data['deleted']=0;
        if (empty($data['uniacid']) || empty($data['openid']) || empty($data['total']) || empty($data['goodsid'])) {
            $aa['meta'] = array('code' => '0', 'message' => "参数缺少");
            $this->response($aa,'json');exit();
        }
        /* 限购*/
//        $maxbuy = D('EweiShopGoods');
//        $goodsskuprice = $maxbuy->getGoodmaxbuy($goodsid,$data['uniacid']);
//        if($goodsskuprice<$data['total']){
//            $aa['meta'] = array('code' => '0', 'message' => "超出限购");
//            $this->response($aa,'json');exit;
//        }
        //print_r($optionid);die;
        if(empty($optionid) || $optionid=='0'){
            $good = D('EweiShopGoods');
            $goodsprice = $good->getGoodprice($goodsid);
            if(!empty($goodsprice)){
                $data['marketprice'] = $goodsprice['marketprice'];
            }
        }else{
            $aa = D('EweiShopGoodsOption');
            $goodsskuprice = $aa->getGoodskuprice($optionid,$data['uniacid']);
            if(!empty($goodsskuprice)){
                $data['marketprice'] = $goodsskuprice;
            }
        }
        $aa = D('EweiShopMemberCart');
        $a = $aa->gwcadd($data);
        //print_r($a);die;
        $this->response($a,'json');
    }
    public function gwclist(){ //购物车列表API  litianyou @ 2.17  c=index&a=gwclist&openid=oJ17UjmkDWm6KWpuysl1t3WAorjw&uniacid=66
        $data['openid']= I('openid'); //用户openid web获取  oJ17UjmkDWm6KWpuysl1t3WAorjw
        $data['uniacid'] = I('uniacid'); //商户ID web获取 66
        $aa = D('EweiShopMemberCart');
        $gwclist = $aa->gwclist($data);
        //print_r($gwclist);die;
        $this->response($gwclist,'json');
    }
    public function gwcsn(){//更新购物车商品数量 litianyou @ 2/20
        $data['goodscartid']= I('goodscartid'); //购物车表id 453
        $data['goodssn']= I('goodssn'); //前台选择商品数量
        $data['uniacid'] = I('uniacid'); //商户ID web获取 66
        $data['openid'] = I('openid'); //商户ID web获取 66
        $aa = D('EweiShopMemberCart');
        $a = $aa->gwcsn($data);
        $this->response($a,'json');
    }
    public function gwcdel(){//购物车删除（可以批量删除） litianyou @ 2/20
        $goodscartid = I('goodscartid');
        $data['goodscartid']=$goodscartid;  //implode(',',array('453'));
        $data['uniacid'] = I('uniacid'); //商户ID web获取 66
        $aa = D('EweiShopMemberCart');
        $a = $aa->gwcdel($data);
        //print_r($a);die;
        $this->response($a,'json');
    }
    /**
     * 购物车移入收藏
     */
    public function favorite_gwc(){ //再购物车里收藏 litianyou @ 2/20
        $data['uniacid'] = I('uniacid'); //商户ID web获取 66
        $data['openid'] = I('openid'); //I('openid'); //用户openid web获取  oJ17UjmkDWm6KWpuysl1t3WAorjw
        $data['goodscartid'] = I('goodscartid'); //"'".implode("','",array('453','452') )."'";  //implode(',',array('453'));
        $data['uniacid'] = I('uniacid'); //商户ID web获取 66
        $aa = D('EweiShopMemberCart');
        $a = $aa->favorite_gwc($data);
        //print_r($a);die;
        $this->response($a,'json');
    }
    public function favorite(){//在商品详情页里收藏 litianyou @ 2/28
        $data['goodid'] = I('goodid');  //implode(',',array('453'));  ims_ewei_shop_member_history
        $data['openid']= I('openid');
        $data['uniacid'] = I('uniacid'); //商户ID web获取 66
        $aa = D('EweiShopMemberFavorite');
        $a = $aa->favorite($data);
        $this->response($a,'json');
    }
    public function unfavorite(){//在商品详情页里取消收藏 litianyou @ 2/28
        $data['goodid'] = I('goodid');  //implode(',',array('453'));  ims_ewei_shop_member_history
        $data['openid']= I('openid');
        $data['uniacid'] = I('uniacid'); //商户ID web获取 66
        $aa = D('EweiShopMemberFavorite');
        $a = $aa->unfavorite($data);
        $this->response($a,'json');
    }
    public function buynow(){ //立即购买
        $data = array();
        $data['uniacid'] = I('uniacid'); //商户ID
        $data['openid'] = I('openid');  //用户openID
        $data['total'] = I('total');  //数量
        $data['goodsid'] = I('goodsid'); //商品ID
        $data['optionid'] = I('optionid'); //商品的属性sku id
        if (empty($data['uniacid']) || empty($data['openid']) || empty($data['total']) || empty($data['goodsid'])) {
            $aa['meta'] = array('code' => '0', 'message' => "参数缺少");
            $this->response($aa, 'json'); exit;
        }
        if(empty($data['optionid']) || $data['optionid']=='0'){
            $good = D('EweiShopGoods');
            $arr = $good->getGoodprice($data['goodsid'],$data['uniacid']);
            if(!empty($arr)){
                $data['marketprice'] = $arr['marketprice'];
                $data['title'] = '';
                $data['goodsname'] = $arr['title'];
            }
        }else{
            $aa = D('EweiShopGoodsOption');
            $arr = $aa->getGoodskuprice($data['optionid'],$data['uniacid']);
            //print_r($arr);die;
            $good = D('EweiShopGoods');
            $ar = $good->getGoodprice($data['goodsid'],$data['uniacid']);
            if(!empty($ar)){
                $data['marketprice'] = $arr['marketprice'];
                $data['title'] = $arr['title'];
                $data['goodsname'] = $ar['title'];
            }
        }
        $order = D('EweiShopOrder');
        //print_r($data);die;
        $a = $order->buynow($data);
        $this->response($a,'json');
    }
    public function buynows(){ //立即购买下订单
        $data = array();
        $data['uniacid'] = I('uniacid');
        $data['openid'] = I('openid');
        $data['total'] = I('total');
        $data['goodsid'] = I('goodsid');
        $dispatch['dispatch'] = I('dispatch');
        $dispatch['uniacid'] = I('uniacid');
        $data['addressid'] = I('addressid'); //收货地址ID
        $data['optionid'] = I('optionid'); //商品的属性sku id
        if (empty($data['uniacid']) || empty($data['openid']) || empty($data['total']) || empty($data['goodsid']) || empty($data['addressid'])) {
            $aa['meta'] = array('code' => '0', 'message' => "参数缺少");
            $this->response($aa, 'json'); exit;
        }
        $address = M('ewei_shop_member_address');
        $whereaddress = array(  //查出收货地址
            'openid' => $data['openid'],
            'id' => $data['addressid'], // 前端传来的收货地址ID
            'uniacid' => $data['uniacid'],
            'isdefault' => 1,
            'deleted' => 0
        );
        $address = $address->where($whereaddress)->field('id,realname,mobile,address')->find();  //收货地址
        if(!empty($address)){
            $b = serialize($address);
        }else{
            $aa['meta'] = array('code' => '2', 'message' => "非法操作");
            $this->response($aa, 'json'); exit;
        }
        if(empty($data['optionid']) || $data['optionid']=='0'){ //没有属性id
            $good = D('EweiShopGoods');
            $arr = $good->getGoodprice($data['goodsid'],$data['uniacid']); //查询商品的售价，商品名
            if(!empty($arr)){
                $data['marketprice'] = $arr['marketprice'];
                $data['title'] = '';
                $data['goodsname'] = $arr['title'];
            }
        }else{  //有optionid
            $option = D('EweiShopGoodsOption');
            $arr = $option->getGoodskuprice($data['optionid'],$data['uniacid']);//查有optionid的商品信息
            //p($arr);die;
            $good = D('EweiShopGoods');
            $ar = $good->getGoodprice($data['goodsid'],$data['uniacid']); //查商品名
            if(!empty($ar)){
                $data['marketprice'] = $arr['marketprice'];
                $data['title'] = $arr['title'];
                $data['goodsname'] = $ar['title'];
            }
        }
        //p($data);die;
        $goods = D('EweiShopGoods');
        $wheregood = array(
            'id' => $data['goodsid']
        );
        $img = $goods->where($wheregood)->field('thumb,goodssn,productsn')->find(); //返回商品图片编号，和条码
        //p($img);die;
        $aa['thumb'] = C('IMAGE_RESOURCE') . '/' . $img['thumb'];
        $dispatchDb = D('EweiShopDispatch'); //运费
        $dispatchdata = $dispatchDb->getdispatch($dispatch);
        $aa['distribution'] = $dispatchdata;
        $aa['memberid'] = $data['memberid'];
        $aa['goodsname'] = $data['goodsname'];
        $aa['title'] = $data['title'];
        $prices = $data['marketprice'] * $data['total']; //单价*数量
        $aa['prices'] = $data['marketprice'];
        $pricesy = $prices + $aa['distribution'];
        $mj = D('EweiShopSysset'); //满减
        $mj = $mj->getmj($data['uniacid']);
        if ($prices >= $mj['enoughmoney']) {
            $aa['marketprice'] = $pricesy - $mj['enoughdeduct'];
            $aa['m'] = $mj['enoughmoney'];
            $aa['j'] = $mj['enoughdeduct'];
        } else {
            $aa['marketprice'] = $pricesy;
            $aa['j'] = 0;
        }
        //return $aa;
        $aa['total'] = $data['total'];
        $aa['prices'] = $prices;
        $ar = array();
        while (count($ar) < 6) {
            $ar[] = rand(0, 9);
            $ar = array_unique($ar);
        }
        $arr['ordersn'] = 'SH' . date('YmdHis') . implode("", $ar); //订单号
        $arr['openid'] = $data['openid'];  //openid
        $arr['uniacid'] = $data['uniacid']; //商户id
        $arr['goodsprice'] = $prices; // 原始订单金额
        $arr['oldprice'] = $pricesy;
        $arr['dispatchprice'] = $aa['distribution'];
        $arr['olddispatchprice'] = $aa['distribution'];
        $arr['price'] =$aa['marketprice']; //支付订单金额
        $arr['discountprice'] = $aa['j']; //优惠金额
        $arr['remark'] = $data['remark']; //留言
        $arr['addressid'] = $data['addressid']; //收货地址id
        $arr['createtime'] = time(); //下单时间
        $arr['address'] = $b; //收货地址
        //p($arr);die;
        $model = M();
        $model->startTrans(); //开启事务
        $order = M('ewei_shop_order');
        $orderadd = $order->add($arr);  // 添加到订单表
        $order->where(array('id'=>$orderadd))->save(array('address'=>$arr['address']));
        if ($orderadd) {
            $orderg = M('ewei_shop_order_goods');  //
            //return $data1;
            $ordergoods['uniacid'] = $data['uniacid']; //商户id
            $ordergoods['orderid'] = $orderadd; //订单ID
            $ordergoods['goodsid'] = $data['goodsid']; //商品id
            $ordergoods['price'] = $data['marketprice']; //商品价格
            $ordergoods['total'] = $data['total']; //数量
            $ordergoods['optionid'] = $data['optionid'] ? $data['optionid'] : 0; //属性id
            $ordergoods['createtime'] = time(); //创建时间
            $ordergoods['optionname'] = $data['title']; //属性名称
            $ordergoods['realprice'] =$aa['marketprice'];
            $ordergoods['oldprice'] = $data['marketprice'];
            $ordergoods['goodsn'] = $img['goodssn'];
            $ordergoods['productsn'] = $img['productsn'];
            $order_good = $orderg->add($ordergoods); //加入订单商品表
            if($orderadd && $order_good){
                $model->commit(); //事务提交
            }else{
                $model->rollback(); //回滚
            }
            $orderaddo['orderid'] = $orderadd;
            $orderaddo['status'] = '0';  //未付款
            $orderaddo['price'] = $aa['marketprice'];
            $orderaddo['ordersn'] = $arr['ordersn'];
            $orderaddo['meta'] = array('code' => '1', 'message' => "调用成功");
        }else{
            $orderaddo['meta'] = array('code' => '0', 'message' => "添加订单失败");
        }
        $this->response($orderaddo,'json');
    }
    public function favoritedel(){//删除收藏（可以批量删除） litianyou @ 2/27
        $data['favoriteid']= I('favoriteid');  //implode(',',array('453'));
        $data['openid']= I('openid');
        $data['uniacid'] = I('uniacid'); //商户ID web获取 66
        $aa = D('EweiShopMemberFavorite');
        $a = $aa->favoritedel($data);
        $this->response($a,'json');
    }
    public function favoritelist(){//我的收藏列表 litianyou @ 2/28
        $data['uniacid']= I('uniacid'); //I('uniacid'); //商户ID web获取 66
        $data['openid']= I('openid'); //I('openid'); //用户openid web获取  oJ17UjmkDWm6KWpuysl1t3WAorjw
        $user = D('EweiShopMemberFavorite');
        $arr = $user->favoritelist($data);
        $this->response($arr,'json');
    }
    /**
     * 删除足迹--修改
     */
    public function historydel(){//删除足记（可以批量删除） litianyou @ 2/27 c=index&a=historydel&openid=oJ17UjmkDWm6KWpuysl1t3WAorjw&uniacid=66&historyid=8058,8059
        $data['historyid'] = I('historyid');  //implode(',',array('453'));  ims_ewei_shop_member_history
        $data['openid']= I('openid');
        $data['uniacid'] = I('uniacid'); //商户ID web获取 66
        $aa = D('EweiShopMemberHistory');
        $a = $aa->historydel($data);
        $this->response($a,'json');
    }
    public function discount(){//促销商品 litianyou litianyou @ 2/21
        $fenis = I('fenis');//不同的分类名称（热销,新品...）
        $uniacid = I('uniacid');//不同的分类名称（热销,新品...）
        $user = D('EweiShopGoods');
        $gwclist = $user->discount($fenis,$uniacid);
        $this->response($gwclist,'json');
    }
    public function notice(){//店铺公告 litianyou litianyou @ 2/20
        $data['uniacid'] = I('uniacid');
        $user = D('EweiShopNotice');
        $gwclist = $user->notice($data);
        $this->response($gwclist,'json');
    }
    public function tasses(){//同店推荐 litianyou litianyou @ 2/20
        $uniacid = I('uniacid');//不同的分类名称（热销,新品...）
        $user = D('EweiShopGoods');
        $data = $user->tasses($uniacid);
        $this->response($data,'json');
    }
    public function member_history(){//我的足迹列表 litianyou @ 2/20
        $data['uniacid']= I('uniacid'); //I('uniacid'); //商户ID web获取 66
        $data['openid']= I('openid'); //I('openid'); //用户openid web获取  oJ17UjmkDWm6KWpuysl1t3WAorjw
        //print_r($data);die;
        $user = D('EweiShopMemberHistory');
        $arr = $user->member_history($data);
        //print_r($arr);die;
        $this->response($arr,'json');
    }
    
    
    /**
     * //商品搜索 litianyou @ 2/21
     */
    public function search(){
        $data['uniacid']= I('get.uniacid','','intval'); //商户ID web获取 66
        $data['goodsname']= I('get.goodsname','','string'); //商品名称 web获取
        if(empty($data['goodsname']) || empty($data['uniacid'])){
            $arr = array(
                'code'=>'0',
                'message'=>'参数错误!'
            );
            $this->response($arr,'json');
        }
        $data['openid'] = I('get.openid','','string');//用户主键id
        $data['price'] = I('get.price','','intval');
        $data['sales']  = I('get.sales','','intval');
        $user = D('EweiShopGoods');
        $arr = $user->search($data);
        $this->response($arr,'json');
    }
    public function goodsall(){//这家店铺所有的商品  @ litianyou 2/21
        $data['uniacid']= I('uniacid'); //商户ID web获取 66
        $data['sort']= I('sort'); //商户ID web获取 66
        $user = D('EweiShopGoods');
        $arr = $user->goodsall($data);
        $this->response($arr,'json');
    }
    public function tdtj(){//同店推荐
        $data['uniacid']= I('uniacid'); //商户ID web获取 66
        $data['sort']= I('sort'); //商户ID web获取 66
        $user = D('EweiShopGoods');
        $arr = $user->goodsall($data);
        $this->response($arr,'json');
        //$Blog = A('Admin/Blog','Event');
    }

    /**
     * xiugai 存在问题------------------------
     *litianyou @ 3/17
     *  多属性的拼optionid
     * */
    public function getoptionid(){
        $data['option'] = I('option');//这个是前端传过来的所有的属性ID 比如一个红色的ID是（1）和一个尺寸的id（3），不用管顺序了  1,3
       
        $data['uniacid'] = I('uniacid');
        $data['openid'] = I('openid');
        $data['goodsid'] = I('goodsid');
        if (empty($data['uniacid']) || empty($data['openid']) || empty($data['goodsid'])){
            $aa['meta'] = array('code' => '0', 'message' => "参数缺少");
            $this->response($aa, 'json'); exit;
        }
        $data['option'] = substr($data['option'],-1) == ',' ? substr($data['option'], 0,-1) : false;
        // if(empty($data['option'])){
        //     $aa['meta'] = array('code' => '0', 'message' => "属性为空",'data'=>);
        //     $this->response($aa, 'json'); exit;
        // }

        $option = D('ewei_shop_goods_option');
        $where = array(
            'uniacid' => I('uniacid'),
            'goodsid' => I('goodsid')
        );
        $optionsid =$option->where($where)->field('id,specs,marketprice,stock')->select();

        $chaid = explode(',',$data['option']);

        foreach($optionsid as $k=>$v){
            $specsid = explode('_',$v['specs']);
            $fanid = array_diff($chaid,$specsid);
            if(empty($fanid)){  //比较2个数组,如果为空的话就是直接 返回出所对应的optionid
//                $optionid = $v['id'];
                $oldoption = $v;
            }
        }
       
            $aa['data'] = array('optionid' =>$oldoption['id'],'marketprice'=>$oldoption['marketprice'],'stock'=>$oldoption['stock'], 'message' => "调用成功");
            $this->response($aa,'json');
    

    }

    /**
     * 幻灯片查询
     * @param $uniacid 商户ID
     *
     * @return 0成功返回$token 1失败返回失败
     */
    public function index(){
//        查询商户logo、img
        $set = A('Commission/Common');
        $uniacid_info = $set->getSysset('shop',I('uniacid'));
        $set_img = getImgUrl($uniacid_info['img']);
        $set_logo = getImgUrl($uniacid_info['logo']);
        //banner 幻灯片图
        //实例化幻灯片表
        $banner=D("ewei_shop_adv");
        $where['enabled']=1;
        $where['uniacid']= I('uniacid');
        //根据所属商户ID 查询幻灯片
        $date=$banner->getBannerList($where);
        foreach($date as &$v){
            if($v['thumb'] == ''){
                $v['thumb']=''.$v['thumb'];
            }else{
                $v['thumb']='http://uploads.qusaoba.net/'.$v['thumb'];
            }
        }
        // //商品一级分类
        // //实例化商品类别表
        // $category=D('ewei_shop_category');
        // $where1['parentid']=0;
        // $where1['uniacid']=I('get.uniacid');
        // $where1['enabled']=1;
        // //查找一级分类
        // $date1=$category->getCategoryList($where1);
        // dump($data1);exit;
        //推荐商品列表 分页刷新 每次刷新6个
        //$limit 条数
        //$page 页数
        $limit=$_GET['limit'];
        $page=$_GET['page'];
        if ($page==1) {
            $num=0;
        }else{
            $num=($page-1)*$limit;
        }
        //实例化商品表
        $goods=D('ewei_shop_goods');
        $where2['isrecommand']=1;
        $where2['deleted']=0;
        $where2['status']=1;
        $where2['uniacid']=I('get.uniacid');
        // $where1['uniacid']=I('get.uniacid');
        // $where2['enabled']=1;
        //根据商户ID 和推荐 查找商品
        $date2 = $goods->goodsSelectById($where2,$num,$limit);
        foreach($date2 as &$v){
            if($v['thumb'] == ''){
                $v['thumb']=''.$v['thumb'];
            }else{
                $v['thumb']='http://uploads.qusaoba.net/'.$v['thumb'];
            }
        }
        $data3=$goods->goodsCount($where2);
        // dump($data3);exit;
        $data['banner']=$date;
        $data['goods']=$date2;
        $data['count']=$data3;
        $data['logo']   = $set_logo;
        $data['img']    =$set_img;

        if ($data) {
            $this->ajaxReturnSuccess(1,'查询成功',$data);
        }else{
            $this->ajaxReturnError(0,'没有数据');
        }
    }
    //JSON返回成功
    /*
    查询轮播图
    Input:
         $errorCode
        $errorName
        $result
    Output:

   */
    public function ajaxReturnSuccess($errorCode, $errorName,$result)
    {

        $ret['code'] = $errorCode;
        $ret['msg'] = $errorName;
        $ret['data']    =$result;
        $this->ajaxReturn($ret, 'json', true);
    }
    //JSON返回失败
    public function ajaxReturnError($errorCode, $errorName)
    {
        $ret['code'] = $errorCode;
        $ret['msg'] = $errorName;
        // $ret['error'] = $error;
        $ret['data']    =null;
        $this->ajaxReturn($ret, 'json', true);
    }
    /**
     * 根据条件 筛选商品排序
     * @param $uniacid 商户ID
     *
     * @return 
     */
    public function orderByCondition(){
        //获得商户ID
        $uniacid=I('get.uniacid');
        //获得分类ID
        $ccate=I('get.ccate');
        //获得顶级分类
        $classid=I('get.classsid');
        if (!empty($ccate)) {
           $where1['g.ccate']=$ccate;
            $where['ccate']=$ccate;
        }
        if (!empty($classid)) {
           $where1['g.pcate']=$classid;
            $where['pcate']=$classid;
        }
        //拼接按评论排序的条件
        $where1['g.uniacid']=$uniacid;
        $where1['g.isrecommand']=1;
        //拼接 销量 价格筛选的条件
        $where['uniacid']=$uniacid;
        //接收传过来的排序条件 销量
        $sales=I('get.sales');
        //接收传过来的排序条件 评价
        $comment=I('get.comment');
        //接收传过来的排序条件 价格
        $marketprice=I('get.marketprice');
        //$limit 条数
        //$page 页数
        $limit=$_GET['limit'];
        $page=$_GET['page'];
        if ($page==1) {
            $num=0;
        }else{
            $num=($page-1)*$limit;
        }
        // $arr=array('order','id'=>'desc');
        // dump($arr);
        // exit;
        //实例化商品表
        $shopGoodsDb=D('ewei_shop_goods');
        if (!empty($sales)) {
            //如果销量字段不为空 根据销量排序
           $order='sales desc';
        }
        if (!empty($marketprice)) {
            //如果价格字段不为空 根据价格排序
            $order='marketprice asc';
        }
        // dump($where);exit;
        $data=$shopGoodsDb->goodsSelectByWhere($where,$order);
        // $commentDb=D('ewei_shop_order_comment');

            // $db=M();
        if (!empty($comment)) {
            //如果评论字段不为空 根据评论排序
            $data=$shopGoodsDb->alias('g')->field('g.id,title,thumb,marketprice,productprice,IFNULL(SUM(level),0) as level_total')->join('LEFT JOIN ims_ewei_shop_order_comment as c on g.id=c.goodsid')->where($where1)->group('g.id')->order('level_total desc')->limit($num,$limit)->select();
       // $sql="SELECT g.id, title,thumb,marketprice,productprice,g.isrecommand,IFNULL(SUM(`level`),0)as levelcount from ims_ewei_shop_goods as g LEFT JOIN  ims_ewei_shop_order_comment as c on g.id=c.goodsid WHERE g.uniacid=".$uniacid." and isrecommand = 1 GROUP BY g.id  ORDER BY levelcount DESC LIMIT ".$num.','.$limit;
       // $data=$db->query($sql);
            // $sql=$shopGoodsDb->getLastSQL();
        }
        foreach ($data as $key => $val) {
            //得到的数据 遍历 给图片加上路径
            $data[$key]['thumb']='http://uploads.qusaoba.net/'.$val['thumb'];
        }
        //返回数据
        $this->ajaxReturnSuccess(200,'查找成功',$data);

    }
    //获取配送方式
    public function getDistributionMode(){
        $uniacid=I('get.uniacid');
        $shopDispatchDb=D('ewei_shop_dispatch');
        $whereDispatch['uniacid']=array('eq',$uniacid);
        $dispatch=$shopDispatchDb->findData($whereDispatch);
        $this->ajaxReturnSuccess(200,'查找成功',$dispatch);

    }
}