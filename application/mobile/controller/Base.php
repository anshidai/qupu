<?php
namespace app\mobile\controller;

use think\Controller;
use think\facade\Request;
use think\App;
use app\services\common\BUCom;

/**
* 基础控制器
*/
class Base extends Controller
{
    public function initialize() 
    {
        header('Content-Type:text/html; charset="utf-8"');
        parent::initialize();
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
    * 检测ip地址是否北京、上海
    * @return bool true是 false不是
    */
    protected function checkIpAdrr()
    {
        $location = BUCom::getClientRealIpAddr();

        $is = false;
        if (!empty($location)) {
            // if (strpos($location['country'], '北京') !== false || strpos($location['country'], '上海') !== false) {
            //     $is = true;
            // }

            // $hour = date('G', time());
            // if (strpos($location['country'], '成都') !== false && ($hour >= 8 && $hour <= 19)) {
            //     $is = true;
            // }

            if (strpos($location['country'], '北京') !== false) {
                $is = true;
            }

        }

        return $is;
    }


}
