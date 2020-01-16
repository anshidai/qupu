<?php

Route::domain('admin-qupu.jupeixun.cn', function() {
	Route::rule('','admin/Page/login', 'get');
	
	Route::rule('admin/welcome','admin/Admin/welcome', 'get');

	Route::group('page', function(){
		Route::rule('login','admin/Page/login', 'get|post');
		Route::rule('logout','admin/Page/logout', 'get|post');
		Route::rule('verify','admin/Page/verify', 'get');
	});
	
	//分类路由
	Route::group('cate', function(){
		Route::rule('list','admin/category.Category/lists', 'get');
		Route::rule('add','admin/category.Category/add', 'get|post');
		Route::rule('edit','admin/category.Category/edit', 'get|post');
		Route::rule('parentCate','admin/category.Category/parentCate', 'get');
		Route::rule('CateOne','admin/category.Category/CateOne', 'get');
		Route::rule('CateTwo','admin/category.Category/CateTwo', 'get');
		Route::rule('CateThree','admin/category.Category/CateThree', 'get');
		Route::rule('changeClose','admin/category.Category/changeClose', 'get');
		Route::rule('changeOpen','admin/category.Category/changeOpen', 'get');
		Route::rule('changeSort','admin/category.Category/changeSort', 'get');
	});

	//作者路由
	Route::group('author', function(){
		Route::rule('list','admin/songs.Author/lists', 'get');
		Route::rule('auditlist','admin/songs.Author/auditlist', 'get');
		Route::rule('add','admin/songs.Author/add', 'get|post');
		Route::rule('edit','admin/songs.Author/edit', 'get|post');
		Route::rule('changePass','admin/songs.Author/changePass', 'get');
		Route::rule('changePush','admin/songs.Author/changePush', 'get');
		Route::rule('changeDel','admin/songs.Author/changeDel', 'get');
	});

	//歌谱路由
	Route::group('gepu', function(){
		Route::rule('list','admin/songs.Gepu/lists', 'get');
		Route::rule('auditlist','admin/songs.Gepu/auditlist', 'get');
		Route::rule('add','admin/songs.Gepu/add', 'get|post');
		Route::rule('edit','admin/songs.Gepu/edit', 'get|post');
		Route::rule('changePass','admin/songs.Gepu/changePass', 'get');
		Route::rule('changePush','admin/songs.Gepu/changePush', 'get');
		Route::rule('changeDel','admin/songs.Gepu/changeDel', 'get');
	});

	//后台管理员路由
	Route::group('adminuser', function(){
		Route::rule('list','admin/auth.AdminUser/lists', 'get');
		Route::rule('add','admin/auth.AdminUser/add', 'get|post');
		Route::rule('edit','admin/auth.AdminUser/edit', 'get|post');
		Route::rule('changeDel','admin/auth.AdminUser/changeDel', 'get');
		Route::rule('changeClose','admin/auth.AdminUser/changeClose', 'get');
		Route::rule('changeOpen','admin/auth.AdminUser/changeOpen', 'get');
		Route::rule('permis','admin/auth.AdminUser/permis', 'get|post');
		Route::rule('changePwd','admin/auth.AdminUser/changepwd', 'get|post');
	});

	//权限路由
	Route::group('permission', function(){
		Route::rule('list','admin/auth.Permission/lists', 'get');
		Route::rule('add','admin/auth.Permission/add', 'get|post');
		Route::rule('edit','admin/auth.Permission/edit', 'get|post');
		Route::rule('changeClose','admin/auth.Permission/changeClose', 'get');
		Route::rule('changeOpen','admin/auth.Permission/changeOpen', 'get');
	});
	
	//角色路由	
	Route::group('role', function(){
		Route::rule('list','admin/auth.Role/lists', 'get');
		Route::rule('add','admin/auth.Role/add', 'get|post');
		Route::rule('edit','admin/auth.Role/edit', 'get|post');
		Route::rule('permis','admin/auth.Role/permis', 'get|post');
		Route::rule('changeClose','admin/auth.Role/changeClose', 'get');
		Route::rule('changeOpen','admin/auth.Role/changeOpen', 'get');
	});
	
	//兜底路由
	// Route::miss('public/miss');
});

Route::domain('api-qupu.jupeixun.cn', function() {

	Route::rule('locoyApi/spider','api/LocoyApi/spider', 'post');
	Route::rule('locoyApi/getCategory','api/LocoyApi/getCategory', 'get');

	Route::rule('LocoyCiyuApi/spider','api/LocoyCiyuApi/spider', 'post');
	Route::rule('LocoyCiyuApi/getCategory','api/LocoyCiyuApi/getCategory', 'get');

	Route::rule('LocoyChengyuApi/spider','api/LocoyChengyuApi/spider', 'post');
	Route::rule('LocoyChengyuApi/update','api/LocoyChengyuApi/update', 'post');
	Route::rule('LocoyChengyuApi/getCategory','api/LocoyChengyuApi/getCategory', 'get');

});

Route::domain('qp.jupeixun.cn', function() {
	// Route::get('','home/Index/index');
	Route::get('','home/Chengyu/lists');
	Route::get('ciyu/','home/Ciyu/lists');
	Route::get('ciyu/<pinyin>/','home/Ciyu/lists')->pattern(['pinyin' => '[a-zA-Z0-9]+']);
	Route::get('ciyu/<identify>','home/Ciyu/detail')->pattern(['identify' => '[a-zA-Z0-9]+'])->ext('html');

	Route::get('chengyu/','home/Chengyu/lists');
	Route::get('chengyu/<pinyin>/','home/Chengyu/lists')->pattern(['pinyin' => '[a-zA-Z0-9]+']);
	Route::get('chengyu/<identify>','home/Chengyu/detail')->pattern(['identify' => '[a-zA-Z0-9]+'])->ext('html');

	Route::get('robots','home/Robots/robot')->ext('txt');
});

Route::domain('qupu.jupeixun.cn', function() {
	// Route::get('','mobile/Index/index');
	Route::get('','mobile/Chengyu/lists');
	Route::get('ciyu/','mobile/Ciyu/lists');
	Route::get('ciyu/<pinyin>/','mobile/Ciyu/lists')->pattern(['pinyin' => '[a-zA-Z0-9]+']);
	Route::get('ciyu/<identify>','mobile/Ciyu/detail')->pattern(['identify' => '[a-zA-Z0-9]+'])->ext('html');

	Route::get('chengyu/','mobile/Chengyu/lists');
	Route::get('chengyu/<pinyin>/','mobile/Chengyu/lists')->pattern(['pinyin' => '[a-zA-Z0-9]+']);
	Route::get('chengyu/<identify>','mobile/Chengyu/detail')->pattern(['identify' => '[a-zA-Z0-9]+'])->ext('html');
	
	Route::get('robots','mobile/Robots/robot')->ext('txt');
});


return [];
