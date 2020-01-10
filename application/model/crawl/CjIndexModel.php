<?php

namespace app\model\crawl;

use think\Request;
use app\model\BaseModel;

/**
* 采集索引模型
*/
class CjIndexModel extends BaseModel
{
	protected $table = 'qp_cj_index';

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


	/**
	* 检查是否存在记录
	* @param string $url 采集url
    * @return boole true-存在 false-不存在
	*/
	public static function existByUrl($url)
	{
		$map = [
			['url', '=', $url],
		];

		return parent::getTotal($map);
	}

}