<?php

namespace app\home\controller;

use think\Request;
use app\components\helper\ArrayHelper;
use app\components\helper\StringHelper;
use app\services\category\BUCategoryService;
use app\services\article\BUArticleService;
use app\services\common\BUCom;
use app\components\Urls;
use app\Inc\TableConst;
use app\components\COM;

class Article extends Base
{
    protected $pagesize = 25;
    protected $maxPage = 20; //列表页最多放出分页数

    public function lists(Request $request)
    {
    	$pinyin = $request->param('pinyin');
        $page = $request->param('page', 1, 'intval');

        $baseUrl = Urls::url('home_category-pinyin', $pinyin);
        $firstUrl = $baseUrl;

        //当前分类
        $info = BUCategoryService::getCateInfoByPinyin($pinyin);
        if (empty($info)) {
            COM::sendHttpStatus(404);
            return $this->fetch('public/404');
        }

        //父级分类
        $cateParentInfo = BUCategoryService::getCateInfo($info['parentid']);

        $subCate = BUCategoryService::getSubCateList($info['id']);

        //故事排行
        $toplist = BUArticleService::getHitsArticle(0, 10); 

        //推荐故事
        $recommend = BUArticleService::getRandByIndex(10); 

        $list = [];
        $count = BUArticleService::getArticleTotal($info['id']);
        if ($count) {
            $list = BUArticleService::getArticleList($info['id'], $page, $this->pagesize);
        }

        foreach ($list as &$val) {
            $val['introduction'] = StringHelper::clearTrim($val['introduction']);
        }

        //限制列表页
        if ($count >= ($this->maxPage * $this->pagesize)) {
            $count = $this->maxPage * $this->pagesize;
        }       
        if ($page > $this->maxPage) {
            $page = $this->maxPage;
        }
        $pages = BUCom::showPages($baseUrl, $firstUrl, $count, $page, $this->pagesize, 2);

        $this->assign('info', $info);
        $this->assign('cateParentInfo', $cateParentInfo);
        $this->assign('count', $count);
        $this->assign('list', $list);
        $this->assign('toplist', $toplist);
        $this->assign('recommend', $recommend);
        $this->assign('pages', $pages);
        $this->assign('subCate', $subCate);
        return $this->fetch();
    }

    public function detail(Request $request)
    {
    	$identify = $request->param('identify');

        $info = BUArticleService::getArticleByIdentify($identify);
        if (empty($info) || $info['status'] != TableConst::ARTICLE_STATUS_PASS || $info['is_show'] != TableConst::ARTICLE_SHOW_OK) {
            COM::sendHttpStatus(404);
            return $this->fetch('public/404');
        }
        $info['introduction'] = StringHelper::clearTrim($info['introduction']);

        //当前分类
        $cateInfo = BUCategoryService::getCateInfo($info['catid']);

        //父级分类
        $cateParentInfo = BUCategoryService::getCateInfo($cateInfo['parentid']);

        //上一页
        $prelink = BUArticleService::getArticlePrev($info['id']);

        //下一页
        $nextlink = BUArticleService::getArticleNext($info['id']);

        //故事排行
        $toplist = BUArticleService::getHitsArticle(0, 10); 

        //推荐故事
        $recommend = BUArticleService::getRandByIndex(10); 

        //相关故事
        $related = [];
        $related = BUArticleService::getRandByCids($info['catid'], 10); 

        $this->assign('info', $info);
        $this->assign('prelink', $prelink);
        $this->assign('nextlink', $nextlink);
        $this->assign('cateInfo', $cateInfo);
        $this->assign('related', $related);
        $this->assign('cateParentInfo', $cateParentInfo);
        $this->assign('toplist', $toplist);
        $this->assign('recommend', $recommend);
        return $this->fetch();
    }

   
}
