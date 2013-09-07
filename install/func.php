<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Qin
 * Date: 13-8-31
 * Time: 下午3:38
 * To change this template use File | Settings | File Templates.
 */
function batout($str, $rn=true, $time=0){
	if($rn != false){
		echo $str . "\r\n";
	}else{
		echo $str;
	}
	if(isset($time) && is_int($time)) sleep($time);
}

function batput($str){
	do{
		batout($str, false, 1);
		$enable = strtolower(trim(fgets(STDIN)));
	}while($enable !== 'y' && $enable !== 'n');
	return $enable;
}

function applst($arr, $str, $xp=false){
	$i = 1;
	$tlist = array();
	$list = explode("\r\n", $arr);
	batout("当前可以选择配置的 " . $str . " 版本为 :\r\n");
	foreach($list as $k=>$v){
		$v = explode("=", $v);
		if($xp == true && substr($v['0'], 3, 3) >= 5.5){
			batout("当前操作系统 ". php_uname('v') . "\r\n不支持PHP5.5以上版本,忽略显示 ".$v['0']." ...\r\n要想使用PHP5.5的特性,请更换操作系统为Windows Vista以上版本...\r\n");
		}else{
			echo "    " . $i . "    " . $v['0'] . "\r\n";
			$file = substr(strrchr($v['1'], '/'), 1);
			$version = preg_replace('/' . $str . '/i', '', $v['0']);
			$tlist[$i] = array($v['0'], $v['1'], $v['2'], $v['3'], strtolower($version), $file, $v['4']);
			$i++;
		}
	}
	batout("\r\n", false);
	do{
		batout('请输入需要配置的 ' . $str . ' 版本 :', false);
		$ver = trim(fgets(STDIN));
	}while(!is_int($ver) && !in_array($ver, range(1, $i-1, 1)));
	batout('当前选择要配置的 ' . $str . ' 版本为 : ' . $tlist[$ver]['0']);
	return $tlist[$ver];
}

function chksum($file, $method, $key){
	if(strtolower($method) == 'md5'){
		$sum = md5_file($file);
	}else{
		$sum = sha1_file($file);
	}
	if($sum === strtolower($key)){
		return true;
	}else{
		return false;
	}
}

function fetch($url, $method, $key){
	$i = 1;
	$file = substr(strrchr($url, '/'), 1);
	do{
		if(!file_exists(DOWN . $file)){
			batout('文件 ' . DOWN . $file . ' 不存在, 开始下载...');
			system(FETCH . ' -c -t 2 ' . $url);
			@rename(ROOT . $file, DOWN . $file);
		}else{
			batout('文件 ' . DOWN . $file . ' 已存在...');
		}
		batout("开始对文件" . DOWN . $file . "进行完整性校对...", true, 2);
		$sum = chksum(DOWN . $file, $method, $key);
		if($sum === true){
			batout('文件 ' . DOWN . $file . ' 完整性校对成功...', true, 1);
			break;
		}else{
			batout('文件 ' . DOWN . $file . "完整性校对失败...\r\n删除文件 ". DOWN . $file . ", 重新下载...\r\n", true, 1);
			@unlink(DOWN . $file);
		}
		if($i == DOWNTIME){
			batout("\r\n下载文件失败, 已尝试下载 ".$i." 次, 请检查网络或者手动下载. 脚本将在3秒后退出\r\n", true, 3);
			exit();
		}
		$i++;
	}while($sum === false);
}

function unzip($archive, $dir, $files, $folder){
	if(substr($dir, -1) !== '/') $dir = $dir . '/';
	if($files == 'All'){
		batout("开始解压 " . $archive . " 里的全部文件到 " . $dir . $folder . " ...\r\n");
		system(UNZIP . ' x ' . $archive . ' -o' . $dir . $folder . ' -y');
	}else{
		batout("开始解压 " . $archive . " 里的 " . $files. " 到 " . $dir . $folder . " ...\r\n");
		system(UNZIP . ' x ' . $archive . ' ' . $files . ' -o' . $dir . ' -y');
		@rename($dir . $files, $dir . $folder);
	}
}

