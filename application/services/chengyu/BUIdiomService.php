<?php

namespace app\services\chengyu;

use think\facade\Request;
use think\facade\Cache;
use app\model\chengyu\IdiomModel;
use app\model\chengyu\IdiomDataModel;
use app\model\chengyu\IdiomStructModel;
use app\services\category\BUCategoryService;
use app\components\helper\StringHelper;
use app\components\helper\ArrayHelper;
use app\components\COM;
use app\Inc\TableConst;

/**
* 成语业务处理
*/
class BUIdiomService
{
	/**
	* 获取成语信息
	* @param string $identify 标记
	*/
	public static function getIdiomByIdentify($identify)
	{
		$data = IdiomModel::getInfoByIdentify($identify);
		if ($data) {
			$content = IdiomDataModel::getInfoByIomid($data['id']);
			if ($content) {
				$content['content'] = StringHelper::replaceSymbol($content['content']);
			}
			$data['content'] = $content;
		}

		return $data;
	}

	/**
	* 获取成语信息
	* @param int $id 成语id
	*/
	public static function getIdiomById($id)
	{
		$data = IdiomModel::getInfo($id);

		return $data;
	}

	/**
	* 获取最新成语
	* @param int|string|array $cids 分类id
	* @param int $num 记录数
	*/
	public static function getBestIdiom($cids = 0, $num = 10)
	{
		if (is_numeric($cids)) {
			$cids = $cids? [$cids]: [];
		} elseif (strpos(',', $cids) !== false) {
			$cids = explode(',', $cids);
		}

		$cacheKey =  COM::getCachekey('home_best_cids', [$cids, $num]);
    	$data = Cache::get($cacheKey);
		if (empty($data)) {
			$map = [];
			if ($cids) {
				array_push($map, ['catid', 'in', $cids]);
			}

			$field = ['id','title','identify','catid','catname','edittime'];
			$data = IdiomModel::getPassList($map, 1, $num, $field, 'edittime desc');

			$expires = 3600 + rand(30, 60);
			Cache::set($cacheKey, json_encode($data), $expires);
		} else {
			$data = json_decode($data, true);
		}

		return $data;
	}

	/**
	* 获取今日更新成语
	* @param int $num 记录数
	*/
	public static function getTodayIdiom($num = 10)
	{
		$cacheKey =  COM::getCachekey('home_today_words', $num);
    	$data = Cache::get($cacheKey);
		if (empty($data)) {
			$map = [
				['catid', 'in', $cids],
				['edittime', '>=', date('Y-m-d').' 00:00:01'],
				['edittime', '<=', date('Y-m-d').' 23:59:59'],
			];
			$field = ['id','title','identify','catid','catname','edittime'];
			$data = IdiomModel::getPassList($map, 1, $num, $field);

			$expires = 86400 + rand(30, 60);
			Cache::set($cacheKey, json_encode($data), $expires);
		} else {
			$data = json_decode($data, true);
		}

		return $data;
	}

	/**
	* 根据成语浏览量
	* @param int|string|array $cids 分类id
	* @param int $num 记录数
	*/
	public static function getHitsIdiom($cids = 0, $num = 10)
	{
		if (is_numeric($cids)) {
			$cids = $cids? [$cids]: [];
		} elseif (strpos(',', $cids) !== false) {
			$cids = explode(',', $cids);
		}

		$cacheKey =  COM::getCachekey('home_hits_cids', [$cids, $num]);
    	$data = Cache::get($cacheKey);
		if (empty($data)) {
			$map = [];
			if ($cids) {
				array_push($map, ['catid', 'in', $cids]);
			}

			$field = ['id','title','identify','catid','catname','edittime','introduction'];
			$data = IdiomModel::getPassList($map, 1, $num, $field, 'hits desc');

			$expires = 3600 + rand(30, 60);
			Cache::set($cacheKey, json_encode($data), $expires);
		} else {
			$data = json_decode($data, true);
		}

		return $data;
	}

