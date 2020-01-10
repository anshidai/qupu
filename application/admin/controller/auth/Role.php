<?php

namespace app\admin\controller\auth;

use think\Request;
use app\admin\controller\Admin;
use app\model\permission\RoleModel;
use app\services\admin\common\PublicService;
use app\services\admin\permission\RoleService;
use app\services\admin\permission\PermissionService;
use app\services\admin\logs\ActLogService;
use app\components\helper\ArrayHelper;
use app\Inc\TableConst;

/**
* 角色
*/
class Role extends Admin
{
	protected $pagesize = 15;

	public function initialize() 
    {
        parent::initialize();

    }

    public function lists(Request $request)
    {
        //判断页面访问权限
        if (!self::$sysadmin && empty(self::$permis['role_list'])) {
            if ($this->isPost() || $this->isAjax()) {
                return $this->jsonError('抱歉没有访问权限');
            } 
            return $this->fetch('public/notpermission');
        }

        $page = $request->get('page', 1, 'intval');
        $pagesize = $request->get('pagesize', $this->pagesize, 'intval');

        $total = RoleModel::getTotal($map);
        $list = RoleModel::getList($map, $page, $pagesize, $field);

        $requestUrl = $request->url(true);
        $pages = PublicService::showPages($requestUrl, $total, $page, $pagesize);

        $this->assign('total', $total);
        $this->assign('list', $list);
        $this->assign('pages', $pages);

    	return $this->fetch();
    }

    /**
    * 角色授权
    */
    public function permis(Request $request)
    {
        //判断页面访问权限
        if (!self::$sysadmin && empty(self::$permis['role_permis'])) {
            if ($this->isPost() || $this->isAjax()) {
                return $this->jsonError('抱歉没有访问权限');
            } 
            return $this->fetch('public/notpermission');
        }

        $id = $request->param('id');

        $info = RoleModel::getInfo($id);
        if ($this->isPost()) {
            try {
                $permis = RoleService::rolePermisPost();
                RoleService::editRolePermis($id, $permis);

            } catch(\Exception $e) {
                return $this->jsonError($e->getMessage());
            }

            //添加日志
            ActLogService::addLog($id, TableConst::ACTLOG_ROLE, '角色授权', $_REQUEST);

            return $this->JsonSuccess([], '操作成功');
        }

        $permis = PermissionService::getPermisList();
        $permisTree = PermissionService::getPermisTree($permis);

        $permisArr = RoleService::getRolePermission($id);
        $permisArr = ArrayHelper::toHashmap($permisArr, 'id'); //已有权限

        $this->assign('info', $info);
        $this->assign('permisTree', $permisTree);
        $this->assign('permisArr', $permisArr);
        return $this->fetch();
    }

    /**
	* 添加角色
    */
    public function add(Request $request)
    {
        //判断页面访问权限
        if (!self::$sysadmin && empty(self::$permis['role_add'])) {
            if ($this->isPost() || $this->isAjax()) {
                return $this->jsonError('抱歉没有访问权限');
            } 
            return $this->fetch('public/notpermission');
        }

        if ($this->isPost()) {
            try {
                $data = RoleService::rolePost();
                $data['status'] = TableConst::ROLE_STATUS_ENABLED;
                
                RoleService::checkExist($data['name']);
                $insertId = RoleModel::_add($data);

                //添加日志
                ActLogService::addLog($insertId, TableConst::ACTLOG_ROLE, '添加角色', $_REQUEST);

            } catch(\Exception $e) {
                return $this->jsonError($e->getMessage());
            }

            return $this->JsonSuccess([], '操作成功');
        }

        return $this->fetch();
    }

    /**
	* 编辑角色
    */
    public function edit(Request $request)
    {
        //判断页面访问权限
        if (!self::$sysadmin && empty(self::$permis['role_edit'])) {
            if ($this->isPost() || $this->isAjax()) {
                return $this->jsonError('抱歉没有访问权限');
            } 
            return $this->fetch('public/notpermission');
        }

        $id = $request->param('id');

        try {
            $info = RoleModel::getInfo($id);
            if (empty($info)) {
                throw new \Exception("角色记录不存在"); 
            }

            if ($this->isPost()) {
                $id = $request->post('id');
                if ($info['id'] != $id) {
                    throw new \Exception("请求ID和提交ID不一致");
                }

                $data = RoleService::rolePost();
                $data['edittime'] = date('Y-m-d H:i:s');
                RoleService::checkExist($data['name'], $id);
                RoleModel::_update($id, $data);

                //添加日志
                ActLogService::addLog($id, TableConst::ACTLOG_ROLE, '编辑角色', $_REQUEST);

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
        if (!self::$sysadmin && empty(self::$permis['role_close'])) {
            if ($this->isPost() || $this->isAjax()) {
                return $this->jsonError('抱歉没有访问权限');
            } 
            return $this->fetch('public/notpermission');
        }

        $id = $request->get('id', 0, 'intval');

        try {
            if (empty($id) || !is_numeric($id)) {
                throw new \Exception("非法ID请求"); 
            }

            $info = RoleModel::getInfo($id);
            if (empty($info)) {
                throw new \Exception("请求记录不存在"); 
            }

            if ($info['status'] == TableConst::ROLE_STATUS_DISABLED) {
                throw new \Exception("已是禁用状态,请勿重复操作"); 
            }

            RoleModel::_update($id, ['status' => TableConst::ROLE_STATUS_DISABLED]);

            //添加日志
            ActLogService::addLog($id, TableConst::ACTLOG_ROLE, '禁用角色', $_REQUEST);

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
        if (!self::$sysadmin && empty(self::$permis['role_open'])) {
            if ($this->isPost() || $this->isAjax()) {
                return $this->jsonError('抱歉没有访问权限');
            } 
            return $this->fetch('public/notpermission');
        }
        
        $id = $request->get('id', 0, 'intval');

        try {
            if (empty($id) || !is_numeric($id)) {
                throw new \Exception("非法ID请求"); 
            }

            $info = RoleModel::getInfo($id);
            if (empty($info)) {
                throw new \Exception("请求记录不存在"); 
            }

            if ($info['status'] == TableConst::ROLE_STATUS_ENABLED) {
                throw new \Exception("已是启用状态,请勿重复操作"); 
            }

            RoleModel::_update($id, ['status' => TableConst::ROLE_STATUS_ENABLED]);

            //添加日志
            ActLogService::addLog($id, TableConst::ACTLOG_ROLE, '启用角色', $_REQUEST);

            return $this->JsonSuccess([], '操作成功');
            
        } catch(\Exception $e) {
            return $this->jsonError($e->getMessage());
        }
    }




}