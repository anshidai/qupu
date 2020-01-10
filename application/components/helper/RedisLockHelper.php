<?php 

namespace app\components\helper;

/**
* Redis锁操作类
*/
class RedisLockHelper
{
	private static $_redis;

	/**
	* 初始化
	*/
	public static function init($redisObj)
	{
		if (is_object($redisObj)) {
			self::$_redis = $redisObj;
		}
	}

	/**
	* 获取锁
	*@param string $key 锁标识
    *@param int $expire 锁过期时间
    *@return boolean
	*/
	public static function lock($key, $expire = 5)
	{
		$isLock = self::$_redis->setnx($key, time() + $expire);

		//不能获取锁
		if(!$isLock) {
			//判断锁是否过期
			$lockTime = self::$_redis->get($key);

			//锁已过期，删除锁，重新获取
			if(time() > $lockTime) {
				self::unlock($key);
				$isLock = self::$_redis->setnx($key, time() + $expire);
				self::$_redis->expire($key, $expire);
			}
		}

		return $isLock? true: false;
	}

	/**
    * 释放锁
    *@param string $key 锁标识
    *@return boolean
    */
	public static function unlock($key)
	{
        return self::$_redis->del($key);
    }



}