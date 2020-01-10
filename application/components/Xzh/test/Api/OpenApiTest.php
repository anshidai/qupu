<?php
/**
 * OpenAPI curl示例
 *
 * @author xzh
 * @date 2018/3/22 下午10:55
 */

namespace Xzh\Client;


use CURLFile;
use Xzh\Client\Api\OpenApi;

class OpenApiTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var OpenApi
     */
    private $openApi;

    /**
     * OpenApiTest constructor.
     * @param null $name
     * @param array $data
     * @param string $dataName
     */
    public function __construct($name = null, array $data = array(), $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->openApi = new OpenApi();
    }

    /**
     * 测试用例-获取用户基本信息接口
     */
    public function testUserInfo()
    {
        $userInfoApi = 'user/info';
        $accessToken = '24.bbac85e0ab7a6b706bc6e2e1387a3ec1.7200.1522213570.282335-10229144';

        $data = new \stdClass();
        $openId = new \stdClass();
        $openId->openid = '36GetTQ2Qh0kpMluyUsRp2_c14';
        $data->user_list = [
            $openId
        ];

        $data = json_encode($data);
        $params = [
            'access_token' => $accessToken,
        ];
        $ret = $this->openApi->postInvoke($userInfoApi, $data, $params);
        $this->logger($ret);
    }

    /**
     * 测试用例-获取粉丝列表
     */
    public function testUserGet()
    {
        $userGetApi = 'user/get';
        $start_index = 0;
        $accessToken = '24.f1cfa26e657eb3041c01860f7fb526a2.7200.1521471972.282335-10229144';
        $params = [
            'start_index' => $start_index,
            'access_token' => $accessToken,
        ];
        try {
            $ret = $this->openApi->getInvoke($userGetApi, $params);
            $this->logger($ret);
        } catch (XzhException $e) {
            $this->logger($e);
        }
    }

    /**
     * 测试用例-上传图文消息内的图片获取url
     */
    public function testMediaUploadimg()
    {
        $mediaUploadImgApi = 'media/uploadimg';
        $accessToken = '24.f1cfa26e657eb3041c01860f7fb526a2.7200.1521471972.282335-10229144';
        $params = [
            'access_token' => $accessToken,
        ];

        $file = '/Users/xx/Desktop/PIC-/test.jpg';
        /**
         * 语音: mp3,wma,wav,amr (最大4M)
         * 图片: jpg,png,jpeg,bmp,gif
         */
        $image = new CURLFile($file);
        $data = [
//    'media' => '@' . $file,
            'media' => $image,
        ];

        $header = [
            'content-type' => 'multipart/form-data',
        ];
        try {
            $ret = $this->openApi->postInvoke($mediaUploadImgApi, $data, $params, $header, 20000, 20000);
            $this->logger($ret);
        } catch (XzhException $e) {
            $this->logger($e);
        }
    }

    /**
     * 打印日志
     * @param $result
     */
    private function logger($result) {
        var_dump($result);
    }
}