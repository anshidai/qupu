<?php

namespace app\model\permission;

use think\Request;
use app\model\BaseModel;
use app\model\permission\RoleModel;

/**
* 用户管理角色模型
*/
class RoleUserModel extends BaseModel
{
	protected $table = 'qp_role_user';
	
	/**
	* 检查是否存在记录
	* @param int $userid 用户id
	* @param int $roleid 角色id
    * @return boole true-存在 false-不存在
	*/
	public static function checkExist($userid, $roleid)
	{
		if (empty($userid) || !is_numeric($userid) || empty($roleid) || !is_numeric($roleid)) {
			return false;
		}

		$map = [
			'userid' => $userid,
			'role_id' => $roleid,
		];
		return parent::getTotal($map);
	}

	/**
	* 获取列表
	* @param int $userid 用户id
	*/
	public function getListByUserid($userid)
	{
		$map = array(
			'userid' => $userid,
		);

		return parent::getList($map);
	}

	/**
	* 删除一条记录
	* @param int $userid 用户id
	* @param int $roleid 角色id
	*/
	public function deleteByRole($userid, $roleid)
	{
		if(empty($userid) || !is_numeric($userid) || empty($roleid) || !is_numeric($roleid)) {
			return false;
		}
		$map = [
			'userid' => $userid,
			'role_id' => $roleid,
		];
		$model = new static;
		
		return $model->where($map)->delete();
	}




}