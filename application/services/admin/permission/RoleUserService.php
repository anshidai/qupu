<?php

namespace app\services\admin\permission;

use think\facade\Request;
use app\model\permission\RoleModel;
use app\model\permission\RoleUserModel;
use app\model\permission\RolePermissionModel;
use app\model\permission\PermissionModel;
use app\components\helper\ArrayHelper;
use app\Inc\TableConst;

/**
* 用户角色后台业务处理
*/
class RoleUserService
{
	/**
	* 用户角色集合
	*/
	public static function roleUserPost($userid)
	{
		$newRoleid = Request::post('roleid');
		$newRoleid = explode(',', $newRoleid);

		$data = [];
		if (empty($userid) || !is_numeric($userid) || empty($newRoleid)) {
			return $data;
		}

		$list = RoleUserModel::getListByUserid($userid);
		if (empty($list)) { //还没有添加角色则返回更新角色集合
			$data['adduser'] = $newRoleid;
			$data['deluser'] = [];
		} else { //对比已有权限和更新权限集合
			$oldRoleid = ArrayHelper::getCols($list, 'role_id'); //已有角色

			/** A已有角色 B修改角色 **/
			//A->B 删除角色
			$data['deluser'] = array_diff($oldRoleid, $newRoleid);
			$data['deluser'] = array_values($data['deluser']);

			//B->A 新增角色
			$data['adduser'] = array_diff($newRoleid, $oldRoleid);
			$data['adduser'] = array_values($data['adduser']);
		}

		return $data;
	} 


	/**
	* 修改用户角色
	* @param int $userid 用户id
	* @param int|array $diff 角色集合
	*/
	public static function editRoleUser($userid, $diff)
	{
		if (empty($userid) || !is_numeric($userid) || empty($diff)) {
			return false;
		}

		$adduser = $diff['adduser']? $diff['adduser']: [];
		$deluser = $diff['deluser']? $diff['deluser']: [];

		//删除角色
		if (!empty($deluser)) {
			self::deleRoleUser($userid, $deluser);
		}

		//新增角色
		if (!empty($adduser)) {
			self::addRoleUser($userid, $adduser);
		}

		return true;
	}


	/**
	* 添加用户角色
	* @param int $userid 用户id
	* @param array $roleids 角色限集合
	*/
	public static function addRoleUser($userid, $roleids)
	{
		if (empty($userid) || !is_numeric($userid) || empty($roleids)) {
			return false;
		}

		for ($i = 0; $i < count($roleids); $i++) {
			if (empty($roleids[$i])) {
				continue;
			}
			$add = [
				'userid' => $userid,
				'role_id' => $roleids[$i],
			];
			RoleUserModel::_add($add);
		}
		return true;
	}

	/**
	* 删除角色权限
	* @param int $userid 用户id
	* @param array $roleids 角色限集合
	*/
	public static function deleRoleUser($userid, $roleids)
	{
		if (empty($userid) || !is_numeric($userid) || empty($roleids)) {
			return false;
		}

		foreach ($roleids as $roleId) {
			RoleUserModel::deleteByRole($userid, $roleId);
		}
		return true;
	}

	/**
	* 获取用户关联角色集合
	* @param int $userid 用户id
	*/
	public static function getRoleByUser($userid)
	{
		return RoleUserModel::getListByUserid($userid);
	}

	/**
	* 获取角色权限列表
	* @param int|array $roleids 角色id集合
	* @param string $field 字段
	*/
	public static function getRolePermission($roleids, $field = [])
	{
		$data = [];
		if (empty($roleids)) {
			return $data;
		}

		if (!is_array($roleids)) {
			if (strpos($roleids, ',')) {
				$roleids = explode(',', $roleids);
			} else {
				$roleids = [$roleids];
			}
		}

		$permissionids = RolePermissionModel::getList(['role_id' => ['in', implode(',', $roleids)]]);
		if (empty($permissionids)) {
			return $data;
		}

		$permissionids = ArrayHelper::getCols($permissionids, 'permission_id');
		$map = [
			['status', '=', TableConst::PERMISSION_STATUS_ENABLED],
			['id', 'in', $permissionids],
		];

		$data = PermissionModel::getList($map, 0, 0, $field);

		return $data;
	}

}