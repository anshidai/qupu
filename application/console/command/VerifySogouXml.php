<?php

namespace app\console\command;

use think\console\Input;
use think\console\Output;
use app\components\helper\HttpHelper;
use app\components\Urls;
use app\Inc\TableConst;

/**
* 校验搜狗xml
*/
class VerifySogouXml extends Base
{
    //成语模板id
    private $chengyuTagTemplate = '70141400';

    //名人名言模板id
    private $mingyanTemplate = '70125900';

    //故事模板id
    private $gushiTemplate = '2196';  //多点2196 单点2267

    //搜狗平台检验地址
    private $verifyApiUrl = 'http://data.open.sogou.com/xmlview/validation/validate';

	protected function configure()
    {
        $this->setName('verifysogouxml')->setDescription('verify sogou xml');
    }

    protected function execute(Input $input, Output $output)
    {
        // $this->verifyIdiomTags();
        // $this->verifyMingyan();
        // $this->verifyKugushi();
    }

    /**
    * 校验成语tag词xml
    */
    public function verifyIdiomTags()
    {
        $logFile = ROOT_PATH.'/runtime/log/verifyIdiomTags.txt';

        $xmlindex = 'https://ciyu.jupeixun.cn/sitemap/sogou/chengyu/sitemapsougou.txt';

        $xmlArr = file_get_contents($xmlindex);
        $xmlArr = explode("\n", $xmlArr);

        for ($i = 0; $i < count($xmlArr); $i++) {
            if (empty($xmlArr[$i])) {
                continue;
            }
            $xmlfile = $xmlArr[$i];

            $params = [
                'header' => 'Content-Type: application/x-www-form-urlencoded',
            ];
            $post = [
                'templateID' => $this->chengyuTagTemplate,
                'type' => 3,
                'xml' => $xmlfile,
            ];

            $res = HttpHelper::curlPost($this->verifyApiUrl, http_build_query($post), $params);
            if ($res['content']) {
                $result = json_decode($res['content'], true);
                if ($result['code'] == 1) {
                    self::printLog("success: {$xmlfile}");
                } else {
                    self::printLog("==fail==: {$xmlfile} ".json_encode($result, JSON_UNESCAPED_UNICODE), $logFile);
                }

            } else {
                self::printLog("==exception==: {$xmlfile} ".json_encode($res, JSON_UNESCAPED_UNICODE), $logFile);
            }
        }

        self::printLog("verifyIdiomTags complete");
    }

    /**
    * 校验名人名言xml
    */
    public function verifyMingyan()
    {
        $logFile = ROOT_PATH.'/runtime/log/verifyMingyan.txt';

        $xmlindex = 'http://down.jupeixun.cn/mingyan/sitemapsogou/sitemapsogou_index.txt';

        $xmlArr = file_get_contents($xmlindex);
        $xmlArr = explode("\n", $xmlArr);

        for ($i = 0; $i < count($xmlArr); $i++) {
            if (empty($xmlArr[$i])) {
                continue;
            }
            $xmlfile = $xmlArr[$i];

            $params = [
                'header' => 'Content-Type: application/x-www-form-urlencoded',
            ];
            $post = [
                'templateID' => $this->mingyanTemplate,
                'type' => 3,
                'xml' => $xmlfile,
            ];

            $res = HttpHelper::curlPost($this->verifyApiUrl, http_build_query($post), $params);
            if ($res['content']) {
                $result = json_decode($res['content'], true);
                if ($result['code'] == 1) {
                    self::printLog("success: {$xmlfile}");
                } else {
                    self::printLog("==fail==: {$xmlfile} ".json_encode($result, JSON_UNESCAPED_UNICODE), $logFile);
                }

            } else {
                self::printLog("==exception==: {$xmlfile} ".json_encode($res, JSON_UNESCAPED_UNICODE), $logFile);
            }
        }

        self::printLog("verifyMingyan complete");
    }

    /**
    * 校验酷故事xml
    */
    public function verifyKugushi()
    {
        $logFile = ROOT_PATH.'/runtime/log/verifyKugushi.txt';

        $pcXmlindex = 'https://www.kugushi.com/xml/pc-gushi.txt';
        $mXmlindex = 'https://www.kugushi.com/xml/wap-gushi.txt';

        $pcXmlArr = file_get_contents($pcXmlindex);
        $pcXmlArr = explode("\n", $pcXmlArr);

        $mXmlArr = file_get_contents($mXmlindex);
        $mXmlArr = explode("\n", $mXmlArr);

        $xmlArr = array_merge($pcXmlArr, $mXmlArr);
        for ($i = 0; $i < count($xmlArr); $i++) {
            if (empty($xmlArr[$i])) {
                continue;
            }
            $xmlfile = $xmlArr[$i];

            $params = [
                'header' => 'Content-Type: application/x-www-form-urlencoded',
            ];
            $post = [
                'templateID' => $this->gushiTemplate,
                'type' => 3,
                'xml' => $xmlfile,
            ];

            $res = HttpHelper::curlPost($this->verifyApiUrl, http_build_query($post), $params);
            if ($res['content']) {
                $result = json_decode($res['content'], true);
                if ($result['code'] == 1) {
                    self::printLog("success: {$xmlfile}");
                } else {
                    self::printLog("==fail==: {$xmlfile} ".json_encode($result, JSON_UNESCAPED_UNICODE), $logFile);
                }

            } else {
                self::printLog("==exception==: {$xmlfile} ".json_encode($res, JSON_UNESCAPED_UNICODE), $logFile);
            }
        }

        self::printLog("verifyKugushi complete");
    }


}