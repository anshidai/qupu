<?php 

namespace app\components\helper;

/**
* 100% 自定义分页类， 在参考CodeIgniter框架分类页基础上进行二次开发
* @link http://codeigniter.org.cn/user_guide/libraries/pagination.html
* @author libaoan 287639598@qq.com
使用：
$config = array(
	'base_url' => 'http://localhost/news/news/{$page}.html',
	'first_url' => 'http://localhost/news/news/',
	'total_rows' => 100,
	'list_rows' => 10,
	'num_links' => 8,
	'cur_page' => 5,
	'p' => 'p',
	'attributes' => array(
		'class' => 'a-link',
	),
	
	'full_tag_open' => '<div class="pages"><ul>',
	'full_tag_close' => '</ul></div>',
	
	'cur_tag_open' => '<li class="curr">',
	'cur_tag_close' => '</li>',
	
	'num_tag_open' => '<li>',
	'num_tag_close' => '</li>',
	
	'prev_tag_open' => '<li class="prev">',
	'prev_tag_close' => '</li>',
	
	'next_tag_open' => '<li class="next">',
	'next_tag_close' => '</li>',
	
	'last_tag_open' => '<li class="last">',
	'last_tag_close' => '</li>',
	
	'first_link' => '首页',
	'last_link' => '末页',
	'prev_link' => '上一页',
	'next_link' => '下一页',
);

$pagination = new PaginationHelper($config);
echo $pagination->createLinks();
*/

class PaginationHelper
{
	//url前缀
	protected $prefix = '';

	//后缀
	protected $suffix = '';
	
	/*
	* 默认分页的 URL 中显示的是你当前正在从哪条记录开始分页，如果你希望显示实际的页数，将该参数设置为 TRUE
	* 这个就是a)、b)的差别了。开启了，page就会表示页数。false就会表示记录数
	* a) locahost/news/page/2 这个2表示第二页
	* b) localhost/news/page/20 这个20表示从第20条记录开始分页，即页面的第一条记录，是数据库中的第20条记录。
	*/
	protected $use_page_numbers = true;
	
	/**
	 * URI Segment
	 */
	protected $uri_segment = 0;
	
	/*
	* 开启url伪静态模式 true-伪静态 false-动态
	* 重置用法 $pagination->setProperty('page_rewrite', false)
	*/
	protected $page_rewrite = true;
	
	/*
	* 伪静态模式下，分页码标识
	* 重置用法 $pagination->setProperty('page_rewrite_mark', '{$page}')
	*/
	protected $page_rewrite_mark = '{$page}';
	
	//显示分页方式  true-正常  false-精简(例如你只想显示上一页和下一页链接)
	protected $display_pages = true;
	
	protected $_attributes = '';

	protected $_link_types = array();

	/*
	* 默认情况下你的查询字符串参数会被忽略，将这个参数设置为 TRUE ，
	* 将会将查询字符串参数添加到 URI 分段的后面 以及 URL 后缀的前面
	* 例：http://example.com/index.php/test/page/20?query=search%term
	*/
	protected $reuse_query_string = true;

	/*
	* 分页a标签增加自定义属性, 给所有<a>标签都加上data-pagination-page
	* 例如： <a href="xxxx" data-pagination-page="2">下一页</a>
	*/
	protected $data_page_attr = 'data-pagination-page'; 
	
