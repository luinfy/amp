<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Qin
 * Date: 13-8-31
 * Time: 下午4:24
 * To change this template use File | Settings | File Templates.
 */

require('conf.php');
require('func.php');
array_shift($argv);
$app_list = array('apache', 'php', 'mysql', 'htdocs', 'temp', 'down');
foreach($app_list as $k=>$v){
	if(!file_exists($root.$v) && !is_dir($root.$v)){
		mkdir($root.$v, 755);
		batout('文件夹 '.$root.$v.' 创建成功.');
	}
	$$v = $root.$v .'/';
}
if(!file_exists($htdocs . 'cgi-bin') && !is_dir($htdocs . 'cgi-bin')){
	mkdir($htdocs . 'cgi-bin', 755);
}

batout("\r\n正在检查当前系统环境, 请稍后...", true, 1);
if(strtolower($argv['0']) == 'amd64') {
    batout('当前系统为64位, 建议配置64位程序...');
}elseif(strtolower($argv['0']) == 'x86'){
    batout('当前系统为32位, 建议配置32位程序...');
}else{
    batout('不能辨别操作系统版本,脚本将在3秒后退出...', true, 3);
    exit();
}
batout("\r\n", false, 2);
$install = batput('请选择配置32位或者64位程序  Y(32位) N(64位) :');
if($install == 'y'){
    $res = file_get_contents(CONF . '32bits.txt');
	define('BITS', 'x86/');
	
}else{
    if(strtolower($argv['0']) == 'x86/') {
        batout('检测到当前操作系统为32位, 不能配置64位程序...', true, 1);
        batout('自动配置为32位 Apache MySQL PHP (PostgreSQL MongoDB)...', false, 2);
        $res = file_get_contents(CONF . '32bits.txt');
		define('BITS', 'x86/');
    }else{
        $res = file_get_contents(CONF . '64bits.txt');
		define('BITS', 'x64/');
    }    
}
$contents = file_get_contents(CONF . 'php_extension_' . substr(BITS, 0, 3) . '.txt');
$list = explode("\r\n", $contents);
foreach($list as $k=>$v){
	$v = explode("=", $v);
	$$v['0'] = $v['1'];
}
batout("", true);
batout("    **************************************************************");
batout("    **  因为 Apachelounge.com 封禁掉部分中国IP的下载            **");
batout("    **  所有 Apache 压缩包均采用 sourceforge.net 镜像下载       **");
batout("    **  也可以从百度网盘上下载需要的文件, 下载地址见 安装说明   **");
batout("    **  其余 MySQL PHP PostgreSQL MongoDB 均采用官方地址下载    **");
batout("    **  请不要修改下载的压缩文件, 脚本会检测压缩文件的完整性    **");
batout("    **************************************************************");
batout("", true, 2);

$res = explode("\r\n\r\n", $res);
$uapache = applst($res['0'], 'Apache');
$umysql = applst($res['1'], 'MySQL');
$iapache = appins($apache, $uapache);
$imysql = appins($mysql, $umysql);
$apache_dir = $iapache['1'] . '/';
$mysql_dir = $imysql['1'] . '/';
$mysql_data = $imysql['1'] . '/data/';
$bat_apache = batstr($apache_dir, 'httpd', 'Apache');
$bat_mysql = batstr($mysql_dir, 'mysqld', 'MySQL');
$ht = batput('默认htdocs目录为 ' . ROOT . "htdocs\r\n是否自定义htdocs目录 Y(是) N(否):");
if($ht == 'y'){
	do{
		batout("请输入正确的htdocs目录 : ", false);
		$htdocs_dir = trim(fgets(STDIN));
	}while(empty($htdocs_dir) || is_null($htdocs_dir) || (!file_exists($htdocs_dir) && !is_dir($htdocs_dir)));
	batout("\r\n使用自定义目录 ".$htdocs_dir . " 为htdocs目录");
}else{
	$htdocs_dir = ROOT . 'htdocs';
	batout("\r\n使用目录 " . $htdocs_dir . " 为htdocs目录");
}

