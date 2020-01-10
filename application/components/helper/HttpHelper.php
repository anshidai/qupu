<?php 

namespace app\components\helper;

/**
* http请求类
*/
class HttpHelper
{
	/**
	* curl get方式请求
	* @param string $url 请求url 
	* @param array $params 请求参数
	* @param int $timeout 超时 单位：秒 
	* @param return array
	*/
	public static function curlGet($url, $params = array(), $timeout = 30)
	{
		$ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //将curl_exec()获取的信息以文件流的形式返回，而不是直接输出
        curl_setopt($ch, CURLOPT_HEADER, 0); //是否取得头信息 
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); //是否抓取跳转后的页面
        curl_setopt($ch, CURLOPT_USERAGENT, isset($params['useragent'])? $params['useragent']: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36');
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout); //接收信息时的超时设置
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout); //建立连接时候的超时设置
		
		if(isset($params['referer'])) {
            curl_setopt($ch, CURLOPT_REFERER, $params['referer']);
        }
        if(isset($params['header'])) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $params['header']);
        }
		if(isset($params['cookie'])) {
            curl_setopt($ch, CURLOPT_COOKIE, $params['cookie']);
        }
        if(!empty($params['proxy'])) {
        	curl_setopt($ch, CURLOPT_PROXYTYPE, 'HTTP');
            curl_setopt($ch, CURLOPT_PROXY, $params['proxy']['ip']); //代理服务器地址 如：112.65.219.72

            //代理服务器端口
            if($params['proxy']['port']) {
            	curl_setopt($ch, CURLOPT_PROXYPORT, $params['proxy']['port']); 
            }else {
            	curl_setopt($ch, CURLOPT_PROXYPORT, 80);
            }
            if($params['proxy']['userpwd']) {
            	curl_setopt($ch, CURLOPT_PROXYUSERPWD, $params['proxy']['userpwd']); //http代理认证帐号，username:password的格式
            }
        }
		if(isset($params['gzip'])) {
			curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
		}
		if(isset($params['ssl'])) {
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		}
		
		$data['content'] = curl_exec($ch);
		$data['httpcode'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$data['error'] = curl_error($ch);
		
		curl_close($ch);
        return $data;
	}
	
	/**
	* curl post方式请求
	* @param string $url 请求url 
	* @param array $post post提交数据 
	* @param int $timeout 超时 单位：秒
	* @param return array	
	*/
	public static function curlPost($url, $post = array(), $params = array(), $timeout = 30)
	{
		$ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);

        curl_setopt($ch, CURLOPT_USERAGENT, isset($params['useragent'])? $params['useragent']: 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/31.0.1650.57 Safari/537.36');
		curl_setopt($ch, CURLOPT_TIMEOUT, 10); //接收信息时的超时设置
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout); //建立连接时候的超时设置
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);

		if(isset($params['referer'])) {
            curl_setopt($ch, CURLOPT_REFERER, $params['referer']);
        }
        if(isset($params['header'])) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $params['header']);
        }
		if(isset($params['cookie'])) {
            curl_setopt($ch, CURLOPT_COOKIE, $params['cookie']);
        }
        if(!empty($params['proxy'])) {
        	curl_setopt($ch, CURLOPT_PROXYTYPE, 'HTTP');
            curl_setopt($ch, CURLOPT_PROXY, $params['proxy']['ip']); //代理服务器地址 如：112.65.219.72

            //代理服务器端口
            if($params['proxy']['port']) {
            	curl_setopt($ch, CURLOPT_PROXYPORT, $params['proxy']['port']); 
            }else {
            	curl_setopt($ch, CURLOPT_PROXYPORT, 80);
            }
            if($params['proxy']['userpwd']) {
            	curl_setopt($ch, CURLOPT_PROXYUSERPWD, $params['proxy']['userpwd']); //http代理认证帐号，username:password的格式
            }
        }
        if(isset($params['ssl'])) {
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		}
		
		$data['content'] = curl_exec($ch);
		$data['httpcode'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$data['error'] = curl_error($ch);
		
		curl_close($ch);
        return $data;
	}
	
	/**
	* curl 批量get请求
	* @param array() $urls 请求url集合
	* @param array $params 请求参数
	* @param int $timeout 超时 单位：秒 
	* @param return array
	*/
	public static function curlGetMulti($urls, $params = array(), $timeout = 30)
	{
		if(empty($urls)) return '';
		if(!is_array($urls)) $urls = (array)$urls;
		
		$queue = curl_multi_init();
		$map = array();
		foreach($urls as $url) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //将curl_exec()获取的信息以文件流的形式返回，而不是直接输出
			curl_setopt($ch, CURLOPT_HEADER, 0); //是否取得头信息 
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); //是否抓取跳转后的页面
			curl_setopt($ch, CURLOPT_USERAGENT, isset($params['useragent'])? $params['useragent']: 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/57.0.2987.133 Safari/537.36');
			curl_setopt($ch, CURLOPT_TIMEOUT, $timeout); //接收信息时的超时设置
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout); //建立连接时候的超时设置
			
			if(isset($params['referer'])) {
				curl_setopt($ch, CURLOPT_REFERER, $params['referer']);
			}
			if(isset($params['header'])) {
				curl_setopt($ch, CURLOPT_HTTPHEADER, $params['header']);
			}
			if(isset($params['cookie'])) {
				curl_setopt($ch, CURLOPT_COOKIE, $params['cookie']);
			}
			if(!empty($params['proxy'])) {
	        	curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
	            curl_setopt($ch, CURLOPT_PROXY, $params['proxy']['ip']); //代理服务器地址 如：112.65.219.72

	            //代理服务器端口
	            if($params['proxy']['port']) {
	            	curl_setopt($ch, CURLOPT_PROXYPORT, $params['proxy']['port']); 
	            }else {
	            	curl_setopt($ch, CURLOPT_PROXYPORT, 80);
	            }
	            if($params['proxy']['userpwd']) {
	            	curl_setopt($ch, CURLOPT_PROXYUSERPWD, $params['proxy']['userpwd']); //http代理认证帐号，username:password的格式
	            }
	            
	        }
			if(isset($params['gzip'])) {
				curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
			}
			if(isset($params['ssl'])) {
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			}
			
			curl_multi_add_handle($queue, $ch);
			$map[(string) $ch] = $url;
		}
		
		$data = array();
		do{
			while(($code = curl_multi_exec($queue, $active)) == CURLM_CALL_MULTI_PERFORM);
			if($code != CURLM_OK) {
				break; 
			}
			while($done = curl_multi_info_read($queue)) {
				$error = curl_error($done['handle']);
				$httpcode = curl_getinfo($done['handle'], CURLINFO_HTTP_CODE);
				$content = curl_multi_getcontent($done['handle']);
				$data[$map[(string) $done['handle']]] = compact('error', 'httpcode', 'content');
				curl_multi_remove_handle($queue, $done['handle']);
				curl_close($done['handle']);
                usleep(1000);
			}
			if($active > 0) {
				curl_multi_select($queue, 0.5);
                usleep(1000);
			}
		}while($active);
		
		curl_multi_close($queue);
		return $data; 
	}
	
	/**
	* file_get_contents get方式请求
	* @param string $url 请求url 
	* @param int $timeout 超时 单位：秒
	* @param return string	
	*/
	public static function fileGetContent($url, $timeout = 30)
	{
		$opts['http'] = array(
			'method' => 'GET',
			'timeout' => $timeout
		);
		return file_get_contents($url, false, stream_context_create($opts));
	}
	
	/**
	* file_get_contents post方式请求
	* @param string $url 请求url 
	* @param array $post post提交数据 
	* @param int $timeout 超时 单位：秒
	* @param return string
	*/
	public static function filePostContent($url, $post = array(), $timeout = 30)
	{
		if(!is_array($post))
			$post = (array)$post;
		
		$opts['http'] = array(
			'method' => 'POST',
			'timeout' => $timeout,
			'content' => http_build_query($post)
		);
		return file_get_contents($url, false, stream_context_create($opts));
	}
	
}