	//分页配置信息
	private $config = array(
		/*
		* 基础链接地址 
		* page_rewrite=>true 伪静态模式 链接格式 'http://localhost/news/{$page}.html', 
		* page_rewrite=>false 动态模式 链接格式 'http://localhost/news/'
		*/
		'base_url' 				=> '',  
		'first_url' 			=> '', //第一个链接设置自定义的url [可选]
		'total_rows' 			=> 0, //总共多少条数据
		'list_rows' 			=> 10, //每页显示几条数据
		'num_links' 			=> 10, //一次显示几个页码
		'cur_page'				=> 1, //当前页码
		'p' 					=> 'p', //分页码标示 [可选]
		'url_suffix' 			=> '', //url后缀 [可选]
		
		/**
		* 给链接添加属性 (例如：所有<a>标签都加上class)
		* array(
			'class' => 'a-link',
			...
		)
		*/
		'attributes' 			=> array(), //[可选]
		
		'full_tag_open' 		=> '', //分页容器的打开标签
		'full_tag_close' 		=> '', //分页容器的关闭标签
		
		'first_tag_open' 		=> '', //"第一页" 链接的打开标签
		'first_tag_close' 		=> '', //"第一页" 链接的关闭标签
		
		'cur_tag_open' 			=> '<strong>', //"当前页" 链接的打开标签
		'cur_tag_close' 		=> '</strong>', //"当前页" 链接的关闭标签
		
		'num_tag_open' 			=> '', //"数字" 链接的打开标签
		'num_tag_close' 		=> '', //"数字" 链接的打开标签
		
		'prev_tag_open' 		=> '', //"上一页" 链接的打开标签
		'prev_tag_close' 		=> '', //"上一页" 链接的关闭标签
		
		'next_tag_open' 		=> '', //"下一页" 链接的打开标签
		'next_tag_close' 		=> '', //"下一页" 链接的关闭标签
		
		'last_tag_open' 		=> '', //"最后一页" 链接的打开标签
		'last_tag_close' 		=> '', //"最后一页" 链接的关闭标签
		
		'first_link' 			=> '&lsaquo; First', //首页 链接名
		'last_link' 			=> 'Last &rsaquo;', //末页 链接名
		
		'prev_link'				=> '&lt;', //上一页 链接名
		'next_link' 			=> '&gt;', //下一页 链接名
	);
	
	
	
	public function __construct($params = array())
	{
		$this->initialize($params);
	}
	
	/**
	 * 初始化
	 * @param array	$params	初始化参数
	 * @return Pagination
	 */
	public function initialize(array $params = array())
	{
		foreach($params as $key => $val) {
			$this->setConfig($key, $val);
		}
		if(!$this->config['p']) {
			$this->config['p'] = 'p';
		}
		if(!$this->config['cur_page']) {
			$this->config['cur_page'] = 1;
		}
		
		//设置配置参数
		if(is_array($this->config['attributes'])) {
			$this->_parse_attributes($this->config['attributes']);
			unset($this->config['attributes']);
		}
		
		//设置url后缀
		if($this->config['url_suffix']) {
			$this->suffix = $this->config['url_suffix'];
		}

		return $this;
	}
	
	/**
     * 定制分页链接设置
     * @param string $name  设置名称
     * @param string $value 设置值
     */
    public function setConfig($name, $value) 
	{
        if(isset($this->config[$name])) {
            $this->config[$name] = $value;
        }
    }
	
	/**
	* 设置属性
	* @param string $name  属性名称
    * @param string $value 属性值
	*/
	public function setProperty($name, $value)
	{
		if(property_exists($this, $name)) {
			$this->$name = $value;
		}
	}
	
	/**
     * 基准链接地址格式化
     */
	public function baseUrl($cur_page)
	{
		$base_url = $this->config['base_url'];
		$append = $this->prefix.$cur_page.$this->suffix;
		if($this->page_rewrite) {
			if(strpos($base_url, $this->page_rewrite_mark) !== false) {
				$url = str_replace($this->page_rewrite_mark, $append, $base_url);
			}else {
				$url = $base_url.$append;
			}
		}else {
			$url = $base_url.'&'.$this->config['p'].'='.$append;
		} 	
		if(strpos($url, 'http://') === false && strpos($url, 'https://') === false) {
			$url = self::isSsl()? 'https://': 'http://'.$url;
		}
		
		return $url;
	}
	
