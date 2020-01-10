<?php

namespace app\Inc;

/**
* 定义表字段状态
*/
class TableConst
{
	//日志类型
	const ACTLOG_USER = 1;  //管理用户
    const ACTLOG_ROLE = 2;  //角色
    const ACTLOG_PERMIS = 3;  //权限
    const ACTLOG_CATEGORY = 4; //分类
    const ACTLOG_CIYU = 5; //词语
    const ACTLOG_CHENGYU = 6; //成语
    const ACTLOG_TAGS = 7; //tags

    //系统管理员
    const SYS_ADMIN = 1; //是

    //管理员启用状态
	const ADMIN_STATUS_DEFAULT = 1; //未启用
    const ADMIN_STATUS_DEL = 2; //已删除
    const ADMIN_STATUS_ENABLED = 3; //启用

    //角色状态
	const ROLE_STATUS_DISABLED = 1; //禁用
	const ROLE_STATUS_ENABLED = 2; //开启
	const ROLE_IS_ADMIN_YES = 1; //是否管理员 是 

	//权限状态
	const PERMISSION_STATUS_DISABLED = 1; //禁用
	const PERMISSION_STATUS_ENABLED = 2; //开启

	//权限类型
	const PERMISSION_CTYPE_MENU = 1; //菜单
	const PERMISSION_CTYPE_PAGE = 2; //页面
	const PERMISSION_CTYPE_BTN = 3; //按钮

	//歌谱审核状态
    const GEPU_STATUS_DEFAULT = 0; //默认
	const GEPU_STATUS_NOT = 1; //未审核
    const GEPU_STATUS_DEL = 2; //已删除
	const GEPU_STATUS_PASS = 3; //审核通过
	public static $gepuStatusList = [
		self::GEPU_STATUS_NOT => '待审核',
		self::GEPU_STATUS_DEL => '已删除',
		self::GEPU_STATUS_PASS => '审核通过',
	];

	//歌谱前台是否显示
    const GEPU_SHOW_DEFAULT = 0; //默认
    const GEPU_SHOW_HIDE = 1; //不显示
    const GEPU_SHOW_OK = 2; //显示

    //分类类型
    const CATE_TYPE_GEPU = 1;
	const CATE_TYPE_AUTHOR = 2;
	public static $cateTypeList = [
        self::CATE_TYPE_GEPU => '歌谱',
		self::CATE_TYPE_AUTHOR => '作者',
	];

	//分类开启状态
	const CATE_STATUS_NO = 1; //未开启
	const CATE_STATUS_DISABLED = 2; //禁用
	const CATE_STATUS_ENABLED = 3; //开启
	const CATE_IS_END_SELECT = 1; //是终极目录

	//采集状态
	const CJ_STATUS_HTTP_NOT = 0; //未操作
	const CJ_STATUS_HTTP_FAIL = 1; //http请求失败
	const CJ_STATUS_HTTP_ERROR = 2; //采集内容不符合要求
	const CJ_STATUS_HTTP_SUCCESS = 3; //采集成功

	//采集类型
	const CJ_CTYPE_GEPU = 1; //歌谱

	//采集站点规则状态
	const CJDOMAIN_STATUS_DEL = 1; //删除
	const CJDOMAIN_STATUS_PASS = 2; //通过

     //附件类型
    const ATTACHMENT_CTYPE_GEPU = 1; //歌谱

    //附件审核状态
    const ATTACHMENT_STATUS_DISABLED = 1; //无效
    const ATTACHMENT_STATUS_ENABLED = 2; //有效

}