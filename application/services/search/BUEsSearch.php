<?php

namespace app\common\model\bu;

use think\facade\Request;
use think\facade\Cache;
use app\components\COM;
use app\components\helper\ArrayHelper;
use Elasticsearch\ClientBuilder;
use app\common\model\bu\BUElasticSearch;

/**
* es搜索引擎
*/
class BUEsSearch
{	
	protected static $esIndex = 'xuanjuzi';

	/**
	* 搜索文章
	* @param string $keyword 搜索词
	* @param int $page 分页码
	* @param int $limit 每页数
	*/
	public static function getSearchByKeyword($keyword, $page = 1, $limit = 10)
	{
		$keyword = str_replace(['句子','的'], '', $keyword);
		$keyword = preg_replace('/[\d+]字/', '', $keyword);

		$elasticSearch = new BUElasticSearch(self::$esIndex);
		$map = [
			'match' => [
				['title' => $keyword],
			],		
		];

		$data = [];
		$res = $elasticSearch->searchDocument($map, $page, $limit);
		if ($res) {
			$searchList = $res['hits']['hits'];
			foreach ($searchList as $val) {
				$data[] = $val['_source'];
			}
		}

		return $data;
	}


}