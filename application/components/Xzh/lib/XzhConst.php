<?php
/**
 * SDK 常量类
 *
 * @author xzh
 * @date 2018/3/22 下午10:04
 */

namespace app\components\Xzh\lib;


class XzhConst
{
    /**
     * 获取凭证获取token URI
     */
    const URI_OAUTH_TOKEN = 'https://openapi.baidu.com/oauth/2.0/token';
    /**
     * 获取授权码code URI
     */
    const URI_AUTHORIZE = 'https://openapi.baidu.com/oauth/2.0/authorize';
    /**
     * 熊掌号Rest服务 URI
     */
    const URI_REST_PREFIXS = 'https://openapi.baidu.com/rest/2.0/cambrian';
    /**
     * TP授权 URI
     */
    const URI_AUTH_TP = 'https://openapi.baidu.com/oauth/2.0/tp/login_page';

    /**
     * 熊掌号授权类型
     */
    const TOKEN_GRANT_TYPE = 'client_credentials';
    /**
     * tp授权类型
     */
    const TP_TOKEN_GRANT_TYPE = 'tp_credentials';


    /**
     * 网页授权-返回授权码code
     */
    const RESPONSE_TYPE = 'code';
    /**
     * 网页授权-授权码
     */
    const AUTHORIZATION_CODE = 'authorization_code';
    /**
     * 代熊掌号发起网页授权-授权码
     */
    const TP_AUTHORIZATION_CODE = 'tp_authorization_code';
    /**
     * 更新AccessToken
     */
    const REFRESH_CODE = 'refresh_token';
    /**
     * 代熊掌号发起网页授权-更新AccessToken
     */
    const TP_REFRESH_CODE = 'tp_refresh_token';

    /**
     * 网页授权-获取网页授权用户信息 API
     */
    const OPENAPI_SNS_USERINFO = 'sns/userinfo';

    /**
     * 熊掌号授权-获取预授权码 API
     */
    const OPENAPI_TP_API_CREATE_PREAUTHCODE = 'tp/api_create_preauthcode';
    /**
     * 熊掌号授权-获取熊掌号调用凭据 API
     */
    const OPENAPI_TP_API_QUERYAUTH = 'tp/api_query_auth';
    /**
     * 熊掌号授权-refresh_token刷新接口调用凭据 API
     */
    const OPENAPI_TP_API_AUTHORIZER_TOKEN= 'tp/api_authorizer_token';
    /**
     * 熊掌号授权-获取熊掌号信息 API
     */
    const OPENAPI_TP_AUTHORIZER_INFO= 'tp/api_get_authorizer_info';
}