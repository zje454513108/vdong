<?php
return array(
    // 数据库连接参数
    'DB_TYPE'   => 'mysql', // 数据库类型
    'DB_HOST'   => '127.0.0.1', // 服务器地址 114.55.177.172 rm-2ze90y819wfj5b091o.mysql.rds.aliyuncs.com
    'DB_NAME'   => 'wd_newweixin', // 数据库名 wd_newweixin
    'DB_USER'   => 'root', // 用户名 remote_max  wd_read
    'DB_PWD'    => 'root', // 密码 max123456 wd_read_123456
    'DB_PORT'   => 3306, // 端口
    'DB_PREFIX' => 'ims_', // 数据库表前缀
    'DB_CHARSET'=> 'utf8', // 字符集  //图片地址 http://uploads.qusaoba.net
    'DB_DEBUG'  =>  TRUE, // 数据库调试模式 开启后可以记录SQL日志 3.2.3新增
    //    图片资源地址
    'IMAGE_RESOURCE'        =>'http://uploads.qusaoba.net',
    'LANG_AUTO_DETECT' => FALSE, //关闭语言的自动检测，如果你是多语言可以开启
    'LANG_SWITCH_ON' => TRUE, //开启语言包功能，这个必须开启
    'DEFAULT_LANG' => 'zh-cn', //zh-cn文件夹名字 /lang/zh-cn/common.php
    'CONTROLLER_LEVEL' => 2,
);