<?php

namespace app\api\controller;

use think\Request;
use Cache;
use think\facade\Config;

use app\model\chengyu\IdiomModel;
use app\model\chengyu\IdiomDataModel;
use app\model\chengyu\IdiomStructModel;
use app\model\category\CategoryModel;
use app\model\crawl\CjIndexModel;
use app\model\crawl\CjDomainModel;
use app\model\common\ConfigModel;
use app\services\category\BUCategoryService;
use app\components\helper\StringHelper;
use app\components\helper\ArrayHelper;
use app\components\helper\PinYinHelper;
use app\components\helper\RedisLockHelper;
use app\Inc\TableConst;
use app\services\admin\chengyu\IdiomService;

class LocoyChengyuApi extends Common
{	
	protected static $cateList = [];

	//采集站点网站缩写
	public $crawlerDomian = []; 

	//采集站点网站缩写（带索引）
	public $domianIndex = []; 

	public function initialize() 
	{
		$this->crawlerDomian = CjDomainModel::getList(['status' => TableConst::CJDOMAIN_STATUS_PASS], 0, 0, 'domain,url_reg');
		$this->domianIndex = ArrayHelper::toHashmap($this->crawlerDomian, 'domain');
	}

    public function spider(Request $request)
    {
        $data = $this->parsePost($request);

		$resid = $this->parseId($data['cjurl'], $data['domain']);
		if(empty($resid)) {
			echo '无法解析文章ID';exit;
		}

		$indexArr = [
			'url' => $data['cjurl'],
			'resid' => $resid,
			'status' => TableConst::CJ_STATUS_HTTP_SUCCESS,
			'adddate' => date('Y-m-d H:i:s'),
			'ctype' => TableConst::CJ_CTYPE_CHENGYU,
			'domain' => $data['domain'],
		];

		$isLock = true;
		if($isLock) {
			if (CjIndexModel::existByUrl($data['cjurl'])) {
				echo '采集url已存在';exit;
			}

			$data['title'] = htmlspecialchars_decode($data['title']);
			$titleHash =  md5($data['title']);
			if(IdiomModel::checkRowExistByHash($titleHash)) {
				echo '标题已存在';exit;
			}

			//新增采集索引记录
			if($indexId = CjIndexModel::_add($indexArr)) {
				$idiomArr = $this->parseIdiomArr($data);

				//新增
				if($idiomid = IdiomModel::_add($idiomArr)) {
					$contentArr = [
						'idiom_id' => $idiomid,
						'content' => $data['content'],
					];
                    IdiomDataModel::_add($contentArr);

                    $struct = $this->parseIdiomStruct($data);
                    foreach ($struct as $val) {
						if (IdiomStructModel::checkRowExist($idiomid, $val['ctype'])) {
							IdiomStructModel::editStruct($idiomid, $val['ctype'], $val['content']);
						} else {
							IdiomStructModel::addStruct($idiomid, $val['ctype'], $val['content']);
						}
					}

					CjIndexModel::_update($indexId, array('extend'=>$idiomid));

					echo '成功';exit;
				}
				unset($_content, $content, $contentArr);
			}
			
			echo '入库失败';exit;

		} else {
			echo '获取锁失败';exit;
		}

    }

