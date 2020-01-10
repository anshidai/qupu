<?php

namespace app\services\admin\page;

use think\facade\Request;
use think\facade\Cache;
use app\components\COM;
use app\components\helper\ArrayHelper;
use app\model\permission\AdminUserModel;
use app\model\permission\PermissionModel;
use app\model\permission\RoleModel;
use app\model\permission\RolePermissionModel;
use app\model\permission\RoleUserModel;
use app\Inc\TableConst;

/**
* 管理员后台业务处理
*/
class AdminUserService
{
	/**
	* 表单数据 param, post, get
	*/
	public static function adminUserPost()
	{	
		$data['nickname'] = Request::post('nickname', ''); //昵称
		$data['sex'] = Request::post('sex', 0, 'intval'); //性别 

		$data['mobile'] = Request::post('mobile', ''); //手机号
		$data['avatar'] = Request::post('avatar', ''); //头像
		$data['email'] = Request::post('email', ''); //邮箱
		$data['remark'] = Request::post('remark', ''); //备注

		$pwd = Request::post('pwd');
		$repwd = Request::post('repwd');
		if (!empty($pwd)) {
			if (!checkPwd($pwd)) {
				throw new \Exception("密码长度6-16位字符");
			}
			if ($pwd != $repwd) {
				throw new \Exception("两次密码输入不一致");
			}

			$data['salt'] = createSalt();
			$data['pwd'] = createPassword($pwd, $data['salt']);
			$data['login_error_num'] = 0;
		}

        return $data;
	}	

	/**
	* 校验role
	*/
	public static function checkRolePost()
	{
		$sysadmin = Request::post('sys_admin', 0, 'intval'); //是否管理员
		$roleid = Request::post('roleid'); //角色id

		if (!empty($sysadmin) && !empty($roleid)) {
			throw new \Exception("勾选管理员 角色不用选择");
		}

		return $sysadmin;
	}

	/**
	* 校验用户名是否存在
	* @param string $name 用户名
	* @param int $userid 排除用户id
	*/
	public static function checkUserExist($name, $userid = 0)
	{
		if (empty($name)) {
			throw new \Exception("用户名不能为空"); 
		}

		if (AdminUserModel::checkRowExist($name, $userid)) {
            throw new \Exception("用户名已经存在"); 
        }

        return true;
	}

	/**
	* 根据用户名获取信息
	* @param string $name 用户名
	*/
	public static function getUserByName($name)
	{
		if (empty($name)) {
			return [];
		}
		$map = [
			'name' => $name,
		];

		return AdminUserModel::getInfoByMap($map);
	}

	/**
	* 根据用户id获取角色
	* @param int $userid 用户id
	*/
	public static function getUserRoles($userid)
	{
		$data = [];
		if (empty($userid) || !is_numeric($userid)) {
			return $data;
		}

		$res = RoleUserModel::getList(['userid' => $userid]);
		if (empty($res)) {
			return $data;
		}

		$roleids = ArrayHelper::getCols($res, 'role_id');

		return $roleids;
	}


	/**
	* 获取用户角色和权限
	* @param int $userid 用户id
	*/
	public static function getRolePermis($userid)
	{
		$data = [];
		if (empty($userid) || !is_numeric($userid)) {
			return $data;
		}

		$cacheKey =  COM::getCachekey('admin_user_permis', $userid);
		$data = Cache::get($cacheKey);
		if (empty($data)) {
			$userInfo = AdminUserModel::getInfo($userid);
			if (empty($userInfo)) {
				return $data;
			}

			$field = ['id,name,url,parentid,identify,ctype,ico,`order`'];
			if ($userInfo['sys_admin'] == TableConst::SYS_ADMIN) { //管理员角色
				$map = [
					'status' => TableConst::PERMISSION_STATUS_ENABLED,
				];
				$permis = PermissionModel::getList($map, 0, 0, $field);
			} else { //其他角色

				//获取角色id集合
				$roleids = self::getUserRoles($userid);
				if (empty($roleids)) {
					return $data;
				}	
				$permis = BUAmRoleUser::getRolePermission($roleids, $field);
			}

			$data['sys_admin'] = ($userInfo['sys_admin'] == TableConst::SYS_ADMIN)? 1: 0;
			$data['permis'] = $permis;

			Cache::set($cacheKey, json_encode($data), 3600);
		} else {
			$data = json_decode($data, true);
		}

		return $data;
	}

	/**
	* 获取用户关联角色
	* @param int $userid 用户id
	*/
	public static function getUserRoleList($userid)
	{
		$data = [];
		$roleids = self::getUserRoles($userid);
		if (empty($roleids)) {
			return $data;
		}

		$map = [
			'id' => ['in', implode(',', $roleids)],
		];

		return RoleModel::getList($map);
	}

	/**
    * 页面权限判断
    * @param array $permission 权限集合
    * @param string $identify 权限标记
    * @return bool true能访问  false不能访问
    */
	public static function pageVerifyPermis($permisArr, $identify)
    {
        if ($permisArr['is_admin']) {
            return true;
        }

        if (empty($permisArr['permis']) || !is_array($permisArr['permis']) || empty($identify)) {
            return false;
        }

        $halt = false;
        foreach ($permisArr['permis'] as $val) {
            if ($val['identify'] == $identify) {
                $halt = true;
                break;
            }
        }

        return $halt;
    }


}
