<?php

namespace app\console\command;

use think\console\Input;
use think\console\Output;
use app\common\model\Article as ArticleModel;
use app\components\helper\SiteMapHelper;
use app\components\helper\DirHelper;
use app\components\Urls;

/**
* sitemap相关操作
*/
class Sitemap extends Base
{
	protected static $pagesize = 1000;
	protected static $maxUrl = 10000; //每个xml文件存放记录数


	protected function configure()
    {
        $this->setName('Sitemap')->setDescription('sitemap操作');
    }

    protected function execute(Input $input, Output $output)
    {
    	$this->createArticleSitemap();
    }

    public function createArticleSitemap()
    {
    	$this->createArticleXml();
    }

   	private function createSiteMapIndex()
   	{

   	}

   	private function createArticleXml()
   	{
   		//xml存放目录
   		$xmldir = ROOT_PATH . 'public'.DIRECTORY_SEPARATOR.'sitemap'.DIRECTORY_SEPARATOR.'juzi';
   		$Mxmldir = ROOT_PATH . 'public'.DIRECTORY_SEPARATOR.'sitemap'.DIRECTORY_SEPARATOR.'juzi'.DIRECTORY_SEPARATOR.'m';

   		if (!file_exists($xmldir)) {
            mkdir($xmldir, 0775);
            chown($xmldir, 'www');
        }
		
		if (!file_exists($Mxmldir)) {
            mkdir($Mxmldir, 0775);
            chown($Mxmldir, 'www');
        }

        // $config = array(
        // 	'sitemapIndexName' => 'sitemap_index',
        // 	'xmlIndexDomain' => DOWN_DOMAIN.'/sitemap/juzi',
        //     'xmlDirPath' => $xmldir,
        //     'xmlName' => 'juzi_',
        //     'maxUrl' => self::$maxUrl,
        // );
        // $sitemap = new SiteMapHelper($config);
		
		$Mconfig = array(
        	'sitemapIndexName' => 'sitemap_index',
        	'xmlIndexDomain' => MOBILE_DOMAIN.'/sitemap/juzi/m',
            'xmlDirPath' => $Mxmldir,
            'xmlName' => 'juzi_',
            'maxUrl' => self::$maxUrl,
        );
        $Msitemap = new SiteMapHelper($Mconfig);

   		$map = [
   			['status', '=', ArticleModel::STATUS_PASS],
			['is_show', '=', ArticleModel::SHOW_OK],
   		];

   		$count = ArticleModel::getTotal($map);
   		$pageMax = ceil($count / self::$pagesize);

   		//删除sitemap目录文件
        if ($count) {
        	DirHelper::delFileUnderDir($xmldir);
        }
		
		//删除sitemap目录文件
        if ($count) {
        	DirHelper::delFileUnderDir($Mxmldir);
        }

   		$step = $count;
        $inc = 0;
   		for ($page = 1; $page <= $pageMax; $page++) {
   			$list = ArticleModel::getList($map, $page, self::$pagesize, ['id','identify','edittime'],'id asc');
   			foreach ($list as $val) {
   				//每1w条生成文件
            	if ($inc >0 && ($inc % self::$maxUrl) == 0) {
            		// $xmlfile = $sitemap->createSitemapXmlFile(); 
            		$Mxmlfile = $Msitemap->createSitemapXmlFile(); 
            		echo " ======== create xml {$xmlfile} ". date('Y-m-d H:i:s')." ========= \n";
            	}

            	 // $url = Urls::url('home_article_detail', $val['identify']);
              //   $urls = array(
              //       'loc' => $url,
              //       'priority' => '0.8',
              //       'lastmod' => date('Y-m-d H:i:s'),
              //       'changefreq' => 'daily'
              //   );
              //   $sitemap->addUrl($urls);
				
				        $Murl = Urls::url('mobile_article_detail', $val['identify']);
                $Murls = array(
                    'loc' => $Murl,
                    'priority' => '0.8',
                    'lastmod' => date('Y-m-d H:i:s'),
                    'changefreq' => 'daily'
                );
                $Msitemap->addUrl($Murls);

                $inc++;
                $step = $step - 1;
                echo "ID:{$val['id']}\t {$count} - {$step}\t". date('Y-m-d H:i:s')."\n";
   			}
   		}

   		//如果还有没处理完url
        // if ($sitemap->getUrls()) {
        //     $xmlfile = $sitemap->createSitemapXmlFile(); 
        //     echo " ======== create xml {$xmlfile} ". date('Y-m-d H:i:s')." ========= \n";
        // }
		
		//如果还有没处理完url
        if ($Msitemap->getUrls()) {
            $Mxmlfile = $Msitemap->createSitemapXmlFile(); 
            echo " ======== create xml {$Mxmlfile} ". date('Y-m-d H:i:s')." ========= \n";
        }

        // $sitemap->createSitemapIndexFiles();
		
        $Msitemap->createSitemapIndexFiles();
        
        echo "createArticleXml complete\n";
   	}
	


}