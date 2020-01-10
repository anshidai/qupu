<?php

namespace app\mobile\controller;

use think\Request;
use app\components\helper\ArrayHelper;
use app\components\helper\StringHelper;
use app\services\category\BUCategoryService;
use app\services\chengyu\BUIdiomService;
use app\services\common\BUCom;
use app\services\common\BUPages;
use app\components\Urls;
use app\Inc\TableConst;
use app\components\COM;

class Chengyu extends Base
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

        if ($pinyin == 'chengyu') {
            $baseUrl = Urls::url('mobile_chengyu_category');
        } else {
            $baseUrl = Urls::url('mobile_chengyu_category-pinyin', $pinyin);
        }
        $firstUrl = $baseUrl;

        $params = [];
        $catid = 0;

        if ($pinyin != 'chengyu' && $pinyin) {
            //当前分类
            $info = BUCategoryService::getCateInfoByPinyin($pinyin);
            if (empty($info)) {
                COM::sendHttpStatus(404);
                return $this->fetch('public/404');
            }
            $catid = $info['id'];
        }

        $list = [];
        $count = BUIdiomService::getIdiomTotal($catid, $params);
        if ($count) {
            $list = BUIdiomService::getIdiomList($catid, $params, $page, $this->pagesize);
            foreach ($list as &$val) {
                $val['voice_file'] = Urls::getMp3Url($val['voice_file']);
            }
        }

        //限制列表页
        if ($count >= ($this->maxPage * $this->pagesize)) {
            $count = $this->maxPage * $this->pagesize;
        }       
        if ($page > $this->maxPage) {
            $page = $this->maxPage;
        }
        $pages = BUPages::showMobilePages($baseUrl, $firstUrl, $count, $page, $this->pagesize, 2);

        $rand = BUIdiomService::getRandByIndex(12);

        $this->assign('info', $info);
        $this->assign('count', $count);
        $this->assign('list', $list);
        $this->assign('pages', $pages);
        $this->assign('ctype', $ctype);
        $this->assign('charlen', $charlen);
        $this->assign('rand', $rand);
        return $this->fetch();
    }

    public function detail(Request $request)
    {
        $identify = $request->param('identify');

        $info = BUIdiomService::getIdiomByIdentify($identify);
        if (empty($info) || $info['status'] != TableConst::IDIOM_STATUS_PASS || $info['is_show'] != TableConst::IDIOM_SHOW_OK) {
            COM::sendHttpStatus(404);
            return $this->fetch('public/404');
        }

        $info['voice_file'] = Urls::getMp3Url($info['voice_file']);


        //检测ip地址是否北京、上海地区, 则不显示广告位
        $showAds = $this->checkIpAdrr()? false: true;

        //当前分类
        $cateInfo = BUCategoryService::getCateInfo($info['catid']);

        $structList = BUIdiomService::parseIdiomStruct($info['id']);

        //相关
        $related = [];
        $related = BUIdiomService::getRandByCidsfilter($info['id'], $info['catid'], 15); 
        foreach ($related as &$val) {
            $val['pos'] = BUCom::getCharLastNum($val['identify']);
        }

        if ($related) {
            $related = array_chunk($related, 3);
        }

        $this->assign('info', $info);
        $this->assign('cateInfo', $cateInfo);
        $this->assign('related', $related);
        $this->assign('showAds', $showAds);
        $this->assign('structList', $structList);
        return $this->fetch();
    }
   
}
