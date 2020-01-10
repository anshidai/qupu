<?php

namespace app\admin\controller\songs;

use think\Request;
use app\admin\controller\Admin;
use app\components\helper\ArrayHelper;
use app\model\songs\QupuModel;
use app\model\songs\GepuDataModel;
use app\services\admin\logs\ActLogService;

use app\services\category\BUCategoryService;
use app\services\admin\common\PublicService;
use app\services\admin\chengyu\IdiomService;
use app\services\chengyu\BUIdiomService;
use app\Inc\TableConst;
use app\components\Urls;
use app\services\common\BUCom;

/**
* 歌谱
*/
class Gepu extends Admin
{
    protected $pagesize = 15;
    protected static $cateList = [];
    protected static $cateTree = [];

    public function initialize() 
    {
        parent::initialize();

        //类型
        $cType = getEnums('cType');
        $this->assign('cType', $cType);

        self::$cateList = BUCategoryService::getCateAll(TableConst::CATE_TYPE_GEPU);
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
        //判断页面访问权限
        if (!self::$sysadmin && empty(self::$permis['gepu_list'])) {
            if ($this->isPost() || $this->isAjax()) {
                return $this->jsonError('抱歉没有访问权限');
            } 
            return $this->fetch('public/notpermission');
        }

        $sname = $request->get('sname', '', 'trim');
        $page = $request->get('page', 1, 'intval');
        $pagesize = $request->get('pagesize', $this->pagesize, 'intval');
        $cid = $request->get('cid', 0, 'intval');
        $status = $request->get('status', 0, 'intval');
        $push = $request->get('push', '', 'trim');
        $sdate = $request->param('sdate', '', 'trim');
        $edate = $request->param('edate', '', 'trim');

        $map = [];
        if (!empty($sname)) {
            if (is_numeric($sname)) {
                $map[] = ['id', '=', $sname];
            } elseif (strlen($sname) > 10 && preg_match('/[a-zA-Z0-9]+/', $sname)) {
                $map[] = ['identify', '=', $sname];
            } else {
                $map[] = ['name', 'like', "%{$sname}%"];
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
            array_push($map, ['status', 'in', [TableConst::GEPU_STATUS_DEFAULT,TableConst::GEPU_STATUS_NOT, TableConst::GEPU_STATUS_PASS]]);
        }

        if ($push != '') {
            array_push($map, ['is_show', '=', $push]);
        }

        if ($sdate && $edate) {
            array_push($map, ['edittime', '>=', $sdate.' 00:00:00']);
            array_push($map, ['edittime', '<=', $edate. ' 23:59:59']);
        }

        $field = ['id','name','identify','thumb','status','is_show','catid','catname','hits','addtime','edittime','ctype'];
        $total = QupuModel::getTotal($map);
        $list = QupuModel::getList($map, $page, $pagesize, $field, 'id desc');

        $requestUrl = $request->url(true);
        $pages = PublicService::showPages($requestUrl, $total, $page, $pagesize);
        
        $this->assign('total', $total);
        $this->assign('list', $list);
        $this->assign('sname', $sname);
        $this->assign('pages', $pages);
        $this->assign('status', $status);
        $this->assign('push', $push);
        $this->assign('cid', $cid);
        $this->assign('sdate', $sdate);
        $this->assign('edate', $edate);
        return $this->fetch();
    }

    /**
    * 审核列表
    */
    public function auditlist(Request $request)
    {
        //判断页面访问权限
        if (!self::$sysadmin && empty(self::$permis['gepu_auditlist'])) {
            if ($this->isPost() || $this->isAjax()) {
                return $this->jsonError('抱歉没有访问权限');
            } 
            return $this->fetch('public/notpermission');
        }

        $sname = $request->get('sname');
        $page = $request->get('page', 1, 'intval');
        $pagesize = $request->get('pagesize', $this->pagesize, 'intval');
        $cid = $request->get('cid', 0, 'intval');
        $push = $request->get('push', '');

        $map = [
            ['status', 'in', [TableConst::GEPU_STATUS_DEFAULT, TableConst::GEPU_STATUS_NOT]],
        ];
        if (!empty($sname)) {
            if (is_numeric($sname)) {
                array_push($map, ['id', 'in', $sname]);
            } else {
                array_push($map, ['name', 'like', "%{$sname}%"]);
            }
        }

        if ($cid) {
            $subCateIds = BUCategoryService::getCateIdsByParentId($cid);
            if (empty($subCateIds)) {
                $subCateIds = [$cid];
            }
            array_push($map, ['catid', 'in', $subCateIds]);
        }

        if ($push != '') {
            array_push($map, ['is_show', '=', $push]);
        }

        $field = ['id','name','identify','thumb','status','is_show','catid','catname','hits','addtime','edittime'];
        $total = QupuModel::getTotal($map);
        $list = QupuModel::getList($map, $page, $pagesize, $field, 'edittime asc,status asc');

        $requestUrl = $request->url(true);
        $pages = PublicService::showPages($requestUrl, $total, $page, $pagesize);
        
        $this->assign('total', $total);
        $this->assign('list', $list);
        $this->assign('sname', $sname);
        $this->assign('pages', $pages);
        $this->assign('push', $push);
        $this->assign('cid', $cid);
        return $this->fetch();
    }

    /**
	* 添加
    */
    public function add(Request $request)
    {
        //判断页面访问权限
        if (!self::$sysadmin && empty(self::$permis['gepu_add'])) {
            if ($this->isPost() || $this->isAjax()) {
                return $this->jsonError('抱歉没有访问权限');
            } 
            return $this->fetch('public/notpermission');
        }

    	if ($this->isPost()) {
            try {
                $data = IdiomService::idiomPost();
                $content = IdiomService::idiomDataPost();
                       
                IdiomService::checIdiomExist($data['title']);
                $data['identify'] = createUniqid();
                $data['act_uid'] = self::$userid;
                $data['title_hash'] = md5($data['title']);

                if ($insertId = IdiomModel::_add($data)) {
                    $content['idiom_id'] = $insertId;
                    IdiomDataModel::_add($content);
                }
            } catch(\Exception $e) {
                return $this->jsonError($e->getMessage());
            }
            return $this->JsonSuccess([], '操作成功');
        }

        return $this->fetch();
    }

    /**
	* 编辑
    */
    public function edit(Request $request)
    {
        //判断页面访问权限
        if (!self::$sysadmin && empty(self::$permis['gepu_edit'])) {
            if ($this->isPost() || $this->isAjax()) {
                return $this->jsonError('抱歉没有访问权限');
            } 
            return $this->fetch('public/notpermission');
        }

        $idiomid = $request->param('id');

        $info = IdiomModel::getInfo($idiomid);
        try {
            if (empty($info)) {
                throw new \Exception("记录不存在"); 
            }

            if ($this->isPost()) {
                $id = $request->post('id');
                if ($info['id'] != $id) {
                    throw new \Exception("请求ID和提交ID不一致");
                }

                $data = IdiomService::idiomPost();
                $content = IdiomService::idiomDataPost();
                $data['edittime'] = date('Y-m-d H:i:s');
                if (empty($info['identify'])) {
                    $data['identify'] = createUniqid();
                }

                if (empty($info['act_uid'])) {
                    $data['act_uid'] = self::$userid;
                }

                $titleHash =  md5($data['title']);
                if ($info['title_hash'] != $titleHash) {
                    $data['title_hash'] = $titleHash;
                }

                IdiomService::checIdiomExist($data['title'], $idiomid);
                if (IdiomModel::_update($idiomid, $data)) {
                    IdiomDataModel::updateByIdiomid($idiomid, $content);
                }
                return $this->JsonSuccess([], '操作成功');
            }

        } catch(\Exception $e) {
            return $this->jsonError($e->getMessage());
        }
        
        $content = IdiomDataModel::getInfoByIomid($idiomid);
        $content['content'] = BUCom::replaceXmlSymbol($content['content']);
        $info['content'] = $content;

        $this->assign('info', $info);
        return $this->fetch();
    }

    
    /**
    * 删除操作
    */
    public function changeDel(Request $request)
    {
        //判断页面访问权限
        if (!self::$sysadmin && empty(self::$permis['chengyu_del'])) {
            if ($this->isPost() || $this->isAjax()) {
                return $this->jsonError('抱歉没有访问权限');
            } 
            return $this->fetch('public/notpermission');
        }

        $idiomid = $request->get('id', 0, 'intval');

        try {
            if (empty($idiomid) || !is_numeric($idiomid)) {
                throw new \Exception("非法ID请求"); 
            }

            $info = IdiomModel::getInfo($idiomid);
            if (empty($info)) {
                throw new \Exception("请求记录不存在"); 
            }

            if ($info['status'] == TableConst::IDIOM_STATUS_DEL) {
                throw new \Exception("已是删除状态,请勿重复操作"); 
            }

            IdiomModel::_update($idiomid, ['status' => TableConst::IDIOM_STATUS_DEL]);

            return $this->JsonSuccess([], '操作成功');
            
        } catch(\Exception $e) {
            return $this->jsonError($e->getMessage());
        }
    }

    /**
    * 审核通过
    */
    public function changePass(Request $request)
    {
        //判断页面访问权限
        if (!self::$sysadmin && empty(self::$permis['chengyu_pass'])) {
            if ($this->isPost() || $this->isAjax()) {
                return $this->jsonError('抱歉没有访问权限');
            } 
            return $this->fetch('public/notpermission');
        }

        $idiomid = $request->get('id', 0, 'intval');

        try {
            if (empty($idiomid) || !is_numeric($idiomid)) {
                throw new \Exception("非法ID请求"); 
            }

            $info = IdiomModel::getInfo($idiomid);
            if (empty($info)) {
                throw new \Exception("请求记录不存在"); 
            }

            if ($info['status'] == TableConst::IDIOM_STATUS_PASS) {
                throw new \Exception("已是通过状态,请勿重复操作"); 
            }

            IdiomModel::_update($idiomid, ['status' => TableConst::IDIOM_STATUS_PASS]);

            return $this->JsonSuccess([], '操作成功');
            
        } catch(\Exception $e) {
            return $this->jsonError($e->getMessage());
        }
    }

    /**
    * 前台发布
    */
    public function changePush(Request $request)
    {
        //判断页面访问权限
        if (!self::$sysadmin && empty(self::$permis['chengyu_push'])) {
            if ($this->isPost() || $this->isAjax()) {
                return $this->jsonError('抱歉没有访问权限');
            } 
            return $this->fetch('public/notpermission');
        }

        $idiomid = $request->get('id', 0, 'intval');

        try {
            if (empty($idiomid) || !is_numeric($idiomid)) {
                throw new \Exception("非法ID请求"); 
            }

            $info = IdiomModel::getInfo($idiomid);
            if (empty($info)) {
                throw new \Exception("请求记录不存在"); 
            }

            if ($info['is_show'] == TableConst::IDIOM_SHOW_OK) {
                throw new \Exception("已是发布状态,请勿重复操作"); 
            }

            if ($info['status'] != TableConst::IDIOM_STATUS_PASS) {
                throw new \Exception("只有审核通过才能发布"); 
            }

            IdiomModel::_update($idiomid, ['is_show' => TableConst::IDIOM_SHOW_OK, 'edittime'=>date('Y-m-d H:i:s')]);

            return $this->JsonSuccess([], '操作成功');
            
        } catch(\Exception $e) {
            return $this->jsonError($e->getMessage());
        }
    }


}
