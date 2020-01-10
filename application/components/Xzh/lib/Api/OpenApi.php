<?php
/**
 * OpenAPI 调用基类
 *
 * @author xzh
 * @date 2018/3/22 下午10:11
 */

namespace app\components\Xzh\lib\Api;


use app\components\Xzh\lib\XzhBase;
use app\components\Xzh\lib\XzhConst;

class OpenApi extends XzhBase
{

    /**
     * @param $api
     * @param $params
     * @param array $headers
     * @param int $connectTimeout
     * @param int $socketTimeout
     * @return array
     */
    public function getInvoke($api, $params, $headers = array(), $connectTimeout = 6000, $socketTimeout = 6000)
    {
        $url = XzhConst::URI_REST_PREFIXS . '/' . $api;
        return parent::getInvoke($url, $params, $headers, $connectTimeout, $socketTimeout);
    }

    /**
     * @param $api
     * @param $data
     * @param $params
     * @param array $headers
     * @param int $connectTimeout
     * @param int $socketTimeout
     * @return array
     */
    public function postInvoke($api, $data, $params, $headers = array(), $connectTimeout = 6000, $socketTimeout = 6000)
    {
        $url = XzhConst::URI_REST_PREFIXS . '/' . $api;
        return parent::postInvoke($url, $data, $params, $headers, $connectTimeout, $socketTimeout);
    }
}