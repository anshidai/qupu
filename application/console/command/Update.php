<?php

namespace app\console\command;

use think\console\Input;
use think\console\Output;
use app\common\model\Article as ArticleModel;
use app\common\model\ActLog as ActLogModel;
use app\common\model\Category as CategoryModel;
use app\admin\model\bu\BUAmActLog;
use app\components\helper\ArrayHelper;

/**
* 更新相关操作
*/
class Update extends Base
{

	protected function configure()
    {
        $this->setName('Update')->setDescription('更新操作');
    }

    protected function execute(Input $input, Output $output)
    {
    	$this->autoPushArticle();
    }

	/**
	* 自动定时发布文章
	*/
	public function autoPushArticle()
	{
		$map = [
			['status', '=', CategoryModel::STATUS_ENABLED],
			['command_times', '<>', ''],
			['command_num', '>', 0],
		];
		$pushArr = CategoryModel::getList($map, 0, 0, ['id','name','command_times','command_num'], 'id asc');

		$logFile = ROOT_PATH.'/runtime/auto_push_article_'.date('Ym').'.txt';

		$hour = date('G');
		foreach ($pushArr as $push) {
			$times = explode(',', $push['command_times']);
			if (!empty($times) && $push['command_num'] && in_array($hour, $times)) {

				//优先发布已审核文章
				$map = [
					['catid', '=', $push['id']],
					['status', '=', ArticleModel::STATUS_PASS],
					['is_show', '=', ArticleModel::SHOW_DEFAULT],
				];
				$res = ArticleModel::getList($map, 1, $push['command_num'], ['id'],'id asc');

				//没有已审核过的文章
				if (empty($res)) {
					$map = [
						['catid', '=', $push['id']],
						['status', '=', ArticleModel::STATUS_NOT],
						['is_show', '=', ArticleModel::SHOW_DEFAULT],
					];
					$res = ArticleModel::getList($map, 1, $push['command_num'], ['id'],'id asc');
				} 

				$ids = ArrayHelper::getCols($res, 'id');
				if (!empty($ids)) {
					$where = [
						['id', 'in', $ids]
					];
					$arr = [
						'status' => ArticleModel::STATUS_PASS,
						'is_show' => ArticleModel::SHOW_OK,
						'edittime' => date('Y-m-d H:i:s'),
					];
					ArticleModel::updateByMap($where, $arr, $push['command_num']);

					$msg = "update:\t".json_encode(array_merge($push, $arr, $where));
	                self::printLog($msg, $logFile);
				}
			}
		}

        echo "autoPushArticle complete".date('Y-m-d H:i:s')."\n";
	}

}
