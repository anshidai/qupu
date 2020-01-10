<?php

namespace app\services\common;

use think\facade\Request;
use app\components\helper\StringHelper;
use app\components\helper\PaginationHelper;
use app\components\helper\IpLocationHelper;

/**
* 通用业务处理
*/
class BUCom
{	
    /**
    * 替换xml内容特殊符号
    */
    public static function replaceXmlSymbol($content)
    {
        $findStr = ['&nbsp;', '&quot;', '&amp;','&lt;','&gt','&rdquo;','&ldquo;','&hellip;','&mdash;','&middot;','&#39;'];
        $replaceStr = [' ', '"', '&', '<', '>', '”', '“', '…', '—','·',"'"];
        $content = str_replace($findStr, $replaceStr, $content);
        $content = trim($content);

        return $content;
    }

    /**
    * 过滤content
    */
    public static function htmlspecialStr($str)
    {
        if (empty($str)) {
            return $str;
        }
        $str = htmlspecialchars_decode(htmlspecialchars_decode($str));
        $str = self::replaceXmlSymbol($str);
        $str = StringHelper::filterUtf8($str); //过滤非utf8字符

        return $str;
    }

    /**
    * 根据用户ip地址返回ip信息
    */
    public static function getClientRealIpAddr()
    {
        $ipdata = ROOT_PATH.'/application/Inc/qqwry.dat';

        $ip = Request::ip();
        //$ip = '114.246.63.218';

        $iplocation = new IpLocationHelper($ipdata);  
        $location = $iplocation->getlocation($ip);

        return $location;
    }


    /**
    * 获取最后几位数字
    */
    public static function getCharLastNum($str)
    {
        $char = '';
        for ($i = 0; $i < strlen($str); $i++) {
            if (is_numeric($str{$i}) && $str{$i} >0 && $str{$i} < 10) {
                $char .= $str{$i};
            }
        }
        
        $char = substr($char, -2);

        return $char;
    }
	

}