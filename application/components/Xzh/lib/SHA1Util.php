<?php
/**
 * SHA1签名，Server 有效性校验
 *
 * @author xzh
 * @date 2018/3/22 下午10:07
 */

namespace app\components\Xzh\lib;


class SHA1Util
{
    /**
     * sha1 加密
     * @param $strToken
     * @param $intTimeStamp
     * @param $strNonce
     * @param string $strEncryptMsg
     * @return string
     */
    public static function getSHA1($strToken, $intTimeStamp, $strNonce, $strEncryptMsg = '')
    {

        $arrParams = array(
            $strToken,
            $intTimeStamp,
            $strNonce,
        );
        if (!empty($strEncryptMsg)) {
            array_unshift($arrParams, $strEncryptMsg);
        }
        sort($arrParams, SORT_STRING);
        $strParam = implode($arrParams);

        return sha1($strParam);
    }
}