<?php

namespace app\mobile\controller;

use think\Request;
use app\components\helper\ArrayHelper;
use app\components\helper\StringHelper;
use app\services\article\BUArticleService;
use app\services\common\BUCom;
use app\components\Urls;
use app\Inc\TableConst;

class Search extends Base
{
    protected $pagesize = 30;

    public function formSearch(Request $request)
    {
        $keyword = $request->param('keyword', '');
        $keyword = urldecode($keyword);

        $list = BUArticleService::getSearchByKeyword($keyword, $this->pagesize);

        $this->assign('keyword', $keyword);
        $this->assign('list', $list);
        return $this->fetch();
    }


   
}
