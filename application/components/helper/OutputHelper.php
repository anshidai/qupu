<?php 

namespace app\components\helper;

use app\components\helper\JsonHelper;

/**
* 输出处理类
*/
class OutputHelper
{
	
	/**
	* ajax json返回正确格式信息
	*/
	public static function ajaxJsonError($msg = '', $url = '', $data = array())
	{
		$json = array(
			'code' => CODE_ERROR,
			'msg' => $msg,
			'url' => $url,
			'data' => $data,
			'timestamp' => time(),
		);
		self::ajaxReturn($json, 'JSON');
	}
	
	/**
	* ajax json返回错误格式信息
	*/
	public static function ajaxJsonSuccess($msg = '', $url = '', $data = array())
	{
		$json = array(
			'code' => CODE_RIGHT,
			'msg' => $msg,
			'url' => $url,
			'data' => $data,
			'timestamp' => time(),
		);
		self::ajaxReturn($json, 'JSON');
	}
	
	/**
	* 输出数据
	* @param array|string $data 输出内容
	* @param string $type 输出格式 JSON|JSONP|XML|EVAL
	*/
	public static function ajaxReturn($data, $type = 'JSON')
	{
		switch(strtoupper($type)) {
			case 'JSON':
			case 'JSONP':
				//返回JSON数据格式
				JsonHelper::echocode($data, true);
				break;
			case 'XML':
				//返回xml格式数据
				header('Content-Type:text/xml; charset=utf-8');
				break;
			case 'EVAL':
				//返回文本
				header('Content-Type:text/html; charset=utf-8');
				exit($data);
			default:
				break;
		}
	}
	
}

