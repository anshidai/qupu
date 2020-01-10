<?php

namespace app\services\ciyu;

use think\facade\Request;
use think\facade\Cache;
use app\model\ciyu\WordsModel;
use app\model\ciyu\WordsDataModel;
use app\model\ciyu\WordsStructModel;
use app\services\category\BUCategoryService;
use app\components\helper\StringHelper;
use app\components\helper\ArrayHelper;
use app\components\COM;
use app\Inc\TableConst;

/**
* 词语业务处理
*/
class BUWordsService
{
	/**
	* 获取词语信息
	* @param string $identify 标记
	*/
	public static function getWordsByIdentify($identify)
	{
		$data = WordsModel::getInfoByIdentify($identify);
		if ($data) {
			$content = WordsDataModel::getInfoByWordid($data['id']);
            $content['content'] = StringHelper::replaceSymbol($content['content']);
			$data['content'] = $content;
		}

		return $data;
	}

	/**
	* 获取词语信息
	* @param int $id 词语id
	*/
	public static function getWordsById($id)
	{
		$data = WordsModel::getInfo($id);

		return $data;
	}

	/**
	* 获取最新词语
	* @param int|string|array $cids 分类id
	* @param int $num 记录数
	*/
	public static function getBestWords($cids = 0, $num = 10)
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
			$data = WordsModel::getPassList($map, 1, $num, $field, 'edittime desc');

