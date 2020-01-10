<?php

/**
* 定义redis,memcache,filecache等缓存key
* 格式 如：
    'city_online_tree' => 'city_online_area',
    'city_child_tree' => 'city_child_tree_%s', %s代表参数
    
* 用法：
* COM::getCachekey($name, $params);
*/

return array(

	//后台用户权限
	'admin_user_permis' => 'admin_user_permis_%s',

	//后台所有分类
	'admin_cate_all' => 'admin_cate_all_%s',

	//获取分类所有子类
	'home_cate_parent' => 'home_cate_parent_%s',

	//根据分类获取最新文章
	'home_best_cids' => 'home_best_cids_%s_%s',

	//根据浏览量获取文章
	'home_hits_cids' => 'home_hits_cids_%s_%s',

	//根据分类随机获取文章
	'home_rand_cids' => 'home_rand_cids_%s_%s',

	//根据随机获取文章
	'home_rand_index' => 'home_rand_index_%s',

	'home_idiom_rand_index' => 'home_idiom_rand_index_%s',

	'home_words_rand_index' => 'home_words_rand_index_%s',

	//根据随机获取文章
	'home_rand_cid_index' => 'home_rand_cid_index_%s_%s',
	
	'home_rand_cid_filter' => 'home_rand_cid_filter_%s_%s_%s',

	//获取当天最新文章
	'home_today_article' => 'home_today_article_%s',

	//成语标题缓存
	'idiom_title_cache' => 'idiom_title_cache',
);