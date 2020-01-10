<?php

namespace app\admin\controller\category;

use think\Request;
use app\admin\controller\Admin;
use think\facade\Request as Requests;
use app\components\COM;
use app\Inc\TableConst;
use app\services\admin\category\CategoryService;
use app\services\category\BUCategoryService;
use app\model\category\CategoryModel;
use app\services\admin\logs\ActLogService;

/**
* 分类
*/
class Category extends Admin
{
	protected static $cateList = [];
    protected static $cateTree = [];
	protected static $cateTypeList = [];
    protected $ctype = '';

	public function initialize() 
    {
        parent::initialize();

        $this->ctype = Requests::get('ctype');
        $this->assign('ctype', $this->ctype);

        self::$cateList = BUCategoryService::getCateAll($this->ctype);
        $this->assign('cateList', self::$cateList);

        self::$cateTree = BUCategoryService::getCateTree(self::$cateList);
        self::$cateTree = BUCategoryService::parseCateTree(self::$cateTree);
        $this->assign('cateTree', self::$cateTree);

        self::$cateTypeList = TableConst::$cateTypeList;
        $this->assign('cateTypeList', self::$cateTypeList);
    }


	public function lists(Request $request)
	{  
        //判断页面访问权限
        if (!self::$sysadmin && empty(self::$permis['cate_list'])) {
            if ($this->isPost() || $this->isAjax()) {
                return $this->jsonError('抱歉没有访问权限');
            } 
            return $this->fetch('public/notpermission');
        }

		return $this->fetch();
	}

	public function add(Request $request)
	{
        //判断页面访问权限
        if (!self::$sysadmin && empty(self::$permis['cate_add'])) {
            if ($this->isPost() || $this->isAjax()) {
                return $this->jsonError('抱歉没有访问权限');
            } 
            return $this->fetch('public/notpermission');
        }

        $pid = $request->param('pid');
        $ctype = $request->param('ctype');

		if ($this->isPost()) {
            try {
                $data = CategoryService::catePost();
                $data['status'] = TableConst::CATE_STATUS_ENABLED;

                CategoryService::checkCateExist($data['name'], $data['parentid']);
                CategoryService::checkRowExistByPinyin($data['pinyin']);
                if ($insertId = CategoryModel::_add($data)) {
                    //刷新分类缓存
                    BUCategoryService::getCateAll($ctype, true);

                    $level = CategoryService::getCateLevel($insertId);
                    if ($level) {
                        CategoryModel::_update($insertId, ['level' => $level]);
                    }
                }

            } catch(\Exception $e) {
                return $this->jsonError($e->getMessage());
            }

            return $this->JsonSuccess([], '操作成功');
        }

        $this->assign('pid', $pid);
        $this->assign('ctype', $ctype);
		return $this->fetch();
	}


	/**
	* 编辑程
    */
    public function edit(Request $request)
    {
        //判断页面访问权限
        if (!self::$sysadmin && empty(self::$permis['cate_edit'])) {
            if ($this->isPost() || $this->isAjax()) {
                return $this->jsonError('抱歉没有访问权限');
            } 
            return $this->fetch('public/notpermission');
        }

        $cid = $request->param('id');

        $info = CategoryModel::getInfo($cid);
        try {
            
            if (empty($info)) {
                throw new \Exception("分类记录不存在"); 
            }

            if ($this->isPost()) {
                $id = $request->post('id');
                if ($info['id'] != $id) {
                    throw new \Exception("请求分类ID和提交ID不一致");
                }
                $data = CategoryService::catePost();
                $data['edittime'] = date('Y-m-d H:i:s');
                $data['level'] = CategoryService::getCateLevel($cid);

                CategoryService::checkCateExist($data['name'], $data['parentid'], $cid);
                CategoryService::checkRowExistByPinyin($data['pinyin'], $id);
                CategoryModel::_update($cid, $data);

                //刷新分类缓存
                BUCategoryService::getCateAll($info['ctype'], true);

                return $this->JsonSuccess([], '操作成功');
            }

        } catch(\Exception $e) {
            return $this->jsonError($e->getMessage());
        }

        $this->assign('info', $info);

        return $this->fetch();
    }

