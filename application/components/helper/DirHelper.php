<?php 

namespace app\components\helper;
/**
* dir目录操作类
*/
class DirHelper
{
	
	/**
	* 递归创建目录
	* @param string $dir 目录名 
	* @param return bool
	*/
	public static function mkDir($dir, $mode = 0755)
	{
		 //$dir = rtrim($dir,'/').'/';
		 if(!is_dir($dir)) {
			if(!self::mkDir(dirname($dir))) {
				return false;
			}
			if(!mkdir($dir, $mode)) {
				return false;
			}
		}
		
		return true;
	}

	/**
	* 改变指定文件所有者
	* @param string $file 文件 
	* @param string $owner 所有者 
	* @param return bool
	*/
	public static function chOwn($file, $owner)
	{
		if(empty($owner)) {
			return false;
		}
		return chown($file, $owner);
	}
	
	
	/**
	* 删除目录， 仅删除指定目录下的文件，不删除目录文件夹
	* @param string $dir 目录
	* @param return bool
	*/
	public static function delFileUnderDir($dir)
	{
		if($handle = opendir($dir)) {
			while(($item = readdir($handle)) !== false) {
				if($item != '.' && $item != '..' ) {
					$path = $dir.'/'.$item;
					if(is_dir($path)) {
						self::delFileUnderDir($path);
					}else {
						unlink($path);
					}
				}
			}
			closedir($handle );
		}
	}
	
	
	/**
	* 删除目录
	* @param string $dir 目录
	* @param return bool
	*/
	public static function delDir($dir)
	{
		if(!file_exists($dir)) {
			return false;
		}
		$_dir = opendir($dir);
		while($filename = readdir($_dir)) {
			$file = $dir . '/' . $filename;
			if($filename != '.' && $filename != '..') {
				if(is_dir($file)) {
					self::delDir($file);
				} 
				else {
					unlink($file);
				}            
			}
		}
		closedir($_dir);
		return rmdir($dir);		
	}
	
	
	/**
	* 复制目录
	* @param string $surDir 原目录
	* @param string $toDir 目标目录
	* @param return bool
	*/
	public static function copyDir($surDir, $toDir)
	{
		$surDir = rtrim($surDir,'/').'/';
		$toDir = rtrim($toDir,'/').'/';
		if(!file_exists($surDir))  {
			return false;
		}
		if(!file_exists($toDir)) {
			self::mkDir($toDir);
		}
		$file = opendir($surDir);
		while($filename = readdir($file)) {
			$file1 = $surDir .'/'.$filename;
			$file2 = $toDir .'/'.$filename;
			if($filename != '.' && $filename != '..') {
				if(is_dir($file1)) {
					self::copyDir($file1, $file2);        
				} 
				else {
					copy($file1, $file2);
				}
			}
		}
		closedir($file);
		return true;
	}
	
	
	/**
	* 列出目录
	* @param string $dir 目录名
	* @param return array 目录数组
	*/
	public static function getDirs($dir)
	{
		$dir = rtrim($dir,'/').'/';
		$dirArray[][] = null;
		if(false !=($handle = opendir($dir))) {
			$i = 0;
			$j = 0;
			while( false !== ($file = readdir($handle ))) {
				if(is_dir($dir . $file )) { 
					//判断是否文件夹
					$dirArray ['dir'][$i] = $file;
					$i ++;
				} 
				else  {
					$dirArray['file'][$j] = $file;
					$j ++;
				}
			}
			closedir($handle);
		}
		return $dirArray;
	}
	
	
	/**
	* 统计文件夹大小
	* @param string $dir 目录名
	* @param return int 文件夹大小(单位 B)
	*/
	public static function getSize($dir)
	{
		$dirlist = opendir($dir);
		$dirsize = 0;
		while(false !== ($folderorfile = readdir($dirlist))) { 
			if($folderorfile != "." && $folderorfile != "..") { 
				if(is_dir("$dir/$folderorfile")) { 
					$dirsize += self::getSize("$dir/$folderorfile"); 
				}
				else { 
					$dirsize += filesize("$dir/$folderorfile"); 
				}
			}    
		}
		closedir($dirlist);
		return $dirsize;
	}
	
	/**
	* 检测是否为空文件夹
	* @param string $dir 目录名
	* @param return bool
	*/
	public static function emptyDir($dir)
	{
		return (($files = @scandir($dir)) && count($files) <= 2); 
	}
	
	
}

