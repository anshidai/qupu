<?php

namespace app\components;

/**
* 公用类
*/
class COM
{
	/**
	 * 获取缓存key
	 * @param string $name 缓存键名
	 * @param string|array $params 键名需要替换参数
	 * @return string
	 */
	public static function getCachekey($name, $params = '')
	{
		$cachelist = require ROOT_PATH.'/config/cachekey.php';
		$key = !empty($cachelist[$name])? $cachelist[$name]: '';
		if($key && $params) {
			if(!is_array($params)) {
				$params = array($params);
			}

			foreach($params as &$vo) {
				if(is_array($vo) || is_object($vo)) {
					$vo = md5(json_encode($vo));
				}
			}
			$key = call_user_func_array('sprintf', array_merge(array($key), $params));
			$key = $name .'_'.md5($key);
		}
		return $key;
	}
	
	/**
	* 发送HTTP状态
	* @param int $code 状态码
	* @return string
	*/
	public static function sendHttpStatus($code) 
	{
		static $_status = array(
				// Informational 1xx
				100 => 'Continue',
				101 => 'Switching Protocols',
				// Success 2xx
				200 => 'OK',
				201 => 'Created',
				202 => 'Accepted',
				203 => 'Non-Authoritative Information',
				204 => 'No Content',
				205 => 'Reset Content',
				206 => 'Partial Content',
				// Redirection 3xx
				300 => 'Multiple Choices',
				301 => 'Moved Permanently',
				302 => 'Moved Temporarily ',  // 1.1
				303 => 'See Other',
				304 => 'Not Modified',
				305 => 'Use Proxy',
				// 306 is deprecated but reserved
				307 => 'Temporary Redirect',
				// Client Error 4xx
				400 => 'Bad Request',
				401 => 'Unauthorized',
				402 => 'Payment Required',
				403 => 'Forbidden',
				404 => 'Not Found',
				405 => 'Method Not Allowed',
				406 => 'Not Acceptable',
				407 => 'Proxy Authentication Required',
				408 => 'Request Timeout',
				409 => 'Conflict',
				410 => 'Gone',
				411 => 'Length Required',
				412 => 'Precondition Failed',
				413 => 'Request Entity Too Large',
				414 => 'Request-URI Too Long',
				415 => 'Unsupported Media Type',
				416 => 'Requested Range Not Satisfiable',
				417 => 'Expectation Failed',
				// Server Error 5xx
				500 => 'Internal Server Error',
				501 => 'Not Implemented',
				502 => 'Bad Gateway',
				503 => 'Service Unavailable',
				504 => 'Gateway Timeout',
				505 => 'HTTP Version Not Supported',
				509 => 'Bandwidth Limit Exceeded'
		);
		if(isset($_status[$code])) {
			header('HTTP/1.1 '.$code.' '.$_status[$code]);
			// 确保FastCGI模式下正常
			header('Status:'.$code.' '.$_status[$code]);
		}
	}
	
}