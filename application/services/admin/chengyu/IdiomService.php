<?php

namespace app\services\admin\chengyu;

use think\facade\Request;
use app\model\chengyu\IdiomModel;
use app\model\chengyu\IdiomDataModel;
use app\model\chengyu\IdiomStructModel;
use app\services\admin\category\CategoryService;
use app\services\category\BUCategoryService;
use app\components\helper\StringHelper;
use app\components\helper\ArrayHelper;
use app\Inc\TableConst;

/**
* 成语后台业务处理
*/
class IdiomService
{
	/**
	* 表单数据 param, post, get
	*/
	public static function idiomPost()
	{	
		$data['title'] = Request::post('title'); //标题
		$data['tags'] = Request::post('tags', ''); //tag标签
		$data['title_pinyin'] = Request::post('title_pinyin', ''); //成语拼音
		$data['base_explain'] = Request::post('base_explain', ''); //基本解释
		$data['title_translate'] = Request::post('title_translate', ''); //英文翻译
		$data['voice_file'] = Request::post('voice_file', ''); //语音文件
		$data['catid'] = Request::post('catid', 0, 'intval'); //分类id
		$data['order'] = Request::post('order', 0, 'intval');
		$data['status'] = Request::post('status', 0, 'intval');
		$data['ctype'] = Request::post('ctype', 0, 'intval'); //成语类型
		$data['struct_type'] = Request::post('struct_type', 0, 'intval'); //结构类型
		$data['is_show'] = Request::post('is_show', 0, 'intval');

		$data = array_map('trim', $data);

		if(empty($data['title'])) {
			throw new \Exception("标题不能为空"); 
		} elseif(empty($data['catid']) || !is_numeric($data['catid'])) {
			throw new \Exception("请选择分类"); 
		} elseif(empty($data['ctype']) || !is_numeric($data['ctype'])) {
			// throw new \Exception("请选择类型"); 
		} elseif(empty(TableConst::$idiomStatusList[$data['status']])) {
			throw new \Exception("请选择审核状态"); 
		} elseif (empty($data['title_pinyin'])) {
			throw new \Exception("词语拼音不能为空"); 
		} elseif (empty($data['base_explain'])) {
			throw new \Exception("基本解释不能为空"); 
		}
		
		$data['charlen'] = mb_strlen($data['title']);
		$data['catname'] = BUCategoryService::getCateName($data['catid']);

		//第一个字符
		$firstChar = StringHelper::msubstr($data['title'], 0, 1, 'utf-8', false);
		$data['first_char'] = $firstChar ?? '';

		//最后一个字符
		$lastChar = StringHelper::msubstr($data['title'], -1, 1, 'utf-8', false);
		$data['last_char'] = $lastChar ?? '';

		if (empty($data['struct_type'])) {
			$data['struct_type'] = self::checkStructType($data['title']);
		}
		

        return $data;
	}	


	/**
	* 内容表单数据 param, post, get
	*/
	public static function idiomDataPost()
	{
		$data['content'] = Request::post('content', '');
		$data['seotitle'] = Request::post('seotitle', '');
		$data['seokeyword'] = Request::post('seokeyword', '');
		$data['seodescription'] = Request::post('seodescription', '');
		if (empty($data['content'])) {
			// throw new \Exception("内容不能为空"); 
		}
		return $data;
	}

	/**
	* 结构表单数据 param, post, get
	*/
	public static function idiomStructPost()
	{
		$struct = Request::post('struct', '');

		$data = [];
		foreach ($struct as $key => $val) {
			$data[] = [
				'ctype' => $key,
				'content' => $val ?? '',
			];
		}
		
		return $data;
	}

