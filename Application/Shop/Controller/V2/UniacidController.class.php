<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Shop\Controller\V2;
use Shop\Controller\V2\CommonController;
/**
 * 商户信息6000
 *
 * @author Administrator
 */
class UniacidController extends CommonController {
    
   
    public function __construct() {
        parent::__construct();
    }
    public function index(){
        if(empty($this->uniacid)){
            $this->outFormat('', '参数错误', 6001);
        }
        $shop = $this->getSysset('shop',$this->uniacid);
        if($shop == false){
            $this->outFormat('', '没有查询到您想要的数据！', 6002);
        }
        $data = array(
            'name'  => $shop['name'],
            'img'   =>getImgUrl($shop['img']),
            'logo'  =>getImgUrl($shop['logo']),
            
        );
        $this->outFormat($data, 'ok', 0);
    }
}