batout('开始配置 ' . $apache_dir . 'conf/httpd.conf', true, 2);
if(!file_exists($apache_dir . 'conf/httpd-default.conf')){
	copy($apache_dir . 'conf/httpd.conf', $apache_dir . 'conf/httpd-default.conf');
}
if($iapache['0'] == '2.2'){
	$httpd = CONF . 'httpd22.txt';
}else{
	$httpd = CONF . 'httpd24.txt';
}
reconf($apache_dir . 'conf/httpd-default.conf', $httpd, $apache_dir . 'conf/httpd.conf');
batout('开始配置 ' . $mysql_dir . 'bin/my.ini', true, 2);
conf(CONF . 'myini.txt', $mysql_dir . 'bin/my.ini');

$ch_pgsql = batput("\r\n是否使用 PostgreSQL 数据  Y(使用) N(不使用) :");
if($ch_pgsql == 'y'){
	if(!file_exists(ROOT . 'pgsql') && !is_dir(ROOT . 'pgsql')) mkdir(ROOT . 'pgsql');
	$pgsql = ROOT . 'pgsql/';
	$upgsql = applst($res['3'], 'PostgreSQL');
	$ipgsql = appins($pgsql, $upgsql);
	$pgsql_dir = $ipgsql['1'] . '/'; 
	if(file_exists($apache_dir . 'bin/libpq.dll')) unlink($apache_dir . 'bin/libpq.dll');
	if(file_exists($apache_dir . 'bin/libintl-8.dll')) unlink($apache_dir . 'bin/libintl-8.dll');
	copy($pgsql_dir . 'bin/libpq.dll', $apache_dir . 'bin/libpq.dll');
	if($ipgsql['0'] < 9.2) copy($pgsql_dir . 'bin/libintl-8.dll', $apache_dir . 'bin/libintl-8.dll');
	$bat_pgsql = batstr($pgsql_dir, 'pg_ctl', 'PostgreSQL');
}

$ch_mongo = batput("\r\n是否使用 MongoDB 数据  Y(使用) N(不使用) :");
if($ch_mongo == 'y'){
	if(!file_exists(ROOT . 'mongo') && !is_dir(ROOT . 'mongo')) mkdir(ROOT . 'mongo');
	$mongo = ROOT . 'mongo/';
	$umongo = applst($res['4'], 'MongoDB');
	$imongo = appins($mongo, $umongo);
	$mongo_dir = $imongo['1'] . '/';
	$bat_mongo = batstr($mongo_dir, 'mongod', 'MongoDB');
	if(!file_exists($mongo_dir . 'data') && !is_dir($mongo_dir . 'data')) mkdir($mongo_dir . 'data', 755);
	if(file_exists($mongo_dir . 'mongo.log')) unlink($mongo_dir . 'mongo.log');
	if(file_exists($mongo_dir . 'mongod.cfg')) unlink($mongo_dir . 'mongod.cfg');
	file_put_contents($mongo_dir . 'mongod.cfg', "logpath=" . $mongo_dir . "mongo.log\r\nlogappend=true\r\ndbpath=" . $mongo_dir . "data\r\n");
}

