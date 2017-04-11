<?php
namespace Shop\Model;
use Think\Model;
class EweiShopGoodsModel extends Model{
    //获取分类下商品列表接口 litianyou @ 2/16
    public function getGoods($data){
        $User = M('ewei_shop_goods');
        //print_r($where);die;
        if(empty($data['ccates'])){
            $aa['meta'] = array('code'=>'0','message'=>'参数不对,调用失败!');
            return $aa;
        }
        if($data['sort'] == '1'){ //销量
            $order = 'sales desc';
        }elseif($data['sort'] == '2'){ //售价从低到高
            $order = 'marketprice';
        }elseif($data['sort'] == '3'){//售价从高到低
            $order = 'marketprice desc';
        }else{
            $order = 'sales desc';
        }
        $where['ccate']=$data['ccates'];
        $where['uniacid']=$data['uniacid'];
        // dump($order);exit;
        // $where = array(
        //     'ccate' => $data['ccates'],
        //     'uniacid' => $data['uniacid']
        // );
        $data = $User->where($where)->field('id,title,thumb,marketprice,description')->order($order)->select(); //返回
        // $sql=$User->getLastSQL();
        // echo $sql;exit;
        if($data){
            foreach($data as &$v){
                if($v['thumb'] != ''){
                    $v['thumb']=C('IMAGE_RESOURCE').'/'.$v['thumb'];
                }
            }
            $aa['data'] = $data;
            $aa['meta'] = array('code'=>'1','message'=>'调用成功!');
            return $aa;
        }else{
            $aa['meta'] = array('code'=>'2','message'=>'无数据!');
            return $aa;
        }
    }
    public function tasses($uniacid){//同店推荐 litianyou litianyou @ 2/20
        if(empty($uniacid)){
            $aa['meta'] = array('code'=>'0','message'=>'参数错误!');
            return $aa;
        }
        $User = M('ewei_shop_goods');
        $where = "uniacid =  $uniacid";
        $data = $User->where("$where")->field('id,title,thumb,marketprice,description')->select();
        if($data){
            foreach($data as $rand=>$v){
                if($v['thumb'] != ''){
                    $data[$rand]['thumb']=C('IMAGE_RESOURCE').'/'.$v['thumb'];
                }
            }
            $rand = array_rand($data,4); //随机取出4个值
            foreach($rand as $k=>$v){
                $arr[] = $data[$v];
            }
            $aa['data'] = $arr;
            $aa['meta'] = array('code'=>'1','message'=>'调用成功!');
            return $aa;
        }else{
            $aa['meta'] = array('code'=>'0','message'=>'调用失败!');
            return $aa;
        }
    }
    public function getGoodprice($goodsid,$uniacid){
        $User = M('ewei_shop_goods');
        $where = array(
            'id' => $goodsid,
            'uniacid' =>$uniacid
        );
        $price = $User->where($where)->field('title,marketprice')->find();
        return $price;
    }
    /**
     * 限购
     * */
    public function getGoodmaxbuy($goodsid,$uniacid){
        $User = M('ewei_shop_goods');
        $where = array(
            'id' => $goodsid,
            'uniacid' =>$uniacid
        );
        $maxbuy = $User->where($where)->field('maxbuy')->find();
        return $maxbuy['maxbuy'];
    }
    //商品详情接口（图文介绍HTML格式）conten 2.16 litianyou
    public function goodcontent($d){
        $User = M('ewei_shop_goods');
        $where = "id = $d[goodsid] ";
        $where .= " and uniacid = $d[uniacid] ";
        $price = $User->where($where)->field('content')->find();
        return $price;
    }
    public function discount($fenis,$uniacid){//促销商品 litianyou @　2/20
        $User = M('ewei_shop_goods');
        if($fenis == 'isdiscount'){//促销
            $where = array(
                'isdiscount'=> 1,
                'uniacid'=> $uniacid
            );
        }elseif($fenis == 'isrecommand'){//推荐
            $where = array(
                'isrecommand'=> 1,
                'uniacid'=> $uniacid
            );
        }elseif($fenis == 'ishot'){//热卖
            $where = array(
                'ishot'=> 1,
                'uniacid'=> $uniacid
            );
        }elseif($fenis == 'isnew'){//新品
            $where = array(
                'isnew'=> 1,
                'uniacid'=> $uniacid
            );
        }else{
            $where = array(
                'isdiscount'=> 1,
                'uniacid'=> $uniacid
            );
        }
        $data = $User->where($where)->field('id,title,thumb,marketprice,description')->select();
        if($data){
            foreach($data as &$v){
                if($v['thumb'] != ''){
                    $v['thumb']=C('IMAGE_RESOURCE').'/'.$v['thumb'];
                }
            }
            $aa['data'] = $data;
            $aa['meta'] = array('code'=>'1','message'=>'调用成功!');
            return $aa;
        }else{
            $aa['meta'] = array('code'=>'0','message'=>'调用失败!');
            return $aa;
        }
    }
    /**
     * //搜索商品 litianyou @　2/21
     * @param type $data
     * @return string
     */
    public function search($data){ //模糊搜
        //return $data;
        $User = M('ewei_shop_goods');
        /*$where = array(
            'status' => 1,
            'uniacid' => $data['uniacid'],
            'title' => "like %$data[goodsname]%"
        );*/
        $where = "status = 1 ";
        $where .= "and uniacid = $data[uniacid]";
        $where .= " and title like '%$data[goodsname]%'";

//        综合搜索
        if(empty($data['price']) && empty($data['sales'])){
            $order = "marketprice DESC,sales DESC";
        }else if($data['price'] && $data['sales']){
            $aa['meta'] = array('code'=>'0','message'=>'非法操作!');
            return $aa;
        }
        //        价格
        if($data['price'] == 1){
            $order = "marketprice ASC";
        } else if($data['price'] == 2){
            $order = "marketprice DESC";
        }
//       销量
        if($data['sales'] == 1){
            $order = "sales ASC";
        } else if($data['sales'] == 2){
            $order = "sales DESC";
        }
        $data = $User->where("$where")
                ->field('id,title,thumb,marketprice,description')
                ->order($order)
                ->select();
        if($data){
            foreach($data as &$v){
                if($v['thumb'] != ''){
                    $v['thumb']=C('IMAGE_RESOURCE').'/'.$v['thumb'];
                }
            }
            $aa['data'] = $data;
            $aa['meta'] = array('code'=>'1','message'=>'调用成功!');
            return $aa;
        }else{
            $aa['meta'] = array('code'=>'0','message'=>'调用失败!');
            return $aa;
        }
    }
    public function goodsall($data){//所有商品 litianyou @　2/21
        //return $data;
        $User = M('ewei_shop_goods');
        $where = array(
            'deleted' => 0,
            'status' => 1,
            'uniacid' => $data['uniacid'],
        );
        if($data['sort'] == '1'){ //销量
            $order = 'sales desc';
        }elseif($data['sort'] == '2'){ //售价从低到高
            $order = 'marketprice';
        }elseif($data['sort'] == '3'){//售价从高到低
            $order = 'marketprice desc';
        }else{
            $order = 'displayorder desc';
        }
        $data = $User->where($where)
            ->field('id,title,thumb,marketprice,description')
            ->order($order)
            ->select();
        if($data){
            foreach($data as &$v){
                if($v['thumb'] != ''){
                    $v['thumb']=C('IMAGE_RESOURCE').'/'.$v['thumb'];
                }
            }
            $aa['data'] = $data;
            $aa['meta'] = array('code'=>'1','message'=>'调用成功!');
            return $aa;
        }else{
            $aa['meta'] = array('code'=>'0','message'=>'调用失败!');
            return $aa;
        }
    }
    /*
    根据条件查询商品表首页信息
    Input:
        查询条件
        
    Output:
        list 
        查询商品名 商品图 原价 现价
    */
    public function goodsSelectById($where2,$num,$limit){
        $userList=$this->field('id,title,thumb,productprice,marketprice')->where($where2)->order('displayorder desc,createtime desc')->limit($num,$limit)->select();
        return $userList;
    }
    /*
    根据条件查询商品表首页信息
    Input:
        查询条件
        
    Output:
        list 
        查询商品名 商品图 原价 现价
    */
    public function goodsSelectByWhere($where2,$order,$page,$limit){
        $userList=$this->field('id,title,thumb,productprice,marketprice')->where($where2)->order($order)->limit($page,$limit)->select();
        return $userList;
    }
    /*
    根据ID查询商品
    Input:
        ID
        
    Output:
        list 
        查询商品名 商品图 原价 现价
    */
    public function goodsFindByWhere($where){
        $data=$this->field('id,title,total,sales,thumb,marketprice,productprice,description,thumb_url')->where($where)->find();
        return $data;
    }
      /*
    根据条件统计商品表首页商品
    Input:
        查询条件
        
    Output:
        list 
        查询商品名 商品图 原价 现价
    */
    public function goodsCount($where){
        $userList=$this->where($where)->count();
        return $userList;
    }
    /**
     * 根据商品id获取商品 
     * @param  $uniacid
     * @param  $openid
     * @param  $id  商品id
     * @param  $field 
     * @return 
     */
    public function getByIdFind($uniacid,$id,$field=''){
        $where = array(
            'uniacid' => $uniacid,
            'id' => $id
        );
        $result = $this->field($field)->where($where)->find();
        return $result;die;
    }
    
    /**
     * 查询单个商品
     * @param type $where
     * @param type $field
     * @return boolean
     */
    public function findGoods($where,$field=''){
        if(empty($where)){
            return false;
        }
        $field = $field ? $field : '*';
        $result = $this->field($field)->where($where)->find();
        return $result;
    }

}