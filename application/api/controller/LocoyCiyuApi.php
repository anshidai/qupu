<?php

namespace app\api\controller;

use think\Request;
use Cache;
use think\facade\Config;
use app\model\ciyu\WordsModel;
use app\model\ciyu\WordsDataModel;
use app\model\ciyu\WordsStructModel;
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

class LocoyCiyuApi extends Common
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
        $data['struct_synonym'] = $request->post('struct_synonym', ''); //近义词
        $data['struct_antonym'] = $request->post('struct_antonym', ''); //反义词

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

		$resid = $this->parseId($data['cjurl'], $data['domain']);
		if(empty($resid)) {
			echo '无法解析文章ID';exit;
		}

		$indexArr = [
			'url' => $data['cjurl'],
			'resid' => $resid,
			'status' => TableConst::CJ_STATUS_HTTP_SUCCESS,
			'adddate' => date('Y-m-d H:i:s'),
			'ctype' => TableConst::CJ_CTYPE_CIYU,
			'domain' => $data['domain'],
		];

		$isLock = true;
		if($isLock) {
			if (CjIndexModel::existByUrl($data['cjurl'])) {
				echo '采集url已存在';exit;
			}

			$data['title'] = htmlspecialchars_decode($data['title']);
			$titleHash =  md5($data['title']);
			if(WordsModel::checkRowExistByHash($titleHash)) {
				echo '标题已存在';exit;
			}

			//新增采集索引记录
			if($indexId = CjIndexModel::_add($indexArr)) {
				$wordsArr = [
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
					'status' => TableConst::WORDS_STATUS_NOT,
					'is_show' => TableConst::WORDS_SHOW_HIDE,
					'order' => 0,
				];

				$cateInfo = BUCategoryService::getCateInfo($data['catid']);
				if ($cateInfo) {
					$wordsArr['catid'] = $cateInfo['id'];
					$wordsArr['catname'] = $cateInfo['name'];
				}

				$wordsArr['charlen'] = mb_strlen($data['title']);

				//新增
				if($wordid = WordsModel::_add($wordsArr)) {
					$contentArr = [
						'word_id' => $wordid,
						'content' => $this->parseWordsContent($data['content']),
					];
                    WordsDataModel::_add($contentArr);

                    $struct = $this->parseWordsStruct($data);
                    foreach ($struct as $val) {
						if (WordsStructModel::checkRowExist($wordid, $val['ctype'])) {
							WordsStructModel::editStruct($wordid, $val['ctype'], $val['content']);
						} else {
							WordsStructModel::addStruct($wordid, $val['ctype'], $val['content']);
						}
					}

					CjIndexModel::_update($indexId, array('extend'=>$wordid));

					echo '成功';exit;
				}
				unset($_content, $content, $contentArr);
			}
			
			echo '入库失败';exit;

		} else {
			echo '获取锁失败';exit;
		}

    }


    /**
	* 获取分类
	*/
	public function getCategory(Request $request)
	{
		$type = $request->get('type');

		$html = '<select name="catid">';
		if(!in_array($type, [TableConst::CATE_TYPE_CIYU])) {
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

	/**
	* 解析结构
	*/
	protected function parseWordsStruct($params)
	{
		$data = [
			//近义词
			['ctype' => TableConst::WORDS_STRUCT_SYNONYM, 'content' => str_replace('||', "\n", $params['struct_synonym'])],

			//反义词
			['ctype' => TableConst::WORDS_STRUCT_ANTONYM, 'content' => str_replace('||', "\n", $params['struct_antonym'])],
		];

		return $data;
	}

	protected function parseWordsContent($content)
	{
		$data = '';

		$content = str_replace('######', '###', $content);
		$contentArr = explode('###', $content);
		for ($i = 0; $i < count($contentArr); $i++) {
			$contentArr[$i] = trim(strip_tags($contentArr[$i]));
			if (!empty($contentArr[$i])) {
				$data .= '<p>'.$contentArr[$i].'</p>';
			}
		}

		return $data;
	}

	

   
}
