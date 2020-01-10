<?php

namespace app\console\command;

use think\console\Input;
use think\console\Output;
use think\facade\Cache;
use app\components\helper\HttpHelper;
use app\components\helper\DirHelper;
use app\Inc\TableConst;
use app\components\COM;
use app\model\chengyu\IdiomModel;

class SyncCache extends Base
{
	protected $pagesize = 1000;

	protected function configure()
    {
        $this->setName('SyncCache')->setDescription('同步缓存');
    }

    protected function execute(Input $input, Output $output)
    {
    	$this->chengyuNameCache();
    }

    /**
	* 成语title写入缓存
    */
    protected function chengyuNameCache()
    {
    	$map = [
            ['status', '=', TableConst::IDIOM_STATUS_PASS],
            ['is_show', '=', TableConst::IDIOM_SHOW_OK],
            //['id', '=', 1],
        ];

        $count = IdiomModel::getTotal($map);
        $pageMax = ceil($count / $this->pagesize);
        for ($page = 1; $page <= $pageMax; $page++) {
        	$list = IdiomModel::getPassList($map, $page, $this->pagesize, ['id','title_hash','title','identify'], 'id asc');
        	foreach ($list as $val) {
        		$cacheKey =  COM::getCachekey('idiom_title_cache');

    			if (!Cache::hexists($cacheKey, $val['title_hash'])) {
    				Cache::hset($cacheKey, $val['title_hash'], $val['identify']);
    			}

    			self::printLog("id: {$val['id']}");
        	}
        }

        self::printLog("chengyuNameCache complete");
    }


}