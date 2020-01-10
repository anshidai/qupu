<?php

namespace app\model\ciyu;

use think\Request;
use app\model\BaseModel;

/**
* 词语内容模型
*/
class WordsDataModel extends BaseModel
{
	protected $table = 'ciyu_words_data';

	/**
	* 更新一条记录
	* @param int $wordid 词语id
	* @param array $data 更新数据
	*/
	public static function updateByWordid($wordid, $data)
	{
		if(empty($wordid) || empty($data)) {
			return false;
		}

		$model = new static;
		return $model->save($data, ['word_id' => $wordid]);
	}

	/**
	* 获取一条记录
	* @param int $wordid 词语id
	* @param array $field 返回字段
	*/
	public static function getInfoByWordid($wordid, $field = [])
	{
		if (empty($wordid) || !is_numeric($wordid)) {
			return [];
		}

		$model = new static;
		$res = $model->field($field)->where(['word_id'=>$wordid])->find();

		return !empty($res)? $res->toArray(): [];
	}


	/**
	* 检查是否存在记录
	* @param int $wordid 词语id
    * @return boole true-存在 false-不存在
	*/
	public static function checkRowExist($wordid)
	{
		$map = [
			['word_id', '=', $ciyuid]
		];

		return self::getTotal($map);
	}


}

