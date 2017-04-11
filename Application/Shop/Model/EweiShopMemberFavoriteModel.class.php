<?php
namespace Shop\Model;
use Think\Model;
class EweiShopMemberFavoriteModel extends Model{
    /**
     * 收藏列表 
     * @param  $uniacid
     * @param  $openid
     * @param  $p
     * @param  $limit
     * @return 
     */
    public function selectListPage($uniacid,$openid,$p,$limit=5){
        $where = array(
            'f.uniacid' => $uniacid,
            'f.openid' => $openid,
            'f.deleted'  => 0
        );
        $count = $this->alias('f')->where($where)->count();
        $Page = new \Think\Page($count, $limit);
        $totalPage = ceil($count / $limit);
        $field = 'f.id,f.goodsid,g.title,g.thumb,g.marketprice,g.productprice';
        $result =  $this
            ->alias('f')
            ->field($field)
            ->join('JOIN ims_ewei_shop_goods g  ON f.goodsid = g.id')
            ->where($where)
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->select();
        $data['totalPage'] = $totalPage;
        $data['result'] = $result;
        return $data;
    }
    /**
     * 取消收藏多个 
     * @param  $uniacid
     * @param  $openid
     * @param  $id  多个
     * @param  
     * @return 
     */
    public function deleteByid($uniacid,$openid,$ids){
        if (empty($ids)) {
            return 0;
        }
        $where = array(
            'uniacid' => $uniacid,
            'id' => array ('in',$ids)
        );
        $data['deleted'] = 1;
        $result = $this->where($where)->save($data);
        return $result;
    }
    /**
     * 查询收藏 
     * @param  $uniacid
     * @param  $openid
     * @param  $id  商品id
     * @param  
     * @return 
     */
    public function getByIdFind($uniacid,$openid,$id,$field=''){
        $where = array(
            'uniacid' => $uniacid,
            'openid' => $openid,
            'goodsid' => $id
        );
        $result = $this->field($field)->where($where)->find();
        return $result;
    }
    /**
     * 添加信息
     * @param  $data 添加的数据
     * @return 
     */
    public function insert($data){
        $result = $this->data($data)->add();
        return $result;
    }
    /**
     * 查询条件查询多条
     * @param  $uniacid 商家id
     * @param  $openid  用户id
     * @param  $where   查询条件     
     * @param  $field   查询字段
     * @param  $order   排序 
     * @return 
     */
    public function getByWhereSelect($uniacid,$openid,$where,$field='',$order){
        $result = $this->where($where)->field($field)->order($order)->select();
        return $result;
    }
    /**
     * 根据条件删除
     * @param  $uniacid 商家id
     * @param  $openid  用户id
     * @param  $where   查询条件     
     * @param  $field   查询字段
     * @param  $order   排序 
     * @return 
     */
    public function delBywhere($where){
        $result = $this->where($where)->delete();
        return $result;
    }
    /**
     * 设置收藏
     * @param  $data
     * @return 
     */
    public function setById($uniacid,$openid,$id,$delete=1){
        $where = array(
            'uniacid' => $uniacid,
            'openid' => $openid,
            'id' => $id
        );
        $data['deleted'] = $delete;
        $result = $this->where($where)->save($data);
        return $result;
    }
}