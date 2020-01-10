<?php

namespace app\services\admin\ciyu;

use think\facade\Request;
use app\components\helper\StringHelper;
use app\components\helper\ArrayHelper;
use app\Inc\TableConst;
use app\model\ciyu\WordsTagsModel;

/**
* 词语tag词后台业务处理
*/
class WordsTagsService
{
	/**
	* 表单数据 param, post, get
	*/
	public static function wordsTagsPost()
	{	
		$data['name'] = Request::post('name'); //标题
		$data['status'] = Request::post('status', 0, 'intval');
		
		if(empty($data['name'])) {
			throw new \Exception("tag词不能为空"); 
		} elseif(empty(TableConst::$wordsTagsStatusList[$data['status']])) {
			throw new \Exception("请选择审核状态"); 
		} 

        return $data;
	}	

	/**
	* 校验标题是否存在
	* @param string $name 名称
	* @param int $notid 排除id
	*/
	public static function checWordsTagExist($name, $notid = 0)
	{
		if (empty($name)) {
			throw new \Exception("tag词不能为空"); 
		}

		if (WordsTagsModel::checkRowExist($name, $notid)) {
            throw new \Exception("tag词已经存在"); 
        }

        return true;
	}

}