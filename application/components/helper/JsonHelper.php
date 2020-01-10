<?php 

namespace app\components\helper;

/**
* json处理类
*/
class JsonHelper
{
	/**
	* 输出json数据
	* @param string|array $data 待生成json内容
	* @param bool $isexit 是否结束程序
	*/
	public static function echocode($data, $isexit = true)
	{
		header('Content-Type: application/json');
		
		$json = self::encode($data);
		if(isset($_GET['callback'])) {
			$json = $_GET['callback'].'('.$json.')';
		}
		if($isexit) {
			exit($json);
		}
		return $json;
	}
	
	/**
	* 数据格式化成json
	* @param string|array $value 待生成json内容
	* @return string json字符
	*/
	public static function encode($value, $options = 320)
	{
		return json_encode($value, $options);
	}
	
	/**
	* 解析json数据
	* @param string $json 解析的数据
	* @param bool $asArray 结果是否返回数组格式
	*/
	public static function decode($json, $asArray = true)
	{
		return json_decode($json, $asArray);
	}
	
	public static function htmlEncode($value)
	{
		return self::encode($value, JSON_UNESCAPED_UNICODE | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);
	}

}


