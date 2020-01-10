<?php

namespace app\services\category;

use think\facade\Request;
use think\facade\Cache;
use app\components\helper\StringHelper;
use app\components\helper\PinYinHelper;
use app\components\helper\ArrayHelper;
use app\model\category\CategoryModel;
use app\Inc\TableConst;
use app\components\COM;

/**
* 分类业务处理
*/
class BUCategoryService
{
	/**
	* 获取一级分类列表
	* @param int $ctype 类型
	*/
	public static function getLevelOneList($ctype)
	{
		$map = [
			'ctype' => $ctype,
			'status' => TableConst::CATE_STATUS_ENABLED,
			'parentid' => 0,
		];
		$data = CategoryModel::getList($map, 1, 0, ['id,name,parentid,is_end,status']);

		return $data;
	}

	/**
	* 获取分类下子分类
	* @param int $cid 分类id
	* @param int $ctype 类型
	*/
	public static function getSubCateList($cid, $ctype)
	{
		if (empty($cid)) {
			return [];
		}

		$cates = self::getCateAll($ctype);

		//优先从缓存取数据
		if (!empty($cates)) {
			foreach ($cates as $val) {
				if ($val['parentid'] == $cid && $val['status'] == TableConst::CATE_STATUS_ENABLED) {
					$data[] = $val;
				}
			}
		} else {
			$map = [
				'ctype' => $ctype,
				'status' => TableConst::CATE_STATUS_ENABLED,
				'parentid' => $cid,
			];
			$data = CategoryModel::getList($map);
		}

		return $data;
	}

	/**
	* 获取分类信息
	* @param int $cid 分类id
	*/
	public static function getCateInfo($cid)
	{
		$cates = self::getCateAll();
		if (!empty($cates[$cid])) {
			return $cates[$cid];
		} else {
			return  CategoryModel::getInfo($cid);
		}
	}

	/**
	* 获取分类信息
	* @param string $pinyin 分类pinyin
	*/
	public static function getCateInfoByPinyin($pinyin)
	{
		$cates = self::getCateAll();

		$data = [];
		foreach ($cates as $val) {
			if ($val['pinyin'] == $pinyin) {
				$data = $val;
				break;
			}
		}
		
		return $data;
	}

	/**
	* 获取分类信息返回字段值
	*/
	public static function getCateField($cid, $field = 'name')
	{
		$info = self::getCateInfo($cid);
		if (!empty($info)) {
			return $info[$field];
		}
		return '';
	}

	/**
	* 根据分类id获取子类id
	* @param int $parentid 父分类id
	*/
	public static function getCateIdsByParentId($parentid)
	{
		$data = self::getSubCateList($parentid);
		return array_column($data, 'id');
	}

	/**
	* 获取所有分类
	* @param int $ctype 类型
	* @param bool $isrefresh 是否重新加载分类
	*/
	public static function getCateAll($ctype = '', $isrefresh = false)
	{
		$cacheKey =  COM::getCachekey('admin_cate_all', $ctype);
    	$data = Cache::get($cacheKey);
		if (empty($data) || $isrefresh) {
			$map = [];
			if ($ctype) {
				$map['ctype'] = $ctype;
			}

			$data = CategoryModel::getList($map, 0, 0);
			$data = ArrayHelper::toHashmap($data, 'id');
			Cache::set($cacheKey, json_encode($data), 3600 * 24);
		} else {
			$data = json_decode($data, true);
		}

		return $data;
	}

	/**
	 * 获得分类树状数据
	 * @param $arr 数据
	 * @param $space 空格符
	 * @param $level 树状层次 1-最上层
	 * @return array
	 */
	public static function getCateTree(&$arr, $pid = 0, $space = '&nbsp;', $level = 1)
	{
		$data = [];
		if(!is_array($arr) || empty($arr)) {
			return $data;
		}
		foreach($arr as $val) {
			if (is_null($val['parentid'])) {
				continue;
			}
			if($val['parentid'] == $pid) {
				$val['level'] = $level;
				$val['space'] = str_repeat($space, $level - 1);
				$val['_name'] = str_repeat('&nbsp;', $val['level'] )."{$val['space']}{$val['name']}";
				$val['child'] = self::getCateTree($arr, $val['id'], $space, $level + 1);
				$data[$val['id']] = $val;
			}
		}
		return $data;
	}

	/**
	 * 树状数据转换成正常数组列
	 * @param $tree 数据
	 * @param $child 子集键名
	 * @param $data 过渡用的中间数组
	 * @return array
	 */
	public static function parseCateTree($tree, $child = 'child', &$data = array())
	{
		foreach($tree as $val) {
			if($val['level'] == 1) {
				$val['_name'] = $val['name'];
			} else if($val['level'] == 2) {
				$val['_name'] = str_repeat('&nbsp;', $val['level'] )."└──{$val['space']}{$val['name']}";
			} else if($val['level'] == 3) {
				$val['_name'] = str_repeat('&nbsp;', $val['level'] )."└────{$val['space']}{$val['name']}";
			} else if($val['level'] == 4) {
				$val['_name'] = str_repeat('&nbsp;', $val['level'] )."└────────{$val['space']}{$val['name']}";
			} else {
				$val['_name'] = "│{$val['space']}└─ {$val['name']}";
			}
			$data[$val['id']] = $val;
			unset($data[$val['id']][$child]); //将子集数据删除
			if(!empty($val['child'])) {
				self::parseCateTree($val[$child], $child, $data);
			}
		}
		return $data;
	}

	/**
	* 获取地区名称
	* @param int $catid 分类id
	*/
	public static function getCateName($catid)
	{
		$res = CategoryModel::getInfo($catid);
		return isset($res['name'])? $res['name']: '';
	}

}