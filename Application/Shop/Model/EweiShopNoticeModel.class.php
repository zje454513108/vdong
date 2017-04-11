<?php
namespace Shop\Model;
use Think\Model;
/***
*店铺公告 litianyou @ 2/20
*/
class EweiShopNoticeModel extends Model{
    public function notice($data){//店铺公告 litianyou @ 2/20 html 格式
        $User = M('ewei_shop_notice');
        $where = array(
            'status' => 1,
            'uniacid' => $data['uniacid']
        );
        if(empty($data['uniacid'])){
            $aa['meta'] = array('code'=>'0','message'=>'参数错误!');
            return $aa;
        }
        $data = $User->where($where)->field('id,title,thumb,link,detail,createtime')->select();
        if($data){
            foreach($data as &$v){
               $v['thumb'] = getImgUrl($v['thumb']);
               $v['createtime'] = date('Y-m-d H:i',$v['createtime']);
            }
            $aa['data'] = $data;
            $aa['meta'] = array('code'=>'1','message'=>'调用成功!');
            return $aa;
        }else{
            $aa['meta'] = array('code'=>'0','message'=>'调用失败!');
            return $aa;
        }
    }
}