	/**
	 * 创建url
	 * @return	string
	 */
	public function createLinks()
	{
		if($this->config['total_rows'] == 0 || $this->config['list_rows'] == 0) {
			return '';
		}

		//计算页码
		$num_pages = (int)ceil($this->config['total_rows'] / $this->config['list_rows']);
		if($num_pages === 1) {
			return '';
		}
		$this->config['num_links'] = (int) $this->config['num_links'];
		if($this->config['num_links'] < 0) {
			return '';
			//die('Your number of links must be a non-negative number.');
		}
		$base_page = $this->use_page_numbers? 1: 0;
		$get = $_GET;
		
		/************* 处理当前分页页码 start *************/
		$this->cur_page = $this->config['cur_page']>0? $this->config['cur_page']: 1;
		if(!$this->page_rewrite) {
			$this->cur_page = $get[$this->config['p']];
		}elseif(empty($this->cur_page)) {
			$this->cur_page = 1;
		}else {
			$this->cur_page = (string)$this->cur_page;
		}
		
		//检测字符串中的字符是否都是数字，负数和小数会检测不通过 
		if(!ctype_digit($this->cur_page) || ($this->use_page_numbers && (int) $this->cur_page === 0)) {
			$this->cur_page = $base_page;
		} else {
			$this->cur_page = (int)$this->cur_page;
		}
		if(!$this->use_page_numbers) {
			$this->cur_page = (int)floor(($this->cur_page/$this->config['list_rows']) + 1);
		}
		
		if($this->use_page_numbers) {
			if($this->cur_page > $num_pages) {
				$this->cur_page = $num_pages;
			}
		}elseif($this->cur_page > $this->config['total_rows']) {
			$this->cur_page = ($num_pages - 1) * $this->config['list_rows'];
		}
		if(!$this->use_page_numbers) {
			$this->cur_page = (int)floor(($this->cur_page/$this->config['list_rows']) + 1);
		}
		$uri_page_number = $this->cur_page;
		/************* 处理当前分页页码 end *************/
		
		
		/************* 处理伪静态或动态url start *************/
		$base_url = trim($this->config['base_url']);
		$first_url = $this->config['first_url'];
		//伪静态模式
		if($this->page_rewrite) {
			if(empty($first_url)) {
				$first_url = $this->baseUrl(1);
			}
		}else {
			$query_string_sep = (strpos($base_url, '?') === false) ? '?' : '&amp;'; //动态url链接符
			unset($get[$this->config['p']]);
			
			if($this->config['base_url']) {
				$base_url = $this->config['base_url'].$query_string_sep.http_build_query($get);
			}else {
				$base_url = $_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].$query_string_sep.http_build_query($get);
			}
			$this->config['base_url'] = $base_url;
			$first_url = $this->baseUrl(1);
		}
		/************* 处理伪静态或动态url end *************/
		
		
		/************* 拼接分页url start *************/
		//开始页码
		$start = (($this->cur_page - $this->config['num_links']) > 0) ? $this->cur_page - ($this->config['num_links'] - 1) : 1;
		
		//结束页码
		$end = (($this->cur_page + $this->config['num_links']) < $num_pages) ? $this->cur_page + $this->config['num_links'] : $num_pages;
	
		//输出
		$output = '';
		
		//第一个链接
		if($this->config['first_link'] !== false && $this->cur_page > ($this->config['num_links'] + 1 + ! $this->config['num_links'])) {
			$attributes = sprintf('%s %s="%d"', $this->_attributes, $this->data_page_attr, 1);
			$output .= $this->config['first_tag_open'].'<a href="'.$first_url.'"'.$attributes.$this->_attr_rel('start').'>'
				.$this->config['first_link'].'</a>'.$this->config['first_tag_close'];
		}
		
		//上一页
		if($this->config['prev_link'] !== false && $this->cur_page !== 1) {
			$i = ($this->use_page_numbers) ? $uri_page_number - 1 : $uri_page_number - $this->config['list_rows'];
			$attributes = sprintf('%s %s="%d"', $this->_attributes, $this->data_page_attr, ($this->cur_page - 1));
			if($i === $base_page) {
				//第一个链接
				$output .= $this->config['prev_tag_open'].'<a href="'.$first_url.'"'.$attributes.$this->_attr_rel('prev').'>'
					.$this->config['prev_link'].'</a>'.$this->config['prev_tag_close'];
			} else {
				$output .= $this->config['prev_tag_open'].'<a href="'.$this->baseUrl($i).'"'.$attributes.$this->_attr_rel('prev').'>'
					.$this->config['prev_link'].'</a>'.$this->config['prev_tag_close'];
			}
		}
		