	/**
	* 编辑结构体
	* @param int $idiomid 成语id
	* @param array $struct 成语结构体
	*/
	public static function editIdiomStruct($idiomid, $struct)
	{
		if (empty($idiomid) || !is_numeric($idiomid)) {
			throw new \Exception("请求id不存在"); 
		} elseif (empty($struct)) {
			throw new \Exception("结构内容不能为空"); 
		}

		foreach ($struct as $val) {
			if (IdiomStructModel::checkRowExist($idiomid, $val['ctype'])) {
				IdiomStructModel::editStruct($idiomid, $val['ctype'], $val['content']);
			} else {
				IdiomStructModel::addStruct($idiomid, $val['ctype'], $val['content']);
			}
		}

		return true;
	}

	
	/**
	* 校验标题是否存在
	* @param string $title 标题
	* @param int $idiomid 排除id
	*/
	public static function checIdiomExist($title, $idiomid = 0)
	{
		if (empty($title)) {
			throw new \Exception("标题不能为空"); 
		}

		if (IdiomModel::checkRowExist($title, $idiomid)) {
            throw new \Exception("标题已经存在"); 
        }

        return true;
	}

	
	/**
	* 获取分类下成语数
	* @param int $catid 分类id
	* @param int $status 审核状态 1 未审核  2 已删除 3 审核通过
	* @param int $isshow 是否显示 1-不显示 2-显示
	*/
	public static function getIdiomCountByCid($catid, $status = 0, $isshow = 0)
	{
		$map = [
			['catid', '=', $catid]
		];

		if ($status) {
			array_push($map, ['status', '=', $status]);
		}
		if ($isshow) {
			array_push($map, ['is_show', '=', $isshow]);
		}

		return IdiomModel::getTotal($map);
	}

	/**
	* 判断成语结构
	*/
	public static function checkStructType($title)
	{
		$type = 0;
		if (empty($title)) {
			return $type;
		}

		$leng = iconv_strlen($title, "UTF-8");
		if ($leng != 4) {
			return $type;
		}

		$char = [];
		$leng = iconv_strlen($title, "UTF-8");
		for($i = 0; $i < $leng; $i++) {
			$char[] = mb_substr($title, $i, 1, "UTF-8");
		}

		if (empty($char) || count($char) != 4) {
			return $type;
		}

		$structType = getEnums('structType');
		foreach ($structType as $key => $val) {
		 	$data[$val] = $key;
		}

		if ($char[0] == $char[1] && $char[2] == $char[3]) {
			return $data['AABB'];
		} elseif ($char[0] == $char[1]) {
			return $data['AABC'];
		} elseif ($char[0] == $char[2] && $char[1] == $char[3]) {
			return $data['ABAB'];
		} elseif ($char[0] == $char[2]) {
			return $data['ABAC'];
		} elseif ($char[1] == $char[2]) {
			return $data['ABBC'];
		} elseif ($char[0] == $char[3]) {
			return $data['ABCA'];
		} elseif ($char[1] == $char[3]) {
			return $data['ABCB'];
		} elseif ($char[2] == $char[3]) {
			return $data['ABCC'];
		}

		return $type;
	}

	/**
	* 重新组合词语
	*/
	public static function groupWords($struct)
	{
		$data = [];

		if (!empty($struct)) {
			foreach ($struct as $key => $val) {
				$tips = '';
				if ($key == in_array($key, [TableConst::IDIOM_STRUCT_SYNONYM, TableConst::IDIOM_STRUCT_ANTONYM])) {
					$tips = '一行一条一个';
				}

				$data[$key] = [
					'id' => $key,
					'name' => $val,
					'tips' => $tips,
				];
			}
		}

		return $data;
	}

	/**
	* 批量获取成语信息
	* @param array|string $ids 成语id
	*/
	public static function getBatchIdiomInfo($ids, $field = [])
	{
		if (empty($ids)) {
			return [];
		} elseif (strpos($ids, ',') !== false) {
			$ids = explode(',', $ids);
		} else {
			if (is_numeric($ids) || is_string($ids)) {
				$ids = [$ids];
			}
		}

		$map = [
			['id', 'in', $ids]
		];

		$data = IdiomModel::getList($map, 0, 0, $field);

		return $data;
	}


}