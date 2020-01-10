<?php 

return [
	//自定义模板目录
	'view_path' => '../template'.DIRECTORY_SEPARATOR.'home'.DIRECTORY_SEPARATOR,

	// 模板参数
    'tpl_replace_string' => [
        '__SKIN__' => CSN_DOMAIN.'/home',
        '__CSS__' => CSN_DOMAIN.'/home/css',
        '__JS__' => CSN_DOMAIN.'/home/js',
        '__IMAGE__' => CSN_DOMAIN.'/home/image',
    ],
];