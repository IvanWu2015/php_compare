<?php

/**
 *   [IvanWu] (C)2001-2099 IvanWu.cn
 * @This is NOT a freeware, use is subject to license terms
 * @Author: wuxin
 * @Date:   2018-12-17 10:45:20
 * @Last Modified by:   wuxin
 * @Last Modified time: 2018-12-17 16:30:17
 * @GitHub: https://github.com/IvanWu2015
 */

error_reporting(E_ERROR);

include_once "./class/CreateMd5.php";
$CreateMd5 = new CreateMd5;//实例化MD5处理类

$action = $_GET['action'] ? $_GET['action'] : 'index';
switch ($action) {
	case 'index':

	break;

	case 'logs':
	$md5_file_list = $CreateMd5->getMd5Logs();
	break;

	case 'scan':
		ini_set('max_execution_time','300');//超时时间设置为5分钟

		// $compare->set('base_dir', 'E:/phpStudy/PHPTutorial/WWW/chinatt/source/plugin/');
		$compare->set('specify_ext_name_list', ['php']);
		$compare->set('filter_name_list', ['.git', 'data', 'cache', '.svn', 'log', 'template']);
		$compare->scanFilelist();
		echo '文件已经生成,请下载下来后与本地生成的MD5文件进行对比,并尽快删除本文件。<br/>';
		echo '路径:'.$compare->save_path.'/'.$compare->md5_file_name;

		break;

		case 'compare':
		ini_set('max_execution_time','300');//超时时间设置为5分钟
		// include the Diff class  
		require_once './class/Diff.php';  
		$file0 = $_POST['file'][0];
		$file1 = $_POST['file'][1];
		// compare two files line by line  
		$diff = Diff::compareFiles('./md5_logs/'.$file0, './md5_logs/'.$file1); 
		$diff_string =  Diff::toTable($diff);  
		break;
	}




	include_once "./template/header.htm";
	include_once "./template/".$action.".htm";
	include_once "./template/footer.htm";
