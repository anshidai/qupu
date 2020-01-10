<?php
/**
 * TP授权相关测试
 *
 * @author xzh
 * @date 2018/3/22 下午10:48
 */

namespace Xzh\Client;


use Xzh\Client\Api\TpAuthApi;

class TpAuthApiTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    private $tpClientId = 'NQrxKn0PYqE5F0OHr3MTQOPedcTEZMH';
    /**
     * @var string
     */
    private $tpClientSecret = 'b7nfYWBz5CPnD6U9bdSrnwPIdwc0NlX1';
    /**
     * @var string
     */
    private $tpAccesstoken = "42.83226fea51f7c0f951fee462ed2d7ed5.7200.1522302983.b_IRxFtoseaolnP3PMNi-lrNE2qyfWx6W3SesXh";
    /**
     * @var TpAuthApi
     */
    private $auth;

    /**
     * TpAuthApiTest constructor.
     * @param null $name
     * @param array $data
     * @param string $dataName
     */
    public function __construct($name = null, array $data = array(), $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->auth = new TpAuthApi();
    }

    /**
     * 获取第三方平台接口调用凭据
     */
    public function testGetTpAccessTokenByTpCredentials()
    {
        $tpVerifyTicket = 'ba9c20f0db6ffb52dd968c6c6266f39d';
        $ret = $this->auth->getTpAccessTokenByTpCredentials($this->tpClientId, $this->tpClientSecret, $tpVerifyTicket);
        $this->logger($ret);
    }

    /**
     * 熊掌号授权-获取预授权码
     */
    public function testGetPreAuthCodeByTpAccessToken()
    {
        //$debug = 1;
        $preAuthCode = $this->auth->getPreAuthCodeByTpAccessToken($this->tpAccesstoken, null);
        $this->logger($preAuthCode);
    }

    /**
     * 熊掌号授权-获取预授权码授权链接
     */
    public function testGetTpAuthorizeRedirectUrl()
    {
        $preAuthCode = "PREAUTHCODE@@@dxgLsxA3sqbwYSA7GmZGxepcGTKRGG6biX7cuvcZwN1WefB5un3kVLs4UBfg0IkH";
        $redirectUri = "http://domain8080/xzhsdk/tp.php";
        $tpAuthorizeRedirectUrl = $this->auth->getTpAuthorizeRedirectUrl($this->tpClientId, $preAuthCode, $redirectUri);
        $this->logger($tpAuthorizeRedirectUrl);
    }

    /**
     * 熊掌号授权-获取熊掌号accessToken
     */
    public function testGetAccessTokenByAuthorizationCode()
    {
        $authorizationCode = "mzs8kqJ623xEZz14rswLqw8ZvfSfkRqfIzEfmR9yAsv4TgzL8Tm9yn7gkRYfvZ6w";
        $accessTokenByAuthorizationCode = $this->auth->getAccessTokenByAuthorizationCode($this->tpAccesstoken, $authorizationCode);
        $this->logger($accessTokenByAuthorizationCode);
    }

    /**
     * 熊掌号授权-refresh_token刷新接口调用凭据
     */
    public function testGetAccessTokenByRefreshToken()
    {
        $refreshToken = "46.4edbbcd55b98195fad7437fac3eb79e3.315360000.1837656807.WFX8yCH-uhS8iBMhjgF8q9UaeqHs-lrNE2qyfWx6W3SesXh";
        $accessTokenByRefreshToken = $this->auth->getAccessTokenByRefreshToken($this->tpAccesstoken, $refreshToken);
        $this->logger($accessTokenByRefreshToken);
    }

    /**
     * 熊掌号授权-获取授权熊掌号信息
     */
    public function testGetAuthorizerInfo()
    {
        $accessToken ="45.b181336622ae020304e63ec30e0dbc97.7200.1522304207.W8LIZ8_0Jz2-JCIcRRnq5dHg7KhF-lrNE2qyfWx6W3SesXh";
        $authorizerInfo = $this->auth->getAuthorizerInfo($accessToken);
        $this->logger($authorizerInfo);
    }

    /**
     * 代熊掌号发起网页授权-获取授权码链接
     */
    public function testGetAuthorizeRedirectUrl()
    {
        $clientId = "R6HzvBSGAvkFMUrhELUZayfH2No86t1k";
        $redirectUri = "http://domain8080/xzhsdk/tp.php";
        $scope = "snsapi_userinfo";
        $state = "dtest=1";
        $authorizeRedirectUrl = $this->auth->getAuthorizeRedirectUrl($this->tpClientId, $clientId, $redirectUri, $scope,null,$state);
        $this->logger($authorizeRedirectUrl);
    }

    /**
     * 代熊掌号发起网页授权-获取网页授权accessToken
     */
    public function testGetAccessTokenByTpAuthorizationCode()
    {
        $clientId = "R6HzvBSGAvkFMUrhELUZayfH2No86t1k";
        $authorizationCode = "13eb882873ecd9124d3d3fd9ed19269e";
        $redirectUri = "http://domain8080/xzhsdk/tp.php";
        $accessTokenByTpAuthorizationCode = $this->auth->getAccessTokenByTpAuthorizationCode($this->tpClientId, $this->tpAccesstoken, $clientId, $authorizationCode, $redirectUri);
        $this->logger($accessTokenByTpAuthorizationCode);
    }

    /**
     * 代熊掌号发起网页授权-拉取用户信息
     */
    public function testGetUserInfoByOpenId()
    {
        $accessToken = "43.d1cb136b6c05882b53c38a5945a15bac.7200.1522308639.U6YMSycskdxAjD0sbYN_svXeYETZCBFfiJA-lrNE2qyfWx6W3SesXh";
        $clientId = "R6HzvBSGAvkFMUrhELUZayfH2No86t1k";
        $openId = "36GetTfdgWREIDdMUpz_RoM_c2";
        $userInfo = $this->auth->getUserInfoByOpenId($accessToken, $clientId, $openId);
        $this->logger($userInfo);
    }

    /**
     * 代熊掌号发起网页授权-刷新接口调用凭据
     */
    public function testGetAccessTokenByTpRefreshToken()
    {
        $clientId = "R6HzvBSGAvkFMUrhELUZayfH2No86t1k";
        $refreshToken = "44.37f90e3c98f58d92d9df112da8b64a5e.2592000.1524893876.ecM8ha76HF6FEoROp1XtjfLR97KbxjEoc_Y-lrNE2qyfWx6W3SesXh";
        $accessTokenByTpRefreshToken = $this->auth->getAccessTokenByTpRefreshToken($this->tpClientId,$this->tpAccesstoken,$clientId,$refreshToken);
        $this->logger($accessTokenByTpRefreshToken);
    }

    /**
     * 打印日志
     * @param $result
     */
    private function logger($result)
    {
        var_dump($result);
    }
}