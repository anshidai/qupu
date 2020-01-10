<?php
namespace app\admin\controller;

use think\Controller;
use think\facade\Request;
use think\App;

/**
* 基础控制器
*/
class Base extends Controller
{
    public function initialize() 
    {
        header('Content-Type:text/html; charset="utf-8"');
        parent::initialize();

        $module = Request::module(); //模块名
        $controller = strtolower(Request::controller()); //控制器名
        $action = strtolower(Request::action()); //方法名

        $routeInfo = Request::routeInfo(); //路由信息

        //请求路由
        $routeUrl = '/' . strtolower($routeInfo['rule']);

        $this->assign('cname', $controller);
        $this->assign('aname', $action);
        $this->assign('routeUrl', $routeUrl);
    }

    /**
	* 所有空操作会解析到这
    */
    public function _empty($name)
    {
        echo '404 not found';exit;
    }

    /**
	* 错误输出
	* @param string $msg 错误信息
	* @param int $code 错误码
    */
    public function jsonError($msg = 'error', $code = 100000)
    {
    	$json = [
    		'code' => $code,
    		'msg' => $msg,
    		'data' => [],
    	];
    	return json($json);
    }

    /**
	* 成功输出json
	* @param array $data 输出内容
    */
    public function JsonSuccess($data = [], $msg = 'success', $url = '')
    {
    	$json = [
            'code' => 2000,
    		'msg' => $msg,
            'url' => $url,
    		'data' => $data,
    	];
    	return json($json);
    }

    /**
    * 判断是否get请求
    */
    public function isGet()
    {
        return isGet();
    }

    /**
    * 判断是否post请求
    */
    public function isPost()
    {
        return isPost();
    }

    /**
    * 判断是否ajax请求
    */
    public function isAjax()
    {
        return isAjax();
    }


}
