<?php 

namespace app\components\helper;

/**
* sitemap网站地图操作类

使用：


//生成sitemap索引入口文件
$config = array(
	'xmlDirPath' => 'xxxx/xxx',
);
$sitemap = new SiteMapHelper($config);

$urls = array(
	'loc' => 'xxx.xml',
	'lastmod' => date('Y-m-d H:i:s'),
);
$sitemap->addUrl($urls);
$sitemap->createSitemapXmlFile();



//生成单个sitemap 具体数据
$config = array(
	'prefix' => '',
	'xmlDirPath' => 'xxxx/xxx',
	'xmlName' => 'sitemap',
	'maxUrl' => 50000,
	'xmlHeader' => 'xxx',
	'xmlFooter' => 'xxx',
);
$sitemap = new SiteMapHelper($config);

$urls = array(
	'loc' => 'xxx.html',
	'priority' => '0.9',
	'lastmod' => date('Y-m-d H:i:s'),
	'changefreq' => 'Always'
);
$sitemap->addUrl($urls);
$sitemap->createSitemapXmlFile();



*/
class SiteMapHelper
{	
	//url容器
	private $urls = array();

	private $config = array(

		//url前缀
		'prefix' => '',
		
		//xml文件存放目录
		'xmlDirPath' => '',
		
		//xml文件名
		'xmlName' => 'sitemap',

		//xml文件最大url数量
		'maxUrl' => 50000,
		
		//xml头信息
		'xmlHeader' => "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\r\n<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\r\n",
		
		//xml结尾
		'xmlFooter' => "</urlset>\r\n",

		//xml index文件名
		'sitemapIndexName' => 'sitemaps',

		//xml index 访问地址
		'xmlIndexDomain' => '',
		
		//xml index 头信息
		'xmlIndexHeader' => "<?xml version='1.0' encoding='UTF-8'?>\r\n<sitemapindex>\r\n",
		
		//xml index 结尾
		'xmlIndexFooter' => "</sitemapindex>\r\n",

	);
	
	/**
	* 构造函数
	*/
	public function __construct($config = array())
	{
		if(is_array($config) && !empty($config)) {
			$this->config = array_merge($this->config, $config);
		}
		if(empty($this->config['xmlDirPath'])) {
			$this->config['xmlDirPath'] = __DIR__;
		}
		
		if(!file_exists($this->config['xmlDirPath'])) {
			if(!mkdir($this->config['xmlDirPath'], 0775)) {
				die('Do not write XML directory.');
			}
		}
	}

	/**
	* 获取urls
	*/
	public function getUrls()
	{
		return $this->urls;
	}
	
	/**
	* 增加url元素
	* @param array|string $url 
	* $url = array(
		'loc' => 'xxx',
		'lastmod' => 'xxx',
		'changefreq' => 'xxx',
		'priority' => 'xxx',
	)
	*/
	public function addUrl($url) 
	{
		if(is_array($url)) {
			$this->urls[] = $url;
		}else {
			if(is_string($url)) {
				$this->urls[] = array('loc'=>$url);
			}else {
				//throw new Exception('addUrl expects an array or string.');
			}
		}
	}
	
	/**
	* 生成sitemap index文件
	*/
	public function createSitemapIndexFiles()
	{
		$indexsArr = $this->getXmlIndexList(); 
		if (!empty($indexsArr)) {
			$handle = fopen(rtrim($this->config['xmlDirPath'], '/') . '/'.$this->config['sitemapIndexName'].'.xml', 'w+');
			fwrite($handle, $this->config['xmlIndexHeader']);
			foreach ($indexsArr as $val) {
				fwrite($handle, $this->xmlIndexUrl($val['indexUrl']));
			}
			fwrite($handle, $this->config['xmlIndexFooter']);
			fclose($handle);
		}

		$this->urls = []; //清空数据
	}


	/**
	* 生成sitemap xml文件
	*/
	public function createSitemapXmlFile()
	{
		$index = $this->getXmlMaxIndex() + 1;

		$xmlfile = $this->config['xmlName']. $index . '.xml';
		
		$handle = fopen(rtrim($this->config['xmlDirPath'], '/') . '/' . $xmlfile, 'w+');
		fwrite($handle, $this->config['xmlHeader']);

		foreach ($this->urls as $val) {
			fwrite($handle, $this->xmlUrl($val));
		}
		fwrite($handle, $this->config['xmlFooter']);
		fclose($handle);
		
		$this->urls = []; //清空数据

		return $xmlfile;
	}
	
	/**
	* 通过方法设置更改属性值
	*/
	public function setConfig($name, $value)
	{
		if(isset($this->config[$name])) {
			$this->config[$name] = $value;
		}
	}

