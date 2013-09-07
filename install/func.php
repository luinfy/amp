<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Qin
 * Date: 13-8-31
 * Time: ����3:38
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
	batout("��ǰ����ѡ�����õ� " . $str . " �汾Ϊ :\r\n");
	foreach($list as $k=>$v){
		$v = explode("=", $v);
		if($xp == true && substr($v['0'], 3, 3) >= 5.5){
			batout("��ǰ����ϵͳ ". php_uname('v') . "\r\n��֧��PHP5.5���ϰ汾,������ʾ ".$v['0']." ...\r\nҪ��ʹ��PHP5.5������,���������ϵͳΪWindows Vista���ϰ汾...\r\n");
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
		batout('��������Ҫ���õ� ' . $str . ' �汾 :', false);
		$ver = trim(fgets(STDIN));
	}while(!is_int($ver) && !in_array($ver, range(1, $i-1, 1)));
	batout('��ǰѡ��Ҫ���õ� ' . $str . ' �汾Ϊ : ' . $tlist[$ver]['0']);
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
			batout('�ļ� ' . DOWN . $file . ' ������, ��ʼ����...');
			system(FETCH . ' -c -t 2 ' . $url);
			@rename(ROOT . $file, DOWN . $file);
		}else{
			batout('�ļ� ' . DOWN . $file . ' �Ѵ���...');
		}
		batout("��ʼ���ļ�" . DOWN . $file . "����������У��...", true, 2);
		$sum = chksum(DOWN . $file, $method, $key);
		if($sum === true){
			batout('�ļ� ' . DOWN . $file . ' ������У�Գɹ�...', true, 1);
			break;
		}else{
			batout('�ļ� ' . DOWN . $file . "������У��ʧ��...\r\nɾ���ļ� ". DOWN . $file . ", ��������...\r\n", true, 1);
			@unlink(DOWN . $file);
		}
		if($i == DOWNTIME){
			batout("\r\n�����ļ�ʧ��, �ѳ������� ".$i." ��, ������������ֶ�����. �ű�����3����˳�\r\n", true, 3);
			exit();
		}
		$i++;
	}while($sum === false);
}

function unzip($archive, $dir, $files, $folder){
	if(substr($dir, -1) !== '/') $dir = $dir . '/';
	if($files == 'All'){
		batout("��ʼ��ѹ " . $archive . " ���ȫ���ļ��� " . $dir . $folder . " ...\r\n");
		system(UNZIP . ' x ' . $archive . ' -o' . $dir . $folder . ' -y');
	}else{
		batout("��ʼ��ѹ " . $archive . " ��� " . $files. " �� " . $dir . $folder . " ...\r\n");
		system(UNZIP . ' x ' . $archive . ' ' . $files . ' -o' . $dir . ' -y');
		@rename($dir . $files, $dir . $folder);
	}
}

function appins($dir, $array){
	if(file_exists($dir . $array['4']) && is_dir($dir . $array['4'])){
		batout('��⵽�ļ��� ' . $dir . $array['4'] . " �Ѵ���...\r\n");
		if(preg_match('/mysql|pgsql|mongo/i', $dir)) batout("\r\n��⵽ " . $dir . $array['4'] . " ���ܰ��������ļ�,�����б�������...\r\n", true, 2);
		batout('ɾ���ļ��н����´�ԭʼѹ������ѹ�ļ�, ʹ�������ļ��н���ɾ�������ļ�');
		$rmdir = batput('��ѡ���Ƿ�ɾ���ļ���  Y(ɾ��) N(��ɾ�������ļ�) : ', false);
		if($rmdir = 'n'){
			batout("\r\n���� " . $dir . $array['4'] . ' �� ' . $array['0'] . " ��������...\r\n", true, 2);
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
			batout("\r\n��ǰϵͳ��֧�� PHP5.5���ϰ汾, \r\n���� PHP" . $version . " ������...\r\n", true, 2);
		}else{
			batout("\r\n��ǰ���õ� " . $str . " �汾Ϊ : " . $v['0'] . "\r\n");
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
	$bat = "@echo off\r\ncd /D %~dp0\r\ncolor 0b\r\n echo ���� Apache MySQL PHP �������ڳ���" . $file . "...\r\n�벻Ҫ�رձ�����, �Ժ�Ƭ��...\r\necho.\r\n";
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