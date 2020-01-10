<?php

namespace app\model;

use Db;
use think\Model;

/**
* 模型基础类
*/
class BaseModel extends Model
{
    protected static function init()
    {
        
    }

    /**
    * 添加一条记录
    * @param array $data 添加数据
    */
    public static function _add($data)
    {
        if(empty($data)) {
            return false;
        }

        $model = new static;
        $res = $model->save($data);

        return !empty($res)? $model->id: 0;
    }

    /**
    * 更新一条记录
    * @param int $id id
    * @param array $data 更新数据
    */
    public static function _update($id, $data)
    {
        if(empty($id) || empty($data)) {
            return false;
        }

        $model = new static;
        return $model->save($data, ['id' => $id]);
    }


    /**
    * 获取一条记录
    * @param int $id id
    * @param array $field 返回字段
    */
    public static  function getInfo($id, $field = [])
    {
        if (empty($id) || !is_numeric($id)) {
            return [];
        }

        $model = new static;
        $res = $model->field($field)->where(['id'=>$id])->find();

        return !empty($res)? $res->toArray(): [];
    }


    /**
    * 获取一条记录
    * @param array $map 查询条件
    * @param array $field 返回字段
    */
    public static function getInfoByMap($map, $field = [])
    {
        if (empty($map)) {
            return [];
        }

        $model = new static;
        $res = $model->field($field)->where($map)->find();

        return !empty($res)? $res->toArray(): [];
    }

    /**
    * 获取一条记录
    * @param string $identify 标记
    * @param array $field 返回字段
    */
    public static  function getInfoByIdentify($identify, $field = [])
    {
        if (empty($identify)) {
            return [];
        }

        $model = new static;
        $res = $model->field($field)->where(['identify'=>$identify])->find();

        return !empty($res)? $res->toArray(): [];
    }

    /**
    * 获取总记录
    * @param array $map 查询条件
    */
    public static function getTotal($map = [])
    {
        $model = new static;
        return $model->where($map)->count();
    }

    /**
    * 获取最大记录id
    * @return int $id 
    */
    public static function getMaxId($map = [])
    {
        $model = new static;
        $maxid = $model->where($map)->max('id');
        return $maxid? $maxid: 1;
    }

    /**
    * 获取最小记录id
    * @return int $id
    */
    public static function getMinId($map = [])
    {
        $model = new static;
        $minid = $model->where($map)->min('id');
        return $minid? $minid: 1;
    }

    /**
    * 获取分页数据
    * @param array $map where条件
    * @param int $page 分页
    * @param int $limit 每页数量
    * @param string $field 显示字段
    * @param string $order 排序
    */
    public static function getList($map = [], $page = 0, $limit = 0, $field = [], $order = '')
    {
        $model = new static;
        $query = $model->field($field)->where($map);
        if ($page && $limit) {
            $query->page($page, $limit);
        }
        if ($order) {
            $query->order($order);
        }

        $res = $query->select();

        return !empty($res)? $res->toArray(): [];
    }

    /**
    * 更新记录
    * @param int $id id
    * @param array $data 更新数据
    */
    public static function updateByMap($map, $data, $limit = 0, $sort = 'id asc')
    {
        if(empty($map) || empty($data)) {
            return false;
        }

        $model = new static;

        $query = $model->where($map)->order($sort)->data($data);
        if ($limit) {
            $query->limit($limit);
        }
        
        return $query->update();
    }
    

    /**
	* 打印最后执行sql
    */
    public static function _sql()
    {
    	var_dump(Db::getLastSql());
    }

}