    public function update(Request $request)
    {
    	$data = $this->parsePost($request);

		$resid = $this->parseId($data['cjurl'], $data['domain']);
		if(empty($resid)) {
			echo '无法解析文章ID';exit;
		}

		$indexArr = [
			'url' => $data['cjurl'],
			'resid' => $resid,
			'status' => TableConst::CJ_STATUS_HTTP_SUCCESS,
			'adddate' => date('Y-m-d H:i:s'),
			'ctype' => TableConst::CJ_CTYPE_CHENGYU,
			'domain' => $data['domain'],
		];

		$data['title'] = htmlspecialchars_decode($data['title']);
		$titleHash =  md5($data['title']);
		$idiomInfo = IdiomModel::getInfoByMap(['title_hash' => $titleHash]);
		if ($idiomInfo) { //更新操作
			$idiomArr = [];
			if (empty($idiomInfo['voice_file']) && $data['voice_file']) {
				$idiomArr['voice_file'] = $data['voice_file'];
			}

			if (empty($idiomInfo['title_pinyin']) && $data['title_pinyin']) {
				$idiomArr['title_pinyin'] = $data['title_pinyin'];
			}

			if (empty($idiomInfo['base_explain']) && $data['base_explain']) {
				$idiomArr['base_explain'] = $data['base_explain'];
			}

			if (empty($idiomInfo['title_translate']) && $data['title_translate']) {
				$idiomArr['title_translate'] = $data['title_translate'];
			}


			if ($idiomArr) {
				IdiomModel::_update($idiomInfo['id'], $idiomArr);
			}

			$contentInfo = IdiomDataModel::getInfoByIomid($idiomInfo['id']);
			if (empty($contentInfo['content']) && $data['content']) {
				IdiomDataModel::_update($contentInfo['id'], ['content' => $data['content']]);
			}

			$struct = $this->parseIdiomStruct($data);
			foreach ($struct as $val) {
				$structInfo = IdiomStructModel::getInfoByType($idiomInfo['id'], $val['ctype']);
				if ($structInfo) {
					if (empty($structInfo['content']) && $val['content']) {
						IdiomStructModel::editStruct($idiomInfo['id'], $val['ctype'], $val['content']);
					}
				} else {
					IdiomStructModel::addStruct($idiomInfo['id'], $val['ctype'], $val['content']);
				}
			}

			echo '成功';exit;

		} else { //新增操作

			if (CjIndexModel::existByUrl($data['cjurl'])) {
				echo '采集url已存在';exit;
			}

			//新增采集索引记录
			if($indexId = CjIndexModel::_add($indexArr)) {

				$idiomArr = $this->parseIdiomArr($data);

				//新增
				if($idiomid = IdiomModel::_add($idiomArr)) {
					$contentArr = [
						'idiom_id' => $idiomid,
						'content' => $data['content'],
					];
                    IdiomDataModel::_add($contentArr);

                    $struct = $this->parseIdiomStruct($data);
                    foreach ($struct as $val) {
						if (IdiomStructModel::checkRowExist($idiomid, $val['ctype'])) {
							IdiomStructModel::editStruct($idiomid, $val['ctype'], $val['content']);
						} else {
							IdiomStructModel::addStruct($idiomid, $val['ctype'], $val['content']);
						}
					}

					CjIndexModel::_update($indexId, array('extend'=>$idiomid));

					echo '成功';exit;
				}
				unset($content, $contentArr);
			}

			echo '入库失败';exit;
		}
    }


    /**
	* 获取分类
	*/
	public function getCategory(Request $request)
	{
		$type = $request->get('type');

		$html = '<select name="catid">';
		if(!in_array($type, [TableConst::CATE_TYPE_CHENGYU])) {
			$html .= '</select>';
		}else {
			$list = BUCategoryService::getCateAll($type);
			$list = BUCategoryService::getCateTree($list);
			foreach($list as $cate) {
				$disabled = $cate['is_end'] != 1? "disabled='disabled'": '';
				$isend = $cate['is_end'] == 1? '【终极】': '';
				$cate['_name'] = str_replace('&nbsp;', '', $cate['_name']);
				$html .= "<option value='{$cate['id']}' {$disabled}>{$cate['_name']}{$isend}</option>\n";
				if(!empty($cate['child'])) {
					foreach($cate['child'] as $child2) {
						$disabled = $child2['is_end'] != 1? "disabled='disabled'": '';
						$isend = $child2['is_end'] == 1? '【终极】': '';
						$child2['_name'] = str_replace('&nbsp;', '', $child2['_name']);
						$html .= "<option value='{$child2['id']}' {$disabled}>{$child2['_name']}{$isend}</option>\n";
						if(!empty($child2['child'])) {
							foreach($child2['child'] as $child3) {
								$disabled = $child3['is_end'] != 1? "disabled='disabled'": '';
								$isend = $child3['is_end'] == 1? '【终极】': '';
								$child3['_name'] = str_replace('&nbsp;', '', $child3['_name']);
								$html .= "<option value='{$child3['id']}' {$disabled}>{$child3['_name']}{$isend}</option>\n";
							}
						}
					}
				}
			}
			$html .= '</select>';
		}
		echo $html;exit;
	}


