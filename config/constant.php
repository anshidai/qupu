<?php


/**
* 生产环境
*/

/**
* 定义系统常量
* 【注意事项】：
	1、定义code状态码 以CODE_开头
	2、如果是域名 需http://或https://开头， 结尾不带斜线
	3、如果是目录 结尾不带斜线 如: /data/xxx/ruturn
*/

//错误code
defined('CODE_ERROR') or define('CODE_ERROR', 10000); 

//正确code
defined('CODE_RIGHT') or define('CODE_RIGHT', 2000);

//加密秘钥
defined('ENCRYPT_KEY') or define('ENCRYPT_KEY', '6NXB6wc1lYZM6ILwhi3D3RSBX8HoBJLm');

//网站名称
defined('SITE_NAME') or define('SITE_NAME', '曲谱网');

//备案号
defined('SITE_ICP') or define('SITE_ICP', '京ICP备17070026号-1');

//conig配置目录
defined('CONFIG_PATH') or define('CONFIG_PATH', ROOT_PATH .'/config');

//mp3,图片存放目录
defined('DOWN_UPLOAD_PATH') or define('DOWN_UPLOAD_PATH', '/home/wwwroot/down-qupu.jupeixun.cn');

//图片域名
defined('IMG_DOMAIN') or define('IMG_DOMAIN', 'https://img01-qupu.jupeixun.cn');

//静态资源域名 css,js
defined('S_DOMAIN') or define('S_DOMAIN', 'https://s2-qupu.jupeixun.cn');

//csdn 静态资源域名
defined('CSN_DOMAIN') or define('CSN_DOMAIN', 'https://s2-qupu.jupeixun.cn');

//pc域名
defined('WWW_DOMAIN') or define('WWW_DOMAIN', 'https://qp.jupeixun.cn');

//m站域名
defined('MOBILE_DOMAIN') or define('MOBILE_DOMAIN', 'https://qupu.jupeixun.cn');

//下载附件域名
defined('DOWN_DOMAIN') or define('DOWN_DOMAIN', 'https://qupu.jupeixun.cn');



