<?php

namespace app\admin\controller;

use think\App;
use think\Request;
use app\components\helper\ArrayHelper;
use app\services\admin\page\LoginService;
use app\services\admin\page\AdminUserService;

/**
* 后台管理
*/
class Admin extends Base
{
    protected static $user = null;
    protected static $isLogin = false;
    protected static $userid = null;
    protected static $username = null;
    protected static $sysadmin = null;

    public static $verifyPermis = null;
    public static $permis = null;

    public function initialize() 
     {
        parent::initialize();

        self::$user = LoginService::parseLogin();
        if(empty(self::$user)) {
            header('Location: /page/login');
        }

        self::$userid = self::$user['userid'];
        self::$username = self::$user['username'];
        self::$sysadmin = self::$user['sys_admin'];

        //超级全局变量
        $GLOBALS['user'] = self::$user;

        //拥有权限
        self::$verifyPermis = AdminUserService::getRolePermis(self::$userid);
        self::$permis = ArrayHelper::toHashmap(self::$verifyPermis['permis'], 'identify');

        $this->assign('user', self::$user);
        $this->assign('verifyPermis', self::$verifyPermis);
        $this->assign('permis', self::$permis);
    }

    public function index()
    {
    	return $this->fetch('welcome');
    }

    public function welcome()
    {
    	return $this->fetch();
    }


    /**
    * 判断访问权限
    * @param string|array $permis 权限code
    */
    protected function checkPermis($permis)
    {
        if (self::$sysadmin) {
            return true;
        }
        
        if (is_string($permis)) {
            $permis = [$permis];
        }

        for ($i = 0; $i < count($permis); $i++) {
            if (empty(self::$permis[$permis[$i]])) {
                if ($this->isPost() || $this->isAjax()) {
                    return $this->jsonError('抱歉没有访问权限');
                } 
                return $this->fetch('public/notpermission');
            }
        }
        return true;
    }

}
