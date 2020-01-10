<?php
/**
 * SDK Error
 *
 * @author xzh
 * @date 2018/3/22 下午10:03
 */

namespace app\components\Xzh\lib;


class XzhError
{

    /**
     * 错误码
     */
    const PARAMS_ERROR = 'SDK1000';
    const REQUEST_EXCEPTION_ERROR = 'SDK1001';
    const AES_ENCRYPT_ERROR = 'SDK1002';
    const AES_DECRYPT_ERROR = 'SDK1003';
    const AES_DECRYPT_XML_ILLEGAL_ERROR = 'SDK1004';
    const AES_DECRYPT_CLIENTID_ERROR = 'SDK1005';

    /**
     * 错误码说明，关系对
     * @var array
     */
    private static $xzhErrors = [
        self::PARAMS_ERROR => '参数错误',
        self::REQUEST_EXCEPTION_ERROR => '请求发生异常错误',
        self::AES_ENCRYPT_ERROR => 'AES签名错误',
        self::AES_DECRYPT_CLIENTID_ERROR => 'AesEncryptUtil 校验ClientID失败;'
    ];

    /**
     * 错误码属性
     * @var
     */
    private $errorCode;

    /**
     * 错误说明属性
     * @var
     */
    private $errorMsg;

    /**
     * XzhError constructor.
     * @param $errorCode
     * @param $errorMsg
     */
    public function __construct($errorCode, $errorMsg)
    {
        $this->errorCode = $errorCode;
        $this->errorMsg = $errorMsg;
    }

    /**
     * @param $code
     * @return mixed
     */
    public static function getXzhError($code)
    {
        $errorMsg = '';
        if (isset(self::$xzhErrors[$code])) {
            $errorMsg = self::$xzhErrors[$code];
        }
        return new XzhError($code, $errorMsg);
    }

    /**
     * @param $code
     * @param $cusErrorMsg
     * @return XzhError
     */
    public static function getCusXzhError($code, $cusErrorMsg)
    {
        return new XzhError($code, $cusErrorMsg);
    }

    /**
     * @return mixed
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }

    /**
     * @return mixed
     */
    public function getErrorMsg()
    {
        return $this->errorMsg;
    }
}