$rmdir_php = '';
$ch_php = batput("\r\n是否配置多个版本PHP方便切换  Y(多版本) N(单版本) :");
if($ch_php == 'y'){
	$muphp = muphp($res['2'], $php, $xp);
	foreach($muphp as $k=>$v){
		$php_dir = $v['1'] . '/';
		$ver = str_replace('.', '', $v['0']);
		conf(CONF . 'phpconf.txt', $apache_dir . 'conf/extra/php-' . $ver . '.conf');
		file_put_contents($apache_dir . 'conf/extra/php-' . $ver . '.conf', "\r\n", FILE_APPEND);
		file_put_contents($apache_dir . 'conf/extra/php-' . $ver . '.conf', "LoadFile \"" . $php_dir . "php5ts.dll\"\r\n", FILE_APPEND);
		if($iapache['0'] == 2.2) file_put_contents($apache_dir . 'conf/extra/php-' . $ver . '.conf', "LoadModule php5_module \"" . $php_dir . "php5apache2_2.dll\"\r\n", FILE_APPEND);
		if($iapache['0'] == 2.4) file_put_contents($apache_dir . 'conf/extra/php-' . $ver . '.conf', "LoadModule php5_module \"" . $php_dir . "php5apache2_4.dll\"\r\n", FILE_APPEND);
		reconf($php_dir . 'php.ini-production', CONF . 'phpini.txt', $php_dir . 'php.ini');
		if(isset($ipgsql)){
			php_pgsql($php_dir);
		}
		if(isset($imongo)){
			$php_mongo = 'php' . $ver . '_mongo';
			php_mongo($php_dir, $ver, $$php_mongo);		
		}
		$start_php = 'start_php' . $ver;
		$$start_php = "del " . windir($apache_dir) . "conf\httpd.conf\r\n";
		$$start_php .= "copy " . windir($apache_dir) . "conf\httpd-php.conf " . windir($apache_dir) . "conf\httpd.conf\r\n";
		$$start_php .= "echo.>>" . windir($apache_dir) . "conf\httpd.conf\r\n";
		$$start_php .= "echo Include conf/extra/php-" . $ver . ".conf>>" . windir($apache_dir) . "conf\httpd.conf\r\n";
		$$start_php .= "echo.>>" . windir($apache_dir) . "conf/httpd.conf\r\n";
		$rmdir_php .= "rd /s /q " . windir($php_dir) . "\r\n";
		if($v['0'] >= 5.5){
			batout("\r\n为 PHP5.5 复制可运行在 Apache 2.2 的 php5apache2_2.dll\r\n", false, 2);
			if(!file_exists($php_dir . 'php5apache2_2.dll')) copy(EXT . BITS . 'php5apache2_2.dll', $php_dir . 'php5apache2_2.dll');
			if(!file_exists($php_dir . 'php5apache2_2_filter.dll')) copy(EXT . BITS . 'php5apache2_2_filter.dll', $php_dir . 'php5apache2_2_filter.dll');
		}
	}
}else{
	$uphp = applst($res['2'], 'PHP');
	$iphp = appins($php, $uphp);
	$php_dir = $iphp['1'] . '/';
	$ver = str_replace('.', '', $iphp['0']);
	conf(CONF . 'phpconf.txt', $apache_dir . 'conf/extra/php-' . $ver . '.conf');
	file_put_contents($apache_dir . 'conf/extra/php-' . $ver . '.conf', "\r\n", FILE_APPEND);
	file_put_contents($apache_dir . 'conf/extra/php-' . $ver . '.conf', "LoadFile \"" . $php_dir . "php5ts.dll\"\r\n", FILE_APPEND);
	if($iapache['0'] == 2.2) file_put_contents($apache_dir . 'conf/extra/php-' . $ver . '.conf', "LoadModule php5_module \"" . $php_dir . "php5apache2_2.dll\"\r\n", FILE_APPEND);
	if($iapache['0'] == 2.4) file_put_contents($apache_dir . 'conf/extra/php-' . $ver . '.conf', "LoadModule php5_module \"" . $php_dir . "php5apache2_4.dll\"\r\n", FILE_APPEND);
	reconf($php_dir . 'php.ini-production', CONF . 'phpini.txt', $php_dir . 'php.ini');
	if(isset($ipgsql)){
		php_pgsql($php_dir);
	}
	if(isset($imongo)){
		$php_mongo = 'php' . $ver . '_mongo';
		php_mongo($php_dir, $ver, $$php_mongo);			
	}
	file_put_contents($apache_dir . 'conf/httpd.conf', "\r\n", FILE_APPEND);
	file_put_contents($apache_dir . 'conf/httpd.conf', "Include conf/extra/php-" . $ver . ".conf", FILE_APPEND);
	$rmdir_php .= "rd /s /q " . windir($php_dir) . "\r\n";
	if($iphp['0'] >= 5.5){
		batout("\r\n为 PHP5.5 复制可运行在 Apache 2.2 的 php5apache2_2.dll\r\n", false, 2);
		if(!file_exists($php_dir . 'php5apache2_2.dll')) copy(EXT . BITS . 'php5apache2_2.dll', $php_dir . 'php5apache2_2.dll');
		if(!file_exists($php_dir . 'php5apache2_2_filter.dll')) copy(EXT . BITS . 'php5apache2_2_filter.dll', $php_dir . 'php5apache2_2_filter.dll');
	}
}

