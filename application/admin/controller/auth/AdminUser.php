<?php

namespace app\admin\controller\auth;

use think\Request;
use app\admin\controller\Admin;
use app\components\helper\ArrayHelper;
use app\model\permission\AdminUserModel;
use app\model\common\ConfigModel;
use app\services\admin\page\AdminUserService;
use app\services\admin\common\PublicService;
use app\services\admin\permission\RoleUserService;
use app\services\admin\permission\RoleService;
use app\services\admin\logs\ActLogService;
use app\Inc\TableConst;

/**
* 管理员
*/
class AdminUser extends Admin
{
	protected $pagesize = 15;

    public function initialize() 
    {
        parent::initialize();
    }

    /**
	* 管理员列表
    */
    public function lists(Request $request)
    {
        //判断页面访问权限
        if (!self::$sysadmin && empty(self::$permis['adminuser_list'])) {
            if ($this->isPost() || $this->isAjax()) {
                return $this->jsonError('抱歉没有访问权限');
            } 
            return $this->fetch('public/notpermission');
        }
        
        $sname = $request->get('sname');
        $page = $request->get('page', 1, 'intval');
        $pagesize = $request->get('pagesize', 10, 'intval');

        $map = $field = [];
        if (!empty($sname)) {
            $map[] = ['name', 'like', "%{$sname}%"];
        }

        $total = AdminUserModel::getTotal($map);
        $list = AdminUserModel::getList($map, $page, $pagesize, $field);
        foreach ($list as &$val) {
            $val['role_name'] = '';
            $userRole = AdminUserService::getUserRoleList($val['id']);
            if (!empty($userRole)) {
                $userRole = ArrayHelper::getCols($userRole, 'name');
                $val['role_name'] = implode(',', $userRole);
            }
        }

        $requestUrl = $request->url(true);
        $pages = PublicService::showPages($requestUrl, $total, $page, $pagesize);

        $this->assign('sname', $sname);
        $this->assign('total', $total);
        $this->assign('list', $list);
        $this->assign('pages', $pages);
        return $this->fetch();
    }

    /**
	* 添加管理员
    */
    public function add(Request $request)
    {
        //判断页面访问权限
        if (!self::$sysadmin && empty(self::$permis['adminuser_add'])) {
            if ($this->isPost() || $this->isAjax()) {
                return $this->jsonError('抱歉没有访问权限');
            } 
            return $this->fetch('public/notpermission');
        }

        $userName = $request->post('name');

    	if ($this->isPost()) {
            try {
            	
                if(empty($userName)) {
					throw new \Exception("用户不能为空"); 
				}

                $configInfo = ConfigModel::getInfoByName('register_admin_filter');
                if (!empty($configInfo)) {
                    $userFilter = explode(',', $configInfo['kval']);
                    if (in_array($userName, $userFilter)) {
                        throw new \Exception("当前用户名禁止注册"); 
                    }
                }

                $data = AdminUserService::adminUserPost();
                if (empty($data['pwd'])) {
                	throw new \Exception("请输入密码");
                }

                $data['name'] = $userName;
                $data['lasttime'] = date('Y-m-d H:i:s');
                $data['status'] = TableConst::ADMIN_STATUS_ENABLED;
				$data['lastip'] = $request->ip();
				$data['regip'] = $request->ip();

                AdminUserService::checkUserExist($data['name']);
                $insertId = AdminUserModel::_add($data);

                //添加日志
                ActLogService::addLog($insertId, TableConst::ACTLOG_USER, '新增用户', $_REQUEST);

            } catch(\Exception $e) {
                return $this->jsonError($e->getMessage());
            }

            return $this->JsonSuccess([], '操作成功');
        }

        return $this->fetch();
    }

    /**
	* 编辑管理员
    */
    public function edit(Request $request)
    {
        //判断页面访问权限
        if (!self::$sysadmin && empty(self::$permis['adminuser_edit'])) {
            if ($this->isPost() || $this->isAjax()) {
                return $this->jsonError('抱歉没有访问权限');
            } 
            return $this->fetch('public/notpermission');
        }

        $userid = $request->param('id');

        $info = AdminUserModel::getInfo($userid);
        try {
            
            if (empty($info)) {
                throw new \Exception("用户记录不存在"); 
            }

            if ($this->isPost()) {
                $id = $request->post('id');
                if ($info['id'] != $id) {
                    throw new \Exception("请求ID和提交ID不一致");
                }

                $data = AdminUserService::adminUserPost();
                $data['edittime'] = date('Y-m-d H:i:s');

                AdminUserModel::_update($userid, $data);

                //添加日志
                ActLogService::addLog($userid, TableConst::ACTLOG_USER, '编辑用户', $_REQUEST);

                return $this->JsonSuccess([], '操作成功');
            }

        } catch(\Exception $e) {
            return $this->jsonError($e->getMessage());
        }

        $this->assign('info', $info);
        return $this->fetch();
    }