function appins($dir, $array){
	if(file_exists($dir . $array['4']) && is_dir($dir . $array['4'])){
		batout('检测到文件夹 ' . $dir . $array['4'] . " 已存在...\r\n");
		if(preg_match('/mysql|pgsql|mongo/i', $dir)) batout("\r\n检测到 " . $dir . $array['4'] . " 可能包含数据文件,请自行备份数据...\r\n", true, 2);
		batout('删除文件夹将重新从原始压缩包解压文件, 使用现有文件夹将仅删除配置文件');
		$rmdir = batput('请选择是否删除文件夹  Y(删除) N(仅删除配置文件) : ', false);
		if($rmdir = 'n'){
			batout("\r\n将对 " . $dir . $array['4'] . ' 的 ' . $array['0'] . " 重新配置...\r\n", true, 2);
			return array(substr($array['4'], 0, 3), $dir . $array['4']);
		}else{
			$tmp_bat = "@echo off\r\nrd /s /q ".str_replace('/', '\\', $dir . $array['4']);
			file_put_contents(TEMP . 'tmp_bat.bat', $tmp_bat);
			system(TEMP . 'tmp_bat.bat');
			unlink(TEMP . 'tmp_bat.bat');
			fetch($array['1'], $array['2'], $array['3']);
			unzip(DOWN . $array['5'], $dir, $array['6'], $array['4']);
		}
	}else{
		fetch($array['1'], $array['2'], $array['3']);
		unzip(DOWN . $array['5'], $dir, $array['6'], $array['4']);
	}
	return array(substr($array['4'], 0, 3), $dir . $array['4']);
}

function muphp($arr, $dir, $xp=false){
	$str = 'PHP';
	$list = explode("\r\n", $arr);
	$tlist = array();
	foreach($list as $k=>$v){
		$v = explode("=", $v);
		$file = substr(strrchr($v['1'], '/'), 1);
		$version = preg_replace('/' . $str . '/i', '', $v['0']);
		if($xp == true && substr($version, 0, 3) >= 5.5){
			batout("\r\n当前系统不支持 PHP5.5以上版本, \r\n忽略 PHP" . $version . " 的配置...\r\n", true, 2);
		}else{
			batout("\r\n当前配置的 " . $str . " 版本为 : " . $v['0'] . "\r\n");
			$tlist[] = appins($dir, array($v['0'], $v['1'], $v['2'], $v['3'], strtolower($version), $file, $v['4']));
		}
	}
	return $tlist;
}

function reconf($base, $fix, $file){
	global $apache_dir, $htdocs_dir, $php_dir;
	if(file_exists($file)) unlink($file);
	$base = file_get_contents($base);
	$fix = file_get_contents($fix);
	$list = explode("\r\n", $fix);
	foreach($list as $k=>$v){
		$v = explode("==", $v);
		$key = str_replace('/', '\/', $v['0']);
		$key = str_replace("'", "\'", $key);
		if(preg_match('/{(.*)}/', $v['1'], $m)) {
			$v['1'] = preg_replace('/{(.*)}/', $$m['1'], $v['1']);
		}
		if(preg_match('/'.$key.'/', $base)) {
			$base = preg_replace('/'.$key.'/', $v['1'], $base);
		}
	}
	file_put_contents($file, $base);
}

function conf($fix, $file){
	global $mysql_dir, $mysql_data, $php_dir;
	if(file_exists($file)) unlink($file);
	$f = fopen($file, 'a+');
	$fix = file_get_contents($fix);
	$list = explode("\r\n", $fix);
	foreach($list as $k=>$v){
		if(preg_match('/{(.*)}/', $v, $m)) {
			$v = preg_replace('/{(.*)}/', $$m['1'], $v);
		}
		fwrite($f, $v . "\r\n");
	}
	fclose($f);
}

function php_pgsql($php_dir){
	file_put_contents($php_dir . 'php.ini', "\r\n", FILE_APPEND);
	file_put_contents($php_dir . 'php.ini', "extension=php_pdo_pgsql.dll\r\n", FILE_APPEND);
	file_put_contents($php_dir . 'php.ini', "extension=php_pgsql.dll\r\n", FILE_APPEND);
}