			$expires = 3600 + rand(30, 60);
			Cache::set($cacheKey, json_encode($data), $expires);
		} else {
			$data = json_decode($data, true);
		}

		return $data;
	}

	/**
	* 获取今日更新词语
	* @param int $num 记录数
	*/
	public static function getTodayWords($num = 10)
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
			$data = WordsModel::getPassList($map, 1, $num, $field);

			$expires = 86400 + rand(30, 60);
			Cache::set($cacheKey, json_encode($data), $expires);
		} else {
			$data = json_decode($data, true);
		}

		return $data;
	}

	/**
	* 根据词语浏览量
	* @param int|string|array $cids 分类id
	* @param int $num 记录数
	*/
	public static function getHitsWords($cids = 0, $num = 10)
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
			$data = WordsModel::getPassList($map, 1, $num, $field, 'hits desc');

			$expires = 3600 + rand(30, 60);
			Cache::set($cacheKey, json_encode($data), $expires);
		} else {
			$data = json_decode($data, true);
		}

		return $data;
	}

	/**
	* 随机获取词语
	*/
	public static function getRandByIndex($num = 10)
	{
		$cacheKey =  COM::getCachekey('home_rand_index', $num);
    	$data = Cache::get($cacheKey);
		if (empty($data)) {
			$field = 't1.id,t1.title,t1.identify,t1.catid,t1.catname,t1.title_pinyin,t1.base_explain,t1.edittime';
			$data = WordsModel::getRandListByCateId(0, $num, $field);

			$expires = 3600 + rand(30, 60);
			Cache::set($cacheKey, json_encode($data), $expires);
		} else {
			$data = json_decode($data, true);
		}

		return $data;
	}

	/**
	* 随机获取同分类线下词语
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
			$data = WordsModel::getRandListByCateId($cids, $num, $field);

			$expires = 3600 + rand(30, 60);
			Cache::set($cacheKey, json_encode($data), $expires);
		} else {
			$data = json_decode($data, true);
		}

		return $data;
	}


	/**
	* 随机获取词语
	*/
	public static function getRandByCids($cids, $num = 10)
	{
		$cacheKey =  COM::getCachekey('home_words_rand_index', [$cids, $num]);
    	$data = Cache::get($cacheKey);
		if (empty($data)) {
			if (is_numeric($cids) || is_string($cids)) {
				$cids = explode(',', $cids);
			} 

			$field = 't1.id,t1.title,t1.identify,t1.catid,t1.catname,t1.title_pinyin,t1.base_explain,t1.edittime';
			$data = WordsModel::getRandListByCateId($cids, $num, $field);

			$expires = 3600 + rand(30, 60);
			Cache::set($cacheKey, json_encode($data), $expires);
		} else {
			$data = json_decode($data, true);
		}

		return $data;
	}

	/**
    * 获取上一条
    * @param int $id 词语id
    */
    public static function getWordsPrev($id)
    {
        $map = array(
        	['id', '<', $id],
            ['status', '=', TableConst::WORDS_STATUS_PASS],
            ['is_show', '=', TableConst::WORDS_SHOW_OK],
        );

        return WordsModel::getInfoByMap($map, ['id','title','identify']);
    }

    /**
    * 获取下一条记录
    * @param int $id 词语id
    */
    public static function getWordsNext($id)
    {
    	$map = array(
    		['id', '>', $id],
            ['status', '=', TableConst::WORDS_STATUS_PASS],
            ['is_show', '=', TableConst::WORDS_SHOW_OK],
        );

        return WordsModel::getInfoByMap($map, ['id','title','identify']);
    }

    /**
    * 获取词语列表
    * @param int $cid 分类id
    * @param int $p 分页码
    * @param int $pagesize 获取数量
    */
    public static function getWordsList($cid = 0, $page = 1, $pagesize = 10)
    {
    	$map = [
    		['status', '=', TableConst::WORDS_STATUS_PASS],
            ['is_show', '=', TableConst::WORDS_SHOW_OK],
    	];

        if($cid) {
        	$catids = BUCategoryService::getCateIdsByParentId($cid);
        	if (empty($catids)) {
        		$catids = [$cid];
        	} 
        	array_push($map, ['catid', 'in', $catids]);
        }

        $field = ['id','title','identify','catid','catname','title_pinyin','base_explain','edittime'];
        $list = WordsModel::getList($map, $page, $pagesize, $field, 'edittime desc');
        
        return $list;
    }
    
    /**
    * 获取词语总数
    * @param int $cid 分类id
    */
    public static function getWordsTotal($cid = 0)
    {
     	$map = [
    		['status', '=', TableConst::WORDS_STATUS_PASS],
            ['is_show', '=', TableConst::WORDS_SHOW_OK],
    	];

        if($cid) {
        	$catids = BUCategoryService::getCateIdsByParentId($cid);
        	if (empty($catids)) {
        		$catids = [$cid];
        	} 
        	array_push($map, ['catid', 'in', $catids]);
        }
        
        return WordsModel::getTotal($map);
    }

    /**
	* 表单搜索
    */
    public static function getSearchByKeyword($keyword, $pagesize = 20)
    {
    	$map = [
    		['status', '=', TableConst::WORDS_STATUS_PASS],
            ['is_show', '=', TableConst::WORDS_SHOW_OK],
            ['title', 'like', "%{$keyword}%"]
    	];

    	$field = ['id','title','identify','catid','catname','edittime'];
        $list = WordsModel::getList($map, 1, $pagesize, $field);

        return $list;
    }

    /**
	* 根据多个id获取
	* @param string|array $ids id
	* @param array $field 显示字段
    */
    public static function getWordsListByIds($ids, $field = [], $limit = 0)
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
    		['status', '=', TableConst::WORDS_STATUS_PASS],
            ['is_show', '=', TableConst::WORDS_SHOW_OK],
    		['id', 'in', $ids]
    	];
        $list = WordsModel::getList($map, 1, $limit, $field);

        return $list;
    }	


    /**
	* 获取成语结构
	* @param int $ciyuid 词语id
	*/
	public static function getWordsStruct($ciyuid)
	{
		$data = WordsStructModel::getListByIdwordid($ciyuid);
		if ($data) {
			$data = ArrayHelper::toHashmap($data, 'ctype');
		}
		
		return $data;
	}

	/**
	* 成语结构
	*/
	public static function parseWordsStruct($ciyuid, $chunk = true)
	{
		$data = [];

		$struct = self::getWordsStruct($ciyuid);
		foreach ($struct as $val) {
			if (in_array($val['ctype'], [TableConst::WORDS_STRUCT_SYNONYM])) {
				$val['content_arr'] = [];
				if (!empty($val['content'])) {
					$val['content_arr'] = explode("\n", $val['content']);
					if ($chunk) {
						$val['content_arr'] = array_chunk($val['content_arr'], 3);
					}
				}
				$data['synonym'][] = $val;
			} elseif (in_array($val['ctype'], [TableConst::WORDS_STRUCT_ANTONYM])) {
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


}