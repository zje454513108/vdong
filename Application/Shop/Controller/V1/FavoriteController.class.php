<?php

/**
* VDONG Project
* ============================================================================
* 接口功能：商城-收藏控制器  FavoriteController
* URL：url + /index.php/Shop/Favorite/ + 方法名
* ----------------------------------------------------------------------------
* 开发者：ZJE  QQ:454513108
* ----------------------------------------------------------------------------
* 时间：2017-03-31
* ----------------------------------------------------------------------------
*/
namespace Shop\Controller;

use Shop\Controller\CommonController;

class FavoriteController extends CommonController {
    protected $EweiShopMemberFavorite;
    protected $EweiShopGoods;
    
    public function __construct() {
        parent::__construct();
        $this->EweiShopMemberFavorite = D('EweiShopMemberFavorite');
        $this->EweiShopGoods = D('EweiShopGoods');
    }
    /**
     * 收藏列表 
     * @param  输入参数：用户id【openid】
     *                   商户id【uniacid】
     *                   
     * @return 返回参数：
     */
    public function favoriteList(){
        $openid = I('get.openid', '', 'string');
        $p = I('get.p', 1, 'intval'); //当前页码
        $limit = I('get.limit', 5, 'intval'); //每页条数
        $list = $this->EweiShopMemberFavorite->selectListPage($this->uniacid,$openid,$p,$limit);
        $this->outFormats($list['result'],L('SUCCESS'),$p, $list['totalPage'],L('CODE_ONE'));
    }
    /**
     * 取消收藏 
     * @param  输入参数：用户id【openid】
     *                  商户id【uniacid】
     *                  收藏id【favoriteid】
     * @return 返回参数：
     */
    public function favoriteDel(){
        $openid = I('get.openid', '', 'string');
        $ids= I('favoriteid');
        // 取消收藏多个 
        $result = $this->EweiShopMemberFavorite->deleteByid($this->uniacid,$openid,$ids);
        if($result){
            $this->outFormat($result,L('SUCCESS'),L('CODE_ONE'));
        }else{
            $this->outFormat($result,L('F_DELETED'),L('F_CODE_ZERO'));
        }
    }
    /**
     * 收藏设置 
     * @param  输入参数：用户id【openid】
     *                  商户id【uniacid】
     *                  收藏id【favoriteid】
     * @return 返回参数：
     */
    public function favoriteSet(){
        $id =  I('goodid');
        $openid = I('get.openid', '', 'string');
        $goods_field = 'id';
        // 根据商品id获取商品
        $goods = $this->EweiShopGoods->getByIdFind($this->uniacid,$id,$goods_field);
        if (empty($goods)) {
            $this->outFormat($goods,L('GOODS_ERROR'),L('F_CODE_ONE'));
        }
        $data_field = 'id,deleted';
        // 查询收藏 
        $data = $this->EweiShopMemberFavorite->getByIdFind($this->uniacid,$openid,$id,$data_field);
        if (empty($data)) {
            $data = array(
                'uniacid' => $this->uniacid,
                'openid' => $openid,
                'goodsid' => $id,
                'createtime' => time()
            );
            // 新增收藏
            $result = $this->EweiShopMemberFavorite->insert($data);
            if($result){
                $this->outFormat($goods,L('F_SUCCESS'),L('F_CODE_TWO'));
            }
        } else {
            if (empty($data['deleted'])) {
                // 设置收藏
                $result = $this->EweiShopMemberFavorite->setById($this->uniacid,$openid,$data['id']);
                $this->outFormat($goods,L('F_CANCEL'),L('F_CODE_THREE'));
            } else {
                // 设置收藏
                $result = $this->EweiShopMemberFavorite->setById($this->uniacid,$openid,$data['id'],0);
                $this->outFormat($goods,L('F_SUCCESS'),L('F_CODE_TWO'));
            }
        }
    }
}