    /**
	* 解析url
	*/
	protected function parseId($url, $domain)
	{
		$id = 0;
		if (empty($this->crawlerDomian)) {
			return $id;
		}

		foreach ($this->crawlerDomian as $val) {
			if ($val['domain'] == $domain) {
				if(preg_match($val['url_reg'], $url, $match)) {
					$id = trim($match[1]);
					if (StringHelper::checkHasChinese($match[1])) {
						$id = md5($id);
					} 
					break;
				}
			}
		}

		return $id;
	}

	protected function parseIdiomArr($data)
	{
		$data['title'] = htmlspecialchars_decode($data['title']);
		$titleHash =  md5($data['title']);

		$idiomArr = [
			'title' => $data['title'],
			'title_hash' => $titleHash,
			'base_explain' => $data['base_explain'],
			'title_translate' => $data['title_translate'],
			'title_pinyin' => $data['title_pinyin'],
			'voice_file' => $data['voice_file'],
			'identify' => createUniqid(),
			'addtime' => date('Y-m-d H:i:s'),
			'edittime' => date('Y-m-d H:i:s'),
			'cjurl' => $data['cjurl'],
			'cjnav' => $data['cjnav'],
			'domain' => $data['domain'],
			'status' => TableConst::IDIOM_STATUS_NOT,
			'is_show' => TableConst::IDIOM_SHOW_HIDE,
			'order' => 0,
		];

		$cateInfo = BUCategoryService::getCateInfo($data['catid']);
		if ($cateInfo) {
			$idiomArr['catid'] = $cateInfo['id'];
			$idiomArr['catname'] = $cateInfo['name'];
		}

		$idiomArr['charlen'] = mb_strlen($data['title']);

		//第一个字符
		$firstChar = StringHelper::msubstr($data['title'], 0, 1, 'utf-8', false);
		$idiomArr['first_char'] = $firstChar ?? '';

		//最后一个字符
		$lastChar = StringHelper::msubstr($data['title'], -1, 1, 'utf-8', false);
		$idiomArr['last_char'] = $lastChar ?? '';

		$idiomArr['struct_type'] = IdiomService::checkStructType($data['title']);

		return $idiomArr;
	}

