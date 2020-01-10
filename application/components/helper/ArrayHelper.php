<?php 

namespace app\components\helper;

/**
* 数组处理类
*/
class ArrayHelper
{
	
	/**
	* 从数组中删除空白的元素（包括只有空白字符的元素）
	* 用法：
    * $arr = array('', 'test', '   ');
    * ArrayHelper::removeEmpty($arr);
    * var_dump($hashmap);
    *   // 输出结果为
    *   array(
    *      'test'
    *    )
	 
	* @param array $arr 要处理的数组
	* @param boolean $trim 是否对数组元素调用 trim 函数
	*/
	public static function removeEmpty(&$arr, $trim = true)
	{
		foreach($arr as $key => $val) {
            if(is_array($val)) {
                self::removeEmpty($arr[$key]);
            }else {
                $val = trim($val);
                if($val == '') {
                    unset($arr[$key]);
                }elseif($trim) {
                    $arr[$key] = $val;
                }
            }
        }
	}
	
	/**
	* 从一个二维数组中返回指定键的所有值
	* @param array $arr 数据源
	* @param string $col 要查询的键
	* @return array 包含指定键所有值的数组
	*/
	public static function getCols($arr, $col)
	{
		$ret = array();
        foreach($arr as $val) {
            if(isset($val[$col])) {
                $ret[] = $val[$col];
            }
        }
        return $ret;
	}
	
	/**
     * 将一个二维数组转换为 HashMap，并返回结果
     *
     * 用法1：
     * $rows = array(
     *     array('id' => 1, 'value' => '1-1'),
     *     array('id' => 2, 'value' => '2-1'),
     * );
     * $hashmap = ArrayHelper::toHashmap($rows, 'id', 'value');
     *
     * var_dump($hashmap);
     *   // 输出结果为
     *    array(
     *      1 => '1-1',
     *      2 => '2-1',
     *    )
     *
     * 如果省略 $valueField 参数，则转换结果每一项为包含该项所有数据的数组。
     *
     * 用法2：
     * @code php
     * $rows = array(
     *     array('id' => 1, 'value' => '1-1'),
     *     array('id' => 2, 'value' => '2-1'),
     * );
     * $hashmap = ArrayHelper::toHashmap($rows, 'id');
     *
     * var_dump($hashmap);
     *   // 输出结果为
     *    array(
     *      1 => array('id' => 1, 'value' => '1-1'),
     *      2 => array('id' => 2, 'value' => '2-1'),
     *    )
     *
     * @param array $arr 数据源
     * @param string $keyField 按照什么键的值进行转换
     * @param string $valueField 对应的键值
     * @return array 转换后的 HashMap 样式数组
     */
    public static function toHashmap($arr, $keyField, $valueField = null)
    {
        $ret = array();
        if($valueField) {
            foreach($arr as $val) {
                $ret[$val[$keyField]] = $val[$valueField];
            }
        } else {
            foreach($arr as $val) {
                $ret[$val[$keyField]] = $val;
            }
        }
        return $ret;
    }
     
    /**
     * 将一个二维数组按照指定字段的值分组
     *
     * @param array $arr 数据源
     * @param string $keyField 作为分组依据的键名
     * @return array 分组后的结果
     */
    public static function groupBy($arr, $keyField)
    {
        $ret = array();
        foreach ($arr as $val) {
            $key = $val[$keyField];
            $ret[$key][] = $val;
        }
        return $ret;
    }
     
    /**
     * 将一个平面的二维数组按照指定的字段转换为树状结构
     *
     * 如果要获得任意节点为根的子树，可以使用 $refs 参数：
     * 用法：
     * $refs = null;
     * $tree = ArrayHelper::tree($rows, 'id', 'parent', 'nodes', $refs);
     *
     * // 输出 id 为 3 的节点及其所有子节点
     * $id = 3;
     * var_dump($refs[$id]);
     *
     * @param array $arr 数据源
     * @param string $keyNodeId 节点ID字段名
     * @param string $keyParentId 节点父ID字段名
     * @param string $keyChildrens 保存子节点的字段名
     * @param boolean $refs 是否在返回结果中包含节点引用
     * return array 树形结构的数组
     */
    public static function toTree($arr, $keyNodeId, $keyParentId = 'parent_id', $keyChildrens = 'childrens', &$refs = null)
    {
        $refs = array();
        foreach($arr as $offset => $val) {
            $arr[$offset][$keyChildrens] = array();
            $refs[$val[$keyNodeId]] = &$arr[$offset];
        }
     
        $tree = array();
        foreach($arr as $offset => $val) {
            $parentId = $val[$keyParentId];
            if($parentId) {
                if(!isset($refs[$parentId])) {
                    $tree[] = &$arr[$offset];
                    continue;
                }
                $parent = &$refs[$parentId];
                $parent[$keyChildrens][] =& $arr[$offset];
            }
            else {
                $tree[] = &$arr[$offset];
            }
        }
        return $tree;
    }
     
