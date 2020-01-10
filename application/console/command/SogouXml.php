<?php

namespace app\console\command;

use think\console\Input;
use think\console\Output;
use app\components\helper\DirHelper;
use app\components\helper\StringHelper;
use app\components\helper\ArrayHelper;
use app\components\helper\FileHelper;
use app\components\Urls;
use app\Inc\TableConst;
use app\model\chengyu\IdiomModel;
use app\model\chengyu\IdiomStructModel;
use app\model\chengyu\IdiomTagsModel;

/**
* sogou xml
* xml过滤特殊字符  https://segmentfault.com/q/1010000012209804/a-1020000012209869
* 使用xmlwriter, 非法xml会自动转义的
*/
class SogouXml extends Base
{
	protected $pagesize = 500;

    //xml存放目录
    protected $xmlChengyuDir = null;

	protected function configure()
    {
        $this->setName('sogouXml')->setDescription('sogou xml');

        $this->xmlChengyuDir = ROOT_PATH . 'public'.DIRECTORY_SEPARATOR.'/sitemap/sogou/chengyu';
    }

    protected function execute(Input $input, Output $output)
    {
        $this->createChengyuTagsXml();
    	$this->createChengyuTagsSiteMapIndex();
    }

    public function createChengyuTagsSiteMapIndex()
    {
        $file = $this->xmlChengyuDir . "/chengyu_*.xml";

        $arr = glob($file);
        if ($arr) {
            $xml = '';
            for ($i = 1; $i<=count($arr); $i++) {
                $xml .= MOBILE_DOMAIN."/sitemap/sogou/chengyu/chengyu_{$i}.xml\n";
            }
            file_put_contents($this->xmlChengyuDir . "/sitemapsougou.txt", $xml);
        }

        echo "createChengyuTagsSiteMapIndex ". date('Y-m-d H:i:s')."\n";;
    }


    public function createChengyuTagsXml()
    {
    	if (!file_exists($this->xmlChengyuDir)) {
            DirHelper::mkDir($this->xmlChengyuDir);
            chown($this->xmlChengyuDir, 'www');
        }
        $map = [
            ['status', '=', TableConst::TAGS_STATUS_ENABLED],
            // ['id', '=', 1599],
        ];

        $count = IdiomTagsModel::getTotal($map);
        
        //删除sitemap目录文件
        if ($count) {
            DirHelper::delFileUnderDir($this->xmlChengyuDir);
        }

        $currFileNum = 1; //xml文件计数器
        $fileMaxSize = 1024 * 1024 * 5; //文件大小限制

        $xmlHeader = '<?xml version="1.0" encoding="GBK"?>'."\n".'<DOCUMENT>'."\n";
        $xmlFooter = "</DOCUMENT>\n";

        $structType = getEnums('structType');
        
        $pageMax = ceil($count / $this->pagesize);
        for ($page = 1; $page <= $pageMax; $page++) {
            $list = IdiomTagsModel::getList($map, $page, $this->pagesize);
            foreach ($list as $val) {
                $idiomMap = [
                    ['id', 'in', explode(',', $val['resource'])],
                    ['status', '=', TableConst::IDIOM_STATUS_PASS],
                    ['is_show', '=', TableConst::IDIOM_SHOW_OK],
                    ['title_pinyin', '<>', ''],
                    ['voice_file', '<>', ''],
                ];

                $chengyuList = IdiomModel::getList($idiomMap);
                if (empty($chengyuList)) {
                    continue;
                }

                foreach ($chengyuList as $item) {
                    $struct = IdiomStructModel::getListByIdiomid($item['id']);
                    $struct = ArrayHelper::toHashmap($struct, 'ctype');

                    $tagname = $val['name'];
                    $xmlTxt = $this->parseChengyuXml($tagname, $item, $struct);
                    if (empty($xmlTxt)) {
                        continue;
                    }

                    $currXmlFile = $this->xmlChengyuDir . "/chengyu_{$currFileNum}.xml";
                    if (!file_exists($currXmlFile)) {
                        file_put_contents($currXmlFile, $xmlHeader);
                    }

                    //判断是否最后一条记录
                    $islast = false;
                    if ($page == $pageMax && $key == (count($list) - 1)) {
                        $islast = true;
                    }

                    $filesize = $this->getXmlFileSize($currXmlFile);
                    if ($filesize > $fileMaxSize || $islast) { //文件大小超过最大限制
                        $currFileNum = $currFileNum + 1;

                        file_put_contents($currXmlFile, StringHelper::StrToiconv($xmlFooter, 'GBK'), FILE_APPEND);
                        parent::printLog($currXmlFile);
                    } else {
                        file_put_contents($currXmlFile, StringHelper::StrToiconv($xmlTxt, 'GBK'), FILE_APPEND);
                    }
                    usleep(200);
                }   

                parent::printLog('tag id: '.$val['id']);
            }
        }

        //判断最后一个xml文件结尾是否正常
        $this->lastXmlPaddingFooter();

        parent::printLog('createChengyuTagsXml complete');
    }

