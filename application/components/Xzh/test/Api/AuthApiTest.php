<?php
/**
 * 熊掌号授权相关测试
 *
 * @author xzh
 * @date 2018/3/22 下午10:17
 */

namespace Xzh\Client;


use Xzh\Client\Api\AuthApi;

class AuthApiTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    private $clientId = '6HzvBSGAvkFMUrhELUZayfH2No86t1k';
    /**
     * @var string
     */
    private $clientSecret = 'umHZ1Wd33IYPr1zHXLKqXvHMGXCgdPRO';
    /**
     * @var AuthApi
     */
    private $auth;

    /**
     * AuthApiTest constructor.
     * @param null $name
     * @param array $data
     * @param string $dataName
     */
    public function __construct($name = null, array $data = array(), $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->auth = new AuthApi();
    }

    /**
     * 获取熊掌号调用凭证
     */
    public function testGetAccessTokenByClientCredentials()
    {
        $ret = $this->auth->getAccessTokenByClientCredentials($this->clientId, $this->clientSecret);
        $this->logger($ret);
    }

    /**
     * 网页授权-获取预授权码链接
     */
    public function testGetAuthorizeRedirectUrl()
    {
        $redirectUri = 'http://domain8080/wx2.php';
        $scope = 'snsapi_userinfo';
        $state = 'dtest=1';
        $url = $this->auth->getAuthorizeRedirectUrl($this->clientId, $redirectUri, $scope, null, $state);
        $this->logger($url);
    }

    /**
     * 网页授权-根据授权码获取用户accessToken
     */
    public function testGetAccessTokenByAuthorizationCode()
    {
        $authorizationCode = 'bf3d1aa6b8657701cf480bc34dcff926';
        $redirectUri = 'http://domain8080/wx2.php';
        $ret = $this->auth->getAccessTokenByAuthorizationCode($this->clientId, $this->clientSecret, $authorizationCode, $redirectUri);
        $this->logger($ret);
    }

    /**
     * 网页授权-根据openId获取用户信息
     */
    public function testGetUserInfoByOpenId()
    {
        $accessToken = '40.d95c0aa73f901d168f71f341a9518ed9.7200.1521462425.XRGT5LZtLOlgSOeoylABdrnajqM-36GetTS5Gr6hZ9Fscj';
        $openId = '36GetTfdgWREIDdMUpz_RoM_c2';
        $ret = $this->auth->getUserInfoByOpenId($accessToken, $openId);
        $this->logger($ret);
    }

    /**
     * 网页授权-根据refreshAccessToken获取AccessToken
     */
    public function testGetAccessTokenByRefreshToken()
    {
        $refreshToken = '41.1040cc0b78628f821bffa1ce4a097e84.2592000.1524047225.XRGT5LZtLOlgSOeoylABdrnajqM-36GetTS5Gr6hZ9Fscj';
        $ret = $this->auth->getAccessTokenByRefreshToken($this->clientId, $this->clientSecret, $refreshToken);
        $this->logger($ret);
    }

    /**
     * 打印日志
     * @param $result
     */
    private function logger($result) {
        var_dump($result);
    }
}