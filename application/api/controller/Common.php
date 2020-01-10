<?php 

namespace app\api\controller;

use think\Controller;
use think\facade\Request;
use think\App;

class Common extends Controller 
{
    
    protected function initialize()
    {
        header('Content-Type:text/html; charset="utf-8"');
    }
    
    /**
    * 打印输出
    */
	public static function printLog($msg = '')
	{
        if(is_array($msg)) {
            echo date('Y-m-d H:i:s')."\n";
            var_dump($msg)."\n";
        }else {
            echo date('Y-m-d H:i:s')." {$msg}\n"; 
        }
	}
    
}