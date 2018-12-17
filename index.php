<?php

/**
 *   [IvanWu] (C)2001-2099 IvanWu.cn
 * @This is NOT a freeware, use is subject to license terms
 * @Author: wuxin
 * @Date:   2018-12-17 10:45:20
 * @Last Modified by:   wuxin
 * @Last Modified time: 2018-12-17 21:58:05
 * @GitHub: https://github.com/IvanWu2015
 */

error_reporting(E_ERROR);
ini_set('max_execution_time','120');//超时时间设置为2分钟

session_start();
$lock = file_exists(__DIR__.'/md5.lock');
if($lock && $_SESSION['md5_check'] != 'ivan') {
	exit('请先删除当前目录下的md5.lock文件再运行。');
} else {
	if(file_put_contents(__DIR__.'/md5.lock', 'ivan')) {
		$_SESSION['md5_check'] = 'ivan';
	} else {
		exit('lock文件创建失败，请检查'.__DIR__.'目录是否有写入权限。');
	}
}

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
	$scan = intval($_POST['scan']);
	$path = $_POST['path'] ? $_POST['path'] : $_GET['path'];
	$path = $path ? str_replace('\\', '/', $path) : '';
	if($scan == 1 && !empty($path) ) {
		$CreateMd5->set('scan_path', $path);//测试时为避免分析太多文件，指定较小的一层目录生成
		$CreateMd5->set('specify_ext_name_list', ['php']);
		$CreateMd5->set('filter_name_list', ['.git', 'data', 'cache', '.svn', 'log', 'template']);
		$CreateMd5->scanFilelist();
		echo '<script language="javascript" type="text/javascript">
		alert("已经生成生成当前环境的MD5文件");
		window.location.href="./index.php?action=logs"; 
		</script>';
	} else {
		
		//列出当前目录结构
		if(empty($path)) {
			$dir_list = scandir($base_dir);
		} else {
			$dir_list = scandir($path);
		}

	}

	break;

	case 'compare':
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
