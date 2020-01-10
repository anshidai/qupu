<?php

namespace app\services\common;

use think\facade\Request;
use app\components\helper\StringHelper;
use app\components\helper\PaginationHelper;
use app\components\helper\IpLocationHelper;

/**
* 分页业务处理
*/
class BUPages
{	
   
	/**
	* m站分页
	*/
	public static function showMobilePages($baseUrl, $firstUrl, $total, $page, $pagesize, $numlinks = 5)
	{
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
            
            'full_tag_open' => '<p class="pages">',
            'full_tag_close' => '</p>',
            
            'cur_tag_open' => '<b>',
            'cur_tag_close' => '</b>',
            
            'num_tag_open' => '',
            'num_tag_close' => '',
            
            'prev_tag_open' => '',
            'prev_tag_close' => '',
            
            'next_tag_open' => '',
            'next_tag_close' => '',

            'first_tag_open' => '',
            'first_tag_close' => '',
            
            'last_tag_open' => '',
            'last_tag_close' => '',
            
            'first_link' => '首页',
            'last_link' => '',
            'prev_link' => '上页',
            'next_link' => '下页',
        );
        $pagination = new PaginationHelper($config);
        $pages = $pagination->createLinks();

        return $pages;
	}

    /**
    * pc站分页
    */
    public static function showPcPages($baseUrl, $firstUrl, $total, $page, $pagesize, $numlinks = 5)
    {
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
            
            'full_tag_open' => '<p class="pages">',
            'full_tag_close' => '</p>',
            
            'cur_tag_open' => '<b>',
            'cur_tag_close' => '</b>',
            
            'num_tag_open' => '',
            'num_tag_close' => '',
            
            'prev_tag_open' => '',
            'prev_tag_close' => '',
            
            'next_tag_open' => '',
            'next_tag_close' => '',

            'first_tag_open' => '',
            'first_tag_close' => '',
            
            'last_tag_open' => '',
            'last_tag_close' => '',
            
            'first_link' => '首页',
            'last_link' => '',
            'prev_link' => '上页',
            'next_link' => '下页',
        );
        $pagination = new PaginationHelper($config);
        $pages = $pagination->createLinks();

        return $pages;
    }
	

}