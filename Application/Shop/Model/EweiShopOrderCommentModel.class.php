<?php
namespace Shop\Model;
use Think\Model;
/**
 * litianyou @ 2017.2.15
 * 用户评论列表接口
 * */
class EweiShopOrderCommentModel extends Model{
    /**
     * 用户评论列表接口 litianyou 2.17
     *一个订单所有的评论都在一条数据中，只能评论3次，商家的回复也在这个表中
     */
    public function comment($data){
        $User = M('ewei_shop_order_comment');
        $where = array(
            'goodsid'=>$data['goodsid'],
            'uniacid'=>$data['uniacid']
        );
        if(empty($data['goodsid'])){
            $aa['meta'] = array('code'=>'0','message'=>'参数不对,调用失败!');
            return $aa;
        }
        $data = $User->where($where)->field('id,nickname,headimgurl,level,content,images,append_content,append_images,reply_content,reply_images,append_reply_content,append_reply_images')->order('id desc')->select();
        //return $data;
        if($data){
            foreach($data as &$v){  //a:3:{i:0;s:52:"images/66/2017/02/HOhg2CG2hhgOzGO5hOPCHhHOGG3P6K.jpg";i:1;s:52:"images/66/2017/02/HOhg2CG2hhgOzGO5hOPCHhHOGG3P6K.jpg";i:2;s:52:"images/66/2017/02/xK9637kYHK13971yE773H7HlHppK9k.jpg";}
                if($v['images'] == 'a:0:{}'){
                    $v['images']=null;
                }else{
                    $v['images'] = unserialize($v['images']);
                    foreach($v['images'] as $k=>$z){
                        $v['images'][$k] =C('IMAGE_RESOURCE').'/'.$z;
                    }
                }
                if($v['append_images'] == 'a:0:{}'){
                    $v['append_images']=null;
                }
                else{
                    $v['append_images'] = unserialize($v['append_images']);
                    foreach($v['append_images'] as $k=>$z){
                        $v['append_images'][$k] =C('IMAGE_RESOURCE').'/'.$z;
                    }
                }
                if($v['reply_images'] == 'a:0:{}'){
                    $v['reply_images']=null;
                }else{
                    $v['reply_images'] = unserialize($v['reply_images']);
                    foreach($v['reply_images'] as $k=>$z){
                        $v['reply_images'][$k] =C('IMAGE_RESOURCE').'/'.$z;
                    }
                }
                if($v['append_reply_images'] == 'a:0:{}'){
                    $v['append_reply_images']=null;
                }else{
                    $v['append_reply_images'] = unserialize($v['images']);
                    foreach($v['append_reply_images'] as $k=>$z){
                        $v['append_reply_images'][$k] =C('IMAGE_RESOURCE').'/'.$z;
                    }
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
        查询评论等级
    */
    public function selectByLevel($where){
        $data=$this->field('level')->where($where)->select();
        return $data;
    }
    /**
     * 分页查询 
     * @param  $uniacid
     * @param  $openid
     * @param  $p
     * @param  $limit
     * @return 
     */
    public function selectListPage($where,$field,$order,$p,$limit=5){
        $count = $this->where($where)->count();
        $Page = new \Think\Page($count, $limit);
        $totalPage = ceil($count / $limit);
        $result =  $this
            ->field($field)
            ->where($where)
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->order($order)
            ->select();
        $data['totalPage'] = $totalPage;
        $data['result'] = $result;
        return $data;
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
     * 更新信息
     * @param  $data 添加的数据
     * @return 
     */
    public function updateByWhere($where,$data){
        $result = $this->where($where)->save($data); 
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
     * 根据条件查询数量
     * @param  $uniacid 商家id
     * @param  $openid  用户id
     * @param  $id   主键id     
     * @param  $field   查询字段
     * @return 
     */
    public function countByWhere($where){
        $result = $this->where($where)->count();
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