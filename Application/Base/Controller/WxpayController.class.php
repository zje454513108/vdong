<?php
/**
 * Created by PhpStorm.
 * User: Stone
 * Date: 2016/9/19
 * Time: 00:12
 * mail: wxstones@gmail.com
 */
namespace Base\Controller;
use Think\Controller;
// Vendor('Wxpay.WxPay#JsApiPay');
// Vendor('Wxpay.WxPay#Notify');
class WxpayController extends Controller {
	/** 链接wechat接口 */


     public function jsApiCall()
    {
    	//导入第三方支付类
		Vendor('Wxpay.WxPayPubHelper');
       //接收传过来的金额数
		$moeny = I('post.moeny');
		// $moeny = '1';
		//接收商品描述
		$body=I('post.body');
		//接收传过来的订单详情
		$detail=I('post.detail');
		//附加数据 分店信息等
		$attach=I('post.attach');
		//接收商户订单号
		$out_trade_no=I('post.out_trade_no');
		// return $_POST;exit;
		//微信支付回调地址
		$notify_url=I('post.notify_url');
		//接收 商户号
		$uniacid=I('post.uniacid');
		//接收 模块号
		$ptype=I('post.ptype');
		//实例化小程序支付表
		$WxPayDb=D('uni_wxapps');
		//拼接搜索条件
		$where['uniacid']=$uniacid;
		$where['ptype']=$ptype;
		// dump($where);exit;
		//根据商户号 模块号 查找发起支付需要的参数
		$dataList=$WxPayDb->getDateByUniacid($where);
		// var_dump($dataList);exit;
		// $str=unserialize($dataList['payment']);
		//取出发起支付需要的4个参数
		$mchid=$dataList['mch_id'];
		$key=$dataList['appkey'];
		$appid=$dataList['appid'];
		$secret=$dataList['appsecret'];
		//接收 用户openID
		$openId=I('post.appid');
		//支付金额 单位为分
		$tal_fee = $moeny;

		//实例化 JS 支付类
		$tools = new \JsApiPay();
		//支付需要的参数 可以写活
		// b455b73a0a2f02daab9c817c82350034
		// $key='wdtxdiancan1234567890beijingfeng';
		// $appid='wx3e8a50459e2d1b66';
		// $secret='b455b73a0a2f02daab9c817c82350034';
		// $mchid='1445001702';
		//把支付需要的参数 赋值
		$tools::$appid= $appid;
		$tools::$mchid= $mchid;
		$tools::$key= $key;
		$tools::$secret= $secret;
		//设置 支付的参数
		 $input = new \WxPayUnifiedOrder();
		 //设置商品描述
        $input->SetBody($body);
        // $input->SetOut_trade_no(rand(10000,9999999));
        //设置分店号
        $input->SetAttach($attach);
        //设置支付金额
        $input->SetTotal_fee($tal_fee);
        //设置交易开始时间
        $input->SetTime_start(date("YmdHis"));
        //设置交易结束时间
        $input->SetTime_expire(date("YmdHis", time() + 600));
        //设置订单详情
        $input->SetDetail($detail);
        //设置商户订单号
        $input->SetOut_trade_no($out_trade_no);
        //设置支付成功回调地址
        $input->SetNotify_url($notify_url);
        //设置支付类型
        $input->SetTrade_type("JSAPI");
        //设置openID
        $input->SetOpenid($openId);
        //传入参数 获得下单接口需要的参数
        $order = \WxPayApi::unifiedOrder($input);
        // dump($order);exit;
		//给下单接口传入参数 获得返回的调起JSapi支付需要的参数
		$jsApiParameters = $tools->GetJsApiParameters($order);
		echo $jsApiParameters ;exit;
    }
    /* 支付回调地址 只要有数据回来  那说明   肯定是支付成功的   不支付成功不会回调*/
	public function notify(){
		//实例化支付回调类
		$notify= new \Notify();
        	//获得微信支付成功返回的数据
		  $xml = $GLOBALS['HTTP_RAW_POST_DATA'];

		 
		
    }
   
}