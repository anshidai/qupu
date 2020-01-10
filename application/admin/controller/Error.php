<?php

namespace app\admin\controller;

use think\App;
use think\Request;
use app\admin\model\bu\BUAmPage;

/**
* 错误处理
*/
class Error extends Base
{
	/**
	* 空操作
	*/
	public function _empty()
	{
		echo 'error';exit;
	}
  



}