	/**
	* 随机获取成语
	*/
	public static function getRandByIndex($num = 10)
	{
		$cacheKey =  COM::getCachekey('home_idiom_rand_index', $num);
    	$data = Cache::get($cacheKey);
		if (empty($data)) {
			$field = 't1.id,t1.title,t1.identify,t1.catid,t1.catname,t1.title_pinyin,t1.base_explain,t1.edittime';
			$data = IdiomModel::getRandListByCateId(0, $num, $field);

			$expires = 3600 + rand(30, 60);
			Cache::set($cacheKey, json_encode($data), $expires);
		} else {
			$data = json_decode($data, true);
		}

		return $data;
	}

	/**
	* 随机获取同分类线下成语
	*/
	public static function getRandByCidsfilter($id, $cids, $num = 10)
	{
		$cacheKey =  COM::getCachekey('home_rand_cid_filter', [$id, $cids, $num]);
    	$data = Cache::get($cacheKey);
		if (empty($data)) {
			if (is_numeric($cids) || is_string($cids)) {
				$cids = explode(',', $cids);
			} 

			$field = 't1.id,t1.title,t1.identify,t1.catid,t1.catname,t1.title_pinyin,t1.base_explain,t1.edittime';
			$data = IdiomModel::getRandListByCateId($cids, $num, $field);

			$expires = 3600 + rand(30, 60);
			Cache::set($cacheKey, json_encode($data), $expires);
		} else {
			$data = json_decode($data, true);
		}

		return $data;
	}


	/**
	* 随机获取成语
	*/
	public static function getRandByCids($cids, $num = 10)
	{
		$cacheKey =  COM::getCachekey('home_rand_cid_index', [$cids, $num]);
    	$data = Cache::get($cacheKey);
		if (empty($data)) {
			if (is_numeric($cids) || is_string($cids)) {
				$cids = explode(',', $cids);
			} 

			$field = 't1.id,t1.title,t1.identify,t1.catid,t1.catname,t1.title_pinyin,t1.base_explain,t1.edittime';
			$data = IdiomModel::getRandListByCateId($cids, $num, $field);

			$expires = 3600 + rand(30, 60);
			Cache::set($cacheKey, json_encode($data), $expires);
		} else {
			$data = json_decode($data, true);
		}

		return $data;
	}

	/**
    * 获取上一条
    * @param int $id 成语id
    */
    public static function getIdiomPrev($id)
    {
        $map = array(
        	['id', '<', $id],
            ['status', '=', TableConst::WORDS_STATUS_PASS],
            ['is_show', '=', TableConst::WORDS_SHOW_OK],
        );

        return IdiomModel::getInfoByMap($map, ['id','title','identify']);
    }

    /**
    * 获取下一条记录
    * @param int $id 成语id
    */
    public static function getIdiomNext($id)
    {
    	$map = array(
    		['id', '>', $id],
            ['status', '=', TableConst::WORDS_STATUS_PASS],
            ['is_show', '=', TableConst::WORDS_SHOW_OK],
        );

        return IdiomModel::getInfoByMap($map, ['id','title','identify']);
    }

    /**
    * 获取成语列表
    * @param int $cid 分类id
    * @param int $p 分页码
    * @param int $pagesize 获取数量
    */
    public static function getIdiomList($catid = 0, $params = [], $page = 1, $pagesize = 10)
    {
    	$map = [
    		['status', '=', TableConst::IDIOM_STATUS_PASS],
            ['is_show', '=', TableConst::IDIOM_SHOW_OK],
    	];
    	$map = array_merge($map, $params);

        if($catid) {
        	$catids = BUCategoryService::getCateIdsByParentId($catid);
        	if (empty($catids)) {
        		$catids = [$catid];
        	} 
        	array_push($map, ['catid', 'in', $catids]);
        }

        $field = ['id','title','identify','catid','catname','title_pinyin','base_explain','voice_file','edittime'];
        $list = IdiomModel::getList($map, $page, $pagesize, $field, 'edittime desc');
        
        return $list;
    }
    
    /**
    * 获取成语总数
    * @param int $cid 分类id
    */
    public static function getIdiomTotal($catid = 0, $params = [])
    {
     	$map = [
    		['status', '=', TableConst::IDIOM_STATUS_PASS],
            ['is_show', '=', TableConst::IDIOM_SHOW_OK],
    	];
    	$map = array_merge($map, $params);

        if($catid) {
        	$catids = BUCategoryService::getCateIdsByParentId($catid);
        	if (empty($catids)) {
        		$catids = [$catid];
        	} 
        	array_push($map, ['catid', 'in', $catids]);
        }
        
        return IdiomModel::getTotal($map);
    }

