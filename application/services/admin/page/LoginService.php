<?php

namespace app\services\admin\page;

use think\facade\Request;
use think\facade\Cookie;
use Cache;
use app\components\COM;
use app\Inc\TableConst;
use app\components\helper\StringHelper;
use app\model\permission\AdminUserModel;
use app\services\admin\page\AdminUserService;

/**
* 登录业务处理
*/
class LoginService
{	
	//登录cookie
	const USER_COOKIE = 'qupu_user';

	/**
	* 登录成功后台
	*/
	public static function loginSuccess($userArr, $online = false)
	{
		$json = json_encode([
			'userid' => $userArr['id'],
			'username' => $userArr['name'],
			'org_id' => $userArr['org_id'],
			'org_role' => $userArr['org_role'],
			'sys_admin' => $userArr['sys_admin'],
		]);

		$cookieVal = authcode($json, 'ENCODE', ENCRYPT_KEY);
		$expire = $online? 15*86400: 0;
		Cookie::set(self::USER_COOKIE, $cookieVal, $expire);
	}
	
	/**
	* 解析登录状态
	*/
	public static function parseLogin()
	{
		$cookieVal = authcode(Cookie::get(self::USER_COOKIE), 'DECODE', ENCRYPT_KEY);
		if(empty($cookieVal)) {
			return false;
		}
		
		return json_decode($cookieVal, true);
	}
	
	/**
	* 退出登录
	*/
	public static function loginOut()
	{
		$data = self::parseLogin();
		if (!empty($data['userid'])) {

			//清空权限缓存
			$cacheKey =  COM::getCachekey('admin_user_permis', $data['userid']);
			Cache::rm($cacheKey);
		}
		Cookie::delete(self::USER_COOKIE);
	}

	/**
	* 校验用户信息
	*/
	public static function checkVerifyUserGroup($username, $password)
	{
		$user = AdminUserService::getUserByName($username);	
		if(empty($user)) {
			throw new \Exception('账号或密码错误');
		}elseif($user['status'] != TableConst::ADMIN_STATUS_ENABLED) {
			throw new \Exception('账号异常,请联系管理员');
		}

		//连续登录错误次数
		if($user['login_error_num'] >= 5) {
			throw new \Exception('密码错误次数超过最大限制，请联系管理员');
		}
		
		$hashpwd = createPassword($password, $user['salt']);
		if($user['pwd'] != $hashpwd) {
			self::updateLoginErrorNum($user['id']);
			throw new \Exception('账号或密码错误');
		}
		
		return $user;
	}

	/**
	* 更新登录密码错误次数
	* @param int $uid 用户id
	*/
	public static function updateLoginErrorNum($userid)
	{
		if(empty($userid) || !is_numeric($userid)) {
			return false;
		}

		$userInfo = AdminUserModel::getInfo($userid);
		if($userInfo) {
			$data = array(
				'login_error_num' => $userInfo['login_error_num'] + 1,
			);
			AdminUserModel::_update($userid, $data);
		}

		return true;
	}

	/**
	* 更新登录后状态
	* @param int $userid 用户id
	*/
	public static function updateLogonStatus($userid)
	{
		if(empty($userid) || !is_numeric($userid)) {
			return false;
		}

		$data = [
			'lastip' => Request::ip(),
			'lasttime' => date('Y-m-d H:i:s'),
			'login_error_num' => 0,
		];
		AdminUserModel::_update($userid, $data);

		return true;
	}

}