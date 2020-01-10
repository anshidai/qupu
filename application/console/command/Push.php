<?php

namespace app\console\command;

use think\console\Input;
use think\console\Output;
use app\common\model\Article as ArticleModel;
use app\common\model\ActLog as ActLogModel;
use app\admin\model\bu\BUAmActLog;
use app\components\Urls;

/**
* 推送相关操作
*/
class Push extends Base
{

  //百度pc推送
  protected $baiduApi = 'http://data.zz.baidu.com/urls?site=www.woxiexin.com&token=jw9fMSGOGvAcuv5g';

  //百度m站推送
	protected $baiduMApi = 'http://data.zz.baidu.com/urls?site=m.xuanjuzi.com&token=jw9fMSGOGvAcuv5g';

  //百度移动天级推送
  protected $baiduMDayApi = 'http://data.zz.baidu.com/urls?appid=1630045469262215&token=bGKLoZaUky7o4yFN&type=realtime';

  //百度移动周级推送
  protected $baiduMWeekApi = 'http://data.zz.baidu.com/urls?appid=1630045469262215&token=bGKLoZaUky7o4yFN&type=batch';

	protected static $pagesize = 1000;

	protected function configure()
    {
        $this->setName('Push')->setDescription('推送操作');
    }

    protected function execute(Input $input, Output $output)
    {
    	$this->autoPushBaidu();
    	// $this->pushDayBaidu();
    	// $this->pushWeekBaidu();
    	//$this->fullPushBaidu();
    }

    /**
    * 百度移动天级推送
    */
    public function pushDayBaidu()
    {
		$sdate = date('Y-m-d').' 00:00:01';
		$edate = date('Y-m-d').' 23:59:59';

		$map = [
			['status', '=', ArticleModel::STATUS_PASS],
			['is_show', '=', ArticleModel::SHOW_OK],
			['edittime', '>=', $sdate],
			['edittime', '<=', $edate],
		];

		$list = ArticleModel::getList($map, 1, self::$pagesize, ['id','identify'],'id asc');
		foreach ($list as $val) {
			$url = Urls::url('mobile_article_detail', $val['identify']);
			$resJson = $this->syncMipUrl(array($url), $this->baiduMDayApi);
			$resJson = json_decode($resJson, true);
			if($resJson['remain_realtime'] == '0') {
				parent::printLog('当天提交数量已达到上限');
				break;
			}
			parent::printLog($url.' '.json_encode($resJson));
		}
		parent::printLog('pushDayBaidu complete');
    }
	
	/**
    * 百度移动周级推送
    */
    public function pushWeekBaidu()
    {
		$sdate = date('Y-m-d').' 00:00:01';
		$edate = date('Y-m-d').' 23:59:59';
		$map = [
			['status', '=', ArticleModel::STATUS_PASS],
			['is_show', '=', ArticleModel::SHOW_OK],
			['edittime', '>=', $sdate],
			['edittime', '<=', $edate],
		];
		
		$count = ArticleModel::getTotal($map);
   		$pageMax = ceil($count / self::$pagesize);
		for ($page = 1; $page <= $pageMax; $page++) {
			$list = ArticleModel::getList($map, $page, self::$pagesize, ['id','identify'],'id asc');
   			foreach ($list as $val) {
				$url = Urls::url('mobile_article_detail', $val['identify']);
				$resJson = $this->syncMipUrl(array($url), $this->baiduMWeekApi);
				$resJson = json_decode($resJson, true);
				if($resJson['remain_batch'] == '0') {
					parent::printLog('当天提交数量已达到上限');
					break 2;
				}
				parent::printLog($url.' '.json_encode($resJson));
			}
		}
		parent::printLog('pushWeekBaidu complete');
    }

    /**
	* 百度定时推送
    */
    public function autoPushBaidu()
    {
    	$sdate = date('Y-m-d').' 00:00:01';
    	$edate = date('Y-m-d').' 23:59:59';
    	$map = [
			['status', '=', ArticleModel::STATUS_PASS],
			['is_show', '=', ArticleModel::SHOW_OK],
			['edittime', '>=', $sdate],
			['edittime', '<=', $edate],
		];

   		$count = ArticleModel::getTotal($map);
   		$pageMax = ceil($count / self::$pagesize);

   		$step = $count;
   		for ($page = 1; $page <= $pageMax; $page++) {
   			$list = ArticleModel::getList($map, $page, self::$pagesize, ['id','identify'],'id asc');
   			foreach ($list as $val) {
   				// $url = Urls::url('home_article_detail', $val['identify']);
   				// $resJson = $this->syncMipUrl(array($url), $this->baiduApi);

				$mUrl = Urls::url('mobile_article_detail', $val['identify']);
				$resJson = $this->syncMipUrl(array($mUrl), $this->baiduMApi);

   				$resJson = json_decode($resJson, true);
				if($resJson['remain'] == '0') {
					parent::printLog('当天提交数量已达到上限');
					$remainRealtime = 0; 
					break 2;
				}else {
					$remainRealtime = $resJson['remain'];
				}
				parent::printLog($url.' '.json_encode($resJson));
   			}
   		}

   		parent::printLog('fullPushBaidu complete');
    }

    /**
	* 百度全量推送
    */
    public function fullPushBaidu()
    {
    	$map = [
   			['status', '=', ArticleModel::STATUS_PASS],
			['is_show', '=', ArticleModel::SHOW_OK],
   		];

   		$count = ArticleModel::getTotal($map);
   		$pageMax = ceil($count / self::$pagesize);

   		$step = $count;
   		for ($page = 1; $page <= $pageMax; $page++) {
   			$list = ArticleModel::getList($map, $page, self::$pagesize, ['id','identify'],'id asc');
   			foreach ($list as $val) {
   				// $url = Urls::url('home_article_detail', $val['identify']);
   				// $resJson = $this->syncMipUrl(array($url), $this->baiduApi);

				$mUrl = Urls::url('mobile_article_detail', $val['identify']);
				$resJson = $this->syncMipUrl(array($mUrl), $this->baiduMApi);
				
   				$resJson = json_decode($resJson, true);
				if($resJson['remain'] == '0') {
					parent::printLog('当天提交数量已达到上限');
					$remainRealtime = 0; 
					return $remainRealtime;
				}else {
					$remainRealtime = $resJson['remain'];
				}

				parent::printLog($url.' '.json_encode($resJson));
   			}	
   		}

   		parent::printLog('fullPushBaidu complete');
    }


    private function syncMipUrl($urls, $apiurl)
	{
		if(!empty($urls)) {
			$ch = curl_init();
			$options =  array(
			    CURLOPT_URL => $apiurl,
			    CURLOPT_POST => true,
			    CURLOPT_RETURNTRANSFER => true,
			    CURLOPT_POSTFIELDS => implode("\n", $urls),
			    CURLOPT_HTTPHEADER => array('Content-Type: text/plain'),
			);
			curl_setopt_array($ch, $options);
			$result = curl_exec($ch);
			return $result;
		}
	}
	

}
