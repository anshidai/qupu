<?php
/**
 * 熊掌号授权操作类
 *
 * @author xzh
 * @date 2018/3/22 下午10:10
 */

namespace app\components\Xzh\lib\Api;

use app\components\Xzh\lib\XzhBase;
use app\components\Xzh\lib\XzhConst;

class AuthApi extends XzhBase
{
    /**
     * 获取熊掌号调用凭据accessToken
     * @param $clientId
     * @param $clientSecret
     * @return array
     */
    public function getAccessTokenByClientCredentials($clientId, $clientSecret)
    {
        $params = [
            'grant_type' => XzhConst::TOKEN_GRANT_TYPE,
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
        ];

        return $this->getInvoke(XzhConst::URI_OAUTH_TOKEN, $params);
    }

    /**
     * 网页授权-获取授权码链接
     * @param $clientId
     * @param $redirectUri
     * @param $scope
     * @param null $pass_no_login
     * @param null $state
     * @return string
     */
    public function getAuthorizeRedirectUrl($clientId, $redirectUri, $scope, $pass_no_login = null, $state = null)
    {

        $params = [
            'response_type' => XzhConst::RESPONSE_TYPE,
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'scope' => $scope,
        ];

        if ($pass_no_login != null) {
            $params['pass_no_login'] = $pass_no_login;
        }

        if ($state != null) {
            $params['state'] = $state;
        }

        return XzhConst::URI_AUTHORIZE . '?' . http_build_query($params);
    }

    /**
     * 网页授权-根据授权码获取用户accessToken
     * @param $clientId
     * @param $clientSecret
     * @param $authorizationCode
     * @param $redirectUri
     * @return array|mixed
     */
    public function getAccessTokenByAuthorizationCode($clientId, $clientSecret, $authorizationCode, $redirectUri)
    {

        $params = [
            'grant_type' => XzhConst::AUTHORIZATION_CODE,
            'code' => $authorizationCode,
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'redirect_uri' => $redirectUri,
        ];

        return $this->getInvoke(XzhConst::URI_OAUTH_TOKEN, $params);
    }

    /**
     * 网页授权-根据openId获取用户信息
     * @param $accessToken
     * @param $openId
     * @return array|mixed
     */
    public function getUserInfoByOpenId($accessToken, $openId)
    {

        $params = [
            'access_token' => $accessToken,
            'openid' => $openId,
        ];

        $url = XzhConst::URI_REST_PREFIXS . '/' . XzhConst::OPENAPI_SNS_USERINFO;

        return $this->getInvoke($url, $params);
    }

    /**
     * 网页授权-根据refreshAccessToken获取AccessToken
     * @param $clientId
     * @param $clientSecret
     * @param $refreshToken
     * @return array|mixed
     */
    public function getAccessTokenByRefreshToken($clientId, $clientSecret, $refreshToken)
    {

        $params = [
            'grant_type' => XzhConst::REFRESH_CODE,
            'refresh_token' => $refreshToken,
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
        ];

        return $this->getInvoke(XzhConst::URI_OAUTH_TOKEN, $params);
    }

}