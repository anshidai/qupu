<?php 

return [
	//自定义模板目录
	'view_path' => '../template'.DIRECTORY_SEPARATOR.'mobile'.DIRECTORY_SEPARATOR,

	// 模板参数
    'tpl_replace_string' => [
        '__MSKIN__' => CSN_DOMAIN.'/mobile',
        '__MCSS__' => CSN_DOMAIN.'/mobile/css',
        '__MJS__' => CSN_DOMAIN.'/mobile/js',
        '__MIMAGE__' => CSN_DOMAIN.'/mobile/image',
    ],
];