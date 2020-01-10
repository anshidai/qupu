<?php

namespace app\home\controller;

use think\Request;

class Robots extends Base
{
	public function robot()
	{
		$txt = file_get_contents(ROOT_PATH.'/public/robots_pc.txt');
		echo $txt;exit;
	}

}