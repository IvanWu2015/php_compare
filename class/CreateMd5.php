<?php

/**
 *   [IvanWu] (C)2001-2099 IvanWu.cn
 * @This is NOT a freeware, use is subject to license terms
 * @Author: wuxin
 * @Date:   2018-12-12 21:44:09
 * @Last Modified by:   wuxin
 * @Last Modified time: 2018-12-17 16:27:08
 * @GitHub: https://github.com/IvanWu2015
 */

/**
 * 文件MD5生成处理类
 */
class CreateMd5 {
	public $base_dir;	//当前目录
	public $md5file_list;	//最终的MD5文件结果
	public $save_path = '';	//md5文件保存路径
	public $scan_path = '';//扫描路径
	public $filter_name_list = ['.git', 'data', 'cache', '.svn', 'log', '.gitignore'];//过滤指定文件夹或文件不处理
	public $filter_ext_name_list = ['jpg','gif','png','bmp','css', 'md5', 'txt'];//需要过滤不处理的文件类型
	public $specify_ext_name_list = ['php','htm']; //指定要处理的文件类型

	public $md5_file_name = '';//存储的MD5文件名称
	public $file_list = [];//存储扫描到的文件列表
	public $file_handle = '';//存储MD5文件对象


	/**
	 * 初始化处理
	 */
	function __construct() {
		$this->base_dir = dirname(__DIR__);//设置当前目录为根目录
		$this->base_dir = str_replace('\\', '/', $this->base_dir);
		$this->save_path = $this->base_dir.'/md5_logs';
		$this->scan_path = $this->base_dir;
	}

	/**
	 * 最后操作 关闭写入文件
	 */
	function __destruct(){
		fclose($this->file_handle);
		return;
	}

	/**
	 * 设置参数
	 *
	 * @param      string  $key    The key
	 * @param      string/array  $value  The value
	 */
	function set($key, $value) {
		$this->$key = $value;
		return;
	}

	/**
	 * 循环获取指定目录的文件
	 *
	 * @param      string  $dir       The dir
	 * @return     array   The dir list.
	 */
	function scanFilelist($dir = '') {
		$dir = empty($dir) ? $this->scan_path : $dir;
		$dir_list = scandir($dir);
		foreach($dir_list as $file) {
			if(!in_array($file, $this->filter_name_list) && !in_array('.', '..')) {
	            //子文件夹递归
				if(is_dir($dir."/".$file)) { 
					$this->scanFilelist($dir."/".$file);
				} else {
					$ext_name = end(explode(".",$file));
	    				//如果指定了文件类型
					if(!empty($this->specify_ext_name_list)) {
						if(in_array($ext_name, $this->specify_ext_name_list)) {
							$this->addMd5toList($dir, $file);
						}
	    					//否则过滤掉指定文件类型
					} else{
						if(!in_array($ext_name, $this->filter_ext_name_list)) {
							$this->addMd5toList($dir, $file);
						}
					}
				}
			}
		}
		return $this->file_list;
	}


	/**
	 * Creates a md 5 file.
	 */
	function createMd5File() {
		if(is_dir($this->save_path) == false){
			if(@mkdir($this->save_path) == false) {
				echo "文件存储路径创建失败，请确定是否有文件夹创建权限。或者使用->set('save_path', '新路径')指定新路径<br/>";
				echo "当前存储路径为：".$this->save_path;
			}
		}
		$this->md5_file_name = 'scan_'.$_SERVER['SERVER_NAME'].'_'.date("Ymd_His",time()+8*60*60).'.md5';
		$this->file_handle = fopen($this->save_path.'/'.$this->md5_file_name, 'a');
		if(!$this->file_handle) {
			exit('md5文件生成错误，请检查是否有写入权限。生成路径：<br/>'.$this->save_path.'/'.$this->md5_file_name);
		}
		$this->addLine('/****************************************************');
		$this->addLine('*                 Php Md5 Scan');
		$this->addLine('*        Tool Author:  IvanWu(admin@ivanwu.cn)');
		$this->addLine('*         Web   URL : '.$_SERVER['SCRIPT_NAME']);
		$this->addLine('*        Server   IP: '.$_SERVER['SERVER_ADDR']);
		$this->addLine('*        Server Name: '.$_SERVER['SERVER_NAME']);
		$this->addLine('*        Scan   Path: '.$this->scan_path);
		$this->addLine('*        Scan   Time: '.date("Y-m-d H:i:s",time()+8*60*60));
		$this->addLine('*****************************************************/'."\r\n");
		return true;
	}

	/**
	 * 计算单个文件MD5并存入总列表中
	 *
	 * @param      string  $dir    The dir
	 * @param      string  $file   The file
	 */
	function addMd5toList($dir, $file) {
		$this->file_list[] = $file;
		$md5_value= md5_file($dir.'/'.$file);
		$path = str_replace($this->base_dir, '.', $dir);//使用相对路径，避免不同环境目录不同无法对比
		$line = $path.'/'.$file.'|'.$md5_value;
		$this->md5file_list[] = $line;
		$this->addLine($line);
		return;
	}


	/**
	 * 将信息写入行
	 *
	 * @param      string  $line   The line
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	function addLine($line) {
		if(!$this->file_handle) {
			$this->createMd5File();//创建存储文件
		}
		return fwrite($this->file_handle, $line."\r\n");
	}


	function getMd5Logs() {
		$file_lists = scandir($this->base_dir.'/md5_logs');
		$md5_list = [];
		foreach($file_lists as $temp) {
			if(end(explode('.', $temp)) == 'md5'){
				$new_file['file_name'] = $temp;
				$new_file['file_size'] = filesize($this->base_dir.'/md5_logs/'.$temp);
				$md5_list[] = $new_file;
			}
		}
		return $md5_list;
	}

}