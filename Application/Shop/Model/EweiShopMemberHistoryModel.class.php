<?php
namespace Shop\Model;
use Think\Model;
/**
 *足迹  litianyou @ 2/27
 */
class EweiShopMemberHistoryModel extends Model{
    /**
     * 足迹列表 
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
            ->order('id DESC')
            ->select();
        $data['totalPage'] = $totalPage;
        $data['result'] = $result;
        return $data;
    }
    /**
     * 取消足迹多个 
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
     * 添加信息
     * @param  $data 添加的数据
     * @return 
     */
    public function insert($data){
        $result = $this->data($data)->add();
        return $result;
    }
    /**
     * 根据id查询
     * @param  $uniacid 商家id
     * @param  $openid  用户id
     * @param  $id   主键id     
     * @param  $field   查询字段
     * @return 
     */
    public function getByidFind($uniacid,$openid,$id,$field=''){
        $where = array(
            'uniacid' => $uniacid,
            'openid' => $openid,
            'id' => $id
        );
        $result = $this->where($where)->field($field)->find();
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
}