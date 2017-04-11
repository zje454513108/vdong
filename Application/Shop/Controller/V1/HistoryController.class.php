<?php

/**
* VDONG Project
* ============================================================================
* 接口功能：商城-足迹控制器  HistoryController
* URL：url + /index.php/Shop/History/ + 方法名
* ----------------------------------------------------------------------------
* 开发者：ZJE  QQ:454513108
* ----------------------------------------------------------------------------
* 时间：2017-03-31
* ----------------------------------------------------------------------------
*/
namespace Shop\Controller;

use Shop\Controller\CommonController;

class HistoryController extends CommonController {
    protected $EweiShopMemberHistory;
    
    public function __construct() {
        parent::__construct();
        $this->EweiShopMemberHistory = D('EweiShopMemberHistory');
    }
    /**
     * 足迹列表 
     * @param  输入参数：用户id【openid】
     *                   商户id【uniacid】
     *                   
     * @return 返回参数：
     */
    public function memberHistoryList(){
        $openid = I('get.openid', '', 'string');
        $p = I('get.p', 1, 'intval'); //当前页码
        $limit = I('get.limit', 5, 'intval'); //每页条数
        $list = $this->EweiShopMemberHistory->selectListPage($this->uniacid,$openid,$p,$limit);
        $this->outFormats($list['result'],L('SUCCESS'),$p, $list['totalPage'],L('CODE_ONE'));
    }
    /**
     * 删除足迹 
     * @param  输入参数：用户id【openid】
     *                  商户id【uniacid】
     *                  足迹id【historyid】
     * @return 返回参数：
     */
    public function memberHistoryDel(){
        $openid = I('get.openid', '', 'string');
        $ids= I('historyid');
        // 取消足迹多个
        $result = $this->EweiShopMemberHistory->deleteByid($this->uniacid,$openid,$ids);
        if($result){
            $this->outFormat($result,L('SUCCESS'),L('CODE_ONE'));
        }else{
            $this->outFormat($result,L('H_DELETED'),L('H_CODE_ZERO'));
        }
    }
}
