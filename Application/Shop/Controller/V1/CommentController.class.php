<?php

/**
* VDONG Project
* ============================================================================
* 接口功能：商城-足迹控制器  CommentController
* URL：url + /index.php/Shop/Comment/ + 方法名
* ----------------------------------------------------------------------------
* 开发者：ZJE  QQ:454513108
* ----------------------------------------------------------------------------
* 时间：2017-03-31
* ----------------------------------------------------------------------------
*/
namespace Shop\Controller;

use Shop\Controller\CommonController;

class CommentController extends CommonController {
    protected $EweiShopOrderComment;
    protected $EweiShopOrder;
    protected $CommentModel;
    
    public function __construct() {
        parent::__construct();
        $this->EweiShopOrderComment = D('EweiShopOrderComment');
        $this->EweiShopOrder = D('EweiShopOrder');
        $this->EweiShopMember = D('EweiShopMember');
        $this->CommentModel =  D('Comment');
        // $Model = new \Think\Model() 

    }
    /**
     * 评价列表 
     * @param  输入参数：用户id【openid】
     *                   商品id【goodsid】
     *                   
     * @return 返回参数：
     */
    public function commentList(){
        $openid = I('get.openid', '', 'string');
        $goodsid   = I('goodsid');
        $p = I('get.p', 1, 'intval'); //当前页码
        $limit = I('get.limit', 5, 'intval'); //每页条数
        $where = array(
            'uniacid' => $this->uniacid,
            'goodsid' => $goodsid,
            'deleted' => 0
        );
        $field = 'id,nickname,headimgurl,level,content,createtime, images,append_images,
        append_content,reply_images,reply_content,append_reply_images,append_reply_content';
        $order = 'id DESC';
        $list = $this->EweiShopOrderComment->selectListPage($where,$field,$order,$p,$limit);
        foreach ($list as $row) {
            $row['headimgurl']          = $row['headimgurl'];
            $row['createtime']          = date('Y-m-d H:i', $row['createtime']);
            $images                     = unserialize($row['images']);
            $row['images']              = is_array($images) ? $images : array();
            $append_images              = unserialize($row['append_images']);
            $row['append_images']       = is_array($append_images) ? $append_images : array();
            $reply_images               = unserialize($row['reply_images']);
            $row['reply_images']        = is_array($reply_images) ? $reply_images : array();
            $append_reply_images        = unserialize($row['append_reply_images']);
            $row['append_reply_images'] = is_array($append_reply_images) ? $append_reply_images : array();
        }
        unset($row);
        $this->outFormats($list['result'],L('SUCCESS'),$p, $list['totalPage'],L('CODE_ONE'));
    }
    /**
     * 添加评价 
     * @param  输入参数：用户id【openid】
     *                   商品id【goodsid】
     *                   
     * @return 返回参数：
     */
    public function commentSave(){
        $comments['goodsid'] = I('goodsid');
        $comments['level'] = I('level');
        $comments['content'] = I('content');
        $comments['images'] = I('images');
        $orderid = I('orderid');
        $openid = I('get.openid', '', 'string');
        $field = 'id,status,iscomment';
        $order = $this->EweiShopOrder->getByidFind($this->uniacid,$openid,$orderid,$field);
        if (empty($order)) {
            $this->outFormat('',L('ORDER_ERROR',array('orderid' => $orderid)),L('C_CODE_ZERO'));
        }
        if ($order['status'] != 3 && $order['status'] != 4) {
            $this->outFormat('',L('ORDER_NON_RECEIPT',array('orderid' => $orderid)),L('C_CODE_ONE'));
        }
        if ($order['iscomment'] >= 2) {
            $this->outFormat('',L('ISCOMMENT'),L('C_CODE_TWO'));
        }
        // 获取用户信息
        $member_field = 'nickname,avatar';
        $member   = $this->EweiShopMember->getByidFind($this->uniacid,$openid,$member_field);

        if (!is_array($comments)) {
            $this->outFormat('',L('DATA_ERROR'),L('C_CODE_THREE'));
        }
        foreach ($comments as $c) {
            $old_c_where = array(
                    'uniacid' => $this->uniacid,
                    'openid' => $openid,
                    'orderid' => $orderid
                );
            $old_c = $this->EweiShopOrderComment->countByWhere($old_c_where);
            if (empty($old_c)) {
                $comment = array(
                    'uniacid' => $this->uniacid,
                    'orderid' => $orderid,
                    'goodsid' => $c['goodsid'],
                    'level' => $c['level'],
                    'content' => $c['content'],
                    'images' => $c['images'],
                    'openid' => $openid,
                    'nickname' => $member['nickname'],
                    'headimgurl' => $member['avatar'],
                    'createtime' => time()
                );
                $result = $this->EweiShopOrderComment->insert($comment);
                if($result){
                    $this->outFormat('',L('SUCCESS'),L('CODE_ONE'));
                }
            } else {
                $update_comment = array(
                    'append_content' => $c['content'],
                    'append_images' => $c['images']
                );
                $comment_where = array(
                        'uniacid' => $this->uniacid,
                        'goodsid' => $c['goodsid'],
                        'orderid' => $orderid
                    );
                $result = $this->EweiShopOrderComment->updateByWhere($comment_where,$update_comment);
                if($result){
                    $this->outFormat('',L('SUCCESS'),L('CODE_ONE'));
                }
                
            }
        }

        if ($order['iscomment'] <= 0) {
            $d['iscomment'] = 1;
        } else {
            $d['iscomment'] = 2;
        }
        // 写到这了
        $order_where['id'] =$orderid;
        $order_result = $this->EweiShopOrder->updateByWhere($order_where,$d);
        $goods = $this->CommentModel->selectGoods($this->uniacid,$orderid);
        $data['order'] = $order;
        $data['goods'] = $goods;
        $this->outFormat('',L('SUCCESS'),L('CODE_ONE'));
    }

    
}
