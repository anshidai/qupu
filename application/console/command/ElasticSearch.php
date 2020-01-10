<?php

namespace app\console\command;

use think\console\Input;
use think\console\Output;
use app\common\model\Article as ArticleModel;
use app\components\Urls;
use app\common\model\bu\BUElasticSearch;

/**
* ES搜索引擎
*/
class ElasticSearch extends Base
{
	protected static $pagesize = 1000;
	protected static $esIndex = 'xuanjuzi';

	protected function configure()
    {
        $this->setName('slasticsearch')->setDescription('ES搜索引擎');
    }

    protected function execute(Input $input, Output $output)
    {
    	// $this->createEsIndex();
    	// $this->createEsMappings();
    	// $this->createFullArticle();
    }

    /**
	* 全量推送文章到搜索引擎
    */
    public function createFullArticle()
    {
    	$elasticSearch = new BUElasticSearch(self::$esIndex);

    	$map = [
   			['status', '=', ArticleModel::STATUS_PASS],
			['is_show', '=', ArticleModel::SHOW_OK],
   		];

   		$count = ArticleModel::getTotal($map);
   		$step = $count;
   		$pageMax = ceil($count / self::$pagesize);
   		for ($page = 1; $page <= $pageMax; $page++) {
   			$list = ArticleModel::getList($map, $page, self::$pagesize, ['*'], 'id asc');
   			foreach ($list as $val) {
   				$doc = [
					'id' => $val['id'],
					'title' => $val['title'],
					'identify' => $val['identify'],
					'catid' => $val['catid'],
					'catname' => $val['catname'],
					'tags' => $val['tags'],
					'lexer_word' => $val['lexer_word'],
					'introduction' => $val['introduction'],
					'addtime' => date('Y-m-d', strtotime($val['addtime'])). 'T'. date('H:i:s', strtotime($val['addtime'])).'+08:00',
					'edittime' => date('Y-m-d', strtotime($val['edittime'])). 'T'. date('H:i:s', strtotime($val['edittime'])).'+08:00',
					'status' => $val['status'],
					'is_show' => $val['is_show'],
				];
				$res = $elasticSearch->addDocument($val['id'], $doc);

				$step--;
				self::printLog("{$count} - {$step}");
				self::printLog($res);
   			}
   		}

   		self::printLog('createFullArticle');
    }

    /**
	* 创建ES索引
    */
    protected function createEsIndex()
    {
		$elasticSearch = new BUElasticSearch(self::$esIndex);

		$res = $elasticSearch->createIndex();
		var_dump($res);exit;
    }

    /**
	* 创建ES Mapping
    */
    protected function createEsMappings()
    {
		$elasticSearch = new BUElasticSearch(self::$esIndex);

		/**
		* 
		index定义字段的分析类型以及检索方式
			no，则无法通过检索查询到该字段
			not_analyzed则会将整个字段存储为关键词，常用于汉字短语、邮箱等复杂的字符串
			analyzed则将会通过默认的standard分析器进行分析
		*/
		$properties = [
			'id' => [
				'type' => 'integer',
				'index' => true, //是否构建倒排索引 默认true
			],
			'title' => [
				'type' => 'text',

				//指定分词器，默认分词器为standard analyzer
				//ik_max_word 会将文本做最细粒度的拆分 会将文本做最细粒度的拆分，比如会将“中华人民共和国国歌”拆分为“中华人民共和国,中华人民,中华,华人,人民共和国,人民,人,民,共和国,共和,和,国国,国歌”
				//ik_smart 会做最粗粒度的拆分，比如会将“中华人民共和国国歌”拆分为“中华人民共和国,国歌”
				'analyzer' => 'ik_max_word',
			],
			'identify' => [
				'type' => 'keyword',
			],
			'catid' => [
				'type' => 'integer',
			],
			'catname' => [
				'type' => 'text',
			],
			'tags' => [
				'type' => 'text',
			],
			'lexer_word' => [
				'type' => 'text',
			],
			'introduction' => [
				'type' => 'text',
				// 'index' => false,
			],
			'addtime' => [
				'type' => 'date',
			],
			'edittime' => [
				'type' => 'date',
			],
			'status' => [
				'type' => 'integer',
			],
			'is_show' => [
				'type' => 'integer',
			],
		];

		$res = $elasticSearch->createMappings($properties);
		var_dump($res);exit;
    }


}