<?php

namespace app\model\category;

use think\Request;
use app\model\BaseModel;

/**
* 分类模型
*/
class CategoryModel extends BaseModel
{
	protected $table = 'qp_category';
	
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
	* 获取一条记录
	* @param string $name 查询条件
	* @param array $field 返回字段
	*/
	public static function getInfoByName($name, $field = [])
	{
		if (empty($name)) {
			return [];
		}

		$model = new static;
		$res = $model->field($field)->where(['name' => $name])->find();

		return !empty($res)? $res->toArray(): [];
	}

	/**
	* 获取父分类信息
	* @param int $cid 分类id
	*/
	public function getParentByCid($cid)
	{
		if (empty($cid) || !is_numeric($cid)) {
			return [];
		}
		$map = [
			'parentid' => $cid,
		];
		return self::getInfoByMap($map);
	}

	/**
	* 检查是否存在记录
	* @param string $name 名称
	* @param int $parentid 父级id
	* @param int $cid 排除分类id
    * @return boole true-存在 false-不存在
	*/
	public static function checkRowExist($name, $parentid = 0, $cid = 0)
	{
		$map = [
			['name', '=', $name],
			['parentid', '=', $parentid]
		];

		if ($cid) {
			$map[] = ['id', 'neq', $cid];
		}

		return self::getTotal($map);
	}


}

