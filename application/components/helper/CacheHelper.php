<?php 

namespace app\components\helper;

/**
* 缓存处理类
$type redis|memcache|file

用法1：
CM:cache($type)->get($name);
CM:cache($type)->set($name, $value);

用法2：
CacheHelper::instance($type)->get($name);
CacheHelper::instance($type)->set($name, $value);

*/
class CacheHelper
{
	private static $_instance = array();
	
	public static function instance($type)
	{
		if(!isset(self::$_instance[$type]) || !is_object(self::$_instance[$type])) {
			$obj = null;
			switch($type) {
				case 'redis':
					$config = require COMMON_PATH.'/Conf/redis.php';
					$obj = new \Think\Cache\Driver\Redis($config);
					break;
					
				case 'memcache':
					break;
					
				case 'file':
					break;
			}
			self::$_instance[$type] = $obj;
		}
		return self::$_instance[$type];
	}
	
}


