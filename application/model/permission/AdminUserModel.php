<?php

namespace app\model\permission;

use think\Request;
use app\model\BaseModel;

/**
* 管理员模型
*/
class AdminUserModel extends BaseModel
{
	protected $table = 'qp_admin_user';
	
    /**
	* 添加一条记录
	* @param array $data 添加数据
	*/
    public static function _add($data)
    {
    	if(empty($data)) {
    		return false;
    	}
        $data['addtime'] = date('Y-m-d H:i:s');
        $data['edittime'] = date('Y-m-d H:i:s');

        return parent::_add($data);
    }

	/**
	* 检查是否存在记录
	* @param string $name 名称
	* @param int $userid 用户id
    * @return boole true-存在 false-不存在
	*/
	public static function checkRowExist($name, $userid = 0)
	{
		$map = [
			['name', '=', $name]
		];
		if (!empty($userid) && is_numeric($userid)) {
			$map[] = ['id', 'neq', $userid];
		}

		return parent::getTotal($map);
	}


}

