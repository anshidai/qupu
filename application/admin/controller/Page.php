<?php
namespace app\admin\controller;

use think\App;
use think\Request;
use app\services\admin\page\LoginService;

class Page extends Base
{
    /**
	* 登录
    */
    public function login(Request $request)
    {
        if(LoginService::parseLogin()) {
            header('Location: /admin/welcome');
        }

    	if($this->isPost()) {

       		$username = $request->post('username');
       		$password = $request->post('password');
       		$online = $request->post('online', 0, 'intval');

			try{
				if(empty($username) || empty($password)) {
					throw new \Exception("请填写账号或密码"); 
				}

				$user = LoginService::checkVerifyUserGroup($username, $password);
				LoginService::loginSuccess($user, $online);
				LoginService::updateLogonStatus($user['id']);

				return $this->JsonSuccess();
			}catch(\Exception $e) {
				return $this->jsonError($e->getMessage());
			}
		}

    	return $this->fetch();
    }

    /**
	* 退出
    */
    public function logout(Request $request)
    {
    	LoginService::loginOut();

        header('Location: /');
    }

}
