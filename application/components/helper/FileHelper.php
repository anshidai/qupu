<?php 

namespace app\components\helper;

class FileHelper 
{  
	/**
	* 获取文件倒数几行
	*/
	public static function getFileTail($file, $num)
	{
	    $fp = fopen($file, "r");
	    $pos = -2;
	    $eof = '';
	    $head = false; //当总行数小于Num时，判断是否到第一行了
	    $lines = [];
	    while($num > 0) {
	        while($eof != "\n") {
	            if(fseek($fp, $pos, SEEK_END) == 0) { //fseek成功返回0，失败返回-1
	                $eof = fgetc($fp);
	                $pos--;
	            } else { //当到达第一行，行首时，设置$pos失败
	                fseek($fp, 0, SEEK_SET);
	                $head = true; //到达文件头部，开关打开
	                break;
	            }
	        }
	        array_unshift($lines,fgets($fp));
	        if($head) { 
	        	break;
	        }  
	        //这一句，只能放上一句后，因为到文件头后，把第一行读取出来再跳出整个循环
	        $eof = '';
	        $num--;
	    }

	    fclose($fp);
	    return $lines;
	}
}