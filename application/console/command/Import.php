<?php

namespace app\console\command;

use think\console\Input;
use think\console\Output;
use app\components\helper\SiteMapHelper;
use app\components\helper\ArrayHelper;
use app\components\helper\StringHelper;
use app\Inc\TableConst;
use app\services\common\BUPhpExcel;
use app\model\chengyu\IdiomModel;
use app\model\chengyu\IdiomStructModel;
use app\model\chengyu\IdiomTagsModel;


class Import extends Base
{
    protected $pagesize = 1000;

	protected function configure()
    {
        $this->setName('import')->setDescription('导入');
    }

    protected function execute(Input $input, Output $output)
    {
        $this->updateChengyu();
    	// $this->verifyPinyin();
    }

    public function verifyPinyin()
    {
        $logFile = ROOT_PATH.'/runtime/log/verifyPinyin.txt';
        $map = [
           
        ];

        $count = IdiomModel::getTotal($map);
        $pageMax = ceil($count / $this->pagesize);
        for ($page = 1; $page <= $pageMax; $page++) {
            $list = IdiomModel::getList($map, $page, $this->pagesize, ['id','title','title_pinyin'], 'id asc');
            foreach ($list as $val) {
                if (empty($val['title_pinyin'])) {
                    continue;
                }

                $val['title'] = str_replace([',','，'], ' ', $val['title']);
                $val['title'] = str_replace('  ', ' ', $val['title']);
                $titleArr = StringHelper::mbStrSplit($val['title']);

                $val['title_pinyin'] = str_replace([',','，'], ' ', $val['title_pinyin']);
                $val['title_pinyin'] = str_replace('  ', ' ', $val['title_pinyin']);
                $pinyinArr = explode(' ', $val['title_pinyin']);
                if (count($titleArr) != count($pinyinArr)) {
                    self::printLog("ID: {$val['id']}", $logFile);
                } 
            }
        }

        self::printLog("verifyPinyin complete");
    }

    public function updateChengyu()
    {
        $file = ROOT_PATH.'/data/bd_chengyu.xlsx';

    	$res = BUPhpExcel::readExcelFileToArray($file);
        foreach ($res as $val) {
            $title = trim($val[1]);
            $pinyin = trim($val[2]);
            $baseexplain = trim($val[3]);
            $translate = trim($val[4]);
            $mp3 = trim($val[5]);
            $chuchu = trim($val[6]);
            $lieju = trim($val[7]);
            $diangu = trim($val[8]);
            $jinyici = trim($val[9]);
            $fanyici = trim($val[10]);
            $yufa = trim($val[11]);
            $shili = trim($val[12]);
            $jieshi = trim($val[13]);
            $mimian = trim($val[14]);

            $info = IdiomModel::getInfoByMap(['title_hash' => md5($title)]);
            if (empty($info)) {
                continue;
            }

            // file_put_contents('./dd.txt', var_export($val,true));exit;

            $idiomArr = $structArr = [];
            if ($pinyin && empty($info['title_pinyin'])) {
                $idiomArr['title_pinyin'] = $pinyin;
            }

            if ($baseexplain && empty($info['base_explain'])) {
                $idiomArr['base_explain'] = $baseexplain;
            }

            if ($mp3 && empty($info['voice_file'])) {
                $idiomArr['voice_file'] = $mp3;
            }

            if ($translate && empty($info['title_translate'])) {
                $idiomArr['title_translate'] = $translate;
            }

            $structInfo = IdiomStructModel::getListByIdiomid($info['id']);
            $structInfo = ArrayHelper::toHashmap($structInfo, 'ctype');

            if ($jieshi && empty($structInfo[TableConst::IDIOM_STRUCT_EXPLAIN])) {
                $structArr[] = [
                    'ctype' => TableConst::IDIOM_STRUCT_EXPLAIN,
                    'content' => $jieshi,
                ];

            } elseif ($baseexplain && empty($jieshi) && empty($structInfo[TableConst::IDIOM_STRUCT_EXPLAIN]['content'])) {
                $structArr[] = [
                    'ctype' => TableConst::IDIOM_STRUCT_EXPLAIN,
                    'content' => $baseexplain,
                ];
            }

            if ($lieju && empty($structInfo[TableConst::IDIOM_STRUCT_ENSAMPLE]['content'])) {
                $structArr[] = [
                    'ctype' => TableConst::IDIOM_STRUCT_ENSAMPLE,
                    'content' => $lieju,
                ];
            }

            if ($diangu && empty($structInfo[TableConst::IDIOM_STRUCT_SOURCE]['content'])) {
                $structArr[] = [
                    'ctype' => TableConst::IDIOM_STRUCT_SOURCE,
                    'content' => $diangu,
                ];
            }

            if ($shili && empty($structInfo[TableConst::IDIOM_STRUCT_CASE]['content'])) {
                $structArr[] = [
                    'ctype' => TableConst::IDIOM_STRUCT_CASE,
                    'content' => $shili,
                ];
            }

            if ($jinyici && empty($structInfo[TableConst::IDIOM_STRUCT_SYNONYM]['content'])) {
                $structArr[] = [
                    'ctype' => TableConst::IDIOM_STRUCT_SYNONYM,
                    'content' => str_replace("||", "\n", $jinyici),
                ];
            }

            if ($fanyici && empty($structInfo[TableConst::IDIOM_STRUCT_ANTONYM]['content'])) {
                $structArr[] = [
                    'ctype' => TableConst::IDIOM_STRUCT_ANTONYM,
                    'content' => str_replace("||", "\n", $fanyici),
                ];
            }

            if ($yufa && empty($structInfo[TableConst::IDIOM_STRUCT_GRAMMAR]['content'])) {
                $structArr[] = [
                    'ctype' => TableConst::IDIOM_STRUCT_GRAMMAR,
                    'content' => $yufa,
                ];
            }

            if ($mimian && empty($structInfo[TableConst::IDIOM_STRUCT_RIDDLE]['content'])) {
                $structArr[] = [
                    'ctype' => TableConst::IDIOM_STRUCT_RIDDLE,
                    'content' => $mimian,
                ];
            }

            if ($idiomArr) {
                IdiomModel::_update($info['id'], $idiomArr);
            }

            if ($structArr) {
                foreach ($structArr as $item) {
                    IdiomStructModel::editStruct($info['id'], $item['ctype'], $item['content']);
                }
            }

            if ($idiomArr || $structArr) {
                self::printLog("ID: {$info['id']}");
                // file_put_contents('./dd.txt', "ID: {$info['id']}\n", FILE_APPEND);
                // file_put_contents('./dd.txt', var_export(array_merge($idiomArr, $structArr),true), FILE_APPEND);
            }
            
        }

        self::printLog('importChengyu complete');
    }



}