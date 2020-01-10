<?php

namespace app\mobile\controller;

use think\Request;
use think\facade\Cookie;
use think\Cache;
use think\Log;
use think\Loader;
use app\components\helper\ArrayHelper;

class Test extends Base
{
    public function test(Request $request)
    {
		//https://www.jianshu.com/p/0e15930f89d0
		Loader::import('Elasticsearch.autoload');
		
		$params['hosts'] = array(
            '127.0.0.1:9200'
        );
        $this->client = new \Elasticsearch\Client($params);
		var_dump($this->client);
		
	}
	
	
}