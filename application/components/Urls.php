<?php

namespace app\components;

/**
* url类
*/
class Urls
{
	/**
    * url规则
    * 格式：控制器名_方法 => 映射url
    * 域名常量使用方法：
        格式: {常量名},  如: {DOMAIN}
    * 动态参数使用方法：
        格式: %s 或 %d, 如: {DOMAIN}/detail-%d.html
    */
    protected static $rules = array(
        'index' => '{WWW_DOMAIN}',
        'home_ciyu_category' => '{WWW_DOMAIN}/ciyu/', //列表-分类
        'home_ciyu_category-cid' => '{WWW_DOMAIN}/ciyu/%s/', //列表-分类
        'home_ciyu_category-pinyin' => '{WWW_DOMAIN}/ciyu/%s/', //拼音列表-分类
		'home_ciyu_detail' => '{WWW_DOMAIN}/ciyu/%s.html', //详情页

        'home_chengyu_category' => '{WWW_DOMAIN}/chengyu/', //列表-分类
        'home_chengyu_category-cid' => '{WWW_DOMAIN}/chengyu/%s/', //列表-分类
        'home_chengyu_category-pinyin' => '{WWW_DOMAIN}/chengyu/%s/', //拼音列表-分类
        'home_chengyu_detail' => '{WWW_DOMAIN}/chengyu/%s.html', //详情页

        /************************ m站 ************************/
        'mobile_index' => '{MOBILE_DOMAIN}',
        'mobile_ciyu_category' => '{MOBILE_DOMAIN}/ciyu/', //词语列表-分类
        'mobile_ciyu_category-cid' => '{MOBILE_DOMAIN}/ciyu/%s/', //词语列表-分类
        'mobile_ciyu_category-pinyin' => '{MOBILE_DOMAIN}/ciyu/%s/', //词语拼音列表-分类
        'mobile_ciyu_detail' => '{MOBILE_DOMAIN}/ciyu/%s.html', //词语详情页

        'mobile_chengyu_category' => '{MOBILE_DOMAIN}/chengyu/', //成语列表-分类
        'mobile_chengyu_category-cid' => '{MOBILE_DOMAIN}/chengyu/%s/', //成语列表-分类
        'mobile_chengyu_category-pinyin' => '{MOBILE_DOMAIN}/chengyu/%s/', //成语拼音列表-分类
        'mobile_chengyu_detail' => '{MOBILE_DOMAIN}/chengyu/%s.html', //成语详情页
    );
    
    /**
    * 输出url
    * @param string $name url映射键名
    * @param array $arguments 动态参数
    * @param string $domain 域名
    */
    public static function url($name, $arguments = array(), $domain = '')
    {
        $name = strtolower($name);
        if(!isset(self::$rules[$name])) return '';
        
        if(!is_array($arguments)) {
            $arguments = array($arguments);
        }
        $url = self::$rules[$name];
		
		//设置参数大于实际传入参数，则用空元素填充缺省传入参数
		if(preg_match_all('/%[%bcdeEufFgGosxX]/', $url, $matchNum)) {
			if(count($matchNum[0])>count($arguments)) {
				for($i=0; $i<=(count($matchNum[0])-count($arguments)); $i++) {
					array_push($arguments, '');
				}
			}
		}
        if(preg_match('/\{(.*)\}/', $url, $match)) {
            $url = str_replace($match[0], '', $url);
            if(defined($match[1])) {
                $url = constant($match[1]).$url;
            }
        }
        if(!empty($arguments)) {
            $url = call_user_func_array('sprintf', array_merge(array($url), $arguments));
        }
        if($domain && strpos($url, 'http://') === false) {
            $domain = strpos($domain, 'http://') === false? 'http://'.$domain: $domain;
            $url = rtrim($domain, '/').'/'.ltrim($url, '/');
        }

        return $url;
    }

    /**
    * 获取当前页面url
    */
    public static function curPageURL() 
    {
      $pageURL = 'http';
      if($_SERVER["HTTPS"] == 'on') {
        $pageURL .= 's';
      }
      $pageURL .= '://';
     
      if($_SERVER["SERVER_PORT"] != '80') {
        $pageURL .= $_SERVER["SERVER_NAME"] . ':' . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
      } else {
        $pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
      }
      return $pageURL;
    }

    /**
    * 获取mp3链接
    */
    public static function getMp3Url($url)
    {
        if (empty($url)) {
            return '';
        }

        if (strpos($url, 'http') !== false) {
            return $url;
        }

        return CSN_DOMAIN.'/'.$url;
    }



}