<?php

namespace app\admin\controller\auth;

use think\Request;
use app\admin\controller\Admin;
use app\model\permission\PermissionModel;
use app\services\admin\permission\PermissionService;
use app\services\admin\logs\ActLogService;
use app\Inc\TableConst;

/**
* 权限
*/
class Permission extends Admin
{
	protected static $permisTree = null;

	public function initialize() 
    {
        parent::initialize();

        $permis = PermissionService::getPermisList();
		self::$permisTree = PermissionService::getPermisTree($permis);
		self::$permisTree = PermissionService::parsePermisTree(self::$permisTree);

		$this->assign('permisTree', self::$permisTree);
    }

    public function lists(Request $request)
    {
        //判断页面访问权限
        if (!self::$sysadmin && empty(self::$permis['permis_list'])) {
            if ($this->isPost() || $this->isAjax()) {
                return $this->jsonError('抱歉没有访问权限');
            } 
            return $this->fetch('public/notpermission');
        }

    	return $this->fetch();
    }


    /**
	* 添加权限
    */
    public function add(Request $request)
    {
        //判断页面访问权限
        if (!self::$sysadmin && empty(self::$permis['permis_add'])) {
            if ($this->isPost() || $this->isAjax()) {
                return $this->jsonError('抱歉没有访问权限');
            } 
            return $this->fetch('public/notpermission');
        }

        $pid = $request->param('pid');

        if ($this->isPost()) {
            try {
                $data = PermissionService::permissionPost();
                $data['status'] = TableConst::PERMISSION_STATUS_ENABLED;
                
                PermissionService::checkIdentifyExist($data['identify']);
                $insertId = PermissionModel::_add($data);

                //添加日志
                ActLogService::addLog($insertId, TableConst::ACTLOG_PERMIS, '添加权限', $_REQUEST);

            } catch(\Exception $e) {
                return $this->jsonError($e->getMessage());
            }

            return $this->JsonSuccess([], '操作成功');
        }

        $this->assign('pid', $pid);

        return $this->fetch();
    }

    /**
	* 编辑权限
    */
    public function edit(Request $request)
    {
        //判断页面访问权限
        if (!self::$sysadmin && empty(self::$permis['permis_edit'])) {
            if ($this->isPost() || $this->isAjax()) {
                return $this->jsonError('抱歉没有访问权限');
            } 
            return $this->fetch('public/notpermission');
        }

        $id = $request->param('id');

        try {
            $info = PermissionModel::getInfo($id);
            if (empty($info)) {
                throw new \Exception("权限记录不存在"); 
            }

            if ($this->isPost()) {
                $id = $request->post('id');
                if ($info['id'] != $id) {
                    throw new \Exception("请求权限ID和提交ID不一致");
                }

                $data = PermissionService::permissionPost();
                $data['edittime'] = date('Y-m-d H:i:s');
                PermissionService::checkIdentifyExist($data['identify'], $id);
                PermissionModel::_update($id, $data);

                //添加日志
                ActLogService::addLog($id, TableConst::ACTLOG_PERMIS, '编辑权限', $_REQUEST);

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
        if (!self::$sysadmin && empty(self::$permis['permis_close'])) {
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

            $info = PermissionModel::getInfo($id);
            if (empty($info)) {
                throw new \Exception("请求记录不存在"); 
            }

            if ($info['status'] == TableConst::PERMISSION_STATUS_DISABLED) {
                throw new \Exception("已是禁用状态,请勿重复操作"); 
            }

            PermissionModel::_update($id, ['status' => TableConst::PERMISSION_STATUS_DISABLED]);

            //添加日志
            ActLogService::addLog($id, TableConst::ACTLOG_PERMIS, '禁用权限', $_REQUEST);

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
        if (!self::$sysadmin && empty(self::$permis['permis_open'])) {
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

            $info = PermissionModel::getInfo($id);
            if (empty($info)) {
                throw new \Exception("请求记录不存在"); 
            }

            if ($info['status'] == TableConst::PERMISSION_STATUS_ENABLED) {
                throw new \Exception("已是启用状态,请勿重复操作"); 
            }

            PermissionModel::_update($id, ['status' => TableConst::PERMISSION_STATUS_ENABLED]);

            //添加日志
            ActLogService::addLog($id, TableConst::ACTLOG_PERMIS, '启用权限', $_REQUEST);

            return $this->JsonSuccess([], '操作成功');
            
        } catch(\Exception $e) {
            return $this->jsonError($e->getMessage());
        }
    }


}