	/**
	* 通过方法获取属性值
	*/
	public function getConfig($name)
	{
		return isset($this->config[$name])? $this->config[$name]: '';
	}
	
	/**
	* 写入文件
	* @param string $fname 保存的文件名
	* @param array|string $data 保存的数据
	*/
	public static function saveToFile($fname, $data)
	{
		if(empty($data)) {
			return false;
		}
		$handle = fopen($fname, 'w+');
        if($handle === false) {
			return false;
		}
        fwrite($handle, $data);
        fclose($handle);
	}

	/**
	* xml尾部追加内容
	*/
	public function xmlIncAddContent($index)
	{
		$file = $this->config['xmlDirPath'].'/'.$this->config['xmlName']."{$index}.xml";
		$content = file_get_contents($file);
		if (empty($content)) {
			$this->createSitemapXmlFile();
		} else {
			$xml = '';
			foreach ($this->urls as $val) {
				$xml .= $this->xmlUrl($val);
			}
			$content = str_replace('</urlset>', $xml."</urlset>\r\n", $content);
			file_put_contents($file, $content);
			$this->urls = [];
		}
	}

	/**
	* 查询某个xml文件记录数
	*/
	public function getXmlRecord($index = 0)
	{
		if (empty($index)) {
			$index = $this->getXmlMaxIndex();
		}

		$file = $this->config['xmlDirPath'].'/'.$this->config['xmlName']."{$index}.xml";
		$content = file_get_contents($file);
		$count = substr_count($content, '</url>');

		return $count;
	}

	/**
	* 获取xml文件序号列表
	*/	
	protected function getXmlIndexList()
	{
		$files = [];
		$fullPath = $this->config['xmlDirPath'].'/'.$this->config['xmlName'].'*.xml';

		$globArr = glob($fullPath);
		for ($i = 0; $i<count($globArr); $i++) {
            $pathinfo = pathinfo($globArr[$i]);
            $reg = $this->config['xmlName'].'(\d+)\.xml';
            if (preg_match("/{$reg}/", $pathinfo['basename'], $match)) {
                $index = $match[1];
                $files[$index] = [
                	'index' => $index,
                	'indexUrl' => $this->config['xmlIndexDomain'].'/'.$pathinfo['basename'],
                	'basename' => $pathinfo['basename'],
                ];
            } 
        }

        if (!empty($files)) {
        	ksort($files);
        }

        return $files;
	}

	/**
	* 获取当前xml已有文件最大序号
	* @return int 0没有文件 >0已有文件最大序号
	*/
	public function getXmlMaxIndex()
	{
		$index = 0;
		$files = $this->getXmlIndexList();
		if (!empty($files)) {
			$index = end($files)['index'];
		}

        return $index;
	}
	
	/**
	* 拼接xml index数据
	* @param array $xmls xml元素
	*/
	protected function xmlIndexUrl($xmls)
	{
		$xml = '';		
		if(empty($xmls)) {
			return $xml;
		}
		if (!is_array($xmls)) {
			$xmls = array($xmls);
		}

		$xml .= '<sitemap>'."\r\n";
		for ($i = 0; $i < count($xmls); $i++) {
			$xml .= "<loc>{$xmls[$i]}</loc>\r\n";
			$xml .= "<lastmod>".date('Y-m-d')."</lastmod>\r\n";
		}
		$xml .= '</sitemap>'."\r\n";
		
		return $xml;
	}
	
	
	/**
	* 拼接xml数据
	* @param array $url url元素
	*/
	protected function xmlUrl($url)
	{
		$xml = '';
		
		if(empty($url)) {
			return $xml;
		}
		$xml .= '<url>'."\r\n";
		
		$keys = array_keys($url);
		for($i=0; $i<count($keys); $i++) {
			if($keys[$i] == 'loc') {
				$xml .= "<{$keys[$i]}>".$this->setUrlPrefix($url[$keys[$i]])."</{$keys[$i]}>\r\n";
			}else {
				$xml .= "<{$keys[$i]}>{$url[$keys[$i]]}</{$keys[$i]}>\r\n";
			}
		}
		$xml .= '</url>'."\r\n";
		
		return $xml;
	}
	
	/**
	* 设置url前缀或域名
	* @param string $url url
	*/
	protected function setUrlPrefix($url)
	{
		if($this->config['prefix']) {
			$url = $this->config['prefix'] . $url;
		}
		
		return $url;
	}
	
	public function __destruct()
	{
		$this->urls = array(); //清空数据
	}
	
	
	
}
