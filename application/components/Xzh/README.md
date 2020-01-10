# 熊掌号开放平台基础sdk说明
## sdk提供的能力
* 消息加减密          lib/AesEncryptUtil.php
* auth授权           lib/Api/AuthApi.php 
* 第三方服务商授权相关 lib/Api/TpAuthApi.php
* OpenAPI curl基类   lib/Api/OpenApi.php

## 目录结构说明

````

|____composer.lock
|____test                      #测试目录
| |____Api
| | |____TpAuthApiTest.php     #第三方服务商授权相关测试
| | |____OpenApiTest.php       #OpenAPI调用示例
| | |____AuthApiTest.php       #熊掌号授权相关测试
| |____Encrypt
| | |____AesEncryptTest.php    #消息加减密测试
|____README.md
|____lib
| |____SHA1Util.php            #SHA1签名工具类
| |____XzhConst.php            
| |____XzhException.php
| |____XzhHttpClient.php
| |____XzhError.php
| |____XzhBase.php
| |____XmlUtil.php             #XML处理工具类
| |____Api                     #API
| | |____AuthApi.php           #授权相关API
| | |____TpAuthApi.php         #第三方服务商授权相关API
| | |____OpenApi.php           #OpenAPI基类
| |____AesEncryptUtil.php      #AES 消息加减密
|____composer.json             #composer项目依赖配置

````

## 使用说明
* 项目为Composer项目，需要安装Composer
* 在根目录下 composer update 下载依赖包
````

demo
lib
test
vendor
composer.json
composer.lock
README.md

````
* 自动加载 require_once('/path/vendor/autoload.php');

## 示例
````

use Xzh\Client\Api\AuthApi;

require_once(dirname(__DIR__) . '/vendor/autoload.php');

$clientId = '6HzvBSGAvkFMUrhELUZayfH2No86t1k';
$clientSecret = 'umHZ1Wd33IYPr1zHXLKqXvHMGXCgdPRO';
$xzhAuth = new AuthApi();
$creInfo = $xzhAuth->getAccessTokenByClientCredentials($clientId, $clientSecret);
print_r($creInfo);

````
