<?php

namespace app\model\songs;

use think\Request;
use app\model\BaseModel;
use app\Inc\TableConst;
use Db;

/**
* 作者模型
*/
class AuthorModel extends BaseModel
{
	protected $table = 'gp_author';

	/**
    * 获取通过审核列表
    * @param array $map where条件
    * @param int $page 分页
    * @param int $limit 每页数量
    * @param string $field 显示字段
    * @param string $order 排序
    */
    public static function getPassList($map = [], $page = 0, $limit = 0, $field = [], $order = '')
    {
    	array_push($map, ['status', '=', TableConst::AUTHOR_STATUS_PASS]);
    	array_push($map, ['is_show', '=', TableConst::AUTHOR_SHOW_OK]);

        $model = new static;
        $query = $model->field($field)->where($map);
        if ($page && $limit) {
            $query->page($page, $limit);
        }
        if ($order) {
            $query->order($order);
        }

        $res = $query->select();

        return !empty($res)? $res->toArray(): [];
    }
	

	/**
	* 检查是否存在记录
	* @param string $name 标题
	* @param int $authorid 作者id
    * @return boole true-存在 false-不存在
	*/
	public static function checkRowExist($name, $authorid = 0)
	{
		$map = [
			['name', '=', $name]
		];
		if (!empty($authorid) && is_numeric($authorid)) {
			$map[] = ['id', 'neq', $authorid];
		}

		return self::getTotal($map);
	}

}

