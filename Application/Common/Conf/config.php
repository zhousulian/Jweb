<?php
return array(
		//数据库配置信息
	'DB_TYPE'   => 'mysql', // 数据库类型
    'DB_HOST'   => '', // 服务器地址
    'DB_NAME'   => 'NetServerNew', // 数据库名
	'DB_USER'   => 'root', // 用户名
	'DB_PWD'    => 'asd_789', // 密码 ptt2018 asd_789
	'DB_PORT'   => 3306, // 端口
	'DB_PREFIX' => 'tab_', // 数据库表前缀 
	'DB_CHARSET'=> 'utf8', // 字符集
	'DB_DEBUG'  =>  TRUE, // 数据库调试模式 开启后可以记录SQL日志

	'URL_MODEL'=>2,  //URL重写
	//url 不区分大小写
	'URL_CASE_INSENSITIVE' => false,

	// 开启路由
	'URL_ROUTER_ON'   => true, 
	//定义路由
    'URL_ROUTE_RULES'=>array(
    	'ptt'  => 'Admin/AdLog/pttlogin',
    	'pttsub'  => 'Admin/SubAdLog/pttlogin',
	),
	'URL_HTML_SUFFIX'=>'',  //伪静态后缀

	//session(array('use_trans_sid'=>1,'expire'=>6)); 



);