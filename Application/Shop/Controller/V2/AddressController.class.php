<?php

namespace Shop\Controller\V2;
use Shop\Controller\V2\CommonController;

/**
 * 会员地址控制器9000--
 *
 * @author Administrator
 */
class AddressController extends CommonController {

    protected $addr;


    public function __construct() {
        parent::__construct();
        $this->addr = D('EweiShopMemberAddress');
    }

    /**
     * 
     * 地址列表
     */
    public function index() {
        $openid = I('get.openid', '', 'string');
        $this->getMember($this->uniacid, $openid);
        $p = I('get.p', 1, 'intval'); //当前页码
        $limit = I('get.limit', 5, 'intval'); //每页条数

        $where= array(
            'uniacid'   =>$this->uniacid,
            'openid'        =>$openid,
            'deleted'   =>0
        );
        $count = $this->addr->getAddrCount($where);
        $Page = new \Think\Page($count, $limit);
        $totalPage = ceil($count / $limit);
        $data = $this->addr
                ->field('id,openid,realname,mobile,province,city,area,address,isdefault')
                ->where($where)
                ->limit($Page->firstRow . ',' . $Page->listRows)
                ->select();
        if ($data) {
            $this->outFormats($data, 'ok', $p, $totalPage, 0);
        } else {
            $this->outFormats(array(), '暂无数据', $p, $totalPage, 0);
        }
    }

    /**
     * 地址详情
     */
    public function info() {
        $openid = I('get.openid', '', 'string');
        $this->getMember($this->uniacid, $openid);
        $id = I('get.id', '', 'intval'); //主键id
        if (empty($id)) {
            $this->outFormat('', '参数错误', 9001);
        }
        $field = 'id,openid,realname,mobile,province,city,area,address,isdefault';
        $where= array(
            'uniacid'   =>$this->uniacid,
            'openid'        =>$openid,
            'deleted'   =>0,
            'id'        =>$id
        );
        $data = $this->addr->getByWhereFind($where,$field);
                
        if ($data) {
            $this->outFormat($data, 'ok', 0);
        } else {
            $this->outFormat('', '非法操作', 9002);
        }
    }

    /**
     * 添加收货地址
     */
    public function add() {
        $openid = I('post.openid', '', 'string');
        $this->getMember($this->token, $openid);
//        需要添加的字段
        $realname = I('post.realname', '', 'string');
        if (empty($realname)) {
            $this->outFormat('', '请输入收件人', 9003);
        }
        $mobile = I('post.mobile', '', 'string');
        $rep = "/^(13[0-9]|14[0-9]|15[0-9]|17[0-9]|18[0-9])\d{8}$/i";
        if (empty($mobile) || !preg_match($rep, $mobile)) {
            $this->outFormat('', '请输入正确的联系电话', 9004);
        }
        $province = I('post.province', '', 'string');
        if (empty($province)) {
            $this->outFormat('', '请选择省份', 9005);
        }
        $city = I('post.city', '', 'string');
        if (empty($city)) {
            $this->outFormat('', '请选择城市', 9006);
        }
        $area = I('post.area', '', 'string');
        if (empty($area)) {
            $this->outFormat('', '请选择区域', 9007);
        }
        $address = I('post.address', '', 'string');
        if (empty($address)) {
            $this->outFormat('', '请输入收货地址', 9008);
        }
        $data = array(
            'uniacid' => $this->token,
            'openid' => $openid,
            'realname' => $realname,
            'mobile' => $mobile,
            'province' => $province,
            'city' => $city,
            'area' => $area,
            'address' => $address,
        );
        $where= array(
            'uniacid'       =>$this->token,
            'openid'        =>$openid,
            'deleted'       =>0,
            'isdefault'     =>1
        );
//        查询是否有默认收货地址
        $has = $this->addr->getByWhereFind($where,'id');
                
        $data['isdefault'] = $has ? 0 : 1;
        $result = $this->addr
                ->addAddress($data);
        if ($result) {
            $this->outFormat(array('num' => 1,'id'=>$result), 'ok', 0);
        } else {
            $this->outFormat('', '操作失败', 9009);
        }
    }

    /**
     * 设置默认地址
     */
    public function setDefault() {
        $openid = I('post.openid', '', 'string');
        $this->getMember($this->token, $openid);
        $id = I('post.id', '', 'intval'); //主键id
        if (empty($id)) {
            $this->outFormat('', '参数错误', 9010);
        }
        $where= array(
            'uniacid'       =>$this->token,
            'openid'        =>$openid,
            'deleted'       =>0,
            'id'            =>$id
        );
        $re = $this->addr->getByWhereFind($where,'isdefault');
          
        if (empty($re) || $re['isdefault'] == 1) {
            $this->outFormat('', '非法操作', 9011);
        }
        // 添加事务
        $model = M();
        $model->startTrans();
        // 将原来默认地址去除
        $re1 = $model
                ->table(C('DB_PREFIX') . 'ewei_shop_member_address')
                ->where("uniacid=%d and openid='%s' and deleted=0 and isdefault=1", array($this->token, $openid))
                ->save(array('isdefault' => 0));
        // 将现在地址标记为默认
        $re2 = $model
                ->table(C('DB_PREFIX') . 'ewei_shop_member_address')
                ->where("uniacid=%d and openid='%s' and id=%d and deleted=0", array($this->token, $openid, $id))
                ->save(array('isdefault' => 1));

        if ($re1 && $re2) {
            $model->commit();
            $this->outFormat(array('num' => 1), 'ok', 0);
        } else {
            $model->rollback();
            $this->outFormat('', '操作失败', 9012);
        }
    }

