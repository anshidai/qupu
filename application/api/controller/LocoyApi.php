<?php

namespace app\api\controller;

use think\Request;
use Cache;
use think\facade\Config;
use app\model\article\ArticleModel;
use app\model\article\ArticleDataModel;
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

class LocoyApi extends Common
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
        $title = $request->post('title'); //采集标题
        $content = $request->post('content'); //采集内容
        $cjnav = $request->post('cjnav'); //采集面包屑
        $domain = $request->post('domain'); //采集网站简写
        $cjurl = $request->post('url'); //采集url
        $catid = $request->post('catid'); //所属分类

        $title = trim(urldecode($title));
		$cjurl = trim(urldecode($cjurl));
		$content = trim(urldecode($content));
		$cjnav = trim(urldecode($cjnav));
		$cjnav = trim($cjnav, '>');

		$domain = trim(urldecode($domain));
		$catid = trim(urldecode($catid));

		$content = preg_replace('/^<div>/', '', $content);
		$content = preg_replace('/<\/div><BR>$/', '', $content);
		$content = preg_replace('/<\/div>$/', '', $content);
		$content = str_replace('<p></p', '', $content);
		$content = trim($content);

        if(empty($title)) {
			echo '标题不能为空';exit;
		} elseif(empty($content)) {
			echo '内容不能为空';exit;
		} elseif(empty($this->domianIndex[$domain])) {
			echo '网站缩写错误';exit;
		} elseif(empty($cjurl)) {
			echo '采集url不能为空';exit;
		}

		if (mb_strlen(strip_tags($content)) < 150) {
			// echo '采集内容太短';exit;
		}

		$resid = $this->parseId($cjurl, $domain);
		if(empty($resid)) {
			echo '无法解析文章ID';exit;
		}

		$indexArr = [
			'url' => $cjurl,
			'resid' => $resid,
			'status' => TableConst::CJ_STATUS_HTTP_SUCCESS,
			'adddate' => date('Y-m-d H:i:s'),
			'ctype' => TableConst::CJ_CTYPE_ARTICLE,
			'domain' => $domain,
		];

		$isLock = true;
		if($isLock) {
			if (CjIndexModel::existByUrl($cjurl)) {
				echo '采集url已存在';exit;
			}

			$title = htmlspecialchars_decode($title);
			$titleHash =  md5($title);
			if(ArticleModel::checkRowExistByHash($titleHash)) {
				echo '标题已存在';exit;
			}

			//新增采集索引记录
			if($indexId = CjIndexModel::_add($indexArr)) {
				$articleArr = [
					'title' => $title,
					'title_hash' => $titleHash,
					'identify' => createUniqid(),
					'addtime' => date('Y-m-d H:i:s'),
					'edittime' => date('Y-m-d H:i:s'),
					'cjurl' => $cjurl,
					'cjnav' => $cjnav,
					'domain' => $domain,
					'status' => TableConst::ARTICLE_STATUS_NOT,
					'order' => 0,
				];

				$cateInfo = BUCategoryService::getCateInfo($catid);
				if ($cateInfo) {
					$articleArr['catid'] = $cateInfo['id'];
					$articleArr['catname'] = $cateInfo['name'];
				}

				$_content = htmlspecialchars_decode(htmlspecialchars_decode($content));
				$_content = StringHelper::deleteHtmlTag($_content);
				$_content = strip_tags($_content);
				$_content = StringHelper::replaceSpecialNull($_content);
				$articleArr['introduction'] = StringHelper::strSubstrUtf8($_content, 400);
				$articleArr['introduction'] = StringHelper::clearTrim($articleArr['introduction']);

				//新增
				if($articleid = ArticleModel::_add($articleArr)) {
					$contentArr = [
						'article_id' => $articleid,
						'content' => $content,
					];
                    ArticleDataModel::_add($contentArr);

					CjIndexModel::_update($indexId, array('extend'=>$articleid));

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
					$id = $match[1];
					break;
				}
			}
		}

		return $id;
	}


	

   
}
