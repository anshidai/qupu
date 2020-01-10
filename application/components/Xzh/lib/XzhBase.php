<?php
/**
 * 业务基类
 *
 * @author xzh
 * @date 2018/3/22 下午10:05
 */

namespace app\components\Xzh\lib;


use Exception;

class XzhBase
{

    /**
     * @param $path
     * @param $params
     * @param array $headers
     * @param int $connectTimeout
     * @param int $socketTimeout
     * @return array
     * @throws XzhException
     */
    protected function getInvoke($path, $params, $headers=array(), $connectTimeout = 6000, $socketTimeout = 6000)
    {
        try {
            $httpClient = new XzhHttpClient($connectTimeout, $socketTimeout);
            $get = $httpClient->get($path, $params, $headers);
            return $get['content'];
        } catch (Exception $e) {
            throw new XzhException(XzhError::getCusXzhError(XzhError::REQUEST_EXCEPTION_ERROR, $e->getMessage()));
        }
    }

    /**
     * @param $path
     * @param $data
     * @param $params
     * @param int $connectTimeout
     * @param int $socketTimeout
     * @param array $headers
     * @return array
     * @throws XzhException
     */
    protected function postInvoke($path, $data, $params, $headers=array(), $connectTimeout = 6000, $socketTimeout = 6000)
    {
        try {
            $httpClient = new XzhHttpClient($connectTimeout, $socketTimeout);
            $post = $httpClient->post($path, $data, $params, $headers);
            return $post['content'];
        } catch (Exception $e) {
            throw new XzhException(XzhError::getCusXzhError(XzhError::REQUEST_EXCEPTION_ERROR, $e->getMessage()));
        }
    }

}