    protected function parseChengyuXml($tagname, $data, $struct)
    {
        $xml = '';
        if (empty($data['title_pinyin']) || empty($data['voice_file']) || empty($data['base_explain'])) {
            return $xml;
        }

        $tagname = StringHelper::filterUtf8($tagname);

        $pcurl = Urls::url('home_chengyu_detail', $data['identify']);
        $murl = Urls::url('mobile_chengyu_detail', $data['identify']);
        
        $xml .= "<item>\n";
        $xml .= "<key><![CDATA[{$tagname}{$data['id']}]]></key>\n";
        $xml .= "<display>\n";
        $xml .= "<title><![CDATA[{$tagname}_聚培训成语]]></title>\n";
        $xml .= "<url><![CDATA[{$murl}]]></url>\n";
        $xml .= "<pcurl><![CDATA[{$pcurl}]]></pcurl>\n";
        $xml .= "<chengyu><![CDATA[{$data['title']}]]></chengyu>\n";

        $xml .= "<pyform>\n";

        //拼音
        $xml .= "<pinyin><![CDATA[{$data['title_pinyin']}]]></pinyin>\n";

        //语音
        $voicefile = Urls::getMp3Url($data['voice_file']);
        $xml .= "<yuyin><![CDATA[{$voicefile}]]></yuyin>\n";

        //释义
        $data['base_explain'] = StringHelper::filterUtf8($data['base_explain']);
        $xml .= "<shiyi><![CDATA[".StringHelper::replaceSymbol($data['base_explain'])."]]></shiyi>\n";
        $xml .= "</pyform>\n";

        //出处
        $chuchu = !empty($struct[TableConst::IDIOM_STRUCT_SOURCE]) ? $struct[TableConst::IDIOM_STRUCT_SOURCE]['content']: '';
        $chuchu = StringHelper::replaceSymbol($chuchu);
        $chuchu = StringHelper::filterUtf8($chuchu);

        $xml .= "<chuchu><![CDATA[".strip_tags($chuchu)."]]></chuchu>\n";

        //主人公
        $xml .= "<protagonist><![CDATA[]]></protagonist>\n";

        //近义词
        $synonym = !empty($struct[TableConst::IDIOM_STRUCT_SYNONYM]) ? $struct[TableConst::IDIOM_STRUCT_SYNONYM]['content']: '';
        $synonym = str_replace("\n", ';', $synonym);
        $xml .= "<jyc><![CDATA[".$synonym."]]></jyc>\n";

        //反义词
        $antonym = !empty($struct[TableConst::IDIOM_STRUCT_ANTONYM]) ? $struct[TableConst::IDIOM_STRUCT_ANTONYM]['content']: '';
        $antonym = str_replace("\n", ';', $antonym);
        $xml .= "<fyc><![CDATA[".$antonym."]]></fyc>\n";

        //例句
        $liju = !empty($struct[TableConst::IDIOM_STRUCT_ENSAMPLE])? $struct[TableConst::IDIOM_STRUCT_ENSAMPLE]['content']: '';
        $liju = StringHelper::filterUtf8($liju);
        $liju = StringHelper::replaceSymbol($liju);
        $xml .= "<liju><![CDATA[".strip_tags($liju)."]]></liju>\n";

        $fenjie = $this->fenjieChengyu($data['title'], $data['title_pinyin']);
        foreach ($fenjie as $val) {
            $xml .= "<fenjie>\n";
            $xml .= "<danzi><![CDATA[".$val['char']."]]></danzi>\n";
            $xml .= "<dzshiyi><![CDATA[]]></dzshiyi>\n";
            $xml .= "<dzpinyin><![CDATA[".$val['pinyin']."]]></dzpinyin>\n";
            $xml .= "</fenjie>\n";
        }

        $xml .= "<num><![CDATA[".$this->getCharLen($data['title'])."]]></num>\n";

        $tag = str_replace(['对应的成语','中的成语','的成语','成语'], '', $tagname);
        $xml .= "<tag><![CDATA[".$tag."]]></tag>\n";
        $xml .= "<mingzhong><![CDATA[成语]]></mingzhong>\n"; //请统一填写命中词“成语”
        $xml .= "<jinyici><![CDATA[近义词]]></jinyici>\n"; //请统一固定填写“近义词”
        $xml .= "<fanyici><![CDATA[反义词]]></fanyici>\n"; //请统一固定填写“反义词”
        $xml .= "<showurl><![CDATA[ciyu.jupeixun.cn]]></showurl>\n";
        $xml .= '<sogouToday><![CDATA[${sogouToday}]]></sogouToday>'."\n";

        $jiegou = $structType[$data['struct_type']] ?? '';
        $xml .= "<jiegou><![CDATA[".$jiegou."]]></jiegou>\n";

        $xml .= "</display>\n";
        $xml .= "</item>\n";

        return $xml;
    }


