<?php

namespace app\model\permission;

use think\Request;
use app\model\BaseModel;
use app\model\permission\RoleModel;

/**
* 角色权限模型
*/
class RolePermissionModel extends BaseModel
{
	protected $table = 'qp_role_permission';
	
	/**
	* 检查是否存在记录
	* @param string $name 名称
	* @param int $notId 排除id
    * @return boole true-存在 false-不存在
	*/
	public static function checkExist($name, $notId = 0)
	{
		$map = [
			'name' => $name,
		];

		if (!empty($notId)) {
			$map['id'] = ['neq', $notId];
		}

		return parent::getTotal($map);
	}

	/**
	* 获取列表
	* @param int $roleId 角色id
	*/
	public function getListByRoleId($roleId)
	{
		$map = array(
			'role_id' => $roleId,
		);

		return parent::getList($map);
	}

	/**
	* 删除一条记录
	* @param int $roleid 角色id
	* @param int $permissionId 权限d
	*/
	public function deleteByPermis($roleid, $permissionId)
	{
		if(empty($roleid) || !is_numeric($roleid) || empty($permissionId) || !is_numeric($permissionId)) {
			return false;
		}
		$map = [
			'role_id' => $roleid,
			'permission_id' => $permissionId,
		];
		$model = new static;
		
		return $model->where($map)->delete();
	}




}