    /**
    * 授权
    */
    public function permis(Request $request)
    {
        //判断页面访问权限
        if (!self::$sysadmin && empty(self::$permis['adminuser_permis'])) {
            if ($this->isPost() || $this->isAjax()) {
                return $this->jsonError('抱歉没有访问权限');
            } 
            return $this->fetch('public/notpermission');
        }

        $userid = $request->param('id', 0, 'intval');

        $info = AdminUserModel::getInfo($userid);
        try {
            if (empty($info)) {
                throw new \Exception("用户记录不存在"); 
            }
            if ($this->isPost()) {
                $id = $request->post('id');
                if ($info['id'] != $id) {
                    throw new \Exception("请求用户ID和提交ID不一致");
                }
                //校验表单
                $sysadmin = AdminUserService::checkRolePost();

                $data = RoleUserService::roleUserPost($userid);
                RoleUserService::editRoleUser($userid, $data);

                AdminUserModel::_update($userid, ['sys_admin' => $sysadmin]);

                //添加日志
                ActLogService::addLog($userid, TableConst::ACTLOG_USER, '用户授权', $_REQUEST);
                return $this->JsonSuccess([], '操作成功');
            }

        } catch(\Exception $e) {
            return $this->jsonError($e->getMessage());
        }

        $roleList = RoleService::getRole();
        $roleUser = RoleUserService::getRoleByUser($userid);
        $roleUser = ArrayHelper::toHashmap($roleUser, 'role_id');

        $this->assign('info', $info);
        $this->assign('roleList', $roleList);
        $this->assign('roleUser', $roleUser);
        return $this->fetch();
    }


    /**
    * 修改密码
    */
    public function changepwd(Request $request)
    {
        $pwd = $request->post('pwd');
        $repwd = $request->post('repwd');

        $info = AdminUserModel::getInfo(self::$userid);

        try {
            if ($this->isPost()) {
                if (empty($info)) {
                    throw new \Exception("用户记录不存在"); 
                }
                if (empty($pwd)) {
                    throw new \Exception("密码不能为空"); 
                }
                if (!checkPwd($pwd)) {
                    throw new \Exception("密码长度6-16位字符");
                }
                if ($pwd != $repwd) {
                    throw new \Exception("两次密码输入不一致");
                }

                $salt = createSalt();
                $data = [
                    'salt' => $salt,
                    'pwd' => createPassword($pwd, $salt),
                    'login_error_num' => 0,
                ];
                if (AdminUserModel::_update(self::$userid, $data)) {
                    //添加日志
                    ActLogService::addLog(self::$userid, TableConst::ACTLOG_USER, '用户修改密码', $_REQUEST);

                    return $this->JsonSuccess([], '操作成功，请重新登录', '/page/logout');
                } else {
                    return $this->jsonError('操作失败');
                }
            }

        } catch(\Exception $e) {
            return $this->jsonError($e->getMessage());
        }

        $this->assign('info', $info);
        return $this->fetch();
    }

    /**
    * 删除操作
    */
    public function changeDel(Request $request)
    {
        //判断页面访问权限
        if (!self::$sysadmin && empty(self::$permis['adminuser_del'])) {
            if ($this->isPost() || $this->isAjax()) {
                return $this->jsonError('抱歉没有访问权限');
            } 
            return $this->fetch('public/notpermission');
        }

        $userid = $request->get('id', 0, 'intval');

        try {
            if (empty($userid) || !is_numeric($userid)) {
                throw new \Exception("非法ID请求"); 
            }

            $info = AdminUserModel::getInfo($userid);
            if (empty($info)) {
                throw new \Exception("请求记录不存在"); 
            }

            if ($info['status'] == TableConst::ADMIN_STATUS_DEL) {
                throw new \Exception("已是删除状态,请勿重复操作"); 
            }

            AdminUserModel::_update($userid, ['status' => TableConst::ADMIN_STATUS_DEL]);

            //添加日志
            ActLogService::addLog($userid, TableConst::ACTLOG_USER, '删除用户', $_REQUEST);

            return $this->JsonSuccess([], '操作成功');
            
        } catch(\Exception $e) {
            return $this->jsonError($e->getMessage());
        }
    }

    /**
    * 禁用操作
    */
    public function changeClose(Request $request)
    {
        //判断页面访问权限
        if (!self::$sysadmin && empty(self::$permis['adminuser_close'])) {
            if ($this->isPost() || $this->isAjax()) {
                return $this->jsonError('抱歉没有访问权限');
            } 
            return $this->fetch('public/notpermission');
        }

        $userid = $request->get('id', 0, 'intval');

        try {
            if (empty($userid) || !is_numeric($userid)) {
                throw new \Exception("非法ID请求"); 
            }

            $info = AdminUserModel::getInfo($userid);
            if (empty($info)) {
                throw new \Exception("请求记录不存在"); 
            }

            if ($info['status'] == TableConst::ADMIN_STATUS_DEFAULT) {
                throw new \Exception("已是禁用状态,请勿重复操作"); 
            }

            AdminUserModel::_update($userid, ['status' => TableConst::ADMIN_STATUS_DEFAULT]);

            //添加日志
            ActLogService::addLog($userid, TableConst::ACTLOG_USER, '禁用用户', $_REQUEST);

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
        if (!self::$sysadmin && empty(self::$permis['adminuser_open'])) {
            if ($this->isPost() || $this->isAjax()) {
                return $this->jsonError('抱歉没有访问权限');
            } 
            return $this->fetch('public/notpermission');
        }
        
        $userid = $request->get('id', 0, 'intval');

        try {
            if (empty($userid) || !is_numeric($userid)) {
                throw new \Exception("非法ID请求"); 
            }

            $info = AdminUserModel::getInfo($userid);
            if (empty($info)) {
                throw new \Exception("请求记录不存在"); 
            }

            if ($info['status'] == TableConst::ADMIN_STATUS_ENABLED) {
                throw new \Exception("已是启用状态,请勿重复操作"); 
            }

            AdminUserModel::_update($userid, ['status' => TableConst::ADMIN_STATUS_ENABLED]);

            //添加日志
            ActLogService::addLog($userid, TableConst::ACTLOG_USER, '启用用户', $_REQUEST);

            return $this->JsonSuccess([], '操作成功');
            
        } catch(\Exception $e) {
            return $this->jsonError($e->getMessage());
        }
    }



}