    /**
     * 更新地址
     */
    public function update() {
        $openid = I('post.openid', '', 'string');
        $this->getMember($this->token, $openid);
//        需要添加的字段
        $id = I('post.id', '', 'string');
        if (empty($id)) {
            $this->outFormat('', '参数错误', 9013);
        }
        $where= array(
            'uniacid'       =>$this->token,
            'openid'        =>$openid,
            'deleted'       =>0,
            'id'            =>$id
        );
        $re = $this->addr->getByWhereFind($where,'id');

        if (empty($re)) {
            $this->outFormat('', '非法操作', 9014);
        }
        $realname = I('post.realname', '', 'string');
        if (empty($realname)) {
            $this->outFormat('', '请输入收件人', 9015);
        }
        $mobile = I('post.mobile', '', 'string');
        $rep = "/^(13[0-9]|14[0-9]|15[0-9]|17[0-9]|18[0-9])\d{8}$/i";
        if (empty($mobile) || !preg_match($rep, $mobile)) {
            $this->outFormat('', '请输入正确的联系电话', 9016);
        }
        $province = I('post.province', '', 'string');
        if (empty($province)) {
            $this->outFormat('', '请选择省份', 9018);
        }
        $city = I('post.city', '', 'string');
        if (empty($city)) {
            $this->outFormat('', '请选择城市', 9019);
        }
        $area = I('post.area', '', 'string');
        if (empty($area)) {
            $this->outFormat('', '请选择区域', 9020);
        }
        $address = I('post.address', '', 'string');
        if (empty($address)) {
            $this->outFormat('', '请输入收货地址', 9021);
        }
        $data = array(
            'realname' => $realname,
            'mobile' => $mobile,
            'province' => $province,
            'city' => $city,
            'area' => $area,
            'address' => $address,
        );
//        更新收货地址
        $result = $this->addr->updateAddress($where,$data);

        if ($result !== false) {
            $this->outFormat(array('num' => 1), 'ok', 0);
        } else {
            $this->outFormat('', '操作失败', 9022);
        }
    }

    /**
     * 删除地址
     */
    public function delete() {
        $openid = I('post.openid', '', 'string');
        $this->getMember($this->token, $openid);
        $id = I('post.id', '', 'intval'); //主键id
        if (empty($id)) {
            $this->outFormat('', '参数错误', 9023);
        }
        $where= array(
            'uniacid'       =>$this->token,
            'openid'        =>$openid,
            'deleted'       =>0,
            'id'            =>$id
        );
//        判断当前id是否合法
        $re = $this->addr->getByWhereFind($where,'isdefault');

        if (empty($re) || $re['deleted'] == 1) {
            $this->outFormat('', '非法操作', 9024);
        }
        $where1= array(
            'uniacid'       =>$this->token,
            'openid'        =>$openid,
            'deleted'       =>0,
            'isdefault'     =>0
        );
//        是否有非默认地址
        $notdefault = $this->addr->getByWhereFind($where1,'id');

//        当前删除的地址非默认地址
        if ($re['isdefault'] == 0 || empty($notdefault)) {
            $result = $this->addr->updateAddress($where,array('deleted' => 1));

            if ($result) {
                $this->outFormat(array('num' => 1), 'ok', 0);
            } else {
                $this->outFormat('', '操作失败', 9025);
            }
        }
//        当前操作是默认地址，还需判断是否还有其他非默认地址      
//        如果没有非默认地址，就直接修改当前数据
//        开启事物
        $model = M();
        $model->startTrans();
        // 将原来默认地址去除
        $re1 = $model
                ->table(C('DB_PREFIX') . 'ewei_shop_member_address')
                ->where("uniacid=%d and openid='%s' and id=%d and deleted=0", array($this->token, $openid, $id))
                ->save(array('deleted' => 1));
        $re2 = $model
                ->table(C('DB_PREFIX') . 'ewei_shop_member_address')
                ->where("uniacid=%d and openid='%s' and id=%d and deleted=0", array($this->token, $openid, $notdefault['id']))
                ->save(array('isdefault' => 1));
        if ($re1 && $re2) {
            $model->commit();
            $this->outFormat(array('num' => 1), 'ok', 0);
        } else {
            $model->rollback();
            $this->outFormat('', '操作失败', 9026);
        }
    }

    /**
     * 获取省/市/区
     * 
     */
    public function getProvince() {
        $openid = I('get.openid', '', 'string');
        $id = I('get.id',0,'intval');//地区id主键
        $this->getMember($this->uniacid, $openid);
        $model = M('ewei_shop_diqu');
        $where = array(
            'parent_id' =>$id,
        );
        $data = $model->field('region_id,region_name')->where($where)->select();
        if($data){
            $this->outFormat($data, 'ok', 0);
        }else{
            $this->outFormat(array(), '没有数据', 9027);
        }
    }

}
