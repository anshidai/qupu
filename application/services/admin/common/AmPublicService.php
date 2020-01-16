<?php

namespace app\services\admin\common;

use think\facade\Request;
use app\components\helper\PaginationHelper;

/**
* 后台公用
*/
class AmPublicService
{
	/**
	* 后台分页
	*/
	public static function showPages($url, $total, $page, $pagesize, $numlinks = 5)
	{
		$baseUrl = $url;
        $firstUrl = preg_replace('/[\?\&]page=\d+/', '', $baseUrl);
        $baseUrl = preg_replace('/[\?\&]page=\d+/', '', $baseUrl);
        if (strpos($baseUrl, '?') !== false) {
            $baseUrl .= '&page={$page}';
        } else {
            $baseUrl .= '?page={$page}';
        }

        $config = array(
            'base_url' => $baseUrl,
            'first_url' => $firstUrl,
            'total_rows' => $total,
            'list_rows' => $pagesize,
            'num_links' => $numlinks,
            'cur_page' => $page,
            'p' => 'page',
            'attributes' => array(
                'class' => 'a-link',
            ),
            
            'full_tag_open' => '<ul class="pagination">',
            'full_tag_close' => '</ul>',
            
            'cur_tag_open' => '<li><a class="active">',
            'cur_tag_close' => '</a></li>',
            
            'num_tag_open' => '<li>',
            'num_tag_close' => '</li>',
            
            'prev_tag_open' => '<li>',
            'prev_tag_close' => '</li>',
            
            'next_tag_open' => '<li>',
            'next_tag_close' => '</li>',
            
            'last_tag_open' => '<li>',
            'last_tag_close' => '</li>',
            
            'first_link' => '首页',
            'last_link' => '末页',
            'prev_link' => '上页',
            'next_link' => '下页',
        );
        $pagination = new PaginationHelper($config);
        $pages = $pagination->createLinks();

        return $pages;
	}


}