    /**
    * 禁用操作
    */
    public function changeClose(Request $request)
    {
        //判断页面访问权限
        if (!self::$sysadmin && empty(self::$permis['cate_close'])) {
            if ($this->isPost() || $this->isAjax()) {
                return $this->jsonError('抱歉没有访问权限');
            } 
            return $this->fetch('public/notpermission');
        }

        $cid = $request->get('id', 0, 'intval');

        try {
            if (empty($cid) || !is_numeric($cid)) {
                throw new \Exception("非法ID请求"); 
            }

            $info = CategoryModel::getInfo($cid);
            if (empty($info)) {
                throw new \Exception("请求记录不存在"); 
            }

            if ($info['status'] == TableConst::CATE_STATUS_DISABLED) {
                throw new \Exception("已是禁用状态,请勿重复操作"); 
            }

            CategoryModel::_update($cid, ['status' => TableConst::CATE_STATUS_DISABLED]);

            //刷新分类缓存
            BUCategoryService::getCateAll($info['ctype'], true);

            return $this->JsonSuccess([], '操作成功');
            
        } catch(\Exception $e) {
            return $this->jsonError($e->getMessage());
        }
    }

    /**
    * 启用操作
    */
    public function changeOpen(Request $request)
    {
        //判断页面访问权限
        if (!self::$sysadmin && empty(self::$permis['cate_open'])) {
            if ($this->isPost() || $this->isAjax()) {
                return $this->jsonError('抱歉没有访问权限');
            } 
            return $this->fetch('public/notpermission');
        }

        $cid = $request->get('id', 0, 'intval');

        try {
            if (empty($cid) || !is_numeric($cid)) {
                throw new \Exception("非法ID请求"); 
            }

            $info = CategoryModel::getInfo($cid);
            if (empty($info)) {
                throw new \Exception("请求记录不存在"); 
            }

            if ($info['status'] == TableConst::CATE_STATUS_ENABLED) {
                throw new \Exception("已是启用状态,请勿重复操作"); 
            }

            CategoryModel::_update($cid, ['status' => TableConst::CATE_STATUS_ENABLED]);

            //刷新分类缓存
            BUCategoryService::getCateAll($info['ctype'], true);

            return $this->JsonSuccess([], '操作成功');
            
        } catch(\Exception $e) {
            return $this->jsonError($e->getMessage());
        }
    }

    /**
    * 修改排序
    */
    public function changeSort(Request $request)
    {
        $cid = $request->get('id', 0, 'intval');
        $sort = $request->get('sort', 0, 'intval');
        
        try {
            if (empty($cid) || !is_numeric($cid)) {
                throw new \Exception("非法ID请求"); 
            }

            $info = CategoryModel::getInfo($cid);
            if (empty($info)) {
                throw new \Exception("请求记录不存在"); 
            }

            CategoryModel::_update($cid, ['order' => $sort]);

            //刷新分类缓存
            BUCategoryService::getCateAll($info['ctype'], true);
            
            return $this->JsonSuccess([], '操作成功');
            
        } catch(\Exception $e) {
            return $this->jsonError($e->getMessage());
        }
    }


	/**
	* 获取一级分类
	*/
	public function parentCate(Request $request)
	{
        $ctype = $request->param('ctype', 0);
		$data['list'] = BUCategoryService::getLevelOneList($ctype);

		return $this->JsonSuccess($data);
	}

    /**
    * 获取一级分类
    */
    public function CateOne(Request $request)
    {
        $ctype = $request->param('ctype', 0);

        $data['list'] = BUCategoryService::getLevelOneList($ctype);

        return $this->JsonSuccess($data);
    }

    /**
    * 获取二级分类
    */
    public function CateTwo(Request $request)
    {
        $cid = $request->get('cid', 0, 'intval');
        $ctype = $request->param('ctype', 0);

        $data['list'] = BUCategoryService::getSubCateList($cid, $ctype);

        return $this->JsonSuccess($data);
    }

    /**
    * 获取三级分类
    */
    public function CateThree(Request $request)
    {
        $cid = $request->get('cid', 0, 'intval');
        $ctype = $request->param('ctype');

        $data['list'] = BUCategoryService::getSubCateList($cid, $ctype);

        return $this->JsonSuccess($data);
    }



}