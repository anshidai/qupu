<?php
/**
 * 消息加减密
 *
 * @author xzh
 * @date 2018/4/7 下午9:28
 */

namespace Xzh\Client;


class AesEncryptTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    private $clientId = 'xKn0PYqE5F0OHr3MTQOPedcTEZMH';
    /**
     * @var string
     */
    private $aesKey = 'U8BQbjSYrZnLDBSuUC8DSKwM6vKboLRUqDUPds5uHi6';

    /**
     * @var string
     */
    private $aesToken = '1989xbp';

    /**
     * @var AesEncryptUtil
     */
    private $crypto;

    /**
     * AesEncryptTest constructor.
     * @param null $name
     * @param array $data
     * @param string $dataName
     */
    public function __construct($name = null, array $data = array(), $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->crypto = new AesEncryptUtil($this->clientId, $this->aesKey);
    }

    /**
     * 息加减密-使用示例
     */
    public function testTextMsgEncryptAndDecrypt()
    {
        $encrypt = 'kcNL2Xb42V1u/ex9GbS47I1ZrB1vOeABhOdjqSA5ZtsPTepm7ZzP+GUy5uZooNjhGZR1eeLCB30KT3wGnpPO0+EulFbhYEhcYeOd+dXC6dj+n0/nmcc+B+zs4rMwCZnjt57NxGt+hiP6xSUr8iUDdLsOTHCxA3mvQrBzdh8ilY2V/tum28b86/BCYMCqzPzDL2WW+uVeQbKLR8mNOSyZ94gUnEBf26tyGPqjxWX/bDSUZS0fUGGChz+S6fGaYUama+ovsjdisWHWwzfiFpgouVP+YQmx6j9SKALnxaJtzj3Ck7tlPkceq4s4wKIBiXh5RRLRBU6hZyqJxT5kpDrkcIuUvq8qK/qVImFmjuPkAYSAqZ4XQ++rXuu59u+9m9IX2PxSa375vd0idBAqmLBOEa5kBG7yIgsxoYWnVezvDjRKb0ve6Ii0c/WDb4NQPUxZfuQts69h9by9PfmYDN/snaE1MeP1K0qP2tkwMY/K7fjzvK4ao2uQqOP8dq7LVjhJ';

        // 熊掌号加密消息
//        $dXml = $this->crypto->decrypt($encrypt,true);
        // TP加密消息
        $decryptText = $this->crypto->decrypt($encrypt,false);
        echo 'decrypt: '.$decryptText.PHP_EOL;


        $eXml = $this->crypto->encrypt($decryptText);
        echo 'encrypt: '.$eXml.PHP_EOL;
    }

    /**
     * 消息真实性校验
     */
    public function testXzhServerSign()
    {
        // 从 url get获取
        $msgSignature = '59acc4676c267b4e14ffae6a8bcab2862d02c5d0';
        $timeStamp = 1529633409;
        $nonce = 1617361377;

        // 从 post 获取
        $postData = '<xml><AppId><![CDATA[3NQrxKn0PYqE5F0OHr3MTQOPedcTEZMH]]></AppId><TpClientId><![CDATA[3NQrxKn0PYqE5F0OHr3MTQOPedcTEZMH]]></TpClientId><Encrypt><![CDATA[kcNL2Xb42V1u/ex9GbS47I1ZrB1vOeABhOdjqSA5ZtsPTepm7ZzP+GUy5uZooNjhGZR1eeLCB30KT3wGnpPO0+EulFbhYEhcYeOd+dXC6dj+n0/nmcc+B+zs4rMwCZnjt57NxGt+hiP6xSUr8iUDdLsOTHCxA3mvQrBzdh8ilY2V/tum28b86/BCYMCqzPzDL2WW+uVeQbKLR8mNOSyZ94gUnEBf26tyGPqjxWX/bDSUZS0fUGGChz+S6fGaYUama+ovsjdisWHWwzfiFpgouVP+YQmx6j9SKALnxaJtzj3Ck7tlPkceq4s4wKIBiXh5RRLRBU6hZyqJxT5kpDrkcIuUvq8qK/qVImFmjuPkAYSAqZ4XQ++rXuu59u+9m9IX2PxSa375vd0idBAqmLBOEa5kBG7yIgsxoYWnVezvDjRKb0ve6Ii0c/WDb4NQPUxZfuQts69h9by9PfmYDN/snaE1MeP1K0qP2tkwMY/K7fjzvK4ao2uQqOP8dq7LVjhJ]]></Encrypt></xml>';

        $arrPostData = XmlUtil::xml2Array($postData);
        $decryptText = $arrPostData['Encrypt'];

        $signature = SHA1Util::getSHA1($this->aesToken, $timeStamp, $nonce, $decryptText);
        if ($signature == $msgSignature) {
            echo "msg from xzh platform";
        } else {
            echo "msg from ...";
        }
    }
}