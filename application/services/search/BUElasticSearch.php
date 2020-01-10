<?php

namespace app\common\model\bu;

use think\facade\Request;
use think\facade\Cache;
use app\components\COM;
use app\components\helper\ArrayHelper;
use Elasticsearch\ClientBuilder;

/**
* ElasticSearch业务逻辑
* 参考 
* https://blog.csdn.net/Srodong/article/details/88414092
* https://blog.csdn.net/zx711166/article/details/81667862
* https://www.cnblogs.com/zlslch/p/6440373.html
* https://www.cnblogs.com/leeSmall/p/9195782.html
* https://github.com/medcl/elasticsearch-analysis-ik
* https://blog.csdn.net/haixwang/article/details/80358324
*/
class BUElasticSearch
{	
    protected $client = null;
    protected $esIndex = null;

    public function __construct($esIndex)
    {
        if (empty($esIndex)) {
            throw new \Exception("参数缺失"); 
        }

        $this->esIndex = $esIndex;

        $params = [
            '127.0.0.1:9200'
        ];

        $this->client = ClientBuilder::create()->setHosts($params)->build();
    }

    /**
    * 创建索引
    */
    public function createIndex($shards = 5, $replicas = 0)
    {
        $params = [
            'index' => $this->esIndex,
            'body' => [
                'settings' => [
                    'number_of_shards' => $shards,
                    'number_of_replicas' => $replicas,
                ]
            ]
        ];

        try {
            return $this->client->indices()->create($params);

        } catch (Elasticsearch\Common\Exceptions\BadRequest400Exception $e) {
            $msg = $e->getMessage();
            $msg = json_decode($msg,true);
            return $msg;
        }
    }

    /**
    * 删除索引
    */
    public function delIndex()
    {
        $params = [
            'index' => $this->esIndex,
        ];

        return $this->client->indices()->delete($params);
    }


    /**
    * 创建文档模板映射
    * @param array $properties 
    */
    public function createMappings(array $properties = [])
    {
        $params = [
            'index' => $this->esIndex,
            'body' => [
                '_source' => [
                    'enabled' => true
                ],
                'properties' => $properties,
            ]
        ];

        return  $this->client->indices()->putMapping($params);
    }

    /**
    * 查看映射
    */
    public function getMapping()
    {
        $params = [
            'index' => $this->esIndex,
        ];

        return $this->client->indices()->getMapping($params);
    }

    /**
    * 添加文档
    * @param int $id主键id
    */
    public function addDocument($id, array $doc)
    {
        $params = [
            'index' => $this->esIndex,
            'id' => $id,
            'body' => $doc
        ];
 
        return $this->client->index($params);
    }

    /**
    * 更新文档
    * @param int $id主键id
    */
    public function updateDocument($id, array $doc)
    {
        $params = [
            'index' => $this->esIndex,
            'id' => $id,
            'body' => $doc
        ];
 
        return $this->client->update($params);
    }

    /**
    * 删除文档
    * @param int $id主键id
    * @param array $doc 文档
    */
    public function delDocument($id)
    {
        $params = [
            'index' => $this->esIndex,
            'id' => $id,
        ];
 
        return $this->client->delete($params);
    }

    /**
    * 获取文档
    * @param int $id主键id
    */
    public function getDocument($id)
    {
        $params = [
            'index' => $this->esIndex,
            'id' => $id,
        ];
 
        return $this->client->get($params);
    }

    /**
    * 判断文档是否存在
    * @param int $id主键id
    */
    public function existsDocument($id)
    {
        $params = [
            'index' => $this->esIndex,
            'id' => $id,
        ];
 
        return $this->client->exists($params);
    }

    /**
    * 获取文档列表
    * @param array $map 筛选条件

    //条件查询
    'match' => [
        ['title' => '关键词1'],
        ['title' => '关键词2'],
        ['tag' => 'tag1'],
        ['tag' => 'tag2'],
        ...
    ],

    //针对字段提权重
    'should' => [
        ['title' => '增加权重词1']
        ['title' => '增加权重词2']
    ],
    
    //不包含条件
    'must_not' => [
        ['title' => '不包含关键词1'],
        ['title' => '不包含关键词12'],
        ...
    ],
    
    //排序
    'sort' => [
        'id' => 'desc',
        'addtime' => 'asc',
    ],

    //高亮
    'highlight' => 'title',

    * @param int $page 页码
    * @param int $pagesize 取多少条
    */
    public function searchDocument($map = [], $page = 1, $pagesize = 10)
    {
        $matchArr = $map['match'] ?? [];
        $mustnotArr = $map['must_not'] ?? [];
        $shouldArr = $map['should'] ?? [];
        $sortArr = $map['sort'] ?? [];
        $highlightArr = $map['highlight'] ?? '';

        //必要条件
        $match = [];
        if ($matchArr) {
            foreach ($matchArr as $val) {
                foreach ($val as $key => $item) {
                    $match[] = ['match' => [$key => ['query' => $item]]];
                }
            }
        }

        //过滤条件(字段提权重)
        $should = [];
        if ($shouldArr) {
            foreach ($shouldArr as $val) {
                foreach ($val as $key => $item) {
                    $should[] = ['match' => [$key => ['query' => $item]]];
                }
            }
        }

        //不包含条件
        $mustnot = [];
        if ($mustnotArr) {
            foreach ($mustnotArr as $val) {
                foreach ($val as $key => $item) {
                    $mustnot[] = ['match' => [$key => ['query' => $item]]];
                }
            }
        }

        //排序条件
        $sort = [];
        if ($sortArr) {
            foreach ($sortArr as $key => $val) {
                $sort[$key] = ['order' => $val];
            }
        }

        //高亮
        $highlight = [];
        if ($highlightArr) {
            for ($i = 0; $i <count($highlightArr); $i++) {
                $highlight = [
                    'pre_tags' => ["<em>"],
                    'post_tags' => ["</em>"],
                    'fields' => [
                        $highlightArr[$i] => new \stdClass()
                    ]
                ];
            }
        }

        // $highlight = [
        //         'pre_tags' => ["<em>"],
        //         'post_tags' => ["</em>"],
        //         'fields' => [
        //             'title' => new \stdClass(),
        //             'catname' => new \stdClass()
        //         ]
        //     ];

        $params = [
            'index' => $this->esIndex,
            'body' => [

                //搜索条件
                'query' => [
                    'bool' => [
                        //必须条件
                        'must' => $match,

                        //不包含条件
                        'must_not' => $mustnot,

                        //加权值筛选
                        'should' => $should,
                    ],
                ],

                //排序
                'sort' => $sort,
                'from' => ($page - 1) * $pagesize,
                'size' => $pagesize,

                //高亮
                // 'highlight' => $highlight,
            ],
        ];

        return $this->client->search($params);
    }


}