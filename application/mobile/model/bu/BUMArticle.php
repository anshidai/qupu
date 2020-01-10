<?php 

namespace app\mobile\model\bu;

use app\common\model\Article as ArticleModel;
use app\common\model\ArticleData as ArticleDataModel;
use think\facade\Cache;
use app\components\COM;
use app\common\model\bu\BUCommon;
use app\common\model\bu\BUCategory;

/**
* 文章相关业务操作
*/
class BUMArticle
{

	public static function getPages($baseUrl, $firstUrl, $total, $page, $pagesize, $numLinks = 1)
    {
        $params = array(
            'baseUrl' => $baseUrl,
            'firstUrl' => $firstUrl,
            'total' => $total,
            'pagesize' => $pagesize,
            'currPage' => $page,
            'numLinks' => $numLinks,

            'full_tag_open' => '',
            'full_tag_close' => '',
            'first_tag_open' => '',
            'first_tag_close' => '',
            
            'cur_tag_open' => '<a><font color="red">',
            'cur_tag_close' => '</font></a>',
            
            'num_tag_open' => '',
            'num_tag_close' => '',
            
            'prev_tag_open' => '',
            'prev_tag_close' => '',
            
            'next_tag_open' => '',
            'next_tag_close' => '',
            
            'last_tag_open' => '',
            'last_tag_close' => '',
        );

        return BUCommon::pageShow($params);
    }

}