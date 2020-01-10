<?php
/**
 *  TP 授权操作类
 *
 * @author xzh
 * @date 2018/3/22 下午10:12
 */

namespace app\components\Xzh\lib\Api;


use app\components\Xzh\lib\XzhBase;
use app\components\Xzh\lib\XzhConst;

class TpAuthApi extends XzhBase
{
    /**
     * 获取第三方平台接口调用凭据
     * @param $clientId
     * @param $clientSecret
     * @param $tpVerifyTicket
     * @return array|mixed
     */
    public function getTpAccessTokenByTpCredentials($clientId, $clientSecret, $tpVerifyTicket)
    {
        $params = [
            'grant_type'       => XzhConst::TP_TOKEN_GRANT_TYPE,
            'tp_client_id'     => $clientId,
            'tp_client_secret' => $clientSecret,
            'tp_verify_ticket' => $tpVerifyTicket,
        ];

        return $this->getInvoke(XzhConst::URI_OAUTH_TOKEN, $params);
    }

    /**
     * 熊掌号授权-获取预授权码
     * @param $tpAccessToken
     * @param null $debug
     * @return array
     */
    public function getPreAuthCodeByTpAccessToken($tpAccessToken, $debug = null)
    {
        $params = [
            'tp_access_token' => $tpAccessToken,
        ];

        if(isset($debug) && $debug == 1) {
            $params['debug'] = $debug;
        }

        $url = XzhConst::URI_REST_PREFIXS . '/' . XzhConst::OPENAPI_TP_API_CREATE_PREAUTHCODE;

        return $this->getInvoke($url, $params);
    }

    /**
     * 熊掌号授权-获取预授权码授权链接
     * @param $tpClientId
     * @param $preAuthCode
     * @param $redirectUri
     * @return string
     */
    public function getTpAuthorizeRedirectUrl($tpClientId, $preAuthCode, $redirectUri)
    {
        $params = [
            'tp_client_id'  => $tpClientId,
            'pre_auth_code' => $preAuthCode,
            'redirect_uri'  => $redirectUri,
        ];

        return XzhConst::URI_AUTH_TP . '?' . http_build_query($params);
    }

    /**
     * 熊掌号授权-获取熊掌号accessToken
     * @param $tpAccessToken
     * @param $authorizationCode
     * @return array
     */
    public function getAccessTokenByAuthorizationCode($tpAccessToken, $authorizationCode)
    {
        $params = [
            'tp_access_token'  => $tpAccessToken,
            'authorization_code' => $authorizationCode,
        ];

        $url = XzhConst::URI_REST_PREFIXS . '/' . XzhConst::OPENAPI_TP_API_QUERYAUTH;

        return $this->getInvoke($url, $params);
    }

    /**
     * 熊掌号授权-refresh_token刷新接口调用凭据
     * @param $tpAccessToken
     * @param $refreshToken
     * @return array
     */
    public function getAccessTokenByRefreshToken($tpAccessToken, $refreshToken)
    {
        $params = [
            'tp_access_token'  => $tpAccessToken,
            'refresh_token' => $refreshToken,
        ];

        $url = XzhConst::URI_REST_PREFIXS . '/' . XzhConst::OPENAPI_TP_API_AUTHORIZER_TOKEN;

        return $this->getInvoke($url, $params);
    }

    /**
     * 熊掌号授权-获取授权熊掌号信息
     * @param $accessToken
     * @return array
     */
    public function getAuthorizerInfo($accessToken)
    {
        $params = [
            'access_token' => $accessToken,
        ];

        $url = XzhConst::URI_REST_PREFIXS . '/' . XzhConst::OPENAPI_TP_AUTHORIZER_INFO;

        return $this->getInvoke($url, $params);
    }

    /**
     * 代熊掌号发起网页授权-获取授权码链接
     * @param $tpClientId
     * @param $clientId
     * @param $redirectUri
     * @param $scope
     * @param null $pass_no_login
     * @param null $state
     * @return string
     */
    public function getAuthorizeRedirectUrl($tpClientId, $clientId, $redirectUri, $scope, $pass_no_login = null, $state = null)
    {

        $params = [
            'response_type' => XzhConst::RESPONSE_TYPE,
            'client_id'     => $clientId,
            'redirect_uri'  => $redirectUri,
            'scope'         => $scope,
            'tp_client_id'  => $tpClientId,
        ];

        if($pass_no_login != null) {
            $params['pass_no_login'] = $pass_no_login;
        }

        if($state != null) {
            $params['state'] = $state;
        }

        return XzhConst::URI_AUTHORIZE . '?' . http_build_query($params);
    }

    /**
     * 代熊掌号发起网页授权-获取网页授权accessToken
     * @param $tpClientId
     * @param $tpAccessToken
     * @param $clientId
     * @param $authorizationCode
     * @param $redirectUri
     * @return array
     */
    public function getAccessTokenByTpAuthorizationCode($tpClientId, $tpAccessToken, $clientId, $authorizationCode, $redirectUri)
    {

        $params = [
            'grant_type'      => XzhConst::TP_AUTHORIZATION_CODE,
            'code'            => $authorizationCode,
            'client_id'       => $clientId,
            'tp_client_id'    => $tpClientId,
            'tp_access_token' => $tpAccessToken,
            'redirect_uri'    => $redirectUri,
        ];

        return $this->getInvoke(XzhConst::URI_OAUTH_TOKEN, $params);
    }

    /**
     * 代熊掌号发起网页授权-拉取用户信息
     * @param $accessToken
     * @param $clientId
     * @param $openId
     * @return array
     */
    public function getUserInfoByOpenId($accessToken, $clientId, $openId)
    {

        $params = [
            'access_token'  => $accessToken,
            'client_id'     => $clientId,
            'openid'        => $openId,
        ];

        $url = XzhConst::URI_REST_PREFIXS . '/' . XzhConst::OPENAPI_SNS_USERINFO;

        return $this->getInvoke($url, $params);
    }

    /**
     * 代熊掌号发起网页授权-刷新接口调用凭据
     * @param $tpClientId
     * @param $tpAccessToken
     * @param $clientId
     * @param $refreshToken
     * @return array
     */
    public function getAccessTokenByTpRefreshToken($tpClientId, $tpAccessToken, $clientId, $refreshToken)
    {

        $params = [
            'grant_type'       => XzhConst::TP_REFRESH_CODE,
            'refresh_token'    => $refreshToken,
            'client_id'        => $clientId,
            'tp_client_id'     => $tpClientId,
            'tp_access_token'  => $tpAccessToken,
        ];

        return $this->getInvoke(XzhConst::URI_OAUTH_TOKEN, $params);
    }

}