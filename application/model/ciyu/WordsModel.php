<?php

namespace app\model\ciyu;

use think\Request;
use app\model\BaseModel;
use app\Inc\TableConst;
use Db;

/**
* 词语模型
*/
class WordsModel extends BaseModel
{
	protected $table = 'ciyu_words';
	
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
	* 更新记录
	* @param int $id id
	* @param array $data 更新数据
	*/
	public static function updateByMap($map, $data, $limit = 0, $sort = 'id asc')
	{
		if(empty($map) || empty($data)) {
			return false;
		}

		$model = new static;

		$query = $model->where($map)->order($sort)->data($data);
		if ($limit) {
			$query->limit($limit);
		}
		
		return $query->update();
	}

	/**
    * 获取通过审核文章列表
    * @param array $map where条件
    * @param int $page 分页
    * @param int $limit 每页数量
    * @param string $field 显示字段
    * @param string $order 排序
    */
    public static function getPassList($map = [], $page = 0, $limit = 0, $field = [], $order = '')
    {
    	array_push($map, ['status', '=', TableConst::WORDS_STATUS_PASS]);
    	array_push($map, ['is_show', '=', TableConst::WORDS_SHOW_OK]);

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
	* @param string $title 标题
	* @param int $wordid 词语id
    * @return boole true-存在 false-不存在
	*/
	public static function checkRowExist($title, $wordid = 0)
	{
		$map = [
			['title', '=', $title]
		];
		if (!empty($wordid) && is_numeric($wordid)) {
			$map[] = ['id', 'neq', $wordid];
		}

		return self::getTotal($map);
	}

	/**
	* 检查是否存在记录
	* @param string $titleHash hash标题
	* @param int $wordid 词语id
    * @return boole true-存在 false-不存在
	*/
	public static function checkRowExistByHash($titleHash, $wordid = 0)
	{
		$map = [
			['title_hash', '=', $titleHash]
		];
		if (!empty($wordid) && is_numeric($wordid)) {
			$map[] = ['id', 'neq', $wordid];
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
		$map = "t1.status=".TableConst::WORDS_STATUS_PASS." AND t1.is_show=".TableConst::WORDS_SHOW_OK;
		if (is_numeric($cids)) {
			$cids = $cids? [$cids]: [];
		} elseif (strpos(',', $cids) !== false) {
			$cids = explode(',', $cids);
		}

		if (!empty($cids)) {
			$map .= " and t1.catid in(".implode(',', $cids).")";
		}

// 		$sql = "SELECT {$field} FROM `ciyu_words` AS t1 JOIN (SELECT ROUND(RAND() * ((SELECT MAX(id) FROM `ciyu_words`)-(SELECT MIN(id) FROM `ciyu_words`))+(SELECT MIN(id) FROM `ciyu_words`)) AS id) AS t2
// WHERE t1.id >= t2.id and {$map} ORDER BY t1.id LIMIT {$num}";

		$sql = "SELECT {$field} from ciyu_words t1 where {$map} order by RAND() limit {$num}";

        return Db::query($sql);
	}

}

