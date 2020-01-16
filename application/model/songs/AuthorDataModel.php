<?php

namespace app\model\songs;

use think\Request;
use app\model\BaseModel;

/**
* 作者内容模型
*/
class AuthorDataModel extends BaseModel
{
	protected $table = 'qp_author_data';

	/**
	* 更新一条记录
	* @param int $authorid 作者id
	* @param array $data 更新数据
	*/
	public static function updateByAuthorid($authorid, $data)
	{
		if(empty($authorid) || empty($data)) {
			return false;
		}

		$model = new static;
		return $model->save($data, ['author_id' => $authorid]);
	}

	/**
	* 获取一条记录
	* @param int $authorid 作者id
	* @param array $field 返回字段
	*/
	public static function getInfoByAuthorid($authorid, $field = [])
	{
		if (empty($authorid) || !is_numeric($authorid)) {
			return [];
		}

		$model = new static;
		$res = $model->field($field)->where(['author_id'=>$authorid])->find();

		return !empty($res)? $res->toArray(): [];
	}


	/**
	* 检查是否存在记录
	* @param int $authorid 作者id
    * @return boole true-存在 false-不存在
	*/
	public static function checkRowExist($authorid)
	{
		$map = [
			['author_id', '=', $authorid]
		];

		return self::getTotal($map);
	}


}

