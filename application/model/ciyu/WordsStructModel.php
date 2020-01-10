<?php

namespace app\model\ciyu;

use think\Request;
use app\model\BaseModel;

/**
* 词语结构模型
*/
class WordsStructModel extends BaseModel
{
	protected $table = 'ciyu_words_struct';

	/**
	* 新增一条记录
	* @param int $wordid 词语id
	* @param int $ctype 类型
	* @param string $content 
	*/
	public static function addStruct($wordid, $ctype, $content = '')
	{
		if(empty($wordid) || empty($ctype)) {
			return false;
		}

		$arr = [
			'word_id' => $wordid,
			'ctype' => $ctype,
			'content' => $content,
		];

		$model = new static;
        $res = $model->save($arr);

        return !empty($res)? $model->id: 0;
	}

	/**
	* 更新一条记录
	* @param int $wordid 词语id
	* @param int $ctype 类型
	* @param string $content 
	*/
	public static function editStruct($wordid, $ctype, $content = '')
	{
		if(empty($wordid) || empty($ctype)) {
			return false;
		}

		$map = [
			['word_id', '=', $wordid],
			['ctype', '=', $ctype],
		];

		$model = new static;
		return $model->save(['content' => $content], $map);
	}

	/**
	* 获取一条记录
	* @param int $wordid 词语id
	* @param int $ctype 类型
	* @param array $field 返回字段
	*/
	public static function getInfoByType($wordid, $ctype, $field = [])
	{
		if(empty($wordid) || empty($ctype) || empty($data)) {
			return [];
		}

		$map = [
			['word_id', '=', $wordid],
			['ctype', '=', $ctype],
		];

		$model = new static;
		$res = $model->field($field)->where($map)->select();

		return !empty($res)? $res->toArray(): [];
	}

	/**
	* 获取记录
	* @param int $wordid 词语id
	* @param array $field 返回字段
	*/
	public static function getListByIdwordid($wordid, $field = [])
	{
		if(empty($wordid)) {
			return [];
		}

		$map = [
			['word_id', '=', $wordid],
		];

		$model = new static;
		$res = $model->field($field)->where($map)->select();

		return !empty($res)? $res->toArray(): [];
	}


	/**
	* 检查是否存在记录
	* @param int $wordid 词语id
	* @param int $ctype 类型
    * @return boole true-存在 false-不存在
	*/
	public static function checkRowExist($wordid, $ctype)
	{
		$map = [
			['word_id', '=', $wordid],
			['ctype', '=', $ctype],
		];

		return self::getTotal($map);
	}


}

