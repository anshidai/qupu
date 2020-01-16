<?php

namespace app\admin\controller\songs;

use think\Request;
use app\admin\controller\Admin;
use think\facade\Request as Requests;
use app\components\COM;
use app\Inc\TableConst;
use app\services\admin\category\AmCategoryService;
use app\services\category\BUCategoryService;
use app\model\category\CategoryModel;
use app\model\songs\AuthorModel;
use app\model\songs\AuthorDataModel;

/**
* 作者
*/
class Author extends Admin
{
	protected $pagesize = 15;
    protected static $cateList = [];
    protected static $cateTree = [];

	public function initialize() 
    {
        parent::initialize();

        self::$cateList = BUCategoryService::getCateAll(TableConst::CATE_TYPE_AUTHOR);
        $this->assign('cateList', self::$cateList);

        self::$cateTree = BUCategoryService::getCateTree(self::$cateList);
        self::$cateTree = BUCategoryService::parseCateTree(self::$cateTree);
        $this->assign('cateTree', self::$cateTree);
    }

    /**
	* 列表
    */
    public function lists(Request $request)
    {
        $this->checkPermis('author_list');

        $keyword = $request->get('keyword', '', 'trim');
        $page = $request->get('page', 1, 'intval');
        $pagesize = $request->get('pagesize', $this->pagesize, 'intval');
        $status = $request->get('status', 0, 'intval');
        $push = $request->get('push', '', 'trim');
        $sdate = $request->param('sdate', '', 'trim');
        $edate = $request->param('edate', '', 'trim');

        $map = [];
        if (!empty($keyword)) {
            if (is_numeric($keyword)) {
                $map[] = ['id', '=', $keyword];
            } elseif (strlen($keyword) > 10 && preg_match('/[a-zA-Z0-9]+/', $keyword)) {
                $map[] = ['identify', '=', $keyword];
            } else {
                $map[] = ['name', 'like', "%{$keyword}%"];
            }
        }

        if ($cid) {
            $subCateIds = BUCategoryService::getCateIdsByParentId($cid);
            if (empty($subCateIds)) {
                $subCateIds = [$cid];
            }
            array_push($map, ['catid', 'in', $subCateIds]);
        }

        if ($status) {
            array_push($map, ['status', '=', $status]);
        } else {
            array_push($map, ['status', 'in', [TableConst::AUTHOR_STATUS_DEFAULT,TableConst::AUTHOR_STATUS_NOT, TableConst::AUTHOR_STATUS_PASS]]);
        }

        if ($push != '') {
            array_push($map, ['is_show', '=', $push]);
        }

        if ($sdate && $edate) {
            array_push($map, ['edittime', '>=', $sdate.' 00:00:00']);
            array_push($map, ['edittime', '<=', $edate. ' 23:59:59']);
        }

        $field = ['id','name','identify','avatar','status','is_show','ctype','addtime','edittime'];
        $total = QupuModel::getTotal($map);
        $list = QupuModel::getList($map, $page, $pagesize, $field, 'id desc');

        $requestUrl = $request->url(true);
        $pages = PublicService::showPages($requestUrl, $total, $page, $pagesize);
        
        $this->assign('total', $total);
        $this->assign('list', $list);
        $this->assign('keyword', $keyword);
        $this->assign('pages', $pages);
        $this->assign('status', $status);
        $this->assign('push', $push);
        $this->assign('sdate', $sdate);
        $this->assign('edate', $edate);
        return $this->fetch();
    }



}