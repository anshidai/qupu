<?php

namespace app\services\admin\chengyu;

use think\facade\Request;
use app\components\helper\StringHelper;
use app\components\helper\ArrayHelper;
use app\Inc\TableConst;
use app\model\chengyu\IdiomTagsModel;

/**
* 成语tag词后台业务处理
*/
class IdiomTagsService
{
	/**
	* 表单数据 param, post, get
	*/
	public static function idiomTagsPost()
	{	
		$data['name'] = Request::post('name'); //标题
		$data['status'] = Request::post('status', 0, 'intval');
		$data['resource'] = Request::post('ids', '');

		if(empty($data['name'])) {
			throw new \Exception("tag词不能为空"); 
		} elseif (empty($data['resource'])) {
			throw new \Exception("tag词还未关联成语");
		} elseif(empty(TableConst::$idiomTagStatusList[$data['status']])) {
			throw new \Exception("请选择审核状态"); 
		} 

		$data['resource'] = implode(',', $data['resource']);

        return $data;
	}	

	/**
	* 校验标题是否存在
	* @param string $name 名称
	* @param int $notid 排除id
	*/
	public static function checIdiomTagExist($name, $notid = 0)
	{
		if (empty($name)) {
			throw new \Exception("tag词不能为空"); 
		}

		if (IdiomTagsModel::checkRowExist($name, $notid)) {
            throw new \Exception("tag词已经存在"); 
        }

        return true;
	}

}