    /**
     * 将树形数组展开为平面的数组
     * 这个方法是 tree() 方法的逆向操作。
     *
     * @param array $tree 树形数组
     * @param string $keyChildrens 包含子节点的键名
     * @return array 展开后的数组
     */
    public static function treeToArray($tree, $keyChildrens = 'childrens')
    {
        $ret = array();
        if(isset($tree[$keyChildrens]) && is_array($tree[$keyChildrens])) {
            foreach($tree[$keyChildrens] as $child) {
                $ret = array_merge($ret, self::treeToArray($child, $keyChildrens));
            }
            unset($node[$keyChildrens]);
            $ret[] = $tree;
        }
        else {
            $ret[] = $tree;
        }
        return $ret;
    }
     
    /**
     * 根据指定的键对数组排序
     *
     * @param array $array 要排序的数组
     * @param string $keyname 排序的键
     * @param int $dir 排序方向
     * @return array 排序后的数组
     */
    public static function sortByCol($array, $keyname, $dir = SORT_ASC)
    {
        return self::sortByMultiCols($array, array($keyname => $dir));
    }
     
    /**
     * 将一个二维数组按照多个列进行排序，类似 SQL 语句中的 ORDER BY
     * 用法：
     * $rows = ArrayHelper::sortByMultiCols($rows, array(
     *     'parent' => SORT_ASC,
     *     'name' => SORT_DESC,
     * ));
     * @param array $rowset 要排序的数组
     * @param array $args 排序的键
     * @return array 排序后的数组
     */
    public static function sortByMultiCols($rowset, $args)
    {
        $sortArray = array();
        $sortRule = '';
        foreach($args as $sortField => $sortDir) {
            foreach ($rowset as $offset => $row) {
                $sortArray[$sortField][$offset] = $row[$sortField];
            }
            $sortRule .= '$sortArray[\'' . $sortField . '\'], ' . $sortDir . ', ';
        }
        if(empty($sortArray) || empty($sortRule)) {
            return $rowset;
        }
        eval('array_multisort(' . $sortRule . '$rowset);');
        return $rowset;
    }
	
	/**
     * 合并两个数组
     * @param array $arr1
     * @param array $arr2
     * @return array 合并后数组
     */
    public static function arrayMerge($arr1, $arr2)
    {
        if(!is_array($arr1) || !is_array($arr2)) {
            return array();
        }
        $ret = $arr1;
        foreach($arr2 as $key => $val) {
            $ret[$key] = $val;
        }
        return $ret;
    }
	
	/**
     * 检查个数组中所有元素是否在字符串中出现
     * @param array|string $search 查找内容
     * @param string $text 在该内容中进行查找
	 * @return boole true:存在 false：不存在
     */
    public static function searchInText($search, $text)
    {
        if(is_array($search)) {
            $res = preg_match("/" . implode('|',$search) . "/", $text);
        } else {
            $res = strpos($text, $search);
            if($res !== false) {
                $res = true;
            }
        }
        if($res) {
            $res = true;
        } else {
            $res = false;
        }
        return $res;
    }
	
	/**
     * 不区分大小写的in_array实现
     * @param array|string $value 查找内容
     * @param array $array 在该内容中进行查找
	 * @return boole true:存在 false：不存在
     */
	public static function inArrayCase($value, $array)
	{
		return in_array(strtolower($value),array_map('strtolower',$array));
	}
	
	/**
    * 将一个数组分割成多个数组
    * @param array $inputArr 输入数组
    * @param int $size 拆分后单元数组个数
    */
    public static function chunkArr($inputArr, $size)
    {
        if(empty($inputArr)) return array();
        
        if(!is_array($inputArr)) $inputArr = array($inputArr);
        
        return array_chunk($inputArr, $size);    
    }
	
	/**
	* 在现有数组中插入某个元素
	*/
	public static function addArrayKey($inputArr, $name, $value = '')
	{
		if(empty($inputArr)) return array();
		
		if(!is_array($inputArr)) $inputArr = array($inputArr);
	
		foreach($inputArr as $key=>$val) {
			$data[$key][$name] = isset($val[$value])? $val[$value]: $val;
		}
		
		return $data;
	}
	
	/**
    * 返回分割后数组
    * @param array $arr 输入数组
    * @param string $keyField 主键名称
    * @param int $size 每个数组存放多少个元素
    */
    public static function loopArr($arr, $keyField, $size = 10)
    {
        if(empty($arr)) return array();
        
        foreach($arr as $val) {
            $data[$val[$keyField]] = $val[$keyField];    
        }
        return isset($data)? self::chunkArr($data, $size): array();
    }
    
    /**
    * 数组对象转换成数组 
    */
    public static function arrayObjectToArray($arrobject)
    {
        $_array = is_object($arrobject)? get_object_vars($arrobject): $arrobject;
        foreach($_array as $key => $value) {
            $value = (is_array($value) || is_object($value)) ? self::arrayObjectToArray($value) : $value;
            $array[$key] = $value;    
        } 
        return $array;   
    }

    /**
    * 对象转换成数组 
    */
    public static function objectToArray($obj) {
        $obj = (array)$obj;
        foreach($obj as $k => $v) {
            if(gettype($v) == 'resource') {
                return;
            }
            if(gettype($v) == 'object' || gettype($v) == 'array') {
                $obj[$k] = (array)self::objectToArray($v);
            }
        }
        return $obj;
    }
    
	
	
}