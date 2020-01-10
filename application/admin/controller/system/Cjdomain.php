<?php

namespace app\admin\controller\system;

use think\Request;
use app\admin\controller\Admin;
use app\model\crawl\CjDomainModel;
use app\Inc\TableConst;

/**
* 采集站点管理
*/
class Cjdomain extends Admin
{
	public function initialize() 
    {
        parent::initialize();
    }

    /**
	* 采集站点列表
    */
    public function lists(Request $request)
    {
		$regUrl = [
			[
				'reg' => '/.*\/(\d+).htm$/iUs',
				'title' => '格式：xxx/123.htm',
			],
			[
				'reg' => '/.*\/(\d+).html$/iUs',
				'title' => '格式：xxx/123.html',
			],
			[
				'reg' => '/.*\/(\d+).shtml$/iUs',
				'title' => '格式：xxx/123.shtml',
			],
			[
				'reg' => '/.*\/(\w+).html$/iUs',
				'title' => '格式：xxx/abcdef.html',
			],
			[
				'reg' => '/.*\/(\w+).shtml$/iUs',
				'title' => '格式：xxx/abcdef.shtml',
			],
			[
				'reg' => '/.*\/[a-zA-Z]+(\d+)\/?$/iUs',
				'title' => '格式：xxx/abc123/',
			],
			[
				'reg' => '/.*\/r_chengyu_(\w+)\/$/iUs',
				'title' => '格式：xxx/r_chengyu_abc123/',
			],
			[
				'reg' => '/.*\/r_ci_(\w+)\/$/iUs',
				'title' => '格式：xxx/r_ci_abc123/',
			],
			[
				'reg' => '/.*\?type\=idiom&title=(.*)/is',
				'title' => '格式：xxx/view?type=idiom&title=abc',
			],
		];

		$list = CjDomainModel::getList([], 0, 0, ['*'], 'status desc,id asc');

		$this->assign('regUrl', $regUrl);	
		$this->assign('list', $list);	
		return $this->fetch();
    }

    /**
	* 采集站点新增
    */
    public function add(Request $request)
	{
		$domain = $request->post('domain');
		$urlReg = $request->post('url_reg');
		$referUrl = $request->post('refer_url');
		$remark = $request->post('remark', '');

		try {
			if (empty($domain)) {
				throw new \Exception('网站缩写不能为空');
			} elseif (empty($urlReg)) {
				throw new \Exception('请选择采集网址规则');
			} elseif (empty($referUrl)) {
				throw new \Exception('采集网址url不能为空');
			} 
			if (CjDomainModel::checkRowExist($domain, $urlReg)) {
				throw new \Exception('采集url规则已存在');
			}
			
			$arr = [
				'domain' => $domain,
				'url_reg' => $urlReg,
				'refer_url' => $referUrl,
				'status' => TableConst::CJDOMAIN_STATUS_PASS,
				'remark' => $remark,
			];

			if (CjDomainModel::_add($arr)) {
				return $this->JsonSuccess([], '添加成功');
			} else {
				throw new \Exception('添加失败');
			}

		} catch(\Exception $e) {
			return $this->jsonError($e->getMessage());
		}
	}

	/**
	* 采集站点编辑
    */
	public function edit(Request $request)
	{
		$id = $request->post('id');
		$urlReg = $request->post('url_reg');
		$referUrl = $request->post('refer_url');
		$remark = $request->post('remark');

		if ($this->isPost()) {
			try {

				if (empty($id) || !is_numeric($id)) {
					throw new \Exception('请求id参数错误');
				} elseif (empty($urlReg)) {
					throw new \Exception('请选择采集网址规则');
				} elseif (empty($referUrl)) {
					throw new \Exception('采集网址url不能为空');
				} 

				$info = CjDomainModel::getInfo($id);
				if (empty($info)) {
					throw new \Exception('当前记录不存在');
				}

				if (CjDomainModel::checkRowExist($info['domain'], $urlReg, $id)) {
					throw new \Exception('采集url规则已存在');
				}

				$arr = [
					'url_reg' => $urlReg,
					'refer_url' => $referUrl,
					'remark' => $remark,
					'edittime' => date('Y-m-d H:i:s'),
				];

				if (CjDomainModel::_update($id, $arr)) {
					return $this->JsonSuccess([], '修改成功');
				} else {
					throw new \Exception('修改失败');
				}

			} catch(\Exception $e) {
				return $this->jsonError($e->getMessage());
			}
		}
	}

    /**
	* 采集站点删除
    */
    public function del(Request $request)
	{
		$id = $request->get('id');

		$info = CjDomainModel::getInfo($id);
		if (empty($info)) {
			return $this->jsonError('当前记录不存在');
		}

		if ($info['status'] == TableConst::CJDOMAIN_STATUS_DEL) {
			return $this->jsonError('当前记录已是删除状态，请勿重复操作');
		}

		if (CjDomainModel::_update($id, ['status'=> TableConst::CJDOMAIN_STATUS_DEL])) {
			return $this->JsonSuccess([], '删除成功');
		} else {
			return $this->jsonError('删除失败');
		}
	}

	/**
	* 采集站点启用
    */
    public function open(Request $request)
	{
		$id = $request->get('id');

		$info = CjDomainModel::getInfo($id);
		if (empty($info)) {
			return $this->jsonError('当前记录不存在');
		}

		if ($info['status'] == TableConst::CJDOMAIN_STATUS_PASS) {
			return $this->jsonError('当前记录已是启用状态，请勿重复操作');
		}

		if (CjDomainModel::_update($id, ['status'=> TableConst::CJDOMAIN_STATUS_PASS])) {
			return $this->JsonSuccess([], '操作成功');
		} else {
			return $this->jsonError('操作失败');
		}
	}

}