function php_mongo($php_dir, $ver, $file){
	if(file_exists($php_dir . 'ext/php_mongo.dll')) unlink($php_dir . 'ext/php_mongo.dll');
	copy(EXT . BITS . $file, $php_dir . 'ext/php_mongo.dll');
	file_put_contents($php_dir . 'php.ini', "\r\n", FILE_APPEND);
	file_put_contents($php_dir . 'php.ini', "[Mongodb]\r\n", FILE_APPEND);
	file_put_contents($php_dir . 'php.ini', "extension=" . $php_dir . "ext/php_mongo.dll\r\n", FILE_APPEND);
	file_put_contents($php_dir . 'php.ini', "mongo.default_host=\"localhost\"\r\n", FILE_APPEND);
	file_put_contents($php_dir . 'php.ini', "mongo.default_port=27017\r\n", FILE_APPEND);
	file_put_contents($php_dir . 'php.ini', "mongo.auto_reconnect=1\r\n", FILE_APPEND);
	file_put_contents($php_dir . 'php.ini', "mongo.allow_persistent=1\r\n", FILE_APPEND);
	file_put_contents($php_dir . 'php.ini', "mongo.chunk_size=262144\r\n", FILE_APPEND);
	file_put_contents($php_dir . 'php.ini', "mongo.cmd=\"$\"\r\n", FILE_APPEND);
	file_put_contents($php_dir . 'php.ini', "mongo.utf8=1\r\n", FILE_APPEND);
}

function windir($dir){
	return str_replace("/", "\\", $dir);
}

function batstr($dir, $exe, $str){
	$start = "net start " . $str . "\r\n";
	$stop = "net stop " . $str . "\r\n";
	$rmdir = "rd /s /q " . windir($dir) . "\r\n";
	if(substr($exe, -4) == '.exe') $exe = substr($exe, 0, strlen($exe)-4);
	switch($exe){
		case 'httpd':
			$install = windir($dir) . "bin\httpd.exe -k install -n " . $str . "\r\nsc config " . $str . " start=demand\r\n";
			$uninstall = windir($dir) . "bin\httpd.exe -k uninstall -n " . $str . "\r\n";
			break;
		case 'mysqld':
			$install = windir($dir) . "bin\mysqld.exe --install " . $str . " --defaults-file=\"" . windir($dir) . "bin\my.ini\"\r\nsc config " . $str . " start=demand\r\n";
			$uninstall = windir($dir) . "bin\mysqld.exe --remove\r\n";
			break;
		case 'pg_ctl':
			$install = windir($dir) . "bin\initdb.exe -D " . windir($dir) . "data -E UTF8 --locale=C\r\n";
			$install .= windir($dir) . "bin\pg_ctl.exe register -D " . windir($dir) . "data -N " . $str ."\r\nsc config " . $str . " start=demand\r\n";
			$uninstall = windir($dir) . "bin\pg_ctl.exe unregister -N " . $str . "\r\n";
			break;
		case 'mongod':
			$install = windir($dir) . "bin\mongod.exe --config " . windir($dir) . "mongod.cfg --install\r\nsc config MongoDB start=demand\r\n";
			$uninstall = windir($dir) . "bin\mongod.exe --remove\r\n";
			break;
		default:
	}
	return array($start, $stop, $install, $uninstall, $rmdir);
}

function build($file, $str, $pause=false){
	$bat = "@echo off\r\ncd /D %~dp0\r\ncolor 0b\r\n echo 本地 Apache MySQL PHP 环境正在尝试" . $file . "...\r\n请不要关闭本窗口, 稍候片刻...\r\necho.\r\n";
	if(file_exists(ROOT . $file . '.bat')) unlink(ROOT . $file . '.bat');
	$f = fopen(ROOT . $file . '.bat', 'a+');
	fwrite($f, $bat);
	fwrite($f, $str);
	if($pause !== false) fwrite($f, "pause");
	fclose($f);
}

function getpatch(){
	$list = scandir(PATCH);
	foreach($list as $file){
		if($file != '.' && $file != '..' && strtolower(substr($file, -4)) == '.php') require PATCH . $file;
	}
}
?>