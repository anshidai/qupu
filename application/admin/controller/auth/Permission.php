<?php

namespace app\admin\controller\auth;

use think\Request;
use app\admin\controller\Admin;
use app\model\permission\PermissionModel;
use app\services\admin\permission\AmPermissionService;
use app\services\admin\logs\AmActLogService;
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

        $permis = AmPermissionService::getPermisList();
		self::$permisTree = AmPermissionService::getPermisTree($permis);
		self::$permisTree = AmPermissionService::parsePermisTree(self::$permisTree);

		$this->assign('permisTree', self::$permisTree);
    }

    public function lists(Request $request)
    {
        $this->checkPermis('permis_list');

    	return $this->fetch();
    }


    /**
	* 添加权限
    */
    public function add(Request $request)
    {
        $this->checkPermis('permis_add');

        $pid = $request->param('pid');

        if ($this->isPost()) {
            try {
                $data = AmPermissionService::permissionPost();
                $data['status'] = TableConst::PERMISSION_STATUS_ENABLED;
                
                AmPermissionService::checkIdentifyExist($data['identify']);
                $insertId = PermissionModel::_add($data);

                //添加日志
                AmActLogService::addLog($insertId, TableConst::ACTLOG_PERMIS, '添加权限', $_REQUEST);

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
        $this->checkPermis('permis_edit');

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

                $data = AmPermissionService::permissionPost();
                $data['edittime'] = date('Y-m-d H:i:s');
                AmPermissionService::checkIdentifyExist($data['identify'], $id);
                PermissionModel::_update($id, $data);

                //添加日志
                AmActLogService::addLog($id, TableConst::ACTLOG_PERMIS, '编辑权限', $_REQUEST);

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
        $this->checkPermis('permis_close');

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
            AmActLogService::addLog($id, TableConst::ACTLOG_PERMIS, '禁用权限', $_REQUEST);

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
        $this->checkPermis('permis_open');
        
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
            AmActLogService::addLog($id, TableConst::ACTLOG_PERMIS, '启用权限', $_REQUEST);

            return $this->JsonSuccess([], '操作成功');
            
        } catch(\Exception $e) {
            return $this->jsonError($e->getMessage());
        }
    }


}