<?php
/**
 * SDK Http 工具
 *
 * @author xzh
 * @date 2018/3/22 下午9:59
 */

namespace app\components\Xzh\lib;

use Exception;

class XzhHttpClient
{
    private $connectTimeout;
    private $socketTimeout;
    private $headers;

    /**
     * XzhHttpClient constructor.
     * @param int $connectTimeout
     * @param int $socketTimeout
     * @param array $headers
     */
    public function __construct($connectTimeout = 6000, $socketTimeout = 6000, $headers = array())
    {
        $this->connectTimeout = $connectTimeout;
        $this->socketTimeout = $socketTimeout;
        $this->headers = $headers;
    }

    /**
     * 连接超时
     * @param $ms
     */
    public function setConnectionTimeoutInMillis($ms)
    {
        $this->connectTimeout = $ms;
    }

    /**
     * 响应超时
     * @param $ms
     */
    public function setSocketTimeoutInMillis($ms)
    {
        $this->socketTimeout = $ms;
    }

    /**
     * @param $url
     * @param array $data
     * @param array $params
     * @param array $headers
     * @return array
     * @throws Exception
     */
    public function post($url, $data=array(), $params=array(), $headers=array())
    {
        $url = $this->buildUrl($url, $params);
        $headers = array_merge($this->headers, $this->buildHeaders($headers));

        $ch = curl_init();
        if (class_exists('CURLFile')) {
            curl_setopt($ch, CURLOPT_SAFE_UPLOAD, true);
        } else {
            if (defined('CURLOPT_SAFE_UPLOAD')) {
                curl_setopt($ch, CURLOPT_SAFE_UPLOAD, false);
            }
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, $this->socketTimeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, $this->connectTimeout);
        $content = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if($code === 0) {
            throw new Exception(curl_error($ch));
        }

        curl_close($ch);
        return array(
            'code' => $code,
            'content' => $content,
        );
    }

    /**
     * @param $url
     * @param array $params
     * @param array $headers
     * @return array
     * @throws Exception
     */
    public function get($url, $params=array(), $headers=array())
    {
        $url = $this->buildUrl($url, $params);
        $headers = array_merge($this->headers, $this->buildHeaders($headers));

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, $this->socketTimeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, $this->connectTimeout);
        $content = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if($code === 0) {
            throw new Exception(curl_error($ch));
        }

        curl_close($ch);
        return array(
            'code' => $code,
            'content' => $content,
        );
    }

    /**
     * @param $headers
     * @return array
     */
    private function buildHeaders($headers)
    {
        $result = array();
        if(empty($headers)) {
            return $result;
        }

        foreach($headers as $k => $v){
            $result[] = sprintf('%s:%s', $k, $v);
        }

        return $result;
    }

    /**
     * @param $url
     * @param $params
     * @return string
     */
    private function buildUrl($url, $params)
    {
        if(!empty($params)) {
            $str = http_build_query($params);
            return $url . (strpos($url, '?') === false ? '?' : '&') . $str;
        } else {
            return $url;
        }
    }
}
