<?php
namespace Base\Controller;
use Think\Controller;
class PrintController extends Controller {
  //打印机添加接口
	public function print_add(){
      //导入第三方类
       vendor('Yprint.print');
       //实例化第三方类
      $print = new \Yprint();

    //易联云帐号ID
    $partner=I("post.partner");
    //易联云API
    $apikey=I("post.apikey");
    //易联云帐号
    $username=I("post.username");
    //打印机名
    $printname=I("post.printname");
    //打印机卡号
    $mobilephone=I("post.mobilephone");
    //打印机终端号
    $machine=I("post.machine");
    //打印机密钥
    $msign=I("post.msign");
    //商户ID
    $niacid=I("post.niacid");
    //模块标识
    $module=I("post.module");
    //添加时间 添加时的时间戳
    $addtime=time();
    //删除 默认为1 未删除
    $del=1;
    //状态 默认为1 启用
    $status=1;
    //实例化打印机表
    $printUserDb=D('ewei_print_user');
    // $printUserDb=D('print_user');
    // dump($printUserDb);exit;
    //调用第三方的添加接口
    $result=$print->action_addprint($partner,$machine,$username,$printname,$mobilephone,$apikey,$msign);
    // dump($result);exit;
    // $result=1;
    //判断是否添加成功 返回码1为成功
    if ($result==1) {
     // $status=1;
      $data['partner']=$partner;
      $data['apikey']=$apikey;
      $data['username']=$username;
      $data['printname']=$printname;
      $data['mobilephone']=$mobilephone;
      $data['machine']=$machine;
      $data['msign']=$msign;
      $data['addtime']=$addtime;
      $data['niacid']=$niacid;
      $data['module']=$module;
      $data['del']=$del;
      $data['status']=$status;
      //把数据添加到 打印机表
      $result=$printUserDb->insertPrintUser($data);
     $this->ajaxReturnSuccess(0,'添加成功',$result);
      
    }else{
     $this->ajaxReturnError(1,'添加失败');
    }

  }
  //查询商户所属打印机列表
  public function print_select(){
    //接收传过来的商户ID
    $niacid=I('post.niacid');
    //接收传过来的模块标识
    $module=I('post.module');
    //实例化打印机表
    $printUserDb=D('ewei_print_user');
    //封装查询条件
    $where['niacid']=$niacid;
    $where['module']=$module;
    $where['del']=1;
    $where['status']=1;
    // dump($where);exit;
    //调用自定义model类封装方法,执行查询操作
    $printList=$printUserDb->selectPrintList($where);
    //成功返回结果集合 失败返回失败码
    if ($printList) {
      $this->ajaxReturnSuccess(0,'查询成功',$printList);
    }else{
      $this->ajaxReturnError(1,'查询失败');
    }

  }
  //删除打印机接口
  public function print_del(){
      //导入第三方类
       vendor('Yprint.print');
       //实例化第三方类
      $print = new \Yprint();
      //需要删除的打印机表ID
      $printId=I("post.printId"); 
      $niacid=I("post.niacid"); 
    //实例化打印机表
      $printUserDb=D('ewei_print_user');
      //封装搜索条件
      $where['id']=$printId;
      $where['niacid']=$niacid;
      $data=$printUserDb->selectPrintList($where);
      foreach ($data as $k => $v) {
        // dump($v);exit;
      //调用易联云删除接口
      $result=$print->action_removeprinter($v['partner'],$v['machine'],$v['apikey'],$v
        ['msign']);
       if ($result) {
        $data=$printUserDb->deletePrintList($where);
       if ($data) {
         $this->ajaxReturnSuccess(0,'删除成功',$result);
       }else{
        $this->ajaxReturnError(2,'数据库删除失败');
       }
     }else{
      $this->ajaxReturnError(1,'删除失败');
     }
      }
    
  }
  //执行打印操作
  public function print_start(){
    //实例化打印机表
    $printUserDb=D('ewei_print_user');
    //模块标识
      // $module=I("post.module");
     //商户标识 商户ID 
      $niacid=I('post.niacid');
      //模块标识
      $module=I('post.module');
    //订单详细内容
      $content=I('post.content');
    //系统标识
      $system=I("post.system");
    //订单号
      $order=I("post.order");
      //添加时间 执行打印时获取当前时间戳
      $addtime=time();
      // return $_POST;exit;
      //删除状态 默认为1 未删除
      $del=1;
      //状态 默认为1 成功
      //根据传过来的商户ID 模块标识 进行打印机查询
      $where['niacid']=$niacid;
      $where['module']=$module;
      $where['del']=1;
      $where['status']=1;
    // dump($content);exit;
    //导入第三方类
       vendor('Yprint.print');
    //实例化第三方类
      $print = new \Yprint();
    //实例化打印记录表
    $printListDb=D('ewei_print_list');
      //调用自定义model类封装方法,执行查询操作
    $printList=$printUserDb->selectPrintList($where);
  if (!empty($printList)) {
      foreach ($printList as $key => $value) {
    //易联云帐号ID
      $partner=$value['partner']; 
    //打印机终端号
     $machine=$value['machine']; 
    //易联云API密钥
      $apikey=$value['apikey'];
    //打印机密钥
      $msign=$value['msign'];
      //调用易联云第三方接口 执行打印
      
      $content=urldecode($content);
    $jieguo=$print->action_print($partner,$machine,$content,$apikey,$msign);
    //将得到的JSON 转为数组 进行判断
    $str=json_decode($jieguo,true);
      if ($str['state']==1) {
      $status=1;
      $data['system']=$system;
      $data['module']=$module;
      $data['order']=$order;
      $data['content']=$content;
      $data['niacid']=$niacid;
      $data['addtime']=$addtime;
      $data['module']=$module;
      $data['del']=$del;
      $data['status']=$status;
      //把打印记录添加到打印记录表
      $result=$printListDb->insertPrintList($data);
      $this->ajaxReturnSuccess(0,'打印成功',$result);
    }else{
       $status=2;
      $data['system']=$system;
      $data['module']=$module;
      $data['order']=$order;
      $data['content']=$content;
      $data['niacid']=$niacid;
      $data['addtime']=$addtime;
      $data['module']=$module;
      $data['del']=$del;
      $data['status']=$status;
      $result=$printListDb->insertPrintList($data);
      $this->ajaxReturnSuccess(1,'打印失败',$result);
    }
  
    }
  }
  exit;
    // $str['state']=1;
    
  }
  //查询打印记录表
  public function print_list_select(){
    //商户标识 商户ID 
      $niacid=I('post.niacid');
      //模块标识
      $module=I("post.module");
      //每页显示的数据
       $limit=I('post.limit');
       //页数
        $page=I('post.page');
        //如果页数小于等于1 则为第一页 否则则为去掉第一页的数据
        if ($page>=1) {
                $num=0;
            }else{
                $num=($page-1)*$limit;
            }
      //实例化打印记录表
    $printListDb=D('ewei_print_list');
    //封装查询条件 $where
    $where['niacid']=$niacid;
    $where['module']=$module;
    $where['status']=1;
    $where['del']=1;
    //调用自定义分页查询方法
    $printList = $printListDb->selectPrintList($where,$num,$limit);
    //返回查询数据
    $this->ajaxReturnSuccess(0,'查询成功',$printList);
  }
  //失败时调用的返回JSON方法
  public function ajaxReturnError($errorCode, $errorName)
    {
        $ret['code'] = $errorCode;
        $ret['msg'] = $errorName;
        // $ret['error'] = $error;
        $ret['data']    =null;
        $this->ajaxReturn($ret, 'json', true);
    }
    
     //成功时调用的返回JSON方法
      public function ajaxReturnSuccess($errorCode, $errorName,$result)
    {
        
        $ret['code'] = $errorCode;
        $ret['msg'] = $errorName;
        $ret['data']    =$result;
        $this->ajaxReturn($ret, 'json', true);
    } 
}