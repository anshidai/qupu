<?php

namespace app\model\permission;

use think\Request;
use app\model\BaseModel;

/**
* 角色模型
*/
class RoleModel extends BaseModel
{
	protected $table = 'qp_role';
	
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
	* @param int $notId 排除id
    * @return boole true-存在 false-不存在
	*/
	public static function checkExist($name, $notId = 0)
	{
		$map = [
			['name', '=', $name]
		];
		if (!empty($notId)) {
			$map[] = ['id', 'neq', $notId];
		}

		return parent::getTotal($map);
	}





}