if(!file_exists($apache_dir . 'conf/httpd-php.conf')) copy($apache_dir . 'conf/httpd.conf', $apache_dir . 'conf/httpd-php.conf');
getpatch();


if(file_exists(INS . 'ins_service.bat')) unlink(INS . 'ins_service.bat');
rename(ROOT . '安装服务.bat', INS . 'ins_service.bat');
$cmd_start = $bat_apache['0'] . $bat_mysql['0'];
$cmd_stop = $bat_apache['1'] . $bat_mysql['1'];
$cmd_install = $bat_apache['2'] . $bat_mysql['2'];
$cmd_uninstall = $bat_apache['3'] . $bat_mysql['3'];
$cmd_rmdir = $bat_apache['4'] . $bat_mysql['4'];
if(isset($ipgsql)){
	$cmd_start .= $bat_pgsql['0'];
	$cmd_stop .= $bat_pgsql['1'];
	$cmd_install .= $bat_pgsql['2'];
	$cmd_uninstall .= $bat_pgsql['3'];
	$cmd_rmdir .= $bat_pgsql['4'];
}
if(isset($imongo)){
	$cmd_start .= $bat_mongo['0'];
	$cmd_stop .= $bat_mongo['1'];
	$cmd_install .= $bat_mongo['2'];
	$cmd_uninstall .= $bat_mongo['3'];
	$cmd_rmdir .= $bat_mongo['4'];
}
build('安装服务', $cmd_install);
build('停止服务', $cmd_stop);
$rmdir_top = '';
foreach($app_list as $k=>$v){
	$rmdir_top .= "rd /s /q " . windir($root . $v) . "\r\n";
}
$del_cmd = "del 安装服务.bat\r\ndel 停止服务.bat\r\n";
$ch_rmdir = "echo.\r\n:rcos\r\nset /p no=   请确认是否删除所有文件夹 Y(删除)或 N(不删除):\r\nif /I \"%no%\"==\"y\" goto rdr\r\nif /I \"%no%\"==\"n\" exit\r\necho 输入错误，请重新输入...\r\ngoto rcos\r\necho.\r\n:rdr\r\n";

if(isset($start_php53)){
	build('启动PHP53', $start_php53 . $cmd_start);
	$del_cmd .= "del 启动PHP53.bat\r\n";
}	
if(isset($start_php54)){
	build('启动PHP54', $start_php54 . $cmd_start);
	$del_cmd .= "del 启动PHP54.bat\r\n";
}
if(isset($start_php55)){
	build('启动PHP55', $start_php55 . $cmd_start);
	$del_cmd .= "del 启动PHP55.bat\r\n";
}
if(isset($iphp)){
	build('启动服务', $cmd_start);
	$del_cmd .= "del 启动服务.bat\r\n";
}
build('卸载服务', $cmd_uninstall . $ch_rmdir . $cmd_rmdir . $rmdir_php . $rmdir_top . $del_cmd);
exit();
?>