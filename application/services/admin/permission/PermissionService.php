<?php

namespace app\services\admin\permission;

use think\facade\Request;
use app\model\permission\PermissionModel;

/**
* 权限后台业务处理
*/
class PermissionService
{
	/**
	* 权限表单数据 param, post, get
	*/
	public static function permissionPost()
	{	
		$data['name'] = Request::post('name');
		$data['url'] = Request::post('url');
		$data['parentid'] = Request::post('parentid', 0, 'intval');
		$data['ctype'] = Request::post('ctype', 0, 'intval');
		$data['is_sys'] = Request::post('is_sys', 0, 'intval');
		$data['identify'] = Request::post('identify');
		$data['order'] = Request::post('order', 0, 'intval');
		$data['remark'] = Request::post('remark');

		if (empty($data['name'])) {
			throw new \Exception("权限名称不能为空"); 
		} elseif(empty($data['identify'])) {
			throw new \Exception("权限标识不能为空"); 
		} elseif(empty($data['ctype']) || !is_numeric($data['ctype'])) {
			throw new \Exception("请选择权限类型"); 
		}

		if (!empty($data['url'])) {
			$data['url'] = htmlspecialchars_decode(urldecode($data['url']));
		}

		return $data;
	}

	public static function getPermisList()
	{
		$map = [];

		return PermissionModel::getList($map, 0, 0, [], 'id asc,order desc');
	}


	/**
	* 校验权限标记是否存在
	* @param string $identify 权限标记
	* @param int $courseId 排除权限id
	*/
	public static function checkIdentifyExist($identify, $id = 0)
	{
		if (empty($identify)) {
			throw new \Exception("权限标记不能为空"); 
		}

		if (PermissionModel::checkIdentifyExist($identify, $id)) {
            throw new \Exception("权限标记已经存在"); 
        }

        return true;
	}

	/**
	 * 获得树状数据
	 * @param $arr 数据
	 * @param $space 空格符
	 * @param $level 树状层次 1-最上层
	 * @return array
	 */
	public static function getPermisTree(&$arr, $pid = 0, $space = '&nbsp;', $level = 1)
	{
		if(!is_array($arr) || empty($arr)) {
			return false;
		}
		foreach($arr as $val) {
			if($val['parentid'] == $pid) {
				$val['level'] = $level;
				$val['_name'] = str_repeat('&nbsp;', $val['level'] )."{$val['space']}{$val['name']}";
				$val['child'] = self::getPermisTree($arr, $val['id'], $space, $level + 1);
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
	public static function parsePermisTree($tree, $child = 'child', &$data = array())
	{	
		foreach($tree as $val) {
			if($val['level'] == 1) {
				$val['_name'] = "<strong>{$val['name']}</strong>";
			} elseif($val['level'] == 2) {
				$val['_name'] = '└─'.str_repeat('─', $val['level']). str_repeat($val['space'], $val['level']). " <b>{$val['name']}</b>";
			} else {
				$val['_name'] = '└─'.str_repeat('─', $val['level']). str_repeat($val['space'], $val['level']). " {$val['name']}";
			}
			$data[$val['id']] = $val;
			unset($data[$val['id']][$child]); //将子集数据删除
			if(!empty($val['child'])) {
				self::parsePermisTree($val[$child], $child, $data);
			}
		}
		return $data;
	}


}