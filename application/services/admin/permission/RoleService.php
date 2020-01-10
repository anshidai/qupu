<?php

namespace app\services\admin\permission;

use think\facade\Request;
use app\model\permission\RoleModel;
use app\model\permission\RolePermissionModel;
use app\model\permission\PermissionModel;
use app\components\helper\ArrayHelper;
use app\Inc\TableConst;

/**
* 角色后台业务处理
*/
class RoleService
{
	/**
	* 角色表单数据 param, post, get
	*/
	public static function rolePost()
	{
		$data['name'] = Request::post('name');
		$data['remark'] = Request::post('remark');

		if (empty($data['name'])) {
			throw new \Exception("角色名称不能为空"); 
		} 

		return $data;
	}

	/**
	* 表单数据 param, post, get
	*/
	public static function permisPost()
	{	
		$data['roleid'] = Request::post('roleid');
		$data['remark'] = Request::post('remark');

		if (empty($data['roleid'])) {
			throw new \Exception("请选择角色"); 
		} 

		return $data;
	}

	/**
	* 修改角色权限
	* @param int $roleid 角色id
	* @param int|array $diff 权限集合
	*/
	public static function editRolePermis($roleid, $diff)
	{
		if (empty($roleid) || !is_numeric($roleid) || empty($diff)) {
			return false;
		}

		$addpermis = $diff['addpermis']? $diff['addpermis']: [];
		$delpermis = $diff['delpermis']? $diff['delpermis']: [];

		//删除权限
		if (!empty($delpermis)) {
			self::deleRolePermis($roleid, $delpermis);
		}

		//新增权限
		if (!empty($addpermis)) {
			self::addRolePermis($roleid, $addpermis);
		}

		return true;
	}


	/**
	* 添加角色权限
	* @param int $roleid 角色id
	* @param array $permission 权限集合
	*/
	public static function addRolePermis($roleid, $permission)
	{
		if (empty($roleid) || !is_numeric($roleid) || empty($permission)) {
			return false;
		}

		for ($i = 0; $i < count($permission); $i++) {
			if (empty($permission[$i])) {
				continue;
			}
			$add = [
				'role_id' => $roleid,
				'permission_id' => $permission[$i],
			];
			RolePermissionModel::_add($add);
		}
		return true;
	}

	/**
	* 删除角色权限
	* @param int $roleid 角色id
	* @param array $permissions 权限集合
	*/
	public static function deleRolePermis($roleid, $permissions)
	{
		if (empty($roleid) || !is_numeric($roleid) || empty($permissions)) {
			return false;
		}

		foreach ($permissions as $permisId) {
			RolePermissionModel::deleteByPermis($roleid, $permisId);
		}
		return true;
	}

	/**
	* 权限集合
	*/
	public static function rolePermisPost()
	{
		$roleId = Request::post('id');
		$newPermission = Request::param('permis/a'); //解决post可以传数组

		$data = [];
		if (empty($roleId) || !is_numeric($roleId)) {
			return $data;
		}

		$list = RolePermissionModel::getListByRoleId($roleId);
		if (empty($list)) { //还没有添加权限则返回更新权限集合
			$data['addpermis'] = $newPermission;
			$data['delpermis'] = [];
		} else { //对比已有权限和更新权限集合
			$oldPermis = ArrayHelper::getCols($list, 'permission_id'); //已有权限

			/** A已有权限 B修改权限 **/
			//A->B 删除权限
			$data['delpermis'] = array_diff($oldPermis, $newPermission);
			$data['delpermis'] = array_values($data['delpermis']);

			//B->A 新增权限
			$data['addpermis'] = array_diff($newPermission, $oldPermis);
			$data['addpermis'] = array_values($data['addpermis']);
		}

		return $data;
	} 

	/**
	* 获取角色下权限列表
	* @param int $roleId 角色id
	*/
	public static function getRolePermission($roleId, $field = [])
	{
		$data = [];
		if (empty($roleId) || !is_numeric($roleId)) {
			return $data;
		}

		$roleInfo = RoleModel::getInfo($roleId);
		if (empty($roleInfo)) {
			return $data;
		}

		$permis = RolePermissionModel::getList(['role_id' => $roleId]);
		if (!empty($permis)) {
			$permisIds = ArrayHelper::getCols($permis, 'permission_id');
			$map = [
				'id' => $permisIds
			];

			$data = PermissionModel::getList($map);
		}

		return $data;
	}

	/**
	* 获取角色
	*/
	public static function getRole($field = [])
	{
		$map = [
			'status' => TableConst::ROLE_STATUS_ENABLED,
		];

		return RoleModel::getList($map, 0, 0, $field);
	}

	/**
	* 角色名是否存在
	* @param string $name 角色名
	* @param int $id 排除品牌id
	*/
	public static function checkExist($name, $id = 0)
	{
		if (empty($name)) {
			throw new \Exception("角色名称不能为空"); 
		}

		if (RoleModel::checkExist($name, $id)) {
            throw new \Exception("角色名称已经存在"); 
        }

        return true;
	}




}