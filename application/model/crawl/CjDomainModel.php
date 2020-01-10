<?php

namespace app\model\crawl;

use think\Request;
use app\model\BaseModel;

/**
* 采集站点模型
*/
class CjDomainModel extends BaseModel
{
	protected $table = 'qp_cj_domain';

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
        $data['edittime'] = date('Y-m-d H:i:s');

        return parent::_add($data);
    }

    /**
	* 检查是否存在
	* @param string $domain 网站缩写
	* @param string $urlreg url规则
	* @param int $notid 过滤id
	* @param bool true:存在 false:不存在
	*/
	public function checkRowExist($domain, $urlreg, $notid = 0)
	{
		$map = [
			['domain', '=', $domain],
			['url_reg', '=', $urlreg],
		];

		if ($notid) {
			array_push($map, ['id', '<>', $notid]);
		}

		return parent::getTotal($map);
	}

}