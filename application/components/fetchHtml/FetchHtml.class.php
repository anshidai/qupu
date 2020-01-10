<?php

namespace Components\fetchHtml;

require_once __DIR__.'/simple_html_dom.php';

/**
* 页面抓取方法、 基于simple_html功能实现 
*/
class FetchHtml
{
    
    static $objectHtml = ''; #html页面解析后内容
    
    /**
    * 获取网页部分的内容
    * @param $paramArr 参数：如下参数详情
    * array(
    *   'node'=>array(
    *       'element' => '', #节点元素名称 可以是id、class
    *       'index' => 0, #节点索引位置 0第一个 1第二个...依次类推
    *   )
    * )
    * @return html
    */
    public static function getNodeHtml($paramArr, $simple_html)
    {
        $options = array(
            'node' => array(
                'element' => '', #节点元素名称 可以是id、class
                'index' => '0', #节点索引位置 0第一个 1第二个...依次类推
            )
        );
        if(is_array($paramArr)) {
            $options = array_merge($options, $paramArr);
        }
        extract($options);
        
        self::parserHtml($simple_html);
        
        if(!empty(self::$objectHtml)) {
            return self::$objectHtml->find($node['element'],$node['index']);    
        }else {
            return false;
        }
    }
    
        /**
    * 获取指定区域的内容
    * @param $paramArr 参数：如下参数详情
    *   'node'=>array(
    *       'element' => '', #节点元素名称 可以是id(如：div#nav) 、class(如：div.nav)
    *       'index' => 0, #节点索引位置 0第一个 1第二个...依次类推
    *   ),
    *   'items'=>array(
    *       #键名
    *       'name'=>array( 
    *            'element' => 'li>a', #查找的节点元素 多个用> 目前就支持2级 支持元素class(如：div.nav)和元素id(如：div#nav) 
    *                                还可以连贯 div#nav>li
    *           'node' => 'all', //是否匹配所有此元素 all-匹配所有 留空或其他值 则表示取一次元素
    *            'index' => 0, #子节点索引位置 默认0 0第一个 1第二个...依次类推
    *           'attr' => 'href' #获取元素属性,留空或不设置将获取 element元素的内容
    *       ),
    *       //键名可以有多个
    *       'linkurl'=>array(
    *           'index' => '0',
    *           'element' => 'ul#nav>li',
    *           'attr' => 'href'
    *       ),
    *   )
    * )
    * @param $simple_html 针对区域内容再次进行抓取 默认为空
    * @return html
    */
    public static function getNodeAttribute($paramArr, $simple_html)
    {
        
        //如果没有子节点配置就直接返回父节点数据
        if(!empty($paramArr['node']) && empty($paramArr['items'])) {
            return self::getNodeHtml($paramArr, $simple_html);
        }
        
        $options = array('items'=>array());
        if(is_array($paramArr)) {
            $options = array_merge($options, $paramArr);
        }
        
        if(empty($simple_html)) return false;
        
        if(is_object($simple_html)) {
            self::$objectHtml = $simple_html;
        }else {
            self::$objectHtml = self::getNodeHtml($paramArr, $simple_html);
        }
        
        if(empty(self::$objectHtml)) return false;

        $data = array();
        foreach($paramArr['items'] as $k=>$item) {
            $item['index'] = $item['index']? $item['index']: 0;
            $nodes = explode('>',$item['element']);
            $len = count($nodes);
            if($item['node'] === 'all') {
                foreach(self::$objectHtml->find($nodes[0]) as $key=>$element) {
                    if($len == 1) {
                        $data[$k][] = empty($item['attr'])? $element->innertext: $element->$item['attr'];
                    }else if($len == 2) {
                        if($element->find($nodes[1], $item['index'])) {
                            $data[$k][] = empty($item['attr'])? $element->find($nodes[1], $item['index'])->innertext: $element->find($nodes[1],$item['index'])->$item['attr'];     
                        }else {
                            $data[$k][] = '';    
                        } 
                    }
                }
            }else {
                if(self::$objectHtml->find($nodes[0],$item['index'])) {
                    if($len == 1) {
                        $data[$k] = empty($item['attr'])? self::$objectHtml->find($nodes[0],$item['index'])->innertext: self::$objectHtml->find($nodes[0],$item['index'])->$item['attr'];
                    }else if($len == 2) {
                        $data[$k] = empty($item['attr'])? self::$objectHtml->find($nodes[0],$item['index'])->find($nodes[1],0)->innertext: self::$objectHtml->find($nodes[0],$item['index'])->find($nodes[1],0)->$item['attr'];
                    }    
                }else {
                    $data[$k] = '';
                }
            }
        }
        self::$objectHtml->clear();
        return $data;
    }
    
    /**
    * 解析html
    */
    private static function parserHtml($html)
    {
        self::$objectHtml = self::createObjectHtml($html);
    }
    
    /**
    * 传入自定义页面内容
    */
    private static function createObjectHtml($html) 
    {
         return get_create_html($html);
    }
    
}

