<?php

namespace app\model\permission;

use think\Request;
use app\model\BaseModel;

/**
* 权限模型
*/
class PermissionModel extends BaseModel
{
	protected $table = 'qp_permission';
	
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
	* 检查权限标识是否存在记录
	* @param string $name 名称
	* @param int $notId 排除id
    * @return boole true-存在 false-不存在
	*/
	public static function checkIdentifyExist($identify, $notId = 0)
	{
		$map = [
			['identify', '=', $identify]
		];
		if (!empty($notId)) {
			$map[] = ['id', 'neq', $notId];
		}

		return parent::getTotal($map);
	}





}