	protected function parsePost($request)
	{
		$data['title'] = $request->post('title', ''); //采集标题
        $data['title_pinyin'] = $request->post('title_pinyin', ''); //成语拼音
        $data['base_explain'] = $request->post('base_explain', ''); //基本解释
        $data['title_translate'] = $request->post('title_translate', ''); //英文翻译
        $data['voice_file'] = $request->post('voice_file', ''); //语音文件
        $data['tags'] = $request->post('tags', ''); //tag标签

        $data['content'] = $request->post('content', ''); //采集内容
        $data['cjnav'] = $request->post('cjnav', ''); //采集面包屑
        $data['domain'] = $request->post('domain', ''); //采集网站简写
        $data['cjurl'] = $request->post('url', ''); //采集url
        $data['catid'] = $request->post('catid', 0); //所属分类

        $data['struct_explain'] = $data['base_explain']; //解释
        $data['struct_ensample'] = $request->post('struct_ensample', ''); //例句
        $data['struct_source'] = $request->post('struct_source', ''); //典故出处
        $data['struct_case'] = $request->post('struct_case', ''); //示例
        $data['struct_synonym'] = $request->post('struct_synonym', ''); //近义词
        $data['struct_antonym'] = $request->post('struct_antonym', ''); //反义词
        $data['struct_degree'] = $request->post('struct_degree', ''); //常用程度
        $data['struct_emotion'] = $request->post('struct_emotion', ''); //感情色彩
        $data['struct_grammar'] = $request->post('struct_grammar', ''); //语法用法
        $data['struct_struct'] = $request->post('struct_struct', ''); //成语结构
        $data['struct_enrtime'] = $request->post('struct_enrtime', ''); //产生年代
        $data['struct_pronounce'] = $request->post('struct_pronounce', ''); //成语正音
        $data['struct_discern'] = $request->post('struct_discern', ''); //成语辨形
        $data['struct_riddle'] = $request->post('struct_riddle', ''); //成语谜面

        $data = array_map('urldecode', $data);
        $data = array_map('trim', $data);

        $data['content'] = str_replace(['<div></div>', '<p></p>'], '', $data['content']);
        $data['title'] = str_replace([';', '；'], '，', $data['title']);
        $data['title'] = str_replace('  ', ' ', $data['title']);
        $data['title_pinyin'] = str_replace([';', '；'], '，', $data['title_pinyin']);
        $data['title_pinyin'] = str_replace('  ', ' ', $data['title_pinyin']);

        if(empty($data['title'])) {
			echo '标题不能为空';exit;
		} elseif(empty($data['content'])) {
			// echo '内容不能为空';exit;
		} elseif(empty($this->domianIndex[$data['domain']])) {
			echo '网站缩写错误';exit;
		} elseif(empty($data['cjurl'])) {
			echo '采集url不能为空';exit;
		}

		if (mb_strlen(strip_tags($data['content'])) < 150) {
			// echo '采集内容太短';exit;
		}

        return $data;
	}


	/**
	* 解析结构
	*/
	protected function parseIdiomStruct($params)
	{
		$data = [
			//解释
			['ctype' => TableConst::IDIOM_STRUCT_EXPLAIN, 'content' => $params['struct_explain']],

			//例句
			['ctype' => TableConst::IDIOM_STRUCT_ENSAMPLE, 'content' => $params['struct_ensample']],

			//典故出处
			['ctype' => TableConst::IDIOM_STRUCT_SOURCE, 'content' => $params['struct_source']],

			//示例
			['ctype' => TableConst::IDIOM_STRUCT_CASE, 'content' => $params['struct_case']],

			//近义词
			['ctype' => TableConst::IDIOM_STRUCT_SYNONYM, 'content' => str_replace('||', "\n", $params['struct_synonym'])],

			//反义词
			['ctype' => TableConst::IDIOM_STRUCT_ANTONYM, 'content' => str_replace('||', "\n", $params['struct_antonym'])],

			//常用程度
			['ctype' => TableConst::IDIOM_STRUCT_DEGREE, 'content' => $params['struct_degree']],

			//感情色彩
			['ctype' => TableConst::IDIOM_STRUCT_EMOTION, 'content' => $params['struct_emotion']],

			//语法用法
			['ctype' => TableConst::IDIOM_STRUCT_GRAMMAR, 'content' => $params['struct_grammar']],

			//成语结构
			['ctype' => TableConst::IDIOM_STRUCT_STRUCT, 'content' => $params['struct_struct']],

			//产生年代
			['ctype' => TableConst::IDIOM_STRUCT_ENRTIME, 'content' => $params['struct_enrtime']],

			//成语正音
			['ctype' => TableConst::IDIOM_STRUCT_PRONOUNCE, 'content' => $params['struct_pronounce']],

			//成语辨形
			['ctype' => TableConst::IDIOM_STRUCT_DISCERN, 'content' => $params['struct_discern']],

			//成语谜面
			['ctype' => TableConst::IDIOM_STRUCT_RIDDLE, 'content' => $params['struct_riddle']],
		];

		return $data;
	}


	

   
}
