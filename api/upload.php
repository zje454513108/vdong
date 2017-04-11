<?php
require '../vendor/autoload.php';
// 引入鉴权类
use Qiniu\Auth;

// 引入上传类
use Qiniu\Storage\UploadManager;

if(!$_FILES){
	exit;
}

// 需要填写你的 Access Key 和 Secret Key
$accessKey = 'YSANi3pgRVsLCESVuEZhzX1rv35a-HNHVnWTOkEz';
$secretKey = '-NKfQrlMyptEXgGeJZl-d57UmYMbLjUmAyq_Vjn6';

// 构建鉴权对象
$auth = new Auth($accessKey, $secretKey);

// 要上传的空间
$bucket = 'wdtx-uploads';

// 生成上传 Token
$token = $auth->uploadToken($bucket);


// 要上传文件的本地路径
$filePath = $_FILES['file']['tmp_name'];



// 上传到七牛后保存的文件名
$key = 'wxapp/'.date('Y-m-d').'/'.time().'/'. $_FILES['file']['name'];

// 初始化 UploadManager 对象并进行文件的上传。
$uploadMgr = new UploadManager();

// 调用 UploadManager 的 putFile 方法进行文件的上传。
list($ret, $err) = $uploadMgr->putFile($token, $key, $filePath);


echo 'http://uploads.qusaoba.net/'.$ret['key'];
//七牛
exit;