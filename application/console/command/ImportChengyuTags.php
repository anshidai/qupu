<?php

namespace app\console\command;

use think\console\Input;
use think\console\Output;
use app\components\helper\SiteMapHelper;
use app\Inc\TableConst;
use app\services\common\BUPhpExcel;
use app\model\chengyu\IdiomModel;
use app\model\chengyu\IdiomTagsModel;


class ImportChengyuTags extends Base
{
	protected function configure()
    {
        $this->setName('ImportChengyuTags')->setDescription('导入成语tags');
    }

    protected function execute(Input $input, Output $output)
    {
    	$this->importTags();
    }

    public function importTags()
    {
        $file = ROOT_PATH.'/data/chengyu_1108.xlsx';

    	$res = BUPhpExcel::readExcelFileToArray($file);
        foreach ($res as $val) {
            $name = array_shift($val);

            $ids = $this->getChengyuByName($val);
            if (empty($ids) || empty($name)) {
                continue;
            }

            $tagInfo = IdiomTagsModel::getInfoByMap(['name' => $name]);
            if ($tagInfo) {
                $tagid = $tagInfo['id'];
                $arr = [
                    'resource' => implode(',', $ids),
                    'edittime' => date('Y-m-d H:i:s'),
                ];
                IdiomTagsModel::_update($tagid, $arr);

            } else {
                $arr = [
                    'name' => $name,
                    'identify' => createUniqid(),
                    'resource' => implode(',', $ids),
                    'status' => TableConst::IDIOM_TAGS_STATUS_PASS,
                ];
                $tagid = IdiomTagsModel::_add($arr);
            }

            self::printLog("ID: {$tagid}");
        }

        self::printLog('importTags complete');
    }

   	
	protected function getChengyuByName($names)
    {
        if (empty($names)) {
            return [];
        }

        if (is_string($names)) {
            $names = [$names];
        }

        for ($i = 0; $i < count($names); $i++) {
            $map = [
                ['title_hash', '=', md5($names[$i])],
                ['status', '=', TableConst::IDIOM_TAGS_STATUS_PASS],
                ['is_show', '=', TableConst::IDIOM_SHOW_OK],
            ];
            $info = IdiomModel::getInfoByMap($map, ['id']);
            if ($info) {
                $data[$info['id']] = $info['id'];
            }
        }

        return $data;
    }


}