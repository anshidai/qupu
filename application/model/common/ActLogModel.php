<?php

namespace app\model\common;

use think\Request;
use app\model\BaseModel;

/**
* 后台操作日志模型
*/
class ActLogModel extends BaseModel
{
    protected $table = 'qp_act_log';
    
	/**
	* 添加一条记录
	* @param array $data 添加数据
	*/
    public static function _add($data)
    {
    	if(empty($data)) {
    		return false;
    	}
        $data['addtime'] = date('Y-m-d H:i:s');

        return parent::_add($data);
    }



}