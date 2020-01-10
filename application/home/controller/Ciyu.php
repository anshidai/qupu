<?php

namespace app\home\controller;

use think\Request;
use app\components\helper\ArrayHelper;
use app\components\helper\StringHelper;
use app\services\category\BUCategoryService;
use app\services\ciyu\BUWordsService;
use app\services\common\BUCom;
use app\services\common\BUPages;
use app\components\Urls;
use app\Inc\TableConst;
use app\components\COM;

class Ciyu extends Base
{
    protected $pagesize = 10;
    protected $maxPage = 20; //列表页最多放出分页数

    public function initialize() 
    {
        parent::initialize();

        //类型
        $cType = getEnums('cType');
        $this->assign('cType', $cType);

        //字数
        $zishuLenth = getEnums('zishuLenth');
        $this->assign('zishuLenth', $zishuLenth);
    }

    public function lists(Request $request)
    {
    	$pinyin = $request->param('pinyin', '');
        $ctype = $request->param('type', 0, 'intval');
        $charlen = $request->param('len', 0, 'intval');
        $page = $request->param('page', 1, 'intval');

        if ($pinyin == 'ciyu') {
            $baseUrl = Urls::url('mobile_ciyu_category');
        } else {
            $baseUrl = Urls::url('mobile_ciyu_category-pinyin', $pinyin);
        }
        $firstUrl = $baseUrl;

        $params = [
            'catid' => 0
        ];

        if ($pinyin != 'ciyu' && $pinyin) {
            //当前分类
            $info = BUCategoryService::getCateInfoByPinyin($pinyin);
            if (empty($info)) {
                COM::sendHttpStatus(404);
                return $this->fetch('public/404');
            }
            $params['catid'] = $info['id'];
        }

        $list = [];
        $count = BUWordsService::getWordsTotal($params['catid']);
        if ($count) {
            $list = BUWordsService::getWordsList($params['catid'], $page, $this->pagesize);
        }

        //限制列表页
        if ($count >= ($this->maxPage * $this->pagesize)) {
            $count = $this->maxPage * $this->pagesize;
        }       
        if ($page > $this->maxPage) {
            $page = $this->maxPage;
        }
        $pages = BUPages::showPcPages($baseUrl, $firstUrl, $count, $page, $this->pagesize, 2);

        $wordsRand = BUWordsService::getRandByIndex(12);
        if ($wordsRand) {
            $wordsRand = array_chunk($wordsRand, 3);
        }

        $this->assign('info', $info);
        $this->assign('count', $count);
        $this->assign('list', $list);
        $this->assign('pages', $pages);
        $this->assign('ctype', $ctype);
        $this->assign('charlen', $charlen);
        $this->assign('wordsRand', $wordsRand);
        return $this->fetch();
    }

    public function detail(Request $request)
    {
    	$identify = $request->param('identify');

        $info = BUWordsService::getWordsByIdentify($identify);
        if (empty($info) || $info['status'] != TableConst::WORDS_STATUS_PASS || $info['is_show'] != TableConst::WORDS_SHOW_OK) {
            COM::sendHttpStatus(404);
            return $this->fetch('public/404');
        }

        //当前分类
        $cateInfo = BUCategoryService::getCateInfo($info['catid']);

        $structList = BUWordsService::parseWordsStruct($info['id'], false);

        //相关
        $related = [];
        $related = BUWordsService::getRandByCidsfilter($info['id'], $info['catid'], 15); 
        foreach ($related as &$val) {
            $val['pos'] = BUCom::getCharLastNum($val['identify']);
        }

        if ($related) {
            $related = array_chunk($related, 3);
        }

        $this->assign('info', $info);
        $this->assign('cateInfo', $cateInfo);
        $this->assign('related', $related);
        $this->assign('structList', $structList);
        return $this->fetch();
    }

   
}