		//显示分页方式
		if($this->display_pages) {
			//创建分页码
			for($loop = $start - 1; $loop <= $end; $loop++) {
				$i = ($this->use_page_numbers) ? $loop : ($loop * $this->config['list_rows']) - $this->config['list_rows'];
				$attributes = sprintf('%s %s="%d"', $this->_attributes, $this->data_page_attr, $loop);
				if($i >= $base_page) {
					if($this->cur_page === $loop) {
						//当前页
						$output .= $this->config['cur_tag_open'].$loop.$this->config['cur_tag_close'];
					} elseif ($i === $base_page) {
						//第一个链接
						$output .= $this->config['num_tag_open'].'<a href="'.$first_url.'"'.$attributes.$this->_attr_rel('start').'>'
							.$loop.'</a>'.$this->config['num_tag_close'];
					} else {
						$output .= $this->config['num_tag_open'].'<a href="'.$this->baseUrl($i).'"'.$attributes.'>'
							.$loop.'</a>'.$this->config['num_tag_close'];
					}
				}
			}
		}

		//下一页
		if($this->config['next_link'] !== false && $this->cur_page < $num_pages) {
			$i = ($this->use_page_numbers) ? $this->cur_page + 1 : $this->cur_page * $this->config['list_rows'];
			$attributes = sprintf('%s %s="%d"', $this->_attributes, $this->data_page_attr, $this->cur_page + 1);
			$output .= $this->config['next_tag_open'].'<a href="'.$this->baseUrl($i).'"'.$attributes
				.$this->_attr_rel('next').'>'.$this->config['next_link'].'</a>'.$this->config['next_tag_close'];
		}

		//尾页
		if($this->config['last_link'] !== false && ($this->cur_page + $this->config['num_links'] + ! $this->config['num_links']) < $num_pages) {
			$i = ($this->use_page_numbers) ? $num_pages : ($num_pages * $this->config['list_rows']) - $this->config['list_rows'];
			$attributes = sprintf('%s %s="%d"', $this->_attributes, $this->data_page_attr, $num_pages);
			$output .= $this->config['last_tag_open'].'<a href="'.$this->baseUrl($i).'"'.$attributes.'>'
				.$this->config['last_link'].'</a>'.$this->config['last_tag_close'];
		}
		
		/************* 拼接分页url end *************/
		
		$output = preg_replace('#([^:"])//+#', '\\1/', $output);

		return $this->config['full_tag_open'].$output.$this->config['full_tag_close'];
	}

	/**
	 * 解析自定义属性
	 */
	protected function _parse_attributes()
	{
		$this->config['attributes']['rel'] || $this->config['attributes']['rel'] = true;
		$this->_link_types = ($this->config['attributes']['rel'])
			? array('start' => 'start', 'prev' => 'prev', 'next' => 'next')
			: array();
		unset($this->config['attributes']['rel']);
		
		$this->_attributes = '';
		foreach($this->config['attributes'] as $key => $value)
		{
			$this->_attributes .= ' '.$key.'="'.$value.'"';
		}
	}

	/**
	 * Add "rel" attribute
	 *
	 * @link	http://www.w3.org/TR/html5/links.html#linkTypes
	 * @param	string	$type
	 * @return	string
	 */
	protected function _attr_rel($type)
	{
		if (isset($this->_link_types[$type]))
		{
			unset($this->_link_types[$type]);
			return ' rel="'.$type.'"';
		}

		return '';
	}
	
	/**
	 * 判断是否SSL协议
	 * @return bool
	 */
	public static function isSsl() 
	{
		if(isset($_SERVER['HTTPS']) && ('1' == $_SERVER['HTTPS'] || 'on' == strtolower($_SERVER['HTTPS']))) {
			return true;
		}elseif(isset($_SERVER['SERVER_PORT']) && ('443' == $_SERVER['SERVER_PORT'] )) {
			return true;
		}
		return false;
	}
	
}
