<?php

namespace app\console\command;

use think\console\Input;
use think\console\Output;
use app\components\helper\HttpHelper;
use app\components\helper\DirHelper;
use app\components\helper\StringHelper;
use app\Inc\TableConst;
use app\model\chengyu\IdiomModel;
use app\services\common\BUUpyun;

class SyncMp3 extends Base
{

	protected function configure()
    {
        $this->setName('SyncMp3')->setDescription('同步mp3');
    }

    protected function execute(Input $input, Output $output)
    {
    	// $this->remoteChengyuMp3();
    	// $this->autoChengyuMp3();
    	// $this->resetChengyuMp3();
    }

    public function remoteChengyuMp3()
    {
    	$map = [
			['voice_file', '=', ''],
		];

		$errorNum = 0;
		$currId = IdiomModel::getMinId($map);
		$maxId = IdiomModel::getMaxId($map);
		while (true) {
			if ($errorNum >= 10) {
				// parent::printLog('error num max exit');
				// break;
			}

			if ($currId > $maxId) {
				parent::printLog('max id exit');
				break;
			}

			array_push($map, ['id', '>', $currId]);
			$info = IdiomModel::getInfoByMap($map, ['id','title','voice_file','addtime']);
			if (empty($info)) {
				$errorNum++;
				continue;
			}
			$currId = $info['id'];

			sleep(1);

			$url = "https://hanyu.baidu.com/s?wd={$info['title']}";
			// $url = "https://hanyu.baidu.com/s?wd=能言会道";
			$res = HttpHelper::curlGet($url, ['ssl' => true]);
			if (empty($res['content'])) {
				parent::printLog('id: '.$info['id'].' not content');
				continue;
			}

			$html = StringHelper::contentSubstr('<div id="pinyin">', '</div>', $res['content']);
			if (empty($html)) {
				parent::printLog('id: '.$info['id'].' not html');
				continue;
			}

			if (preg_match_all('/<a\s+(.*)<\/a>/', $html, $match)) {
				if (empty($match[1])) {
					parent::printLog('id: '.$info['id'].' not pinyin');
					continue;
				}
				foreach ($match[1] as $val) {
					$mp3 = StringHelper::contentSubstr('url="', '"', $val);
					if ($mp3 && strpos($mp3, 'http') !== false) {
						IdiomModel::_update($info['id'], ['voice_file' => $mp3]);

						parent::printLog('id: '.$info['id'].' mp3: '.$mp3);
						break;
					}
				}
			}
		}

		parent::printLog('remoteChengyuMp3 complete');
    }

	/**
	* 同步mp3到本地
	*/
	public function autoChengyuMp3()
	{
		$map = [
			// ['voice_file', '<>', ''],
			['voice_file', 'like', "%http%"],
		];

		$errorNum = 0;

		$currId = IdiomModel::getMinId($map);
		while (true) {
			if ($errorNum >= 10) {
				parent::printLog('error num max exit');
				break;
			}

			array_push($map, ['id', '>=', $currId]);
			$info = IdiomModel::getInfoByMap($map, ['id','title','voice_file','addtime']);
			if (empty($info)) {
				$errorNum++;
				continue;
			}

			$currId = $info['id'];
			$params = [];
			if (strpos($info['voice_file'], 'https') !== false) {
				$params['ssl'] = true;
			}

			$res = HttpHelper::curlGet($info['voice_file'], $params);
			if ($res['error'] || empty($res['content']) || $res['httpcode'] != 200) {
				file_put_contents(ROOT_PATH.'/runtime/log/notfound_mp3.txt', "{$info['id']} - {$info['voice_file']}\n", FILE_APPEND);
				continue;
			}

			$md5 = md5($info['title']);
			$saveDir = 'mp3/'.date('Ym', strtotime($info['addtime'])).'/'.substr($md5, 0, 2);
			$filename = $md5.'.mp3';
			$fullFile = DOWN_UPLOAD_PATH.'/'.$saveDir.'/'.$filename;

			if (!file_exists(DOWN_UPLOAD_PATH.'/'.$saveDir)) {
	            DirHelper::mkDir(DOWN_UPLOAD_PATH.'/'.$saveDir);
	            chown(DOWN_UPLOAD_PATH.'/'.$saveDir, 'www');
	        }

			file_put_contents($fullFile, $res['content']);

			//上传图片到又拍云
            if(file_exists($fullFile)) {
            	IdiomModel::_update($info['id'], ['voice_file' => $saveDir.'/'.$filename]);

                $sourceFile = $fullFile;
                $upFile = '/'.$saveDir.'/'.$filename;
                $upRes = BUUpyun::uploadImg($sourceFile, $upFile);
            }

			unset($res);

			parent::printLog('id: '.$info['id']);
			usleep(500);
		}

        echo "autoChengyuMp3 complete".date('Y-m-d H:i:s')."\n";
	}

	/**
	* 重新生成mp3
	*/
	public function resetChengyuMp3()
	{
		$map = [
			['voice_file', '<>', ''],
			// ['voice_file', 'like', "%http%"],
		];

		$errorNum = 0;

		$currId = IdiomModel::getMinId($map);
		while (true) {
			if ($errorNum >= 10) {
				parent::printLog('error num max exit');
				break;
			}

			array_push($map, ['id', '>=', $currId]);
			$info = IdiomModel::getInfoByMap($map, ['id','title','voice_file','addtime']);
			if (empty($info)) {
				$errorNum++;
				$currId = $info['id'] + 1;
				continue;
			}

			$currId = $info['id'] + 1;
			$params = ['ssl' => true];

			$mp3 = CSN_DOMAIN.'/'.$info['voice_file'];

			$res = HttpHelper::curlGet($mp3, $params);
			if ($res['error'] || empty($res['content']) || $res['httpcode'] != 200) {
				file_put_contents(ROOT_PATH.'/runtime/log/notfound_mp3.txt', "{$info['id']} - {$info['voice_file']}\n", FILE_APPEND);
				continue;
			}

			$md5 = md5($info['title']);
			$saveDir = 'mp3/'.date('Ym', strtotime($info['addtime'])).'/'.substr($md5, 0, 2);
			$filename = $md5.'.mp3';
			$fullFile = DOWN_UPLOAD_PATH.'/'.$saveDir.'/'.$filename;

			if (!file_exists(DOWN_UPLOAD_PATH.'/'.$saveDir)) {
	            DirHelper::mkDir(DOWN_UPLOAD_PATH.'/'.$saveDir);
	            chown(DOWN_UPLOAD_PATH.'/'.$saveDir, 'www');
	        }

			file_put_contents($fullFile, $res['content']);

			//上传图片到又拍云
            if(file_exists($fullFile)) {
            	IdiomModel::_update($info['id'], ['voice_file' => $saveDir.'/'.$filename, 'edittime' => date('Y-m-d H:i:s')]);

                $sourceFile = $fullFile;
                $upFile = '/'.$saveDir.'/'.$filename;
                $upRes = BUUpyun::uploadImg($sourceFile, $upFile);

                parent::printLog($upRes);
            }

			unset($res);

			parent::printLog('id: '.$info['id']);
			usleep(500);
		}

        echo "resetChengyuMp3 complete".date('Y-m-d H:i:s')."\n";
	}

}
