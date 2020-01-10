<?php
/**
 *  XML Array 转换工具
 *
 * @author xzh
 * @date 2018/3/22 下午10:07
 */

namespace app\components\Xzh\lib;


class XmlUtil
{
    /**
     * @param $xmlStr
     * @return array
     */
    public static function xml2Array($xmlStr)
    {
        return (array)simplexml_load_string($xmlStr, 'SimpleXMLElement', LIBXML_NOCDATA);
    }

    /**
     * 将数组转为xml格式
     * @param $data
     * @return string
     */
    public static function array2Xml($data)
    {
        $xml   = '<xml>';
        $xml   .= self::arrayFormat2Xml($data);
        $xml   .= '</xml>';
        return $xml;
    }

    /**
     * 数组格式化为 XML片段
     * @param $data
     * @return string
     */
    private static function arrayFormat2Xml($data)
    {
        $xml = '';
        foreach ($data as $key => $val) {
            is_numeric($key) && $key = "item id=\"$key\"";
            $xml    .=  "<$key>";
            $xml    .=  (is_array($val) || is_object($val)) ? self::arrayFormat2Xml($val)  : self::xmlSafeStr($val);
            list($key, ) = explode(' ', $key);
            $xml    .=  "</$key>";
        }

        return $xml;
    }

    /**
     * @param $str
     * @return string
     */
    private static function xmlSafeStr($str)
    {
        return '<![CDATA['.preg_replace("/[\\x00-\\x08\\x0b-\\x0c\\x0e-\\x1f]/",'',$str).']]>';
    }
}