<?php

namespace app\model\ciyu;

use think\Request;
use app\model\BaseModel;

/**
* 词语tags模型
*/
class WordsTagsModel extends BaseModel
{
	protected $table = 'ciyu_words_tags';

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

    	$model = new static;
    	$res = $model->save($data);

    	return !empty($res)? $model->id: 0;
    }

	/**
	* 更新一条记录
	* @param int $id id
	* @param int $ctype 类型
	* @param string $content 
	*/
	public static function editTags($id, $data)
	{
		if(empty($id) || empty($data)) {
			return false;
		}

		$model = new static;
		return $model->save($data, ['id' => $id]);
	}


	/**
	* 检查是否存在记录
	* @param string $name 名称
	* @param int $notid 排除id
    * @return boole true-存在 false-不存在
	*/
	public static function checkRowExist($name, $notid = 0)
	{
		$map = [
			['name', '=', $name],
		];

		if ( $notid) {
			array_push($map, ['id', '<>', $notid]);
		}

		return self::getTotal($map);
	}


}

