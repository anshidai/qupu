<?php

namespace app\console\command;

use think\Request;
use think\console\Command;
use think\console\Input;
use think\console\Output;


class Base extends Command
{
     /**
    * 打印输出
    */
	public static function printLog($msg = '', $file = '', $isecho = true)
	{
		if (is_array($msg)) {
			$msg = json_encode($msg);
		}

		if ($isecho) {
			echo $msg. "\t" .date('Y-m-d H:i:s')."\n";
		}

		if ($file) {
			file_put_contents($file, $msg."\n", FILE_APPEND);
		}
	}

	/**
    * 校验xml文件大小
    */
    protected function getXmlFileSize($xmlfile = '')
    {   
        if (empty($xmlfile)) {
            return 0;
        }

        clearstatcache();
        return filesize($xmlfile);
    }

}
