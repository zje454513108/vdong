<?php
namespace Shop\Controller\V2;
use Shop\Controller\V2\CommonController;
/**
 * litianyou @ 2017/2/15
 * 商城
 */
class GoodsController extends CommonController {
    /**
     * 获取商品详细信息
     * @param $token 商户ID
     * @param $goodsid 商品ID
     * @return 0成功返回
     */
    public function getGoodsDetail(){
        //获得商品ID
         $goodsid = I('get.goodsid');
         //uode商户ID
        $token = I('get.token');
        //获得用户openid;
         $openid = I('get.openid');
         // 如果存在openid 则查找收藏表
         if (!empty($openid)) {
            $favoriteDb = D('ewei_shop_member_favorite');
            $favorite=$favoriteDb->getByIdFind($token,$openid,$goodsid);
            if (empty($favorite)) {
                 $returnData['favorite']=0;
            }else{
                $returnData['favorite']=1;
            }
            //实例化浏览记录表
            $historyDb = D('ewei_shop_member_history');
            $historyWhere['goodsid']=$goodsid;
            $historyWhere['uniacid']=$token;
            $historyWhere['openid']=$openid;
            $historyWhere['deleted']=0;
            //如果没有足迹记录则添加进足迹表
            $history=$historyDb->getByWhereSelect($token,$openid,$historyWhere);

            if (empty($history)) {
            $historyInsert['openid']=$openid;
            $historyInsert['uniacid']=$token;
            $historyInsert['goodsid']=$goodsid;
            $historyInsert['deleted']=0;
            $historyInsert['createtime']=time();
                $historyDb->insert($historyInsert);
            }
         }
         //实例化商品表
        $eweiShopGoodsDb = D('ewei_shop_goods');
        //实例化规格表
        $goodsSpecDb=D('ewei_shop_goods_spec');
        //拼接搜索商品表条件
        $where['id']=$goodsid;
        $where['uniacid']=$token;
        //获得商品信息
        $goods=$eweiShopGoodsDb->goodsFindByWhere($where);
        //把商品里的多图系列化 转换
        $thumbnail=unserialize($goods['thumb_url']);
        //把商品图添加进数组
        array_unshift($thumbnail,$goods['thumb']);
         foreach ($thumbnail as $k => $v) {
           $thumbnail[$k]='http://uploads.qusaoba.net/'.$v;
        }

            // dump($thumbnail);exit;
        //如果查到商品则进入
        if (!empty($goods)) {
            //拼接搜索规格表条件
            $specWhere['goodsid']=$goodsid;
            $specWhere['uniacid']=$token;
            //获得规格
            $specList=$goodsSpecDb->specSelectByWhere($specWhere);
            // dump($specList);exit;
            if (empty($specList)) {
            //如果规格为空 则返回数据
                $spec['spec']='';
                $spec['id']='';
                $spec['marketprice']=$goods['marketprice'];
                $spec['stock']=$goods['stock'];
                $returnData['sku']='';
                $returnData['firstGoods']=$spec;
                $returnData['thumb']=$thumbnail;
                 $this->ajaxReturnSuccess(0,'OK', $returnData);

            }else{
                //如果规格不为空 则进入
                //实例化商品规格项目表
                $goodsSpecItemDb=D('ewei_shop_goods_spec_item');
                $firstGoods = array();
                //循环遍历规格表
                foreach ($specList as $k => $v) {
                    //规格标题
                    $specList[$k]['title']=$v['title'];
                    //拼接搜索规格项目表条件
                    $itemWhere['specid']=$v['id'];
                    $itemWhere['uniacid']=$token;
                    //获得规格项目表参数
                    $specList[$k]['item']=$goodsSpecItemDb->itemSelectByWhere($itemWhere);
                    //给规格项目表里的图片加上路径
                    // p($specList[$k]['item']);die;
                    foreach ($specList[$k]['item'] as $key => $val) {
                         $specList[$k]['item']['thumb']='http://uploads.qusaoba.net/'.$val['thumb'];
                         if($key == 0){
                            $firstGoods[] = $val['optionid'];
                         }
                          
                    }

                }
                // p($firstGoods);die;
                // dump($firstGoods);exit;
               if ($firstGoods) {
                //            获取默认属性的数据
                $optionsid = M('ewei_shop_goods_option')
                        ->where(array('uniacid' => $token, 'goodsid' => $goodsid))
                        ->field('id,specs,marketprice,stock')
                        ->select();
                foreach ($optionsid as $k1 => $v1) {
                    $specsid = explode('_', $v1['specs']);
                    $fanid = array_diff($firstGoods, $specsid);
                    if (empty($fanid)) {  //比较2个数组,如果为空的话就是直接 返回出所对应的optionid
//                $optionid = $v['id'];
                        $oldoption = $v1;
                    }
                }
            }

            // p($oldoption);die;
                //实例化商品属性表
                $shopOptionDb = D('ewei_shop_goods_option');
                //拼接搜索属性的条件
                $optionWhere['uniacid']=$token;
                $optionWhere['goodsid']=$goodsid;
                // $optionField['title']='title';
                //获得属性
                $optionData=$shopOptionDb->optionSelectByWhere($optionWhere,'title,marketprice');
                //设置初始最小价格 
                $min = $optionData[0]['marketprice']; 
                //设置初始最大价格
                $max = $optionData[0]['marketprice'];
                //如果有多条属性价格 设置最小价格 和最大价格
                foreach ($optionData as $k2 => $v2) {
                    if ($v2['marketprice'] < $min) {
                        $min = $v2['marketprice'];
                    }
                    if ($v2['marketprice'] > $max) {
                        $max = $v2['marketprice'];
                    }
                }
                //设置图片路径
                $goods['thumb'] = $thumbnail;
                if ($min != $max) {
                $min = $min < $goods['marketprice'] ? $min : $goods['marketprice'];
                $max = $max > $goods['marketprice'] ? $max : $goods['marketprice'];
                $goods['marketprice'] = "$min-$max"; //得到售价 是一个波动值 比如 1-10 元
                }
                // 返回数据
            $returnData['sku'] = $specList;
            $returnData['firstGoods'] = isset($oldoption) ? $oldoption : '';
            $returnData['data'] = $goods;
            $this->ajaxReturnSuccess(0,'OK', $returnData);
            
            }

        }else{
            $this->ajaxReturnError(101,'未找到商品');
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
        $ret['result'] = $errorName;
        $ret['Response']    =$result;
        $this->ajaxReturn($ret, 'json', true);
    }
    //JSON返回失败
    public function ajaxReturnError($errorCode, $errorName)
    {
        $ret['code'] = $errorCode;
        $ret['result'] = $errorName;
        // $ret['error'] = $error;
        $ret['Response']    =null;
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
        $token=I('get.token');
        //获得二级分类
        $ccate=I('get.ccate');
        //获得顶级分类
        $pcate=I('get.pcate');
        //获得三级分类
        $tcate=I('get.tcate');
        if (!empty($pcate)) {
           $where1['g.pcate']=$pcate;
            $where['pcate']=$pcate;
        }
        if (!empty($ccate)) {
           $where1['g.ccate']=$ccate;
            $where['ccate']=$ccate;
        }
        if (!empty($tcate)) {
           $where1['g.tcate']=$tcate;
            $where['tcate']=$tcate;
        }
        //是否促销
        $isdiscount=I('get.isdiscount');
        if (!empty($isdiscount)) {
           $where1['g.isdiscount']=$isdiscount;
            $where['isdiscount']=$isdiscount;
        }
        //拼接按评论排序的条件
        $where1['g.uniacid']=$token;
        $where1['g.deleted']=0;
        // $where1['g.isrecommand']=1;
        //拼接 销量 价格筛选的条件
        $where['uniacid']=$token;
        // $arr['deleted'] = 0;
        $where['deleted']=0;
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
        if (empty($page)) {
           $page==1;
        }
        if (empty($limit)) {
           $limit=10;
        }
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
        $data=$shopGoodsDb->goodsSelectByWhere($where,$order,$num,$limit);
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
        $this->ajaxReturnSuccess(0,'查找成功',$data);

    }
    //获取配送方式
    public function getDistributionMode(){
        $uniacid=I('get.uniacid');
        $shopDispatchDb=D('ewei_shop_dispatch');
        $whereDispatch['uniacid']=array('eq',$uniacid);
        $dispatch=$shopDispatchDb->findData($whereDispatch);
        $this->ajaxReturnSuccess(200,'查找成功',$dispatch);

    }
    public function getImage(){
         $set = A('Commission/Common');
        $uniacid_info = $set->getSysset('shop',I('get.token'));
        $set_img = getImgUrl($uniacid_info['img']);
        $set_logo = getImgUrl($uniacid_info['logo']);
        //banner 幻灯片图
        //实例化幻灯片表
        $banner=D("ewei_shop_adv");
        $where['enabled']=1;
        $where['uniacid']= I('get.token');
        //根据所属商户ID 查询幻灯片
        $date=$banner->getBannerList($where);
        foreach($date as &$v){
            if($v['thumb'] == ''){
                $v['thumb']=''.$v['thumb'];
            }else{
                $v['thumb']='http://uploads.qusaoba.net/'.$v['thumb'];
            }
        }
      
        // dump($data3);exit;
        $data['banner']=$date;
        $data['logo']   = $set_logo;
        $data['img']    =$set_img;

        if ($data) {
            $this->ajaxReturnSuccess(1,'查询成功',$data);
        }else{
            $this->ajaxReturnError(0,'没有数据');
        }
    }
    public function getGoods(){

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
        $data['goods']=$data2;
        $data['count']=$data3;
         if ($data) {
            $this->ajaxReturnSuccess(1,'查询成功',$data);
        }else{
            $this->ajaxReturnError(101,'没有数据');
        }
    }
    //获得分类列表
    public function getcClassification(){
        $token=I('get.token');
        // $openid=I('get.openid');
        if (empty($token)) {
          $this->ajaxReturnError(101,'不存在此商户!');
        }
        $classid=I('get.classid');
        $isrecommand=I('get.isrecommand');
         $categoryDb = D('ewei_shop_category');
         // dump($categoryDb);exit;
        if (empty($classid)) {
            if(!empty($isrecommand)) {
                $where['$isrecommand']=1;
            }
           $where['uniacid']=$token;
           $where['enabled']=1;
           $where['parentid']=0;
           $data=$categoryDb->getClassification($where);
        }else{
            if(!empty($isrecommand)) {
                $where['$isrecommand']=1;
            }
            $where['uniacid']=$token;
           $where['enabled']=1;
           $where['parentid']=$classid;
           $data=$categoryDb->getClassification($where);
        }
        // dump($pcate);exit;
       foreach ($data as $k => $v) {

           $data[$k]['thumb']='http://uploads.qusaoba.net/'.$v['advimg'];
       }
         $this->ajaxReturnSuccess(0,'查询成功',$data);

    }
    //同店推荐接口
    public function recommend(){
        //获得商户号
        $token=I('get.token');
        if (empty($token)) {
          $this->ajaxReturnError(101,'不存在此商户!');
        }
        //实例化商品表
        $goodsDb=D('ewei_shop_goods');
        $where['uniacid']=$token;
        $where['deleted']=0;
        $where['status']=1;
       $data=$goodsDb->goodsSelectByWhere($where);
       if ($data) {
           foreach ($data as $k => $v) {
              $data[$k]['thumb']='http://uploads.qusaoba.net/'.$v['thumb'];
           }
           $rand = array_rand($data,4); //随机取出4个键
           foreach ($rand as $key => $val) {
             $date[]=$data[$val];
           }
            $this->ajaxReturnSuccess(0,'OK',$date);
       }else{
          $this->ajaxReturnError(103,'此商户下不存在商品!');
       }
      
    }
}