<?php
/**
 * 	php_compare
 * 	php文件对比工具，开发目的为网站被黑后快速对比本地文件与服务器文件差异
 *      This is NOT a freeware, use is subject to license terms
 *      Author: admin@ivanwu.cn
 *      
 */
error_reporting(E_ERROR);
$compare = new CreateMd5List;
$compare->set('base_dir', 'E:/phpStudy/PHPTutorial/WWW/chinatt/source/plugin/');
$compare->set('specify_ext_name_list', ['php', 'html']);
$compare->scanFilelist();
//$compare->createMd5File();
print_r($compare->md5file_list);

/**
 * 文件MD5生成处理类
 */
class CreateMd5List {
	public $base_dir;	//当前目录
	public $md5file_list;	//最终的MD5文件结果
	public $save_path = '';	//md5文件保存路径
	public $filter_name_list = ['.', '..', '.git', 'data', 'cache', '.svn', 'log', '.gitignore'];//过滤指定文件夹或文件不处理
	public $filter_ext_name_list = ['jpg','gif','png','bmp','css', 'md5'];//需要过滤不处理的文件类型
	public $specify_ext_name_list = ['php','htm']; //指定要处理的文件类型


	public $file_list = [];//存储扫描到的文件列表
	public $file_handle = '';//存储MD5文件对象


	/**
	 * 初始化处理
	 */
	function __construct() {
		$this->base_dir = __DIR__;//设置当前目录为根目录
		$this->base_dir = str_replace('\\', '/', $this->base_dir);
		$this->save_path = $this->base_dir;
	}

	/**
	 * { function_description }
	 */
	function __destruct(){
		fclose($this->file_handle);
		return;
	}

	/**
	 * 设置参数
	 * @param      <array>  $setting  The setting
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
		$dir = empty($dir) ? $this->base_dir : $dir;
		$dir_list = scandir($dir);
		foreach($dir_list as $file) {
			if(!in_array($file, $this->filter_name_list)) {
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
		$this->md5_file_name = 'scan_'.date("Ymd_His",time()+8*60*60).'.md5';
		$this->file_handle = fopen($this->save_path.'/'.$this->md5_file_name, 'a');
		if(!$this->file_handle) {
			exit('md5文件生成错误，请检查是否有写入权限。生成路径：<br/>'.$this->save_path.'/'.$this->md5_file_name);
		}
		return true;
	}

	/**
	 * 计算单个文件MD5并存入总列表中
	 *
	 * @param      string  $dir    The dir
	 * @param      string  $file   The file
	 */
	function addMd5toList($dir, $file) {
		if(!$this->file_handle) {
			$this->createMd5File();//创建存储文件
		}
		$this->file_list[] = $file;
		$md5_value= md5_file($dir.'/'.$file);
		$path = str_replace($this->base_dir, '.', $dir);//使用相对路径，避免不同环境目录不同无法对比
		$line = $path.'/'.$file.'|'.$md5_value;
		$this->md5file_list[] = $line;
		fwrite($this->file_handle, $line."\r\n");
		return;
	}




}