    /**
    * 最后一个xml文件填充结尾
    */
    protected function lastXmlPaddingFooter()
    {
        $res = [];

        $file = $this->xmlChengyuDir . "/chengyu_*.xml";
        $arr = glob($file);
        for ($i = 0; $i < count($arr); $i++) {
            if (preg_match('/chengyu_(\d+)\.xml/', $arr[$i], $match)) {
                $res[] = [
                    'id' => $match[1],
                    'file' => $arr[$i],
                ];
            }
        }
        $res = ArrayHelper::sortByCol($res, 'id', SORT_DESC);
        $file = $res[0]['file'];

        $ishave = false; //xml是否有结尾 true有 false无
        $data = FileHelper::getFileTail($res[0]['file'], 5);
        for ($i = 0; $i < count($data); $i++) {
            $data[$i] = trim($data[$i]);
            if (strpos($data[$i], '</DOCUMENT>') !== false) {
                $ishave = true;
            }
        }

        if (!$ishave) {
            file_put_contents($file, StringHelper::StrToiconv("</DOCUMENT>\n", 'GBK'), FILE_APPEND);
        }
    }


    /**
    * 解析成语到一个个汉字
    */
    protected function fenjieChengyu($name, $pinyin)
    {
        $data = [];

        $name = str_replace('，', '', $name);

        $pinyin = str_replace('，', ' ', $pinyin);
        $pinyin = preg_replace('/(\s+)/', ' ', $pinyin);

        $pinyin = explode(' ', $pinyin);
        $chars = StringHelper::mbStrSplit($name);
        for ($i = 0; $i < count($chars); $i++) {
            $data[] = [
                'char' => $chars[$i],
                'pinyin' => $pinyin[$i],
            ];
        }

        return $data;
    }

    protected function getCharLen($name)
    {
        $len = mb_strlen($name);
        $cnLen = ['一','二','三','四','五','六','七','八','九','十'];

        return "{$len}字;".$cnLen[$len-1]."字";
    }
   
	


}