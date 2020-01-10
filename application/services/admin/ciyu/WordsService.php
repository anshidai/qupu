<?php

namespace app\services\admin\ciyu;

use think\facade\Request;
use app\model\ciyu\WordsModel;
use app\model\ciyu\WordsDataModel;
use app\model\ciyu\WordsStructModel;
use app\services\admin\category\CategoryService;
use app\services\category\BUCategoryService;
use app\components\helper\StringHelper;
use app\components\helper\ArrayHelper;
use app\Inc\TableConst;

/**
* 词语后台业务处理
*/
class WordsService
{
	/**
	* 表单数据 param, post, get
	*/
	public static function wordsPost()
	{	
		$data['title'] = Request::post('title'); //标题
		$data['tags'] = Request::post('tags', ''); //tag标签
		$data['title_pinyin'] = Request::post('title_pinyin', ''); //词语拼音
		$data['base_explain'] = Request::post('base_explain', ''); //基本解释
		$data['title_translate'] = Request::post('title_translate', ''); //英文翻译
		$data['voice_file'] = Request::post('voice_file', ''); //语音文件
		$data['catid'] = Request::post('catid', 0, 'intval'); //分类id
		$data['order'] = Request::post('order', 0, 'intval');
		$data['status'] = Request::post('status', 0, 'intval');
		$data['ctype'] = Request::post('ctype', 0, 'intval');
		$data['is_show'] = Request::post('is_show', 0, 'intval');

		if(empty($data['title'])) {
			throw new \Exception("标题不能为空"); 
		} elseif(empty($data['catid']) || !is_numeric($data['catid'])) {
			throw new \Exception("请选择分类"); 
		} elseif(empty($data['ctype']) || !is_numeric($data['ctype'])) {
			throw new \Exception("请选择类型"); 
		} elseif(empty(TableConst::$wordsStatusList[$data['status']])) {
			throw new \Exception("请选择审核状态"); 
		} elseif (empty($data['title_pinyin'])) {
			throw new \Exception("词语拼音不能为空"); 
		} elseif (empty($data['base_explain'])) {
			throw new \Exception("基本解释不能为空"); 
		}
		
		$data['charlen'] = mb_strlen($data['title']);

		$data['catname'] = BUCategoryService::getCateName($data['catid']);
		
        return $data;
	}	

	/**
	* 内容表单数据 param, post, get
	*/
	public static function wordsDataPost()
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
	public static function wordsStructPost()
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
	* @param int $ciyuid 词语id
	* @param array $struct 成语结构体
	*/
	public static function editWordsStruct($ciyuid, $struct)
	{
		if (empty($ciyuid) || !is_numeric($ciyuid)) {
			throw new \Exception("请求id不存在"); 
		} elseif (empty($struct)) {
			throw new \Exception("结构内容不能为空"); 
		}

		foreach ($struct as $val) {
			if (WordsStructModel::checkRowExist($ciyuid, $val['ctype'])) {
				WordsStructModel::editStruct($ciyuid, $val['ctype'], $val['content']);
			} else {
				WordsStructModel::addStruct($ciyuid, $val['ctype'], $val['content']);
			}
		}

		return true;
	}

	
	/**
	* 校验标题是否存在
	* @param string $title 标题
	* @param int $wordid 排除id
	*/
	public static function checWordsExist($title, $wordid = 0)
	{
		if (empty($title)) {
			throw new \Exception("标题不能为空"); 
		}

		if (WordsModel::checkRowExist($title, $wordid)) {
            throw new \Exception("标题已经存在"); 
        }

        return true;
	}

	
	/**
	* 获取分类下词语数
	* @param int $catid 分类id
	* @param int $status 审核状态 1 未审核  2 已删除 3 审核通过
	* @param int $isshow 是否显示 1-不显示 2-显示
	*/
	public static function getWordsCountByCid($catid, $status = 0, $isshow = 0)
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

		return WordsModel::getTotal($map);
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
				if ($key == in_array($key, [TableConst::WORDS_STRUCT_SYNONYM, TableConst::WORDS_STRUCT_ANTONYM])) {
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


}