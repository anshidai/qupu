<?php

namespace app\model\songs;

use think\Request;
use app\model\BaseModel;
use app\Inc\TableConst;
use Db;

/**
* 歌谱模型
*/
class QupuModel extends BaseModel
{
	protected $table = 'qp_gepu';

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
    	array_push($map, ['status', '=', TableConst::GEPU_STATUS_PASS]);
    	array_push($map, ['is_show', '=', TableConst::GEPU_SHOW_OK]);

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
	* @param int $gepuid 歌谱id
    * @return boole true-存在 false-不存在
	*/
	public static function checkRowExist($name, $gepuid = 0)
	{
		$map = [
			['name', '=', $name]
		];
		if (!empty($gepuid) && is_numeric($gepuid)) {
			$map[] = ['id', 'neq', $gepuid];
		}

		return self::getTotal($map);
	}

	/**
	* 随机获取
	* @param int|string|array $cids 分类id
	* @param int $num 记录数
	* @param string $field 字段
	*/
	public function getRandListByCateId($cids = 0, $num = 10, $field = '*')
	{
		$map = "t1.status=".TableConst::GEPU_STATUS_PASS." AND t1.is_show=".TableConst::GEPU_SHOW_OK;
		if (is_numeric($cids)) {
			$cids = $cids? [$cids]: [];
		} elseif (strpos(',', $cids) !== false) {
			$cids = explode(',', $cids);
		}

		if (!empty($cids)) {
			$map .= " and t1.catid in(".implode(',', $cids).")";
		}

// 		$sql = "SELECT {$field} FROM `qp_gepu` AS t1 JOIN (SELECT ROUND(RAND() * ((SELECT MAX(id) FROM `qp_gepu`)-(SELECT MIN(id) FROM `qp_gepu`))+(SELECT MIN(id) FROM `qp_gepu`)) AS id) AS t2
// WHERE t1.id >= t2.id and {$map} ORDER BY t1.id LIMIT {$num}";

		$sql = "SELECT {$field} from qp_gepu t1 where {$map} order by RAND() limit {$num}";

        return Db::query($sql);
	}

}

