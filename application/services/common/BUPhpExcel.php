<?php

namespace app\services\common;

use PHPExcel_IOFactory;
use PHPExcel;

/**
* 分类业务逻辑
*/
class BUPhpExcel
{	
	/**
     * 将读取到的 excel 文件转化为数组数据并返回
     * 此处的要求是：
     *          excel文件的后缀名不要手动改动，一般为 xls、xlsx
     *          excel文件中的数据尽量整理的跟数据表一样规范
     *
     * @param $filename 文件路径，保证能访问到
     * @return array
     */
    public function readExcelFileToArray($filename)
    {
        if (!file_exists($filename)) {
        	throw new \Exception("无法找到文件"); 
        } else {
            $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            $objExcel = null;
            if ($extension == 'xlsx') {
                $objReader = new \PHPExcel_Reader_Excel2007();
                $objExcel = $objReader->load($filename, 'utf-8');
            } else if ($extension == 'xls') {
                $objReader = new \PHPExcel_Reader_Excel5();
                $objExcel = $objReader->load($filename, 'utf-8');
            } else if ($extension == 'csv') {
                $PHPReader = new \PHPExcel_Reader_CSV();
                //默认输入字符集
                $PHPReader->setInputEncoding('GBK');
                //默认的分隔符
                $PHPReader->setDelimiter(',');
                //载入文件
                $objExcel = $PHPReader->load($filename);
            }
            $excelArr = $objExcel->getSheet(0)->toArray();
        }
        return $excelArr;
    }

    /**
     * 将得到的数组数据，转化为Excel文件导出
     * @param array $list 数组数据
     * @param array $headerArr 显示的顶部导航栏
     * @param string $excelTitle 表格标题
     * @param string $savefile 构建存储文件，注意扩展名
     */
    public function outputDataToExcelFile($list = [], $headerArr = [], $excelTitle = '', $savefile = '')
    {
        //实例化PHPExcel类
        $objPHPExcel = new \PHPExcel();
        //设置头信息 激活当前的sheet表
        $objPHPExcel->setActiveSheetIndex(0);
        $keyC = ord('A');
        foreach ($headerArr as $head) {
            $colKey = chr($keyC);
            //TODO 设置表格头（即excel表格的第一行）
            $objPHPExcel->getActiveSheet()->setCellValue($colKey . '1', $head);
            //设置单元格宽度
            $objPHPExcel->getActiveSheet()->getColumnDimension($colKey)->setWidth(20);
            $keyC++;
        }

        $colIndex = 2;
        foreach ($list as $key => $rows) {
            $colKey2 = ord('A');
            foreach ($list[$key] as $keyName => $value) {
                $objPHPExcel->getActiveSheet()->setCellValue(chr($colKey2) . $colIndex, $value);
                $colKey2++;
            }
            $colIndex++;
        }

        //设置当前激活的sheet表格名称；
        $objPHPExcel->getActiveSheet()->setTitle($excelTitle);
        //设置浏览器窗口下载表格
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header('Content-Disposition:inline;filename="' . $savefile . '"');
        //生成excel文件
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        //下载文件在浏览器窗口
        $objWriter->save('php://output');
        exit;
    }

}