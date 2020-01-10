<?php

namespace app\model\songs;

use think\Request;
use app\model\BaseModel;

/**
* 歌谱内容模型
*/
class GepuDataModel extends BaseModel
{
	protected $table = 'qp_gepu_data';

	/**
	* 更新一条记录
	* @param int $gepuid 歌谱id
	* @param array $data 更新数据
	*/
	public static function updateByGepuid($gepuid, $data)
	{
		if(empty($gepuid) || empty($data)) {
			return false;
		}

		$model = new static;
		return $model->save($data, ['gepu_id' => $gepuid]);
	}

	/**
	* 获取一条记录
	* @param int $gepuid 歌谱id
	* @param array $field 返回字段
	*/
	public static function getInfoByGepuid($gepuid, $field = [])
	{
		if (empty($gepuid) || !is_numeric($gepuid)) {
			return [];
		}

		$model = new static;
		$res = $model->field($field)->where(['gepu_id'=>$gepuid])->find();

		return !empty($res)? $res->toArray(): [];
	}


	/**
	* 检查是否存在记录
	* @param int $gepuid 歌谱id
    * @return boole true-存在 false-不存在
	*/
	public static function checkRowExist($gepuid)
	{
		$map = [
			['gepu_id', '=', $gepuid]
		];

		return self::getTotal($map);
	}


}

