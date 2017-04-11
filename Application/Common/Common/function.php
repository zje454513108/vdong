<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * 打印输出数据|show的别名
 * @param void $var
 */
function p($var) {
    if (is_bool($var)) {
        var_dump($var);
    } else if (is_null($var)) {
        var_dump(NULL);
    } else {
        echo "<pre style='position:relative;z-index:1000;padding:10px;border-radius:5px;background:#F5F5F5;border:1px solid #aaa;font-size:14px;line-height:18px;opacity:0.9;'>" . print_r($var, true) . "</pre>";
    }
}

/**
 * 数组转换函数
 * @staticvar type $arr2
 * @param type $a
 * @return type
 */
function arrayChange($a) {
    static $arr2;
    foreach ($a as $v) {
        $arr2 = $v;
    }
    return $arr2;
}

function array_group($arr) {

    $result = [];  //初始化一个数组
    foreach ($arr as $k => $v) {
        $result[$v['pid']][] = $v;  //根据initial 进行数组重新赋值
    }
    return $resut;
}

/**
 * curl处理get和post请求
 * @param  [type] $url  提交url地址
 * @param  [type] $data 提交数据，为空就是get方式
 * @return [type]       返回接受的数据
 */
function http_curl($url, $data = null) {

    //1.初始化，创建一个新cURL资源
    $ch = curl_init();
    //2.设置URL和相应的选项		 
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); //SSL验证
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); //SSL验证	 
    //curl_setopt($ch, CURLOPT_HEADER, 0);
    if (!empty($data)) {
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, FALSE); //禁止curl资源直接输出
    //3.抓取URL并把它传递给浏览器 
    $opt = curl_exec($ch);
    //4.关闭cURL资源，并且释放系统资源		 
    curl_close($ch);
    return $opt;
}

/**
 * * @desc 封装 curl 的调用接口，post的请求方式
 * */
function doCurlPostRequest($url, $requestString, $timeout = 5) {
    if ($url == '' || $requestString == '' || $timeout <= 0) {
        return false;
    }
    $con = curl_init((string) $url);
    curl_setopt($con, CURLOPT_HEADER, false);
    curl_setopt($con, CURLOPT_POSTFIELDS, $requestString);
    curl_setopt($con, CURLOPT_POST, true);
    curl_setopt($con, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($con, CURLOPT_TIMEOUT, (int) $timeout);
    return curl_exec($con);
}

function getOrderContent($data = array(), $num = 1) {

    $content = getInfo($data['orderInfo']);

    $detail = getDetail($data['orderDetail']);

    $content .= $detail['detail'];


    $content .= getPrice($detail['total'], $data['orderCut']);

    return '<MN>' . $num . '</MN>' . $content;
}

function getInfo($info = array()) {
    $title = '';
    $title .= '\r\n<center><FB>----------' . $info['corpname'] . '----------</FB></center>';
    $title .= '\r\n单号:' . $info['orderno'];
    $title .= '\r\n姓名:' . $info['username'];
    $title .= '\r\n电话:' . $info['usertel'];
    $title .= '\r\n地址:' . $info['useraddr'];
    $title .= '\r\n时间:' . $info['time'];

    return $title;
}

//获取详细:三列
function getDetail($detail = array()) {
    /* <table><tr><td>名称</td><td>单价</td><td>数量</td></tr><tr><td>香蕉</td><td>1.5</td><td>4</td></tr><tr><td> 苹果 </td><td> 2.0 </td><td> 3 </td></tr></table> */
    $total = 0;
    $table = '\r\n详细:\r\n------------------------------\r\n';
    $table .= '<table><tr><td>名称</td><td>单价*数量</td><td>小计</td></tr>';
    foreach ($detail as $d) {
        $table .= '<tr><td>';
        $table .= $d['name'] . '</td>';
        $table .= '<td>' . sprintf("%.2f", $d['price']) . '*' . $d['count'] . '</td>';
        $table .= '<td>' . sprintf("%.2f", $d['price'] * $d['count']) . '</td>';
        $table .= '</tr>';

        $total += $d['price'] * $d['count'];
    }
    $table .= '</table>';

    return array('total' => $total, 'detail' => $table);
}

//获取价格
function getPrice($totalPrice, $orderCut = array()) {
    $price = $totalPrice;

    $str = '\r\n共计:' . sprintf("%.2f", $totalPrice);
    if ($orderCut) {
        switch ($orderCut['type']) {
            case '1'://折扣,num>0 and num <10
                $price = ($price * $orderCut['num']) / 10 + $orderCut['fee'];
                $str .= '\r\n优惠:' . $orderCut['num'] . '折';
//                $str .= '\r\n支付金额:' . $totalPrice . '*' . $orderCut['num'] . '折=' . sprintf("%.2f", $price);
                $str .= '\r\n支付金额:' . sprintf("%.2f", $price);
                break;
            case '2'://优惠,num<=totalPrice
                $price = $price - $orderCut['num'] + $orderCut['fee'];
                $str .= '\r\n运费:+' . $orderCut['fee'];
                $str .= '\r\n优惠:-' . $orderCut['num'];
//                $str .= '\r\n支付金额:' . $totalPrice . '+' .$orderCut['fee']. '-' . $orderCut['num'] . '=' . sprintf("%.2f", $price);

                $str .= '\r\n支付金额:' . sprintf("%.2f", $price);
                break;
            default:
                break;
        }
    }

    return $str;
}

/**
 * curl请求操作
 * @param type $url
 * @param type $data
 * @param type $method
 * @return type
 */
function http($url, $data = '', $method = 'GET') {
    $curl = curl_init(); // 启动一个CURL会话  
    curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址  
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // 对认证证书来源的检查  
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false); // 从证书中检查SSL加密算法是否存在  
    curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器  
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转  
    curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer  
    if ($method == 'POST') {
        curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求  
        if ($data != '') {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data); // Post提交的数据包  
        }
    }
    curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环  
    curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容  
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回  
    $tmpInfo = curl_exec($curl); // 执行操作  
    curl_close($curl); // 关闭CURL会话  
    return $tmpInfo; // 返回数据  
}

/**
 * 获取图片完整路径 
 * @param type $img
 * @return type
 */
function getImgUrl($img) {
    $imgUlr = $img ? C('IMAGE_RESOURCE') . '/' . $img : '';
    return $imgUlr;
}
