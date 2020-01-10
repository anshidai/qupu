<?php

namespace app\model\common;

use think\Request;
use app\model\BaseModel;

/**
* 配置模型
*/
class ConfigModel extends BaseModel
{
	protected $table = 'qp_config';

	/**
	* 更新一条数据
	*/
	public static function updateByName($kname, $kval, $extend = '')
	{
		if (empty($kname) || empty($kval)) {
			return false;
		}
		$arr = array(
			'kval' => $kval,
			'extend' => $extend,
		);
		$model = new static;

		return $model->save($arr, ['kname'=>$kname]);
	}

	/**
	* 获取一条记录
	* @param string $kname key名
	* @param array $field 返回字段
	*/
	public static function getInfoByName($kname, $field = [])
	{
		if (empty($kname)) {
			return [];
		}

		$map = [
			'kname' => $kname,
		];
		$model = new static;
		$res = $model->field($field)->where($map)->find();

		return !empty($res)? $res->toArray(): [];
	}

	
	/**
	* 检查是否存在记录
	* @param string $kname 名称
    * @return boole true-存在 false-不存在
	*/
	public static function checkRowExist($kname)
	{
		return self::getInfoByName($kname);
	}


}

