<?php

namespace app\services\admin\category;

use think\facade\Request;
use Cache;
use app\components\COM;
use app\components\helper\StringHelper;
use app\components\helper\PinYinHelper;
use app\model\category\CategoryModel;
use app\Inc\TableConst;

class CategoryService
{
	/**
	* 分类表单数据 param, post, get
	*/
	public static function catePost()
	{	
		$data['name'] = Request::post('name'); //分类名称
		$data['ctype'] = Request::post('ctype', 0, 'intval');
		$data['parentid'] = Request::post('parentid', 0, 'intval'); //父级id
		$data['is_end'] = Request::post('is_end', 0, 'intval'); //是否终极目录 1是
		$data['order'] = Request::post('order', 0, 'intval'); //排序 值越大权重越大
		$data['seotitle'] = Request::post('seotitle', '');
		$data['seokeyword'] = Request::post('seokeyword', '');
		$data['seodescription'] = Request::post('seodescription', '');
		$data['pinyin'] = Request::post('pinyin', '');
		$data['remark'] = Request::post('remark', '');

		if (empty($data['name'])) {
			throw new \Exception("分类名称不能为空"); 
		} elseif(empty(TableConst::$cateTypeList[$data['ctype']])) {
			throw new \Exception("请选择类型"); 
		} elseif (empty($data['pinyin'])) {
			throw new \Exception("拼音不能为空"); 
		}

		if (empty($data['pinyin'])) {
			//如果汉字过长则取首字母
			if (mb_strlen($data['name']) > 10) {
				$data['pinyin'] = PinYinHelper::getFirstPY($data['name']);
			} else {
				$data['pinyin'] = PinYinHelper::getAllPY($data['name']);
			}
		}

		//首字母
		$data['letter'] = StringHelper::getFirstCharter($data['name']);

		return $data;
	}

	/**
	* 校验分类名是否存在
	* @param string $name 分类名
	* @param int $parentid 父级id
	* @param int $cid 排除分类id
	*/
	public static function checkCateExist($name, $parentid = 0, $cid = 0)
	{
		if (empty($name)) {
			throw new \Exception("分类名称不能为空"); 
		}

		if (CategoryModel::checkRowExist($name, $parentid, $cid)) {
            throw new \Exception("分类名称已经存在"); 
        }

        return true;
	}

	/**
	* 判断当前分类是第几层
	*/
	public static function getCateLevel($cid, $level = 1)
	{
		$info = CategoryModel::getInfo($cid);
		if ($info['parentid'] > 0) {
			return self::getCateLevel($info['parentid'], $level + 1);
		}

		return $level;
	}

	/**
	* 判断拼音是否存在
	*/
	public static function checkRowExistByPinyin($pinyin, $notid = 0)
	{
		if (empty($pinyin)) {
			throw new \Exception("拼音不能为空"); 
		}

		$map = [
			['pinyin', '=', $pinyin]
		];

		if ($notid) {
			array_push($map, ['id', '<>', $notid]);
		}

		if (CategoryModel::getTotal($map)) {
            throw new \Exception("{$pinyin} 分类拼音已存在，请手动填写一个"); 
        }

        return true;
	}


}