<?php 

namespace app\services\common;

require_once APP_PATH.'components/Upyun/vendor/autoload.php';

use app\components\helper\ArrayHelper;
use app\components\helper\StringHelper;
use app\components\helper\DirHelper;
use Upyun\Upyun;
use Upyun\Config;

class BUUpyun
{
	private static $upyunObj = null;

	/**
	* 实例化又拍云对象
	*/
	public static function createUpyunObj()
	{
		if(empty(self::$upyunObj)) {
			$config = new Config('down-ciyu', 'libaoan', '%%libaoan!@#');
			self::$upyunObj = new Upyun($config);
		}
		return self::$upyunObj;
	}

	/**
	* 上传图片
	* @param string $sourceFile 图片源文件地址
	* @param string $upFile 上传到又拍云文件地址
	*/
	public static function uploadImg($sourceFile, $upFile)
	{
		if(!file_exists($sourceFile) || empty($upFile)) {
			return false;
		}
		self::createUpyunObj();

		$imgcontent = file_get_contents($sourceFile);
		$res = self::$upyunObj->write($upFile, $imgcontent); //上传图片到又拍云
		return $res;
	}


}