    /**
	* 表单搜索
    */
    public static function getSearchByKeyword($keyword, $pagesize = 20)
    {
    	$map = [
    		['status', '=', TableConst::IDIOM_STATUS_PASS],
            ['is_show', '=', TableConst::IDIOM_SHOW_OK],
            ['title', 'like', "%{$keyword}%"]
    	];

    	$field = ['id','title','identify','catid','catname','edittime'];
        $list = IdiomModel::getList($map, 1, $pagesize, $field);

        return $list;
    }

    /**
	* 根据多个id获取
	* @param string|array $ids id
	* @param array $field 显示字段
    */
    public static function getIdiomListByIds($ids, $field = [], $limit = 0)
    {
    	$data = [];
    	if (empty($ids)) {
    		return $data;
    	}

    	if (is_string($ids) || is_numeric($ids)) {
    		$ids = explode(',', $ids);
    	}

    	if (empty($field)) {
    		$field = ['id','title','identify','catid','catname','edittime'];
    	}

    	$map = [
    		['status', '=', TableConst::IDIOM_STATUS_PASS],
            ['is_show', '=', TableConst::IDIOM_SHOW_OK],
    		['id', 'in', $ids]
    	];
        $list = IdiomModel::getList($map, 1, $limit, $field);

        return $list;
    }	

    /**
	* 获取成语结构
	* @param int $idiomid 成语id
	*/
	public static function getIdiomStruct($idiomid)
	{
		$data = IdiomStructModel::getListByIdiomid($idiomid);
		if ($data) {
			foreach ($data as &$val) {
				$val['name'] = TableConst::$idiomStruct[$val['ctype']];
			}
			$data = ArrayHelper::toHashmap($data, 'ctype');
		}
		
		return $data;
	}


	/**
	* 成语结构
	*/
	public static function parseIdiomStruct($idiomid, $chunk = true)
	{
		$data = [];

		$struct = self::getIdiomStruct($idiomid);
		foreach ($struct as $val) {
			if (in_array($val['ctype'], [TableConst::IDIOM_STRUCT_EXPLAIN,TableConst::IDIOM_STRUCT_SOURCE,TableConst::IDIOM_STRUCT_CASE,TableConst::IDIOM_STRUCT_GRAMMAR,TableConst::IDIOM_STRUCT_STRUCT,TableConst::IDIOM_STRUCT_DEGREE,TableConst::IDIOM_STRUCT_EMOTION,TableConst::IDIOM_STRUCT_ENRTIME, TableConst::IDIOM_STRUCT_PRONOUNCE,TableConst::IDIOM_STRUCT_DISCERN,TableConst::IDIOM_STRUCT_RIDDLE])) {
				$data['base'][] = $val;
			} elseif (in_array($val['ctype'], [TableConst::IDIOM_STRUCT_ENSAMPLE])) {
				$data['ensample'][] = $val;
			} elseif (in_array($val['ctype'], [TableConst::IDIOM_STRUCT_SYNONYM])) {
				$val['content_arr'] = [];
				if (!empty($val['content'])) {
					$val['content_arr'] = explode("\n", $val['content']);
					if ($chunk) {
						$val['content_arr'] = array_chunk($val['content_arr'], 3);
					}
				}
				$data['synonym'][] = $val;
			} elseif (in_array($val['ctype'], [TableConst::IDIOM_STRUCT_ANTONYM])) {
				$val['content_arr'] = [];
				if (!empty($val['content'])) {
					$val['content_arr'] = explode("\n", $val['content']);
					if ($chunk) {
						$val['content_arr'] = array_chunk($val['content_arr'], 3);
					}
				}
				$data['antonym'][] = $val;
			}
		}

		return $data;
	}

	/**
	* 查询成语缓存是否有记录
	*/
	public static function getChengyuHashCache($title)
	{
		$identify = '';
		if (empty($title)) {
			return $identify;
		}

		$cacheKey =  COM::getCachekey('idiom_title_cache');
		$identify = Cache::hget($cacheKey, md5($title));

		return $identify ?? '';
	}


}