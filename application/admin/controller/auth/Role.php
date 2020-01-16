<?php

namespace app\admin\controller\auth;

use think\Request;
use app\admin\controller\Admin;
use app\model\permission\RoleModel;
use app\services\admin\common\AmPublicService;
use app\services\admin\permission\AmRoleService;
use app\services\admin\permission\AmPermissionService;
use app\services\admin\logs\AmActLogService;
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
        $this->checkPermis('role_list');

        $page = $request->get('page', 1, 'intval');
        $pagesize = $request->get('pagesize', $this->pagesize, 'intval');

        $total = RoleModel::getTotal($map);
        $list = RoleModel::getList($map, $page, $pagesize, $field);

        $requestUrl = $request->url(true);
        $pages = AmPublicService::showPages($requestUrl, $total, $page, $pagesize);

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
        $this->checkPermis('role_permis');

        $id = $request->param('id');

        $info = RoleModel::getInfo($id);
        if ($this->isPost()) {
            try {
                $permis = AmRoleService::rolePermisPost();
                AmRoleService::editRolePermis($id, $permis);

            } catch(\Exception $e) {
                return $this->jsonError($e->getMessage());
            }

            //添加日志
            AmActLogService::addLog($id, TableConst::ACTLOG_ROLE, '角色授权', $_REQUEST);

            return $this->JsonSuccess([], '操作成功');
        }

        $permis = AmPermissionService::getPermisList();
        $permisTree = AmPermissionService::getPermisTree($permis);

        $permisArr = AmRoleService::getRolePermission($id);
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
        $this->checkPermis('role_add');

        if ($this->isPost()) {
            try {
                $data = AmRoleService::rolePost();
                $data['status'] = TableConst::ROLE_STATUS_ENABLED;
                
                AmRoleService::checkExist($data['name']);
                $insertId = RoleModel::_add($data);

                //添加日志
                AmActLogService::addLog($insertId, TableConst::ACTLOG_ROLE, '添加角色', $_REQUEST);

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
        $this->checkPermis('role_edit');

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

                $data = AmRoleService::rolePost();
                $data['edittime'] = date('Y-m-d H:i:s');
                AmRoleService::checkExist($data['name'], $id);
                RoleModel::_update($id, $data);

                //添加日志
                AmActLogService::addLog($id, TableConst::ACTLOG_ROLE, '编辑角色', $_REQUEST);

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
        $this->checkPermis('role_close');

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
            AmActLogService::addLog($id, TableConst::ACTLOG_ROLE, '禁用角色', $_REQUEST);

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
        $this->checkPermis('role_open');
        
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
            AmActLogService::addLog($id, TableConst::ACTLOG_ROLE, '启用角色', $_REQUEST);

            return $this->JsonSuccess([], '操作成功');
            
        } catch(\Exception $e) {
            return $this->jsonError($e->getMessage());
        }
    }




}