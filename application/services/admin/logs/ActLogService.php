<?php

namespace app\services\admin\logs;

use app\Inc\TableConst;
use app\model\common\ActLogModel;
use app\services\admin\page\LoginService;

/**
* 后台操作日志
*/
class ActLogService
{
	/** 
    * 新增日志
    * @param int $resid 资源id
    * @param int $ctype 模块类型
    * @param string $intro 日志描述
    * @param string $content 内容
    */
    public static function addLog($resid, $ctype, $intro = '', $content = '')
    {
        if (!is_array($resid)) {
            $resid = (strpos($resid, ',') !== false)? explode(',', $resid): [$resid];
        }

        for ($i = 0; $i < count($resid); $i++) {
            $arr = [
                'resid' => $resid[$i],
                'intro' => $intro,
                'module_type' => $ctype,
                'act_url' => getPageUrl(),
                'content' => !empty($content)? json_encode($content): '',
            ];

            $title = '';
            if ($ctype == TableConst::ACTLOG_CATEGORY) {
                $title = '分类操作';
            } elseif ($ctype == TableConst::ACTLOG_PERMIS) {
                $title = '权限操作';
            } elseif ($ctype == TableConst::ACTLOG_USER) {
                $title = '用户操作';
            } elseif ($ctype == TableConst::ACTLOG_ROLE) {
                $title = '角色操作';
            } elseif ($ctype == TableConst::ACTLOG_CIYU) {
                $title = '词语操作';
            } elseif ($ctype == TableConst::ACTLOG_CHENGYU) {
                $title = '成语操作';
            } 

            $arr['title'] = $title;

            $user = LoginService::parseLogin();
            if ($user) {
                $arr['act_uid'] = $user['userid'];
                $arr['act_name'] = $user['username'];
            }

           ActLogModel::_add($arr);
        }

        return true;
    }

}

